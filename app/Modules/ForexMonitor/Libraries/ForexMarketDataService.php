<?php
/**
 * Service dashboard forex menangani polling realtime GBP/JPY, cache quote dan
 * chart, fallback provider publik, serta pengecekan alert pada setiap refresh.
 */

namespace App\Modules\ForexMonitor\Libraries;

use App\Modules\ForexMonitor\Models\ForexMonitorModel;
use App\Modules\ForexPrediction\Libraries\ForexSignalService;
use App\Modules\ForexMonitor\Libraries\ForexDataService;

class ForexMarketDataService
{
	protected ForexMonitorModel $model;
	protected ForexDataService $dailyService;
	protected ForexSignalService $signalService;
	protected $cache;
	protected string $alphaUrl = 'https://www.alphavantage.co/query';
	protected string $twelveDataUrl = 'https://api.twelvedata.com';
	protected string $finnhubUrl = 'https://finnhub.io/api/v1';
	protected string $oandaUrl = 'https://api-fxpractice.oanda.com/v3';
	protected string $yahooChartUrl = 'https://query1.finance.yahoo.com/v8/finance/chart/GBPJPY=X';
	protected string $yahooQuoteUrl = 'https://finance.yahoo.com/quote/GBPJPY=X';
	protected int $quoteCacheTtl = 25;
	protected int $chartCacheTtl = 300;
	protected int $historyWarmCacheTtl = 21600;
	protected int $indicatorHistoryDays = 45;

	public function __construct(?ForexMonitorModel $model = null)
	{
		$this->model = $model ?: new ForexMonitorModel();
		$this->dailyService = new ForexDataService($this->model);
		$this->signalService = new ForexSignalService($this->model);
		$this->cache = cache();
		$this->quoteCacheTtl = max(10, (int) (env('forex.dashboardQuoteCacheTtl') ?: 25));
		$this->chartCacheTtl = max(60, (int) (env('forex.dashboardChartCacheTtl') ?: 300));
		$this->historyWarmCacheTtl = max(1800, (int) (env('forex.dashboardHistoryWarmCacheTtl') ?: 21600));
		$this->oandaUrl = rtrim((string) (env('forex.oandaApiUrl') ?: $this->oandaUrl), '/');
	}

	/**
	 * Payload dashboard dibangun terpusat agar controller web dan polling AJAX
	 * selalu mengembalikan struktur data yang sama untuk live card dan chart.
	 */
	public function getDashboardPayload(string $timeframe = '1D', bool $forceRefresh = false, int $userId = 0, ?string $granularity = null): array
	{
		$timeframe = $this->normalizeTimeframe($timeframe);
		$this->ensureHistoricalDataCoverage($forceRefresh);

		$livePrice = $this->syncLivePrice($forceRefresh);
		$chartPayload = $this->getChartPayload($timeframe, $forceRefresh, $granularity);
		$livePrice = $this->harmonizeLivePrice($livePrice, $chartPayload);
		$tradingSignal = $this->signalService->getSignalPayload($livePrice, $chartPayload, $userId);
		$chartPayload['signal_overlays'] = $tradingSignal['chart_overlays'] ?? [];

		$triggeredAlerts = $userId > 0 ? $this->model->checkTriggeredAlerts($userId, $livePrice) : [];
		if (!empty($tradingSignal['automation']['triggered_alerts'])) {
			$triggeredAlerts = array_merge($triggeredAlerts, $tradingSignal['automation']['triggered_alerts']);
		}
		$activeAlerts = $userId > 0 ? $this->model->getActiveAlerts($userId) : [];

		return [
			'status' => !empty($livePrice) ? 'ok' : 'warning',
			'pair' => $this->model->getPair(),
			'timeframe' => $timeframe,
			'live_price' => $livePrice,
			'high_low_summary' => $this->model->getHighLowSummary($livePrice),
			'chart' => $chartPayload,
			'trading_signal' => $tradingSignal,
			'active_alerts' => $activeAlerts,
			'triggered_alerts' => $triggeredAlerts,
			'updated_at' => date('Y-m-d H:i:s'),
		];
	}

	/**
	 * Quote realtime disimpan di cache singkat agar polling 30 detik terasa
	 * hidup tetapi tetap aman terhadap limit provider gratis.
	 */
	public function syncLivePrice(bool $forceRefresh = false): array
	{
		$cacheKey = 'forex_gbpjpy_live_quote';
		if ($forceRefresh) {
			$this->cache->delete($cacheKey);
		}

		$cached = $this->cache->get($cacheKey);
		if (!$forceRefresh && is_array($cached) && !empty($cached['current_price'])) {
			return $cached;
		}

		$existingLive = $this->model->getLivePriceRow();
		$latestDaily = $this->model->getLatestSnapshot();

		try {
			$providerPayload = $this->fetchLivePriceFromProviders();
			if (!$providerPayload) {
				return $this->cacheAndReturnLiveFallback($cacheKey, $existingLive, $latestDaily);
			}

			$normalized = $this->normalizeLivePayload($providerPayload, $existingLive, $latestDaily);
			$row = $this->model->upsertLivePrice($normalized);
			$this->cache->save($cacheKey, $row, $this->quoteCacheTtl);

			return $row;
		} catch (\Throwable $e) {
			return $this->cacheAndReturnLiveFallback($cacheKey, $existingLive, $latestDaily);
		}
	}

	/**
	 * Dataset chart dipisahkan per timeframe agar tombol 1D/1W/1M dapat
	 * mengganti sumber data tanpa mengubah kontrak JSON di sisi frontend.
	 */
	public function getChartPayload(string $timeframe = '1D', bool $forceRefresh = false, ?string $granularity = null): array
	{
		$timeframe = $this->normalizeTimeframe($timeframe);
		$granularity = $this->resolveChartGranularity($timeframe, $granularity);
		$cacheKey = 'forex_gbpjpy_chart_' . strtolower($timeframe . '_' . $granularity);
		if ($forceRefresh) {
			$this->cache->delete($cacheKey);
		}

		$cached = $this->cache->get($cacheKey);
		if (!$forceRefresh && is_array($cached) && !empty($cached['timeframe'])) {
			return $cached;
		}

		$payload = $timeframe === '1D'
			? $this->fetchIntradayChartPayload()
			: $this->buildHistoricalChartPayload($timeframe);
		$payload = $this->applyRequestedGranularity($payload, $timeframe, $granularity);

		if (!$payload['candles']) {
			$payload = $this->buildFallbackChartPayload($timeframe);
		}

		$chartPayload = $this->finalizeChartPayload($payload, $timeframe, $granularity);
		$this->cache->save($cacheKey, $chartPayload, $this->chartCacheTtl);

		return $chartPayload;
	}

	/**
	 * Pair chart dan quote memakai provider terbaik yang tersedia berurutan.
	 * Alpha tetap dipanggil dulu sebagai sumber utama sesuai requirement.
	 */
	protected function fetchLivePriceFromProviders(): array
	{
		$providers = [
			[$this, 'fetchAlphaVantageLivePrice'],
			[$this, 'fetchTwelveDataLivePrice'],
			[$this, 'fetchFinnhubLivePrice'],
			[$this, 'fetchOandaLivePrice'],
			[$this, 'fetchYahooPublicLivePrice'],
			[$this, 'scrapeYahooFinanceQuotePage'],
		];

		foreach ($providers as $provider) {
			try {
				$result = call_user_func($provider);
				if (!empty($result['current_price'])) {
					return $result;
				}
			} catch (\Throwable $e) {
				continue;
			}
		}

		return [];
	}

