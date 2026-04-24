<?php
/**
 * Detail project menjadi pusat laporan token usage per member dan riwayat log per task.
 */
helper('html');
$timelineStart = !empty($project['start_date']) ? date('d M Y', strtotime($project['start_date'])) : '-';
$timelineEnd = !empty($project['end_date']) ? date('d M Y', strtotime($project['end_date'])) : '-';
?>
<div class="page-shell">
	<div class="page-hero">
		<div>
			<div class="page-kicker">Project Detail</div>
			<h3 class="page-heading"><?=esc($project['name'])?></h3>
			<p class="page-copy mb-0"><?=esc($project['description'] ?: 'Detail project dan pelaporan token usage ditampilkan terpusat di halaman ini.')?></p>
		</div>
		<div class="page-actions">
			<a href="<?=$module_url?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
			<a href="<?=$module_url . '/edit?id=' . $project['id_project']?>" class="btn btn-outline-success btn-sm"><i class="fas fa-pen me-1"></i> Edit</a>
			<a href="<?=$config->baseURL . 'task-management?project_id=' . $project['id_project']?>" class="btn btn-primary btn-sm"><i class="fas fa-list-check me-1"></i> Task</a>
		</div>
	</div>

	<div class="row g-3 mb-4">
		<div class="col-md-3">
			<div class="card page-card h-100">
				<div class="card-body">
					<div class="text-muted small mb-2">Kategori</div>
					<div class="fw-semibold"><?=esc($project['category_name'] ?: '-')?></div>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card page-card h-100">
				<div class="card-body">
					<div class="text-muted small mb-2">Timeline</div>
					<div class="fw-semibold"><?=$timelineStart?> - <?=$timelineEnd?></div>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card page-card h-100">
				<div class="card-body">
					<div class="text-muted small mb-2">Total Task</div>
					<div class="fw-semibold"><?=number_format((int) $project['total_task'], 0, ',', '.')?></div>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card page-card h-100 border-dark">
				<div class="card-body">
					<div class="text-muted small mb-2">Total Token Project</div>
					<div class="fw-semibold"><?=number_format((float) $token_total, 0, ',', '.')?></div>
				</div>
			</div>
		</div>
	</div>

	<div class="card page-card project-suite-filter-card mb-4">
		<div class="card-body">
			<form method="get" class="row g-3">
				<input type="hidden" name="id" value="<?=$project['id_project']?>">
				<div class="col-lg-4 col-md-6">
					<label class="form-label">Member</label>
					<?=options(['name' => 'user_id', 'class' => 'form-select'], $project_user_options, $token_filters['user_id'])?>
				</div>
				<div class="col-lg-3 col-md-6">
					<label class="form-label">Tanggal Dari</label>
					<input type="date" name="date_from" class="form-control" value="<?=esc($token_filters['date_from'] ?? '')?>">
				</div>
				<div class="col-lg-3 col-md-6">
					<label class="form-label">Tanggal Sampai</label>
					<input type="date" name="date_to" class="form-control" value="<?=esc($token_filters['date_to'] ?? '')?>">
				</div>
				<div class="col-lg-2 col-md-6">
					<div class="form-actions">
						<button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i> Filter</button>
						<a href="<?=$module_url?>/detail?id=<?=$project['id_project']?>" class="btn btn-outline-secondary">Reset</a>
					</div>
				</div>
			</form>
		</div>
	</div>

	<div class="card page-card mb-4">
		<div class="page-toolbar">
			<div>
				<h5 class="mb-1">Ringkasan Token per Member</h5>
				<p class="mb-0 text-muted">Summary ini mengikuti filter member dan rentang tanggal yang sedang aktif.</p>
			</div>
		</div>
		<div class="table-responsive card-table-wrap project-suite-table">
			<table class="table table-striped table-bordered table-hover align-middle mb-0">
				<thead>
					<tr>
						<th>Member</th>
						<th class="text-center">Total Token</th>
					</tr>
				</thead>
				<tbody>
					<?php if (!$token_summary_members): ?>
						<tr>
							<td colspan="2" class="text-center text-muted py-4">Belum ada summary token usage</td>
						</tr>
					<?php endif; ?>

					<?php foreach ($token_summary_members as $summary): ?>
						<tr>
							<td>
								<div class="fw-semibold"><?=esc($summary['nama'])?></div>
								<div class="text-muted small"><?=esc($summary['username'])?></div>
							</td>
							<td class="text-center"><span class="badge bg-dark"><?=number_format((float) $summary['total_token_used'], 0, ',', '.')?></span></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr>
						<th>Total Project Token Usage</th>
						<th class="text-center"><?=number_format((float) $token_total, 0, ',', '.')?></th>
					</tr>
				</tfoot>
			</table>
		</div>

		<?php
		/**
		 * Ringkasan per member ditampilkan ulang dalam bentuk card agar total
		 * token tetap terbaca jelas pada mobile tanpa scroll horizontal.
		 */
		?>
		<div class="project-suite-card-list p-3">
			<?php if (!$token_summary_members): ?>
				<div class="project-suite-empty">Belum ada summary token usage</div>
			<?php endif; ?>

			<?php foreach ($token_summary_members as $summary): ?>
				<div class="project-suite-card">
					<div class="project-suite-card__header">
						<div>
							<div class="project-suite-card__title"><?=esc($summary['nama'])?></div>
							<div class="project-suite-card__subtitle"><?=esc($summary['username'])?></div>
						</div>
						<span class="project-suite-card__badge project-suite-card__badge--neutral"><?=number_format((float) $summary['total_token_used'], 0, ',', '.')?></span>
					</div>
					<div class="project-suite-card__meta">
						<div class="project-suite-card__meta-item project-suite-card__meta-item--full">
							<div class="project-suite-card__meta-label">Total Token</div>
							<div class="project-suite-card__meta-value"><?=number_format((float) $summary['total_token_used'], 0, ',', '.')?></div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>

			<div class="project-suite-card">
				<div class="project-suite-card__meta">
					<div class="project-suite-card__meta-item project-suite-card__meta-item--full">
						<div class="project-suite-card__meta-label">Total Project Token Usage</div>
						<div class="project-suite-card__meta-value"><?=number_format((float) $token_total, 0, ',', '.')?></div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="card page-card">
		<div class="page-toolbar">
			<div>
				<h5 class="mb-1">Riwayat Token Usage</h5>
				<p class="mb-0 text-muted">Riwayat ini memperlihatkan hubungan log token dengan task dan member project.</p>
			</div>
		</div>
		<div class="table-responsive card-table-wrap project-suite-table">
			<table class="table table-striped table-bordered table-hover align-middle mb-0" data-project-datatable="1" data-page-length="10" data-order='[[0,"desc"]]'>
				<thead>
					<tr>
						<th>Waktu</th>
						<th>Task</th>
						<th>Member</th>
						<th>Jenis</th>
						<th class="text-center">Token</th>
						<th>Catatan</th>
					</tr>
				</thead>
				<tbody>
					<?php if (!$token_logs): ?>
						<tr>
							<td colspan="6" class="text-center text-muted py-4">Belum ada riwayat token usage</td>
						</tr>
					<?php endif; ?>

					<?php foreach ($token_logs as $log): ?>
						<tr>
							<td><?=date('d M Y H:i', strtotime($log['created_at']))?></td>
							<td>
								<div class="fw-semibold"><?=esc($log['task_title'])?></div>
								<div class="text-muted small">Task ID: <?=$log['task_id']?></div>
							</td>
							<td>
								<div class="fw-semibold"><?=esc($log['user_name'])?></div>
								<div class="text-muted small"><?=esc($log['username'])?></div>
							</td>
							<td><span class="badge bg-secondary"><?=esc(ucfirst($log['usage_type']))?></span></td>
							<td class="text-center"><span class="badge bg-dark"><?=number_format((float) $log['token_used'], 0, ',', '.')?></span></td>
							<td><?=esc($log['notes'] ?: '-')?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<?php
		/**
		 * Riwayat usage dibuat versi card mobile agar task, member, dan token
		 * tetap informatif saat halaman dibuka dari layar kecil.
		 */
		?>
		<div class="project-suite-card-list p-3">
			<?php if (!$token_logs): ?>
				<div class="project-suite-empty">Belum ada riwayat token usage</div>
			<?php endif; ?>

			<?php foreach ($token_logs as $log): ?>
				<div class="project-suite-card">
					<div class="project-suite-card__header">
						<div>
							<div class="project-suite-card__title"><?=esc($log['task_title'])?></div>
							<div class="project-suite-card__subtitle"><?=date('d M Y H:i', strtotime($log['created_at']))?></div>
						</div>
						<span class="project-suite-card__badge project-suite-card__badge--neutral"><?=number_format((float) $log['token_used'], 0, ',', '.')?></span>
					</div>
					<div class="project-suite-card__meta">
						<div class="project-suite-card__meta-item">
							<div class="project-suite-card__meta-label">Member</div>
							<div class="project-suite-card__meta-value"><?=esc($log['user_name'])?></div>
						</div>
						<div class="project-suite-card__meta-item">
							<div class="project-suite-card__meta-label">Username</div>
							<div class="project-suite-card__meta-value"><?=esc($log['username'])?></div>
						</div>
						<div class="project-suite-card__meta-item">
							<div class="project-suite-card__meta-label">Jenis</div>
							<div class="project-suite-card__meta-value"><?=esc(ucfirst($log['usage_type']))?></div>
						</div>
						<div class="project-suite-card__meta-item">
							<div class="project-suite-card__meta-label">Task ID</div>
							<div class="project-suite-card__meta-value"><?=number_format((int) $log['task_id'], 0, ',', '.')?></div>
						</div>
						<div class="project-suite-card__meta-item project-suite-card__meta-item--full">
							<div class="project-suite-card__meta-label">Catatan</div>
							<div class="project-suite-card__meta-value"><?=esc($log['notes'] ?: '-')?></div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
