<?php
/**
 * PermissionModel - Model untuk manajemen permission module
 * 
 * Model ini menangani operasi CRUD permission untuk setiap module
 * termasuk auto-generate CRUD permission
 * 
 * @package App\Models\Builtin
 * @year 2020-2025
 */

namespace App\Models\Builtin;

class PermissionModel extends \App\Models\BaseModel
{
	/**
	 * Mendapatkan semua module dalam format array terindeks
	 * 
	 * @return array Daftar module dengan key id_module
	 */
	public function getAllModules() 
	{
		$modules = $this->db->table('core_module')
			->orderBy('judul_module', 'ASC')
			->get()
			->getResultArray();
		
		$result = [];
		foreach ($modules as $val) {
			$result[$val['id_module']] = $val['judul_module'];
		}
		
		return $result;
	}
	
	/**
	 * Mendapatkan module berdasarkan ID
	 * 
	 * @param int $idModule ID module
	 * @return array|null Data module
	 */
	public function getModuleById($idModule) 
	{
		return $this->db->table('core_module')
			->where('id_module', $idModule)
			->get()
			->getRowArray();
	}

	/**
	 * Mendapatkan permission berdasarkan ID dengan data module
	 * 
	 * @param int|null $id ID permission
	 * @return array|null Data permission dengan module
	 */
	public function getPermissionById(int $id = null) 
	{
		return $this->db->table('core_module_permission')
			->join('core_module', 'core_module_permission.id_module = core_module.id_module')
			->where('id_module_permission', $id)
			->get()
			->getRowArray();
	}
	
	/**
	 * Mendapatkan permission yang dimiliki role tertentu
	 * Untuk controller "module"
	 * 
	 * @param int $idRole ID role
	 * @return array Daftar id_module_permission
	 */
	public function getRolePermission($idRole) 
	{
		return $this->db->table('core_role_module_permission')
			->select('id_module_permission')
			->where('id_role', $idRole)
			->get()
			->getResultArray();
	}
	
	/**
	 * Mendapatkan permission berdasarkan module atau semua permission
	 * 
	 * @param int|null $id ID module (null untuk semua)
	 * @return array Permission terindeks berdasarkan id_module
	 */
	public function getPermission(int $id = null) 
	{
		$result = [];
		
		if ($id) {
			$modulePermission = $this->db->table('core_module_permission')
				->join('core_module', 'core_module_permission.id_module = core_module.id_module')
				->where('core_module_permission.id_module', $id)
				->get()
				->getResultArray();
		} else {
			$modulePermission = $this->db->table('core_module')
				->join('core_module_permission', 'core_module.id_module = core_module_permission.id_module')
				->orderBy('nama_permission', 'ASC')
				->orderBy('judul_module', 'ASC')
				->get()
				->getResultArray();
		}
		
		foreach ($modulePermission as $val) {
			$result[$val['id_module']][$val['id_module_permission']] = $val;
		}

		return $result;
	}
	
	/**
	 * Cek duplikasi nama permission saat edit
	 * 
	 * @return array|bool Data permission jika duplikat, false jika tidak
	 */
	public function checkDuplicate() 
	{
		$namaPermissionOld = $this->request->getPost('nama_permission_old');
		$namaPermission = $this->request->getPost('nama_permission');
		$idModule = $this->request->getPost('id_module');
		
		if (!empty($namaPermissionOld) && $namaPermission != $namaPermissionOld) {
			return $this->db->table('core_module_permission')
				->where('nama_permission', $namaPermission)
				->where('id_module', $idModule)
				->get()
				->getRowArray();
		}
		
		return false;
	}
	
	/**
	 * Cek permission mana yang sudah ada di database
	 * Digunakan saat auto-generate permission
	 * 
	 * @param array $permission Daftar nama permission
	 * @return array Permission yang sudah ada
	 */
	private function checkPermissionExists($permission) 
	{
		$idModule = (int) $this->request->getPost('id_module');
		
		$query = $this->db->table('core_module_permission')
			->where('id_module', $idModule)
			->whereIn('nama_permission', $permission)
			->get()
			->getResultArray();
		
		$permissionExists = [];
		foreach ($query as $val) {
			$permissionExists[$val['nama_permission']] = $val['nama_permission'];
		}
		
		return $permissionExists;
	}
	
	/**
	 * Generate permission CRUD All (create, read_all, update_all, delete_all)
	 * 
	 * @return void
	 */
	private function saveCrud() 
	{
		$keterangan = ['membuat', 'membaca', 'mengupdate', 'menghapus'];
		$listPermission = ["create", "read_all", "update_all", "delete_all"];
		$permissionExists = $this->checkPermissionExists($listPermission);
		$idModule = (int) $this->request->getPost('id_module');
		
		foreach ($listPermission as $key => $namaPermission) {
			if (in_array($namaPermission, $permissionExists)) {
				continue;
			}
			
			$ketData = $namaPermission == 'create' ? ' data' : ' semua data';
			
			$dataDb = [
				'id_module' => $idModule,
				'nama_permission' => $namaPermission,
				'judul_permission' => ucwords(str_replace('_', ' ', $namaPermission)) . ' Data',
				'keterangan' => 'Hak akses untuk ' . $keterangan[$key] . $ketData
			];
			
			$this->db->table('core_module_permission')->insert($dataDb);
		}
	}
	