	/**
	 * Alpha Vantage dipakai sebagai quote utama karena sudah lebih dulu aktif
	 * pada module monitoring dan tidak butuh dependensi baru.
	 */
	protected function fetchAlphaVantageLivePrice(): array
	{
		$apiKey = trim((string) (env('forex.alphaVantageApiKey') ?: ''));
		if ($apiKey === '') {
			return [];
		}

		$body = $this->requestJson($this->alphaUrl, [
			'function' => 'CURRENCY_EXCHANGE_RATE',
			'from_symbol' => 'GBP',
			'to_symbol' => 'JPY',
			'apikey' => $apiKey,
		]);

		$quote = $body['Realtime Currency Exchange Rate'] ?? [];
		$currentPrice = $this->numericValue($quote['5. Exchange Rate'] ?? null);
		if ($currentPrice <= 0) {
			return [];
		}

		return [
			'current_price' => $currentPrice,
			'quote_time' => $this->normalizeDateTime((string) ($quote['6. Last Refreshed'] ?? '')),
			'provider' => 'Alpha Vantage',
			'source_type' => 'api',
		];
	}

	/**
	 * TwelveData menyediakan quote forex gratis yang ringan untuk melengkapi
	 * live price saat admin menambahkan API key opsional.
	 */
	protected function fetchTwelveDataLivePrice(): array
	{
		$apiKey = trim((string) (env('forex.twelveDataApiKey') ?: ''));
		if ($apiKey === '') {
			return [];
		}

		$body = $this->requestJson($this->twelveDataUrl . '/quote', [
			'symbol' => 'GBP/JPY',
			'apikey' => $apiKey,
		]);

		$currentPrice = $this->numericValue($body['close'] ?? $body['price'] ?? null);
		if ($currentPrice <= 0) {
			return [];
		}

		return [
			'current_price' => $currentPrice,
			'previous_price' => $this->numericValue($body['previous_close'] ?? null),
			'day_open' => $this->numericValue($body['open'] ?? null),
			'day_high' => $this->numericValue($body['high'] ?? null),
			'day_low' => $this->numericValue($body['low'] ?? null),
			'quote_time' => $this->normalizeDateTime((string) ($body['datetime'] ?? $body['timestamp'] ?? '')),
			'provider' => 'TwelveData',
			'source_type' => 'api',
		];
	}

	/**
	 * Finnhub dibaca opsional agar user bisa menambah cadangan quote realtime
	 * tanpa mengubah perilaku default Alpha Vantage yang sudah lebih dulu ada.
	 */
	protected function fetchFinnhubLivePrice(): array
	{
		$apiKey = trim((string) (env('forex.finnhubApiKey') ?: ''));
		if ($apiKey === '') {
			return [];
		}

		$body = $this->requestJson($this->finnhubUrl . '/forex/rates', [
			'base' => 'GBP',
			'token' => $apiKey,
		]);

		$currentPrice = $this->numericValue($body['quote']['JPY'] ?? $body['JPY'] ?? $body['data']['JPY'] ?? null);
		if ($currentPrice <= 0) {
			return [];
		}

		return [
			'current_price' => $currentPrice,
			'provider' => 'Finnhub',
			'source_type' => 'api',
		];
	}

	/**
	 * OANDA dipakai hanya bila kredensial lengkap, sehingga dashboard tetap
	 * ringan dan tidak memaksakan integrasi tambahan pada install dasar.
	 */
	protected function fetchOandaLivePrice(): array
	{
		$accessToken = trim((string) (env('forex.oandaAccessToken') ?: ''));
		$accountId = trim((string) (env('forex.oandaAccountId') ?: ''));
		if ($accessToken === '' || $accountId === '') {
			return [];
		}

		$body = $this->requestJson($this->oandaUrl . '/accounts/' . rawurlencode($accountId) . '/pricing', [
			'instruments' => 'GBP_JPY',
		], [
			'Authorization' => 'Bearer ' . $accessToken,
		]);

		$priceRow = $body['prices'][0] ?? [];
		$bidPrice = $this->numericValue($priceRow['closeoutBid'] ?? $priceRow['bids'][0]['price'] ?? null);
		$askPrice = $this->numericValue($priceRow['closeoutAsk'] ?? $priceRow['asks'][0]['price'] ?? null);
		$currentPrice = $bidPrice > 0 && $askPrice > 0 ? ($bidPrice + $askPrice) / 2 : max($bidPrice, $askPrice);
		if ($currentPrice <= 0) {
			return [];
		}

		return [
			'current_price' => $currentPrice,
			'quote_time' => $this->normalizeDateTime((string) ($priceRow['time'] ?? '')),
			'provider' => 'OANDA',
			'source_type' => 'api',
		];
	}

	/**
	 * Yahoo public chart dipakai sebagai fallback gratis untuk memperoleh quote
	 * plus day high/low tanpa membebani rate limit provider utama.
	 */
	protected function fetchYahooPublicLivePrice(): array
	{
		$result = $this->fetchYahooChartResult('1d', '5m');
		$meta = $result['meta'] ?? [];
		$currentPrice = $this->numericValue($meta['regularMarketPrice'] ?? null);
		if ($currentPrice <= 0) {
			return [];
		}

		return [
			'current_price' => $currentPrice,
			'previous_price' => $this->numericValue($meta['chartPreviousClose'] ?? $meta['previousClose'] ?? null),
			'day_open' => $this->numericValue($meta['chartPreviousClose'] ?? $meta['previousClose'] ?? null),
			'day_high' => $this->numericValue($meta['regularMarketDayHigh'] ?? null),
			'day_low' => $this->numericValue($meta['regularMarketDayLow'] ?? null),
			'quote_time' => $this->normalizeTimestamp($meta['regularMarketTime'] ?? null),
			'provider' => 'Yahoo Finance Public',
			'source_type' => 'public_fallback',
		];
	}

	/**
	 * Scraping halaman Yahoo menjadi lapisan terakhir bila endpoint publiknya
	 * gagal, sehingga dashboard masih punya peluang menampilkan harga.
	 */
	protected function scrapeYahooFinanceQuotePage(): array
	{
		$html = $this->requestText($this->yahooQuoteUrl);
		if ($html === '') {
			return [];
		}

		$patterns = [
			'current_price' => '/"regularMarketPrice"\:\{"raw"\:([0-9.]+)/',
			'previous_price' => '/"regularMarketPreviousClose"\:\{"raw"\:([0-9.]+)/',
			'day_high' => '/"regularMarketDayHigh"\:\{"raw"\:([0-9.]+)/',
			'day_low' => '/"regularMarketDayLow"\:\{"raw"\:([0-9.]+)/',
			'quote_time' => '/"regularMarketTime"\:\{"raw"\:([0-9]+)/',
		];

		$payload = [
			'provider' => 'Yahoo Finance Scrape',
			'source_type' => 'scrape_fallback',
		];

		foreach ($patterns as $key => $pattern) {
			if (!preg_match($pattern, $html, $match)) {
				continue;
			}

			if ($key === 'quote_time') {
				$payload[$key] = $this->normalizeTimestamp($match[1]);
				continue;
			}

			$payload[$key] = $this->numericValue($match[1]);
		}

		return !empty($payload['current_price']) ? $payload : [];
	}

	/**
	 * Intraday chart 1D memakai provider yang paling cocok untuk candle pendek
	 * agar candlestick tetap terasa realtime walau dashboard hanya polling.
	 */
	protected function fetchIntradayChartPayload(): array
	{
		$providers = [
			[$this, 'fetchTwelveDataIntradayCandles'],
			[$this, 'fetchFinnhubIntradayCandles'],
			[$this, 'fetchOandaIntradayCandles'],
			[$this, 'fetchYahooPublicIntradayCandles'],
		];

		foreach ($providers as $provider) {
			try {
				$result = call_user_func($provider);
				if (!empty($result['candles'])) {
					return $result;
				}
			} catch (\Throwable $e) {
				continue;
			}
		}

		return [
			'candles' => [],
			'provider' => 'Database Fallback',
			'source_type' => 'fallback',
			'meta' => [],
		];
	}

