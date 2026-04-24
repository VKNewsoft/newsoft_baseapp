<?php

namespace App\Modules\TaskManagement\Models;

use App\Modules\Project\Models\ProjectReferenceModel;

class TaskManagementModel extends ProjectReferenceModel
{
	protected $tokenUsageModel;

	public function __construct()
	{
		parent::__construct();
		// Model token usage dipisah agar tanggung jawab CRUD task dan audit token tetap jelas.
		$this->tokenUsageModel = new TaskTokenUsageModel();
	}

	/**
	 * Daftar task digrupkan per project agar halaman list sesuai kebutuhan user dan mudah dipindai.
	 */
	public function getGroupedList(array $filters = []): array
	{
		$builder = $this->db->table('project_task pt')
			->select('pt.*, p.name AS project_name, p.start_date AS project_start_date, p.end_date AS project_end_date, pc.name AS category_name, cu.nama AS assigned_name, cu.username AS assigned_username')
			->select('COALESCE(SUM(pttu.token_used), 0) AS total_token_used', false)
			->join('project p', 'p.id_project = pt.project_id')
			->join('project_category pc', 'pc.id_project_category = p.category_id', 'left')
			->join('project_member pm', 'pm.id_project_member = pt.assigned_to')
			->join('core_user cu', 'cu.id_user = pm.user_id')
			->join('project_task_token_usage pttu', 'pttu.task_id = pt.id_project_task', 'left')
			->where('p.is_deleted', 0)
			->where('cu.isDeleted', 0)
			->groupBy('pt.id_project_task')
			->orderBy('p.name', 'ASC')
			->orderBy('pt.start_date', 'ASC')
			->orderBy('pt.id_project_task', 'DESC');

		if (!empty($filters['project_id'])) {
			$builder->where('pt.project_id', (int) $filters['project_id']);
		}

		if (!empty($filters['member_id'])) {
			$builder->where('pt.assigned_to', (int) $filters['member_id']);
		}

		if (!empty($filters['status'])) {
			$builder->where('pt.status', $filters['status']);
		}

		$rows = $builder->get()->getResultArray();
		$grouped = [];
		foreach ($rows as $row) {
			$projectId = (int) $row['project_id'];
			if (!isset($grouped[$projectId])) {
				$grouped[$projectId] = [
					'project' => [
						'id_project' => $projectId,
						'name' => $row['project_name'],
						'category_name' => $row['category_name'],
						'start_date' => $row['project_start_date'],
						'end_date' => $row['project_end_date'],
					],
					'tasks' => [],
				];
			}

			$grouped[$projectId]['tasks'][] = $row;
		}

		return array_values($grouped);
	}

	/**
	 * Detail task dibaca ulang pada form edit agar pilihan member tetap sesuai project asalnya.
	 */
	public function getTaskById(int $id): array
	{
		return (array) $this->db->table('project_task pt')
			->select('pt.*, p.name AS project_name, pm.user_id, cu.nama AS assigned_name, cu.username AS assigned_username')
			->join('project p', 'p.id_project = pt.project_id')
			->join('project_member pm', 'pm.id_project_member = pt.assigned_to')
			->join('core_user cu', 'cu.id_user = pm.user_id')
			->where('pt.id_project_task', $id)
			->where('p.is_deleted', 0)
			->get()
			->getRowArray();
	}

