<?php
/**
 * Model Forex Monitor menangani penyimpanan harga, analisis, live price,
 * alert, dan agregasi histori GBP/JPY untuk kebutuhan monitor terpusat.
 */

namespace App\Modules\ForexMonitor\Models;

class ForexMonitorModel extends \App\Modules\Common\Models\BaseModel
{
	protected string $pair = 'GBPJPY';
	protected string $priceTable = 'forex_price';
	protected string $analysisTable = 'forex_analysis';
	protected string $livePriceTable = 'forex_live_price';
	protected string $alertTable = 'forex_alert';
	protected string $alertHistoryTable = 'forex_alert_history';

	/**
	 * Validasi format tanggal dipusatkan agar controller, service, dan command
	 * memakai aturan tanggal yang sama untuk filter maupun fetch manual.
	 */
	public function normalizeDate(?string $date): ?string
	{
		$date = trim((string) $date);
		if ($date === '') {
			return null;
		}

		$parsed = \DateTime::createFromFormat('Y-m-d', $date);
		if (!$parsed || $parsed->format('Y-m-d') !== $date) {
			return null;
		}

		return $date;
	}

	/**
	 * Pair dibuat tetap agar module fokus ke kebutuhan monitoring GBP/JPY
	 * sesuai scope dan tidak membuka variasi input yang belum dibutuhkan.
	 */
	public function getPair(): string
	{
		return $this->pair;
	}

	/**
	 * Sumber API disediakan terpusat agar nilai database dan tampilan selalu
	 * memakai label yang sama saat data berhasil diambil dari provider gratis.
	 */
	public function getSourceApiLabel(): string
	{
		return 'Alpha Vantage FX_DAILY';
	}

	/**
	 * Filter tanggal default dibatasi 30 hari terakhir agar DataTable dan card
	 * mobile langsung menampilkan histori yang relevan tanpa query terlalu lebar.
	 */
	public function getDateRangeFilters(int $defaultDays = 30): array
	{
		$defaultTo = date('Y-m-d');
		$defaultFrom = date('Y-m-d', strtotime('-' . max(1, $defaultDays - 1) . ' days'));
		$dateFrom = $this->normalizeDate($this->request->getGet('date_from')) ?: $defaultFrom;
		$dateTo = $this->normalizeDate($this->request->getGet('date_to')) ?: $defaultTo;

		if ($dateFrom > $dateTo) {
			$swapDate = $dateFrom;
			$dateFrom = $dateTo;
			$dateTo = $swapDate;
		}

		return [
			'date_from' => $dateFrom,
			'date_to' => $dateTo,
		];
	}

	/**
	 * Ambil satu data harga berdasarkan tanggal agar service bisa mendeteksi
	 * duplikasi fetch dan memilih fallback terakhir saat API sedang gagal.
	 */
	public function getPriceByDate(string $date): array
	{
		return (array) $this->db->table($this->priceTable)
			->where('pair', $this->pair)
			->where('date', $date)
			->get()
			->getRowArray();
	}

	/**
	 * Ambil satu data analisis berdasarkan tanggal supaya service dapat
	 * memastikan report harian ikut tersimpan ketika harga berhasil di-upsert.
	 */
	public function getAnalysisByDate(string $date): array
	{
		return (array) $this->db->table($this->analysisTable)
			->where('pair', $this->pair)
			->where('date', $date)
			->get()
			->getRowArray();
	}

	/**
	 * Data terbaru dipakai sebagai fallback aman saat API gagal atau limit
	 * tercapai, sehingga halaman monitoring tetap bisa menampilkan data terakhir.
	 */
	public function getLatestSnapshot(?string $maxDate = null): array
	{
		$builder = $this->db->table($this->priceTable . ' fp')
			->select('fp.*, fa.high_low_range, fa.trend, fa.summary, fa.created_at AS analysis_created_at')
			->join($this->analysisTable . ' fa', 'fa.pair = fp.pair AND fa.date = fp.date', 'left')
			->where('fp.pair', $this->pair);

		if ($maxDate !== null && $maxDate !== '') {
			$builder->where('fp.date <=', $maxDate);
		}

		return (array) $builder
			->orderBy('fp.date', 'DESC')
			->get()
			->getRowArray();
	}

