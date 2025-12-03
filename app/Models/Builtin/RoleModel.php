<?php
/**
 * RoleModel - Model untuk manajemen role/peran user
 * 
 * Model ini menangani semua operasi database yang berkaitan dengan role
 * termasuk CRUD role, permission, dan module assignment
 * 
 * @package App\Models\Builtin
 * @year 2020-2025
 */

namespace App\Models\Builtin;

class RoleModel extends \App\Models\BaseModel
{
	/**
	 * Mendapatkan semua module yang tersedia
	 * 
	 * @return array Daftar module
	 */
	public function getAllModules() 
	{
		return $this->db->table('core_module')
			->get()
			->getResultArray();
	}
	
	/**
	 * Mendapatkan semua status module
	 * 
	 * @return array Daftar status module
	 */
	public function getModuleStatus() 
	{
		return $this->db->table('core_module_status')
			->get()
			->getResultArray();
	}
	
	/**
	 * Mendapatkan list module role
	 * 
	 * @return array Daftar module dengan role
	 */
	public function listModuleRole() 
	{
		return $this->db->table('core_role')
			->join('core_module', 'core_role.id_module = core_module.id_module')
			->get()
			->getResultArray();
	}
	
	/**
	 * Mendapatkan semua role yang tersedia
	 * 
	 * @return array Daftar role
	 */
	public function getAllRole() 
	{
		return $this->db->table('core_role')
			->get()
			->getResultArray();
	}
	
	/**
	 * Mendapatkan list module dengan permission dan status
	 * 
	 * @return array Daftar module dengan detail
	 */
	public function getListModules() 
	{
		return $this->db->table('core_role_module_permission')
			->join('core_module_permission', 'core_role_module_permission.id_module_permission = core_module_permission.id_module_permission')
			->join('core_module', 'core_module_permission.id_module = core_module.id_module')
			->join('core_module_status', 'core_module.id_module_status = core_module_status.id_module_status')
			->orderBy('nama_module', 'ASC')
			->get()
			->getResultArray();
	}
	
	/**
	 * Mendapatkan data role berdasarkan ID (untuk edit)
	 * 
	 * @return array Data role
	 */
	public function getRole() 
	{
		$idRole = $this->request->getGet('id');
		
		$result = $this->db->table('core_role')
			->where('id_role', $idRole)
			->get()
			->getRowArray();
		
		return $result ?: [];
	}
	
	/**
	 * Simpan data role (create/update)
	 * 
	 * @return array Status dan pesan hasil operasi
	 */
	public function saveData() 
	{
		$fields = ['nama_role', 'judul_role', 'keterangan', 'id_module'];

		$dataDb = [];
		foreach ($fields as $field) {
			$dataDb[$field] = $this->request->getPost($field);
		}
		
		// Pastikan id_module tidak null
		$dataDb['id_module'] = $this->request->getPost('id_module') ?: 0;
		
		// Insert atau Update
		$idRole = $this->request->getPost('id');
		
		if ($idRole) {
			$save = $this->db->table('core_role')->update($dataDb, ['id_role' => $idRole]);
		} else {
			$save = $this->db->table('core_role')->insert($dataDb);
			$idRole = $this->db->insertID();
		}
		
		if ($save) {
			return [
				'status' => 'ok',
				'message' => 'Data berhasil disimpan',
				'id_role' => $idRole
			];
		}
		
		return [
			'status' => 'error',
			'message' => 'Data gagal disimpan'
		];
	}
	
	/**
	 * Hapus data role
	 * 
	 * @return int Jumlah baris yang terhapus
	 */
	public function deleteData() 
	{
		$this->db->table('core_role')->delete(['id_role' => $this->request->getPost('id')]);
		return $this->db->affectedRows();
	}
	
	/**
	 * Hitung total semua role
	 * 
	 * @return int Jumlah role
	 */
	public function countAllData() 
	{
		return $this->db->table('core_role')
			->countAllResults();
	}
	
	/**
	 * Mendapatkan list data role untuk DataTables
	 * Menggunakan parameter binding untuk mencegah SQL injection
	 * 
	 * @return array Data role dengan total filtered
	 */
	public function getListData() 
	{
		$columns = $this->request->getPost('columns');
		
		// Whitelist kolom yang diizinkan untuk search dan order
		$allowedColumns = ['id_role', 'nama_role', 'judul_role', 'keterangan', 'id_module'];
		
		// Build query dengan Query Builder
		$builder = $this->db->table('core_role');
		
		// Search - menggunakan groupStart/groupEnd untuk kondisi OR yang aman
		$searchValue = $this->request->getPost('search')['value'] ?? '';
		if ($searchValue) {
			$builder->groupStart();
			foreach ($columns as $column) {
				$columnData = $column['data'];
				
				// Skip kolom yang mengandung 'ignore' atau tidak ada di whitelist
				if (strpos($columnData, 'ignore') !== false || !in_array($columnData, $allowedColumns)) {
					continue;
				}
				
				$builder->orLike($columnData, $searchValue);
			}
			$builder->groupEnd();
		}
		
		// Hitung total filtered sebelum limit
		$totalFiltered = $builder->countAllResults(false);
		
		// Order By - pastikan kolom ada di whitelist
		$orderData = $this->request->getPost('order');
		if ($orderData && isset($columns[$orderData[0]['column']])) {
			$orderColumn = $columns[$orderData[0]['column']]['data'];
			$orderDir = strtoupper($orderData[0]['dir']) === 'DESC' ? 'DESC' : 'ASC';
			
			// Hanya apply order jika kolom valid
			if (strpos($orderColumn, 'ignore') === false && in_array($orderColumn, $allowedColumns)) {
				$builder->orderBy($orderColumn, $orderDir);
			}
		}
		
		// Limit dan Offset dengan type casting
		$start = (int) ($this->request->getPost('start') ?? 0);
		$length = (int) ($this->request->getPost('length') ?? 10);
		$builder->limit($length, $start);
		
		// Eksekusi query
		$data = $builder->get()->getResultArray();
		
		return [
			'data' => $data,
			'total_filtered' => $totalFiltered
		];
	}
}
?>