	/**
	 * TwelveData time series dipakai bila key tersedia karena endpoint ini
	 * langsung menyediakan OHLC intraday tanpa perlu transformasi rumit.
	 */
	protected function fetchTwelveDataIntradayCandles(): array
	{
		$apiKey = trim((string) (env('forex.twelveDataApiKey') ?: ''));
		if ($apiKey === '') {
			return [];
		}

		$body = $this->requestJson($this->twelveDataUrl . '/time_series', [
			'symbol' => 'GBP/JPY',
			'interval' => '5min',
			'outputsize' => 288,
			'apikey' => $apiKey,
		]);

		$rows = $body['values'] ?? [];
		if (!$rows || !is_array($rows)) {
			return [];
		}

		$candles = [];
		foreach (array_reverse($rows) as $row) {
			$openPrice = $this->numericValue($row['open'] ?? null);
			$highPrice = $this->numericValue($row['high'] ?? null);
			$lowPrice = $this->numericValue($row['low'] ?? null);
			$closePrice = $this->numericValue($row['close'] ?? null);
			$timestamp = strtotime((string) ($row['datetime'] ?? ''));

			if ($openPrice <= 0 || $highPrice <= 0 || $lowPrice <= 0 || $closePrice <= 0 || $timestamp <= 0) {
				continue;
			}

			$candles[] = [
				'timestamp' => $timestamp,
				'label' => date('H:i', $timestamp),
				'open' => $openPrice,
				'high' => $highPrice,
				'low' => $lowPrice,
				'close' => $closePrice,
			];
		}

		return [
			'candles' => $candles,
			'provider' => 'TwelveData',
			'source_type' => 'api',
			'meta' => [
				'current_price' => $candles ? end($candles)['close'] : 0,
			],
		];
	}

	/**
	 * Finnhub intraday dipakai opsional agar dashboard tetap bisa memakai
	 * sumber candle tambahan saat user ingin memperluas redundancy data.
	 */
	protected function fetchFinnhubIntradayCandles(): array
	{
		$apiKey = trim((string) (env('forex.finnhubApiKey') ?: ''));
		if ($apiKey === '') {
			return [];
		}

		$to = time();
		$from = $to - (60 * 60 * 24);
		$body = $this->requestJson($this->finnhubUrl . '/forex/candle', [
			'symbol' => 'OANDA:GBP_JPY',
			'resolution' => '5',
			'from' => $from,
			'to' => $to,
			'token' => $apiKey,
		]);

		if (($body['s'] ?? '') !== 'ok') {
			return [];
		}

		$candles = [];
		foreach (($body['t'] ?? []) as $index => $timestamp) {
			$openPrice = $this->numericValue($body['o'][$index] ?? null);
			$highPrice = $this->numericValue($body['h'][$index] ?? null);
			$lowPrice = $this->numericValue($body['l'][$index] ?? null);
			$closePrice = $this->numericValue($body['c'][$index] ?? null);

			if ($openPrice <= 0 || $highPrice <= 0 || $lowPrice <= 0 || $closePrice <= 0) {
				continue;
			}

			$candles[] = [
				'timestamp' => (int) $timestamp,
				'label' => date('H:i', (int) $timestamp),
				'open' => $openPrice,
				'high' => $highPrice,
				'low' => $lowPrice,
				'close' => $closePrice,
			];
		}

		return [
			'candles' => $candles,
			'provider' => 'Finnhub',
			'source_type' => 'api',
			'meta' => [
				'current_price' => $candles ? end($candles)['close'] : 0,
			],
		];
	}

	/**
	 * OANDA candle M5 dipakai bila tersedia karena cocok untuk chart singkat
	 * tanpa membutuhkan websocket maupun library chart yang berat.
	 */
	protected function fetchOandaIntradayCandles(): array
	{
		$accessToken = trim((string) (env('forex.oandaAccessToken') ?: ''));
		if ($accessToken === '') {
			return [];
		}

		$body = $this->requestJson($this->oandaUrl . '/instruments/GBP_JPY/candles', [
			'price' => 'M',
			'granularity' => 'M5',
			'count' => 288,
		], [
			'Authorization' => 'Bearer ' . $accessToken,
		]);

		$rows = $body['candles'] ?? [];
		if (!$rows || !is_array($rows)) {
			return [];
		}

		$candles = [];
		foreach ($rows as $row) {
			$mid = $row['mid'] ?? [];
			$timestamp = strtotime((string) ($row['time'] ?? ''));
			$openPrice = $this->numericValue($mid['o'] ?? null);
			$highPrice = $this->numericValue($mid['h'] ?? null);
			$lowPrice = $this->numericValue($mid['l'] ?? null);
			$closePrice = $this->numericValue($mid['c'] ?? null);

			if ($timestamp <= 0 || $openPrice <= 0 || $highPrice <= 0 || $lowPrice <= 0 || $closePrice <= 0) {
				continue;
			}

			$candles[] = [
				'timestamp' => $timestamp,
				'label' => date('H:i', $timestamp),
				'open' => $openPrice,
				'high' => $highPrice,
				'low' => $lowPrice,
				'close' => $closePrice,
			];
		}

		return [
			'candles' => $candles,
			'provider' => 'OANDA',
			'source_type' => 'api',
			'meta' => [
				'current_price' => $candles ? end($candles)['close'] : 0,
			],
		];
	}

	/**
	 * Yahoo public chart memberi fallback intraday gratis yang stabil untuk
	 * menjaga chart 1D tetap hidup walau provider API utama sedang terbatas.
	 */
	protected function fetchYahooPublicIntradayCandles(): array
	{
		$result = $this->fetchYahooChartResult('1d', '5m');
		$transformed = $this->transformYahooChartResult($result);

		return [
			'candles' => $transformed['candles'],
			'provider' => 'Yahoo Finance Public',
			'source_type' => 'public_fallback',
			'meta' => $transformed['meta'],
		];
	}

	/**
	 * Timeframe 1W dan 1M dibangun dari histori tersimpan agar angka weekly dan
	 * monthly selalu konsisten dengan data OHLC yang dipakai module monitor.
	 */
	protected function buildHistoricalChartPayload(string $timeframe): array
	{
		$referenceDate = $this->resolveReferenceDate();
		$reference = new \DateTimeImmutable($referenceDate);

		if ($timeframe === '1W') {
			$dateFrom = $reference->modify('monday this week')->format('Y-m-d');
			$dateTo = $reference->modify('sunday this week')->format('Y-m-d');
			$rows = $this->model->getDailyCandlesByDateRange($dateFrom, $dateTo);
			if (!$rows) {
				$rows = $this->model->getRecentDailyCandles(7, $referenceDate);
			}
		} else {
			$dateFrom = $reference->modify('first day of this month')->format('Y-m-d');
			$dateTo = $reference->modify('last day of this month')->format('Y-m-d');
			$rows = $this->model->getDailyCandlesByDateRange($dateFrom, $dateTo);
			if (!$rows) {
				$rows = $this->model->getRecentDailyCandles(24, $referenceDate);
			}
		}

		return [
			'candles' => $this->mapDailyRowsToCandles($rows),
			'provider' => 'Database Historical',
			'source_type' => 'database',
			'meta' => [],
		];
	}

	/**
	 * Granularity dinormalisasi per timeframe agar zoom frontend hanya memilih
	 * resolusi yang memang didukung backend dan tetap ringan untuk dirender.
	 */
	protected function resolveChartGranularity(string $timeframe, ?string $granularity): string
	{
		$granularity = strtoupper(trim((string) $granularity));
		$allowed = [
			'1D' => ['H4', 'H1', 'M15', 'M5'],
			'1W' => ['D1'],
			'1M' => ['W1', 'D1'],
		];
		$default = [
			'1D' => 'H4',
			'1W' => 'D1',
			'1M' => 'W1',
		];

		if ($granularity === '' || !in_array($granularity, $allowed[$timeframe] ?? [], true)) {
			return $default[$timeframe] ?? 'D1';
		}

		return $granularity;
	}