	/**
	 * Query dasar histori dipisahkan agar halaman monitor dan prediction dapat
	 * berbagi aturan filter yang sama tanpa menduplikasi kondisi where.
	 */
	protected function buildHistoryQuery(array $filters = [])
	{
		$builder = $this->db->table($this->priceTable . ' fp')
			->select('fp.*, fa.high_low_range, fa.trend, fa.summary, fa.created_at AS analysis_created_at')
			->join($this->analysisTable . ' fa', 'fa.pair = fp.pair AND fa.date = fp.date', 'left')
			->where('fp.pair', $this->pair);

		if (!empty($filters['date_from'])) {
			$builder->where('fp.date >=', $filters['date_from']);
		}

		if (!empty($filters['date_to'])) {
			$builder->where('fp.date <=', $filters['date_to']);
		}

		return $builder;
	}

	/**
	 * List harga harian menyertakan analisis agar tabel desktop dan card mobile
	 * cukup memakai satu dataset yang sama untuk seluruh tampilan.
	 */
	public function getPriceHistory(array $filters = []): array
	{
		return $this->buildHistoryQuery($filters)
			->orderBy('fp.date', 'DESC')
			->get()
			->getResultArray();
	}

	/**
	 * List report harian diurutkan terbaru lebih dulu agar halaman prediction
	 * langsung menonjolkan analisis paling baru yang relevan untuk dibaca.
	 */
	public function getAnalysisHistory(array $filters = []): array
	{
		return $this->buildHistoryQuery($filters)
			->orderBy('fa.date', 'DESC')
			->get()
			->getResultArray();
	}

	/**
	 * Ringkasan monitor dihitung dari hasil filter aktif agar angka headline
	 * tetap konsisten dengan data tabel yang sedang dilihat user.
	 */
	public function getMonitorMetrics(array $filters = []): array
	{
		$rows = $this->buildHistoryQuery($filters)->get()->getResultArray();
		$totalRange = 0.0;
		$bullishTotal = 0;
		$bearishTotal = 0;
		$sidewaysTotal = 0;

		foreach ($rows as $row) {
			$totalRange += (float) ($row['high_low_range'] ?? 0);
			$trend = (string) ($row['trend'] ?? '');
			if ($trend === 'bullish') {
				$bullishTotal++;
			} elseif ($trend === 'bearish') {
				$bearishTotal++;
			} else {
				$sidewaysTotal++;
			}
		}

		$totalRows = count($rows);
		return [
			'total_days' => $totalRows,
			'average_range' => $totalRows > 0 ? $totalRange / $totalRows : 0,
			'bullish_total' => $bullishTotal,
			'bearish_total' => $bearishTotal,
			'sideways_total' => $sidewaysTotal,
		];
	}

	/**
	 * Metrik laporan prediction dipisahkan agar halaman analisis dapat menampilkan
	 * KPI periode aktif tanpa menambah query agregat yang berulang di controller.
	 */
	public function getReportMetrics(array $filters = []): array
	{
		$rows = $this->getAnalysisHistory($filters);
		$totalRows = count($rows);
		$totalRange = 0.0;
		$maxRange = 0.0;
		$bullishTotal = 0;
		$bearishTotal = 0;
		$sidewaysTotal = 0;

		foreach ($rows as $row) {
			$range = (float) ($row['high_low_range'] ?? 0);
			$totalRange += $range;
			if ($range > $maxRange) {
				$maxRange = $range;
			}

			$trend = (string) ($row['trend'] ?? '');
			if ($trend === 'bullish') {
				$bullishTotal++;
			} elseif ($trend === 'bearish') {
				$bearishTotal++;
			} else {
				$sidewaysTotal++;
			}
		}

		return [
			'total_reports' => $totalRows,
			'average_range' => $totalRows > 0 ? $totalRange / $totalRows : 0,
			'max_range' => $maxRange,
			'bullish_total' => $bullishTotal,
			'bearish_total' => $bearishTotal,
			'sideways_total' => $sidewaysTotal,
		];
	}

