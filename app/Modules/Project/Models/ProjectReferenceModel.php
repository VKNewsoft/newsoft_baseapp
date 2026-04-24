<?php

namespace App\Modules\Project\Models;

class ProjectReferenceModel extends \App\Modules\Common\Models\BaseModel
{
	/**
	 * Menyediakan opsi kategori aktif untuk form project dan filter lain yang membutuhkan referensi kategori.
	 */
	public function getCategoryOptions(bool $withPlaceholder = true): array
	{
		$result = $withPlaceholder ? ['' => 'Pilih kategori project'] : [];
		$rows = $this->db->table('project_category')
			->select('id_project_category, name')
			->where('is_deleted', 0)
			->orderBy('name', 'ASC')
			->get()
			->getResultArray();

		foreach ($rows as $row) {
			$result[$row['id_project_category']] = $row['name'];
		}

		return $result;
	}

	/**
	 * Opsi project aktif dipakai lintas modul untuk filter task dan pengelolaan anggota.
	 */
	public function getProjectOptions(bool $withPlaceholder = true): array
	{
		$result = $withPlaceholder ? ['' => 'Semua project'] : [];
		$rows = $this->db->table('project')
			->select('id_project, name')
			->where('is_deleted', 0)
			->orderBy('name', 'ASC')
			->get()
			->getResultArray();

		foreach ($rows as $row) {
			$result[$row['id_project']] = $row['name'];
		}

		return $result;
	}

	/**
	 * Dropdown user hanya memuat user aktif agar anggota project tidak diambil dari akun terhapus.
	 */
	public function getUserOptions(bool $withPlaceholder = true): array
	{
		$result = $withPlaceholder ? ['' => 'Pilih user'] : [];
		$rows = $this->db->table('core_user')
			->select('id_user, nama, username')
			->where('isDeleted', 0)
			->orderBy('nama', 'ASC')
			->get()
			->getResultArray();

		foreach ($rows as $row) {
			$result[$row['id_user']] = $row['nama'] . ' (' . $row['username'] . ')';
		}

		return $result;
	}

	/**
	 * Dropdown anggota project dibentuk dari relasi project_member agar assignment task selalu valid.
	 */
	public function getProjectMemberOptions(int $projectId = 0, bool $withPlaceholder = true): array
	{
		$result = $withPlaceholder ? ['' => 'Pilih anggota project'] : [];
		if ($projectId <= 0) {
			return $result;
		}

		$rows = $this->db->table('project_member pm')
			->select('pm.id_project_member, cu.nama, cu.username')
			->join('core_user cu', 'cu.id_user = pm.user_id')
			->where('pm.project_id', $projectId)
			->where('cu.isDeleted', 0)
			->orderBy('cu.nama', 'ASC')
			->get()
			->getResultArray();

		foreach ($rows as $row) {
			$result[$row['id_project_member']] = $row['nama'] . ' (' . $row['username'] . ')';
		}

		return $result;
	}

	/**
	 * Filter member untuk daftar task tetap menampilkan konteks project supaya pilihan lebih jelas saat lintas project.
	 */
	public function getTaskMemberFilterOptions(int $projectId = 0, bool $withPlaceholder = true): array
	{
		$result = $withPlaceholder ? ['' => 'Semua anggota'] : [];
		$builder = $this->db->table('project_member pm')
			->select('pm.id_project_member, p.name AS project_name, cu.nama, cu.username')
			->join('project p', 'p.id_project = pm.project_id')
			->join('core_user cu', 'cu.id_user = pm.user_id')
			->where('p.is_deleted', 0)
			->where('cu.isDeleted', 0)
			->orderBy('p.name', 'ASC')
			->orderBy('cu.nama', 'ASC');

		if ($projectId > 0) {
			$builder->where('pm.project_id', $projectId);
		}

		foreach ($builder->get()->getResultArray() as $row) {
			$result[$row['id_project_member']] = $row['project_name'] . ' - ' . $row['nama'] . ' (' . $row['username'] . ')';
		}

		return $result;
	}

	/**
	 * Status task disediakan terpusat agar tampilan form dan list selalu sinkron.
	 */
	public function getTaskStatusOptions(bool $withPlaceholder = true): array
	{
		$options = [
			'todo' => 'To Do',
			'in_progress' => 'In Progress',
			'done' => 'Done',
			'blocked' => 'Blocked',
		];

		return $withPlaceholder ? ['' => 'Semua status'] + $options : $options;
	}

