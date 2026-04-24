<?php
/**
 * Form task memaksa pilihan anggota berdasarkan project agar assignment tidak keluar dari relasi project member.
 */
$taskData = $task ?? [];
$formErrors = $message['form_errors'] ?? [];
$flashMessage = session()->getFlashdata('message');
$tokenUsageEditData = $token_usage_edit ?? [];
?>
<div class="card">
	<div class="card-header d-flex justify-content-between align-items-center">
		<h5 class="card-title mb-0"><?=$title?></h5>
		<div class="d-flex gap-2">
			<?php if (!empty($taskData['project_id'])): ?>
				<a href="<?=$config->baseURL . 'project/detail?id=' . $taskData['project_id']?>" class="btn btn-outline-dark btn-sm"><i class="fas fa-chart-column me-1"></i> Project Detail</a>
			<?php endif; ?>
			<a href="<?=$module_url . ($selected_project_id > 0 ? '?project_id=' . $selected_project_id : '')?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
		</div>
	</div>
	<div class="card-body">
		<?php if (!empty($flashMessage)) { show_alert($flashMessage); } ?>

		<?php if ($formErrors): ?>
			<div class="alert alert-danger">
				<ul class="mb-0">
					<?php foreach ($formErrors as $error): ?>
						<li><?=esc($error)?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<form method="post" action="">
			<div class="row g-3">
				<div class="col-md-6">
					<label class="form-label">Project</label>
					<?=options(['name' => 'project_id', 'id' => 'task-project-id', 'class' => 'form-select'], $project_options, $taskData['project_id'] ?? $selected_project_id)?>
				</div>
				<div class="col-md-6">
					<label class="form-label">Assigned To</label>
					<?=options(['name' => 'assigned_to', 'id' => 'task-member-id', 'class' => 'form-select', 'data-selected' => ($taskData['assigned_to'] ?? '')], $member_options, $taskData['assigned_to'] ?? '')?>
				</div>
				<div class="col-md-8">
					<label class="form-label">Judul Task</label>
					<input type="text" name="title" class="form-control" value="<?=esc($taskData['title'] ?? '')?>" required>
				</div>
				<div class="col-md-2">
					<label class="form-label">Status</label>
					<?=options(['name' => 'status', 'class' => 'form-select'], $status_options, $taskData['status'] ?? 'todo')?>
				</div>
				<div class="col-md-2">
					<label class="form-label">Priority</label>
					<?=options(['name' => 'priority', 'class' => 'form-select'], $priority_options, $taskData['priority'] ?? 'medium')?>
				</div>
				<div class="col-md-6">
					<label class="form-label">Tanggal Mulai</label>
					<input type="date" name="start_date" class="form-control" value="<?=esc($taskData['start_date'] ?? '')?>">
				</div>
				<div class="col-md-6">
					<label class="form-label">Tanggal Selesai</label>
					<input type="date" name="end_date" class="form-control" value="<?=esc($taskData['end_date'] ?? '')?>">
				</div>
				<div class="col-12">
					<label class="form-label">Deskripsi</label>
					<textarea name="description" class="form-control" rows="5"><?=esc($taskData['description'] ?? '')?></textarea>
				</div>
				<div class="col-12">
					<div class="alert alert-light border mb-0">
						Saat project diganti, daftar anggota akan dimuat ulang agar task hanya bisa ditugaskan ke member project tersebut.
					</div>
				</div>
				<div class="col-12 d-flex justify-content-end gap-2">
					<a href="<?=$module_url . ($selected_project_id > 0 ? '?project_id=' . $selected_project_id : '')?>" class="btn btn-outline-secondary">Batal</a>
					<button type="submit" name="submit" value="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan</button>
				</div>
			</div>
		</form>

		<?php if (!empty($taskData['id_project_task'])): ?>
			<hr class="my-4">
			<div class="d-flex justify-content-between align-items-center mb-3">
				<div>
					<h5 class="mb-1">Token Usage</h5>
					<p class="text-muted mb-0">User token mengikuti assignment task saat ini, sehingga tidak dapat diubah manual.</p>
				</div>
				<div class="text-end">
					<div class="fw-semibold"><?=esc($taskData['assigned_name'] ?? '-')?></div>
					<div class="text-muted small"><?=esc($taskData['assigned_username'] ?? '-')?></div>
				</div>
			</div>

			<form method="post" action="" class="border rounded p-3 mb-4 bg-light">
				<div class="row g-3">
					<div class="col-md-3">
						<label class="form-label">Jenis Penggunaan</label>
						<?=options(['name' => 'usage_type', 'class' => 'form-select'], $token_usage_type_options, $tokenUsageEditData['usage_type'] ?? '')?>
					</div>
					<div class="col-md-3">
						<label class="form-label">Jumlah Token</label>
						<input type="number" min="0" step="1" name="token_used" class="form-control" value="<?=esc($tokenUsageEditData['token_used'] ?? '')?>" required>
					</div>
					<div class="col-md-3">
						<label class="form-label">Waktu Penggunaan</label>
						<input type="datetime-local" name="created_at" class="form-control" value="<?=!empty($tokenUsageEditData['created_at']) ? date('Y-m-d\TH:i', strtotime($tokenUsageEditData['created_at'])) : ''?>">
					</div>
					<div class="col-md-3">
						<label class="form-label">User Task</label>
						<input type="text" class="form-control" value="<?=esc(($taskData['assigned_name'] ?? '-') . (!empty($taskData['assigned_username']) ? ' (' . $taskData['assigned_username'] . ')' : ''))?>" readonly>
					</div>
					<div class="col-12">
						<label class="form-label">Catatan</label>
						<textarea name="notes" class="form-control" rows="3"><?=esc($tokenUsageEditData['notes'] ?? '')?></textarea>
					</div>
					<div class="col-12 d-flex justify-content-end gap-2">
						<?php if (!empty($tokenUsageEditData['id_task_token_usage'])): ?>
							<a href="<?=$module_url . '/edit?id=' . $taskData['id_project_task']?>" class="btn btn-outline-secondary">Batal Edit Log</a>
						<?php endif; ?>
						<input type="hidden" name="usage_id" value="<?=esc($tokenUsageEditData['id_task_token_usage'] ?? '')?>">
						<button type="submit" name="submit_token_usage" value="submit_token_usage" class="btn btn-dark"><i class="fas fa-microchip me-1"></i> Simpan Token Usage</button>
					</div>
				</div>
			</form>

			<div class="table-responsive project-suite-table">
				<table class="table table-striped table-bordered table-hover align-middle mb-0" data-project-datatable="1" data-page-length="10" data-order='[[0,"desc"]]'>
					<thead>
						<tr>
							<th>Waktu</th>
							<th>Jenis</th>
							<th class="text-center">Token</th>
							<th>Catatan</th>
							<th style="width: 120px;">Action</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($token_usage_history)): ?>
							<tr>
								<td colspan="5" class="text-center text-muted py-4">Belum ada log token usage untuk task ini</td>
							</tr>
						<?php endif; ?>

						<?php foreach ($token_usage_history as $tokenUsage): ?>
							<?php
							$tokenUsageActions = [
								['type' => 'link', 'href' => $module_url . '/edit?id=' . $taskData['id_project_task'] . '&token_usage_id=' . $tokenUsage['id_task_token_usage'], 'icon' => 'fas fa-edit text-success', 'label' => 'Edit'],
								['separator' => true],
								['type' => 'form', 'action' => '', 'icon' => 'fas fa-trash text-danger', 'label' => 'Hapus', 'hidden' => ['usage_id' => $tokenUsage['id_task_token_usage']], 'attrs' => ['onclick' => "return confirm('Hapus log token usage ini?')", 'name' => 'delete_token_usage', 'value' => 'delete_token_usage']],
							];
							?>
							<tr>
								<td><?=date('d M Y H:i', strtotime($tokenUsage['created_at']))?></td>
								<td><span class="badge bg-secondary"><?=esc(ucfirst($tokenUsage['usage_type']))?></span></td>
								<td class="text-center"><span class="badge bg-dark"><?=number_format((float) $tokenUsage['token_used'], 0, ',', '.')?></span></td>
								<td><?=esc($tokenUsage['notes'] ?: '-')?></td>
								<td><?=btn_dropdown_actions($tokenUsageActions)?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<?php
			/**
			 * Riwayat token usage pada mobile dibuat menjadi card agar proses audit
			 * log task tetap nyaman dibaca tanpa perlu scroll tabel ke samping.
			 */
			?>
			<div class="project-suite-card-list mt-3">
				<?php if (empty($token_usage_history)): ?>
					<div class="project-suite-empty">Belum ada log token usage untuk task ini</div>
				<?php endif; ?>

				<?php foreach ($token_usage_history as $tokenUsage): ?>
					<div class="project-suite-card">
						<div class="project-suite-card__header">
							<div>
								<div class="project-suite-card__title"><?=date('d M Y H:i', strtotime($tokenUsage['created_at']))?></div>
								<div class="project-suite-card__subtitle"><?=esc(ucfirst($tokenUsage['usage_type']))?></div>
							</div>
							<span class="project-suite-card__badge project-suite-card__badge--neutral"><?=number_format((float) $tokenUsage['token_used'], 0, ',', '.')?></span>
						</div>
						<div class="project-suite-card__meta">
							<div class="project-suite-card__meta-item project-suite-card__meta-item--full">
								<div class="project-suite-card__meta-label">Catatan</div>
								<div class="project-suite-card__meta-value"><?=esc($tokenUsage['notes'] ?: '-')?></div>
							</div>
						</div>
						<div class="project-suite-card__actions">
							<a href="<?=$module_url . '/edit?id=' . $taskData['id_project_task'] . '&token_usage_id=' . $tokenUsage['id_task_token_usage']?>" class="btn btn-success btn-sm">Edit</a>
							<form method="post" action="" onsubmit="return confirm('Hapus log token usage ini?')">
								<?=csrf_field()?>
								<input type="hidden" name="usage_id" value="<?=esc($tokenUsage['id_task_token_usage'])?>">
								<button type="submit" name="delete_token_usage" value="delete_token_usage" class="btn btn-outline-danger btn-sm">Hapus</button>
							</form>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