	/**
	 * Simpan harga per tanggal memakai pola upsert manual agar histori tetap
	 * unik per pair dan bisa diperbarui bila API mengoreksi candle harian.
	 */
	public function upsertPrice(array $data): array
	{
		$existing = $this->getPriceByDate((string) $data['date']);
		$payload = [
			'pair' => $this->pair,
			'date' => (string) $data['date'],
			'open_price' => (float) $data['open_price'],
			'high_price' => (float) $data['high_price'],
			'low_price' => (float) $data['low_price'],
			'close_price' => (float) $data['close_price'],
			'source_api' => (string) $data['source_api'],
		];

		if ($existing) {
			$this->db->table($this->priceTable)
				->where('id_forex_price', $existing['id_forex_price'])
				->update($payload);

			return $this->getPriceByDate((string) $data['date']);
		}

		$payload['created_at'] = date('Y-m-d H:i:s');
		$this->db->table($this->priceTable)->insert($payload);

		return $this->getPriceByDate((string) $data['date']);
	}

	/**
	 * Simpan analisis harian mengikuti harga yang sudah tersimpan agar report
	 * selalu satu banding satu dengan candle yang ada pada histori monitoring.
	 */
	public function upsertAnalysis(array $data): array
	{
		$existing = $this->getAnalysisByDate((string) $data['date']);
		$payload = [
			'pair' => $this->pair,
			'date' => (string) $data['date'],
			'high_low_range' => (float) $data['high_low_range'],
			'trend' => (string) $data['trend'],
			'summary' => (string) $data['summary'],
		];

		if ($existing) {
			$this->db->table($this->analysisTable)
				->where('id_forex_analysis', $existing['id_forex_analysis'])
				->update($payload);

			return $this->getAnalysisByDate((string) $data['date']);
		}

		$payload['created_at'] = date('Y-m-d H:i:s');
		$this->db->table($this->analysisTable)->insert($payload);

		return $this->getAnalysisByDate((string) $data['date']);
	}

	/**
	 * Ambil live price terakhir dari tabel lokal agar monitor bisa tetap
	 * merender snapshot walau provider eksternal sedang gagal diakses.
	 */
	public function getLivePriceRow(): array
	{
		return (array) $this->db->table($this->livePriceTable)
			->where('pair', $this->getPair())
			->get()
			->getRowArray();
	}

	/**
	 * Simpan live price per pair secara idempotent agar polling monitor cukup
	 * membaca satu baris snapshot terbaru tanpa query tambahan.
	 */
	public function upsertLivePrice(array $data): array
	{
		$existing = $this->getLivePriceRow();
		$payload = [
			'pair' => $this->getPair(),
			'current_price' => (float) ($data['current_price'] ?? 0),
			'previous_price' => array_key_exists('previous_price', $data)
				? ($data['previous_price'] !== null ? (float) $data['previous_price'] : null)
				: ($existing['current_price'] ?? null),
			'change_amount' => (float) ($data['change_amount'] ?? 0),
			'change_percent' => (float) ($data['change_percent'] ?? 0),
			'day_open' => (float) ($data['day_open'] ?? 0),
			'day_high' => (float) ($data['day_high'] ?? 0),
			'day_low' => (float) ($data['day_low'] ?? 0),
			'provider' => (string) ($data['provider'] ?? 'unknown'),
			'source_type' => (string) ($data['source_type'] ?? 'cache'),
			'quote_time' => (string) ($data['quote_time'] ?? date('Y-m-d H:i:s')),
			'updated_at' => date('Y-m-d H:i:s'),
		];

		if ($existing) {
			$this->db->table($this->livePriceTable)
				->where('id_forex_live_price', $existing['id_forex_live_price'])
				->update($payload);
		} else {
			$payload['created_at'] = date('Y-m-d H:i:s');
			$this->db->table($this->livePriceTable)->insert($payload);
		}

		return $this->getLivePriceRow();
	}

