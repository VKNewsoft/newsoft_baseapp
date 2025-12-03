<?php
/**
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Controllers\Builtin;
use App\Models\Builtin\ModuleModel;

class Module extends \App\Controllers\BaseController
{
	protected $model;
	private $formValidation;
	
	public function __construct() {
		
		parent::__construct();
		$this->model = new ModuleModel;	
		$this->data['site_title'] = 'Module';
		$this->addJs ($this->config->baseURL . 'public/themes/modern/builtin/js/module.js');
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
		
		if(!empty($_POST['submit'])){
			$_SESSION['msg'] = $data['message'];
			$this->session->markAsFlashdata('msg');
			return redirect()->to('builtin/module');
		}else{
			$this->view('builtin/module-form.php', $data);
		}
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
		
		$files = \list_files('app/Controllers');
		$files = array_map('strtolower', $files);
		
		foreach ($query['data'] as $key => &$val) 
		{
			$checked = $val['id_module_status'] == 1 ? 'checked' : '';
			// Disbled module builtin/module
			$disabled = $this->currentModule == $val['nama_module'] ? ' disabled' : '';
			$file_exists = in_array( str_replace('-', '_', $val['nama_module']) . '.php', $files) ? 'Ada' : 'Tidak Ada';
			
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
