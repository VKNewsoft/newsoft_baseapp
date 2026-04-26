<?php
/**
 * Service prediksi forex menghitung proyeksi next-day GBP/JPY dari data OHLC
 * terakhir dengan beberapa metode ringan yang deterministik dan repeatable.
 */

namespace App\Modules\ForexPrediction\Libraries;

use App\Modules\ForexMonitor\Models\ForexMonitorModel;

class ForexPredictionService
{
	protected ForexMonitorModel $model;
	protected $cache;
	protected int $cacheTtl = 43200;

	public function __construct(?ForexMonitorModel $model = null)
	{
		$this->model = $model ?: new ForexMonitorModel();
		$this->cache = cache();
	}

	/**
	 * Prediksi terbaru selalu dibangun dari candle historis yang tersimpan agar
	 * hasil tetap konsisten walau provider API sedang tidak bisa diakses.
	 */
	public function getLatestPrediction(?string $maxBaseDate = null, bool $forceRefresh = false, string $mode = 'manual'): array
	{
		$basePrice = $this->model->getLatestSnapshot($maxBaseDate);
		if (!$basePrice) {
			return [];
		}

		$baseDate = (string) ($basePrice['date'] ?? '');
		if ($baseDate === '') {
			return [];
		}

		$cacheKey = $this->buildCacheKey($baseDate, $mode);
		if ($forceRefresh) {
			$this->cache->delete($cacheKey);
		}

		$cached = $this->cache->get($cacheKey);
		if (!$forceRefresh && is_array($cached) && !empty($cached['base_date']) && $cached['base_date'] === $baseDate) {
			return $cached;
		}

		$prediction = $this->buildPredictionPayload($basePrice, $mode);
		$this->cache->save($cacheKey, $prediction, $this->cacheTtl);
		$this->cache->save($this->buildLatestAliasCacheKey(), $prediction, $this->cacheTtl);

		return $prediction;
	}

	/**
	 * Metadata scheduler dipisahkan agar controller dan command dapat memakai
	 * acuan sesi New York yang sama tanpa hardcode berulang di banyak tempat.
	 */
	public function getSchedulerMeta(string $mode = 'manual'): array
	{
		$timezone = (string) (env('forex.newYorkSessionTimezone') ?: 'America/New_York');
		$openHour = (int) (env('forex.newYorkSessionOpenHour') ?: 8);
		$activeEndHour = (int) (env('forex.newYorkSessionActiveEndHour') ?: 17);
		$now = new \DateTimeImmutable('now', new \DateTimeZone($timezone));
		$sessionOpen = $now->setTime($openHour, 0, 0);
		$sessionEnd = $now->setTime($activeEndHour, 0, 0);

		return [
			'timezone' => $timezone,
			'open_hour' => $openHour,
			'active_end_hour' => $activeEndHour,
			'mode' => $mode,
			'current_time' => $now->format('Y-m-d H:i:s'),
			'session_open_time' => $sessionOpen->format('Y-m-d H:i:s'),
			'session_end_time' => $sessionEnd->format('Y-m-d H:i:s'),
		];
	}

	/**
	 * Tanggal target memakai hari kerja berikutnya supaya prediksi Jumat
	 * otomatis diarahkan ke sesi Senin tanpa menambah rule kalender rumit.
	 */
	public function getNextTradingDate(string $baseDate): string
	{
		$date = new \DateTimeImmutable($baseDate);
		do {
			$date = $date->modify('+1 day');
		} while ((int) $date->format('N') >= 6);

		return $date->format('Y-m-d');
	}

