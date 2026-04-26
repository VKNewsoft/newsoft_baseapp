<?php
/**
 * Service signal forex membangun market context dinamis Daily, Weekly, dan
 * Monthly dari OHLC tersimpan untuk rule BUY/SELL/WAIT/BREAKOUT GBP/JPY.
 */

namespace App\Modules\ForexPrediction\Libraries;

use App\Modules\ForexMonitor\Models\ForexMonitorModel;

class ForexSignalService
{
	protected ForexMonitorModel $model;
	protected $cache;
	protected float $bollingerTouchTolerance = 0.05;
	protected array $timeframePriority = [
		'monthly' => 3,
		'weekly' => 2,
		'daily' => 1,
	];

	public function __construct(?ForexMonitorModel $model = null)
	{
		$this->model = $model ?: new ForexMonitorModel();
		$this->cache = cache();
	}

	/**
	 * Payload signal dibangun dari live price, chart, dan context multi-timeframe
	 * agar dashboard serta module alert membaca level dinamis yang sama.
	 */
	public function getSignalPayload(array $livePrice = [], array $chartPayload = [], int $userId = 0): array
	{
		$latestDaily = $this->model->getLatestSnapshot();
		$currentPrice = (float) ($livePrice['current_price'] ?? $latestDaily['close_price'] ?? 0);
		$referenceDate = $this->resolveReferenceDate($livePrice, $latestDaily);
		$candles = $chartPayload['candles'] ?? [];
		$lastCandle = $candles ? end($candles) : [];
		$lastRsi = $this->resolveLatestRsi($chartPayload);
		$bollinger = $this->buildBollingerSnapshot($candles);
		$fibonacci = $this->buildFibonacciSnapshot($livePrice, $latestDaily);
		$marketContext = $this->buildDynamicMarketContext($referenceDate, $livePrice, $latestDaily, $currentPrice);
		$combinedContext = $marketContext['combined'] ?? [];
		$decision = $this->buildTradingDecision($currentPrice, $lastRsi, $bollinger, $lastCandle, $combinedContext, $marketContext);
		$notes = $this->buildImportantNotes($combinedContext, $decision);

		$payload = [
			'current_signal' => $decision['signal'],
			'signal_label' => $decision['signal_label'],
			'signal_color' => $this->resolveSignalColor($decision['signal']),
			'confidence' => $decision['confidence'],
			'reason' => $decision['reason'],
			'reasons' => $decision['reasons'],
			'recommendation' => $decision['recommendation'],
			'buy_zone' => $combinedContext['buy_zone'] ?? $this->emptyZonePayload(),
			'sell_zone' => $combinedContext['sell_zone'] ?? $this->emptyZonePayload(),
			'breakout_level' => (float) ($combinedContext['breakout_up_level'] ?? 0),
			'breakdown_level' => (float) ($combinedContext['breakout_down_level'] ?? 0),
			'current_price' => $currentPrice,
			'indicators' => [
				'rsi' => $lastRsi,
				'bollinger' => $bollinger,
				'fibonacci' => $fibonacci,
			],
			'market_context' => $marketContext,
			'confluence' => [
				'count' => (int) ($combinedContext['confluence_count'] ?? 0),
				'label' => (string) ($combinedContext['confluence_label'] ?? 'Belum ada confluence kuat'),
				'summary' => (string) ($combinedContext['summary'] ?? ''),
			],
			'notes' => $notes,
			'chart_overlays' => $this->buildChartOverlayPayload($chartPayload, $marketContext, $fibonacci),
			'auto_monitor' => $this->resolveAutoMonitorStatus($userId),
		];

		if ($userId > 0) {
			$payload['automation'] = $this->handleAutomationTrigger($userId, $payload);
		} else {
			$payload['automation'] = [
				'enabled' => false,
				'triggered_alerts' => [],
			];
		}

		return $payload;
	}

	/**
	 * Update auto-monitor dipakai module alert agar user bisa menyalakan atau
	 * mematikan automation signal tanpa membuat tabel konfigurasi baru.
	 */
	public function saveAutoMonitorSetting(int $userId, bool $enabled): array
	{
		$result = $this->model->saveSignalSetting($userId, [
			'auto_monitor' => $enabled ? 1 : 0,
		]);

		return [
			'status' => $result ? 'ok' : 'error',
			'message' => $result
				? ($enabled ? 'Auto-monitor signal berhasil diaktifkan' : 'Auto-monitor signal berhasil dinonaktifkan')
				: 'Auto-monitor signal gagal disimpan',
		];
	}

