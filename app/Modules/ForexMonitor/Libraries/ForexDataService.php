<?php
/**
 * Service forex menangani fetch OHLC gratis dari Alpha Vantage, cache harian,
 * fallback data terakhir, dan sinkronisasi penyimpanan harga serta analisis.
 */

namespace App\Modules\ForexMonitor\Libraries;

use App\Modules\ForexPrediction\Libraries\ForexPredictionService;
use App\Modules\ForexMonitor\Models\ForexMonitorModel;

class ForexDataService
{
	protected ForexMonitorModel $model;
	protected $cache;
	protected string $apiUrl = 'https://www.alphavantage.co/query';
	protected int $cacheTtl = 21600;
	protected ForexPredictionService $predictionService;

	public function __construct(?ForexMonitorModel $model = null)
	{
		$this->model = $model ?: new ForexMonitorModel();
		$this->cache = cache();
		$this->predictionService = new ForexPredictionService($this->model);
	}

	/**
	 * Sinkronisasi data harian dipakai bersama oleh cron dan manual fetch agar
	 * validasi tanggal, cache, dan fallback tidak tersebar di banyak tempat.
	 */
	public function syncDailyData(?string $requestedDate = null, bool $forceRefresh = false): array
	{
		$targetDate = $this->model->normalizeDate($requestedDate) ?: date('Y-m-d');
		$existing = $this->model->getPriceByDate($targetDate);

		if ($existing && !$forceRefresh) {
			$analysis = $this->ensureAnalysisForPrice($existing);
			$prediction = $this->predictionService->getLatestPrediction($existing['date'], false, 'after-save');
			return [
				'status' => 'ok',
				'message' => 'Data GBP/JPY tanggal ' . $targetDate . ' sudah tersedia di database dan dipakai ulang tanpa request API baru.',
				'price' => $existing,
				'analysis' => $analysis,
				'prediction' => $prediction,
				'requested_date' => $targetDate,
				'fetched_date' => $existing['date'],
				'data_source' => 'database',
			];
		}

		$apiKey = $this->getApiKey();
		if ($apiKey === '') {
			return $this->buildFallbackResult(
				$targetDate,
				'warning',
				'API key Alpha Vantage belum diatur, sehingga module memakai data GBP/JPY terakhir yang tersimpan di database.'
			);
		}

		try {
			$seriesPayload = $this->getDailySeries($apiKey, $forceRefresh);
			$resolvedCandle = $this->resolveCandleForDate($seriesPayload['series'], $targetDate);

			if (!$resolvedCandle) {
				return $this->buildFallbackResult(
					$targetDate,
					'warning',
					'Data candle GBP/JPY untuk tanggal ' . $targetDate . ' belum tersedia dari API, sehingga module menampilkan data terakhir yang tersimpan.'
				);
			}

			$pricePayload = [
				'date' => $resolvedCandle['date'],
				'open_price' => $resolvedCandle['open_price'],
				'high_price' => $resolvedCandle['high_price'],
				'low_price' => $resolvedCandle['low_price'],
				'close_price' => $resolvedCandle['close_price'],
				'source_api' => $seriesPayload['source_api'],
			];

			$db = db_connect();
			$db->transStart();
			$price = $this->model->upsertPrice($pricePayload);
			$analysis = $this->model->upsertAnalysis($this->model->buildAnalysisPayload($pricePayload));
			$db->transComplete();

			if (!$db->transStatus()) {
				return $this->buildFallbackResult(
					$targetDate,
					'warning',
					'Penyimpanan data GBP/JPY gagal diselesaikan, sehingga module memakai data terakhir yang tersedia di database.'
				);
			}

			$message = 'Data GBP/JPY tanggal ' . $price['date'] . ' berhasil disinkronkan dari Alpha Vantage.';
			if ($price['date'] !== $targetDate) {
				$message = 'Data candle untuk tanggal ' . $targetDate . ' belum tersedia, sehingga module menyimpan candle trading terakhir pada tanggal ' . $price['date'] . '.';
			}

			return [
				'status' => 'ok',
				'message' => $message,
				'price' => $price,
				'analysis' => $analysis,
				'prediction' => $this->predictionService->getLatestPrediction($price['date'], true, 'after-save'),
				'requested_date' => $targetDate,
				'fetched_date' => $price['date'],
				'data_source' => $seriesPayload['cache_source'],
			];
		} catch (\Throwable $e) {
			return $this->buildFallbackResult(
				$targetDate,
				'warning',
				'API GBP/JPY gagal diakses: ' . $e->getMessage() . '. Module memakai data terakhir yang tersimpan agar sistem tetap berjalan.'
			);
		}
	}

