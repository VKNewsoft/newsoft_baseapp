<?php
/**
 * Model Menu Role
 * 
 * Mengelola assignment menu ke role. Mengatur menu mana saja
 * yang bisa diakses oleh setiap role, termasuk auto-assign parent menu.
 * 
 * @package    App\Models\Builtin
 * @author     Newsoft Developer
 * @copyright  2020-2023
 */

namespace App\Models\Builtin;

class MenuRoleModel extends \App\Models\BaseModel
{
	/**
	 * Mendapatkan semua menu
	 * 
	 * @return array Daftar semua menu
	 */
	public function getAllMenu() {
		return $this->db->table('core_menu')->get()->getResultArray();
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
	 * Mendapatkan semua menu role beserta detail role
	 * 
	 * @return array Daftar menu role dengan informasi role
	 */
	public function getAllMenuRole() {
		return $this->db->table('core_menu_role')
			->select('core_menu_role.*, core_role.*')
			->join('core_role', 'core_role.id_role = core_menu_role.id_role', 'left')
			->get()
			->getResultArray();
	}
	
	/**
	 * Mendapatkan role yang bisa mengakses menu tertentu
	 * 
	 * @param int $id ID menu
	 * @return array Daftar role untuk menu tersebut
	 */
	public function getMenuRoleById($id) {
		return $this->db->table('core_menu_role')
			->where('id_menu', $id)
			->get()
			->getResultArray();
	}
	
	/**
	 * Hapus satu role dari menu
	 * 
	 * @return int Jumlah baris terpengaruh
	 */
	public function deleteData() {
		$idMenu = $this->request->getPost('id_menu');
		$idRole = $this->request->getPost('id_role');
		
		$this->db->table('core_menu_role')
			->where('id_menu', $idMenu)
			->where('id_role', $idRole)
			->delete();
			
		return $this->db->affectedRows();
	}
	
	/**
	 * Simpan role untuk menu (dengan auto-assign parent menu)
	 * 
	 * @return array Status dan informasi parent yang di-insert
	 */
	public function saveData() 
	{
		$idMenu = $this->request->getPost('id_menu');
		$idRoles = $this->request->getPost('id_role') ?? [];
		
		// Cari semua parent menu
		$menuParents = $this->allParents($idMenu);
		
		$insertParent = [];
		if ($menuParents && !empty($idRoles)) {
			// Cek apakah parent sudah di-assign ke role, jika belum tambahkan
			foreach($menuParents as $idMenuParent) {
				foreach ($idRoles as $idRole) {
					// Cek existing assignment
					$exists = $this->db->table('core_menu_role')
						->where('id_menu', $idMenuParent)
						->where('id_role', $idRole)
						->countAllResults();
						
					if (!$exists) {
						$insertParent[] = ['id_menu' => $idMenuParent, 'id_role' => $idRole];
					}
				}
			}
		}

		// Transaksi: Insert parent, hapus role lama, insert role baru
		$this->db->transStart();
		
		// Insert parent menu role jika ada
		if (!empty($insertParent)) {
			$this->db->table('core_menu_role')->insertBatch($insertParent);
		}
		
		// Hapus semua role untuk menu ini
		$this->db->table('core_menu_role')->where('id_menu', $idMenu)->delete();
		
		// Insert role baru yang dipilih
		if (!empty($idRoles)) {
			$dataDb = [];
			foreach ($idRoles as $idRole) {
				$dataDb[] = ['id_menu' => $idMenu, 'id_role' => $idRole];
			}
			$this->db->table('core_menu_role')->insertBatch($dataDb);
		}

		$this->db->transComplete();
		$trans = $this->db->transStatus();
		
		if ($trans) {
			$result = ['status' => 'ok', 'insert_parent' => $insertParent];
		} else {
			$result = ['status' => 'error'];
		}
		return $result;
	}
	
	/**
	 * Mencari semua parent menu secara rekursif
	 * 
	 * @param int $idMenu ID menu
	 * @param array $listParent Array parent (by reference)
	 * @return array Daftar ID parent menu
	 */
	private function allParents($idMenu, &$listParent = []) {
		$query = $this->db->table('core_menu')->get()->getResultArray();
		
		$menu = [];
		foreach($query as $val) {
			$menu[$val['id_menu']] = $val;
		}
		
		if (key_exists($idMenu, $menu)) {
			$parent = $menu[$idMenu]['id_parent'];
			if ($parent) {
				$listParent[$parent] = $parent;
				$this->allParents($parent, $listParent);
			}
		}
		
		return $listParent;
	}
	
	/**
	 * Hitung total menu
	 * 
	 * @return int Jumlah menu
	 */
	public function countAllData() {
		return $this->db->table('core_menu')->countAllResults();
	}
	
	/**
	 * Mendapatkan data menu untuk DataTables dengan filter dan sorting
	 * 
	 * @return array Data menu dan total filtered
	 */
	public function getListData() {
		$columns = $this->request->getPost('columns');

		// Build query
		$builder = $this->db->table('core_menu');

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
				$allowedColumns = ['nama_menu', 'url', 'urut', 'aktif'];
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