	/**
	 * Snapshot D/W/M dihitung dari histori tersimpan agar angka high-low tetap
	 * konsisten dan bisa dipakai ulang oleh monitor maupun prediction.
	 */
	public function getHighLowSummary(array $livePrice = []): array
	{
		$referenceDate = $this->resolveReferenceDate($livePrice);
		$latestDaily = $this->getLatestSnapshot($referenceDate);

		return [
			'daily' => $this->buildDailyHighLow($latestDaily, $livePrice),
			'weekly' => $this->buildWeeklyHighLow($referenceDate, $livePrice),
			'monthly' => $this->buildMonthlyHighLow($referenceDate, $livePrice),
		];
	}

	/**
	 * Active alerts dipakai sidebar monitor agar user bisa melihat target yang
	 * masih menunggu trigger tanpa harus pindah ke module lama terpisah.
	 */
	public function getActiveAlerts(int $userId): array
	{
		if ($userId <= 0) {
			return [];
		}

		return $this->db->table($this->alertTable)
			->where('pair', $this->getPair())
			->where('user_id', $userId)
			->where('is_active', 1)
			->orderBy('created_at', 'DESC')
			->get()
			->getResultArray();
	}

	/**
	 * Daftar alert lengkap dipakai monitor agar alert yang dipause tetap dapat
	 * diedit atau diaktifkan ulang tanpa membuat data baru.
	 */
	public function getAlertList(int $userId): array
	{
		if ($userId <= 0) {
			return [];
		}

		return $this->db->table($this->alertTable)
			->where('pair', $this->getPair())
			->where('user_id', $userId)
			->orderBy('created_at', 'DESC')
			->get()
			->getResultArray();
	}

	/**
	 * Setting signal user disimpan di core_setting_user agar mode auto-monitor
	 * persisten tanpa perlu membuat tabel konfigurasi tambahan yang terpisah.
	 */
	public function getSignalSetting(int $userId): array
	{
		if ($userId <= 0) {
			return ['auto_monitor' => 0];
		}

		$row = (array) $this->db->table('core_setting_user')
			->where('id_user', $userId)
			->where('type', 'forex_signal')
			->get()
			->getRowArray();

		if (!$row) {
			return ['auto_monitor' => 0];
		}

		$payload = json_decode((string) ($row['param'] ?? '{}'), true);
		if (!is_array($payload)) {
			$payload = [];
		}

		return [
			'auto_monitor' => !empty($payload['auto_monitor']) ? 1 : 0,
		];
	}

	/**
	 * Update setting auto-monitor dipusatkan di model agar controller prediction
	 * dan service signal sama-sama memakai format payload user yang seragam.
	 */
	public function saveSignalSetting(int $userId, array $settings): bool
	{
		if ($userId <= 0) {
			return false;
		}

		$payload = [
			'auto_monitor' => !empty($settings['auto_monitor']) ? 1 : 0,
		];

		$existing = (array) $this->db->table('core_setting_user')
			->where('id_user', $userId)
			->where('type', 'forex_signal')
			->get()
			->getRowArray();

		if ($existing) {
			return (bool) $this->db->table('core_setting_user')
				->where('id_user', $userId)
				->where('type', 'forex_signal')
				->update([
					'param' => json_encode($payload),
				]);
		}

		return (bool) $this->db->table('core_setting_user')->insert([
			'id_user' => $userId,
			'param' => json_encode($payload),
			'type' => 'forex_signal',
		]);
	}

	/**
	 * Histori signal otomatis memakai tabel alert history yang sama supaya
	 * rekam jejak manual alert dan automation tetap terkumpul di satu tempat.
	 */
	public function insertSystemAlertHistory(int $userId, array $payload): array
	{
		if ($userId <= 0) {
			return [];
		}

		$data = [
			'alert_id' => null,
			'pair' => $this->getPair(),
			'user_id' => $userId,
			'condition_type' => (string) ($payload['condition_type'] ?? 'signal'),
			'target_price' => (float) ($payload['target_price'] ?? 0),
			'triggered_price' => (float) ($payload['triggered_price'] ?? 0),
			'with_sound' => (int) ($payload['with_sound'] ?? 0),
			'message' => (string) ($payload['message'] ?? ''),
			'created_at' => date('Y-m-d H:i:s'),
		];

		$this->db->table($this->alertHistoryTable)->insert($data);
		return $data;
	}