	/**
	 * Semua metode dibangun dari payload yang sama agar kalkulasi tetap ringan
	 * dan tiap section UI menampilkan angka yang saling konsisten.
	 */
	protected function buildPredictionPayload(array $basePrice, string $mode): array
	{
		$openPrice = (float) ($basePrice['open_price'] ?? 0);
		$highPrice = (float) ($basePrice['high_price'] ?? 0);
		$lowPrice = (float) ($basePrice['low_price'] ?? 0);
		$closePrice = (float) ($basePrice['close_price'] ?? 0);
		$range = max(0.0001, $highPrice - $lowPrice);
		$bodySize = abs($closePrice - $openPrice);
		$bodyRatio = min(1, $bodySize / $range);
		$closePosition = min(1, max(0, ($closePrice - $lowPrice) / $range));
		$volatilityRatio = max(0, $range / max($closePrice, 0.0001));
		$previousDirection = $this->model->detectTrend($openPrice, $closePrice);

		$context = [
			'base_date' => (string) ($basePrice['date'] ?? ''),
			'target_date' => $this->getNextTradingDate((string) ($basePrice['date'] ?? date('Y-m-d'))),
			'pair' => (string) ($basePrice['pair'] ?? $this->model->getPair()),
			'open_price' => $openPrice,
			'high_price' => $highPrice,
			'low_price' => $lowPrice,
			'close_price' => $closePrice,
			'range' => $range,
			'body_size' => $bodySize,
			'body_ratio' => $bodyRatio,
			'close_position' => $closePosition,
			'volatility_ratio' => $volatilityRatio,
			'previous_direction' => $previousDirection,
		];

		$methods = [
			'fibonacci' => $this->buildFibonacciPrediction($context),
			'pivot_point' => $this->buildPivotPrediction($context),
			'elliott_wave' => $this->buildElliottPrediction($context),
			'kang_gun' => $this->buildKangGunPrediction($context),
		];

		return [
			'pair' => $context['pair'],
			'base_date' => $context['base_date'],
			'target_date' => $context['target_date'],
			'generated_at' => date('Y-m-d H:i:s'),
			'scheduler' => $this->getSchedulerMeta($mode),
			'base_price' => [
				'open_price' => $openPrice,
				'high_price' => $highPrice,
				'low_price' => $lowPrice,
				'close_price' => $closePrice,
				'range' => $range,
				'trend' => $previousDirection,
			],
			'methods' => $methods,
			'aggregate' => $this->buildAggregatePrediction($methods, $context),
		];
	}

	/**
	 * Metode Fibonacci memakai range hari sebelumnya untuk membentuk level
	 * support, resistance, dan zona proyeksi high-low pada sesi berikutnya.
	 */
	protected function buildFibonacciPrediction(array $context): array
	{
		$range = $context['range'];
		$closePrice = $context['close_price'];
		$bias = $this->resolveDirectionalBias($context['previous_direction'], $context['body_ratio'], $context['close_position']);

		$levels = [
			'0.382' => $range * 0.382,
			'0.500' => $range * 0.500,
			'0.618' => $range * 0.618,
			'1.000' => $range * 1.000,
		];

		$supportLevels = [
			'0.382' => $closePrice - $levels['0.382'],
			'0.500' => $closePrice - $levels['0.500'],
			'0.618' => $closePrice - $levels['0.618'],
		];
		$resistanceLevels = [
			'0.382' => $closePrice + $levels['0.382'],
			'0.500' => $closePrice + $levels['0.500'],
			'0.618' => $closePrice + $levels['0.618'],
			'1.000' => $closePrice + $levels['1.000'],
		];

		if ($bias === 'bullish') {
			$predictedLow = $supportLevels['0.382'];
			$predictedHigh = $resistanceLevels['0.618'];
			$highZone = [$resistanceLevels['0.618'], $resistanceLevels['1.000']];
			$lowZone = [$supportLevels['0.500'], $supportLevels['0.382']];
		} elseif ($bias === 'bearish') {
			$predictedLow = $supportLevels['0.618'];
			$predictedHigh = $resistanceLevels['0.382'];
			$highZone = [$resistanceLevels['0.382'], $resistanceLevels['0.500']];
			$lowZone = [$supportLevels['0.618'], $closePrice - $levels['1.000']];
		} else {
			$predictedLow = $supportLevels['0.500'];
			$predictedHigh = $resistanceLevels['0.500'];
			$highZone = [$resistanceLevels['0.382'], $resistanceLevels['0.618']];
			$lowZone = [$supportLevels['0.618'], $supportLevels['0.382']];
		}

		return [
			'key' => 'fibonacci',
			'title' => 'Fibonacci Section',
			'direction' => $bias,
			'predicted_high' => $predictedHigh,
			'predicted_low' => $predictedLow,
			'predicted_high_zone' => $highZone,
			'predicted_low_zone' => $lowZone,
			'support_levels' => $supportLevels,
			'resistance_levels' => $resistanceLevels,
			'detail' => 'Fibonacci memakai range candle sebelumnya untuk memetakan support dan resistance berikutnya.',
		];
	}

