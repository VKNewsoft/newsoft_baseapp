<?php
/**
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Controllers\Builtin;
use App\Models\Builtin\UserRoleModel;

class User_role extends \App\Controllers\BaseController
{
	protected $model;
	private $formValidation;
	
	public function __construct() {
		
		parent::__construct();
		$this->addJs ($this->config->baseURL . 'public/themes/modern/builtin/js/user-role.js');
		$this->addStyle ($this->config->baseURL . 'public/vendors/wdi/wdi-loader.css');
		
		$this->model = new UserRoleModel;	
		$this->data['site_title'] = 'User Role';
		
		$roles= $this->model->getAllRole();
		foreach($roles as $row) {
			$this->data['roles'][$row['id_role']] = $row;
		}
	}
	
	public function index()
	{
		$this->hasPermission('read_all');
		
		$data = $this->data;
		if ($this->request->getPost('delete')) {
			$result = $this->model->deleteData();
			
			if ($result) {
				$data['msg'] = ['status' => 'ok', 'message' => 'Data user-role berhasil dihapus'];
			} else {
				$data['msg'] = ['status' => 'error', 'message' => 'Data user-role gagal dihapus'];
			}
		}
		
		// Get user
		$data['users'] = $this->model->getAllUser();
		$this->view('builtin/user-role.php', $data);
	}
	
	public function checkbox() {
		
		$userRole = $this->model->getUserRoleByID($this->request->getGet('id'));
		$this->data['user_role'] = $userRole;
		
		echo view('themes/modern/builtin/user-role-form.php', $this->data);
	}
	
	public function delete() {
		if ($this->request->getPost('id_user')) 
		{
			$result = $this->model->deleteData();
			if ($result) {
				$message = ['status' => 'ok', 'message' => 'Data berhasil dihapus'];
			} else {
				$message = ['status' => 'error', 'message' => 'Data gagal dihapus'];
			}
			echo json_encode($message);
		}
	}
	
	public function edit() 
	{
		if ($this->request->getPost('id_user')) 
		{	
			$result = $this->model->saveData();
			
			if ($result) {
				$message = ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
			} else {
				$message = ['status' => 'error', 'message' => 'Data gagal disimpan'];
			}
		
			echo json_encode($message);
		}
	}
	
	public function getDataDT() {
		
		$this->hasPermission('read_all');
		
		$numData = $this->model->countAllData(' WHERE 1 = 1');
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $numData;
		
		$query = $this->model->getListData(' WHERE 1 = 1');
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
	
		$userRole = [];
		$userRoleAll = $this->model->getUserRole();
		foreach($userRoleAll as $row) {
			$userRole[$row['id_user']][] = $row;
		}
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			
			$listRole = '';
			if (key_exists($val['id_user'], $userRole)) {
				$roles = $userRole[$val['id_user']];
				foreach ($roles as $role) 
				{
					$listRole .= '<span class="badge badge-secondary badge-role px-3 py-2 me-1 mb-1 pe-4">' . $role['judul_role'] . '<a data-action="remove-role" data-id-user="'.$val['id_user'].'" data-role-id="'.$role['id_role'].'" href="javascript:void(0)" class="text-danger"><i class="fas fa-times"></i></a></span>';
				}
			}
			
			$val['ignore_role'] = $listRole;
			$val['ignore_no_urut'] = $no;
			$val['ignore_action'] = btn_dropdown_actions([
				['type' => 'button', 'icon' => 'fas fa-edit text-success', 'label' => 'Edit', 'attrs' => ['class' => 'btn-edit', 'data-id-user' => $val['id_user']]],
			]);
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
	
}