	/**
	 * Candle source digabung per bucket agar load awal chart tetap ringan,
	 * namun backend masih bisa mengirim detail lebih granular saat zoom in.
	 */
	protected function applyRequestedGranularity(array $payload, string $timeframe, string $granularity): array
	{
		$candles = $payload['candles'] ?? [];
		if (!$candles) {
			$payload['meta']['requested_granularity'] = $granularity;
			return $payload;
		}

		if ($timeframe === '1D') {
			$secondsMap = [
				'H4' => 14400,
				'H1' => 3600,
				'M15' => 900,
				'M5' => 300,
			];
			$candles = $this->aggregateCandlesBySeconds($candles, (int) ($secondsMap[$granularity] ?? 3600));
		} elseif ($timeframe === '1M' && $granularity === 'W1') {
			$candles = $this->aggregateCandlesByIsoWeek($candles);
		}

		$payload['candles'] = $candles;
		$payload['meta']['requested_granularity'] = $granularity;
		return $payload;
	}

	/**
	 * Aggregasi intraday dibangun dari source 5 menit agar chart daily bisa
	 * berpindah mulus dari H4 ke H1 lalu M15 tanpa request provider tambahan.
	 */
	protected function aggregateCandlesBySeconds(array $candles, int $bucketSeconds): array
	{
		if ($bucketSeconds <= 300) {
			return $candles;
		}

		$grouped = [];
		foreach ($candles as $candle) {
			$timestamp = (int) ($candle['timestamp'] ?? 0);
			if ($timestamp <= 0) {
				continue;
			}

			$bucketTimestamp = (int) (floor($timestamp / $bucketSeconds) * $bucketSeconds);
			if (empty($grouped[$bucketTimestamp])) {
				$grouped[$bucketTimestamp] = [
					'timestamp' => $bucketTimestamp,
					'label' => date('d M H:i', $bucketTimestamp),
					'open' => (float) ($candle['open'] ?? 0),
					'high' => (float) ($candle['high'] ?? 0),
					'low' => (float) ($candle['low'] ?? 0),
					'close' => (float) ($candle['close'] ?? 0),
				];
				continue;
			}

			$grouped[$bucketTimestamp]['high'] = max($grouped[$bucketTimestamp]['high'], (float) ($candle['high'] ?? 0));
			$grouped[$bucketTimestamp]['low'] = min($grouped[$bucketTimestamp]['low'], (float) ($candle['low'] ?? 0));
			$grouped[$bucketTimestamp]['close'] = (float) ($candle['close'] ?? 0);
		}

		ksort($grouped);
		return array_values($grouped);
	}

	/**
	 * Aggregasi mingguan dari candle harian dipakai untuk load awal monthly
	 * supaya chart bulan penuh tidak terlalu padat sebelum user zoom in.
	 */
	protected function aggregateCandlesByIsoWeek(array $candles): array
	{
		$grouped = [];
		foreach ($candles as $candle) {
			$timestamp = (int) ($candle['timestamp'] ?? 0);
			if ($timestamp <= 0) {
				continue;
			}

			$weekKey = date('o-W', $timestamp);
			if (empty($grouped[$weekKey])) {
				$grouped[$weekKey] = [
					'timestamp' => $timestamp,
					'label' => 'W' . date('W', $timestamp),
					'open' => (float) ($candle['open'] ?? 0),
					'high' => (float) ($candle['high'] ?? 0),
					'low' => (float) ($candle['low'] ?? 0),
					'close' => (float) ($candle['close'] ?? 0),
				];
				continue;
			}

			$grouped[$weekKey]['high'] = max($grouped[$weekKey]['high'], (float) ($candle['high'] ?? 0));
			$grouped[$weekKey]['low'] = min($grouped[$weekKey]['low'], (float) ($candle['low'] ?? 0));
			$grouped[$weekKey]['close'] = (float) ($candle['close'] ?? 0);
		}

		return array_values($grouped);
	}

	/**
	 * Fallback chart minimal dibentuk dari candle terakhir agar halaman tetap
	 * render walau provider live dan histori lengkap belum tersedia.
	 */
	protected function buildFallbackChartPayload(string $timeframe): array
	{
		$latestDaily = $this->model->getLatestSnapshot();
		if (!$latestDaily) {
			return [
				'candles' => [],
				'provider' => 'Fallback Empty',
				'source_type' => 'fallback',
				'meta' => [],
			];
		}

		return [
			'candles' => [[
				'timestamp' => strtotime((string) $latestDaily['date']),
				'label' => (string) $latestDaily['date'],
				'open' => (float) $latestDaily['open_price'],
				'high' => (float) $latestDaily['high_price'],
				'low' => (float) $latestDaily['low_price'],
				'close' => (float) $latestDaily['close_price'],
			]],
			'provider' => 'Database Fallback',
			'source_type' => 'fallback',
			'meta' => [
				'current_price' => (float) $latestDaily['close_price'],
				'day_open' => (float) $latestDaily['open_price'],
				'day_high' => (float) $latestDaily['high_price'],
				'day_low' => (float) $latestDaily['low_price'],
			],
		];
	}

	/**
	 * Struktur chart difinalkan di backend agar frontend hanya fokus render
	 * tanpa menghitung ulang SMA, Bollinger, dan viewport pada polling.
	 */
	protected function finalizeChartPayload(array $payload, string $timeframe, string $granularity): array
	{
		$candles = $payload['candles'] ?? [];
		$indicatorMeta = $this->getIndicatorPeriods($timeframe, count($candles));
		$indicatorContext = $this->buildCanonicalIndicatorContext($timeframe, $candles);
		$indicatorSeries = $this->buildBollingerSeriesCollection($candles, $indicatorContext, $indicatorMeta['bollinger']);
		$axisViewport = $this->buildChartViewport($candles);
		$series = array_merge([
			'candlestick' => $this->buildCandlestickSeries($candles),
			'sma_12' => $this->buildMappedMovingAverageSeries($candles, $indicatorContext, $indicatorMeta['ma_period']),
			'rsi' => $this->buildRsiSeries($candles, $indicatorMeta['rsi_period']),
		], $indicatorSeries);

		return [
			'timeframe' => $timeframe,
			'provider' => (string) ($payload['provider'] ?? 'unknown'),
			'source_type' => (string) ($payload['source_type'] ?? 'cache'),
			'candles' => $candles,
			'series' => $series,
			'meta' => $this->buildChartMeta($candles, $payload['meta'] ?? [], $indicatorMeta, $axisViewport, $granularity),
			'indicators' => $indicatorMeta,
		];
	}

	/**
	 * Query warmup harian dijalankan terbatas agar weekly dan monthly high-low
	 * cukup akurat tanpa memanggil FX_DAILY berulang pada setiap request.
	 */
	protected function ensureHistoricalDataCoverage(bool $forceRefresh = false): void
	{
		$cacheKey = 'forex_gbpjpy_history_warmup_' . date('Ymd');
		if ($forceRefresh) {
			$this->cache->delete($cacheKey);
		}

		if (!$forceRefresh && $this->cache->get($cacheKey)) {
			return;
		}

		$referenceDate = $this->resolveReferenceDate();
		$reference = new \DateTimeImmutable($referenceDate);
		$weekRows = $this->model->getDailyCandlesByDateRange(
			$reference->modify('monday this week')->format('Y-m-d'),
			$reference->format('Y-m-d')
		);
		$monthRows = $this->model->getDailyCandlesByDateRange(
			$reference->modify('first day of this month')->format('Y-m-d'),
			$reference->format('Y-m-d')
		);

		// Bila histori minggu dan bulan aktif sudah cukup, warmup tidak perlu
		// mengulang loop sinkronisasi supaya request dashboard tetap ringan.
		if (count($weekRows) >= 1 && count($monthRows) >= 5 && !$forceRefresh) {
			$this->cache->save($cacheKey, 1, $this->historyWarmCacheTtl);
			return;
		}

		for ($index = 40; $index >= 0; $index--) {
			$targetDate = date('Y-m-d', strtotime('-' . $index . ' days'));
			$this->dailyService->syncDailyData($targetDate, false);
		}

		$this->cache->save($cacheKey, 1, $this->historyWarmCacheTtl);
	}