	/**
	 * Market context multi-timeframe dihitung dari histori OHLC tersimpan agar
	 * seluruh support, resistance, dan breakout level selalu dinamis.
	 */
	protected function buildDynamicMarketContext(string $referenceDate, array $livePrice, array $latestDaily, float $currentPrice): array
	{
		$reference = new \DateTimeImmutable($referenceDate);
		$dailyRecentRows = $this->model->getRecentDailyCandles(24, $referenceDate);
		$dailyRows = $this->prepareTimeframeRows($this->model->getDailyCandlesByDateRange($referenceDate, $referenceDate), $referenceDate, $livePrice);
		$weeklyRows = $this->prepareTimeframeRows(
			$this->model->getDailyCandlesByDateRange(
				$reference->modify('monday this week')->format('Y-m-d'),
				$reference->modify('friday this week')->format('Y-m-d')
			),
			$referenceDate,
			$livePrice
		);
		$monthlyRows = $this->prepareTimeframeRows(
			$this->model->getDailyCandlesByDateRange(
				$reference->modify('first day of this month')->format('Y-m-d'),
				$reference->modify('last day of this month')->format('Y-m-d')
			),
			$referenceDate,
			$livePrice
		);

		$completedDailyRows = $this->model->getDailyCandlesByDateRange($referenceDate, $referenceDate);
		if (!$completedDailyRows && $latestDaily) {
			$completedDailyRows = [$latestDaily];
		}

		$daily = $this->buildTimeframeContext(
			'daily',
			$referenceDate,
			$dailyRows ?: $completedDailyRows,
			$completedDailyRows,
			array_slice($dailyRecentRows, -10),
			$currentPrice
		);
		$weekly = $this->buildTimeframeContext(
			'weekly',
			$reference->modify('monday this week')->format('Y-m-d') . ' s/d ' . $reference->modify('friday this week')->format('Y-m-d'),
			$weeklyRows,
			$this->model->getDailyCandlesByDateRange(
				$reference->modify('monday this week')->format('Y-m-d'),
				$reference->modify('friday this week')->format('Y-m-d')
			),
			array_slice($dailyRecentRows, -14),
			$currentPrice
		);
		$monthly = $this->buildTimeframeContext(
			'monthly',
			$reference->format('F Y'),
			$monthlyRows,
			$this->model->getDailyCandlesByDateRange(
				$reference->modify('first day of this month')->format('Y-m-d'),
				$reference->modify('last day of this month')->format('Y-m-d')
			),
			array_slice($dailyRecentRows, -20),
			$currentPrice
		);

		$contexts = [
			'daily' => $daily,
			'weekly' => $weekly,
			'monthly' => $monthly,
		];
		$contexts['combined'] = $this->buildCombinedMarketContext($contexts, $currentPrice);

		return $contexts;
	}

	/**
	 * Baris periodik disiapkan dengan overlay live intraday agar high-low hari
	 * berjalan ikut tercermin pada context minggu dan bulan aktif.
	 */
	protected function prepareTimeframeRows(array $rows, string $referenceDate, array $livePrice): array
	{
		$normalizedRows = [];
		foreach ($rows as $row) {
			$normalizedRows[(string) ($row['date'] ?? '')] = $row;
		}

		$liveHigh = (float) ($livePrice['day_high'] ?? 0);
		$liveLow = (float) ($livePrice['day_low'] ?? 0);
		if ($referenceDate !== '' && ($liveHigh > 0 || $liveLow > 0)) {
			$existing = $normalizedRows[$referenceDate] ?? [];
			$normalizedRows[$referenceDate] = [
				'date' => $referenceDate,
				'open_price' => (float) ($livePrice['day_open'] ?? ($existing['open_price'] ?? $livePrice['current_price'] ?? 0)),
				'high_price' => $liveHigh > 0
					? max($liveHigh, (float) ($existing['high_price'] ?? 0))
					: (float) ($existing['high_price'] ?? 0),
				'low_price' => $liveLow > 0
					? ($existing ? min($liveLow, (float) ($existing['low_price'] ?? $liveLow)) : $liveLow)
					: (float) ($existing['low_price'] ?? 0),
				'close_price' => (float) ($livePrice['current_price'] ?? ($existing['close_price'] ?? 0)),
			];
		}

		ksort($normalizedRows);
		return array_values($normalizedRows);
	}

