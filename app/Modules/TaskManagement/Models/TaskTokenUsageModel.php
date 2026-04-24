<?php

namespace App\Modules\TaskManagement\Models;

use App\Modules\Project\Models\ProjectReferenceModel;

class TaskTokenUsageModel extends ProjectReferenceModel
{
	/**
	 * Riwayat token usage per task ditampilkan dengan urutan terbaru agar audit log mudah dibaca.
	 */
	public function getTaskUsageHistory(int $taskId): array
	{
		return $this->db->table('project_task_token_usage pttu')
			->select('pttu.*, cu.nama AS user_name, cu.username, pt.title AS task_title')
			->join('core_user cu', 'cu.id_user = pttu.user_id')
			->join('project_task pt', 'pt.id_project_task = pttu.task_id')
			->where('pttu.task_id', $taskId)
			->orderBy('pttu.created_at', 'DESC')
			->orderBy('pttu.id_task_token_usage', 'DESC')
			->get()
			->getResultArray();
	}

	/**
	 * Detail log dipakai untuk proses edit dan delete agar tetap dipastikan berasal dari task yang benar.
	 */
	public function getUsageById(int $usageId): array
	{
		return (array) $this->db->table('project_task_token_usage')
			->where('id_task_token_usage', $usageId)
			->get()
			->getRowArray();
	}

	/**
	 * Simpan log token selalu mengambil project dan user dari task saat ini agar relasi tetap akurat.
	 */
	public function saveUsage(int $taskId, int $usageId = 0): array
	{
		$context = $this->getTaskAssignmentContext($taskId);
		if (!$context) {
			return ['status' => 'error', 'message' => 'Task untuk token usage tidak valid'];
		}

		$tokenUsed = trim((string) $this->request->getPost('token_used'));
		$usageType = trim((string) $this->request->getPost('usage_type'));
		$createdAt = trim((string) $this->request->getPost('created_at'));
		$notes = trim((string) $this->request->getPost('notes'));

		$errors = [];
		if ($tokenUsed === '' || !is_numeric($tokenUsed)) {
			$errors[] = 'Jumlah token wajib berupa angka';
		}

		$tokenUsedValue = (float) $tokenUsed;
		if ($tokenUsed !== '' && $tokenUsedValue < 0) {
			$errors[] = 'Jumlah token tidak boleh bernilai negatif';
		}

		$usageTypeOptions = $this->getTokenUsageTypeOptions(false);
		if ($usageType === '' || !array_key_exists($usageType, $usageTypeOptions)) {
			$errors[] = 'Jenis penggunaan token tidak valid';
		}

		$createdAtValue = $this->normalizeDateTimeValue($createdAt);
		if ($createdAt !== '' && $createdAtValue === null) {
			$errors[] = 'Waktu penggunaan token tidak valid';
		}

		if ($usageId > 0) {
			$current = $this->getUsageById($usageId);
			if (!$current || (int) $current['task_id'] !== $taskId) {
				$errors[] = 'Log token usage tidak ditemukan pada task ini';
			}
		}

		if ($errors) {
			return ['status' => 'error', 'message' => $errors, 'form_errors' => $errors];
		}

		$data = [
			'task_id' => $taskId,
			'project_id' => (int) $context['project_id'],
			'user_id' => (int) $context['user_id'],
			'token_used' => $tokenUsedValue,
			'usage_type' => $usageType,
			'notes' => $notes !== '' ? $notes : null,
			'created_at' => $createdAtValue ?: date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		];

		if ($usageId > 0) {
			// Project dan user selalu disinkronkan dari task agar log tidak bisa menyimpang dari assignment task.
			$this->db->table('project_task_token_usage')->where('id_task_token_usage', $usageId)->update($data);
		} else {
			$this->db->table('project_task_token_usage')->insert($data);
		}

		return ['status' => 'ok', 'message' => 'Log token usage berhasil disimpan'];
	}

	/**
	 * Penghapusan log hanya diizinkan jika log memang milik task yang sedang dibuka.
	 */
	public function deleteUsage(int $taskId, int $usageId): array
	{
		$current = $this->getUsageById($usageId);
		if (!$current || (int) $current['task_id'] !== $taskId) {
			return ['status' => 'warning', 'message' => 'Log token usage tidak ditemukan'];
		}

		$this->db->table('project_task_token_usage')->where('id_task_token_usage', $usageId)->delete();
		return ['status' => 'ok', 'message' => 'Log token usage berhasil dihapus'];
	}

	/**
	 * Saat task pindah project atau member, seluruh log token ikut disinkronkan agar laporan tetap akurat.
	 */
	public function syncUsageContextByTask(int $taskId): void
	{
		$context = $this->getTaskAssignmentContext($taskId);
		if (!$context) {
			return;
		}

		$this->db->table('project_task_token_usage')
			->where('task_id', $taskId)
			->update([
				'project_id' => (int) $context['project_id'],
				'user_id' => (int) $context['user_id'],
				'updated_at' => date('Y-m-d H:i:s'),
			]);
	}

	/**
	 * Helper normalisasi datetime dipakai untuk input log manual dari form task.
	 */
	private function normalizeDateTimeValue(?string $dateTime): ?string
	{
		$dateTime = trim((string) $dateTime);
		if ($dateTime === '') {
			return null;
		}

		$parsed = \DateTime::createFromFormat('Y-m-d\TH:i', $dateTime);
		if (!$parsed || $parsed->format('Y-m-d\TH:i') !== $dateTime) {
			return null;
		}

		return $parsed->format('Y-m-d H:i:s');
	}
}