	/**
	 * Quote live diselaraskan dengan meta chart agar day open/high/low yang
	 * tidak tersedia di provider tertentu tetap bisa diisi dari intraday feed.
	 */
	protected function harmonizeLivePrice(array $livePrice, array $chartPayload): array
	{
		$meta = $chartPayload['meta'] ?? [];
		if (!$meta) {
			return $livePrice;
		}

		$payload = $livePrice ?: $this->buildFallbackLivePayload([], $this->model->getLatestSnapshot());
		$hasChanges = false;

		foreach ([
			'current_price' => 'current_price',
			'day_open' => 'day_open',
			'day_high' => 'day_high',
			'day_low' => 'day_low',
			'previous_price' => 'previous_price',
		] as $metaKey => $payloadKey) {
			$value = $this->numericValue($meta[$metaKey] ?? null);
			if ($value <= 0) {
				continue;
			}

			if ($this->numericValue($payload[$payloadKey] ?? null) !== $value) {
				$payload[$payloadKey] = $value;
				$hasChanges = true;
			}
		}

		if (!empty($meta['last_timestamp'])) {
			$quoteTime = $this->normalizeTimestamp($meta['last_timestamp']);
			if ($quoteTime !== '' && (($payload['quote_time'] ?? '') !== $quoteTime)) {
				$payload['quote_time'] = $quoteTime;
				$hasChanges = true;
			}
		}

		if (!$hasChanges) {
			return $payload;
		}

		$payload = $this->normalizeLivePayload($payload, $this->model->getLivePriceRow(), $this->model->getLatestSnapshot());
		return $this->model->upsertLivePrice($payload);
	}

	/**
	 * Angka perubahan dihitung terpusat agar semua provider menghasilkan format
	 * live card yang seragam untuk current, delta, dan persen perubahan.
	 */
	protected function normalizeLivePayload(array $providerPayload, array $existingLive = [], array $latestDaily = []): array
	{
		$currentPrice = $this->numericValue($providerPayload['current_price'] ?? null);
		$previousPrice = $this->numericValue($providerPayload['previous_price'] ?? null);

		if ($previousPrice <= 0) {
			$previousPrice = $this->numericValue($existingLive['current_price'] ?? null);
		}

		if ($previousPrice <= 0) {
			$previousPrice = $this->numericValue($latestDaily['close_price'] ?? null);
		}

		$dayOpen = $this->numericValue($providerPayload['day_open'] ?? null);
		$dayHigh = $this->numericValue($providerPayload['day_high'] ?? null);
		$dayLow = $this->numericValue($providerPayload['day_low'] ?? null);

		if ($dayOpen <= 0) {
			$dayOpen = $this->numericValue($latestDaily['open_price'] ?? null);
		}
		if ($dayHigh <= 0) {
			$dayHigh = $this->numericValue($latestDaily['high_price'] ?? null);
		}
		if ($dayLow <= 0) {
			$dayLow = $this->numericValue($latestDaily['low_price'] ?? null);
		}

		if ($currentPrice > 0 && $dayHigh > 0) {
			$dayHigh = max($dayHigh, $currentPrice);
		}
		if ($currentPrice > 0 && $dayLow > 0) {
			$dayLow = min($dayLow, $currentPrice);
		}

		$changeAmount = $previousPrice > 0 ? ($currentPrice - $previousPrice) : 0;
		$changePercent = $previousPrice > 0 ? (($changeAmount / $previousPrice) * 100) : 0;

		return [
			'current_price' => $currentPrice,
			'previous_price' => $previousPrice > 0 ? $previousPrice : null,
			'change_amount' => $changeAmount,
			'change_percent' => $changePercent,
			'day_open' => $dayOpen > 0 ? $dayOpen : $currentPrice,
			'day_high' => $dayHigh > 0 ? $dayHigh : $currentPrice,
			'day_low' => $dayLow > 0 ? $dayLow : $currentPrice,
			'provider' => (string) ($providerPayload['provider'] ?? 'Database Fallback'),
			'source_type' => (string) ($providerPayload['source_type'] ?? 'fallback'),
			'quote_time' => $this->normalizeDateTime((string) ($providerPayload['quote_time'] ?? '')),
		];
	}

	/**
	 * Bila semua provider gagal, dashboard memakai gabungan live row lama dan
	 * candle harian terakhir agar halaman tidak pernah berhenti total.
	 */
	protected function cacheAndReturnLiveFallback(string $cacheKey, array $existingLive, array $latestDaily): array
	{
		$fallback = $existingLive ?: $this->buildFallbackLivePayload($existingLive, $latestDaily);
		if (!$fallback && $latestDaily) {
			$fallback = $this->model->upsertLivePrice($this->buildFallbackLivePayload($existingLive, $latestDaily));
		}

		if ($fallback) {
			$this->cache->save($cacheKey, $fallback, $this->quoteCacheTtl);
		}

		return $fallback;
	}

	/**
	 * Live fallback dibentuk dari close harian terakhir agar notifikasi dan
	 * kartu harga masih memiliki angka referensi walau bukan quote realtime.
	 */
	protected function buildFallbackLivePayload(array $existingLive, array $latestDaily): array
	{
		if (!$latestDaily && !$existingLive) {
			return [];
		}

		$currentPrice = $this->numericValue($existingLive['current_price'] ?? null);
		if ($currentPrice <= 0) {
			$currentPrice = $this->numericValue($latestDaily['close_price'] ?? null);
		}

		$previousPrice = $this->numericValue($existingLive['previous_price'] ?? null);
		if ($previousPrice <= 0) {
			$previousPrice = $this->numericValue($latestDaily['open_price'] ?? null);
		}

		$changeAmount = $previousPrice > 0 ? ($currentPrice - $previousPrice) : 0;
		$changePercent = $previousPrice > 0 ? (($changeAmount / $previousPrice) * 100) : 0;

		return [
			'current_price' => $currentPrice,
			'previous_price' => $previousPrice > 0 ? $previousPrice : null,
			'change_amount' => $changeAmount,
			'change_percent' => $changePercent,
			'day_open' => $this->numericValue($existingLive['day_open'] ?? $latestDaily['open_price'] ?? null),
			'day_high' => $this->numericValue($existingLive['day_high'] ?? $latestDaily['high_price'] ?? null),
			'day_low' => $this->numericValue($existingLive['day_low'] ?? $latestDaily['low_price'] ?? null),
			'provider' => (string) ($existingLive['provider'] ?? 'Database Fallback'),
			'source_type' => 'database_fallback',
			'quote_time' => $this->normalizeDateTime((string) ($existingLive['quote_time'] ?? (($latestDaily['date'] ?? '') . ' 00:00:00'))),
		];
	}

	/**
	 * Yahoo chart result diambil terpisah agar live quote dan chart intraday
	 * bisa berbagi satu parser yang sama pada fallback publik.
	 */
	protected function fetchYahooChartResult(string $range, string $interval): array
	{
		$body = $this->requestJson($this->yahooChartUrl, [
			'range' => $range,
			'interval' => $interval,
			'includePrePost' => 'false',
		]);

		$result = $body['chart']['result'][0] ?? [];
		if (!$result) {
			throw new \RuntimeException('Yahoo chart result kosong');
		}

		return $result;
	}

