<?php
/**
 * UserModel - Model untuk manajemen data user
 * 
 * Model ini menangani semua operasi database yang berkaitan dengan user
 * termasuk CRUD, role management, dan autentikasi
 * 
 * @package App\Models\Builtin
 */

namespace App\Models\Builtin;

class UserModel extends \App\Models\BaseModel
{
	/**
	 * Mendapatkan daftar user untuk DataTables dengan filter dan search
	 * 
	 * @param string $where Kondisi WHERE tambahan
	 * @return array Array berisi data user dan total filtered
	 */
	public function getListUsers($where) 
	{
		// Ambil kolom dari DataTables request
		$columns = $this->request->getPost('columns');
		$where .= " AND core_user.isDeleted = 0";
		
		// Daftar kolom yang diizinkan untuk sorting/searching (whitelist)
		$allowedColumns = [
			'id_user', 'nama', 'username', 'email', 'status', 
			'verified', 'nama_company', 'judul_role'
		];
		
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
				$order = " ORDER BY id_user DESC LIMIT {$start}, {$length}";
			}
		} else {
			$order = " ORDER BY id_user DESC LIMIT {$start}, {$length}";
		}
		
		// Query untuk menghitung total filtered
		$sql = "SELECT COUNT(*) as jml FROM
				(SELECT core_user.*, GROUP_CONCAT(judul_role) AS judul_role,
				core_company.nama_company
				FROM core_user 
				LEFT JOIN core_company USING(id_company) 
				LEFT JOIN core_user_role USING(id_user) 
				LEFT JOIN core_role ON core_user_role.id_role = core_role.id_role
				{$where}
				GROUP BY id_user) AS tabel";
		
		$query = $this->db->query($sql)->getRowArray();
		$totalFiltered = $query['jml'];
		
		// Query untuk mengambil data user
		$sql = "SELECT core_user.*, GROUP_CONCAT(judul_role) AS judul_role,
				core_company.nama_company
				FROM core_user 
				LEFT JOIN core_company USING(id_company) 
				LEFT JOIN core_user_role USING(id_user) 
				LEFT JOIN core_role ON core_user_role.id_role = core_role.id_role
				{$where}
				GROUP BY id_user
				{$order}";
		
		$data = $this->db->query($sql)->getResultArray();
		
		return [
			'data' => $data, 
			'total_filtered' => $totalFiltered
		];
	}
	
	/**
	 * Hitung total semua user (untuk DataTables)
	 * 
	 * @param string|null $where Kondisi WHERE tambahan dalam format SQL (sudah include WHERE)
	 * @return int Jumlah user
	 */
	public function countAllUsers($where = null) 
	{
		// $where dari controller sudah include "WHERE", jadi kita replace dengan AND
		$whereClause = $where ?: ' WHERE 1 = 1';
		$whereClause = str_replace(' WHERE ', ' WHERE ', $whereClause); // Pastikan hanya ada 1 WHERE
		$whereClause .= ' AND isDeleted = 0';
		
		$sql = "SELECT COUNT(*) as jml FROM core_user" . $whereClause;
		$query = $this->db->query($sql)->getRow();
		
		return $query->jml;
	}
	
	/**
	 * Mendapatkan semua role yang tersedia
	 * 
	 * @return array Daftar role
	 */
	public function getRoles() 
	{
		return $this->db->table('core_role')
			->get()
			->getResultArray();
	}
	
	/**
	 * Mendapatkan setting registrasi
	 * 
	 * @return array Setting registrasi
	 */
	public function getSettingRegister() 
	{
		return $this->db->table('core_setting')
			->where('type', 'register')
			->get()
			->getResultArray();
	}
	
	/**
	 * Mendapatkan daftar semua module dengan statusnya
	 * 
	 * @return array Daftar module
	 */
	public function getListModules() 
	{
		return $this->db->table('core_module')
			->join('core_module_status', 'core_module.id_module_status = core_module_status.id_module_status')
			->orderBy('nama_module', 'ASC')
			->get()
			->getResultArray();
	}
		
	/**
	 * Simpan data user (create/update)
	 * Handle upload avatar, role assignment, dan access company
	 * 
	 * @param array $user_permission Permission user yang login
	 * @return array Status dan pesan hasil operasi
	 */
	public function saveData($user_permission = []) 
	{ 
		// Field yang selalu boleh diupdate
		$fields = ['nama', 'email'];
		
		// Field tambahan jika user punya permission update_all
		if (in_array('update_all', $user_permission)) {
			$additionalFields = ['username', 'status', 'verified', 'id_module', 'id_company'];
			$fields = array_merge($fields, $additionalFields);
		}

		// Ambil data dari POST sesuai field yang diizinkan
		$dataDb = [];
		foreach ($fields as $field) {
			$dataDb[$field] = $this->request->getPost($field);
		}
		
		// Mulai database transaction
		$this->db->transStart();
		
		// Set password hanya jika ada password baru yang di-input
		$password = $this->request->getPost('password');
		if ($password !== null && $password !== '') {
			$dataDb['password'] = password_hash($password, PASSWORD_DEFAULT);
		}

		// Handle access company (multiple tenant access)
		$accessCompany = $this->request->getPost('access_company');
		if (is_array($accessCompany) && !empty($accessCompany)) {
			// Validasi setiap ID company adalah numeric
			$accessCompany = array_filter($accessCompany, 'is_numeric');
			$dataDb['access_company'] = implode(',', $accessCompany);
		} else {
			// Default ke id_company user
			$dataDb['access_company'] = $dataDb['id_company'] ?? '';
		}
		
		// Insert atau Update data user
		$idUser = $this->request->getPost('id');
		
		if ($idUser) {
			// Update existing user
			$this->db->table('core_user')->update($dataDb, ['id_user' => $idUser]);
		} else {
			// Create new user
			$this->db->table('core_user')->insert($dataDb);
			$idUser = $this->db->insertID();
		}
				
		// Update user roles jika ada permission
		if (in_array('update_all', $user_permission)) {
			$roles = $this->request->getPost('id_role');
			
			if (is_array($roles) && !empty($roles)) {
				$roleData = [];
				foreach ($roles as $idRole) {
					// Validasi ID role adalah numeric
					if (is_numeric($idRole)) {
						$roleData[] = [
							'id_user' => $idUser, 
							'id_role' => $idRole
						];
					}
				}
				
				// Hapus role lama dan insert role baru
				$this->db->table('core_user_role')->delete(['id_user' => $idUser]);
				
				if (!empty($roleData)) {
					$this->db->table('core_user_role')->insertBatch($roleData);
				}
			}
		}
		
		// Selesaikan transaction
		$this->db->transComplete();
		$transStatus = $this->db->transStatus();
		
		$result = ['status' => 'error', 'message' => 'Gagal menyimpan data'];
		
		// Jika transaction berhasil, handle avatar upload
		if ($transStatus) {
			$result = $this->handleAvatarUpload($idUser);
		}

		// Jika berhasil dan user mengedit profil sendiri, reload session
		if ($result['status'] === 'ok') {
			$result['id_user'] = $idUser;
			
			if ($this->session->get('user')['id_user'] == $idUser) {
				// Reload data user di session
				$this->session->set('user', $this->getUserById($idUser));
			}
		}
								
		return $result;
	}
	
	/**
	 * Handle upload dan delete avatar user
	 * 
	 * @param int $idUser ID user
	 * @return array Status dan pesan hasil operasi
	 */
	private function handleAvatarUpload($idUser) 
	{
		$result = ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
		$file = $this->request->getFile('avatar');
		$path = ROOTPATH . 'public/images/user/';
		
		// Ambil nama avatar lama dari database
		$currentAvatar = $this->db->table('core_user')
			->select('avatar')
			->where('id_user', $idUser)
			->get()
			->getRowArray();
		
		$newAvatarName = $currentAvatar['avatar'] ?? '';
		
		// Jika user request hapus avatar
		if ($this->request->getPost('avatar_delete_img')) {
			if ($newAvatarName && file_exists($path . $newAvatarName)) {
				delete_file($path . $newAvatarName);
			}
			$newAvatarName = '';
		}
		
		// Jika ada file avatar baru yang diupload
		if ($file && $file->getName()) {
			// Hapus avatar lama jika ada
			if ($newAvatarName && file_exists($path . $newAvatarName)) {
				delete_file($path . $newAvatarName);
			}
			
			// Upload file baru
			helper('upload_file');
			$newAvatarName = get_filename($file->getName(), $path);
			$file->move($path, $newAvatarName);
			
			if (!$file->hasMoved()) {
				$result['status'] = 'error';
				$result['message'] = 'Error saat memproses gambar';
				return $result;
			}
		}
		
		// Update nama avatar di database
		$this->db->table('core_user')->update(
			['avatar' => $newAvatarName], 
			['id_user' => $idUser]
		);
		
		return $result;
	}
	
	/**
	 * Soft delete user (set isDeleted = 1)
	 * Juga menghapus role user dan file avatar
	 * 
	 * @return bool True jika berhasil, false jika gagal
	 */
	public function deleteUser() 
	{
		$idUser = $this->request->getPost('id');
		
		// Cek apakah user exists
		$user = $this->db->table('core_user')
			->where('id_user', $idUser)
			->get()
			->getRowArray();
		
		if (!$user) {
			return false;
		}

		// Data untuk soft delete
		$dataDb = [
			'tgl_edit' => date("Y-m-d H:i:s"),
			'id_user_edit' => $this->user['id_user'],
			'isDeleted' => 1
		];
		
		// Mulai transaction
		$this->db->transStart();
		
		// Soft delete user
		$this->db->table('core_user')->update($dataDb, ['id_user' => $idUser]);
		
		// Hapus semua role user
		$this->db->table('core_user_role')->delete(['id_user' => $idUser]);
		
		$this->db->transComplete();
		$transStatus = $this->db->transStatus();
		
		// Jika transaction berhasil, hapus file avatar
		if ($transStatus && !empty($user['avatar'])) {
			$avatarPath = ROOTPATH . 'public/images/user/' . $user['avatar'];
			if (file_exists($avatarPath)) {
				delete_file($avatarPath);
			}
		}
		
		return $transStatus;
	}

	/**
	 * Update password user
	 * 
	 * @return bool True jika berhasil
	 */
	public function updatePassword() 
	{
		$newPassword = $this->request->getPost('password_new');
		$passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
		
		return $this->db->table('core_user')
			->update(
				['password' => $passwordHash], 
				['id_user' => $this->user['id_user']]
			);
	}

	/**
	 * Mendapatkan daftar tenant/company untuk dropdown
	 * 
	 * @return array Array dengan id_company sebagai key
	 */
	public function getTenant() 
	{
		$query = $this->db->table('core_company')
			->where('isDeleted', 0)
			->get()
			->getResultArray();
		
		$result = [];
		foreach ($query as $val) {
			$result[$val['id_company']] = $val['nama_company'];
		}
		
		return $result;
	}

	/**
	 * Mendapatkan data tenant/company mentah (raw)
	 * 
	 * @return array Daftar tenant
	 */
	public function getTenantRaw() 
	{
		return $this->db->table('core_company')
			->where('isDeleted', 0)
			->get()
			->getResultArray();
	}
}
?>