	/**
	 * Simpan task menolak assignment yang bukan anggota project untuk menjaga integritas relasi.
	 */
	public function saveData(int $id = 0): array
	{
		$projectId = (int) $this->request->getPost('project_id');
		$assignedTo = (int) $this->request->getPost('assigned_to');
		$title = trim((string) $this->request->getPost('title'));
		$description = trim((string) $this->request->getPost('description'));
		$status = trim((string) $this->request->getPost('status'));
		$priority = trim((string) $this->request->getPost('priority'));
		$startDate = $this->normalizeDate($this->request->getPost('start_date'));
		$endDate = $this->normalizeDate($this->request->getPost('end_date'));

		$errors = [];
		if ($projectId <= 0 || !$this->projectExists($projectId)) {
			$errors[] = 'Project task tidak valid';
		}

		if ($title === '') {
			$errors[] = 'Judul task wajib diisi';
		}

		$statusOptions = $this->getTaskStatusOptions(false);
		if ($status === '' || !array_key_exists($status, $statusOptions)) {
			$errors[] = 'Status task tidak valid';
		}

		$priorityOptions = $this->getTaskPriorityOptions(false);
		if ($priority === '' || !array_key_exists($priority, $priorityOptions)) {
			$errors[] = 'Prioritas task tidak valid';
		}

		if ($assignedTo <= 0 || !$this->projectMemberExists($projectId, $assignedTo)) {
			$errors[] = 'Assignment task harus memakai anggota dari project yang dipilih';
		}

		if ($this->request->getPost('start_date') !== '' && $startDate === null) {
			$errors[] = 'Tanggal mulai task tidak valid';
		}

		if ($this->request->getPost('end_date') !== '' && $endDate === null) {
			$errors[] = 'Tanggal selesai task tidak valid';
		}

		if ($startDate && $endDate && $endDate < $startDate) {
			$errors[] = 'Tanggal selesai task tidak boleh lebih kecil dari tanggal mulai';
		}

		if ($errors) {
			return ['status' => 'error', 'message' => $errors, 'form_errors' => $errors];
		}

		$data = [
			'project_id' => $projectId,
			'title' => $title,
			'description' => $description !== '' ? $description : null,
			'assigned_to' => $assignedTo,
			'status' => $status,
			'priority' => $priority,
			'start_date' => $startDate,
			'end_date' => $endDate,
			'updated_at' => date('Y-m-d H:i:s'),
		];

		if ($id > 0) {
			$this->db->table('project_task')->where('id_project_task', $id)->update($data);
			// Saat task diubah, seluruh log token ikut diselaraskan dengan project dan assignment terbaru.
			$this->tokenUsageModel->syncUsageContextByTask($id);
		} else {
			$data['created_at'] = date('Y-m-d H:i:s');
			$this->db->table('project_task')->insert($data);
		}

		return ['status' => 'ok', 'message' => 'Data task berhasil disimpan'];
	}

	/**
	 * Penghapusan task sederhana karena relasinya hanya bergantung pada project dan anggota yang sudah tervalidasi.
	 */
	public function deleteData(int $id): array
	{
		$task = $this->getTaskById($id);
		if (!$task) {
			return ['status' => 'warning', 'message' => 'Data task tidak ditemukan'];
		}

		// Log token dihapus lebih dulu agar riwayat task tidak menyisakan orphan record pada database lama.
		$this->db->table('project_task_token_usage')->where('task_id', $id)->delete();
		$this->db->table('project_task')->where('id_project_task', $id)->delete();
		return ['status' => 'ok', 'message' => 'Data task berhasil dihapus'];
	}

	/**
	 * Riwayat token usage per task dipakai pada halaman detail/edit task.
	 */
	public function getTaskTokenUsageHistory(int $taskId): array
	{
		return $this->tokenUsageModel->getTaskUsageHistory($taskId);
	}

	/**
	 * Detail log dipakai untuk mode edit inline pada section token usage.
	 */
	public function getTaskTokenUsageById(int $usageId): array
	{
		return $this->tokenUsageModel->getUsageById($usageId);
	}

	/**
	 * Simpan log token diproxy melalui model khusus agar validasi relasi tetap terpusat.
	 */
	public function saveTaskTokenUsage(int $taskId, int $usageId = 0): array
	{
		return $this->tokenUsageModel->saveUsage($taskId, $usageId);
	}

	/**
	 * Hapus log token dipisahkan dari hapus task supaya audit log bisa dikelola dari detail task.
	 */
	public function deleteTaskTokenUsage(int $taskId, int $usageId): array
	{
		return $this->tokenUsageModel->deleteUsage($taskId, $usageId);
	}
}
