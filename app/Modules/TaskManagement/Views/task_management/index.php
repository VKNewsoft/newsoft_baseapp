<?php
/**
 * Halaman task digrupkan per project agar monitoring beban kerja dan timeline lebih mudah dibaca.
 */
helper('html');
$flashMessage = session()->getFlashdata('message');

/**
 * Data task diratakan sekali di view agar tabel desktop dan card mobile
 * memakai sumber data yang sama tanpa perlu mengubah controller lama.
 */
$taskRows = [];
foreach ($task_groups as $group) {
	$projectInfo = $group['project'];
	foreach ($group['tasks'] as $task) {
		$taskRows[] = [
			'project' => $projectInfo,
			'task' => $task,
		];
	}
}
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
			<p class="page-copy mb-0">Filter task berdasarkan project, anggota, status, dan rentang tanggal dengan tampilan yang tetap nyaman di desktop maupun mobile.</p>
		</div>
		<div class="page-actions">
			<a href="<?=$module_url?>/add<?=(!empty($filters['project_id']) ? '?project_id=' . $filters['project_id'] : '')?>" class="btn btn-success btn-sm"><i class="fa fa-plus pe-1"></i> Tambah Task</a>
		</div>
	</div>

	<?php if (!empty($flashMessage)) { show_alert($flashMessage); } ?>

	<div class="card page-card project-suite-filter-card mb-4">
		<div class="card-body">
			<form method="get" class="row g-3">
				<div class="col-lg-3 col-md-6">
					<label class="form-label">Project</label>
					<?=options(['name' => 'project_id', 'class' => 'form-select'], $project_options, $filters['project_id'])?>
				</div>
				<div class="col-lg-3 col-md-6">
					<label class="form-label">Member</label>
					<?=options(['name' => 'member_id', 'class' => 'form-select'], $member_options, $filters['member_id'])?>
				</div>
				<div class="col-lg-2 col-md-6">
					<label class="form-label">Status</label>
					<?=options(['name' => 'status', 'class' => 'form-select'], $status_options, $filters['status'])?>
				</div>
				<div class="col-lg-2 col-md-6">
					<label class="form-label">Tanggal Dari</label>
					<input type="date" name="date_from" class="form-control" value="<?=esc($filters['date_from'] ?? '')?>">
				</div>
				<div class="col-lg-2 col-md-6">
					<label class="form-label">Tanggal Sampai</label>
					<input type="date" name="date_to" class="form-control" value="<?=esc($filters['date_to'] ?? '')?>">
				</div>
				<div class="col-12">
					<div class="form-actions">
						<button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i> Filter</button>
						<a href="<?=$module_url?>" class="btn btn-outline-secondary">Reset</a>
					</div>
				</div>
			</form>
		</div>
	</div>

	<div class="card page-card mb-4 task-page-group">
		<div class="card-header">
			<div class="fw-semibold">Daftar Task</div>
			<div class="text-muted small">List task mengikuti filter aktif dan tetap memakai data relasi project member yang sama.</div>
		</div>
		<div class="card-body p-0">
			<div class="table-responsive card-table-wrap project-suite-table">
				<table class="table table-striped table-bordered table-hover align-middle mb-0" data-project-datatable="1" data-page-length="10" data-order='[[5,"asc"]]'>
					<thead>
						<tr>
							<th>Project</th>
							<th>Task</th>
							<th>Assigned To</th>
							<th>Status</th>
							<th>Priority</th>
							<th>Timeline</th>
							<th class="text-center">Token</th>
							<th style="width: 120px;">Action</th>
						</tr>
					</thead>
					<tbody>
						<?php if (!$taskRows): ?>
							<tr>
								<td colspan="8" class="text-center text-muted py-4">Belum ada task yang sesuai dengan filter.</td>
							</tr>
						<?php endif; ?>

						<?php foreach ($taskRows as $row): ?>
							<?php
							$projectInfo = $row['project'];
							$task = $row['task'];
							$taskTimelineStart = !empty($task['start_date']) ? date('d M Y', strtotime($task['start_date'])) : '-';
							$taskTimelineEnd = !empty($task['end_date']) ? date('d M Y', strtotime($task['end_date'])) : '-';
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
										'date_from_filter' => $filters['date_from'],
										'date_to_filter' => $filters['date_to'],
									],
									'attrs' => ['onclick' => "return confirm('Hapus task ini?')"],
								],
							];
							?>
							<tr>
								<td>
									<div class="fw-semibold"><?=esc($projectInfo['name'])?></div>
									<div class="text-muted small"><?=esc($projectInfo['category_name'] ?: 'Tanpa kategori')?></div>
								</td>
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
								<td><?=$taskTimelineStart?> - <?=$taskTimelineEnd?></td>
								<td class="text-center"><span class="badge bg-dark"><?=number_format((float) $task['total_token_used'], 0, ',', '.')?></span></td>
								<td><?=btn_dropdown_actions($actions)?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<?php
			/**
			 * Card mobile menampilkan hirarki info penting saja agar task mudah
			 * discan di layar kecil tanpa membutuhkan horizontal scroll.
			 */
			?>
			<div class="project-suite-card-list p-3">
				<?php if (!$taskRows): ?>
					<div class="project-suite-empty">Belum ada task yang sesuai dengan filter.</div>
				<?php endif; ?>

				<?php foreach ($taskRows as $row): ?>
					<?php
					$projectInfo = $row['project'];
					$task = $row['task'];
					$taskTimelineStart = !empty($task['start_date']) ? date('d M Y', strtotime($task['start_date'])) : '-';
					$taskTimelineEnd = !empty($task['end_date']) ? date('d M Y', strtotime($task['end_date'])) : '-';
					?>
					<div class="project-suite-card">
						<div class="project-suite-card__header">
							<div>
								<div class="project-suite-card__title"><?=esc($task['title'])?></div>
								<div class="project-suite-card__subtitle"><?=esc($projectInfo['name'])?></div>
							</div>
							<span class="project-suite-card__badge project-suite-card__badge--neutral"><?=esc(str_replace('_', ' ', ucfirst($task['status'])))?></span>
						</div>
						<div class="project-suite-card__meta">
							<div class="project-suite-card__meta-item">
								<div class="project-suite-card__meta-label">Assigned To</div>
								<div class="project-suite-card__meta-value"><?=esc($task['assigned_name'])?></div>
							</div>
							<div class="project-suite-card__meta-item">
								<div class="project-suite-card__meta-label">Priority</div>
								<div class="project-suite-card__meta-value"><?=esc(ucfirst($task['priority']))?></div>
							</div>
							<div class="project-suite-card__meta-item project-suite-card__meta-item--full">
								<div class="project-suite-card__meta-label">Timeline</div>
								<div class="project-suite-card__meta-value"><?=$taskTimelineStart?> - <?=$taskTimelineEnd?></div>
							</div>
							<div class="project-suite-card__meta-item">
								<div class="project-suite-card__meta-label">Kategori</div>
								<div class="project-suite-card__meta-value"><?=esc($projectInfo['category_name'] ?: '-')?></div>
							</div>
							<div class="project-suite-card__meta-item">
								<div class="project-suite-card__meta-label">Token Usage</div>
								<div class="project-suite-card__meta-value"><?=number_format((float) $task['total_token_used'], 0, ',', '.')?></div>
							</div>
						</div>
						<div class="project-suite-card__actions">
							<a href="<?=$config->baseURL . 'project-member?project_id=' . $projectInfo['id_project']?>" class="btn btn-outline-primary btn-sm">Member</a>
							<a href="<?=$module_url?>/edit?id=<?=$task['id_project_task']?>" class="btn btn-success btn-sm">Edit</a>
							<form method="post" action="<?=$module_url?>/delete" onsubmit="return confirm('Hapus task ini?')">
								<?=csrf_field()?>
								<input type="hidden" name="id" value="<?=$task['id_project_task']?>">
								<input type="hidden" name="project_id_filter" value="<?=$filters['project_id']?>">
								<input type="hidden" name="member_id_filter" value="<?=$filters['member_id']?>">
								<input type="hidden" name="status_filter" value="<?=esc($filters['status'])?>">
								<input type="hidden" name="date_from_filter" value="<?=esc($filters['date_from'])?>">
								<input type="hidden" name="date_to_filter" value="<?=esc($filters['date_to'])?>">
								<button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
							</form>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>
