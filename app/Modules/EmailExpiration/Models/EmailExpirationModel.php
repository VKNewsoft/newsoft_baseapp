<?php
/**
 * EmailExpirationModel - Model untuk manajemen masa aktif akun email.
 *
 * Model ini menangani:
 * - pembuatan tabel bila belum ada saat first setup
 * - CRUD akun email dengan soft delete
 * - perhitungan tgl_end yang konsisten dari tgl_start + expiration
 * - aksi renew yang selalu memakai tanggal klik sebagai awal periode baru
 */

namespace App\Modules\EmailExpiration\Models;

class EmailExpirationModel extends \App\Modules\Common\Models\BaseModel
{
	protected $table = 'base_email_expiration';

	public function __construct()
	{
		parent::__construct();

		/**
		 * Tabel baru dijaga idempotent agar module langsung usable setelah pull
		 * walau schema installer belum sempat dijalankan.
		 */
		$this->ensureTableExists();
	}

	/**
	 * Membuat tabel baru jika belum ada tanpa menyentuh tabel existing lain.
	 */
	protected function ensureTableExists(): void
	{
		$sql = "CREATE TABLE IF NOT EXISTS `base_email_expiration` (
			`id_email_expiration` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`email_akun` varchar(190) NOT NULL,
			`expiration_hari` int(11) NOT NULL,
			`tgl_start` date NOT NULL,
			`tgl_end` date NOT NULL,
			`tgl_input` datetime DEFAULT NULL,
			`tgl_edit` datetime DEFAULT NULL,
			`id_user_input` int(10) unsigned DEFAULT NULL,
			`id_user_edit` int(10) unsigned DEFAULT NULL,
			`isDeleted` tinyint(1) NOT NULL DEFAULT 0,
			PRIMARY KEY (`id_email_expiration`),
			KEY `idx_base_email_expiration_deleted` (`isDeleted`),
			KEY `idx_base_email_expiration_end` (`tgl_end`),
			KEY `idx_base_email_expiration_email` (`email_akun`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

		$this->db->query($sql);
	}

	/**
	 * Hitung tgl_end secara konsisten berdasarkan tgl_start + expiration.
	 */
	protected function calculateEndDate(string $startDate, int $expirationDays): string
	{
		$date = new \DateTimeImmutable($startDate);
		return $date->modify('+' . $expirationDays . ' days')->format('Y-m-d');
	}

	/**
	 * Normalisasi data input agar hitungan tanggal selalu konsisten di server.
	 */
	protected function normalizePayload(array $payload): array
	{
		$email = trim((string) ($payload['email_akun'] ?? ''));
		$expirationHari = max(1, (int) ($payload['expiration_hari'] ?? 0));
		$tglStart = trim((string) ($payload['tgl_start'] ?? ''));

		if ($email === '' || $tglStart === '') {
			throw new \RuntimeException('Email/Akun, Expiration, dan Tanggal Mulai wajib diisi');
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			throw new \RuntimeException('Format email tidak valid');
		}

		return [
			'email_akun' => $email,
			'expiration_hari' => $expirationHari,
			'tgl_start' => $tglStart,
			'tgl_end' => $this->calculateEndDate($tglStart, $expirationHari)
		];
	}

	/**
	 * Cegah duplikasi akun email aktif agar data monitoring tetap jelas.
	 */
	protected function emailExists(string $email, int $excludeId = 0): bool
	{
		$builder = $this->db->table($this->table)
			->where('isDeleted', 0)
			->where('email_akun', $email);

		if ($excludeId > 0) {
			$builder->where('id_email_expiration !=', $excludeId);
		}

		return (bool) $builder->countAllResults();
	}

	/**
	 * Ambil detail akun email berdasarkan ID.
	 */
	public function getById(int $id)
	{
		return $this->db->table($this->table)
			->where('id_email_expiration', $id)
			->where('isDeleted', 0)
			->get()
			->getRowArray();
	}

	/**
	 * Simpan data akun email baru/edit dengan hitungan tanggal server-side.
	 */
	public function saveData(): array
	{
		try {
			$payload = $this->normalizePayload($this->request->getPost());
			$id = (int) ($this->request->getPost('id') ?? 0);

			if ($this->emailExists($payload['email_akun'], $id)) {
				return [
					'status' => 'error',
					'message' => 'Akun email sudah terdaftar'
				];
			}

			$this->db->transStart();

			if ($id > 0) {
				$payload['tgl_edit'] = date('Y-m-d H:i:s');
				$payload['id_user_edit'] = (int) ($this->user['id_user'] ?? 0);
				$this->db->table($this->table)->update($payload, ['id_email_expiration' => $id]);
			} else {
				$payload['tgl_input'] = date('Y-m-d H:i:s');
				$payload['id_user_input'] = (int) ($this->user['id_user'] ?? 0);
				$this->db->table($this->table)->insert($payload);
			}

			$this->db->transComplete();

			if ($this->db->transStatus()) {
				return [
					'status' => 'ok',
					'message' => 'Data subscription manager berhasil disimpan'
				];
			}
		} catch (\Throwable $e) {
			return [
				'status' => 'error',
				'message' => $e->getMessage()
			];
		}

		return [
			'status' => 'error',
			'message' => 'Data subscription manager gagal disimpan'
		];
	}

	/**
	 * Renew selalu dimulai dari tanggal klik agar periode baru tidak ambigu.
	 */
	public function renewData(int $id): array
	{
		$data = $this->getById($id);
		if (!$data) {
			return [
				'status' => 'error',
				'message' => 'Data email tidak ditemukan'
			];
		}

		$tglStart = date('Y-m-d');
		$tglEnd = $this->calculateEndDate($tglStart, (int) $data['expiration_hari']);

		$this->db->transStart();
		$this->db->table($this->table)->update([
			'tgl_start' => $tglStart,
			'tgl_end' => $tglEnd,
			'tgl_edit' => date('Y-m-d H:i:s'),
			'id_user_edit' => (int) ($this->user['id_user'] ?? 0)
		], ['id_email_expiration' => $id]);
		$this->db->transComplete();

		if ($this->db->transStatus()) {
			return [
				'status' => 'ok',
				'message' => 'Periode email berhasil diperpanjang'
			];
		}

		return [
			'status' => 'error',
			'message' => 'Periode email gagal diperpanjang'
		];
	}

	/**
	 * Soft delete dipakai agar histori data tidak langsung hilang permanen.
	 */
	public function deleteData(int $id): bool
	{
		return (bool) $this->db->table($this->table)->update([
			'isDeleted' => 1,
			'tgl_edit' => date('Y-m-d H:i:s'),
			'id_user_edit' => (int) ($this->user['id_user'] ?? 0)
		], ['id_email_expiration' => $id]);
	}

	/**
	 * Hitung status masa aktif untuk badge dan highlight tabel.
	 */
	public function buildStatusMeta(string $endDate): array
	{
		$today = new \DateTimeImmutable(date('Y-m-d'));
		$end = new \DateTimeImmutable($endDate);
		$daysRemaining = (int) $today->diff($end)->format('%r%a');

		if ($daysRemaining < 0) {
			return [
				'code' => 'expired',
				'label' => 'Expired',
				'badge_class' => 'bg-danger',
				'days_remaining' => $daysRemaining
			];
		}

		if ($daysRemaining <= 7) {
			return [
				'code' => 'near_expired',
				'label' => 'Mendekati Expired',
				'badge_class' => 'bg-warning text-dark',
				'days_remaining' => $daysRemaining
			];
		}

		return [
			'code' => 'active',
			'label' => 'Aktif',
			'badge_class' => 'bg-success',
			'days_remaining' => $daysRemaining
		];
	}

	/**
	 * Total data aktif untuk DataTables.
	 */
	public function countAllData(): int
	{
		return (int) $this->db->table($this->table)
			->where('isDeleted', 0)
			->countAllResults();
	}

	/**
	 * Menyatukan input filter agar web DataTable dan mobile list memakai
	 * aturan server-side yang sama tanpa menduplikasi logika query.
	 */
	public function getFilterPayload(): array
	{
		return [
			'search' => trim((string) (($this->request->getPost('search')['value'] ?? $this->request->getGet('search') ?? ''))),
			'renew_status' => trim((string) ($this->request->getPost('renew_status') ?? $this->request->getGet('renew_status') ?? 'all')),
			'sort_expiration' => trim((string) ($this->request->getPost('sort_expiration') ?? $this->request->getGet('sort_expiration') ?? 'nearest'))
		];
	}

	/**
	 * Query dasar dijaga terpusat agar filter renew dan urutan expired selalu
	 * konsisten di DataTable web maupun list mobile.
	 */
	protected function buildFilteredListQuery(array $filters = [])
	{
		$searchValue = trim((string) ($filters['search'] ?? ''));
		$renewStatus = trim((string) ($filters['renew_status'] ?? 'all'));
		$sortExpiration = trim((string) ($filters['sort_expiration'] ?? 'nearest'));

		$builder = $this->db->table($this->table)
			->select('id_email_expiration, subscription, email_akun, expiration_hari, tgl_start, tgl_end')
			->select('DATEDIFF(tgl_end, CURDATE()) AS days_remaining', false)
			->where('isDeleted', 0);

		if ($searchValue !== '') {
			$builder->groupStart()
				->like('subscription', $searchValue)
				->orLike('email_akun', $searchValue)
				->orLike('tgl_start', $searchValue)
				->orLike('tgl_end', $searchValue)
				->groupEnd();
		}

		/**
		 * Status renew mengikuti kebutuhan monitoring agar akun yang sudah
		 * jatuh tempo hari ini atau sebelumnya langsung masuk kategori perlu renew.
		 */
		if ($renewStatus === 'ready') {
			$builder->where('DATE(tgl_end) <= CURDATE()', null, false);
		} elseif ($renewStatus === 'not_ready') {
			$builder->where('DATE(tgl_end) > CURDATE()', null, false);
		}

		/**
		 * Urutan memakai jarak absolut dari hari ini agar data paling dekat
		 * atau paling jauh dari current date tampil sesuai kebutuhan filter.
		 */
		if ($sortExpiration === 'longest') {
			$builder->orderBy('ABS(DATEDIFF(tgl_end, CURDATE()))', 'DESC', false);
		} else {
			$builder->orderBy('ABS(DATEDIFF(tgl_end, CURDATE()))', 'ASC', false);
		}

		return $builder;
	}

	/**
	 * Data list DataTables dengan pencarian dan sorting yang aman.
	 */
	public function getListData(): array
	{
		$columns = $this->request->getPost('columns') ?? [];
		$allowedColumns = ['subscription','email_akun', 'expiration_hari', 'tgl_start', 'tgl_end'];
		$filters = $this->getFilterPayload();
		$builder = $this->buildFilteredListQuery($filters);

		$totalFiltered = $builder->countAllResults(false);

		/**
		 * DataTables tetap boleh mengirim order bawaan, namun filter urutan
		 * module diprioritaskan untuk kolom tgl_end agar hasilnya konsisten.
		 */
		$orderData = $this->request->getPost('order');
		if ($orderData && isset($columns[$orderData[0]['column']])) {
			$orderColumn = $columns[$orderData[0]['column']]['data'] ?? '';
			$orderDir = strtoupper($orderData[0]['dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
			if (in_array($orderColumn, $allowedColumns, true) && $orderColumn !== 'tgl_end') {
				$builder->orderBy($orderColumn, $orderDir);
			}
		}

		$start = (int) ($this->request->getPost('start') ?? 0);
		$length = (int) ($this->request->getPost('length') ?? 10);
		$builder->limit($length, $start);

		return [
			'data' => $builder->get()->getResultArray(),
			'total_filtered' => $totalFiltered
		];
	}

	/**
	 * Data mobile dipisah dari DataTable agar render card bisa memakai pola
	 * incremental load more tanpa mengubah query utama module.
	 */
	public function getMobileListData(): array
	{
		$filters = $this->getFilterPayload();
		$builder = $this->buildFilteredListQuery($filters);
		$totalFiltered = $builder->countAllResults(false);

		$offset = max(0, (int) ($this->request->getGet('offset') ?? 0));
		$limit = max(1, min(20, (int) ($this->request->getGet('limit') ?? 10)));
		$builder->limit($limit, $offset);

		return [
			'data' => $builder->get()->getResultArray(),
			'total_filtered' => $totalFiltered,
			'offset' => $offset,
			'limit' => $limit
		];
	}
}
