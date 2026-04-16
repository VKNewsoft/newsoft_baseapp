<?php
/**
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Modules\Builtin\Controllers;
use App\Modules\Builtin\Models\RoleModel;

class Role extends \App\Modules\Common\Controllers\BaseController
{
	protected $model;
	private $formValidation;
	
	public function __construct() {
		
		parent::__construct();
		// $this->mustLoggedIn();
		
		$this->model = new RoleModel;	
		$this->data['site_title'] = 'Halaman Role';
		
		// HMVC asset load: form/list role pakai asset shared builtin agar class UI tetap sinkron.
		$this->addJs($this->commonAsset('builtin/js/role.js') . '?v=' . @filemtime(APPPATH . 'Modules/Common/Assets/builtin/js/role.js'));
		$this->addStyle($this->commonAsset('builtin/css/role.css') . '?v=' . @filemtime(APPPATH . 'Modules/Common/Assets/builtin/css/role.css'));
		
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
		// Halaman list cukup membawa data layout dasar karena isi tabel dimuat
		// bertahap lewat DataTable server-side untuk menjaga render awal tetap cepat.
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
		
		$this->view('builtin/role-result.php', $data);
	}
	
	public function add() 
	{
		$this->hasPermission('create');
		if ($this->request->isAJAX()) {
			return $this->ajaxForm();
		}
		
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
		if ($this->request->isAJAX()) {
			return $this->ajaxForm();
		}
		
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

	public function ajaxForm()
	{
		$this->setData();
		$data = $this->data;
		$data['msg'] = [];
		$data['role'] = [];
		$data['title'] = 'Tambah Role';

		$idRole = (int) ($this->request->getGet('id') ?? 0);
		if ($idRole > 0) {
			$data['role'] = $this->model->getRoleById($idRole);
			if (!$data['role']) {
				return $this->response->setStatusCode(404)->setBody('Data role tidak ditemukan');
			}
			$data['title'] = 'Edit Role';
		}

		return $this->response->setBody($this->fetchView('builtin/role-form-ajax.php', $data));
	}

	public function ajaxSave()
	{
		$result = [
			'status' => 'error',
			'message' => 'Data gagal disimpan'
		];

		if (!$this->request->isAJAX() || !$this->request->getPost('submit')) {
			return $this->response->setJSON([
				'status' => 'error',
				'message' => 'Invalid request'
			]);
		}

		$idRole = (int) ($this->request->getPost('id') ?? 0);
		if ($idRole > 0) {
			if (!$this->hasPermission('update_all')) {
				return $this->response->setJSON([
					'status' => 'error',
					'message' => 'Anda tidak memiliki permission update_all'
				]);
			}
		} else {
			if (!$this->hasPermission('create')) {
				return $this->response->setJSON([
					'status' => 'error',
					'message' => 'Anda tidak memiliki permission create'
				]);
			}
		}

		$save = $this->saveData();
		if (!empty($save['form_errors'])) {
			$result['message'] = $save['form_errors'];
		} elseif (($save['msg']['status'] ?? '') === 'ok') {
			$result['status'] = 'ok';
			$result['message'] = $save['msg']['message'];
		} else {
			$result['message'] = $save['msg']['message'] ?? $result['message'];
		}

		return $this->response->setJSON($result);
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
		
		// Optimasi query list: ambil module dan permission assignment hanya untuk data
		// pada halaman aktif agar render awal DataTable tetap ringan.
		$modules = [];
		$modulesRole = [];
		$moduleIds = [];
		$roleIds = [];
		foreach ($query['data'] as $val) {
			if (!empty($val['id_module'])) {
				$moduleIds[] = (int) $val['id_module'];
			}
			if (!empty($val['id_role'])) {
				$roleIds[] = (int) $val['id_role'];
			}
		}

		foreach ($this->model->getModulesByIds(array_unique($moduleIds)) as $val) {
			$modules[$val['id_module']] = $val;
		}

		foreach ($this->model->getRoleModulePermissionMap(array_unique($roleIds)) as $val) {
			$modulesRole[$val['id_role']][$val['id_module']] = true;
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
						$keteranganModule = '<div class="table-inline-note table-inline-note-warning" title="Role ' . esc($val['nama_role']) . ' tidak memiliki permission pada module ' . esc($module) . '"><span class="text-danger">Perlu assign permission</span> <a href="' . base_url() . '/builtin/role-permission/edit?id=' . $val['id_role'] . '">Assign</a></div>';
					}
				} else {
					$keteranganModule = '<div class="table-inline-note table-inline-note-warning" title="Role ' . esc($val['nama_role']) . ' tidak memiliki permission pada module apapun"><span class="text-danger">Belum ada permission</span> <a href="' . base_url() . '/builtin/role-permission/edit?id=' . $val['id_role'] . '">Assign</a></div>';
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