	/**
	 * Satu block timeframe memuat high-low, support, resistance, buffer, dan
	 * status breakout agar UI bisa menampilkannya per card secara terpisah.
	 */
	protected function buildTimeframeContext(
		string $timeframeKey,
		string $dateLabel,
		array $displayRows,
		array $completedRows,
		array $volatilityRows,
		float $currentPrice
	): array {
		$labelMap = [
			'daily' => 'Daily',
			'weekly' => 'Weekly',
			'monthly' => 'Monthly',
		];
		$displayRows = array_values(array_filter($displayRows));
		$completedRows = array_values(array_filter($completedRows));

		$highPrice = $this->maxRowValue($displayRows, 'high_price');
		$lowPrice = $this->minRowValue($displayRows, 'low_price');
		$range = max(0.0001, $highPrice - $lowPrice);
		$atr = $this->calculateAtr($volatilityRows);
		$buffer = $this->resolveZoneBuffer($range, $atr, $timeframeKey);
		$referenceHigh = $this->maxRowValue($completedRows ?: $displayRows, 'high_price');
		$referenceLow = $this->minRowValue($completedRows ?: $displayRows, 'low_price');
		$resistanceZone = $this->buildZonePayload(max(0, $highPrice - $buffer), $highPrice + $buffer);
		$supportZone = $this->buildZonePayload(max(0, $lowPrice - $buffer), $lowPrice + $buffer);
		$status = $this->resolveTimeframeStatus($currentPrice, $supportZone, $resistanceZone, $referenceHigh, $referenceLow);

		return [
			'key' => $timeframeKey,
			'label' => $labelMap[$timeframeKey] ?? ucfirst($timeframeKey),
			'date_label' => $dateLabel,
			'high_price' => $highPrice,
			'low_price' => $lowPrice,
			'range' => $range,
			'atr' => $atr,
			'buffer' => $buffer,
			'resistance_zone' => $resistanceZone,
			'support_zone' => $supportZone,
			'breakout_up_level' => $referenceHigh,
			'breakout_down_level' => $referenceLow,
			'status' => $status['status'],
			'status_label' => $status['label'],
			'status_color' => $status['color'],
			'bias' => $status['bias'],
		];
	}

	/**
	 * Context gabungan memilih level dominan dengan prioritas Monthly, Weekly,
	 * lalu Daily, sekaligus menghitung confluence antar timeframe.
	 */
	protected function buildCombinedMarketContext(array $contexts, float $currentPrice): array
	{
		$priorityOrdered = $this->sortContextsByPriority($contexts);
		$buyContext = $this->findContextByStatuses($priorityOrdered, ['near_support', 'bullish_breakout']);
		$sellContext = $this->findContextByStatuses($priorityOrdered, ['near_resistance', 'bearish_breakout']);
		$bullishBreakoutContext = $this->findContextByStatuses($priorityOrdered, ['bullish_breakout']);
		$bearishBreakoutContext = $this->findContextByStatuses($priorityOrdered, ['bearish_breakout']);
		$confluenceCount = $this->calculateConfluenceCount($contexts);
		$dominantContext = $this->resolveDominantContext($bullishBreakoutContext, $bearishBreakoutContext, $buyContext, $sellContext);

		$buyZone = $buyContext ? $buyContext['support_zone'] : ($dominantContext['support_zone'] ?? $this->emptyZonePayload());
		$sellZone = $sellContext ? $sellContext['resistance_zone'] : ($dominantContext['resistance_zone'] ?? $this->emptyZonePayload());

		if ($buyContext) {
			$buyZone['timeframe'] = $buyContext['label'];
		}
		if ($sellContext) {
			$sellZone['timeframe'] = $sellContext['label'];
		}

		$summary = $this->buildCombinedSummary($dominantContext, $confluenceCount, $currentPrice);
		$confluenceLabel = $confluenceCount >= 2
			? 'Confluence kuat pada ' . $confluenceCount . ' timeframe'
			: 'Belum ada confluence kuat antar timeframe';

		return [
			'priority_timeframe' => $dominantContext['key'] ?? 'monthly',
			'dominant_status' => $dominantContext['status'] ?? 'inside_range',
			'dominant_label' => $dominantContext['status_label'] ?? 'Inside Range',
			'confluence_count' => $confluenceCount,
			'confluence_label' => $confluenceLabel,
			'buy_zone' => $buyZone,
			'sell_zone' => $sellZone,
			'breakout_up_level' => (float) ($bullishBreakoutContext['breakout_up_level'] ?? $dominantContext['breakout_up_level'] ?? 0),
			'breakout_down_level' => (float) ($bearishBreakoutContext['breakout_down_level'] ?? $dominantContext['breakout_down_level'] ?? 0),
			'summary' => $summary,
		];
	}

