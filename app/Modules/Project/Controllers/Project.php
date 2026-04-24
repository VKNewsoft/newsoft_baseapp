<?php
/**
 * Controller Project menangani CRUD master project serta navigasi ke modul anggota dan task.
 */

namespace App\Modules\Project\Controllers;

use App\Modules\Project\Models\ProjectModel;

class Project extends \App\Modules\Common\Controllers\BaseController
{
	protected $model;

	public function __construct()
	{
		parent::__construct();
		$this->model = new ProjectModel();
		$this->data['site_title'] = 'Project';
		helper(['html', 'form']);
	}

	public function index()
	{
		$this->hasPermission('read_all', true);
		$data = $this->data;
		$data['title'] = 'Project List';
		$data['projects'] = $this->model->getListData();
		$this->view('project/index.php', $data);
	}

	public function add()
	{
		$this->hasPermission('create', true);
		return $this->renderForm();
	}

	public function detail()
	{
		$this->hasPermission('read_all', true);
		$projectId = (int) $this->request->getGet('id');
		$project = $this->model->getProjectDetailById($projectId);
		if (!$project) {
			$this->errorDataNotFound();
			return;
		}

		$filters = [
			'user_id' => (int) $this->request->getGet('user_id'),
			'date_from' => $this->model->normalizeDate($this->request->getGet('date_from')),
			'date_to' => $this->model->normalizeDate($this->request->getGet('date_to')),
		];

		$data = $this->data;
		$data['title'] = 'Project Detail';
		$data['project'] = $project;
		$data['token_filters'] = $filters;
		$data['project_user_options'] = $this->model->getProjectUserOptions($projectId);
		$data['token_summary_members'] = $this->model->getProjectTokenSummaryByMember($projectId, $filters);
		$data['token_total'] = $this->model->getProjectTokenTotal($projectId, $filters);
		$data['token_logs'] = $this->model->getProjectTokenUsageLogs($projectId, $filters);
		$this->view('project/detail.php', $data);
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
	 * Form add/edit dipusatkan agar field project dan validasi tampil konsisten.
	 */
	private function renderForm(int $id = 0)
	{
		$data = $this->data;
		$data['title'] = $id > 0 ? 'Edit Project' : 'Tambah Project';
		$data['project'] = $id > 0 ? $this->model->getProjectById($id) : [];
		$data['category_options'] = $this->model->getCategoryOptions();

		if ($id > 0 && !$data['project']) {
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
			$data['project'] = array_merge($data['project'], [
				'name' => $this->request->getPost('name'),
				'description' => $this->request->getPost('description'),
				'category_id' => $this->request->getPost('category_id'),
				'start_date' => $this->request->getPost('start_date'),
				'end_date' => $this->request->getPost('end_date'),
			]);
		}

		$this->view('project/form.php', $data);
	}
}
