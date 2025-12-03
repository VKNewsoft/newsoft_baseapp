<?php
/**
 * Menu Role Controller
 * Mengelola assignment menu ke role (role mana yang bisa akses menu apa)
 * 
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Controllers\Builtin;
use App\Models\Builtin\MenuRoleModel;

class Menu_role extends \App\Controllers\BaseController
{
	protected $model;
	private $formValidation;
	
	public function __construct() 
	{
		parent::__construct();
		$this->addJs($this->config->baseURL . 'public/themes/modern/builtin/js/menu-role.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/wdi/wdi-loader.css');

		$this->model = new MenuRoleModel;	
		
		$roles = $this->model->getAllRole();
		foreach($roles as $row) {
			$this->data['roles'][$row['id_role']] = $row;
		}
	}
	
	/**
	 * Halaman utama menu role
	 */
	public function index()
	{
		$this->hasPermission('read_all');
		$this->view('builtin/menu-role.php', $this->data);
	}
	
	/**
	 * Hapus menu role assignment
	 */
	public function delete() 
	{
		if ($this->request->getPost('id_menu')) 
		{
			$query = $this->model->deleteData();
			if ($query) {
				$message = ['status' => 'ok', 'message' => 'Data berhasil dihapus'];
			} else {
				$message = ['status' => 'error', 'message' => 'Data gagal dihapus'];
			}
			echo json_encode($message);
		}
	}
	
	/**
	 * Tampilkan checkbox role untuk menu tertentu
	 */
	public function checkbox()
	{
		$idMenu = $this->request->getGet('id');
		$menuRole = $this->model->getMenuRoleById($idMenu);
		
		$checked = [];
		foreach ($menuRole as $row) {
			$checked[] = $row['id_role'];
		}
	
		$this->data['checked'] = $checked;
		echo view('themes/modern/builtin/menu-role-form.php', $this->data);
	}
	
	/**
	 * Edit/simpan menu role assignment
	 */
	public function edit()
	{
		$this->hasPermission('update_all');
		
		// Submit data
		if ($this->request->getPost('id_menu')) 
		{
			$result = $this->model->saveData();
			
			if ($result['status'] == 'ok') {
				$message = [
					'status' => 'ok', 
					'message' => 'Data berhasil disimpan', 
					'data_parent' => json_encode($result['insert_parent'])
				];
			} else {
				$message = ['status' => 'error', 'message' => 'Data gagal disimpan'];
			}

			echo json_encode($message);
		}
	}
	
	/**
	 * Get data untuk DataTables dengan AJAX
	 */
	public function getDataDT() {
		
		$this->hasPermission('read_all');
		
		$numData = $this->model->countAllData();
		$result['draw'] = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $numData;
		
		$query = $this->model->getListData();
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		
		$menuRole = [];
		$menuRoleAll = $this->model->getAllMenuRole();
		foreach($menuRoleAll as $row) {
			$menuRole[$row['id_menu']][] = $row;
		}
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			$listRole = '';
			if (key_exists($val['id_menu'], $menuRole)) {
				$roles = $menuRole[$val['id_menu']];
				foreach ($roles as $role) 
				{
					$listRole .= '<span class="badge badge-secondary badge-role px-3 py-2 me-1 mb-1 pe-4">' . $role['judul_role'] . '<a data-action="remove-role" data-id-menu="'.$val['id_menu'].'" data-role-id="'.$role['id_role'].'" href="javascript:void(0)" class="text-danger"><i class="fas fa-times"></i></a></span>';
				}
			}
			
			$val['ignore_role'] = $listRole;
			$val['ignore_no_urut'] = $no;
			$val['ignore_action'] = btn_dropdown_actions([
				['type' => 'button', 'icon' => 'fas fa-edit text-success', 'label' => 'Edit', 'attrs' => ['class' => 'btn-edit', 'data-id-menu' => $val['id_menu']]],
			]);
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
}