	/**
	 * Keputusan final memakai market context dinamis sebagai filter utama lalu
	 * dikonfirmasi RSI, Bollinger, dan Fibonacci secara deterministic.
	 */
	protected function buildTradingDecision(
		float $currentPrice,
		float $lastRsi,
		array $bollinger,
		array $lastCandle,
		array $combinedContext,
		array $marketContext
	): array {
		$reasons = [];
		$signal = 'WAIT';
		$signalLabel = 'WAIT';
		$confidence = 'low';
		$recommendation = 'Menunggu harga mendekati support/resistance dinamis dengan konfirmasi indikator.';
		$dominantStatus = (string) ($combinedContext['dominant_status'] ?? 'inside_range');
		$touchesLowerBand = $this->touchesLowerBand($currentPrice, $lastCandle, $bollinger);
		$touchesUpperBand = $this->touchesUpperBand($currentPrice, $lastCandle, $bollinger);
		$confluenceCount = (int) ($combinedContext['confluence_count'] ?? 0);

		if ($dominantStatus === 'bullish_breakout') {
			$signal = 'BREAKOUT';
			$signalLabel = 'BUY BREAKOUT';
			$confidence = $confluenceCount >= 2 ? 'high' : 'medium';
			$recommendation = 'Harga menembus resistance timeframe dominan, fokus pada continuation buy saat breakout bertahan.';
			$reasons[] = 'Breakout bullish di atas high ' . ucfirst((string) ($combinedContext['priority_timeframe'] ?? 'monthly')) . '.';
		} elseif ($dominantStatus === 'bearish_breakout') {
			$signal = 'SELL';
			$signalLabel = 'SELL BREAKDOWN';
			$confidence = $confluenceCount >= 2 ? 'high' : 'medium';
			$recommendation = 'Harga menembus low timeframe dominan, risiko penurunan lanjutan meningkat.';
			$reasons[] = 'Breakout bearish di bawah low ' . ucfirst((string) ($combinedContext['priority_timeframe'] ?? 'monthly')) . '.';
		} elseif ($dominantStatus === 'near_support' && $lastRsi > 0 && $lastRsi < 30 && $touchesLowerBand) {
			$signal = 'BUY';
			$signalLabel = 'STRONG BUY ZONE';
			$confidence = $confluenceCount >= 2 ? 'high' : 'medium';
			$recommendation = 'Harga berada di support timeframe dominan dan oversold, cocok untuk menunggu entry buy bertahap.';
			$reasons[] = 'Harga masuk support zone dinamis timeframe dominan.';
			$reasons[] = 'RSI di bawah 30 menandakan oversold.';
			$reasons[] = 'Harga menyentuh lower Bollinger Band.';
		} elseif ($dominantStatus === 'near_resistance' && $lastRsi > 70 && $touchesUpperBand) {
			$signal = 'SELL';
			$signalLabel = 'SELL / TAKE PROFIT';
			$confidence = $confluenceCount >= 2 ? 'high' : 'medium';
			$recommendation = 'Harga berada di resistance timeframe dominan dan overbought, cocok untuk take profit atau sell konservatif.';
			$reasons[] = 'Harga masuk resistance zone dinamis timeframe dominan.';
			$reasons[] = 'RSI di atas 70 menandakan overbought.';
			$reasons[] = 'Harga menyentuh upper Bollinger Band.';
		} else {
			$reasons[] = 'Belum ada konfirmasi lengkap dari support/resistance dinamis dan indikator utama.';
		}

		$reasons[] = (string) ($combinedContext['summary'] ?? '');
		$fibBias = $this->buildFibonacciBias($currentPrice, $marketContext, $combinedContext);
		if ($fibBias !== '') {
			$reasons[] = $fibBias;
		}
		if ($lastRsi > 0) {
			$reasons[] = 'RSI terakhir berada di ' . number_format($lastRsi, 2, ',', '.') . '.';
		}
		if ($confluenceCount >= 2) {
			$reasons[] = 'Terdapat confluence pada ' . $confluenceCount . ' timeframe.';
		}

		return [
			'signal' => $signal,
			'signal_label' => $signalLabel,
			'confidence' => $confidence,
			'reason' => implode(' ', array_values(array_filter(array_unique($reasons)))),
			'reasons' => array_values(array_filter(array_unique($reasons))),
			'recommendation' => $recommendation,
		];
	}

	/**
	 * Note penting dibuat dinamis agar tidak lagi menampilkan level harga statis
	 * dan tetap konsisten dengan support serta resistance hasil perhitungan baru.
	 */
	protected function buildImportantNotes(array $combinedContext, array $decision): array
	{
		$sellZoneLabel = (string) (($combinedContext['sell_zone']['label'] ?? 'resistance aktif'));
		$buyZoneLabel = (string) (($combinedContext['buy_zone']['label'] ?? 'support aktif'));
		$breakoutLabel = $combinedContext['breakout_up_level'] > 0
			? number_format((float) $combinedContext['breakout_up_level'], 4, ',', '.')
			: 'resistance breakout aktif';

		return [
			'Avoid buying near active resistance zone ' . $sellZoneLabel,
			'Wait for pullback to support zone ' . $buyZoneLabel . ' OR breakout above ' . $breakoutLabel,
		];
	}

	/**
	 * RSI terakhir dibaca dari chart payload agar panel signal dan panel RSI
	 * selalu konsisten walau chart memakai timeframe yang berbeda.
	 */
	protected function resolveLatestRsi(array $chartPayload): float
	{
		$rsi = (float) ($chartPayload['meta']['last_rsi'] ?? 0);
		if ($rsi > 0) {
			return $rsi;
		}

		$rsiSeries = $chartPayload['series']['rsi'] ?? [];
		if ($rsiSeries) {
			$lastPoint = end($rsiSeries);
			return (float) ($lastPoint['y'] ?? 0);
		}

		return 0.0;
	}

