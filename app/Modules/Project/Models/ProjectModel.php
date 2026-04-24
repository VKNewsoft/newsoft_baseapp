<?php

namespace App\Modules\Project\Models;

class ProjectModel extends ProjectReferenceModel
{
	/**
	 * Daftar project dilengkapi jumlah member dan task agar halaman list memberi ringkasan cepat.
	 */
	public function getListData(): array
	{
		return $this->db->table('project p')
			->select('p.*, pc.name AS category_name')
			->select('(SELECT COUNT(*) FROM project_member pm WHERE pm.project_id = p.id_project) AS total_member', false)
			->select('(SELECT COUNT(*) FROM project_task pt WHERE pt.project_id = p.id_project) AS total_task', false)
			->select('(SELECT COALESCE(SUM(pttu.token_used), 0) FROM project_task_token_usage pttu WHERE pttu.project_id = p.id_project) AS total_token_used', false)
			->join('project_category pc', 'pc.id_project_category = p.category_id', 'left')
			->where('p.is_deleted', 0)
			->orderBy('p.start_date', 'ASC')
			->orderBy('p.name', 'ASC')
			->get()
			->getResultArray();
	}

	/**
	 * Detail project dipakai pada form edit dan tombol navigasi ke module anggota/task.
	 */
	public function getProjectById(int $id): array
	{
		return (array) $this->db->table('project')
			->where('id_project', $id)
			->where('is_deleted', 0)
			->get()
			->getRowArray();
	}

	/**
	 * Detail project diperluas dengan agregat token agar halaman detail dapat menjadi pusat reporting.
	 */
	public function getProjectDetailById(int $id): array
	{
		return (array) $this->db->table('project p')
			->select('p.*, pc.name AS category_name')
			->select('(SELECT COUNT(*) FROM project_member pm WHERE pm.project_id = p.id_project) AS total_member', false)
			->select('(SELECT COUNT(*) FROM project_task pt WHERE pt.project_id = p.id_project) AS total_task', false)
			->select('(SELECT COALESCE(SUM(pttu.token_used), 0) FROM project_task_token_usage pttu WHERE pttu.project_id = p.id_project) AS total_token_used', false)
			->join('project_category pc', 'pc.id_project_category = p.category_id', 'left')
			->where('p.id_project', $id)
			->where('p.is_deleted', 0)
			->get()
			->getRowArray();
	}

	/**
	 * Simpan project dibungkus validasi terpusat agar field timeline dan kategori tetap konsisten.
	 */
	public function saveData(int $id = 0): array
	{
		$validation = $this->validateData($id);
		if ($validation['errors']) {
			return [
				'status' => 'error',
				'message' => $validation['errors'],
				'form_errors' => $validation['errors'],
			];
		}

		$data = $validation['data'];
		$now = date('Y-m-d H:i:s');
		$data['updated_at'] = $now;

		$this->db->transStart();
		if ($id > 0) {
			$this->db->table('project')->where('id_project', $id)->update($data);
		} else {
			$data['created_at'] = $now;
			$this->db->table('project')->insert($data);
			$id = (int) $this->db->insertID();
		}
		$this->db->transComplete();

		if (!$this->db->transStatus()) {
			return ['status' => 'error', 'message' => 'Data project gagal disimpan'];
		}

		return ['status' => 'ok', 'message' => 'Data project berhasil disimpan', 'id_project' => $id];
	}