	/**
	 * Histori trigger disediakan untuk monitor agar user dapat audit alert mana
	 * yang pernah kena harga dan kapan kejadian itu berlangsung.
	 */
	public function getAlertHistory(int $userId, int $limit = 100): array
	{
		if ($userId <= 0) {
			return [];
		}

		$builder = $this->db->table($this->alertHistoryTable)
			->where('pair', $this->getPair())
			->where('user_id', $userId)
			->orderBy('created_at', 'DESC');

		if ($limit > 0) {
			$builder->limit($limit);
		}

		return $builder->get()->getResultArray();
	}

	/**
	 * Candle harian per rentang tanggal dipusatkan di model agar summary D/W/M
	 * dan chart historis memakai query yang sama serta mudah diuji ulang.
	 */
	public function getDailyCandlesByDateRange(string $dateFrom, string $dateTo): array
	{
		if ($dateFrom === '' || $dateTo === '') {
			return [];
		}

		return $this->db->table($this->priceTable)
			->where('pair', $this->getPair())
			->where('date >=', $dateFrom)
			->where('date <=', $dateTo)
			->orderBy('date', 'ASC')
			->get()
			->getResultArray();
	}

	/**
	 * Data candle terbaru dipakai saat periode minggu/bulan aktif masih kosong
	 * agar chart tetap bisa menampilkan fallback historis yang paling relevan.
	 */
	public function getRecentDailyCandles(int $limit = 30, ?string $maxDate = null): array
	{
		$builder = $this->db->table($this->priceTable)
			->where('pair', $this->getPair());

		if ($maxDate !== null && $maxDate !== '') {
			$builder->where('date <=', $maxDate);
		}

		$rows = $builder
			->orderBy('date', 'DESC')
			->limit(max(1, $limit))
			->get()
			->getResultArray();

		return array_reverse($rows);
	}

	/**
	 * Detail alert dipakai saat form edit dibuka dari monitor supaya operator
	 * dapat menyesuaikan threshold tanpa berpindah ke module lain.
	 */
	public function getAlertById(int $alertId, int $userId): array
	{
		if ($alertId <= 0 || $userId <= 0) {
			return [];
		}

		return (array) $this->db->table($this->alertTable)
			->where('id_forex_alert', $alertId)
			->where('user_id', $userId)
			->where('pair', $this->getPair())
			->get()
			->getRowArray();
	}

	/**
	 * Simpan alert user dengan validasi deterministic agar kondisi above/below
	 * dan target price selalu tersimpan rapi untuk checker realtime.
	 */
	public function saveAlertData(int $userId): array
	{
		$id = (int) ($this->request->getPost('id') ?? 0);
		$targetPrice = trim((string) ($this->request->getPost('target_price') ?? ''));
		$conditionType = trim((string) ($this->request->getPost('condition_type') ?? 'above'));
		$withSound = $this->request->getPost('with_sound') === '1' ? 1 : 0;
		$isActive = $this->request->getPost('is_active') === '0' ? 0 : 1;
		$existing = $id > 0 ? $this->getAlertById($id, $userId) : [];

		$errors = [];
		if ($userId <= 0) {
			$errors[] = 'User alert tidak valid';
		}

		if (!is_numeric($targetPrice) || (float) $targetPrice <= 0) {
			$errors[] = 'Target price wajib berupa angka lebih besar dari nol';
		}

		if (!in_array($conditionType, ['above', 'below'], true)) {
			$errors[] = 'Kondisi alert tidak valid';
		}

		if ($id > 0 && !$existing) {
			$errors[] = 'Data alert tidak ditemukan';
		}

		if ($errors) {
			return [
				'status' => 'error',
				'message' => implode('<br>', $errors),
			];
		}

		$payload = [
			'pair' => $this->getPair(),
			'user_id' => $userId,
			'condition_type' => $conditionType,
			'target_price' => (float) $targetPrice,
			'with_sound' => $withSound,
			'is_active' => $isActive,
			'updated_at' => date('Y-m-d H:i:s'),
		];

		/**
		 * Saat target atau arah diubah, checker harga harus memulai crossing baru
		 * supaya alert tidak langsung terpicu memakai jejak harga lama.
		 */
		if (!$existing
			|| $existing['condition_type'] !== $conditionType
			|| (float) $existing['target_price'] !== (float) $targetPrice
			|| ((int) ($existing['is_active'] ?? 0) === 0 && $isActive === 1)
		) {
			$payload['last_checked_price'] = null;
			$payload['triggered_at'] = null;
		}

		if ($id > 0) {
			$this->db->table($this->alertTable)
				->where('id_forex_alert', $id)
				->where('user_id', $userId)
				->update($payload);
		} else {
			$payload['created_at'] = date('Y-m-d H:i:s');
			$this->db->table($this->alertTable)->insert($payload);
			$id = (int) $this->db->insertID();
		}

		return [
			'status' => 'ok',
			'message' => 'Alert forex berhasil disimpan',
			'id_forex_alert' => $id,
		];
	}

