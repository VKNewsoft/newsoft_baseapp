<?php
/**
 * Halaman task digrupkan per project agar monitoring beban kerja dan timeline lebih mudah dibaca.
 */
helper('html');
$flashMessage = session()->getFlashdata('message');
?>
<style>
	/* Layout task dibuat fleksibel agar area tabel memanjang sampai mendekati footer
	   tanpa memaksa lebar tabel melewati area layar aktif. */
	.task-page-shell {
		min-height: calc(100vh - 140px);
		display: flex;
		flex-direction: column;
	}

	/* Wrapper group diatur full width supaya kartu tabel memenuhi area konten. */
	.task-page-group {
		width: 100%;
		max-width: 100%;
	}

	/* Overflow horizontal tetap ditahan di container agar tabel tidak keluar layar. */
	.task-page-group .card-table-wrap,
	.task-page-group .table-responsive {
		flex: 1 1 auto;
		width: 100%;
		max-width: 100%;
		overflow-x: auto;
	}

	/* Tabel diberi lebar minimum seperlunya agar kolom tetap rapi namun tidak memaksa viewport melebar. */
	.task-page-group table {
		width: 100%;
		min-width: 980px;
	}

	/* Card task per project dibuat fleksibel supaya area tabel pada tiap grup ikut memanjang. */
	.task-page-group {
		flex: 1 1 auto;
		display: flex;
		flex-direction: column;
	}

	/* Header card tetap fixed height, sedangkan body tabel mengisi sisa ruang yang tersedia. */
	.task-page-group .card-header {
		flex: 0 0 auto;
	}
</style>
<div class="page-shell task-page-shell">
	<div class="page-hero">
		<div>
			<div class="page-kicker">Task Management</div>
			<h3 class="page-heading"><?=$current_module['judul_module']?></h3>
			<p class="page-copy mb-0">Filter task berdasarkan project, anggota, dan status dengan tampilan grup per project.</p>
		</div>
		<div class="page-actions">
			<a href="<?=$module_url?>/add<?=(!empty($filters['project_id']) ? '?project_id=' . $filters['project_id'] : '')?>" class="btn btn-success btn-sm"><i class="fa fa-plus pe-1"></i> Tambah Task</a>
		</div>
	</div>

	<?php if (!empty($flashMessage)) { show_alert($flashMessage); } ?>

	<div class="card page-card mb-4">
		<div class="card-body">
			<form method="get" class="row g-3">
				<div class="col-md-4">
					<label class="form-label">Project</label>
					<?=options(['name' => 'project_id', 'class' => 'form-select'], $project_options, $filters['project_id'])?>
				</div>
				<div class="col-md-4">
					<label class="form-label">Member</label>
					<?=options(['name' => 'member_id', 'class' => 'form-select'], $member_options, $filters['member_id'])?>
				</div>
				<div class="col-md-4">
					<label class="form-label">Status</label>
					<?=options(['name' => 'status', 'class' => 'form-select'], $status_options, $filters['status'])?>
				</div>
				<div class="col-12 d-flex gap-2">
					<button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i> Filter</button>
					<a href="<?=$module_url?>" class="btn btn-outline-secondary">Reset</a>
				</div>
			</form>
		</div>
	</div>

	<?php if (!$task_groups): ?>
		<div class="alert alert-light border">Belum ada task yang sesuai dengan filter.</div>
	<?php endif; ?>

	<?php foreach ($task_groups as $group): ?>
		<?php
		$projectInfo = $group['project'];
		$projectTimelineStart = !empty($projectInfo['start_date']) ? date('d M Y', strtotime($projectInfo['start_date'])) : '-';
		$projectTimelineEnd = !empty($projectInfo['end_date']) ? date('d M Y', strtotime($projectInfo['end_date'])) : '-';
		?>
		<div class="card page-card mb-4 task-page-group">
			<div class="card-header d-flex justify-content-between align-items-center">
				<div>
					<div class="fw-semibold"><?=esc($projectInfo['name'])?></div>
					<div class="text-muted small">Kategori: <?=esc($projectInfo['category_name'] ?: '-')?> | Timeline: <?=$projectTimelineStart?> - <?=$projectTimelineEnd?></div>
				</div>
				<a href="<?=$config->baseURL . 'project-member?project_id=' . $projectInfo['id_project']?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-users me-1"></i> Member</a>
			</div>
			<div class="table-responsive card-table-wrap">
				<table class="table table-striped table-bordered table-hover align-middle mb-0">
					<thead>
						<tr>
							<th>Task</th>
							<th>Assigned To</th>
							<th>Status</th>
							<th>Priority</th>
							<th class="text-center">Token</th>
							<th>Timeline</th>
							<th style="width: 120px;">Action</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($group['tasks'] as $task): ?>
							<?php
							// Hidden filter dikirim ulang saat delete supaya user kembali ke list dengan konteks yang sama.
							$actions = [
								['type' => 'link', 'href' => $module_url . '/edit?id=' . $task['id_project_task'], 'icon' => 'fas fa-edit text-success', 'label' => 'Edit'],
								['separator' => true],
								[
									'type' => 'form',
									'action' => $module_url . '/delete',
									'icon' => 'fas fa-trash text-danger',
									'label' => 'Hapus',
									'hidden' => [
										'id' => $task['id_project_task'],
										'project_id_filter' => $filters['project_id'],
										'member_id_filter' => $filters['member_id'],
										'status_filter' => $filters['status'],
									],
									'attrs' => ['onclick' => "return confirm('Hapus task ini?')"],
								],
							];
							$taskTimelineStart = !empty($task['start_date']) ? date('d M Y', strtotime($task['start_date'])) : '-';
							$taskTimelineEnd = !empty($task['end_date']) ? date('d M Y', strtotime($task['end_date'])) : '-';
							?>
							<tr>
								<td>
									<div class="fw-semibold"><?=esc($task['title'])?></div>
									<div class="text-muted small"><?=esc($task['description'] ?: 'Tanpa deskripsi task')?></div>
								</td>
								<td>
									<div class="fw-semibold"><?=esc($task['assigned_name'])?></div>
									<div class="text-muted small"><?=esc($task['assigned_username'])?></div>
								</td>
								<td><span class="badge bg-secondary"><?=esc(str_replace('_', ' ', ucfirst($task['status'])))?></span></td>
								<td><span class="badge bg-warning text-dark"><?=esc(ucfirst($task['priority']))?></span></td>
								<td class="text-center"><span class="badge bg-dark"><?=number_format((float) $task['total_token_used'], 0, ',', '.')?></span></td>
								<td><?=$taskTimelineStart?> - <?=$taskTimelineEnd?></td>
								<td><?=btn_dropdown_actions($actions)?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	<?php endforeach; ?>
</div>
