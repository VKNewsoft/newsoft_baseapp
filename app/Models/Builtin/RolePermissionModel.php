<?php
/**
 * Model Role Permission
 * 
 * Mengelola assignment permission ke role. Mengatur hak akses
 * berdasarkan role untuk setiap module dan permission.
 * 
 * @package    App\Models\Builtin
 * @author     Newsoft Developer
 * @copyright  2020-2023
 */

namespace App\Models\Builtin;

class RolePermissionModel extends \App\Models\BaseModel
{
	/**
	 * Hapus satu permission dari role
	 * 
	 * @param int $idRole ID role
	 * @param int $idPermission ID module permission
	 * @return bool Status penghapusan
	 */
	public function deletePermission($idRole, $idPermission) {
		return $this->db->table('core_role_module_permission')
			->where('id_role', $idRole)
			->where('id_module_permission', $idPermission)
			->delete();
	}
	
	/**
	 * Hapus semua permission role untuk satu module
	 * 
	 * @param int $idRole ID role
	 * @param int $idModule ID module
	 * @return bool Status penghapusan
	 */
	public function deleteRolePermissionByModule($idRole, $idModule) {
		// Ambil semua permission untuk module ini
		$permissions = $this->db->table('core_module_permission')
			->select('id_module_permission')
			->where('id_module', $idModule)
			->get()
			->getResultArray();
		
		if (empty($permissions)) {
			return true;
		}
		
		$permissionIds = array_column($permissions, 'id_module_permission');
		
		return $this->db->table('core_role_module_permission')
			->where('id_role', $idRole)
			->whereIn('id_module_permission', $permissionIds)
			->delete();
	}
	
	/**
	 * Mendapatkan semua permission yang dimiliki role
	 * 
	 * @param int $id ID role
	 * @return array Permission yang dimiliki role (indexed by id_module_permission)
	 */
	public function getRolePermissionByIdRole($id) 
	{
		$query = $this->db->table('core_role_module_permission')
			->where('id_role', $id)
			->get()
			->getResultArray();
		
		$result = [];
		foreach ($query as $val) {
			$result[$val['id_module_permission']] = $val;
		}

		return $result;
	}
	
	/**
	 * Mendapatkan semua permission dikelompokkan per module
	 * 
	 * @return array Permission grouped by id_module
	 */
	public function getAllPermissionByModule() 
	{
		$modulePermission = $this->db->table('core_module_permission')
			->select('core_module_permission.*, core_module.*')
			->join('core_module', 'core_module.id_module = core_module_permission.id_module', 'left')
			->orderBy('judul_module', 'ASC')
			->get()
			->getResultArray();
		
		$result = [];
		foreach ($modulePermission as $val) {
			$result[$val['id_module']][$val['id_module_permission']] = $val;
		}

		return $result;
	}
	
	/**
	 * Mendapatkan semua module
	 * 
	 * @return array Daftar module (indexed by id_module)
	 */
	public function getAllModules() {
		$query = $this->db->table('core_module')
			->orderBy('judul_module', 'ASC')
			->get()
			->getResultArray();
		
		$result = [];
		foreach ($query as $val) {
			$result[$val['id_module']] = $val;
		}
		return $result;
	}
	
	/**
	 * Mendapatkan module berdasarkan ID atau semua module
	 * 
	 * @param int $idModule ID module (optional)
	 * @return array Daftar module (indexed by id_module)
	 */
	public function getAllModulesById($idModule = 0) {
		$builder = $this->db->table('core_module');
		
		if ($idModule) {
			$builder->where('id_module', $idModule);
		}
		
		$query = $builder->orderBy('judul_module', 'ASC')
			->get()
			->getResultArray();
		
		$result = [];
		foreach ($query as $val) {
			$result[$val['id_module']] = $val;
		}
		return $result;
	}
	
	/**
	 * Mendapatkan detail role berdasarkan ID
	 * 
	 * @param int $id ID role
	 * @return array Data role
	 */
	public function getRoleById($id) {
		return $this->db->table('core_role')
			->where('id_role', $id)
			->get()
			->getRowArray();
	}
	
	/**
	 * Mendapatkan semua role
	 * 
	 * @return array Daftar semua role
	 */
	public function getAllRole() {
		return $this->db->table('core_role')->get()->getResultArray();
	}
	
	/**
	 * Mendapatkan semua role permission dengan detail module
	 * 
	 * @return array Daftar role permission lengkap
	 */
	public function getAllRolePermission() {
		return $this->db->table('core_role_module_permission')
			->select('core_role_module_permission.*, core_module_permission.*, core_module.*')
			->join('core_module_permission', 'core_module_permission.id_module_permission = core_role_module_permission.id_module_permission', 'left')
			->join('core_module', 'core_module.id_module = core_module_permission.id_module', 'left')
			->get()
			->getResultArray();
	}
	
