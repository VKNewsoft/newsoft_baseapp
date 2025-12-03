<?php
/**
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Controllers\Builtin;
use App\Models\Builtin\ModuleRoleModel;

class Module_role extends \App\Controllers\BaseController
{
	protected $model;
	private $formValidation;
	
	public function __construct() {
		
		parent::__construct();
		$this->addJs ($this->config->baseURL . 'public/themes/modern/builtin/js/module-role.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/wdi/wdi-loader.css');
		
		$this->model = new ModuleRoleModel;	
		$this->data['site_title'] = 'Module Role';
	}
	
	public function index()
	{
		$this->hasPermission('read_all');
		$this->view('builtin/module-role.php', $this->data);
	}
	
	public function delete() {
		if ($this->request->getPost('id_module')) 
		{
			$query = $this->model->deleteData();
			if ($query) {
				$message = ['status' => 'ok', 'message' => 'Data berhasil dihapus'];
			} else {
				$message = ['status' => 'error', 'message' => 'Data gagal dihapus'];
			}
			echo json_encode($message);
			exit;
		}
	}
	
	public function edit()
	{
		$this->hasPermission('update_all');
		$breadcrumb['Edit'] = '';
		
		// Submit data
		if ($this->request->getPost('submit')) 
		{
			
			$query = $this->model->saveData();
			
			if ($query) {
				$message = ['status' => 'ok', 'content' => 'Data berhasil disimpan'];
			} else {
				$message = ['status' => 'error', 'content' => 'Data gagal disimpan'];
			}
			$data['message'] = $message;
		}
		
		$data = $this->data;
		$data['title'] = 'Edit ' . $this->currentModule['judul_module'];
		$data['module'] = $this->model->getModule($this->request->getGet('id'));
		$data['role'] = $this->model->getAllRole();
		$data['role_detail'] = $this->model->getRoleDetail();
		$data['module_role'] = $this->model->getModuleRoleById($this->request->getGet('id'));
	
		$this->view('builtin/module-role-form.php', $data);
	}
	
	public function detail() {
		$breadcrumb['Detail'] = '';
		
		$data = $this->data;
		$data['title'] = 'Edit ' . $this->currentModule['judul_module'];

		$data['module'] = $this->model->getModule($_GET['id']);
		$data['role'] = $this->model->getAllRole();
		$data['role_detail'] = $this->model->getRoleDetail();
		$data['module_role'] = $this->model->getModuleRoleById($_GET['id']);
		
		$this->view('builtin/module-role-detail.php', $data);
	}
	
	public function getDataDT() {
		
		$this->hasPermission('read_all');
		
		$numData = $this->model->countAllData();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $numData;
		
		$query = $this->model->getListData();
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		
		$moduleRole = [];
		$moduleRoleAll = $this->model->getAllModuleRole();
		foreach($moduleRoleAll as $row) {
			$moduleRole[$row['id_module']][] = $row;
		}
	
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			
			$listRole = '';
			if (key_exists($val['id_module'], $moduleRole)) {
				$roles = $moduleRole[$val['id_module']];
				foreach ($roles as $role) 
				{
					$listRole .= '<span class="badge badge-secondary badge-role px-3 py-2 me-1 mb-1 pe-4">' . $role['judul_role'] . '<a data-action="remove-role" data-id-module="'.$val['id_module'].'" data-id-role="'.$role['id_role'].'" href="javascript:void(0)" class="text-danger"><i class="fas fa-times"></i></a></span>';
				}
			}
			
			$val['ignore_role'] = $listRole;
			$val['ignore_no_urut'] = $no;
			$val['ignore_action'] = '<div class="btn-action-group">'.
									btn_link(['url' => $this->config->baseURL . 'builtin/module-role/edit?id=' . $val['id_module'], 'label' => 'Edit', 'icon' => 'fas fa-edit', 
												'attr' => ['class' => 'btn btn-success btn-xs me-2', 'target' => '_blank']]
											). 
									btn_link(['url' => $this->config->baseURL . 'builtin/module-role/detail?id=' . $val['id_module'], 'label' => 'Detail', 'icon' => 'fas fa-eye', 
												'attr' => ['class' => 'btn btn-primary btn-xs', 'target' => '_blank']]
											).
									'</div>';
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result);
	}
}