<?php
/**
 * Model User Role
 * 
 * Mengelola assignment role kepada user. Satu user bisa memiliki
 * multiple roles untuk akses yang lebih fleksibel.
 * 
 * @package    App\Models\Builtin
 * @author     Newsoft Developer
 * @copyright  2020-2023
 */

namespace App\Modules\Builtin\Models;

class UserRoleModel extends \App\Modules\Common\Models\BaseModel
{
	/**
	 * Mendapatkan semua role
	 * 
	 * @return array Daftar semua role
	 */
	public function getAllRole() {
		return $this->db->table('core_role')->get()->getResultArray();
	}
	
	/**
	 * Mendapatkan semua user role beserta detailnya
	 * 
	 * @return array Daftar user role dengan join ke tabel core_role
	 */
	public function getUserRole() {
		return $this->db->table('core_user_role')
			->select('core_user_role.*, core_role.*')
			->join('core_role', 'core_role.id_role = core_user_role.id_role', 'left')
			->get()
			->getResultArray();
	}
	
	/**
	 * Mendapatkan role yang dimiliki user berdasarkan ID
	 * 
	 * @param int $id ID user
	 * @return array Daftar role user
	 */
	public function getUserRoleByID($id) {
		return $this->db->table('core_user_role')
			->where('id_user', $id)
			->get()
			->getResultArray();
	}
	
	/**
	 * Mendapatkan semua user
	 * 
	 * @return array Daftar semua user
	 */
	public function getAllUser() {
		return $this->db->table('core_user')->get()->getResultArray();
	}
	
	/**
	 * Hapus satu role dari user
	 * 
	 * @return int Jumlah baris yang terpengaruh
	 */
	public function deleteData() {
		$idUser = $this->request->getPost('id_user');
		$idRole = $this->request->getPost('id_role');
		
		$this->db->table('core_user_role')
			->where('id_user', $idUser)
			->where('id_role', $idRole)
			->delete();
			
		return $this->db->affectedRows();
	}
	
	/**
	 * Simpan role untuk user (replace semua role lama dengan role baru)
	 * 
	 * @return bool Status transaksi
	 */
	public function saveData() 
	{
		$idUser = $this->request->getPost('id_user');
		$idRoles = $this->request->getPost('id_role') ?? [];
		
		$this->db->transStart();
		
		// Hapus semua role user yang lama
		$this->db->table('core_user_role')->where('id_user', $idUser)->delete();
		
		// Insert role baru
		if (!empty($idRoles)) {
			$insert = [];
			foreach ($idRoles as $key => $idRole) {
				$insert[] = ['id_user' => $idUser, 'id_role' => $idRole];
			}
			$this->db->table('core_user_role')->insertBatch($insert);
		}
		
		$this->db->transComplete();
		return $this->db->transStatus();
	}
	
	/**
	 * Hitung total user yang tidak dihapus
	 * 
	 * @return int Jumlah user aktif
	 */
	public function countAllData() {
		return $this->db->table('core_user')
			->where('isDeleted', 0)
			->countAllResults();
	}
	
	/**
	 * Mendapatkan data user untuk DataTables dengan filter dan sorting
	 * 
	 * @param string $where Kondisi WHERE tambahan
	 * @return array Data user dan total filtered
	 */
	public function getListData() {
		$columns = $this->request->getPost('columns') ?: [];
		$allowedColumns = ['id_user', 'username', 'email', 'nama', 'created_at'];
		$builder = $this->db->table('core_user')
			->where('isDeleted', 0);
		
		$searchAll = $this->request->getPost('search')['value'] ?? '';
		if ($searchAll) {
			$builder->groupStart();
			$first = true;
			foreach ($columns as $val) {
				$columnName = $val['data'] ?? '';
				if (strpos($columnName, 'ignore') !== false || !in_array($columnName, $allowedColumns, true)) {
					continue;
				}

				if ($first) {
					$builder->like($columnName, $searchAll);
					$first = false;
				} else {
					$builder->orLike($columnName, $searchAll);
				}
			}
			$builder->groupEnd();
		}
		
		$totalFiltered = $builder->countAllResults(false);
		
		$orderData = $this->request->getPost('order');
		$columnsPost = $this->request->getPost('columns');
		
		if (!empty($orderData) && !empty($columnsPost[$orderData[0]['column']]['data'])) {
			$columnName = $columnsPost[$orderData[0]['column']]['data'];
			
			if (strpos($columnName, 'ignore') === false) {
				if (in_array($columnName, $allowedColumns, true)) {
					$direction = strtoupper($orderData[0]['dir']) === 'DESC' ? 'DESC' : 'ASC';
					$builder->orderBy($columnName, $direction);
				}
			}
		}

		$start = (int) ($this->request->getPost('start') ?: 0);
		$length = (int) ($this->request->getPost('length') ?: 10);
		$data = $builder->limit($length, $start)->get()->getResultArray();

		return ['data' => $data, 'total_filtered' => $totalFiltered];
	}
}
?>