	/**
	 * Bollinger dihitung dari close intraday supaya sinyal menyentuh upper atau
	 * lower band bisa diperbarui setiap 30 detik bersama polling dashboard.
	 */
	protected function buildBollingerSnapshot(array $candles, int $period = 20, float $multiplier = 2.0): array
	{
		$closes = array_map(static fn ($item) => (float) ($item['close'] ?? 0), $candles);
		$total = count($closes);
		if ($total < 2) {
			return [
				'period' => $period,
				'middle' => 0.0,
				'upper' => 0.0,
				'lower' => 0.0,
			];
		}

		$window = array_slice($closes, -min($period, $total));
		$middle = array_sum($window) / count($window);
		$variance = 0.0;
		foreach ($window as $closePrice) {
			$variance += pow($closePrice - $middle, 2);
		}

		$standardDeviation = sqrt($variance / count($window));

		return [
			'period' => min($period, $total),
			'middle' => $middle,
			'upper' => $middle + ($standardDeviation * $multiplier),
			'lower' => $middle - ($standardDeviation * $multiplier),
		];
	}

	/**
	 * Fibonacci tetap memakai swing high-low dinamis agar signal tambahan tetap
	 * konsisten dengan area harga yang baru saja dipakai market context.
	 */
	protected function buildFibonacciSnapshot(array $livePrice, array $latestDaily): array
	{
		$swingHigh = max(
			(float) ($livePrice['day_high'] ?? 0),
			(float) ($latestDaily['high_price'] ?? 0)
		);
		$swingLow = min(
			(float) ($livePrice['day_low'] ?: 0),
			(float) ($latestDaily['low_price'] ?? 0)
		);

		if ($swingLow <= 0 || $swingHigh <= $swingLow) {
			$swingHigh = (float) ($latestDaily['high_price'] ?? 0);
			$swingLow = (float) ($latestDaily['low_price'] ?? 0);
		}

		$range = max(0.0001, $swingHigh - $swingLow);

		return [
			'swing_high' => $swingHigh,
			'swing_low' => $swingLow,
			'0.382' => $swingHigh - ($range * 0.382),
			'0.500' => $swingHigh - ($range * 0.500),
			'0.618' => $swingHigh - ($range * 0.618),
			'1.000' => $swingHigh,
		];
	}

	/**
	 * Ringkasan Fibonacci membantu menjelaskan apakah harga mendekati retracement
	 * penting dari swing aktif tanpa bergantung pada level statis lama.
	 */
	protected function buildFibonacciBias(float $currentPrice, array $marketContext, array $combinedContext): string
	{
		$buyZone = $combinedContext['buy_zone'] ?? [];
		$sellZone = $combinedContext['sell_zone'] ?? [];
		if (!empty($buyZone['min']) && $currentPrice <= (float) $buyZone['max']) {
			return 'Harga sudah mendekati area support dan retracement aktif.';
		}

		if (!empty($sellZone['max']) && $currentPrice >= (float) $sellZone['min']) {
			return 'Harga sudah mendekati area resistance dan retracement aktif.';
		}

		return 'Harga masih bergerak di area tengah antara support dan resistance aktif.';
	}

	/**
	 * Overlay chart mengikuti timeframe chart aktif agar support, resistance,
	 * dan breakout line yang digambar tetap relevan dengan panel yang dilihat.
	 */
	protected function buildChartOverlayPayload(array $chartPayload, array $marketContext, array $fibonacci): array
	{
		$timeframeMap = [
			'1D' => 'daily',
			'1W' => 'weekly',
			'1M' => 'monthly',
		];
		$contextKey = $timeframeMap[strtoupper((string) ($chartPayload['timeframe'] ?? '1D'))] ?? 'daily';
		$context = $marketContext[$contextKey] ?? [];

		return [
			'support_zone' => [
				(float) ($context['support_zone']['min'] ?? 0),
				(float) ($context['support_zone']['max'] ?? 0),
			],
			'resistance_zone' => [
				(float) ($context['resistance_zone']['min'] ?? 0),
				(float) ($context['resistance_zone']['max'] ?? 0),
			],
			'breakout_up_level' => (float) ($context['breakout_up_level'] ?? 0),
			'breakout_down_level' => (float) ($context['breakout_down_level'] ?? 0),
			'fibonacci_levels' => [
				'0.382' => (float) ($fibonacci['0.382'] ?? 0),
				'0.500' => (float) ($fibonacci['0.500'] ?? 0),
				'0.618' => (float) ($fibonacci['0.618'] ?? 0),
			],
			'timeframe' => $context['label'] ?? 'Daily',
		];
	}

	/**
	 * Status auto-monitor dibaca terpusat agar dashboard dan module alert tidak
	 * menebak-nebak apakah automation signal sedang diaktifkan user atau tidak.
	 */
	protected function resolveAutoMonitorStatus(int $userId): array
	{
		$setting = $this->model->getSignalSetting($userId);

		return [
			'enabled' => !empty($setting['auto_monitor']),
			'label' => !empty($setting['auto_monitor']) ? 'Auto-monitor aktif' : 'Auto-monitor nonaktif',
		];
	}

