<?php
/**
 * ModuleModel - Model untuk manajemen module dan permission
 * 
 * Model ini menangani semua operasi database yang berkaitan dengan module
 * termasuk CRUD module, permission, role assignment, dan status
 * 
 * @package App\Models\Builtin
 * @year 2020-2025
 */

namespace App\Models\Builtin;

class ModuleModel extends \App\Models\BaseModel
{
	/**
	 * Mendapatkan semua module diurutkan berdasarkan judul
	 * 
	 * @return array Daftar module
	 */
	public function getAllModules() 
	{
		return $this->db->table('core_module')
			->orderBy('judul_module', 'ASC')
			->get()
			->getResultArray();
	}
	
	/**
	 * Mendapatkan semua status module
	 * 
	 * @return array Daftar status module
	 */
	public function getAllModuleStatus() 
	{
		return $this->db->table('core_module_status')
			->get()
			->getResultArray();
	}
	
	/**
	 * Mendapatkan detail module berdasarkan ID
	 * 
	 * @param int $idModule ID module
	 * @return array Data module
	 */
	public function getModule($idModule) 
	{
		return $this->db->table('core_module')
			->where('id_module', $idModule)
			->get()
			->getRowArray();
	}
	
	/**
	 * Mendapatkan semua module role
	 * 
	 * @return array Daftar module dengan role
	 */
	public function getAllModuleRole() 
	{
		return $this->db->table('module_role')
			->join('core_module', 'module_role.id_module = core_module.id_module')
			->get()
			->getResultArray();
	}
	
	/**
	 * Mendapatkan semua role
	 * 
	 * @return array Daftar role
	 */
	public function getAllRoles() 
	{
		return $this->db->table('core_role')
			->get()
			->getResultArray();
	}
	
	/**
	 * Mendapatkan permission berdasarkan ID module (private method)
	 * 
	 * @param int $id ID module
	 * @return array Daftar permission
	 */
	private function getPermissionByIdModule($id) 
	{
		return $this->db->table('core_module_permission')
			->where('id_module', (int) $id)
			->get()
			->getResultArray();
	}
	
	/**
	 * Mendapatkan role berdasarkan ID module
	 * 
	 * @param int $id ID module
	 * @return array Daftar role
	 */
	public function getRoleByIdModule($id) 
	{
		return $this->db->table('core_role')
			->where('id_module', (int) $id)
			->get()
			->getResultArray();
	}
	
	/**
	 * Hapus data module beserta dependencies (permission, role)
	 * Menggunakan transaction untuk menjaga integritas data
	 * 
	 * @return bool Status berhasil/gagal
	 */
	public function deleteData() 
	{
		$this->db->transStart();
		
		$id = $this->request->getPost('id');
		
		// Hapus module
		$this->db->table('core_module')->delete(['id_module' => $id]);
		
		// Hapus permission terkait
		$modulePermission = $this->getPermissionByIdModule($id);
		$this->db->table('core_module_permission')->delete(['id_module' => $id]);
		
		if ($modulePermission) {
			foreach ($modulePermission as $val) {
				$this->db->table('core_role_module_permission')
					->delete(['id_module_permission' => $val['id_module_permission']]);
			}
		}
		
		// Update role yang menggunakan module ini
		$role = $this->getRoleByIdModule($id);
		if ($role) {
			foreach ($role as $val) {
				$this->db->table('core_role')
					->update(['id_module' => null], ['id_role' => $val['id_role']]);
			}
		}
		
		$this->db->transComplete();
		
		return $this->db->transStatus() !== false;
	}
	
	/**
	 * Update status module atau login requirement
	 * 
	 * @return void
	 */
	public function updateStatus() 
	{
		$switchType = $this->request->getPost('switch_type');
		$field = $switchType == 'aktif' ? 'id_module_status' : 'login';
		
		$this->db->table('core_module')->update(
			[$field => $this->request->getPost('id_result')], 
			['id_module' => $this->request->getPost('id_module')]
		);
	}
	
	/**
	 * Mendapatkan semua module dengan status
	 * 
	 * @return array Daftar module dengan status
	 */
	public function getModules() 
	{
		return $this->db->table('core_module')
			->join('core_module_status', 'core_module.id_module_status = core_module_status.id_module_status')
			->orderBy('judul_module', 'ASC')
			->get()
			->getResultArray();
	}
	
	/**
	 * Mendapatkan permission dari module tertentu
	 * 
	 * @param int $idModule ID module
	 * @return array Daftar permission
	 */
	public function getModulePermission($idModule) 
	{
		return $this->db->table('core_module_permission')
			->where('id_module', $idModule)
			->get()
			->getResultArray();
	}
	
	/**
	 * Mendapatkan role permission berdasarkan module
	 * Diformat sebagai array dengan key id_role dan id_module_permission
	 * 
	 * @param int $idModule ID module
	 * @return array Daftar role permission terindeks
	 */
	public function getRolePermissionByModule($idModule) 
	{
		$query = $this->db->table('core_role_module_permission')
			->join('core_module_permission', 'core_role_module_permission.id_module_permission = core_module_permission.id_module_permission')
			->where('id_module', $idModule)
			->get()
			->getResultArray();
		
		$result = [];
		foreach ($query as $val) {
			$result[$val['id_role']][$val['id_module_permission']] = $val;
		}
		
		return $result;
	}
	
	/**
	 * Simpan data module (create/update)
	 * Dengan otomatis generate permission jika diminta
	 * 
	 * @return array Status dan pesan hasil operasi
	 */
	public function saveData() 
	{
		$fields = ['nama_module', 'judul_module', 'deskripsi', 'id_module_status', 'login'];

		$dataDb = [];
		foreach ($fields as $field) {
			$dataDb[$field] = $this->request->getPost($field);
		}
		
		// Mulai transaction
		$this->db->transStart();
		
		// Insert atau Update
		$idModule = $this->request->getPost('id');
		
		if ($idModule) {
			$save = $this->db->table('core_module')->update($dataDb, ['id_module' => $idModule]);
		} else {
			$save = $this->db->table('core_module')->insert($dataDb);
			$idModule = $this->db->insertID();
		}
		
		// Generate permission otomatis jika diminta
		if (!empty($this->request->getPost('generate_permission'))) {
			$_POST['id_module'] = $idModule;
			$model = new \App\Models\Builtin\PermissionModel;
			$model->saveData();
		}
		
		$this->db->transComplete();
		
		if ($this->db->transStatus() === false) {
			return [
				'status' => 'error',
				'message' => 'Data gagal disimpan'
			];
		}
		
		return [
			'status' => 'ok',
			'message' => 'Data berhasil disimpan',
			'id_module' => $idModule
		];
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
	 * Hitung total semua module
	 * 
	 * @return int Jumlah module
	 */
	public function countAllData() 
	{
		return $this->db->table('core_module')
			->countAllResults();
	}
	
	/**
	 * Mendapatkan list data module untuk DataTables
	 * Menggunakan parameter binding untuk mencegah SQL injection
	 * 
	 * @return array Data module dengan total filtered
	 */
	public function getListData() 
	{
		$columns = $this->request->getPost('columns');
		
		// Whitelist kolom yang diizinkan untuk search dan order
		$allowedColumns = ['id_module', 'nama_module', 'judul_module', 'deskripsi', 'id_module_status', 'login'];
		
		// Build query dengan Query Builder
		$builder = $this->db->table('core_module');
		
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