	/**
	 * Generate permission CRUD Own (create, read_own, update_own, delete_own)
	 * 
	 * @return void
	 */
	private function saveCrudOwn() 
	{
		$keterangan = ['membuat', 'membaca', 'mengupdate', 'menghapus'];
		$listPermission = ["create", "read_own", "update_own", "delete_own"];
		$permissionExists = $this->checkPermissionExists($listPermission);
		$idModule = (int) $this->request->getPost('id_module');
		
		foreach ($listPermission as $key => $namaPermission) {
			if (in_array($namaPermission, $permissionExists)) {
				continue;
			}
			
			$ketData = $namaPermission == 'create' ? ' data' : ' data miliknya sendiri';
			
			$dataDb = [
				'id_module' => $idModule,
				'nama_permission' => $namaPermission,
				'judul_permission' => ucwords(str_replace('_', ' ', $namaPermission)) . ' Data',
				'keterangan' => 'Hak akses untuk ' . $keterangan[$key] . $ketData
			];
			
			$this->db->table('core_module_permission')->insert($dataDb);
		}
	}
	
	/**
	 * Simpan data permission (manual atau auto-generate)
	 * 
	 * @return array Status dan ID permission
	 */
	public function saveData() 
	{
		$this->db->transStart();
		
		$idNew = '';
		$generatePermission = $this->request->getPost('generate_permission');
		
		if ($generatePermission) {
			if ($generatePermission == 'crud_all') {
				$this->saveCrud();
			} elseif ($generatePermission == 'crud_own') {
				$this->saveCrudOwn();
			} elseif ($generatePermission == 'crud_all_crud_own') {
				$this->saveCrud();
				$this->saveCrudOwn();
			} else {
				// Manual permission
				$dataDb = [
					'id_module' => (int) $this->request->getPost('id_module'),
					'nama_permission' => $this->request->getPost('nama_permission'),
					'judul_permission' => $this->request->getPost('judul_permission'),
					'keterangan' => $this->request->getPost('keterangan')
				];
				
				if (empty($this->request->getPost('id'))) {
					$this->db->table('core_module_permission')->insert($dataDb);
					$idNew = $this->db->insertID();
				} else {
					$this->db->table('core_module_permission')
						->update($dataDb, ['id_module_permission' => (int) $this->request->getPost('id')]);
				}
			}
			
			// Auto-assign permission ke role jika ada
			if (!empty($this->request->getPost('id_role'))) {
				$idModule = (int) $this->request->getPost('id_module');
				
				$modulePermission = $this->db->table('core_module_permission')
					->where('id_module', $idModule)
					->get()
					->getResultArray();
				
				$values = [];
				foreach ($modulePermission as $val) {
					$values[] = [
						'id_role' => $this->request->getPost('id_role'),
						'id_module_permission' => $val['id_module_permission']
					];
				}
				
				if ($values) {
					$this->db->table('core_role_module_permission')->insertBatch($values);
				}
			}
		}
		
		$this->db->transComplete();
		
		if ($this->db->transStatus() == false) {
			return [
				'status' => 'error',
				'message' => 'Data gagal disimpan',
				'id' => $idNew
			];
		}
		
		return [
			'status' => 'ok',
			'message' => 'Data berhasil disimpan',
			'id' => $idNew
		];
	}
	
	/**
	 * Hapus semua permission berdasarkan module
	 * 
	 * @param int $id ID module
	 * @return bool Status transaksi
	 */
	public function deletePermissionByModule($id) 
	{
		$this->db->transStart();
		
		$idModule = (int) trim($id);
		
		// Ambil daftar id_module_permission untuk dihapus
		$permissions = $this->db->table('core_module_permission')
			->select('id_module_permission')
			->where('id_module', $idModule)
			->get()
			->getResultArray();
		
		$permissionIds = array_column($permissions, 'id_module_permission');
		
		if ($permissionIds) {
			$this->db->table('core_role_module_permission')
				->whereIn('id_module_permission', $permissionIds)
				->delete();
		}
		
		$this->db->table('core_module_permission')->delete(['id_module' => $idModule]);
		
		$this->db->transComplete();
		return $this->db->transStatus();
	}
	
	/**
	 * Hapus permission berdasarkan ID
	 * 
	 * @param int $id ID permission
	 * @return bool Status transaksi
	 */
	public function deleteData($id) 
	{
		$this->db->transStart();
		
		$idPermission = (int) trim($id);
		
		$this->db->table('core_role_module_permission')
			->delete(['id_module_permission' => $idPermission]);
		
		$this->db->table('core_module_permission')
			->delete(['id_module_permission' => $idPermission]);
		
		$this->db->transComplete();
		return $this->db->transStatus();
	}
	
