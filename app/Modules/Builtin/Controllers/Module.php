<?php
/**
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Modules\Builtin\Controllers;
use App\Modules\Builtin\Models\ModuleModel;

class Module extends \App\Modules\Common\Controllers\BaseController
{
	protected $model;
	private $formValidation;

	private function getModuleControllerPath($namaModule)
	{
		$map = [
			'login' => APPPATH . 'Modules/Login/Controllers/Login.php',
			'welcome' => APPPATH . 'Modules/Welcome/Controllers/Welcome.php',
			'dashboard' => APPPATH . 'Modules/Dashboard/Controllers/Dashboard.php',
			'company' => APPPATH . 'Modules/Company/Controllers/Company.php',
			'filepicker' => APPPATH . 'Modules/Filepicker/Controllers/Filepicker.php',
			'identitas' => APPPATH . 'Modules/Identitas/Controllers/Identitas.php',
			'installer' => APPPATH . 'Modules/Installer/Controllers/Installer.php',
			'midtrans' => APPPATH . 'Modules/Midtrans/Controllers/Midtrans.php',
			'recovery' => APPPATH . 'Modules/Recovery/Controllers/Recovery.php',
			'register' => APPPATH . 'Modules/Register/Controllers/Register.php',
			'securitymonitor' => APPPATH . 'Modules/SecurityMonitor/Controllers/SecurityMonitor.php',
			'wilayah' => APPPATH . 'Modules/Builtin/Controllers/Wilayah.php',
			'builtin/menu' => APPPATH . 'Modules/Builtin/Controllers/Menu.php',
			'builtin/menu-role' => APPPATH . 'Modules/Builtin/Controllers/Menu_role.php',
			'builtin/module' => APPPATH . 'Modules/Builtin/Controllers/Module.php',
			'builtin/module-role' => APPPATH . 'Modules/Builtin/Controllers/Module_role.php',
			'builtin/permission' => APPPATH . 'Modules/Builtin/Controllers/Permission.php',
			'builtin/qrscan' => APPPATH . 'Modules/Builtin/Controllers/Qrscan.php',
			'builtin/role' => APPPATH . 'Modules/Builtin/Controllers/Role.php',
			'builtin/role-permission' => APPPATH . 'Modules/Builtin/Controllers/Role_permission.php',
			'builtin/setting-app' => APPPATH . 'Modules/Builtin/Controllers/Setting_app.php',
			'builtin/setting-layout' => APPPATH . 'Modules/Builtin/Controllers/Setting_layout.php',
			'builtin/setting-registrasi' => APPPATH . 'Modules/Builtin/Controllers/Setting_registrasi.php',
			'builtin/user' => APPPATH . 'Modules/Builtin/Controllers/User.php',
			'builtin/user-role' => APPPATH . 'Modules/Builtin/Controllers/User_role.php',
		];

		if (isset($map[$namaModule])) {
			return $map[$namaModule];
		}

		if (strpos($namaModule, 'builtin/') === 0) {
			$controllerName = str_replace(' ', '', ucwords(str_replace('-', ' ', substr($namaModule, 8))));
			return APPPATH . 'Modules/Builtin/Controllers/' . $controllerName . '.php';
		}

		$controllerName = str_replace(' ', '', ucwords(str_replace('-', ' ', $namaModule)));
		return APPPATH . 'Modules/' . $controllerName . '/Controllers/' . $controllerName . '.php';
	}
	
	public function __construct() {
		
		parent::__construct();
		$resultPageVersion = '?v=' . @filemtime(ROOTPATH . 'public/themes/modern/css/result-page.css');
		$resultTableVersion = '?v=' . @filemtime(ROOTPATH . 'public/themes/modern/js/result-table.js');
		$this->model = new ModuleModel;	
		$this->data['site_title'] = 'Module';
		$this->addJs ($this->config->baseURL . 'public/themes/modern/js/result-table.js' . $resultTableVersion);
		$this->addJs ($this->config->baseURL . 'public/themes/modern/builtin/js/module.js');
		$this->addStyle ($this->config->baseURL . 'public/themes/modern/css/result-page.css' . $resultPageVersion);
	}
	
	public function index()
	{
		$this->hasPermission('read_all');
		$data = $this->data;
		$data['msg'] = [];
		if($this->session->has('msg')){
			$data['msg'] = $this->session->get('msg');
		}
		$this->view('builtin/module-result.php', $data);
	}
	
	public function delete() {
		$result = $this->model->deleteData();
		// $result = false;
		if ($result) {
			$message = ['status' => 'ok', 'message' => 'Data role berhasil dihapus'];
		} else {
			$message = ['status' => 'error', 'message' => 'Data role gagal dihapus'];
		}
		echo json_encode($message);
	}
	
	public function ajaxSwitchModuleStatus() {
		
		// Module Aktif/Nonaktif/Login
		if ($this->request->getPost('change_module_attr')) 
		{
			$updateStatus = $this->model->updateStatus();
					
			if ($this->request->getPost('ajax')) {
				if ($updateStatus) {
					echo 'ok';
				} else {
					echo 'error';
				}
			}
		}
	}
	
	public function add() 
	{
		$this->hasPermission('create');
		
		$this->setData();
		$this->data['module_status'] = $this->model->getAllModuleStatus();
		$data = $this->data;
		
		$breadcrumb['Add'] = '';
		$data['title'] = 'Tambah ' . $this->currentModule['judul_module'];
		$data['message'] = [];
		
		if ($this->request->getPost('submit'))
		{
			$save_message = $this->saveData();
			$data['message'] = $save_message;
		}
		
		if ($this->request->getPost('submit')) {
			$this->session->set('msg', $data['message']);
			$this->session->markAsFlashdata('msg');
			return redirect()->to('builtin/module');
		}

		$this->view('builtin/module-form.php', $data);
	}
	
	public function edit()
	{
		$this->hasPermission('update_all');
		
		$saveMessage = [];
		if ($this->request->getPost('submit'))
		{
			$saveMessage = $this->saveData();
		}
		
		$this->setData($this->request->getGet('id'));
		$data = $this->data;
		$data['message'] = $saveMessage;
		
		$data['title'] = 'Edit Data Module';
		
		$module = $this->model->getModule($this->request->getGet('id'));
		$data = array_merge($data, $module);

		$data['module_status'] = $this->model->getAllModuleStatus();
		$breadcrumb['Edit'] = '';

		if($this->request->getPost('submit')){
			$this->session->set('msg', $data['message']);
			$this->session->markAsFlashdata('msg');
			return redirect()->to('builtin/module');
		}else{
			$this->view('builtin/module-form.php', $data);
		}
	}
	
	private function setData($id_module = null) 
	{
		$this->data['id'] = $id_module;
		$this->data['role_permission_module'] = [];
		$this->data['module_permission'] = [];
		if ($id_module){
			$this->data['module'] = $this->model->getModule($id_module);
			$this->data['role_permission_module'] = $this->model->getRolePermissionByModule($id_module);
			$this->data['module_permission'] = $this->model->getModulePermission($id_module);
		}
		$list_role = $this->model->getAllRoles();
		$roles = [];
		foreach ($list_role as $val) {
			$roles[$val['id_role']] = $val;
		}
		$this->data['roles'] = $roles;
		
	}
	
	private function saveData() 
	{
		$unique = false;
		if ($this->request->getPost('nama_module') != $this->request->getPost('nama_module_old')) {
			$unique = true;
		}
		
		$formErrors = $this->validateForm($unique);
	
		if ($formErrors) {
			$data['status'] = 'error';
			$data['form_errors'] = $formErrors;
			$data['message'] = $formErrors;
		} else {
			$data = $this->model->saveData();
		}
		
		return $data;
	}
	
	private function validateForm($check_unique = false) {
	
		$validation =  \Config\Services::validation();
		$unique = '';
		if ($check_unique) {
			$unique = '|is_unique[core_module.nama_module]';
		}
		$validation->setRule('nama_module', 'Nama Module', 'trim|required' . $unique);
		$validation->setRule('judul_module', 'Judul Module', 'trim|required');
		$validation->setRule('deskripsi', 'Deskripsi Module', 'trim|required');
		$validation->setRule('id_module_status', 'ID Module Status', 'trim|required');
		$validation->withRequest($this->request)->run();
		$form_errors = $validation->getErrors();
		
		return $form_errors;
	}
	
	public function getDataDT() {
		
		$this->hasPermission('read_all');
		
		$num_data = $this->model->countAllData();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListData();
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');		
		$no = $this->request->getPost('start') + 1 ?: 1;
		$login = ['Y' => 'Ya', 'N' => 'Tidak', 'R' => 'Restrict'];
		
		foreach ($query['data'] as $key => &$val) 
		{
			$checked = $val['id_module_status'] == 1 ? 'checked' : '';
			// Disbled module builtin/module
			$disabled = $this->currentModule == $val['nama_module'] ? ' disabled' : '';
			$file_exists = is_file($this->getModuleControllerPath($val['nama_module'])) ? 'Ada' : 'Tidak Ada';
			
			$val['login'] = $login[$val['login']];
			$val['ignore_file_exists'] = $file_exists;
			$val['ignore_aktif'] = '<div class="form-switch">
								<input name="aktif" type="checkbox" class="form-check-input switch" data-module-id="'.$val['id_module'].'" ' . $checked . $disabled . '>
							</div>';
			$val['ignore_no_urut'] = $no;
			$val['ignore_action'] = btn_dropdown_actions([
				['type' => 'link', 'href' => base_url() . '/builtin/module/edit?id=' . $val['id_module'], 'icon' => 'fas fa-edit text-success', 'label' => 'Edit', 'attrs' => ['class' => 'btn-edit', 'data-id' => $val['id_module']]],
				['type' => 'button','icon' => 'fas fa-times text-danger', 'label' => 'Delete', 'attrs' => ['class' => 'btn-delete', 'data-id' => $val['id_module'], 'data-delete-title' => 'Hapus data module: <strong>' . $val['judul_module'] . '</strong>']]
			]);
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
	
}