	/**
	 * Toggle active alert dipisahkan agar monitor bisa mematikan target lama
	 * tanpa menghapus histori trigger yang sudah pernah tercatat.
	 */
	public function toggleAlertStatus(int $alertId, int $userId): array
	{
		$alert = $this->getAlertById($alertId, $userId);
		if (!$alert) {
			return ['status' => 'error', 'message' => 'Data alert tidak ditemukan'];
		}

		$newStatus = (int) ($alert['is_active'] ? 0 : 1);
		$payload = [
			'is_active' => $newStatus,
			'updated_at' => date('Y-m-d H:i:s'),
		];

		/**
		 * Reaktivasi alert mengosongkan checkpoint harga agar crossing berikutnya
		 * dihitung ulang dari refresh realtime setelah alert dihidupkan kembali.
		 */
		if ($newStatus === 1) {
			$payload['last_checked_price'] = null;
			$payload['triggered_at'] = null;
		}

		$this->db->table($this->alertTable)
			->where('id_forex_alert', $alertId)
			->where('user_id', $userId)
			->update($payload);

		return [
			'status' => 'ok',
			'message' => $newStatus === 1 ? 'Alert berhasil diaktifkan' : 'Alert berhasil dinonaktifkan',
		];
	}

	/**
	 * Hapus alert aktif hanya dari tabel master agar histori trigger yang sudah
	 * terjadi tetap tersimpan sebagai catatan penggunaan user.
	 */
	public function deleteAlert(int $alertId, int $userId): array
	{
		$alert = $this->getAlertById($alertId, $userId);
		if (!$alert) {
			return ['status' => 'error', 'message' => 'Data alert tidak ditemukan'];
		}

		$this->db->table($this->alertTable)
			->where('id_forex_alert', $alertId)
			->where('user_id', $userId)
			->delete();

		return ['status' => 'ok', 'message' => 'Alert berhasil dihapus'];
	}

