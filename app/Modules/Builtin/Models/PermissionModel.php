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

namespace App\Modules\Builtin\Models;

class PermissionModel extends \App\Modules\Common\Models\BaseModel
{
	/**
	 * Ambil nilai request dengan prioritas override payload
	 *
	 * @param string $key
	 * @param array $payload
	 * @return mixed
	 */
	private function getInputValue(string $key, array $payload = [])
	{
		if (array_key_exists($key, $payload)) {
			return $payload[$key];
		}

		return $this->request->getPost($key);
	}

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
	private function checkPermissionExists($permission, array $payload = []) 
	{
		$idModule = (int) $this->getInputValue('id_module', $payload);
		
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
	private function saveCrud(array $payload = []) 
	{
		$keterangan = ['membuat', 'membaca', 'mengupdate', 'menghapus'];
		$listPermission = ["create", "read_all", "update_all", "delete_all"];
		$permissionExists = $this->checkPermissionExists($listPermission, $payload);
		$idModule = (int) $this->getInputValue('id_module', $payload);
		
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
			
			// Cek jika insert gagal
			if ($this->db->insertID() == 0) {
				$error = $this->db->error();
				if (!empty($error['message'])) {
					throw new \RuntimeException('Gagal insert permission ' . $namaPermission . ': ' . $error['message']);
				}
			}
		}
	}
	
