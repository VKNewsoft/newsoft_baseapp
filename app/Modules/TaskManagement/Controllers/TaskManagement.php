<?php
/**
 * Controller Task Management menangani CRUD task, filter, dan validasi member berdasarkan project.
 */

namespace App\Modules\TaskManagement\Controllers;

use App\Modules\TaskManagement\Models\TaskManagementModel;

class TaskManagement extends \App\Modules\Common\Controllers\BaseController
{
	protected $model;

	public function __construct()
	{
		parent::__construct();
		$projectSuiteCssVersion = '?v=' . @filemtime(APPPATH . 'Modules/Common/Assets/css/project-suite.css');
		$projectSuiteJsVersion = '?v=' . @filemtime(APPPATH . 'Modules/Common/Assets/js/project-suite.js');
		$this->model = new TaskManagementModel();
		$this->data['site_title'] = 'Task Management';

		// Script form dipisahkan agar dropdown anggota project dapat berubah dinamis saat project diganti.
		$this->addStyle($this->commonAsset('css/project-suite.css') . $projectSuiteCssVersion);
		$this->addJs($this->commonAsset('js/project-suite.js') . $projectSuiteJsVersion);
		$this->addJs($this->moduleAsset('js/task-form.js') . '?v=' . @filemtime(APPPATH . 'Modules/TaskManagement/Assets/js/task-form.js'));
		helper(['html', 'form']);
	}

	public function index()
	{
		$this->hasPermission('read_all', true);

		$filters = [
			'project_id' => (int) $this->request->getGet('project_id'),
			'member_id' => (int) $this->request->getGet('member_id'),
			'status' => trim((string) $this->request->getGet('status')),
			/**
			 * Filter tanggal dipakai untuk mempersempit task berdasarkan rentang
			 * timeline tanpa mengubah struktur list dan grouping yang sudah ada.
			 */
			'date_from' => $this->model->normalizeDate($this->request->getGet('date_from')),
			'date_to' => $this->model->normalizeDate($this->request->getGet('date_to')),
		];

		$data = $this->data;
		$data['title'] = 'Task List';
		$data['filters'] = $filters;
		$data['project_options'] = $this->model->getProjectOptions();
		$data['member_options'] = $this->model->getTaskMemberFilterOptions($filters['project_id']);
		$data['status_options'] = $this->model->getTaskStatusOptions();
		$data['task_groups'] = $this->model->getGroupedList($filters);
		$this->view('task_management/index.php', $data);
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

		$query = http_build_query(array_filter([
			'project_id' => (int) $this->request->getPost('project_id_filter'),
			'member_id' => (int) $this->request->getPost('member_id_filter'),
			'status' => trim((string) $this->request->getPost('status_filter')),
			'date_from' => trim((string) $this->request->getPost('date_from_filter')),
			'date_to' => trim((string) $this->request->getPost('date_to_filter')),
		], static fn ($value) => $value !== '' && $value !== 0));

		return redirect()->to($this->moduleURL . ($query ? '?' . $query : ''));
	}

	public function memberOptions()
	{
		$this->hasPermissionPrefix('read');
		return $this->response->setJSON([
			'status' => 'ok',
			'data' => $this->model->getProjectMemberOptions((int) $this->request->getGet('project_id')),
		]);
	}

	/**
	 * Form task membangun dropdown member berdasarkan project terpilih agar validasi assignment lebih jelas di UI.
	 */
	private function renderForm(int $id = 0)
	{
		$data = $this->data;
		$data['title'] = $id > 0 ? 'Edit Task' : 'Tambah Task';
		$data['task'] = $id > 0 ? $this->model->getTaskById($id) : [];

		if ($id > 0 && !$data['task']) {
			$this->errorDataNotFound();
			return;
		}

		if ($id > 0 && $this->request->getPost('submit_token_usage')) {
			$usageId = (int) $this->request->getPost('usage_id');
			$message = $this->model->saveTaskTokenUsage($id, $usageId);
			$this->session->setFlashdata('message', $message);
			return redirect()->to($this->moduleURL . '/edit?id=' . $id);
		}

		if ($id > 0 && $this->request->getPost('delete_token_usage')) {
			$usageId = (int) $this->request->getPost('usage_id');
			$message = $this->model->deleteTaskTokenUsage($id, $usageId);
			$this->session->setFlashdata('message', $message);
			return redirect()->to($this->moduleURL . '/edit?id=' . $id);
		}

		$selectedProjectId = $id > 0 ? (int) ($data['task']['project_id'] ?? 0) : (int) $this->request->getGet('project_id');
		// Placeholder dijaga agar input baru tidak otomatis mengambil project pertama.
		$data['project_options'] = $this->model->getProjectOptions();
		$data['status_options'] = $this->model->getTaskStatusOptions(false);
		$data['priority_options'] = $this->model->getTaskPriorityOptions(false);
		$data['member_options'] = $this->model->getProjectMemberOptions($selectedProjectId);
		$data['token_usage_type_options'] = $this->model->getTokenUsageTypeOptions(false);

		if ($this->request->getPost('submit')) {
			$message = $this->model->saveData($id);
			$selectedProjectId = (int) $this->request->getPost('project_id');

			if ($message['status'] === 'ok') {
				$this->session->setFlashdata('message', $message);
				return redirect()->to($this->moduleURL . ($selectedProjectId > 0 ? '?project_id=' . $selectedProjectId : ''));
			}

			$data['message'] = $message;
			$data['task'] = [
				'project_id' => $selectedProjectId,
				'title' => $this->request->getPost('title'),
				'description' => $this->request->getPost('description'),
				'assigned_to' => $this->request->getPost('assigned_to'),
				'status' => $this->request->getPost('status'),
				'priority' => $this->request->getPost('priority'),
				'start_date' => $this->request->getPost('start_date'),
				'end_date' => $this->request->getPost('end_date'),
			];
			$data['member_options'] = $this->model->getProjectMemberOptions($selectedProjectId);
		}

		$data['selected_project_id'] = $selectedProjectId;
		$data['token_usage_history'] = $id > 0 ? $this->model->getTaskTokenUsageHistory($id) : [];
		$data['token_usage_edit'] = [];
		if ($id > 0 && $this->request->getGet('token_usage_id')) {
			$tokenUsageEdit = $this->model->getTaskTokenUsageById((int) $this->request->getGet('token_usage_id'));
			if ($tokenUsageEdit && (int) $tokenUsageEdit['task_id'] === $id) {
				$data['token_usage_edit'] = $tokenUsageEdit;
			}
		}
		$this->view('task_management/form.php', $data);
	}
}