	/**
	 * Checker alert berjalan pada setiap refresh snapshot untuk mendeteksi
	 * crossing threshold berdasarkan harga saat ini vs last_checked_price.
	 */
	public function checkTriggeredAlerts(int $userId, array $livePrice): array
	{
		$currentPrice = (float) ($livePrice['current_price'] ?? 0);
		if ($userId <= 0 || $currentPrice <= 0) {
			return [];
		}

		$alerts = $this->getActiveAlerts($userId);
		$triggeredAlerts = [];
		foreach ($alerts as $alert) {
			$targetPrice = (float) ($alert['target_price'] ?? 0);
			$lastCheckedPrice = isset($alert['last_checked_price']) && $alert['last_checked_price'] !== null
				? (float) $alert['last_checked_price']
				: null;

			$isTriggered = false;
			if ($alert['condition_type'] === 'above') {
				$isTriggered = $lastCheckedPrice !== null
					? ($lastCheckedPrice < $targetPrice && $currentPrice >= $targetPrice)
					: ($currentPrice >= $targetPrice);
			} elseif ($alert['condition_type'] === 'below') {
				$isTriggered = $lastCheckedPrice !== null
					? ($lastCheckedPrice > $targetPrice && $currentPrice <= $targetPrice)
					: ($currentPrice <= $targetPrice);
			}

			$updatePayload = [
				'last_checked_price' => $currentPrice,
				'updated_at' => date('Y-m-d H:i:s'),
			];

			if ($isTriggered) {
				$message = 'Alert ' . strtoupper($this->getPair()) . ' ' . $alert['condition_type']
					. ' ' . number_format($targetPrice, 4, ',', '.')
					. ' terpenuhi di harga ' . number_format($currentPrice, 4, ',', '.');

				$updatePayload['is_active'] = 0;
				$updatePayload['triggered_at'] = date('Y-m-d H:i:s');
				$this->db->table($this->alertHistoryTable)->insert([
					'alert_id' => (int) $alert['id_forex_alert'],
					'pair' => $this->getPair(),
					'user_id' => $userId,
					'condition_type' => $alert['condition_type'],
					'target_price' => $targetPrice,
					'triggered_price' => $currentPrice,
					'with_sound' => (int) ($alert['with_sound'] ?? 0),
					'message' => $message,
					'created_at' => date('Y-m-d H:i:s'),
				]);

				$triggeredAlerts[] = [
					'id_forex_alert' => (int) $alert['id_forex_alert'],
					'message' => $message,
					'with_sound' => (int) ($alert['with_sound'] ?? 0),
					'condition_type' => $alert['condition_type'],
					'target_price' => $targetPrice,
					'triggered_price' => $currentPrice,
				];
			}

			$this->db->table($this->alertTable)
				->where('id_forex_alert', (int) $alert['id_forex_alert'])
				->where('user_id', $userId)
				->update($updatePayload);
		}

		return $triggeredAlerts;
	}

	/**
	 * Trend dasar mengikuti aturan bisnis sederhana yang diminta module agar
	 * hasil analisis konsisten di cron, manual fetch, dan halaman prediction.
	 */
	public function detectTrend(float $openPrice, float $closePrice): string
	{
		if ($closePrice > $openPrice) {
			return 'bullish';
		}

		if ($closePrice < $openPrice) {
			return 'bearish';
		}

		return 'sideways';
	}

	/**
	 * Range dan narasi harian dibentuk terpusat agar teks report selalu seragam
	 * meskipun analisis dibuat dari command cron atau tombol fetch manual.
	 */
	public function buildAnalysisPayload(array $priceData): array
	{
		$openPrice = (float) $priceData['open_price'];
		$highPrice = (float) $priceData['high_price'];
		$lowPrice = (float) $priceData['low_price'];
		$closePrice = (float) $priceData['close_price'];
		$range = max(0, $highPrice - $lowPrice);
		$trend = $this->detectTrend($openPrice, $closePrice);

		$trendLabelMap = [
			'bullish' => 'bullish',
			'bearish' => 'bearish',
			'sideways' => 'sideways',
		];
		$trendLabel = $trendLabelMap[$trend] ?? 'sideways';

		return [
			'date' => $priceData['date'],
			'pair' => $this->pair,
			'high_low_range' => $range,
			'trend' => $trend,
			'summary' => 'Pada ' . $priceData['date'] . ' pasangan GBP/JPY bergerak ' . $trendLabel
				. ' dengan range ' . number_format($range, 4, ',', '.') . '. Harga dibuka di '
				. number_format($openPrice, 4, ',', '.') . ', menyentuh high '
				. number_format($highPrice, 4, ',', '.') . ', low '
				. number_format($lowPrice, 4, ',', '.') . ', lalu ditutup di '
				. number_format($closePrice, 4, ',', '.') . '.',
		];
	}

	/**
	 * Tanggal referensi default memakai tanggal quote realtime bila tersedia,
	 * agar agregasi D/W/M bisa ikut membaca sesi berjalan yang masih aktif.
	 */
	protected function resolveReferenceDate(array $livePrice = []): string
	{
		$quoteTime = trim((string) ($livePrice['quote_time'] ?? ''));
		if ($quoteTime !== '') {
			return date('Y-m-d', strtotime($quoteTime));
		}

		$latest = $this->getLatestSnapshot();
		if (!empty($latest['date'])) {
			return (string) $latest['date'];
		}

		return date('Y-m-d');
	}

