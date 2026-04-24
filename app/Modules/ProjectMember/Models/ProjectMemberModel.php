<?php

namespace App\Modules\ProjectMember\Models;

use App\Modules\Project\Models\ProjectReferenceModel;

class ProjectMemberModel extends ProjectReferenceModel
{
	/**
	 * List anggota mendukung filter project dan menghitung jumlah task agar pembagian kerja mudah dipantau.
	 */
	public function getListData(int $projectId = 0): array
	{
		$builder = $this->db->table('project_member pm')
			->select('pm.*, p.name AS project_name, cu.nama AS user_name, cu.username')
			->select('(SELECT COUNT(*) FROM project_task pt WHERE pt.assigned_to = pm.id_project_member) AS total_task', false)
			->join('project p', 'p.id_project = pm.project_id')
			->join('core_user cu', 'cu.id_user = pm.user_id')
			->where('p.is_deleted', 0)
			->where('cu.isDeleted', 0)
			->orderBy('p.name', 'ASC')
			->orderBy('cu.nama', 'ASC');

		if ($projectId > 0) {
			$builder->where('pm.project_id', $projectId);
		}

		return $builder->get()->getResultArray();
	}

	/**
	 * Detail anggota dipakai ulang pada form edit untuk menjaga selected option tetap sesuai data.
	 */
	public function getMemberById(int $id): array
	{
		return (array) $this->db->table('project_member pm')
			->select('pm.*')
			->join('project p', 'p.id_project = pm.project_id')
			->where('pm.id_project_member', $id)
			->where('p.is_deleted', 0)
			->get()
			->getRowArray();
	}

	/**
	 * Validasi anggota memastikan kombinasi project-user unik dan hanya memakai user yang masih aktif.
	 */
	public function saveData(int $id = 0): array
	{
		$projectId = (int) $this->request->getPost('project_id');
		$userId = (int) $this->request->getPost('user_id');
		$errors = [];

		if ($projectId <= 0 || !$this->projectExists($projectId)) {
			$errors[] = 'Project anggota tidak valid';
		}

		if ($userId <= 0 || !$this->userExists($userId)) {
			$errors[] = 'User anggota tidak valid';
		}

		$builder = $this->db->table('project_member')
			->select('id_project_member')
			->where('project_id', $projectId)
			->where('user_id', $userId);
		if ($id > 0) {
			$builder->where('id_project_member !=', $id);
		}

		if ($projectId > 0 && $userId > 0 && $builder->get()->getRowArray()) {
			$errors[] = 'User sudah terdaftar sebagai anggota project';
		}

		if ($errors) {
			return ['status' => 'error', 'message' => $errors, 'form_errors' => $errors];
		}

		$data = [
			'project_id' => $projectId,
			'user_id' => $userId,
			'updated_at' => date('Y-m-d H:i:s'),
		];

		if ($id > 0) {
			$this->db->table('project_member')->where('id_project_member', $id)->update($data);
		} else {
			$data['created_at'] = date('Y-m-d H:i:s');
			$this->db->table('project_member')->insert($data);
		}

		return ['status' => 'ok', 'message' => 'Data anggota project berhasil disimpan'];
	}

	/**
	 * Anggota yang masih punya task aktif ditahan penghapusannya agar task tidak kehilangan owner.
	 */
	public function deleteData(int $id): array
	{
		$member = $this->getMemberById($id);
		if (!$member) {
			return ['status' => 'warning', 'message' => 'Data anggota project tidak ditemukan'];
		}

		$taskCount = $this->db->table('project_task')->where('assigned_to', $id)->countAllResults();
		if ($taskCount > 0) {
			return ['status' => 'error', 'message' => 'Anggota project tidak dapat dihapus karena masih dipakai task'];
		}

		$this->db->table('project_member')->where('id_project_member', $id)->delete();
		return ['status' => 'ok', 'message' => 'Data anggota project berhasil dihapus'];
	}
}