	/**
	 * Automation signal memakai cache state per user agar alert tidak spam pada
	 * setiap polling ketika harga masih bertahan di zona yang sama.
	 */
	protected function handleAutomationTrigger(int $userId, array $signalPayload): array
	{
		$autoMonitor = $this->resolveAutoMonitorStatus($userId);
		$result = [
			'enabled' => $autoMonitor['enabled'],
			'triggered_alerts' => [],
		];

		if (!$autoMonitor['enabled']) {
			return $result;
		}

		$signal = (string) ($signalPayload['current_signal'] ?? 'WAIT');
		$cacheKey = 'forex_signal_auto_state_' . $userId;
		$lastState = (string) ($this->cache->get($cacheKey) ?: '');

		if ($signal === 'WAIT') {
			$this->cache->save($cacheKey, 'WAIT', 43200);
			return $result;
		}

		$eventMap = [
			'BUY' => 'buy_zone',
			'SELL' => strpos((string) ($signalPayload['signal_label'] ?? ''), 'BREAKDOWN') !== false ? 'breakdown' : 'sell_zone',
			'BREAKOUT' => 'breakout',
		];
		$conditionType = $eventMap[$signal] ?? 'signal';
		if ($lastState === $conditionType) {
			return $result;
		}

		$targetPrice = $conditionType === 'buy_zone'
			? (float) ($signalPayload['buy_zone']['max'] ?? 0)
			: ($conditionType === 'sell_zone'
				? (float) ($signalPayload['sell_zone']['min'] ?? 0)
				: ($conditionType === 'breakdown'
					? (float) ($signalPayload['breakdown_level'] ?? 0)
					: (float) ($signalPayload['breakout_level'] ?? 0)));

		$message = 'Auto-monitor ' . $this->model->getPair() . ' menghasilkan '
			. strtoupper((string) ($signalPayload['signal_label'] ?? $signal))
			. ' di harga ' . number_format((float) ($signalPayload['current_price'] ?? 0), 4, ',', '.')
			. '. ' . (string) ($signalPayload['recommendation'] ?? '');

		$history = $this->model->insertSystemAlertHistory($userId, [
			'condition_type' => $conditionType,
			'target_price' => $targetPrice,
			'triggered_price' => (float) ($signalPayload['current_price'] ?? 0),
			'with_sound' => 0,
			'message' => $message,
		]);

		$this->cache->save($cacheKey, $conditionType, 43200);
		$result['triggered_alerts'][] = [
			'id_forex_alert' => 0,
			'message' => $message,
			'with_sound' => 0,
			'condition_type' => $conditionType,
			'target_price' => $targetPrice,
			'triggered_price' => (float) ($signalPayload['current_price'] ?? 0),
			'created_at' => $history['created_at'] ?? date('Y-m-d H:i:s'),
		];

		return $result;
	}

	/**
	 * Signal badge memakai warna konsisten agar BUY/SELL/WAIT/BREAKOUT mudah
	 * dibedakan sekilas di dashboard desktop maupun kartu mobile.
	 */
	protected function resolveSignalColor(string $signal): string
	{
		switch (strtoupper($signal)) {
			case 'BUY':
				return 'green';
			case 'SELL':
				return 'red';
			case 'BREAKOUT':
				return 'blue';
			default:
				return 'yellow';
		}
	}

	/**
	 * Cek status area per timeframe dibungkus helper agar breakout, support,
	 * dan resistance selalu memakai logika evaluasi yang konsisten.
	 */
	protected function resolveTimeframeStatus(
		float $currentPrice,
		array $supportZone,
		array $resistanceZone,
		float $breakoutUpLevel,
		float $breakoutDownLevel
	): array {
		if ($breakoutUpLevel > 0 && $currentPrice > $breakoutUpLevel) {
			return [
				'status' => 'bullish_breakout',
				'label' => 'Bullish Breakout',
				'color' => 'blue',
				'bias' => 'bullish',
			];
		}

		if ($breakoutDownLevel > 0 && $currentPrice < $breakoutDownLevel) {
			return [
				'status' => 'bearish_breakout',
				'label' => 'Bearish Breakout',
				'color' => 'red',
				'bias' => 'bearish',
			];
		}

		if ($this->isWithinZone($currentPrice, $supportZone)) {
			return [
				'status' => 'near_support',
				'label' => 'Near Support',
				'color' => 'green',
				'bias' => 'bullish',
			];
		}

		if ($this->isWithinZone($currentPrice, $resistanceZone)) {
			return [
				'status' => 'near_resistance',
				'label' => 'Near Resistance',
				'color' => 'red',
				'bias' => 'bearish',
			];
		}

		return [
			'status' => 'inside_range',
			'label' => 'Inside Range',
			'color' => 'yellow',
			'bias' => 'neutral',
		];
	}