	/**
	 * Prioritas task dibuat eksplisit agar sorting dan label tetap konsisten.
	 */
	public function getTaskPriorityOptions(bool $withPlaceholder = true): array
	{
		$options = [
			'low' => 'Low',
			'medium' => 'Medium',
			'high' => 'High',
			'urgent' => 'Urgent',
		];

		return $withPlaceholder ? ['' => 'Pilih prioritas'] + $options : $options;
	}

	/**
	 * Jenis penggunaan token dipusatkan agar form log token dan laporan memakai label yang sama.
	 */
	public function getTokenUsageTypeOptions(bool $withPlaceholder = true): array
	{
		$options = [
			'prompt' => 'Prompt',
			'completion' => 'Completion',
			'embedding' => 'Embedding',
			'adjustment' => 'Adjustment',
			'other' => 'Other',
		];

		return $withPlaceholder ? ['' => 'Pilih jenis penggunaan'] + $options : $options;
	}

	/**
	 * Validasi format tanggal dipusatkan agar seluruh form project/task memakai aturan yang sama.
	 */
	public function normalizeDate(?string $date): ?string
	{
		$date = trim((string) $date);
		if ($date === '') {
			return null;
		}

		$parsed = \DateTime::createFromFormat('Y-m-d', $date);
		if (!$parsed || $parsed->format('Y-m-d') !== $date) {
			return null;
		}

		return $date;
	}

	/**
	 * Helper pengecekan kategori aktif dipakai pada validasi project.
	 */
	public function categoryExists(int $categoryId): bool
	{
		return (bool) $this->db->table('project_category')
			->select('id_project_category')
			->where('id_project_category', $categoryId)
			->where('is_deleted', 0)
			->get()
			->getRowArray();
	}

	/**
	 * Helper ini memastikan project yang dipakai di modul lain masih aktif dan tidak soft delete.
	 */
	public function projectExists(int $projectId): bool
	{
		return (bool) $this->db->table('project')
			->select('id_project')
			->where('id_project', $projectId)
			->where('is_deleted', 0)
			->get()
			->getRowArray();
	}

	/**
	 * Validasi user aktif dipakai saat menyimpan anggota project.
	 */
	public function userExists(int $userId): bool
	{
		return (bool) $this->db->table('core_user')
			->select('id_user')
			->where('id_user', $userId)
			->where('isDeleted', 0)
			->get()
			->getRowArray();
	}

	/**
	 * Validasi assignment task memastikan member memang tercatat pada project yang sama.
	 */
	public function projectMemberExists(int $projectId, int $memberId): bool
	{
		return (bool) $this->db->table('project_member')
			->select('id_project_member')
			->where('id_project_member', $memberId)
			->where('project_id', $projectId)
			->get()
			->getRowArray();
	}

	/**
	 * Detail relasi task-member-user dipakai untuk validasi token usage dan sinkronisasi perubahan task.
	 */
	public function getTaskAssignmentContext(int $taskId): array
	{
		return (array) $this->db->table('project_task pt')
			->select('pt.id_project_task, pt.project_id, pt.assigned_to, pm.user_id, cu.nama AS assigned_name, cu.username AS assigned_username')
			->join('project p', 'p.id_project = pt.project_id')
			->join('project_member pm', 'pm.id_project_member = pt.assigned_to')
			->join('core_user cu', 'cu.id_user = pm.user_id')
			->where('pt.id_project_task', $taskId)
			->where('p.is_deleted', 0)
			->where('cu.isDeleted', 0)
			->get()
			->getRowArray();
	}

	/**
	 * Opsi member per project dipakai pada filter laporan token agar pilihan tetap relevan dengan project aktif.
	 */
	public function getProjectUserOptions(int $projectId = 0, bool $withPlaceholder = true): array
	{
		$result = $withPlaceholder ? ['' => 'Semua member'] : [];
		if ($projectId <= 0) {
			return $result;
		}

		$rows = $this->db->table('project_member pm')
			->select('cu.id_user, cu.nama, cu.username')
			->join('core_user cu', 'cu.id_user = pm.user_id')
			->where('pm.project_id', $projectId)
			->where('cu.isDeleted', 0)
			->orderBy('cu.nama', 'ASC')
			->get()
			->getResultArray();

		foreach ($rows as $row) {
			$result[$row['id_user']] = $row['nama'] . ' (' . $row['username'] . ')';
		}

		return $result;
	}
}