	/**
	 * Pivot Point standar memberi level kunci intraday yang mudah dihitung
	 * dan cocok untuk indikasi breakout atau reversal secara deterministik.
	 */
	protected function buildPivotPrediction(array $context): array
	{
		$highPrice = $context['high_price'];
		$lowPrice = $context['low_price'];
		$closePrice = $context['close_price'];
		$pivot = ($highPrice + $lowPrice + $closePrice) / 3;
		$r1 = (2 * $pivot) - $lowPrice;
		$r2 = $pivot + ($highPrice - $lowPrice);
		$s1 = (2 * $pivot) - $highPrice;
		$s2 = $pivot - ($highPrice - $lowPrice);
		$isStrongBreakout = $closePrice > $pivot && $context['close_position'] >= 0.7 && $context['body_ratio'] >= 0.45;
		$isStrongBreakdown = $closePrice < $pivot && $context['close_position'] <= 0.3 && $context['body_ratio'] >= 0.45;

		if ($isStrongBreakout) {
			$direction = 'bullish';
			$indication = 'breakout';
			$predictedHigh = $r2;
			$predictedLow = $pivot;
		} elseif ($isStrongBreakdown) {
			$direction = 'bearish';
			$indication = 'breakout';
			$predictedHigh = $pivot;
			$predictedLow = $s2;
		} elseif ($closePrice >= $pivot) {
			$direction = 'bullish';
			$indication = 'reversal';
			$predictedHigh = $r1;
			$predictedLow = $s1;
		} else {
			$direction = 'bearish';
			$indication = 'reversal';
			$predictedHigh = $r1;
			$predictedLow = $s1;
		}

		return [
			'key' => 'pivot_point',
			'title' => 'Pivot Section',
			'direction' => $direction,
			'predicted_high' => $predictedHigh,
			'predicted_low' => $predictedLow,
			'pivot' => $pivot,
			'r1' => $r1,
			'r2' => $r2,
			's1' => $s1,
			's2' => $s2,
			'indication' => $indication,
			'detail' => 'Pivot Point menyorot level kunci untuk membaca peluang breakout dibanding reversal.',
		];
	}

	/**
	 * Elliott Wave sederhana hanya membaca kekuatan candle terakhir untuk
	 * memilih bias impulsive continuation atau corrective pullback.
	 */
	protected function buildElliottPrediction(array $context): array
	{
		$range = $context['range'];
		$closePrice = $context['close_price'];
		$isBullishStrong = $context['previous_direction'] === 'bullish' && $context['body_ratio'] >= 0.55 && $context['close_position'] >= 0.75;
		$isBearishStrong = $context['previous_direction'] === 'bearish' && $context['body_ratio'] >= 0.55 && $context['close_position'] <= 0.25;
		$isWeakCandle = $context['body_ratio'] < 0.32;

		if ($isBullishStrong) {
			$waveBias = 'impulse';
			$direction = 'bullish';
			$predictedHigh = $closePrice + ($range * 0.618);
			$predictedLow = $closePrice - ($range * 0.236);
		} elseif ($isBearishStrong) {
			$waveBias = 'impulse';
			$direction = 'bearish';
			$predictedHigh = $closePrice + ($range * 0.236);
			$predictedLow = $closePrice - ($range * 0.618);
		} elseif ($isWeakCandle && $context['previous_direction'] === 'bullish') {
			$waveBias = 'corrective';
			$direction = 'bearish';
			$predictedHigh = $closePrice + ($range * 0.236);
			$predictedLow = $closePrice - ($range * 0.382);
		} elseif ($isWeakCandle && $context['previous_direction'] === 'bearish') {
			$waveBias = 'corrective';
			$direction = 'bullish';
			$predictedHigh = $closePrice + ($range * 0.382);
			$predictedLow = $closePrice - ($range * 0.236);
		} else {
			$waveBias = 'corrective';
			$direction = $context['previous_direction'] === 'sideways' ? 'sideways' : $context['previous_direction'];
			$predictedHigh = $closePrice + ($range * 0.300);
			$predictedLow = $closePrice - ($range * 0.300);
		}

		return [
			'key' => 'elliott_wave',
			'title' => 'Elliott Wave Section',
			'direction' => $direction,
			'predicted_high' => $predictedHigh,
			'predicted_low' => $predictedLow,
			'wave_bias' => $waveBias,
			'detail' => 'Elliott Wave sederhana memetakan candle kuat sebagai impulsive dan candle lemah sebagai corrective.',
		];
	}