	/**
	 * Daily high-low memakai snapshot live lebih dulu bila tersedia, lalu
	 * fallback ke candle harian terakhir agar kartu monitor tetap terisi.
	 */
	protected function buildDailyHighLow(array $latestDaily = [], array $livePrice = []): array
	{
		$highPrice = (float) ($livePrice['day_high'] ?? ($latestDaily['high_price'] ?? 0));
		$lowPrice = (float) ($livePrice['day_low'] ?? ($latestDaily['low_price'] ?? 0));
		$referenceDate = $this->resolveReferenceDate($livePrice);

		return [
			'label' => 'Daily',
			'date_label' => $referenceDate,
			'high_price' => $highPrice,
			'low_price' => $lowPrice,
		];
	}

	/**
	 * Weekly high-low dihitung dari awal minggu Senin berdasarkan histori daily
	 * lalu disesuaikan dengan nilai live day_high/day_low bila sesi berjalan ada.
	 */
	protected function buildWeeklyHighLow(string $referenceDate, array $livePrice = []): array
	{
		$reference = new \DateTimeImmutable($referenceDate);
		$weekStart = $reference->modify('monday this week')->format('Y-m-d');
		$weekEnd = $reference->modify('sunday this week')->format('Y-m-d');
		$rows = $this->db->table($this->priceTable)
			->select('high_price, low_price')
			->where('pair', $this->getPair())
			->where('date >=', $weekStart)
			->where('date <=', $weekEnd)
			->get()
			->getResultArray();

		$highPrice = 0.0;
		$lowPrice = 0.0;
		foreach ($rows as $index => $row) {
			$rowHigh = (float) $row['high_price'];
			$rowLow = (float) $row['low_price'];
			$highPrice = $index === 0 ? $rowHigh : max($highPrice, $rowHigh);
			$lowPrice = $index === 0 ? $rowLow : min($lowPrice, $rowLow);
		}

		if (!empty($livePrice)) {
			$highPrice = $highPrice > 0 ? max($highPrice, (float) ($livePrice['day_high'] ?? 0)) : (float) ($livePrice['day_high'] ?? 0);
			$liveLow = (float) ($livePrice['day_low'] ?? 0);
			if ($liveLow > 0) {
				$lowPrice = $lowPrice > 0 ? min($lowPrice, $liveLow) : $liveLow;
			}
		}

		return [
			'label' => 'Weekly',
			'date_label' => $weekStart . ' s/d ' . $weekEnd,
			'high_price' => $highPrice,
			'low_price' => $lowPrice,
		];
	}

	/**
	 * Monthly high-low memakai seluruh candle bulan berjalan dan menambahkan
	 * high-low live hari ini agar perubahan sesi aktif ikut tercermin.
	 */
	protected function buildMonthlyHighLow(string $referenceDate, array $livePrice = []): array
	{
		$reference = new \DateTimeImmutable($referenceDate);
		$monthStart = $reference->modify('first day of this month')->format('Y-m-d');
		$monthEnd = $reference->modify('last day of this month')->format('Y-m-d');
		$rows = $this->db->table($this->priceTable)
			->select('high_price, low_price')
			->where('pair', $this->getPair())
			->where('date >=', $monthStart)
			->where('date <=', $monthEnd)
			->get()
			->getResultArray();

		$highPrice = 0.0;
		$lowPrice = 0.0;
		foreach ($rows as $index => $row) {
			$rowHigh = (float) $row['high_price'];
			$rowLow = (float) $row['low_price'];
			$highPrice = $index === 0 ? $rowHigh : max($highPrice, $rowHigh);
			$lowPrice = $index === 0 ? $rowLow : min($lowPrice, $rowLow);
		}

		if (!empty($livePrice)) {
			$highPrice = $highPrice > 0 ? max($highPrice, (float) ($livePrice['day_high'] ?? 0)) : (float) ($livePrice['day_high'] ?? 0);
			$liveLow = (float) ($livePrice['day_low'] ?? 0);
			if ($liveLow > 0) {
				$lowPrice = $lowPrice > 0 ? min($lowPrice, $liveLow) : $liveLow;
			}
		}

		return [
			'label' => 'Monthly',
			'date_label' => $reference->format('F Y'),
			'high_price' => $highPrice,
			'low_price' => $lowPrice,
		];
	}
}
