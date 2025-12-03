<?php
/**
 * WilayahModel - Model untuk manajemen data wilayah Indonesia
 * 
 * Model ini menangani semua operasi database yang berkaitan dengan wilayah
 * hierarkis Indonesia (Provinsi, Kabupaten, Kecamatan, Kelurahan)
 * termasuk CRUD dan query relationship antar entitas
 * 
 * @package App\Models\Builtin
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Models\Builtin;

class WilayahModel extends \App\Models\BaseModel
{
	/**
	 * Constructor
	 */
	public function __construct() 
	{
		parent::__construct();
	}
	
	// ========================================================================
	// PROVINSI OPERATIONS
	// ========================================================================
	
	/**
	 * Mendapatkan daftar provinsi untuk DataTables dengan filter dan search
	 * 
	 * @param string $where Kondisi WHERE tambahan
	 * @return array Array berisi data provinsi dan total filtered
	 */
	public function getListProvinsi($where) 
	{
		$columns = $this->request->getPost('columns');
		
		// Daftar kolom yang diizinkan untuk sorting/searching (whitelist)
		$allowedColumns = ['id_wilayah_propinsi', 'nama_propinsi'];
		
		// Proses pencarian (Search)
		$searchValue = $this->request->getPost('search')['value'] ?? '';
		$searchValue = $this->db->escapeString($searchValue);
		
		if ($searchValue) {
			$whereCols = [];
			foreach ($columns as $col) {
				$colName = $col['data'] ?? '';
				
				// Skip kolom yang mengandung 'ignore' dan validasi kolom yang diizinkan
				if (strpos($colName, 'ignore') !== false) {
					continue;
				}
				
				if (in_array($colName, $allowedColumns)) {
					$whereCols[] = $colName . " LIKE '%" . $searchValue . "%'";
				}
			}
			
			if (!empty($whereCols)) {
				$where .= ' AND (' . implode(' OR ', $whereCols) . ')';
			}
		}
		
		// Proses ordering dan limit
		$start = (int) ($this->request->getPost('start') ?: 0);
		$length = (int) ($this->request->getPost('length') ?: 10);
		
		$orderData = $this->request->getPost('order');
		$order = '';
		
		if (!empty($orderData) && is_array($orderData)) {
			$columnIndex = (int) $orderData[0]['column'];
			$columnName = $columns[$columnIndex]['data'] ?? '';
			$direction = strtoupper($orderData[0]['dir']) === 'DESC' ? 'DESC' : 'ASC';
			
			// Validasi kolom untuk ordering
			if (in_array($columnName, $allowedColumns)) {
				$order = " ORDER BY {$columnName} {$direction} LIMIT {$start}, {$length}";
			} else {
				$order = " ORDER BY nama_propinsi ASC LIMIT {$start}, {$length}";
			}
		} else {
			$order = " ORDER BY nama_propinsi ASC LIMIT {$start}, {$length}";
		}
		
		$whereClause = $where ?: ' WHERE 1 = 1';
		
		// Query untuk menghitung total filtered
		$sql = "SELECT COUNT(*) as jml FROM core_wilayah_propinsi {$whereClause}";
		$query = $this->db->query($sql)->getRowArray();
		$totalFiltered = $query['jml'];
		
		// Query untuk mengambil data provinsi
		$sql = "SELECT * FROM core_wilayah_propinsi {$whereClause} {$order}";
		$data = $this->db->query($sql)->getResultArray();
		
		return [
			'data' => $data, 
			'total_filtered' => $totalFiltered
		];
	}
	
	/**
	 * Hitung total semua provinsi (untuk DataTables)
	 * 
	 * @param string|null $where Kondisi WHERE tambahan
	 * @return int Jumlah provinsi
	 */
	public function countAllProvinsi($where = null) 
	{
		$whereClause = $where ?: ' WHERE 1 = 1';
		$sql = "SELECT COUNT(*) as jml FROM core_wilayah_propinsi" . $whereClause;
		$query = $this->db->query($sql)->getRow();
		
		return $query->jml;
	}
	
	/**
	 * Mendapatkan data provinsi berdasarkan ID
	 * 
	 * @param int $id ID provinsi
	 * @return array|null Data provinsi atau null jika tidak ditemukan
	 */
	public function getProvinsiById($id) 
	{
		return $this->db->table('core_wilayah_propinsi')
			->where('id_wilayah_propinsi', $id)
			->get()
			->getRowArray();
	}
	
	/**
	 * Mendapatkan daftar provinsi untuk dropdown (key-value pair)
	 * 
	 * @return array Daftar provinsi dengan key id_wilayah_propinsi
	 */
	public function getProvinsiList() 
	{
		$query = $this->db->table('core_wilayah_propinsi')
			->orderBy('nama_propinsi', 'ASC')
			->get()
			->getResultArray();
		
		$result = [];
		foreach ($query as $val) {
			$result[$val['id_wilayah_propinsi']] = $val['nama_propinsi'];
		}
		
		return $result;
	}
	
	/**
	 * Simpan data provinsi (create/update)
	 * 
	 * @return array Status dan pesan hasil operasi
	 */
	public function saveProvinsi() 
	{ 
		$fields = ['nama_propinsi'];
		
		// Ambil data dari POST sesuai field yang diizinkan
		$dataDb = [];
		foreach ($fields as $field) {
			$dataDb[$field] = $this->request->getPost($field);
		}
		
		// Mulai database transaction
		$this->db->transStart();
		
		$id = $this->request->getPost('id');
		
		// Update atau Insert
		if ($id) {
			// Update existing data
			$this->db->table('core_wilayah_propinsi')
				->where('id_wilayah_propinsi', $id)
				->update($dataDb);
			
			$message = 'Data provinsi berhasil diupdate';
		} else {
			// Insert new data
			$this->db->table('core_wilayah_propinsi')->insert($dataDb);
			$id = $this->db->insertID();
			
			$message = 'Data provinsi berhasil ditambahkan';
		}
		
		// Commit transaction
		$this->db->transComplete();
		
		if ($this->db->transStatus() === false) {
			return [
				'status' => 'error',
				'message' => 'Terjadi kesalahan saat menyimpan data'
			];
		}
		
		return [
			'status' => 'ok',
			'message' => $message,
			'id' => $id
		];
	}
	
	/**
	 * Hapus data provinsi
	 * 
	 * @return bool True jika berhasil, false jika gagal
	 */
	public function deleteProvinsi() 
	{
		$id = $this->request->getPost('delete');
		
		if (!$id) {
			return false;
		}
		
		$this->db->transStart();
		
		$this->db->table('core_wilayah_propinsi')
			->where('id_wilayah_propinsi', $id)
			->delete();
		
		$this->db->transComplete();
		
		return $this->db->transStatus();
	}
	
	// ========================================================================
	// KABUPATEN OPERATIONS
	// ========================================================================
	
	/**
	 * Mendapatkan daftar kabupaten untuk DataTables dengan filter dan search
	 * 
	 * @param string $where Kondisi WHERE tambahan
	 * @return array Array berisi data kabupaten dan total filtered
	 */
	public function getListKabupaten($where) 
	{
		$columns = $this->request->getPost('columns');
		
		// Daftar kolom yang diizinkan untuk sorting/searching (whitelist)
		$allowedColumns = ['id_wilayah_kabupaten', 'nama_kabupaten', 'nama_propinsi'];
		
		// Proses pencarian (Search)
		$searchValue = $this->request->getPost('search')['value'] ?? '';
		$searchValue = $this->db->escapeString($searchValue);
		
		if ($searchValue) {
			$whereCols = [];
			foreach ($columns as $col) {
				$colName = $col['data'] ?? '';
				
				if (strpos($colName, 'ignore') !== false) {
					continue;
				}
				
				if (in_array($colName, $allowedColumns)) {
					$whereCols[] = $colName . " LIKE '%" . $searchValue . "%'";
				}
			}
			
			if (!empty($whereCols)) {
				$where .= ' AND (' . implode(' OR ', $whereCols) . ')';
			}
		}
		
		// Proses ordering dan limit
		$start = (int) ($this->request->getPost('start') ?: 0);
		$length = (int) ($this->request->getPost('length') ?: 10);
		
		$orderData = $this->request->getPost('order');
		$order = '';
		
		if (!empty($orderData) && is_array($orderData)) {
			$columnIndex = (int) $orderData[0]['column'];
			$columnName = $columns[$columnIndex]['data'] ?? '';
			$direction = strtoupper($orderData[0]['dir']) === 'DESC' ? 'DESC' : 'ASC';
			
			if (in_array($columnName, $allowedColumns)) {
				$order = " ORDER BY {$columnName} {$direction} LIMIT {$start}, {$length}";
			} else {
				$order = " ORDER BY nama_kabupaten ASC LIMIT {$start}, {$length}";
			}
		} else {
			$order = " ORDER BY nama_kabupaten ASC LIMIT {$start}, {$length}";
		}
		
		$whereClause = $where ?: ' WHERE 1 = 1';
		
		// Query untuk menghitung total filtered
		$sql = "SELECT COUNT(*) as jml FROM core_wilayah_kabupaten 
				LEFT JOIN core_wilayah_propinsi USING(id_wilayah_propinsi)
				{$whereClause}";
		$query = $this->db->query($sql)->getRowArray();
		$totalFiltered = $query['jml'];
		
		// Query untuk mengambil data kabupaten dengan nama provinsi
		$sql = "SELECT core_wilayah_kabupaten.*, core_wilayah_propinsi.nama_propinsi 
				FROM core_wilayah_kabupaten 
				LEFT JOIN core_wilayah_propinsi USING(id_wilayah_propinsi)
				{$whereClause} {$order}";
		$data = $this->db->query($sql)->getResultArray();
		
		return [
			'data' => $data, 
			'total_filtered' => $totalFiltered
		];
	}
	
	/**
	 * Hitung total semua kabupaten (untuk DataTables)
	 * 
	 * @param string|null $where Kondisi WHERE tambahan
	 * @return int Jumlah kabupaten
	 */
	public function countAllKabupaten($where = null) 
	{
		$whereClause = $where ?: ' WHERE 1 = 1';
		$sql = "SELECT COUNT(*) as jml FROM core_wilayah_kabupaten" . $whereClause;
		$query = $this->db->query($sql)->getRow();
		
		return $query->jml;
	}
	
	/**
	 * Mendapatkan data kabupaten berdasarkan ID dengan relasi provinsi
	 * 
	 * @param int $id ID kabupaten
	 * @return array|null Data kabupaten atau null jika tidak ditemukan
	 */
	public function getKabupatenById($id) 
	{
		return $this->db->table('core_wilayah_kabupaten')
			->select('core_wilayah_kabupaten.*, core_wilayah_propinsi.nama_propinsi')
			->join('core_wilayah_propinsi', 'core_wilayah_kabupaten.id_wilayah_propinsi = core_wilayah_propinsi.id_wilayah_propinsi', 'left')
			->where('id_wilayah_kabupaten', $id)
			->get()
			->getRowArray();
	}
	
	/**
	 * Mendapatkan daftar kabupaten berdasarkan provinsi untuk dropdown
	 * 
	 * @param int $idProvinsi ID provinsi
	 * @return array Daftar kabupaten dengan key id_wilayah_kabupaten
	 */
	public function getKabupatenListByProvinsi($idProvinsi) 
	{
		$query = $this->db->table('core_wilayah_kabupaten')
			->where('id_wilayah_propinsi', $idProvinsi)
			->orderBy('nama_kabupaten', 'ASC')
			->get()
			->getResultArray();
		
		$result = [];
		foreach ($query as $val) {
			$result[$val['id_wilayah_kabupaten']] = $val['nama_kabupaten'];
		}
		
		return $result;
	}
	
	/**
	 * Simpan data kabupaten (create/update)
	 * 
	 * @return array Status dan pesan hasil operasi
	 */
	public function saveKabupaten() 
	{ 
		$fields = ['id_wilayah_propinsi', 'nama_kabupaten'];
		
		$dataDb = [];
		foreach ($fields as $field) {
			$dataDb[$field] = $this->request->getPost($field);
		}
		
		$this->db->transStart();
		
		$id = $this->request->getPost('id');
		
		if ($id) {
			$this->db->table('core_wilayah_kabupaten')
				->where('id_wilayah_kabupaten', $id)
				->update($dataDb);
			
			$message = 'Data kabupaten berhasil diupdate';
		} else {
			$this->db->table('core_wilayah_kabupaten')->insert($dataDb);
			$id = $this->db->insertID();
			
			$message = 'Data kabupaten berhasil ditambahkan';
		}
		
		$this->db->transComplete();
		
		if ($this->db->transStatus() === false) {
			return [
				'status' => 'error',
				'message' => 'Terjadi kesalahan saat menyimpan data'
			];
		}
		
		return [
			'status' => 'ok',
			'message' => $message,
			'id' => $id
		];
	}
	
	/**
	 * Hapus data kabupaten
	 * 
	 * @return bool True jika berhasil, false jika gagal
	 */
	public function deleteKabupaten() 
	{
		$id = $this->request->getPost('delete');
		
		if (!$id) {
			return false;
		}
		
		$this->db->transStart();
		
		$this->db->table('core_wilayah_kabupaten')
			->where('id_wilayah_kabupaten', $id)
			->delete();
		
		$this->db->transComplete();
		
		return $this->db->transStatus();
	}
	
	// ========================================================================
	// KECAMATAN OPERATIONS
	// ========================================================================
	
	/**
	 * Mendapatkan daftar kecamatan untuk DataTables dengan filter dan search
	 * 
	 * @param string $where Kondisi WHERE tambahan
	 * @return array Array berisi data kecamatan dan total filtered
	 */
	public function getListKecamatan($where) 
	{
		$columns = $this->request->getPost('columns');
		
		$allowedColumns = ['id_wilayah_kecamatan', 'nama_kecamatan', 'nama_kabupaten', 'nama_propinsi'];
		
		// Proses pencarian (Search)
		$searchValue = $this->request->getPost('search')['value'] ?? '';
		$searchValue = $this->db->escapeString($searchValue);
		
		if ($searchValue) {
			$whereCols = [];
			foreach ($columns as $col) {
				$colName = $col['data'] ?? '';
				
				if (strpos($colName, 'ignore') !== false) {
					continue;
				}
				
				if (in_array($colName, $allowedColumns)) {
					$whereCols[] = $colName . " LIKE '%" . $searchValue . "%'";
				}
			}
			
			if (!empty($whereCols)) {
				$where .= ' AND (' . implode(' OR ', $whereCols) . ')';
			}
		}
		
		// Proses ordering dan limit
		$start = (int) ($this->request->getPost('start') ?: 0);
		$length = (int) ($this->request->getPost('length') ?: 10);
		
		$orderData = $this->request->getPost('order');
		$order = '';
		
		if (!empty($orderData) && is_array($orderData)) {
			$columnIndex = (int) $orderData[0]['column'];
			$columnName = $columns[$columnIndex]['data'] ?? '';
			$direction = strtoupper($orderData[0]['dir']) === 'DESC' ? 'DESC' : 'ASC';
			
			if (in_array($columnName, $allowedColumns)) {
				$order = " ORDER BY {$columnName} {$direction} LIMIT {$start}, {$length}";
			} else {
				$order = " ORDER BY nama_kecamatan ASC LIMIT {$start}, {$length}";
			}
		} else {
			$order = " ORDER BY nama_kecamatan ASC LIMIT {$start}, {$length}";
		}
		
		$whereClause = $where ?: ' WHERE 1 = 1';
		
		// Query untuk menghitung total filtered
		$sql = "SELECT COUNT(*) as jml FROM core_wilayah_kecamatan 
				LEFT JOIN core_wilayah_kabupaten USING(id_wilayah_kabupaten)
				LEFT JOIN core_wilayah_propinsi USING(id_wilayah_propinsi)
				{$whereClause}";
		$query = $this->db->query($sql)->getRowArray();
		$totalFiltered = $query['jml'];
		
		// Query untuk mengambil data kecamatan dengan nama kabupaten dan provinsi
		$sql = "SELECT core_wilayah_kecamatan.*, 
				core_wilayah_kabupaten.nama_kabupaten,
				core_wilayah_propinsi.nama_propinsi 
				FROM core_wilayah_kecamatan 
				LEFT JOIN core_wilayah_kabupaten USING(id_wilayah_kabupaten)
				LEFT JOIN core_wilayah_propinsi USING(id_wilayah_propinsi)
				{$whereClause} {$order}";
		$data = $this->db->query($sql)->getResultArray();
		
		return [
			'data' => $data, 
			'total_filtered' => $totalFiltered
		];
	}
	
	/**
	 * Hitung total semua kecamatan (untuk DataTables)
	 * 
	 * @param string|null $where Kondisi WHERE tambahan
	 * @return int Jumlah kecamatan
	 */
	public function countAllKecamatan($where = null) 
	{
		$whereClause = $where ?: ' WHERE 1 = 1';
		$sql = "SELECT COUNT(*) as jml FROM core_wilayah_kecamatan" . $whereClause;
		$query = $this->db->query($sql)->getRow();
		
		return $query->jml;
	}
	
	/**
	 * Mendapatkan data kecamatan berdasarkan ID dengan relasi kabupaten dan provinsi
	 * 
	 * @param int $id ID kecamatan
	 * @return array|null Data kecamatan atau null jika tidak ditemukan
	 */
	public function getKecamatanById($id) 
	{
		return $this->db->table('core_wilayah_kecamatan')
			->select('core_wilayah_kecamatan.*, 
					  core_wilayah_kabupaten.nama_kabupaten, 
					  core_wilayah_kabupaten.id_wilayah_propinsi,
					  core_wilayah_propinsi.nama_propinsi')
			->join('core_wilayah_kabupaten', 'core_wilayah_kecamatan.id_wilayah_kabupaten = core_wilayah_kabupaten.id_wilayah_kabupaten', 'left')
			->join('core_wilayah_propinsi', 'core_wilayah_kabupaten.id_wilayah_propinsi = core_wilayah_propinsi.id_wilayah_propinsi', 'left')
			->where('id_wilayah_kecamatan', $id)
			->get()
			->getRowArray();
	}
	
	/**
	 * Mendapatkan daftar kecamatan berdasarkan kabupaten untuk dropdown
	 * 
	 * @param int $idKabupaten ID kabupaten
	 * @return array Daftar kecamatan dengan key id_wilayah_kecamatan
	 */
	public function getKecamatanListByKabupaten($idKabupaten) 
	{
		$query = $this->db->table('core_wilayah_kecamatan')
			->where('id_wilayah_kabupaten', $idKabupaten)
			->orderBy('nama_kecamatan', 'ASC')
			->get()
			->getResultArray();
		
		$result = [];
		foreach ($query as $val) {
			$result[$val['id_wilayah_kecamatan']] = $val['nama_kecamatan'];
		}
		
		return $result;
	}
	
	/**
	 * Simpan data kecamatan (create/update)
	 * 
	 * @return array Status dan pesan hasil operasi
	 */
	public function saveKecamatan() 
	{ 
		$fields = ['id_wilayah_kabupaten', 'nama_kecamatan'];
		
		$dataDb = [];
		foreach ($fields as $field) {
			$dataDb[$field] = $this->request->getPost($field);
		}
		
		$this->db->transStart();
		
		$id = $this->request->getPost('id');
		
		if ($id) {
			$this->db->table('core_wilayah_kecamatan')
				->where('id_wilayah_kecamatan', $id)
				->update($dataDb);
			
			$message = 'Data kecamatan berhasil diupdate';
		} else {
			$this->db->table('core_wilayah_kecamatan')->insert($dataDb);
			$id = $this->db->insertID();
			
			$message = 'Data kecamatan berhasil ditambahkan';
		}
		
		$this->db->transComplete();
		
		if ($this->db->transStatus() === false) {
			return [
				'status' => 'error',
				'message' => 'Terjadi kesalahan saat menyimpan data'
			];
		}
		
		return [
			'status' => 'ok',
			'message' => $message,
			'id' => $id
		];
	}
	
	/**
	 * Hapus data kecamatan
	 * 
	 * @return bool True jika berhasil, false jika gagal
	 */
	public function deleteKecamatan() 
	{
		$id = $this->request->getPost('delete');
		
		if (!$id) {
			return false;
		}
		
		$this->db->transStart();
		
		$this->db->table('core_wilayah_kecamatan')
			->where('id_wilayah_kecamatan', $id)
			->delete();
		
		$this->db->transComplete();
		
		return $this->db->transStatus();
	}
	
	// ========================================================================
	// KELURAHAN OPERATIONS
	// ========================================================================
	
	/**
	 * Mendapatkan daftar kelurahan untuk DataTables dengan filter dan search
	 * 
	 * @param string $where Kondisi WHERE tambahan
	 * @return array Array berisi data kelurahan dan total filtered
	 */
	public function getListKelurahan($where) 
	{
		$columns = $this->request->getPost('columns');
		
		$allowedColumns = ['id_wilayah_kelurahan', 'nama_kelurahan', 'nama_kecamatan', 'nama_kabupaten', 'nama_propinsi'];
		
		// Proses pencarian (Search)
		$searchValue = $this->request->getPost('search')['value'] ?? '';
		$searchValue = $this->db->escapeString($searchValue);
		
		if ($searchValue) {
			$whereCols = [];
			foreach ($columns as $col) {
				$colName = $col['data'] ?? '';
				
				if (strpos($colName, 'ignore') !== false) {
					continue;
				}
				
				if (in_array($colName, $allowedColumns)) {
					$whereCols[] = $colName . " LIKE '%" . $searchValue . "%'";
				}
			}
			
			if (!empty($whereCols)) {
				$where .= ' AND (' . implode(' OR ', $whereCols) . ')';
			}
		}
		
		// Proses ordering dan limit
		$start = (int) ($this->request->getPost('start') ?: 0);
		$length = (int) ($this->request->getPost('length') ?: 10);
		
		$orderData = $this->request->getPost('order');
		$order = '';
		
		if (!empty($orderData) && is_array($orderData)) {
			$columnIndex = (int) $orderData[0]['column'];
			$columnName = $columns[$columnIndex]['data'] ?? '';
			$direction = strtoupper($orderData[0]['dir']) === 'DESC' ? 'DESC' : 'ASC';
			
			if (in_array($columnName, $allowedColumns)) {
				$order = " ORDER BY {$columnName} {$direction} LIMIT {$start}, {$length}";
			} else {
				$order = " ORDER BY nama_kelurahan ASC LIMIT {$start}, {$length}";
			}
		} else {
			$order = " ORDER BY nama_kelurahan ASC LIMIT {$start}, {$length}";
		}
		
		$whereClause = $where ?: ' WHERE 1 = 1';
		
		// Query untuk menghitung total filtered
		$sql = "SELECT COUNT(*) as jml FROM core_wilayah_kelurahan 
				LEFT JOIN core_wilayah_kecamatan USING(id_wilayah_kecamatan)
				LEFT JOIN core_wilayah_kabupaten USING(id_wilayah_kabupaten)
				LEFT JOIN core_wilayah_propinsi USING(id_wilayah_propinsi)
				{$whereClause}";
		$query = $this->db->query($sql)->getRowArray();
		$totalFiltered = $query['jml'];
		
		// Query untuk mengambil data kelurahan dengan semua relasi
		$sql = "SELECT core_wilayah_kelurahan.*, 
				core_wilayah_kecamatan.nama_kecamatan,
				core_wilayah_kabupaten.nama_kabupaten,
				core_wilayah_propinsi.nama_propinsi 
				FROM core_wilayah_kelurahan 
				LEFT JOIN core_wilayah_kecamatan USING(id_wilayah_kecamatan)
				LEFT JOIN core_wilayah_kabupaten USING(id_wilayah_kabupaten)
				LEFT JOIN core_wilayah_propinsi USING(id_wilayah_propinsi)
				{$whereClause} {$order}";
		$data = $this->db->query($sql)->getResultArray();
		
		return [
			'data' => $data, 
			'total_filtered' => $totalFiltered
		];
	}
	
	/**
	 * Hitung total semua kelurahan (untuk DataTables)
	 * 
	 * @param string|null $where Kondisi WHERE tambahan
	 * @return int Jumlah kelurahan
	 */
	public function countAllKelurahan($where = null) 
	{
		$whereClause = $where ?: ' WHERE 1 = 1';
		$sql = "SELECT COUNT(*) as jml FROM core_wilayah_kelurahan" . $whereClause;
		$query = $this->db->query($sql)->getRow();
		
		return $query->jml;
	}
	
	/**
	 * Mendapatkan data kelurahan berdasarkan ID dengan semua relasi
	 * 
	 * @param int $id ID kelurahan
	 * @return array|null Data kelurahan atau null jika tidak ditemukan
	 */
	public function getKelurahanById($id) 
	{
		return $this->db->table('core_wilayah_kelurahan')
			->select('core_wilayah_kelurahan.*, 
					  core_wilayah_kecamatan.nama_kecamatan,
					  core_wilayah_kecamatan.id_wilayah_kabupaten,
					  core_wilayah_kabupaten.nama_kabupaten,
					  core_wilayah_kabupaten.id_wilayah_propinsi,
					  core_wilayah_propinsi.nama_propinsi')
			->join('core_wilayah_kecamatan', 'core_wilayah_kelurahan.id_wilayah_kecamatan = core_wilayah_kecamatan.id_wilayah_kecamatan', 'left')
			->join('core_wilayah_kabupaten', 'core_wilayah_kecamatan.id_wilayah_kabupaten = core_wilayah_kabupaten.id_wilayah_kabupaten', 'left')
			->join('core_wilayah_propinsi', 'core_wilayah_kabupaten.id_wilayah_propinsi = core_wilayah_propinsi.id_wilayah_propinsi', 'left')
			->where('id_wilayah_kelurahan', $id)
			->get()
			->getRowArray();
	}
	
	/**
	 * Mendapatkan daftar kelurahan berdasarkan kecamatan untuk dropdown
	 * 
	 * @param int $idKecamatan ID kecamatan
	 * @return array Daftar kelurahan dengan key id_wilayah_kelurahan
	 */
	public function getKelurahanListByKecamatan($idKecamatan) 
	{
		$query = $this->db->table('core_wilayah_kelurahan')
			->where('id_wilayah_kecamatan', $idKecamatan)
			->orderBy('nama_kelurahan', 'ASC')
			->get()
			->getResultArray();
		
		$result = [];
		foreach ($query as $val) {
			$result[$val['id_wilayah_kelurahan']] = $val['nama_kelurahan'];
		}
		
		return $result;
	}
	
	/**
	 * Simpan data kelurahan (create/update)
	 * 
	 * @return array Status dan pesan hasil operasi
	 */
	public function saveKelurahan() 
	{ 
		$fields = ['id_wilayah_kecamatan', 'nama_kelurahan'];
		
		$dataDb = [];
		foreach ($fields as $field) {
			$dataDb[$field] = $this->request->getPost($field);
		}
		
		$this->db->transStart();
		
		$id = $this->request->getPost('id');
		
		if ($id) {
			$this->db->table('core_wilayah_kelurahan')
				->where('id_wilayah_kelurahan', $id)
				->update($dataDb);
			
			$message = 'Data kelurahan berhasil diupdate';
		} else {
			$this->db->table('core_wilayah_kelurahan')->insert($dataDb);
			$id = $this->db->insertID();
			
			$message = 'Data kelurahan berhasil ditambahkan';
		}
		
		$this->db->transComplete();
		
		if ($this->db->transStatus() === false) {
			return [
				'status' => 'error',
				'message' => 'Terjadi kesalahan saat menyimpan data'
			];
		}
		
		return [
			'status' => 'ok',
			'message' => $message,
			'id' => $id
		];
	}
	
	/**
	 * Hapus data kelurahan
	 * 
	 * @return bool True jika berhasil, false jika gagal
	 */
	public function deleteKelurahan() 
	{
		$id = $this->request->getPost('delete');
		
		if (!$id) {
			return false;
		}
		
		$this->db->transStart();
		
		$this->db->table('core_wilayah_kelurahan')
			->where('id_wilayah_kelurahan', $id)
			->delete();
		
		$this->db->transComplete();
		
		return $this->db->transStatus();
	}
}