	/**
	 * Simpan permission untuk role (replace atau update)
	 * 
	 * @return bool Status transaksi
	 */
	public function saveData() 
	{
		$idRole = $this->request->getPost('id');
		$idModule = $this->request->getPost('id_module');
		$permissions = $this->request->getPost('permission') ?? [];
		
		$this->db->transStart();
		
		// Hapus permission lama
		if (!empty($idModule) && $idModule != 'semua_module') {
			// Hapus hanya untuk module tertentu
			$this->deleteRolePermissionByModule($idRole, $idModule);
		} else {
			// Hapus semua permission role
			$this->db->table('core_role_module_permission')
				->where('id_role', $idRole)
				->delete();
		}
		
		// Insert permission baru
		if (!empty($permissions)) {
			$dataDb = [];
			foreach ($permissions as $val) {
				$dataDb[] = ['id_role' => $idRole, 'id_module_permission' => $val];
			}
			$this->db->table('core_role_module_permission')->insertBatch($dataDb);
		}
		
		$this->db->transComplete();
		return $this->db->transStatus();
	}
	
	/**
	 * Cek apakah role memiliki semua permission yang tersedia
	 * 
	 * @param int $idRole ID role
	 * @return bool True jika punya semua permission
	 */
	public function hasAllPermission($idRole) {
		// Hitung permission yang belum dimiliki
		$countMissing = $this->db->table('core_module_permission')
			->select('core_module_permission.id_module_permission')
			->join('core_module', 'core_module.id_module = core_module_permission.id_module', 'left')
			->join('core_role_module_permission', 'core_role_module_permission.id_module_permission = core_module_permission.id_module_permission AND core_role_module_permission.id_role = ' . (int)$idRole, 'left')
			->where('core_role_module_permission.id_role', null)
			->countAllResults();
		
		return $countMissing === 0;
	}
	
	/**
	 * Hapus semua permission dari role
	 * 
	 * @return bool Status penghapusan
	 */
	public function deleteAllPermission() {
		$idRole = $this->request->getPost('id_role');
		return $this->db->table('core_role_module_permission')
			->where('id_role', $idRole)
			->delete();
	}
	
	/**
	 * Assign atau unassign satu permission ke role
	 * 
	 * @return bool Status operasi
	 */
	public function assignPermission() 
	{
		$assign = $this->request->getPost('assign');
		$idRole = $this->request->getPost('id_role');
		$idModulePermission = $this->request->getPost('id_module_permission');
		
		if ($assign == 'Y') {
			return $this->db->table('core_role_module_permission')
				->insert(['id_role' => $idRole, 'id_module_permission' => $idModulePermission]);
		}
		
		return $this->db->table('core_role_module_permission')
			->where('id_role', $idRole)
			->where('id_module_permission', $idModulePermission)
			->delete();
	}
	
	/**
	 * Assign atau unassign semua permission ke role
	 * 
	 * @return bool Status transaksi
	 */
	public function assignAllPermission() 
	{
		$assignAll = $this->request->getPost('assign_all');
		$idRole = $this->request->getPost('id_role');
		
		if ($assignAll == 'Y') {
			// Ambil semua permission
			$data = $this->db->table('core_module_permission')->get()->getResultArray();
			
			$dataDb = [];
			foreach ($data as $val) {
				$dataDb[] = ['id_role' => $idRole, 'id_module_permission' => $val['id_module_permission']];
			}
			
			$this->db->transStart();
			$this->db->table('core_role_module_permission')->where('id_role', $idRole)->delete();
			if (!empty($dataDb)) {
				$this->db->table('core_role_module_permission')->insertBatch($dataDb);
			}
			$this->db->transComplete();
			return $this->db->transStatus();
		}
		
		// Unassign all
		return $this->db->table('core_role_module_permission')
			->where('id_role', $idRole)
			->delete();
	}
	
	/**
	 * Hitung total permission yang tersedia
	 * 
	 * @return int Jumlah permission
	 */
	public function countAllDataPermission() {
		return $this->db->table('core_module_permission')->countAllResults();
	}
	
