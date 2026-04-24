<?php
/**
 * Controller Project Category menangani CRUD kategori project yang dipakai oleh modul project dan task.
 */

namespace App\Modules\ProjectCategory\Controllers;

use App\Modules\ProjectCategory\Models\ProjectCategoryModel;

class ProjectCategory extends \App\Modules\Common\Controllers\BaseController
{
	protected $model;

	public function __construct()
	{
		parent::__construct();
		$this->model = new ProjectCategoryModel();
		$this->data['site_title'] = 'Project Category';
		helper(['html', 'form']);
	}

	public function index()
	{
		$this->hasPermission('read_all', true);
		$data = $this->data;
		$data['title'] = 'Category List';
		$data['categories'] = $this->model->getListData();
		$this->view('project_category/index.php', $data);
	}

	public function add()
	{
		$this->hasPermission('create', true);
		return $this->renderForm();
	}

	public function edit()
	{
		$this->hasPermissionPrefix('update');
		return $this->renderForm((int) $this->request->getGet('id'));
	}

	public function delete()
	{
		$this->hasPermissionPrefix('delete');
		$message = $this->model->deleteData((int) $this->request->getPost('id'));
		$this->session->setFlashdata('message', $message);
		return redirect()->to($this->moduleURL);
	}

	/**
	 * Form kategori tetap sengaja ringkas karena hanya mengelola master data dropdown.
	 */
	private function renderForm(int $id = 0)
	{
		$data = $this->data;
		$data['title'] = $id > 0 ? 'Edit Category' : 'Tambah Category';
		$data['category'] = $id > 0 ? $this->model->getCategoryById($id) : [];

		if ($id > 0 && !$data['category']) {
			$this->errorDataNotFound();
			return;
		}

		if ($this->request->getPost('submit')) {
			$message = $this->model->saveData($id);
			if ($message['status'] === 'ok') {
				$this->session->setFlashdata('message', $message);
				return redirect()->to($this->moduleURL);
			}

			$data['message'] = $message;
			$data['category'] = ['name' => $this->request->getPost('name')];
		}

		$this->view('project_category/form.php', $data);
	}
}