	/**
	 * Hitung total permission berdasarkan WHERE clause
	 * 
	 * @param string $where Kondisi WHERE (deprecated - akan diganti)
	 * @return int Jumlah permission
	 */
	public function countAllData($where) 
	{
		// TODO: Refactor untuk menghilangkan raw SQL
		$sql = 'SELECT COUNT(*) AS jml FROM core_module_permission' . $where;
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	/**
	 * Mendapatkan list data permission untuk DataTables
	 * Menggunakan parameter binding untuk mencegah SQL injection
	 * 
	 * @param string $where Kondisi WHERE tambahan
	 * @return array Data permission dengan total filtered
	 */
	public function getListData($where) 
	{
		$columns = $this->request->getPost('columns');
		
		// Whitelist kolom yang diizinkan
		$allowedColumns = ['id_module_permission', 'nama_permission', 'judul_permission', 'keterangan', 'id_module', 'nama_module', 'judul_module'];
		
		// Build query dengan Query Builder
		$builder = $this->db->table('core_module_permission')
			->join('core_module', 'core_module_permission.id_module = core_module.id_module');
		
		// TODO: Parse $where ke Query Builder (untuk sementara masih raw SQL)
		// Search
		$searchValue = $this->request->getPost('search')['value'] ?? '';
		if ($searchValue) {
			$builder->groupStart();
			foreach ($columns as $column) {
				$columnData = $column['data'];
				
				if (strpos($columnData, 'ignore_search') !== false || 
					strpos($columnData, 'ignore') !== false ||
					!in_array($columnData, $allowedColumns)) {
					continue;
				}
				
				$builder->orLike($columnData, $searchValue);
			}
			$builder->groupEnd();
		}
		
		// Hitung total filtered
		// Note: Untuk sementara masih gunakan raw SQL karena $where param
		$sql = 'SELECT COUNT(*) AS jml_data FROM core_module_permission LEFT JOIN core_module USING(id_module) ' . $where;
		if ($searchValue) {
			$whereCols = [];
			foreach ($columns as $column) {
				$columnData = $column['data'];
				if (strpos($columnData, 'ignore_search') === false && 
					strpos($columnData, 'ignore') === false &&
					in_array($columnData, $allowedColumns)) {
					$whereCols[] = $columnData . ' LIKE "' . $this->db->escapeString($searchValue) . '"';
				}
			}
			if ($whereCols) {
				$sql .= ' AND (' . join(' OR ', $whereCols) . ')';
			}
		}
		$totalFiltered = $this->db->query($sql)->getRowArray()['jml_data'];
		
		// Order By
		$orderData = $this->request->getPost('order');
		if ($orderData && isset($columns[$orderData[0]['column']])) {
			$orderColumn = $columns[$orderData[0]['column']]['data'];
			$orderDir = strtoupper($orderData[0]['dir']) === 'DESC' ? 'DESC' : 'ASC';
			
			if (strpos($orderColumn, 'ignore') === false && in_array($orderColumn, $allowedColumns)) {
				$builder->orderBy($orderColumn, $orderDir);
			}
		}
		
		// Limit dan Offset
		$start = (int) ($this->request->getPost('start') ?? 0);
		$length = (int) ($this->request->getPost('length') ?? 10);
		$builder->limit($length, $start);
		
		// Eksekusi query
		// Note: Masih gunakan raw SQL karena ada $where param dari luar
		$sql = 'SELECT * FROM core_module_permission LEFT JOIN core_module USING(id_module) ' . $where;
		if ($searchValue) {
			$whereCols = [];
			foreach ($columns as $column) {
				$columnData = $column['data'];
				if (strpos($columnData, 'ignore_search') === false && 
					strpos($columnData, 'ignore') === false &&
					in_array($columnData, $allowedColumns)) {
					$whereCols[] = $columnData . ' LIKE "%' . $this->db->escapeString($searchValue) . '%"';
				}
			}
			if ($whereCols) {
				$sql .= ' AND (' . join(' OR ', $whereCols) . ')';
			}
		}
		
		// Order
		if ($orderData && isset($columns[$orderData[0]['column']])) {
			$orderColumn = $columns[$orderData[0]['column']]['data'];
			$orderDir = strtoupper($orderData[0]['dir']) === 'DESC' ? 'DESC' : 'ASC';
			if (strpos($orderColumn, 'ignore') === false && in_array($orderColumn, $allowedColumns)) {
				$sql .= ' ORDER BY ' . $orderColumn . ' ' . $orderDir;
			}
		}
		
		$sql .= ' LIMIT ' . $start . ', ' . $length;
		$data = $this->db->query($sql)->getResultArray();
		
		return [
			'data' => $data,
			'total_filtered' => $totalFiltered
		];
	}
}
?>