	/**
	 * ATR harian sederhana dipakai sebagai basis buffer agar zone support dan
	 * resistance menjadi range dinamis tanpa harga hardcoded.
	 */
	protected function calculateAtr(array $rows, int $period = 14): float
	{
		$rows = array_values(array_filter($rows));
		if (!$rows) {
			return 0.0;
		}

		$rows = array_slice($rows, -min($period, count($rows)));
		$trueRanges = [];
		$previousClose = null;
		foreach ($rows as $row) {
			$highPrice = (float) ($row['high_price'] ?? 0);
			$lowPrice = (float) ($row['low_price'] ?? 0);
			$closePrice = (float) ($row['close_price'] ?? 0);
			$range = max(0, $highPrice - $lowPrice);

			if ($previousClose === null) {
				$trueRanges[] = $range;
			} else {
				$trueRanges[] = max(
					$range,
					abs($highPrice - $previousClose),
					abs($lowPrice - $previousClose)
				);
			}

			$previousClose = $closePrice;
		}

		return $trueRanges ? (array_sum($trueRanges) / count($trueRanges)) : 0.0;
	}

	/**
	 * Buffer zone dijaga ringan dengan kombinasi ATR dan persen range supaya
	 * tiap timeframe punya lebar area yang tetap proporsional.
	 */
	protected function resolveZoneBuffer(float $range, float $atr, string $timeframeKey): float
	{
		$factorMap = [
			'daily' => ['range' => 0.08, 'atr' => 0.18],
			'weekly' => ['range' => 0.10, 'atr' => 0.20],
			'monthly' => ['range' => 0.12, 'atr' => 0.22],
		];
		$factors = $factorMap[$timeframeKey] ?? $factorMap['daily'];

		return max(
			0.0001,
			$range * $factors['range'],
			$atr * $factors['atr']
		);
	}

	/**
	 * Zone payload dibuat seragam agar UI, signal, dan automation bisa memakai
	 * min, max, midpoint, dan label tanpa merakit ulang format angka.
	 */
	protected function buildZonePayload(float $min, float $max): array
	{
		if ($max < $min) {
			$swap = $min;
			$min = $max;
			$max = $swap;
		}

		return [
			'min' => $min,
			'max' => $max,
			'mid' => ($min + $max) / 2,
			'label' => number_format($min, 4, ',', '.') . ' - ' . number_format($max, 4, ',', '.'),
		];
	}

	/**
	 * Ringkasan gabungan membantu user melihat apakah bias dominan datang dari
	 * breakout, support, resistance, atau confluence antar timeframe.
	 */
	protected function buildCombinedSummary(array $dominantContext, int $confluenceCount, float $currentPrice): string
	{
		if (!$dominantContext) {
			return 'Belum ada context dominan yang cukup untuk menyimpulkan bias pasar.';
		}

		$summary = 'Timeframe dominan saat ini adalah ' . ($dominantContext['label'] ?? 'Monthly')
			. ' dengan status ' . strtolower((string) ($dominantContext['status_label'] ?? 'inside range')) . '.';

		if ($confluenceCount >= 2) {
			$summary .= ' Confluence muncul pada ' . $confluenceCount . ' timeframe.';
		} else {
			$summary .= ' Timeframe lain belum memberi konfirmasi kuat.';
		}

		$summary .= ' Harga terakhir berada di ' . number_format($currentPrice, 4, ',', '.') . '.';
		return $summary;
	}

	/**
	 * Context dominan memilih breakout lebih dulu, lalu support/resistance,
	 * dengan override timeframe yang lebih tinggi terhadap timeframe bawah.
	 */
	protected function resolveDominantContext(?array $bullishBreakout, ?array $bearishBreakout, ?array $buyContext, ?array $sellContext): array
	{
		$candidates = array_values(array_filter([
			$bullishBreakout,
			$bearishBreakout,
			$buyContext,
			$sellContext,
		]));
		if (!$candidates) {
			return [];
		}

		/**
		 * Kandidat diurutkan berdasarkan prioritas timeframe tertinggi lebih dulu,
		 * lalu breakout diutamakan dibanding sentuhan support atau resistance.
		 */
		usort($candidates, function (array $left, array $right): int {
			$leftPriority = (int) ($this->timeframePriority[$left['key'] ?? 'daily'] ?? 0);
			$rightPriority = (int) ($this->timeframePriority[$right['key'] ?? 'daily'] ?? 0);
			if ($leftPriority !== $rightPriority) {
				return $rightPriority <=> $leftPriority;
			}

			$weightMap = [
				'bullish_breakout' => 3,
				'bearish_breakout' => 3,
				'near_support' => 2,
				'near_resistance' => 2,
				'inside_range' => 1,
			];
			$leftWeight = (int) ($weightMap[$left['status'] ?? 'inside_range'] ?? 0);
			$rightWeight = (int) ($weightMap[$right['status'] ?? 'inside_range'] ?? 0);
			return $rightWeight <=> $leftWeight;
		});

		return $candidates[0];
	}