	/**
	 * API key dibaca dari dua format env agar setup lama dan baru tetap kompatibel.
	 */
	protected function getApiKey(): string
	{
		return trim((string) (env('forex.alphaVantageApiKey') ?: getenv('FOREX_ALPHA_VANTAGE_API_KEY') ?: ''));
	}

	/**
	 * Cache series harian dipakai untuk menekan request berulang ke API gratis
	 * karena module hanya membutuhkan satu pair dan satu interval yang tetap.
	 */
	protected function getDailySeries(string $apiKey, bool $forceRefresh = false): array
	{
		$cacheKey = 'forex_gbpjpy_fx_daily_series';
		if ($forceRefresh) {
			$this->cache->delete($cacheKey);
		}

		$cached = $this->cache->get($cacheKey);
		if (!$forceRefresh && is_array($cached) && !empty($cached['series'])) {
			$cached['cache_source'] = 'cache';
			return $cached;
		}

		$response = \Config\Services::curlrequest([
			'timeout' => 20,
			'headers' => [
				'Accept' => 'application/json',
				'User-Agent' => 'Newsoft Baseapp Forex Monitor',
			],
		])->get($this->apiUrl, [
			'query' => [
				'function' => 'FX_DAILY',
				'from_symbol' => 'GBP',
				'to_symbol' => 'JPY',
				'outputsize' => 'compact',
				'apikey' => $apiKey,
			],
		]);

		$body = json_decode((string) $response->getBody(), true);
		if (!is_array($body)) {
			throw new \RuntimeException('Response API tidak valid');
		}

		if (!empty($body['Error Message'])) {
			throw new \RuntimeException((string) $body['Error Message']);
		}

		if (!empty($body['Note'])) {
			throw new \RuntimeException((string) $body['Note']);
		}

		if (!empty($body['Information'])) {
			throw new \RuntimeException((string) $body['Information']);
		}

		$series = $body['Time Series FX (Daily)'] ?? [];
		if (!is_array($series) || !$series) {
			throw new \RuntimeException('Series FX_DAILY tidak ditemukan pada response API');
		}

		$normalizedSeries = [];
		foreach ($series as $date => $row) {
			$normalizedDate = $this->model->normalizeDate((string) $date);
			if ($normalizedDate === null) {
				continue;
			}

			$normalizedSeries[$normalizedDate] = [
				'1. open' => $row['1. open'] ?? null,
				'2. high' => $row['2. high'] ?? null,
				'3. low' => $row['3. low'] ?? null,
				'4. close' => $row['4. close'] ?? null,
			];
		}

		if (!$normalizedSeries) {
			throw new \RuntimeException('Series FX_DAILY kosong setelah normalisasi data');
		}

		krsort($normalizedSeries);
		$payload = [
			'series' => $normalizedSeries,
			'source_api' => $this->model->getSourceApiLabel(),
			'fetched_at' => date('Y-m-d H:i:s'),
			'cache_source' => 'api',
		];

		$this->cache->save($cacheKey, $payload, $this->cacheTtl);

		return $payload;
	}

	/**
	 * Pemilihan candle dibuat toleran terhadap weekend/libur agar cron tetap
	 * bisa mengambil candle trading terbaru tanpa memaksakan tanggal kosong.
	 */
	protected function resolveCandleForDate(array $series, string $targetDate): array
	{
		if (isset($series[$targetDate])) {
			return $this->normalizeCandleRow($targetDate, $series[$targetDate]);
		}

		foreach ($series as $date => $row) {
			if ($date <= $targetDate) {
				return $this->normalizeCandleRow($date, $row);
			}
		}

		return [];
	}

