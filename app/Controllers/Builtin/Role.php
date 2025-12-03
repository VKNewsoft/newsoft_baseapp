<?php
/**
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Controllers\Builtin;
use App\Models\Builtin\RoleModel;

class Role extends \App\Controllers\BaseController
{
	protected $model;
	private $formValidation;
	
	public function __construct() {
		
		parent::__construct();
		// $this->mustLoggedIn();
		
		$this->model = new RoleModel;	
		$this->data['site_title'] = 'Halaman Role';

		$this->addJs ( $this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css' );
		
		$this->addJs ($this->config->baseURL . 'public/themes/modern/builtin/js/role.js');
		
		helper(['cookie', 'form']);
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
	
	public function index()
	{
		$this->hasPermission('read_all');
		$this->setData();
		$data = $this->data;
		if ($this->request->getPost('delete')) 
		{
			$this->hasPermission('delete_all');;
			$result = $this->model->deleteData();
			if ($result) {
				$data['msg'] = ['status' => 'ok', 'message' => 'Data berhasil dihapus'];
			} else {
				$data['msg'] = ['status' => 'warning', 'message' => 'Tidak ada data yang dihapus'];
			}
		}
		
		$data['module'] = $this->model->getAllModules();
		$data['result'] = $this->model->getAllRole();
		
		$this->view('builtin/role-result.php', $data);
	}
	
	public function add() 
	{
		$this->hasPermission('create');
		
		$this->setData();
		$data = $this->data;
		
		$breadcrumb['Add'] = '';
		$data['title'] = 'Tambah ' . $this->currentModule['judul_module'];
		$data['msg'] = [];
		
		$error = false;
		if ($this->request->getPost('submit'))
		{
			$save_msg = $this->saveData();
			$data = array_merge( $data, $save_msg);
		}
		
		$this->view('builtin/role-form.php', $data);
	}
	
	public function edit()
	{
		$this->hasPermission('update_all');
		
		if (!$this->request->getGet('id')) {
			$this->printError(['status' => 'error', 'message' => 'Parameter tidak lengkap']);
			return;
		}
		
		$this->setData();
		$data = $this->data;
		$data['title'] = 'Edit ' . $this->currentModule['judul_module'];
		$breadcrumb['Edit'] = '';
	
		// Submit
		$data['msg'] = [];
		if ($this->request->getPost('submit')) 
		{
			$save = $this->saveData();
			$data = array_merge($data, $save);
		}

		$this->view('builtin/role-form.php', $data);
	}
	
	public function setData() {
		$this->data['module_role'] = $this->model->listModuleRole();
		$this->data['module_status'] = $this->model->getModuleStatus();
		$this->data['role'] = $this->model->getRole();
		$this->data['list_module'] = $this->model->getListModules();
	}
	
	private function saveData() 
	{
		$formErrors = $this->validateForm();
	
		if ($formErrors) {
			$data['msg']['status'] = 'error';
			$data['form_errors'] = $formErrors;
			$data['msg']['message'] = $formErrors;
		} else {
			$save = $this->model->saveData();
			if ($save['status'] == 'ok') {
				$data['msg']['status'] = 'ok';
				$data['msg']['message'] = 'Data berhasil disimpan';
			} else {
				$data['msg']['status'] = 'error';
				$data['msg']['message'] = $save['message'];
			}
		}
		
		return $data;
	}
	
	private function validateForm() {

		$validation =  \Config\Services::validation();
		if ($this->request->getPost('id_role') == '') {
			$validation->setRule('nama_role', 'Nama Role', 'trim|required');
		}
		$validation->setRule('judul_role', 'Judul Role', 'trim|required');
		$validation->setRule('keterangan', 'keterangan', 'trim|required');
		$validation->withRequest($this->request)->run();
		$form_errors = $validation->getErrors();
		
		if (!$this->auth->validateFormToken('form_role')) {
			$form_errors['token'] = 'Token tidak ditemukan, submit ulang form dengan mengklik tombol submit';
		}
		
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
		
		$listModules = $this->model->getListModules();
		$module = [];
		foreach ($listModules as $val) {
			$modules[$val['id_module']] = $val;
			$modulesRole[$val['id_role']][$val['id_module']] = $val;
		}
		
		foreach ($query['data'] as $key => &$val) 
		{
			$module = '';
			if (key_exists($val['id_module'], $modules)) {
				$module = $modules[$val['id_module']]['judul_module'];
			}
			$keteranganModule = '';
			
			if ($module) {
				if (key_exists($val['id_role'], $modulesRole)) {
					if (!key_exists($val['id_module'], $modulesRole[$val['id_role']])) {
						$keteranganModule = '<p class="text-danger text-wrap"><small class="text-wrap">Role <strong>' . $val['nama_role'] . '</strong> tidak memiliki permission pada module <strong>' . $module . '</strong>, silakan <a href="' . base_url() . '/builtin/role-permission/edit?id=' . $val['id_role'] . '">assign</a> permission terlebih dahulu</small></p>';
					}
				} else {
					$keteranganModule = '<p class="text-danger text-wrap"><small class="text-wrap">Role <strong>' . $val['nama_role'] . '</strong> tidak memiliki permission pada module apapun, silakan <a href="' . base_url() . '/builtin/role-permission/edit?id=' . $val['id_role'] . '">assign</a> permission terlebih dahulu</small></p>';
				}
			} else {
				$module = '-';
			}
						
			$val['id_module'] = $module . $keteranganModule;
			$val['ignore_no_urut'] = $no;
			$val['ignore_action'] = btn_dropdown_actions([
				['type' => 'link','href' => base_url() . '/builtin/role/edit?id=' . $val['id_role'], 'icon' => 'fas fa-edit text-success', 'label' => 'Edit', 'attrs' => ['class' => 'btn-edit', 'data-id' => $val['id_role']]],
				['type' => 'button','icon' => 'fas fa-times text-danger', 'label' => 'Delete', 'attrs' => ['class' => 'btn-delete', 'data-id' => $val['id_role'], 'data-delete-title' => 'Hapus data role: <strong>' . $val['judul_role'] . '</strong>']]
			]);
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
	
}
