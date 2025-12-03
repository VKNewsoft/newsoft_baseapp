<?php
/**
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Controllers\Builtin;
use App\Models\Builtin\UserModel;
use \Config\App;

class User extends \App\Controllers\BaseController
{
	protected $model;
	protected $moduleURL;
	
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new UserModel;	
		$this->formValidation =  \Config\Services::validation();
		$this->data['site_title'] = 'Halaman Profil';
		
		$this->addJs($this->config->baseURL . 'public/themes/modern/builtin/js/user.js');
		$this->addJs($this->config->baseURL . 'public/themes/modern/builtin/js/image-upload.js');
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css' );
		
		$this->addStyle ( $this->config->baseURL . 'public/themes/modern/css/image-upload.css' );
		
		if ($this->request->getGet('mobile') == 'true') {
			$this->addJs($this->config->baseURL . 'public/themes/modern/builtin/js/user-mobile.js');
		}
		
		helper(['cookie', 'form']);
		// echo '<pre>'; print_r($this->userPermission); die;
	}
	
	public function index()
	{
		$this->hasPermissionPrefix('read');

		if ($this->request->getPost('delete')) 
		{
			$this->hasPermissionPrefix('delete');
			
			$result = $this->model->deleteUser();
			if ($result) {
				$data['message'] = ['status' => 'ok', 'message' => 'Data user berhasil dihapus'];
			} else {
				$data['message'] = ['status' => 'warning', 'message' => 'Tidak ada data yang dihapus'];
			}
		}
		
		$data['title'] = 'Data User';		
		$this->view('builtin/user/result.php', array_merge($data, $this->data));
	}
	
	public function getDataDT() {
		
		$this->hasPermission('read_all');
		
		$numUsers = $this->model->countAllUsers();
		$user = $this->model->getListUsers('');
		$aksesData = $this->model->getTenantRaw();
		$keys = array_column($aksesData, 'id_company');
		
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $numUsers;
		$result['recordsFiltered'] = $user['total_filtered'];		
		
		helper('html');
		
		foreach ($user['data'] as $key => &$val) {			
			$role = '';
			if ($val['judul_role']) {
				$split = explode(',', $val['judul_role']);
				foreach ($split as $judul_role) {
					$role .= '<span class="badge bg-secondary me-2">' . $judul_role . '</span>';
				}	
			}

			$val['ignore_access_company'] = "";
			if($val['access_company']){
				$splitter = explode(",",$val['access_company']);
				$accessCompany = "";
				$counter = 0;
				foreach ($splitter as $vAccess) {
					$key = array_search($vAccess, $keys);
					$accessCompany .= '<span class="badge bg-secondary me-2">' . $aksesData[$key]['nama_company'] . '</span>';
					$counter++;
					if ($counter % 4 == 0 && $counter < count($splitter)) {
						$accessCompany .= '<br>';
					}
				}
				$val['ignore_access_company'] = '<div style="white-space: break-spaces;">' . $accessCompany . '</div>';
			}

			$val['judul_role'] = '<div style="white-space:break-spaces">' . $role . '</div>';
			$val['nama_company'] = '<span class="badge bg-secondary me-2">' . $val['nama_company'] . '</span>';			
			$val['verified'] =  $val['verified'] == 1 ? 'Ya' : 'Tidak' ;

			$actions = [
				['type' => 'link', 'href' => $this->moduleURL . '/edit?id='. $val['id_user'], 'icon' => 'fas fa-edit text-success', 'label' => 'Edit', 'attrs' => ['class' => 'btn-edit', 'data-id' => $val['id_module']]],
			];
			if ($this->hasPermission('delete_own') || $this->hasPermission('delete_all')) {
				$actions[] = ['type' => 'form', 'action' => $this->moduleURL, 'icon' => 'fas fa-times text-danger', 'label' => 'Delete', 'attrs' => ['data-action' => 'delete-data', 'id' => $val['id_user'], 'data-delete-title' => 'Hapus data user: <strong>'.$val['nama'].'</strong> ?']];
			}
			$val['ignore_btn_action'] = btn_dropdown_actions($actions);
		}
					
		$result['data'] = $user['data'];
		echo json_encode($result); exit();
	}

	
	/**
	 * Method getDataTenant - Ambil data tenant berdasarkan ID
	 * @param int $idTenant - ID tenant yang akan diambil (optional)
	 * @return array - Data tenant
	 */
	public function getDataTenant($idTenant = null) 
	{
		$data = [
			'id_tenant' => $idTenant ?: '',
			'tenant' => $this->model->getTenant()
		];
		
		return $data;
	}
	
	/**
	 * Method add - Halaman form tambah user baru
	 * Menampilkan form dan proses submit data user baru
	 */
	public function add() 
	{
		// Cek permission untuk create data
		$this->hasPermission('create');
		
		// Set data awal untuk form
		$this->setData();
		$data = $this->data;
		$data['title'] = 'Tambah User';
		
		// Ambil setting registrasi
		$settingReg = $this->model->getSettingRegister();
		$data['setting_registrasi'] = [];
		foreach ($settingReg as $val) {
			$data['setting_registrasi'][$val['param']] = $val['value'];
		}
		
		// Set tenant utama dari session user
		$userSession = $this->session->get('user');
		$data['id_tenant_utama'] = $userSession['id_tenant'] ?? $userSession['id_company'] ?? 0;
		$data['id_company_utama'] = $userSession['id_company'] ?? $userSession['id_tenant'] ?? 0;
		$data['user_edit']['id_tenant'] = $data['id_tenant_utama'];
		$data['user_edit']['id_company'] = $data['id_company_utama'];
		
		// Proses submit form
		if ($this->request->getPost('submit')) {
			$data['message'] = $this->saveData();
			
			// Jika berhasil, ambil data user yang baru dibuat
			if ($data['message']['status'] == 'ok') {
				$result = $this->model->getUserById($data['message']['id_user'], true);
				
				if (!$result) {
					$this->errorDataNotFound();
					return;
				}
				
				$data = array_merge($data, $result);
			}
		}
		
		// Ambil data tenant dan tenant access
		$dataTenant = $this->getDataTenant($data['user_edit']['id_tenant'] ?? null);
		$data = array_merge($data, $dataTenant);
		
		$data['tenant_access'] = $this->model->getTenantRaw();
		
		// Tampilkan view form
		$this->view('builtin/user/form.php', $data);
	}	public function edit()
	{
		$this->hasPermissionPrefix('update');
		
		$this->setData();
		$data = $this->data;
		$data['title'] = 'Edit User';
		$breadcrumb['Edit'] = '';
			
		// Submit
		$data['message'] = [];
		if ($this->request->getPost('submit')) 
		{
		// print_r($this->request->getPost());die;

			$saveMessage = $this->saveData();
			if ($this->request->getGet('mobile') == 'true') {
				echo json_encode($saveMessage);
				exit;
			}
			$data = array_merge( $data, $saveMessage);
		}
		
		$result = $this->model->getUserById($this->request->getGet('id'), true);
		
		if (!$result) {
			$this->errorDataNotFound();
			return;
		}
		
		$data['user_edit'] = $result;
		
		// Ambil data tenant berdasarkan id_company dari user yang diedit
		$dataTenant = $this->getDataTenant($result['id_company'] ?? null);
		
		// Set ID tenant/company utama dari session (untuk backward compatibility)
		$userSession = $this->session->get('user');
		$data['id_tenant_utama'] = $userSession['id_tenant'] ?? $userSession['id_company'] ?? 0;
		$data['id_company_utama'] = $userSession['id_company'] ?? $userSession['id_tenant'] ?? 0;
		
		$data = array_merge($data, $dataTenant);

		$data['tenant_access'] = $this->model->getTenantRaw();

		if ($this->request->getGet('mobile') == 'true') {
			echo view('themes/modern/builtin/user/form.php', $data);
		} else {
			$this->view('builtin/user/form.php', $data);
		}
	}
	
	public function setData() {
		$this->data['roles'] = $this->model->getRoles();
		$this->data['user_permission'] = $this->userPermission;
		$this->data['list_module'] = $this->model->getListModules();
	}
	
	private function saveData() 
	{		
		$formErrors = $this->validateForm();
		$error = false;		
		
		if ($formErrors) {
			$data['status'] = 'error';
			$data['form_errors'] = $formErrors;
			$data['message'] = $formErrors;
			$error = true;
		}
		
		if (!$error) {				
			$data = $this->model->saveData($this->userPermission);
		}
		
		return $data;
	}
	
	private function validateForm() {
	
		$validation =  \Config\Services::validation();
		$validation->setRule('nama', 'Nama', 'trim|required');
		$validation->setRule('username', 'Username', 'trim|required');
		$validation->setRule('email', 'Email', 'trim|required|valid_email');
		
		if ($this->request->getPost('id')) {
			if ($this->request->getPost('email') != $this->request->getPost('email_lama')) {
				// echo 'sss'; die;
				$validation->setRules(
					['email' => [
							'label'  => 'Email',
							'rules'  => 'required|valid_email|is_unique[core_user.email]',
							'errors' => [
								'is_unique' => 'Email sudah digunakan'
								, 'valid_email' => 'Alamat email tidak valid'
							]
						]
					]
					
				);
			}
		} else {
			if ($this->hasPermission('update_all')) 
			{
				$validation->setRule('password', 'Password', 'trim|required|min_length[3]');
				$validation->setRules(
					[
						'email' => [
							'label'  => 'Email',
							'rules'  => 'required|valid_email|is_unique[core_user.email]',
							'errors' => [
								'is_unique' => 'Email sudah digunakan'
								, 'valid_email' => 'Alamat email tidak valid'
							]
						],
						'ulangi_password' => [
							'label'  => 'Ulangi Password',
							'rules'  => 'required|matches[password]',
							'errors' => [
								'required' => 'Ulangi password tidak boleh kosong'
								, 'matches' => 'Ulangi password tidak cocok dengan password'
							]
						]
					]
					
				);
			}
		}
		
		$valid = $validation->withRequest($this->request)->run();
		$form_errors = $validation->getErrors();

		$file = $this->request->getFile('avatar');
		if ($file && $file->getName())
		{
			if ($file->isValid())
			{
				$type = $file->getMimeType();
				$allowed = ['image/png', 'image/jpeg', 'image/jpg'];
				
				if (!in_array($type, $allowed)) {
					$form_errors['avatar'] = 'Tipe file harus ' . join($allowed, ', ');
				}
				
				if ($file->getSize() > 300 * 1024) {
					$form_errors['avatar'] = 'Ukuran file maksimal 300Kb';
				}
				
				$info = \Config\Services::image()
						->withFile($file->getTempName())
						->getFile()
						->getProperties(true);
				
				if ($info['height'] < 100 || $info['width'] < 100) {
					$form_errors['avatar'] = 'Dimensi file minimal: 100px x 100px';
				}
				
			} else {
				$form_errors['avatar'] = $file->getErrorString().'('.$file->getError().')';
			}
		}
		
		return $form_errors;
	}
	
	public function edit_password()
	{
		$data['title'] = 'Edit Password';
		$breadcrumb['Edit Password'] = '';
		
		$formErrors = null;
		$this->data['status'] = '';
		
		if ($this->request->getPost('submit')) 
		{
			$result = $this->model->getUserById();
			$error = false;
			
			if ($result) {
				
				if (!password_verify($this->request->getPost('password_old'), $result['password'])) {
					$error = true;
					$this->data['message'] = ['status' => 'error', 'message' => 'Password lama tidak cocok'];
				}
			} else {
				$error = true;
				$this->data['message'] = ['status' => 'error', 'message' => 'Data user tidak ditemukan'];
			}
		
			if (!$error) {
		
				$this->formValidation->setRule('password_new', 'Password', 'trim|required');
				$this->formValidation->setRule('password_new_confirm', 'Confirm Password', 'trim|required|matches[password_new]');
					
				$this->formValidation->withRequest($this->request)->run();
				$errors = $this->formValidation->getErrors();
				
			$custom_validation = new \App\Libraries\FormValidation;
			$custom_validation->checkPassword('password_new', $this->request->getPost('password_new'));
		
			$formErrors = array_merge($custom_validation->getErrors(), $errors);
				
				if ($formErrors) {
					$this->data['message'] = ['status' => 'error', 'message' => $formErrors];
				} else {
					$update = $this->model->updatePassword();
					if ($update) {
						$this->data['message'] = ['status' => 'ok', 'message' => 'Password berhasil diupdate'];
					} else {
						$this->data['message'] = ['status' => 'error', 'message' => 'Password gagal diupdate... Mohon hubungi admin. Terima Kasih...'];
					}
				}
			}
			
			if ($this->request->getGet('mobile') == 'true') {
				echo json_encode($this->data['message']);
				exit;
			}
		}		$this->data['title'] = 'Edit Password';
		$this->data['form_errors'] = $formErrors;
		$this->data['user'] = $this->model->getUserById($this->user['id_user']);
		
		if ($this->request->getGet('mobile') == 'true') {
			echo view('themes/modern/builtin/user/form-edit-password.php', $this->data);
		} else {
			$this->view('builtin/user/form-edit-password.php', $this->data);
		}
	}

	// AJAX methods for real-time validation
	public function ajaxCheckUsername()
	{
		$username = $this->request->getPost('username') ?: '';
		$idUser = $this->request->getPost('id_user') ?: 0;

		if (empty($username)) {
			echo json_encode(['available' => false, 'message' => 'Username tidak boleh kosong']);
			return;
		}

		// Jika sedang edit dan username sama dengan yang lama, anggap tersedia
		if ($idUser > 0) {
			$currentUser = $this->model->getUserById($idUser);
			if ($currentUser && $currentUser['username'] === $username) {
				echo json_encode([
					'available' => true,
					'message' => 'Username valid'
				]);
				return;
			}
		}

		// Check if username exists (excluding current user if editing)
		$builder = $this->model->db->table('core_user');
		$builder->where('username', $username);
		$builder->where('isDeleted', 0);
		if ($idUser > 0) {
			$builder->where('id_user !=', $idUser);
		}
		$exists = $builder->countAllResults() > 0;

		echo json_encode([
			'available' => !$exists,
			'message' => $exists ? 'Username sudah digunakan' : 'Username tersedia'
		]);
	}

	public function ajaxCheckEmail()
	{
		$email = $this->request->getPost('email') ?: '';
		$emailLama = $this->request->getPost('email_lama') ?: '';

		if (empty($email)) {
			echo json_encode(['available' => false, 'message' => 'Email tidak boleh kosong']);
			return;
		}

		// Jika sedang edit dan email sama dengan yang lama, anggap tersedia
		if (!empty($emailLama) && $email === $emailLama) {
			echo json_encode([
				'available' => true,
				'message' => 'Email valid'
			]);
			return;
		}

		// Check if email exists (excluding current email if editing)
		$builder = $this->model->db->table('core_user');
		$builder->where('email', $email);
		$builder->where('isDeleted', 0);
		if (!empty($emailLama)) {
			$builder->where('email !=', $emailLama);
		}
		$exists = $builder->countAllResults() > 0;

		echo json_encode([
			'available' => !$exists,
			'message' => $exists ? 'Email sudah digunakan' : 'Email tersedia'
		]);
	}
}
