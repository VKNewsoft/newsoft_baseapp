<?php

namespace App\Modules\ProjectCategory\Models;

use App\Modules\Project\Models\ProjectReferenceModel;

class ProjectCategoryModel extends ProjectReferenceModel
{
	/**
	 * Daftar kategori ikut menampilkan jumlah project agar admin tahu kategori yang masih aktif dipakai.
	 */
	public function getListData(): array
	{
		return $this->db->table('project_category pc')
			->select('pc.*')
			->select('(SELECT COUNT(*) FROM project p WHERE p.category_id = pc.id_project_category AND p.is_deleted = 0) AS total_project', false)
			->where('pc.is_deleted', 0)
			->orderBy('pc.name', 'ASC')
			->get()
			->getResultArray();
	}

	/**
	 * Detail kategori dipakai ulang untuk form edit dan validasi penghapusan.
	 */
	public function getCategoryById(int $id): array
	{
		return (array) $this->db->table('project_category')
			->where('id_project_category', $id)
			->where('is_deleted', 0)
			->get()
			->getRowArray();
	}

	/**
	 * Simpan kategori menjaga nama tetap unik agar dropdown kategori mudah dibaca.
	 */
	public function saveData(int $id = 0): array
	{
		$name = trim((string) $this->request->getPost('name'));
		$errors = [];

		if ($name === '') {
			$errors[] = 'Nama kategori wajib diisi';
		}

		$builder = $this->db->table('project_category')
			->select('id_project_category')
			->where('name', $name)
			->where('is_deleted', 0);
		if ($id > 0) {
			$builder->where('id_project_category !=', $id);
		}

		if ($name !== '' && $builder->get()->getRowArray()) {
			$errors[] = 'Nama kategori sudah digunakan';
		}

		if ($errors) {
			return ['status' => 'error', 'message' => $errors, 'form_errors' => $errors];
		}

		$data = [
			'name' => $name,
			'updated_at' => date('Y-m-d H:i:s'),
		];

		if ($id > 0) {
			$this->db->table('project_category')->where('id_project_category', $id)->update($data);
		} else {
			$data['created_at'] = date('Y-m-d H:i:s');
			$this->db->table('project_category')->insert($data);
		}

		return ['status' => 'ok', 'message' => 'Data kategori berhasil disimpan'];
	}

	/**
	 * Kategori tidak boleh dihapus jika masih dipakai project lain supaya integritas relasi tetap terjaga.
	 */
	public function deleteData(int $id): array
	{
		$category = $this->getCategoryById($id);
		if (!$category) {
			return ['status' => 'warning', 'message' => 'Data kategori tidak ditemukan'];
		}

		$projectCount = $this->db->table('project')
			->where('category_id', $id)
			->where('is_deleted', 0)
			->countAllResults();

		if ($projectCount > 0) {
			return ['status' => 'error', 'message' => 'Kategori tidak dapat dihapus karena masih dipakai project'];
		}

		$this->db->table('project_category')->where('id_project_category', $id)->update([
			'is_deleted' => 1,
			'updated_at' => date('Y-m-d H:i:s'),
		]);

		return ['status' => 'ok', 'message' => 'Data kategori berhasil dihapus'];
	}
}