	/**
	 * Transform Yahoo chart dipusatkan agar mapping meta dan array OHLC lebih
	 * mudah dipakai ulang pada dashboard card maupun candlestick chart.
	 */
	protected function transformYahooChartResult(array $result): array
	{
		$timestamps = $result['timestamp'] ?? [];
		$quote = $result['indicators']['quote'][0] ?? [];
		$meta = $result['meta'] ?? [];
		$candles = [];

		foreach ($timestamps as $index => $timestamp) {
			$openPrice = $this->numericValue($quote['open'][$index] ?? null);
			$highPrice = $this->numericValue($quote['high'][$index] ?? null);
			$lowPrice = $this->numericValue($quote['low'][$index] ?? null);
			$closePrice = $this->numericValue($quote['close'][$index] ?? null);

			if ($openPrice <= 0 || $highPrice <= 0 || $lowPrice <= 0 || $closePrice <= 0) {
				continue;
			}

			$candles[] = [
				'timestamp' => (int) $timestamp,
				'label' => date('H:i', (int) $timestamp),
				'open' => $openPrice,
				'high' => $highPrice,
				'low' => $lowPrice,
				'close' => $closePrice,
			];
		}

		return [
			'candles' => $candles,
			'meta' => [
				'current_price' => $this->numericValue($meta['regularMarketPrice'] ?? null),
				'previous_price' => $this->numericValue($meta['chartPreviousClose'] ?? $meta['previousClose'] ?? null),
				'day_open' => $this->numericValue($meta['chartPreviousClose'] ?? $meta['previousClose'] ?? null),
				'day_high' => $this->numericValue($meta['regularMarketDayHigh'] ?? null),
				'day_low' => $this->numericValue($meta['regularMarketDayLow'] ?? null),
				'last_timestamp' => (int) ($meta['regularMarketTime'] ?? ($candles ? end($candles)['timestamp'] : 0)),
			],
		];
	}

	/**
	 * Row daily database dibentuk ke format candle generik agar kalkulasi MA,
	 * RSI, dan render chart tidak perlu bercabang berdasarkan sumber data.
	 */
	protected function mapDailyRowsToCandles(array $rows): array
	{
		$candles = [];
		foreach ($rows as $row) {
			$timestamp = strtotime((string) ($row['date'] ?? ''));
			if ($timestamp <= 0) {
				continue;
			}

			$candles[] = [
				'timestamp' => $timestamp,
				'label' => date('d M', $timestamp),
				'open' => (float) ($row['open_price'] ?? 0),
				'high' => (float) ($row['high_price'] ?? 0),
				'low' => (float) ($row['low_price'] ?? 0),
				'close' => (float) ($row['close_price'] ?? 0),
			];
		}

		return $candles;
	}

	/**
	 * Candlestick series dibuat dalam format ApexCharts agar frontend cukup
	 * menerima array siap pakai dan tidak perlu transformasi lagi.
	 */
	protected function buildCandlestickSeries(array $candles): array
	{
		$series = [];
		foreach ($candles as $candle) {
			$series[] = [
				'x' => ((int) $candle['timestamp']) * 1000,
				'y' => [
					(float) $candle['open'],
					(float) $candle['high'],
					(float) $candle['low'],
					(float) $candle['close'],
				],
			];
		}

		return $series;
	}

	/**
	 * Weighted price HLCC/4 dipusatkan agar SMA dan Bollinger memakai sumber
	 * harga yang sama di semua timeframe tanpa duplikasi rumus di banyak tempat.
	 */
	protected function buildWeightedPriceValue(array $candle): float
	{
		$highPrice = (float) ($candle['high'] ?? $candle['high_price'] ?? 0);
		$lowPrice = (float) ($candle['low'] ?? $candle['low_price'] ?? 0);
		$closePrice = (float) ($candle['close'] ?? $candle['close_price'] ?? 0);

		return ($highPrice + $lowPrice + $closePrice + $closePrice) / 4;
	}

	/**
	 * Deret weighted price per candle dipakai untuk SMA 12 dan juga untuk
	 * menjembatani perhitungan Bollinger multi-timeframe secara konsisten.
	 */
	protected function buildWeightedPriceValues(array $candles): array
	{
		$values = [];
		foreach ($candles as $candle) {
			$values[] = $this->buildWeightedPriceValue($candle);
		}

		return $values;
	}

	/**
	 * Moving average sederhana dihitung dari source HLCC/4 agar garis utama
	 * chart mengikuti requirement indikator berbasis weighted price.
	 */
	protected function buildMovingAverageSeries(array $candles, int $period): array
	{
		$series = [];
		$weightedValues = $this->buildWeightedPriceValues($candles);

		foreach ($candles as $index => $candle) {
			if ($index + 1 < $period) {
				continue;
			}

			$window = array_slice($weightedValues, $index - $period + 1, $period);
			$series[] = [
				'x' => ((int) $candle['timestamp']) * 1000,
				'y' => round(array_sum($window) / count($window), 6),
			];
		}

		return $series;
	}

	/**
	 * SMA chart juga dipetakan dari history sintetik agar timeframe Weekly
	 * dan Monthly tetap menampilkan garis MA 12 walau candle visible sedikit.
	 */
	protected function buildMappedMovingAverageSeries(array $candles, array $indicatorContext, int $period): array
	{
		$series = [];
		$canonicalValues = $indicatorContext['canonical_values'] ?? [];
		$visibleIndexMap = $indicatorContext['visible_index_map'] ?? [];
		if (!$candles || !$canonicalValues || !$visibleIndexMap) {
			return $series;
		}

		$smaValues = $this->calculateSimpleMovingAverageValues($canonicalValues, $period);
		foreach ($visibleIndexMap as $visibleIndex => $canonicalIndex) {
			if (!isset($candles[$visibleIndex], $smaValues[$canonicalIndex]) || $smaValues[$canonicalIndex] === null) {
				continue;
			}

			$series[] = [
				'x' => ((int) $candles[$visibleIndex]['timestamp']) * 1000,
				'y' => round((float) $smaValues[$canonicalIndex], 6),
			];
		}

		return $series;
	}

	/**
	 * History sintetik 5-menit dibentuk dari daily OHLC agar period Bollinger
	 * besar tetap dapat dihitung ringan walau chart weekly/monthly memakai 1 bar per hari.
	 */
	protected function buildCanonicalIndicatorContext(string $timeframe, array $candles): array
	{
		$visibleWeightedValues = $this->buildWeightedPriceValues($candles);
		$referenceDate = $candles ? date('Y-m-d', (int) end($candles)['timestamp']) : $this->resolveReferenceDate();
		$historyRows = $this->model->getRecentDailyCandles($this->indicatorHistoryDays, $referenceDate);
		usort($historyRows, static function (array $left, array $right): int {
			return strcmp((string) ($left['date'] ?? ''), (string) ($right['date'] ?? ''));
		});

		$canonicalValues = [];
		$visibleIndexMap = [];
		$visibleDateMap = [];
		foreach ($candles as $index => $candle) {
			$visibleDateMap[date('Y-m-d', (int) $candle['timestamp'])] = [
				'index' => $index,
				'value' => (float) ($visibleWeightedValues[$index] ?? 0),
			];
		}

		if ($timeframe === '1D') {
			$currentDate = $candles ? date('Y-m-d', (int) end($candles)['timestamp']) : $referenceDate;
			foreach ($historyRows as $row) {
				$rowDate = (string) ($row['date'] ?? '');
				if ($rowDate === '' || $rowDate >= $currentDate) {
					continue;
				}

				$rowWeighted = $this->buildWeightedPriceValue($row);
				for ($repeat = 0; $repeat < 288; $repeat++) {
					$canonicalValues[] = $rowWeighted;
				}
			}

			foreach ($candles as $index => $candle) {
				$canonicalValues[] = (float) ($visibleWeightedValues[$index] ?? 0);
				$visibleIndexMap[$index] = count($canonicalValues) - 1;
			}
		} else {
			$resolvedDates = [];
			foreach ($historyRows as $row) {
				$rowDate = (string) ($row['date'] ?? '');
				if ($rowDate === '') {
					continue;
				}

				$rowWeighted = isset($visibleDateMap[$rowDate])
					? (float) $visibleDateMap[$rowDate]['value']
					: $this->buildWeightedPriceValue($row);
				for ($repeat = 0; $repeat < 288; $repeat++) {
					$canonicalValues[] = $rowWeighted;
				}

				if (isset($visibleDateMap[$rowDate])) {
					$visibleIndexMap[(int) $visibleDateMap[$rowDate]['index']] = count($canonicalValues) - 1;
					$resolvedDates[$rowDate] = true;
				}
			}

			foreach ($visibleDateMap as $dateKey => $item) {
				if (isset($resolvedDates[$dateKey])) {
					continue;
				}

				for ($repeat = 0; $repeat < 288; $repeat++) {
					$canonicalValues[] = (float) $item['value'];
				}
				$visibleIndexMap[(int) $item['index']] = count($canonicalValues) - 1;
			}
		}

		ksort($visibleIndexMap);
		return [
			'canonical_values' => $canonicalValues,
			'visible_index_map' => $visibleIndexMap,
			'visible_weighted_values' => $visibleWeightedValues,
		];
	}