	/**
	 * Generate permission CRUD Own (create, read_own, update_own, delete_own)
	 * 
	 * @return void
	 */
	private function saveCrudOwn(array $payload = []) 
	{
		$keterangan = ['membuat', 'membaca', 'mengupdate', 'menghapus'];
		$listPermission = ["create", "read_own", "update_own", "delete_own"];
		$permissionExists = $this->checkPermissionExists($listPermission, $payload);
		$idModule = (int) $this->getInputValue('id_module', $payload);
		
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
			
			// Cek jika insert gagal
			if ($this->db->insertID() == 0) {
				$error = $this->db->error();
				if (!empty($error['message'])) {
					throw new \RuntimeException('Gagal insert permission ' . $namaPermission . ': ' . $error['message']);
				}
			}
		}
	}
	
	/**
	 * Simpan data permission (manual atau auto-generate)
	 * 
	 * @return array Status dan ID permission
	 */
	public function saveData(array $payload = []) 
	{
		$this->db->transStart();
		
		$result = $this->saveDataWithoutTransaction($payload);
		
		// Cek dulu apakah saveDataWithoutTransaction() sudah return error
		if (isset($result['status']) && $result['status'] === 'error') {
			$this->db->transRollback();
			return $result;
		}
		
		$this->db->transComplete();
		
		if ($this->db->transStatus() == false) {
			return [
				'status' => 'error',
				'message' => 'Data gagal disimpan',
				'id' => $result['id'] ?? ''
			];
		}
		
		return $result;
	}
	
	/**
	 * Simpan data permission tanpa transaction wrapper
	 * Digunakan ketika dipanggil dari dalam transaction lain (seperti dari ModuleModel)
	 * 
	 * @return array Status dan ID permission
	 */
	public function saveDataWithoutTransaction(array $payload = []) 
	{
		$idNew = '';
		$generatePermission = $this->getInputValue('generate_permission', $payload);
		
		if ($generatePermission) {
			try {
				if ($generatePermission == 'crud_all') {
					$this->saveCrud($payload);
				} elseif ($generatePermission == 'crud_own') {
					$this->saveCrudOwn($payload);
				} elseif ($generatePermission == 'crud_all_crud_own') {
					$this->saveCrud($payload);
					$this->saveCrudOwn($payload);
				} else {
				// Manual permission
				$dataDb = [
					'id_module' => (int) $this->getInputValue('id_module', $payload),
					'nama_permission' => $this->getInputValue('nama_permission', $payload),
					'judul_permission' => $this->getInputValue('judul_permission', $payload),
					'keterangan' => $this->getInputValue('keterangan', $payload)
				];
				
				if (empty($this->getInputValue('id', $payload))) {
					// Insert new permission
					$this->db->table('core_module_permission')->insert($dataDb);
					$idNew = $this->db->insertID();
					
					// Cek apakah insert berhasil
					if (!$idNew || $idNew == 0) {
						$error = $this->db->error();
						return [
							'status' => 'error',
							'message' => 'Gagal insert permission: ' . ($error['message'] ?? '')
						];
					}
				} else {
					$this->db->table('core_module_permission')
						->update($dataDb, ['id_module_permission' => (int) $this->getInputValue('id', $payload)]);
				}
			}
			
			// Auto-assign permission ke role jika ada
			if (!empty($this->getInputValue('id_role', $payload))) {
				$idModule = (int) $this->getInputValue('id_module', $payload);
				
				$modulePermission = $this->db->table('core_module_permission')
					->where('id_module', $idModule)
					->get()
					->getResultArray();
				
				$values = [];
				foreach ($modulePermission as $val) {
					$values[] = [
						'id_role' => (int) $this->getInputValue('id_role', $payload),
						'id_module_permission' => $val['id_module_permission']
					];
				}
				
				if ($values) {
					// insertBatch return value bisa berbeda tergantung driver
					// Lebih aman cek affectedRows() setelah insert
					$this->db->table('core_role_module_permission')->insertBatch($values);
					
					if ($this->db->affectedRows() === 0) {
						// Tidak fatal, mungkin sudah ada atau batch kosong
						// Tidak return error
					}
				}
			}
			} catch (\RuntimeException $e) {
				return [
					'status' => 'error',
					'message' => $e->getMessage()
				];
			}
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
	public function countAllData() 
	{
		return $this->db->table('core_module_permission')->countAllResults();
	}
	
	/**
	 * Mendapatkan list data permission untuk DataTables
	 * Menggunakan parameter binding untuk mencegah SQL injection
	 * 
	 * @param string $where Kondisi WHERE tambahan
	 * @return array Data permission dengan total filtered
	 */
	public function getListData() 
	{
		$columns = $this->request->getPost('columns') ?: [];
		
		// Whitelist kolom yang diizinkan
		$allowedColumns = ['id_module_permission', 'nama_permission', 'judul_permission', 'keterangan', 'id_module', 'nama_module', 'judul_module'];
		
		// Build query dengan Query Builder
		$builder = $this->db->table('core_module_permission')
			// Pilih field inti untuk result page agar query awal permission lebih ringan.
			->select('core_module_permission.id_module_permission, core_module_permission.id_module, core_module_permission.nama_permission, core_module_permission.judul_permission, core_module_permission.keterangan, core_module.judul_module')
			->join('core_module', 'core_module_permission.id_module = core_module.id_module');
		
		$searchValue = $this->request->getPost('search')['value'] ?? '';
		if ($searchValue) {
			$builder->groupStart();
			foreach ($columns as $column) {
				$columnData = $column['data'] ?? '';
				
				if (strpos($columnData, 'ignore_search') !== false || 
					strpos($columnData, 'ignore') !== false ||
					!in_array($columnData, $allowedColumns, true)) {
					continue;
				}
				
				$builder->orLike($columnData, $searchValue);
			}
			$builder->groupEnd();
		}
		
		$totalFiltered = $builder->countAllResults(false);
		
		// Order By
		$orderData = $this->request->getPost('order');
		if ($orderData && isset($columns[$orderData[0]['column']])) {
			$orderColumn = $columns[$orderData[0]['column']]['data'] ?? '';
			$orderDir = strtoupper($orderData[0]['dir']) === 'DESC' ? 'DESC' : 'ASC';
			
			if (strpos($orderColumn, 'ignore') === false && in_array($orderColumn, $allowedColumns, true)) {
				$builder->orderBy($orderColumn, $orderDir);
			}
		}
		
		// Limit dan Offset
		$start = (int) ($this->request->getPost('start') ?? 0);
		$length = (int) ($this->request->getPost('length') ?? 10);
		$builder->limit($length, $start);
		
		$data = $builder->get()->getResultArray();
		
		return [
			'data' => $data,
			'total_filtered' => $totalFiltered
		];
	}
}
?>
