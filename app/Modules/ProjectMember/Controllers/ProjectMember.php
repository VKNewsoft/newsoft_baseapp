<?php
/**
 * Controller Project Member mengelola anggota project dan menjadi sumber validasi assignment task.
 */

namespace App\Modules\ProjectMember\Controllers;

use App\Modules\ProjectMember\Models\ProjectMemberModel;

class ProjectMember extends \App\Modules\Common\Controllers\BaseController
{
	protected $model;

	public function __construct()
	{
		parent::__construct();
		$this->model = new ProjectMemberModel();
		$this->data['site_title'] = 'Project Member';
		helper(['html', 'form']);
	}

	public function index()
	{
		$this->hasPermission('read_all', true);
		$projectId = (int) $this->request->getGet('project_id');

		$data = $this->data;
		$data['title'] = 'Project Member';
		$data['project_id'] = $projectId;
		$data['project_options'] = $this->model->getProjectOptions();
		$data['members'] = $this->model->getListData($projectId);
		$this->view('project_member/index.php', $data);
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

		$projectId = (int) $this->request->getPost('project_id');
		$redirectUrl = $this->moduleURL . ($projectId > 0 ? '?project_id=' . $projectId : '');
		return redirect()->to($redirectUrl);
	}

	/**
	 * Form anggota menerima project dari query string agar alur dari daftar project tetap singkat.
	 */
	private function renderForm(int $id = 0)
	{
		$data = $this->data;
		$data['title'] = $id > 0 ? 'Edit Project Member' : 'Tambah Project Member';
		$data['member'] = $id > 0 ? $this->model->getMemberById($id) : [];

		if ($id > 0 && !$data['member']) {
			$this->errorDataNotFound();
			return;
		}

		$projectId = $id > 0 ? (int) ($data['member']['project_id'] ?? 0) : (int) $this->request->getGet('project_id');
		// Placeholder tetap dipakai supaya form add tidak otomatis memilih project atau user pertama.
		$data['project_options'] = $this->model->getProjectOptions();
		$data['user_options'] = $this->model->getUserOptions();

		if ($this->request->getPost('submit')) {
			$message = $this->model->saveData($id);
			$projectId = (int) $this->request->getPost('project_id');

			if ($message['status'] === 'ok') {
				$this->session->setFlashdata('message', $message);
				return redirect()->to($this->moduleURL . ($projectId > 0 ? '?project_id=' . $projectId : ''));
			}

			$data['message'] = $message;
			$data['member'] = [
				'project_id' => $projectId,
				'user_id' => $this->request->getPost('user_id'),
			];
		}

		$data['project_id'] = $projectId;
		$this->view('project_member/form.php', $data);
	}
}