	/**
	 * Penghapusan project dilakukan berurutan agar relasi task dan member tetap aman.
	 */
	public function deleteData(int $id): array
	{
		$project = $this->getProjectById($id);
		if (!$project) {
			return ['status' => 'warning', 'message' => 'Data project tidak ditemukan'];
		}

		$this->db->transStart();
		$this->db->table('project_task_token_usage')->where('project_id', $id)->delete();
		$this->db->table('project_task')->where('project_id', $id)->delete();
		$this->db->table('project_member')->where('project_id', $id)->delete();
		$this->db->table('project')->where('id_project', $id)->update([
			'is_deleted' => 1,
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		$this->db->transComplete();

		if (!$this->db->transStatus()) {
			return ['status' => 'error', 'message' => 'Data project gagal dihapus'];
		}

		return ['status' => 'ok', 'message' => 'Data project berhasil dihapus'];
	}

	/**
	 * Validasi disusun terpisah agar controller tetap ringan dan mudah dibaca.
	 */
	private function validateData(int $id = 0): array
	{
		$name = trim((string) $this->request->getPost('name'));
		$description = trim((string) $this->request->getPost('description'));
		$categoryId = (int) $this->request->getPost('category_id');
		$startDate = $this->normalizeDate($this->request->getPost('start_date'));
		$endDate = $this->normalizeDate($this->request->getPost('end_date'));

		$errors = [];
		if ($name === '') {
			$errors[] = 'Nama project wajib diisi';
		}

		if ($categoryId <= 0 || !$this->categoryExists($categoryId)) {
			$errors[] = 'Kategori project tidak valid';
		}

		if ($this->request->getPost('start_date') !== '' && $startDate === null) {
			$errors[] = 'Tanggal mulai project tidak valid';
		}

		if ($this->request->getPost('end_date') !== '' && $endDate === null) {
			$errors[] = 'Tanggal selesai project tidak valid';
		}

		if ($startDate && $endDate && $endDate < $startDate) {
			$errors[] = 'Tanggal selesai project tidak boleh lebih kecil dari tanggal mulai';
		}

		$builder = $this->db->table('project')
			->select('id_project')
			->where('name', $name)
			->where('is_deleted', 0);
		if ($id > 0) {
			$builder->where('id_project !=', $id);
		}

		if ($name !== '' && $builder->get()->getRowArray()) {
			$errors[] = 'Nama project sudah digunakan';
		}

		return [
			'errors' => $errors,
			'data' => [
				'name' => $name,
				'description' => $description !== '' ? $description : null,
				'category_id' => $categoryId,
				'start_date' => $startDate,
				'end_date' => $endDate,
			],
		];
	}

	/**
	 * Ringkasan token per member memakai left join agar member tanpa log tetap tampil dengan nilai nol.
	 */
	public function getProjectTokenSummaryByMember(int $projectId, array $filters = []): array
	{
		$sumCondition = '1 = 1';
		if (!empty($filters['date_from'])) {
			$sumCondition .= ' AND DATE(pttu.created_at) >= ' . $this->db->escape($filters['date_from']);
		}
		if (!empty($filters['date_to'])) {
			$sumCondition .= ' AND DATE(pttu.created_at) <= ' . $this->db->escape($filters['date_to']);
		}

		$builder = $this->db->table('project_member pm')
			->select('pm.id_project_member, cu.id_user, cu.nama, cu.username', false)
			->select('COALESCE(SUM(CASE WHEN ' . $sumCondition . ' THEN pttu.token_used ELSE 0 END), 0) AS total_token_used', false)
			->join('core_user cu', 'cu.id_user = pm.user_id')
			->join('project_task_token_usage pttu', 'pttu.project_id = pm.project_id AND pttu.user_id = pm.user_id', 'left')
			->where('pm.project_id', $projectId)
			->where('cu.isDeleted', 0)
			->groupBy('pm.id_project_member')
			->orderBy('cu.nama', 'ASC');

		if (!empty($filters['user_id'])) {
			$builder->where('pm.user_id', (int) $filters['user_id']);
		}

		return $builder->get()->getResultArray();
	}

	/**
	 * Total token project mengikuti filter laporan agar angka header konsisten dengan tabel detail.
	 */
	public function getProjectTokenTotal(int $projectId, array $filters = []): float
	{
		$builder = $this->db->table('project_task_token_usage')
			->select('COALESCE(SUM(token_used), 0) AS total_token_used', false)
			->where('project_id', $projectId);

		if (!empty($filters['user_id'])) {
			$builder->where('user_id', (int) $filters['user_id']);
		}

		if (!empty($filters['date_from'])) {
			$builder->where('DATE(created_at) >=', $filters['date_from']);
		}

		if (!empty($filters['date_to'])) {
			$builder->where('DATE(created_at) <=', $filters['date_to']);
		}

		$row = $builder->get()->getRowArray();
		return (float) ($row['total_token_used'] ?? 0);
	}

	/**
	 * Riwayat token project dipakai untuk audit dan pelacakan penggunaan per task.
	 */
	public function getProjectTokenUsageLogs(int $projectId, array $filters = []): array
	{
		$builder = $this->db->table('project_task_token_usage pttu')
			->select('pttu.*, pt.title AS task_title, cu.nama AS user_name, cu.username, p.name AS project_name')
			->join('project_task pt', 'pt.id_project_task = pttu.task_id')
			->join('core_user cu', 'cu.id_user = pttu.user_id')
			->join('project p', 'p.id_project = pttu.project_id')
			->where('pttu.project_id', $projectId)
			->orderBy('pttu.created_at', 'DESC')
			->orderBy('pttu.id_task_token_usage', 'DESC');

		if (!empty($filters['user_id'])) {
			$builder->where('pttu.user_id', (int) $filters['user_id']);
		}

		if (!empty($filters['date_from'])) {
			$builder->where('DATE(pttu.created_at) >=', $filters['date_from']);
		}

		if (!empty($filters['date_to'])) {
			$builder->where('DATE(pttu.created_at) <=', $filters['date_to']);
		}

		return $builder->get()->getResultArray();
	}
}