	/**
	 * Mendapatkan data permission untuk DataTables dengan status assignment per role
	 * 
	 * @return array Data permission dan total filtered
	 */
	public function getListDataPermission() {
		$columns = $this->request->getPost('columns');
		$idRole = (int) ($this->request->getGet('id') ?? 0);

		// Build WHERE untuk search
		$builder = $this->db->table('core_module_permission')
			->select('core_module_permission.*, core_module.nama_module, core_module.judul_module, rp.id_role')
			->join('core_module', 'core_module.id_module = core_module_permission.id_module', 'left')
			->join('(SELECT id_role, id_module_permission FROM core_role_module_permission WHERE id_role = ' . $idRole . ') as rp', 'rp.id_module_permission = core_module_permission.id_module_permission', 'left');

		// Search
		$searchAll = @$this->request->getPost('search')['value'];
		if ($searchAll) {
			$builder->groupStart();
			$first = true;
			foreach ($columns as $val) {
				if (strpos($val['data'], 'ignore') !== false) {
					continue;
				}
				
				if ($first) {
					$builder->like($val['data'], $searchAll);
					$first = false;
				} else {
					$builder->orLike($val['data'], $searchAll);
				}
			}
			$builder->groupEnd();
		}

		// Count filtered
		$totalFiltered = $builder->countAllResults(false);

		// Order - dengan whitelist
		$orderData = $this->request->getPost('order');
		$columnsPost = $this->request->getPost('columns');
		
		if (!empty($orderData) && !empty($columnsPost[$orderData[0]['column']]['data'])) {
			$columnName = $columnsPost[$orderData[0]['column']]['data'];
			
			if (strpos($columnName, 'ignore') === false) {
				$allowedColumns = ['nama_permission', 'deskripsi_permission', 'nama_module', 'judul_module'];
				if (in_array($columnName, $allowedColumns)) {
					$direction = strtoupper($orderData[0]['dir']) === 'DESC' ? 'DESC' : 'ASC';
					$builder->orderBy($columnName, $direction);
				}
			}
		}

		// Limit
		$start = (int) ($this->request->getPost('start') ?: 0);
		$length = (int) ($this->request->getPost('length') ?: 10);
		$data = $builder->limit($length, $start)->get()->getResultArray();
				
		return ['data' => $data, 'total_filtered' => $totalFiltered];
	}
	
	/**
	 * Hitung total role
	 * 
	 * @return int Jumlah role
	 */
	public function countAllData() {
		return $this->db->table('core_role')->countAllResults();
	}
	
	/**
	 * Mendapatkan data role untuk DataTables dengan jumlah permission
	 * 
	 * @return array Data role dan total filtered
	 */
	public function getListData() {
		$columns = $this->request->getPost('columns');

		// Build query dengan Query Builder
		$builder = $this->db->table('core_role')
			->select('core_role.*, COUNT(DISTINCT core_module_permission.id_module) as jml_module, COUNT(core_role_module_permission.id_module_permission) as jml_permission')
			->join('core_role_module_permission', 'core_role_module_permission.id_role = core_role.id_role', 'left')
			->join('core_module_permission', 'core_module_permission.id_module_permission = core_role_module_permission.id_module_permission', 'left')
			->groupBy('core_role.id_role');

		// Search
		$searchAll = @$this->request->getPost('search')['value'];
		if ($searchAll) {
			$builder->groupStart();
			$first = true;
			foreach ($columns as $val) {
				if (strpos($val['data'], 'ignore') !== false) {
					continue;
				}
				
				// Validasi column exists
				$allowedSearchCols = ['nama_role', 'deskripsi_role'];
				if (in_array($val['data'], $allowedSearchCols)) {
					if ($first) {
						$builder->like($val['data'], $searchAll);
						$first = false;
					} else {
						$builder->orLike($val['data'], $searchAll);
					}
				}
			}
			$builder->groupEnd();
		}

		// Count filtered (clone builder before limit)
		$totalFiltered = $builder->countAllResults(false);

		// Order - dengan whitelist
		$orderData = $this->request->getPost('order');
		$columnsPost = $this->request->getPost('columns');
		
		if (!empty($orderData) && !empty($columnsPost[$orderData[0]['column']]['data'])) {
			$columnName = $columnsPost[$orderData[0]['column']]['data'];
			
			if (strpos($columnName, 'ignore') === false) {
				$allowedColumns = ['nama_role', 'deskripsi_role', 'jml_module', 'jml_permission'];
				if (in_array($columnName, $allowedColumns)) {
					$direction = strtoupper($orderData[0]['dir']) === 'DESC' ? 'DESC' : 'ASC';
					$builder->orderBy($columnName, $direction);
				}
			}
		}

		// Limit
		$start = (int) ($this->request->getPost('start') ?: 0);
		$length = (int) ($this->request->getPost('length') ?: 10);
		$data = $builder->limit($length, $start)->get()->getResultArray();
				
		return ['data' => $data, 'total_filtered' => $totalFiltered];
	}
}
?>