	/**
	 * Nilai SMA umum dipisahkan ke helper agar bisa dipakai ulang oleh series
	 * weighted price maupun perhitungan band yang butuh rolling mean.
	 */
	protected function calculateSimpleMovingAverageValues(array $values, int $period): array
	{
		$result = array_fill(0, count($values), null);
		if ($period < 2 || count($values) < $period) {
			return $result;
		}

		for ($index = $period - 1; $index < count($values); $index++) {
			$window = array_slice($values, $index - $period + 1, $period);
			$result[$index] = array_sum($window) / count($window);
		}

		return $result;
	}

	/**
	 * Bollinger dihitung dari weighted price sintetik agar semua set H4, Daily,
	 * Weekly, dan Monthly tetap tampil walau timeframe chart berubah.
	 */
	protected function calculateBollingerValues(array $values, int $period, float $stdDev): array
	{
		$result = array_fill(0, count($values), null);
		if ($period < 2 || count($values) < $period) {
			return $result;
		}

		for ($index = $period - 1; $index < count($values); $index++) {
			$window = array_slice($values, $index - $period + 1, $period);
			$middle = array_sum($window) / count($window);
			$variance = 0.0;
			foreach ($window as $windowValue) {
				$variance += pow($windowValue - $middle, 2);
			}

			$standardDeviation = sqrt($variance / count($window));
			$result[$index] = [
				'middle' => $middle,
				'upper' => $middle + ($standardDeviation * $stdDev),
				'lower' => $middle - ($standardDeviation * $stdDev),
			];
		}

		return $result;
	}

	/**
	 * Koleksi band dibangun per-set agar frontend bisa menyalakan atau mematikan
	 * satu kelompok Bollinger tanpa menghitung ulang data indikator.
	 */
	protected function buildBollingerSeriesCollection(array $candles, array $indicatorContext, array $bandConfigs): array
	{
		$series = [];
		$canonicalValues = $indicatorContext['canonical_values'] ?? [];
		$visibleIndexMap = $indicatorContext['visible_index_map'] ?? [];
		if (!$candles || !$canonicalValues || !$visibleIndexMap) {
			return $series;
		}

		foreach ($bandConfigs as $bandConfig) {
			$bandKey = (string) ($bandConfig['key'] ?? '');
			if ($bandKey === '') {
				continue;
			}

			$bandValues = $this->calculateBollingerValues(
				$canonicalValues,
				(int) ($bandConfig['period'] ?? 20),
				(float) ($bandConfig['stddev'] ?? 2)
			);
			$upperSeries = [];
			$lowerSeries = [];
			foreach ($visibleIndexMap as $visibleIndex => $canonicalIndex) {
				if (!isset($candles[$visibleIndex], $bandValues[$canonicalIndex]) || $bandValues[$canonicalIndex] === null) {
					continue;
				}

				$timestamp = ((int) $candles[$visibleIndex]['timestamp']) * 1000;
				$upperSeries[] = [
					'x' => $timestamp,
					'y' => round((float) $bandValues[$canonicalIndex]['upper'], 6),
				];
				$lowerSeries[] = [
					'x' => $timestamp,
					'y' => round((float) $bandValues[$canonicalIndex]['lower'], 6),
				];
			}

			$series[$bandKey . '_upper'] = $upperSeries;
			$series[$bandKey . '_lower'] = $lowerSeries;
		}

		return $series;
	}

	/**
	 * RSI ringan dihitung dengan rumus klasik berbasis close agar indikator
	 * tetap informatif tanpa menambah library analisis teknikal eksternal.
	 */
	protected function buildRsiSeries(array $candles, int $period): array
	{
		$series = [];
		$closes = array_column($candles, 'close');
		$rsiValues = $this->calculateRsiValues($closes, $period);

		foreach ($candles as $index => $candle) {
			if (!isset($rsiValues[$index]) || $rsiValues[$index] === null) {
				continue;
			}

			$series[] = [
				'x' => ((int) $candle['timestamp']) * 1000,
				'y' => round((float) $rsiValues[$index], 4),
			];
		}

		return $series;
	}

	/**
	 * Konfigurasi indikator dipusatkan agar label, warna, dan toggle Bollinger
	 * konsisten antara payload backend, legend, dan style chart frontend.
	 */
	protected function getIndicatorPeriods(string $timeframe, int $totalCandles): array
	{
		$bollinger = [
			[
				'key' => 'bb_h4',
				'label' => 'H4 Bollinger 48 x 2',
				'period' => 48,
				'stddev' => 2.0,
				'color' => '#4ea3ff',
				'enabled' => true,
			],
			[
				'key' => 'bb_daily',
				'label' => 'Daily Bollinger 288 x 3',
				'period' => 288,
				'stddev' => 3.0,
				'color' => '#22c55e',
				'enabled' => true,
			],
			[
				'key' => 'bb_weekly',
				'label' => 'Weekly Bollinger 1440 x 2',
				'period' => 1440,
				'stddev' => 2.0,
				'color' => '#f59e0b',
				'enabled' => true,
			],
			[
				'key' => 'bb_monthly',
				'label' => 'Monthly Bollinger 5760 x 2',
				'period' => 5760,
				'stddev' => 2.0,
				'color' => '#a855f7',
				'enabled' => true,
			],
		];
		$toggles = [[
			'key' => 'sma_12',
			'label' => 'SMA 12 (HLCC/4)',
			'color' => '#f8fafc',
			'enabled' => true,
			'series_keys' => ['sma_12'],
		]];
		foreach ($bollinger as $bandConfig) {
			$toggles[] = [
				'key' => $bandConfig['key'],
				'label' => $bandConfig['label'],
				'color' => $bandConfig['color'],
				'enabled' => (bool) $bandConfig['enabled'],
				'series_keys' => [$bandConfig['key'] . '_upper', $bandConfig['key'] . '_lower'],
			];
		}

		return [
			'source' => 'HLCC/4',
			'chart_timeframe' => $timeframe,
			'ma_period' => 12,
			'rsi_period' => max(2, min(14, max(2, $totalCandles - 1))),
			'bollinger' => $bollinger,
			'toggles' => $toggles,
		];
	}

	/**
	 * Viewport chart dibuat langsung dari high-low candle visible agar sumbu Y
	 * benar-benar auto-fit tanpa ruang kosong di atas maupun bawah harga.
	 */
	protected function buildChartViewport(array $candles): array
	{
		$firstCandle = $candles ? reset($candles) : [];
		$lastCandle = $candles ? end($candles) : [];

		return [
			'x_min' => !empty($firstCandle['timestamp']) ? ((int) $firstCandle['timestamp']) * 1000 : null,
			'x_max' => !empty($lastCandle['timestamp']) ? ((int) $lastCandle['timestamp']) * 1000 : null,
			'y_min' => $candles ? min(array_column($candles, 'low')) : 0.0,
			'y_max' => $candles ? max(array_column($candles, 'high')) : 0.0,
		];
	}

	/**
	 * Label granularity dipisahkan agar badge frontend bisa menjelaskan resolusi
	 * aktif tanpa menebak singkatan teknikal dari payload chart backend.
	 */
	protected function buildGranularityLabel(string $granularity): string
	{
		$labelMap = [
			'H4' => '4 Hour',
			'H1' => '1 Hour',
			'M15' => '15 Minute',
			'M5' => '5 Minute',
			'D1' => 'Daily',
			'W1' => 'Weekly',
		];

		return $labelMap[$granularity] ?? $granularity;
	}