	/**
	 * Validasi angka OHLC dilakukan sebelum simpan agar data API yang tidak
	 * lengkap tidak ikut masuk ke histori harga module.
	 */
	protected function normalizeCandleRow(string $date, array $row): array
	{
		$openPrice = is_numeric($row['1. open'] ?? null) ? (float) $row['1. open'] : null;
		$highPrice = is_numeric($row['2. high'] ?? null) ? (float) $row['2. high'] : null;
		$lowPrice = is_numeric($row['3. low'] ?? null) ? (float) $row['3. low'] : null;
		$closePrice = is_numeric($row['4. close'] ?? null) ? (float) $row['4. close'] : null;

		if ($openPrice === null || $highPrice === null || $lowPrice === null || $closePrice === null) {
			throw new \RuntimeException('Nilai OHLC dari API tidak lengkap');
		}

		if ($highPrice < $lowPrice) {
			throw new \RuntimeException('Nilai high lebih kecil dari low pada response API');
		}

		return [
			'date' => $date,
			'open_price' => $openPrice,
			'high_price' => $highPrice,
			'low_price' => $lowPrice,
			'close_price' => $closePrice,
		];
	}

	/**
	 * Analisis harian dipastikan ikut tersimpan walau harga sebelumnya sudah
	 * ada, sehingga modul report tidak kehilangan narasi untuk tanggal tertentu.
	 */
	protected function ensureAnalysisForPrice(array $price): array
	{
		$analysis = $this->model->getAnalysisByDate((string) $price['date']);
		if ($analysis) {
			return $analysis;
		}

		return $this->model->upsertAnalysis($this->model->buildAnalysisPayload($price));
	}

	/**
	 * Saat API gagal, hasil fallback dikembalikan seragam agar controller dan
	 * command dapat menampilkan pesan tanpa perlu logika cabang tambahan.
	 */
	protected function buildFallbackResult(string $targetDate, string $status, string $message): array
	{
		$fallback = $this->model->getLatestSnapshot($targetDate);
		if (!$fallback) {
			return [
				'status' => $status,
				'message' => $message . ' Belum ada data fallback yang tersimpan di database.',
				'price' => [],
				'analysis' => [],
				'requested_date' => $targetDate,
				'fetched_date' => null,
				'data_source' => 'fallback_empty',
			];
		}

		return [
			'status' => $status,
			'message' => $message . ' Data fallback yang dipakai berasal dari tanggal ' . $fallback['date'] . '.',
			'price' => [
				'id_forex_price' => $fallback['id_forex_price'] ?? null,
				'pair' => $fallback['pair'] ?? $this->model->getPair(),
				'date' => $fallback['date'] ?? null,
				'open_price' => $fallback['open_price'] ?? null,
				'high_price' => $fallback['high_price'] ?? null,
				'low_price' => $fallback['low_price'] ?? null,
				'close_price' => $fallback['close_price'] ?? null,
				'source_api' => $fallback['source_api'] ?? $this->model->getSourceApiLabel(),
				'created_at' => $fallback['created_at'] ?? null,
			],
			'analysis' => [
				'date' => $fallback['date'] ?? null,
				'pair' => $fallback['pair'] ?? $this->model->getPair(),
				'high_low_range' => $fallback['high_low_range'] ?? null,
				'trend' => $fallback['trend'] ?? null,
				'summary' => $fallback['summary'] ?? null,
				'created_at' => $fallback['analysis_created_at'] ?? null,
			],
			'prediction' => $this->predictionService->getLatestPrediction($fallback['date'] ?? null, false, 'fallback'),
			'requested_date' => $targetDate,
			'fetched_date' => $fallback['date'] ?? null,
			'data_source' => 'database_fallback',
		];
	}
}