	/**
	 * Kang Gun menggabungkan range, posisi close, dan volatilitas supaya
	 * proyeksi ekspansi harian tetap ringan tetapi lebih adaptif.
	 */
	protected function buildKangGunPrediction(array $context): array
	{
		$range = $context['range'];
		$closePrice = $context['close_price'];
		$strengthScore = (($context['close_price'] - $context['open_price']) / $range) * 100;
		$positionScore = (($context['close_position'] - 0.5) * 100);
		$volatilityBoost = min(30, $context['volatility_ratio'] * 1200);
		$combinedScore = $strengthScore + ($positionScore * 0.5);

		if ($combinedScore > 18) {
			$direction = 'bullish';
		} elseif ($combinedScore < -18) {
			$direction = 'bearish';
		} else {
			$direction = 'sideways';
		}

		$expansionMultiplier = min(1.75, max(0.80, 0.85 + $context['body_ratio'] + ($volatilityBoost / 100)));
		$expansionRange = $range * $expansionMultiplier;

		if ($direction === 'bullish') {
			$predictedHigh = $closePrice + ($expansionRange * 0.65);
			$predictedLow = $closePrice - ($expansionRange * 0.35);
		} elseif ($direction === 'bearish') {
			$predictedHigh = $closePrice + ($expansionRange * 0.35);
			$predictedLow = $closePrice - ($expansionRange * 0.65);
		} else {
			$predictedHigh = $closePrice + ($expansionRange * 0.50);
			$predictedLow = $closePrice - ($expansionRange * 0.50);
		}

		return [
			'key' => 'kang_gun',
			'title' => 'Kang Gun Section',
			'direction' => $direction,
			'predicted_high' => $predictedHigh,
			'predicted_low' => $predictedLow,
			'expansion_range' => $expansionRange,
			'volatility_score' => $volatilityBoost,
			'detail' => 'Kang Gun menggabungkan candle strength, range, dan volatilitas untuk membaca bias ekspansi berikutnya.',
		];
	}

	/**
	 * Ringkasan gabungan memakai suara mayoritas direction dan rata-rata zona
	 * high-low semua metode agar hasil akhir tetap sederhana untuk operator.
	 */
	protected function buildAggregatePrediction(array $methods, array $context): array
	{
		$directionVotes = ['bullish' => 0, 'bearish' => 0];
		$totalHigh = 0.0;
		$totalLow = 0.0;
		$methodCount = 0;

		foreach ($methods as $method) {
			$direction = (string) ($method['direction'] ?? 'sideways');
			if (isset($directionVotes[$direction])) {
				$directionVotes[$direction]++;
			}

			$totalHigh += (float) ($method['predicted_high'] ?? 0);
			$totalLow += (float) ($method['predicted_low'] ?? 0);
			$methodCount++;
		}

		if ($directionVotes['bullish'] > $directionVotes['bearish']) {
			$majorityDirection = 'bullish';
		} elseif ($directionVotes['bearish'] > $directionVotes['bullish']) {
			$majorityDirection = 'bearish';
		} else {
			$majorityDirection = $context['previous_direction'] === 'sideways' ? 'bullish' : $context['previous_direction'];
		}

		$averageHigh = $methodCount > 0 ? ($totalHigh / $methodCount) : 0;
		$averageLow = $methodCount > 0 ? ($totalLow / $methodCount) : 0;

		return [
			'direction' => $majorityDirection,
			'predicted_high' => $averageHigh,
			'predicted_low' => $averageLow,
			'votes' => $directionVotes,
			'summary' => 'Mayoritas metode membaca bias ' . $majorityDirection
				. ' untuk sesi ' . $context['target_date']
				. ' dengan rata-rata proyeksi high di '
				. number_format($averageHigh, 4, ',', '.')
				. ' dan low di '
				. number_format($averageLow, 4, ',', '.')
				. '.',
		];
	}

	/**
	 * Bias pendukung untuk Fibonacci dibuat terpisah agar aturan arah yang
	 * dipakai tetap eksplisit dan mudah dirawat saat formula disetel ulang.
	 */
	protected function resolveDirectionalBias(string $previousDirection, float $bodyRatio, float $closePosition): string
	{
		if ($previousDirection === 'bullish' && ($bodyRatio >= 0.35 || $closePosition >= 0.60)) {
			return 'bullish';
		}

		if ($previousDirection === 'bearish' && ($bodyRatio >= 0.35 || $closePosition <= 0.40)) {
			return 'bearish';
		}

		return 'sideways';
	}

	/**
	 * Cache key dipisahkan per base date dan mode agar rekalkulasi market open
	 * serta re-evaluasi hourly tidak saling menimpa payload historis terbaru.
	 */
	protected function buildCacheKey(string $baseDate, string $mode): string
	{
		return 'forex_prediction_' . md5($this->model->getPair() . '|' . $baseDate . '|' . $mode);
	}

	/**
	 * Alias latest disimpan agar komponen lain dapat mengambil payload terbaru
	 * bila tidak membutuhkan kontrol khusus terhadap tanggal basis prediksi.
	 */
	protected function buildLatestAliasCacheKey(): string
	{
		return 'forex_prediction_latest_' . md5($this->model->getPair());
	}
}
