<?php
/**
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Controllers\Builtin;
use App\Models\Builtin\PermissionModel;

class Permission extends \App\Controllers\BaseController
{
	protected $model;
	private $formValidation;
	
	public function __construct() {
		
		parent::__construct();
		// $this->mustLoggedIn();
		
		$this->model = new PermissionModel;	
		$this->data['site_title'] = 'Halaman Permission';		
		$this->addStyle( base_url() . '/public/vendors/wdi/wdi-loader.css' );
		
		$this->addStyle( base_url() . '/public/vendors/jquery.select2/css/select2.min.css' );
		$this->addJs( base_url() . '/public/vendors/jquery.select2/js/select2.full.min.js' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css' );
		
		$this->addJs( base_url() . '/public/themes/modern/builtin/js/permission.js' );
		
		helper(['cookie', 'form']);
	}
	
	public function index()
	{
		$this->hasPermissionPrefix('read');
	
		$data = $this->data;
		if ($this->request->getPost('delete')) 
		{
			$this->hasPermissionPrefix('delete');
			$result = $this->model->deleteData();
			if ($result) {
				$data['msg'] = ['status' => 'ok', 'message' => 'Data berhasil dihapus'];
			} else {
				$data['msg'] = ['status' => 'warning', 'message' => 'Tidak ada data yang dihapus'];
			}
		}
		
		$id = $this->request->getGet('id_module') ?: null;
		$data['permission'] = $this->model->getPermission($id);
		$data['module'] = ['' => 'All Modules'] + $this->model->getAllModules();
		
		$data['title'] = 'Edit Permission';
		
		$this->view('builtin/permission-form.php', $data);
	}
	
	public function ajaxFormEdit() 
	{
		$data['message'] = [];
		if (!$this->request->getGet('id')) {
			$data['message'] = ['status' => 'error', 'message' => 'Invalid input'];
		} else {
		
			$id = (int) $this->request->getGet('id');
			$query = $this->model->getPermissionById($id);
			$data['result'] = $query;
			$data['modules'] = $this->model->getAllMOdules();
		}
		
		echo view('themes/modern/builtin/permission-form-edit-ajax.php', $data);
		exit;
	}
	
	// Form other controllers e.q module controller
	public function ajaxAdd() 
	{
		$result['status'] ='error';
		$result['message'] ='Invalid Input';
		if ($this->request->getPost('submit')) {
			$formErrors = $this->validateForm();
			if ($formErrors) {
				$result['status'] = 'error';
				$result['message'] = $formErrors;
			} else {
				$result = $this->model->saveData();
				if ($result['status'] == 'ok') {
					$result['data'] = $this->model->getPermission($this->request->getPost('id_module'));
				}
			}
		}
		echo json_encode($result);
	}
	
	public function ajaxGetModulePermissionCheckbox() 
	{
		$result['message'] = ['status' => 'error', 'message' => 'Invalid Input'];

		if ($this->request->getGet('id')) 
		{
			$result['message']['status'] ='ok';
			$result['module_permission'] = $this->model->getPermission($this->request->getGet('id'));
			$query = $this->model->getRolePermission($this->request->getGet('id_role'));
			$rolePermission = [];
			foreach ($query as $val) {
				$rolePermission[$val['id_module_permission']] = $val['id_module_permission'];
			}
			$result['role_permission'] = $rolePermission;
			
			if (!$result['module_permission']) {
				$module = $this->model->getModuleById($this->request->getGet('id'));
				$result['message'] = ['status' => 'error', 'message' => 'Module ' . $module['nama_module'] . ' belum memiliki permission'];
			}
		}
		echo view('themes/modern/builtin/permission-form-checkbox-ajax.php', $result);
	}
	
	public function ajaxDeletePermissionByModule() 
	{
		$result['status'] ='error';
		$result['message'] ='Invalid Input';
		if ($this->request->getPost('submit') && $this->request->getPost('id')) {
			$delete = $this->model->deletePermissionByModule($this->request->getPost('id'));
			if ($delete) {
				$result['status'] = 'ok';
				$result['message'] ='Data berhasil dihapus';
			} else {
				$result['status'] = 'ok';
				$result['message'] ='Tidak ada data yang dihapus';
			}
		}
		echo json_encode($result);
	}
	// --
	
	public function add() 
	{
		$data = $this->data;
		$data['message'] = [];
		$data['title'] = 'Tambah Permission';
		
		$message = [];
		if ($this->request->getPost('submit')) {
			$formErrors = $this->validateForm();

			if ($formErrors) {
				$message['status'] = 'error';
				$data['form_errors'] = $formErrors;
				$message['message'] = $formErrors;
			} else {
				$query = $this->model->saveData();
				if ($query['status'] == 'ok') {
					$message['status'] = 'ok';
					$message['message'] = 'Data berhasil disimpan';
					$data['id'] = $query['id'];
					$data['title'] = 'Edit Permission';
				} else {
					$message['status'] = 'error';
					$message['message'] = 'Data gagal disimpan';
				}
			}
		}
		
		$data['message'] = $message;
		$data['modules'] = $this->model->getAllModules();
		
		$this->view('builtin/permission-form-add.php', $data);
	}
	
	public function ajaxEdit() {
		
		if (!$this->request->getPost('nama_permission') || !$this->request->getPost('judul_permission') || !$this->request->getPost('keterangan') ) {
			$result['status'] = 'error';
			$result['message'] = 'Semua data harus diisi';
			
		} else {
			$formErrors = $this->validateForm();

			if ($formErrors) {
				$result['status'] = 'error';
				$result['message'] = $formErrors;
			} else {
				$query = $this->model->saveData();
				if ($query) {
					$result['status'] = 'ok';
					$result['message'] = 'Data berhasil disimpan';
				} else {
					$result['status'] = 'error';
					$result['message'] = 'Data gagal dihapus';
				}
			}
			
		}
		echo json_encode($result);
			exit;
	}
	
	public function ajaxDelete() {
		
		if (!trim($this->request->getPost('id'))) {
			
			$result['status'] = 'error';
			$result['message'] = 'Semua data harus diisi';
			
		} else {
			$id = (int) $this->request->getPost('id');
			$query = $this->model->deleteData($id);
			if ($query) {
				$result['status'] = 'ok';
				$result['message'] = 'Data berhasil dihapus';
			} else {
				$result['status'] = 'error';
				$result['message'] = 'Data gagal dihapus';
			}
		}
		echo json_encode($result);
		exit;
	}
	
	private function validateForm() {

		$validation =  \Config\Services::validation();
		
		$validation->setRule('id_module', 'ID Module', 'trim|required');
		
		$namaPermissionError = [];
		if ($this->request->getPost('generate_permission') == 'manual') {
			
			$validation->setRule('nama_permission', 'Nama Permission', 'trim|required');
			$validation->setRule('judul_permission', 'Judul Permission', 'trim|required');
			$validation->setRule('keterangan', 'Keterangan', 'trim|required');
			
			/* $exp = explode('_', $_POST['nama_permission']);
			array_map('trim', $exp);
			$list_group = ['create', 'read', 'update', 'delete'];
			if (count($exp) > 1) {
				if (!in_array($exp[0], $list_group)) {
					$nama_permission_error['nama_permission'] = 'Nama permission harus diawali kata read, update, delete dan diikuti underscore (_), kecuali create, misal create, update_own, read_all';
				} else if (strlen($exp[1]) < 3) {
					$nama_permission_error['nama_permission'] = 'Nama permission setelah underscore (_) minimal 3 karakter, misal read_all';
				}
				
			} else {
				if ($exp[0] != 'create') {
					$nama_permission_error['nama_permission'] = 'Nama permission harus diawali kata read, update, delete dan diikuti underscore (_), kecuali create, misal create, update_own, read_all';
				}
			} */
		}
		
		$validation->withRequest($this->request)->run();
		$formErrors = $validation->getErrors();
		$formErrors = array_merge($formErrors, $namaPermissionError);
		
		if (!$formErrors) {
			$duplicate = $this->model->checkDuplicate();
			if ($duplicate) {
				$module = $this->model->getModuleById($this->request->getPost('id_module'));
				$formErrors = $message['message'] = 'Nama permission ' . $this->request->getPost('nama_permission') . ' sudah ada di module ' . $module['judul_module'];
			}
		}
		
		return $formErrors;
	}
	
	public function getDataDT() {
		
		$this->hasPermission('read_all');
		
		$numData = $this->model->countAllData(' WHERE 1 = 1');
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $numData;
		
		$query = $this->model->getListData(' WHERE 1 = 1');
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			$val['ignore_search_urut'] = $no;
			$val['keterangan'] = '<span style="white-space:nowrap">' . $val['keterangan'] . '</span>';
			$val['ignore_search_action'] = btn_dropdown_actions([
				['type' => 'button', 'icon' => 'fas fa-edit text-success', 'label' => 'Edit', 'attrs' => ['class' => 'edit', 'data-id-permission' => $val['id_module_permission']]],
				['type' => 'button','icon' => 'fas fa-times text-danger', 'label' => 'Delete', 'attrs' => ['class' => 'delete', 'data-id-permission' => $val['id_module_permission'], 'data-delete-title' => 'Hapus data module: <strong>' . $val['judul_module'] . '</strong>']]
			]);
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
}