	/**
	 * Meta chart disiapkan agar card live, legend, dan auto-fit axis membaca
	 * satu payload yang sama tanpa perhitungan tambahan di browser.
	 */
	protected function buildChartMeta(array $candles, array $meta, array $indicatorMeta, array $axisViewport, string $granularity): array
	{
		$lastCandle = $candles ? end($candles) : [];
		$closes = array_column($candles, 'close');
		$weightedValues = $this->buildWeightedPriceValues($candles);
		$rsiValues = $this->calculateRsiValues($closes, $indicatorMeta['rsi_period']);
		$smaValues = $this->calculateSimpleMovingAverageValues($weightedValues, $indicatorMeta['ma_period']);
		$lastRsi = null;
		foreach (array_reverse($rsiValues, true) as $value) {
			if ($value !== null) {
				$lastRsi = (float) $value;
				break;
			}
		}
		$lastSma = null;
		foreach (array_reverse($smaValues, true) as $value) {
			if ($value !== null) {
				$lastSma = (float) $value;
				break;
			}
		}

		return [
			'current_price' => $this->numericValue($meta['current_price'] ?? ($lastCandle['close'] ?? null)),
			'previous_price' => $this->numericValue($meta['previous_price'] ?? null),
			'day_open' => $this->numericValue($meta['day_open'] ?? ($candles ? reset($candles)['open'] : null)),
			'day_high' => $this->numericValue($meta['day_high'] ?? ($candles ? max(array_column($candles, 'high')) : null)),
			'day_low' => $this->numericValue($meta['day_low'] ?? ($candles ? min(array_column($candles, 'low')) : null)),
			'last_timestamp' => (int) ($meta['last_timestamp'] ?? ($lastCandle['timestamp'] ?? 0)),
			'last_rsi' => $lastRsi,
			'last_sma' => $lastSma,
			'last_weighted_price' => $weightedValues ? (float) end($weightedValues) : null,
			'total_points' => count($candles),
			'axis' => $axisViewport,
			'granularity' => $granularity,
			'granularity_label' => $this->buildGranularityLabel($granularity),
		];
	}

	/**
	 * Kalkulasi RSI dibuat manual agar hasil repeatable dan tidak bergantung
	 * pada package analisis teknikal yang tidak dibutuhkan module ini.
	 */
	protected function calculateRsiValues(array $closes, int $period): array
	{
		$total = count($closes);
		if ($total <= 1 || $period < 2) {
			return [];
		}

		$rsi = array_fill(0, $total, null);
		$gains = [];
		$losses = [];

		for ($index = 1; $index < $total; $index++) {
			$change = (float) $closes[$index] - (float) $closes[$index - 1];
			$gains[$index] = max(0, $change);
			$losses[$index] = max(0, -$change);
		}

		for ($index = $period; $index < $total; $index++) {
			$gainWindow = array_slice($gains, $index - $period + 1, $period);
			$lossWindow = array_slice($losses, $index - $period + 1, $period);
			$averageGain = $gainWindow ? array_sum($gainWindow) / count($gainWindow) : 0;
			$averageLoss = $lossWindow ? array_sum($lossWindow) / count($lossWindow) : 0;

			if ($averageLoss == 0.0) {
				$rsi[$index] = 100.0;
				continue;
			}

			$relativeStrength = $averageGain / $averageLoss;
			$rsi[$index] = 100 - (100 / (1 + $relativeStrength));
		}

		return $rsi;
	}

	/**
	 * Helper request JSON dipusatkan agar timeout, header, dan validasi note
	 * rate limit provider selalu ditangani konsisten di semua pemanggilan.
	 */
	protected function requestJson(string $url, array $query = [], array $headers = []): array
	{
		$response = \Config\Services::curlrequest([
			'timeout' => 20,
			'http_errors' => false,
			'headers' => array_merge([
				'Accept' => 'application/json',
				'User-Agent' => 'Newsoft Baseapp Forex Dashboard',
			], $headers),
		])->get($url, ['query' => $query]);

		$body = json_decode((string) $response->getBody(), true);
		if (!is_array($body)) {
			throw new \RuntimeException('Response provider tidak valid');
		}

		if (!empty($body['status']) && $body['status'] === 'error') {
			throw new \RuntimeException((string) ($body['message'] ?? 'Provider mengembalikan status error'));
		}

		if (!empty($body['code']) && !empty($body['message']) && !is_array($body['message'])) {
			throw new \RuntimeException((string) $body['message']);
		}

		if (!empty($body['error'])) {
			throw new \RuntimeException(is_string($body['error']) ? $body['error'] : 'Provider mengembalikan error');
		}

		if (!empty($body['Note'])) {
			throw new \RuntimeException((string) $body['Note']);
		}

		if (!empty($body['Information'])) {
			throw new \RuntimeException((string) $body['Information']);
		}

		return $body;
	}

	/**
	 * Request text dipakai khusus fallback scraping agar parser HTML tidak
	 * menambah logika curl terpisah di setiap method fallback publik.
	 */
	protected function requestText(string $url, array $query = [], array $headers = []): string
	{
		$response = \Config\Services::curlrequest([
			'timeout' => 20,
			'http_errors' => false,
			'headers' => array_merge([
				'Accept' => 'text/html,application/xhtml+xml',
				'User-Agent' => 'Newsoft Baseapp Forex Dashboard',
			], $headers),
		])->get($url, ['query' => $query]);

		return (string) $response->getBody();
	}

	/**
	 * Timeframe dijaga hanya pada tiga opsi yang didukung agar endpoint AJAX
	 * tetap deterministic dan tidak membuka variasi parameter liar.
	 */
	protected function normalizeTimeframe(string $timeframe): string
	{
		$timeframe = strtoupper(trim($timeframe));
		if (!in_array($timeframe, ['1D', '1W', '1M'], true)) {
			return '1D';
		}

		return $timeframe;
	}

	/**
	 * Tanggal referensi chart historis mengikuti quote live bila ada, lalu
	 * fallback ke candle harian terakhir yang tersimpan di database.
	 */
	protected function resolveReferenceDate(): string
	{
		$livePrice = $this->model->getLivePriceRow();
		$quoteTime = trim((string) ($livePrice['quote_time'] ?? ''));
		if ($quoteTime !== '') {
			return date('Y-m-d', strtotime($quoteTime));
		}

		$latestDaily = $this->model->getLatestSnapshot();
		if (!empty($latestDaily['date'])) {
			return (string) $latestDaily['date'];
		}

		return date('Y-m-d');
	}

	/**
	 * Nilai numerik dibulatkan halus agar semua provider yang mengirim string
	 * atau integer tetap masuk ke storage dengan format decimal yang konsisten.
	 */
	protected function numericValue($value): float
	{
		if ($value === null || $value === '') {
			return 0.0;
		}

		return is_numeric($value) ? (float) $value : 0.0;
	}

	/**
	 * Datetime provider dinormalisasi ke format database lokal supaya quote
	 * time bisa langsung dipakai di UI tanpa transformasi tambahan.
	 */
	protected function normalizeDateTime(string $value): string
	{
		$value = trim($value);
		if ($value === '') {
			return date('Y-m-d H:i:s');
		}

		$timestamp = strtotime($value);
		return $timestamp ? date('Y-m-d H:i:s', $timestamp) : date('Y-m-d H:i:s');
	}

	/**
	 * Timestamp UNIX diubah ke string datetime standar agar hasil dari Yahoo
	 * maupun Finnhub tetap bisa dipakai oleh tabel live price lokal.
	 */
	protected function normalizeTimestamp($value): string
	{
		$timestamp = is_numeric($value) ? (int) $value : 0;
		if ($timestamp <= 0) {
			return date('Y-m-d H:i:s');
		}

		return date('Y-m-d H:i:s', $timestamp);
	}
}
