<?php
/**
 * Model Module Role
 * 
 * Mengelola assignment module ke role dengan hak akses CRUD
 * (read, create, update, delete) untuk setiap kombinasi module-role.
 * 
 * @package    App\Models\Builtin
 * @author     Newsoft Developer
 * @copyright  2020-2023
 */

namespace App\Models\Builtin;

class ModuleRoleModel extends \App\Models\BaseModel
{
	/**
	 * Mendapatkan semua module
	 * 
	 * @return array Daftar semua module
	 */
	public function getAllModule() {
		return $this->db->table('core_module')->get()->getResultArray();
	}
	
	/**
	 * Mendapatkan detail module berdasarkan ID
	 * 
	 * @param int $id ID module
	 * @return array Data module
	 */
	public function getModule($id) {
		return $this->db->table('core_module')
			->where('id_module', $id)
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
	 * Mendapatkan detail role
	 * 
	 * @return array Data role detail
	 */
	public function getRoleDetail() {
		return $this->db->table('role_detail')->get()->getResultArray();
	}
	
	/**
	 * Mendapatkan semua module role beserta detail role
	 * 
	 * @return array Daftar module role dengan informasi role
	 */
	public function getAllModuleRole() {
		return $this->db->table('module_role')
			->select('module_role.*, core_role.nama_role, core_role.judul_role')
			->join('core_role', 'core_role.id_role = module_role.id_role', 'left')
			->get()
			->getResultArray();
	}
	
	/**
	 * Mendapatkan role yang memiliki akses ke module tertentu
	 * 
	 * @param int $id ID module
	 * @return array Daftar role untuk module tersebut
	 */
	public function getModuleRoleById($id) {
		return $this->db->table('module_role')
			->where('id_module', $id)
			->get()
			->getResultArray();
	}
	
	/**
	 * Mendapatkan module beserta statusnya
	 * 
	 * @return array Daftar module dengan status
	 */
	public function getModuleStatus() {
		return $this->db->table('core_module')
			->select('core_module.*, core_module_status.*')
			->join('core_module_status', 'core_module_status.id_module_status = core_module.id_module_status', 'left')
			->get()
			->getResultArray();
	}
	
	/**
	 * Hapus satu role dari module
	 * 
	 * @return int Jumlah baris terpengaruh
	 */
	public function deleteData() {
		$idModule = $this->request->getPost('id_module');
		$idRole = $this->request->getPost('id_role');
		
		$this->db->table('module_role')
			->where('id_module', $idModule)
			->where('id_role', $idRole)
			->delete();
			
		return $this->db->affectedRows();
	}
	
	/**
	 * Simpan hak akses CRUD untuk module-role
	 * 
	 * @return bool Status transaksi
	 */
	public function saveData() 
	{
		$idModule = $this->request->getPost('id');
		$postData = $this->request->getPost();
		
		$dataDb = [];
		foreach ($postData as $key => $val) {
			$exp = explode('_', $key);
			if ($exp[0] == 'role' && !empty($exp[1])) {
				$idRole = (int) $exp[1];
				$dataDb[] = [
					'id_module' => $idModule,
					'id_role' => $idRole,
					'read_data' => $this->request->getPost('akses_read_data_' . $idRole) ?: 0,
					'create_data' => $this->request->getPost('akses_create_data_' . $idRole) ?: 0,
					'update_data' => $this->request->getPost('akses_update_data_' . $idRole) ?: 0,
					'delete_data' => $this->request->getPost('akses_delete_data_' . $idRole) ?: 0
				];
			}
		}
		
		// Replace: hapus lama, insert baru
		$this->db->transStart();
		$this->db->table('module_role')->where('id_module', $idModule)->delete();
		if (!empty($dataDb)) {
			$this->db->table('module_role')->insertBatch($dataDb);
		}
		$this->db->transComplete();
		
		return $this->db->transStatus();
	}
	
	/**
	 * Hitung total module
	 * 
	 * @return int Jumlah module
	 */
	public function countAllData() {
		return $this->db->table('core_module')->countAllResults();
	}
	
	/**
	 * Mendapatkan data module untuk DataTables dengan filter dan sorting
	 * 
	 * @param string $where Kondisi WHERE tambahan
	 * @return array Data module dan total filtered
	 */
	public function getListData($where) {
		$columns = $this->request->getPost('columns');

		// Build WHERE clause
		$builder = $this->db->table('core_module');
		if (!empty($where)) {
			$builder->where($where, null, false);
		}

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
				$allowedColumns = ['nama_module', 'judul_module', 'deskripsi_module'];
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