	/**
	 * Context diurutkan dari prioritas tertinggi ke terendah agar Monthly dapat
	 * override Weekly dan Daily ketika status antar timeframe saling konflik.
	 */
	protected function sortContextsByPriority(array $contexts): array
	{
		$items = [];
		foreach (['monthly', 'weekly', 'daily'] as $key) {
			if (!empty($contexts[$key])) {
				$items[] = $contexts[$key];
			}
		}

		return $items;
	}

	/**
	 * Cari context pertama berdasarkan status aktif mengikuti urutan prioritas
	 * yang sebelumnya sudah disusun dari timeframe tertinggi ke terendah.
	 */
	protected function findContextByStatuses(array $contexts, array $statuses): ?array
	{
		foreach ($contexts as $context) {
			if (in_array((string) ($context['status'] ?? ''), $statuses, true)) {
				return $context;
			}
		}

		return null;
	}

	/**
	 * Hitung confluence sederhana dari jumlah timeframe yang berbagi bias sama
	 * agar dashboard bisa menandai apakah sinyal tergolong lebih kuat.
	 */
	protected function calculateConfluenceCount(array $contexts): int
	{
		$biasCounts = [
			'bullish' => 0,
			'bearish' => 0,
		];
		foreach (['daily', 'weekly', 'monthly'] as $key) {
			$bias = (string) ($contexts[$key]['bias'] ?? 'neutral');
			if (isset($biasCounts[$bias])) {
				$biasCounts[$bias]++;
			}
		}

		return max($biasCounts);
	}

	/**
	 * Nilai maksimum field row dipusatkan agar perhitungan context tidak perlu
	 * mengulang loop array dengan logika yang sama di banyak method.
	 */
	protected function maxRowValue(array $rows, string $field): float
	{
		$values = [];
		foreach ($rows as $row) {
			$values[] = (float) ($row[$field] ?? 0);
		}

		return $values ? max($values) : 0.0;
	}

	/**
	 * Nilai minimum field row dipusatkan agar context support dapat dibangun
	 * konsisten dari data Daily, Weekly, maupun Monthly.
	 */
	protected function minRowValue(array $rows, string $field): float
	{
		$values = [];
		foreach ($rows as $row) {
			$value = (float) ($row[$field] ?? 0);
			if ($value > 0) {
				$values[] = $value;
			}
		}

		return $values ? min($values) : 0.0;
	}

	/**
	 * Reference date context mengikuti quote live lebih dulu agar daily session
	 * yang sedang berjalan tetap menjadi basis utama context intraday.
	 */
	protected function resolveReferenceDate(array $livePrice, array $latestDaily): string
	{
		$quoteTime = trim((string) ($livePrice['quote_time'] ?? ''));
		if ($quoteTime !== '') {
			return date('Y-m-d', strtotime($quoteTime));
		}

		return (string) ($latestDaily['date'] ?? date('Y-m-d'));
	}

	/**
	 * Context dianggap aktif bila harga berada di dalam zone yang dibentuk dari
	 * high-low timeframe beserta buffer volatilitasnya.
	 */
	protected function isWithinZone(float $price, array $zone): bool
	{
		return $price >= (float) ($zone['min'] ?? 0) && $price <= (float) ($zone['max'] ?? 0);
	}

	/**
	 * Sentuhan lower band memakai low candle terakhir agar wick penolakan juga
	 * ikut dianggap valid walau close belum tepat menempel pada band bawah.
	 */
	protected function touchesLowerBand(float $currentPrice, array $lastCandle, array $bollinger): bool
	{
		$lowerBand = (float) ($bollinger['lower'] ?? 0);
		$lastLow = (float) ($lastCandle['low'] ?? $currentPrice);
		if ($lowerBand <= 0) {
			return false;
		}

		return $currentPrice <= ($lowerBand + $this->bollingerTouchTolerance)
			|| $lastLow <= ($lowerBand + $this->bollingerTouchTolerance);
	}

	/**
	 * Sentuhan upper band juga membaca high candle terakhir agar sinyal sell
	 * bisa menangkap rejection wick di area resistance terdekat.
	 */
	protected function touchesUpperBand(float $currentPrice, array $lastCandle, array $bollinger): bool
	{
		$upperBand = (float) ($bollinger['upper'] ?? 0);
		$lastHigh = (float) ($lastCandle['high'] ?? $currentPrice);
		if ($upperBand <= 0) {
			return false;
		}

		return $currentPrice >= ($upperBand - $this->bollingerTouchTolerance)
			|| $lastHigh >= ($upperBand - $this->bollingerTouchTolerance);
	}

	/**
	 * Zone kosong dipakai sebagai fallback aman agar view dan automation tidak
	 * melempar notice ketika context dominan belum berhasil dibentuk.
	 */
	protected function emptyZonePayload(): array
	{
		return [
			'min' => 0.0,
			'max' => 0.0,
			'mid' => 0.0,
			'label' => '-',
		];
	}
}
