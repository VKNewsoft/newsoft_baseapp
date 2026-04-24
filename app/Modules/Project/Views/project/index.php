<?php
/**
 * Halaman daftar project menampilkan ringkasan timeline, kategori, dan jumlah relasi utama.
 */
helper('html');
$flashMessage = session()->getFlashdata('message');
?>
<style>
	/* Wrapper halaman dibuat setinggi viewport agar area list project tetap
	   memanjang sampai bawah layar tanpa membuat konten keluar dari area layout. */
	.project-page-shell {
		min-height: calc(100vh - 140px);
		display: flex;
		flex-direction: column;
	}

	/* Card utama dibuat fleksibel supaya body tabel dapat mengisi sisa tinggi halaman. */
	.project-page-card {
		flex: 1 1 auto;
		display: flex;
		flex-direction: column;
	}

	/* Body card mengikuti tinggi card agar area tabel tidak berhenti di tengah layar. */
	.project-page-card > .card-body {
		flex: 1 1 auto;
		display: flex;
		flex-direction: column;
	}

	/* Area tabel dibuat elastis sampai mendekati footer, tetapi overflow horizontal
	   tetap ditahan di dalam container agar tidak melewati layar. */
	.project-page-card .card-table-wrap,
	.project-page-card .table-responsive {
		flex: 1 1 auto;
		width: 100%;
		max-width: 100%;
		overflow-x: auto;
	}

	/* Tabel mengikuti lebar container dan diberi minimum seperlunya agar kolom tetap rapi. */
	.project-page-card table {
		width: 100%;
		min-width: 1100px;
	}
</style>
<div class="page-shell project-page-shell">
	<div class="page-hero">
		<div>
			<div class="page-kicker">Project</div>
			<h3 class="page-heading"><?=$current_module['judul_module']?></h3>
			<p class="page-copy mb-0">Kelola project, timeline, anggota, dan jalur menuju task management dari satu halaman ringkas.</p>
		</div>
		<div class="page-actions">
			<a href="<?=$module_url?>/add" class="btn btn-success btn-sm"><i class="fa fa-plus pe-1"></i> Tambah Project</a>
		</div>
	</div>

	<?php if (!empty($flashMessage)) { show_alert($flashMessage); } ?>

	<div class="card page-card project-page-card">
		<div class="card-body p-0">
			<div class="page-toolbar">
				<div>
					<h5 class="mb-1">Daftar Project</h5>
					<p class="mb-0 text-muted">Timeline project ditampilkan langsung agar monitoring rentang kerja tidak perlu masuk ke halaman detail.</p>
				</div>
			</div>

			<div class="table-responsive card-table-wrap project-suite-table">
				<table class="table table-striped table-bordered table-hover align-middle mb-0" data-project-datatable="1" data-page-length="10" data-order='[[1,"asc"]]'>
					<thead>
						<tr>
							<th style="width: 60px;">No</th>
							<th>Nama Project</th>
							<th>Kategori</th>
							<th>Timeline</th>
							<th class="text-center">Member</th>
							<th class="text-center">Task</th>
							<th class="text-center">Total Token</th>
							<th style="width: 120px;">Action</th>
						</tr>
					</thead>
					<tbody>
						<?php if (!$projects): ?>
							<tr>
								<td colspan="8" class="text-center text-muted py-4">Belum ada data project</td>
							</tr>
						<?php endif; ?>

						<?php foreach ($projects as $index => $project): ?>
							<?php
							// Action list dibuat konsisten dengan helper bawaan supaya tampilan admin tetap seragam.
							$actions = [
								['type' => 'link', 'href' => $module_url . '/detail?id=' . $project['id_project'], 'icon' => 'fas fa-chart-column text-warning', 'label' => 'Detail'],
								['type' => 'link', 'href' => $module_url . '/edit?id=' . $project['id_project'], 'icon' => 'fas fa-edit text-success', 'label' => 'Edit'],
								['type' => 'link', 'href' => $config->baseURL . 'project-member?project_id=' . $project['id_project'], 'icon' => 'fas fa-users text-primary', 'label' => 'Member'],
								['type' => 'link', 'href' => $config->baseURL . 'task-management?project_id=' . $project['id_project'], 'icon' => 'fas fa-list-check text-info', 'label' => 'Task'],
								['separator' => true],
								['type' => 'form', 'action' => $module_url . '/delete', 'icon' => 'fas fa-trash text-danger', 'label' => 'Hapus', 'hidden' => ['id' => $project['id_project']], 'attrs' => ['onclick' => "return confirm('Hapus project ini?')"]],
							];
							$timelineStart = !empty($project['start_date']) ? date('d M Y', strtotime($project['start_date'])) : '-';
							$timelineEnd = !empty($project['end_date']) ? date('d M Y', strtotime($project['end_date'])) : '-';
							?>
							<tr>
								<td class="text-center"><?=($index + 1)?></td>
								<td>
									<div class="fw-semibold"><?=esc($project['name'])?></div>
									<div class="text-muted small"><?=esc($project['description'] ?: 'Tanpa deskripsi project')?></div>
								</td>
								<td><?=esc($project['category_name'] ?: '-')?></td>
								<td><?=$timelineStart?> - <?=$timelineEnd?></td>
								<td class="text-center"><span class="badge bg-secondary"><?=$project['total_member']?></span></td>
								<td class="text-center"><span class="badge bg-info text-dark"><?=$project['total_task']?></span></td>
								<td class="text-center"><span class="badge bg-dark"><?=number_format((float) $project['total_token_used'], 0, ',', '.')?></span></td>
								<td><?=btn_dropdown_actions($actions)?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<?php
			/**
			 * Card mobile memakai data project yang sama agar tampilan responsif
			 * tidak mengubah query, routing, maupun alur action yang sudah ada.
			 */
			?>
			<div class="project-suite-card-list p-3">
				<?php if (!$projects): ?>
					<div class="project-suite-empty">Belum ada data project</div>
				<?php endif; ?>

				<?php foreach ($projects as $project): ?>
					<?php
					$timelineStart = !empty($project['start_date']) ? date('d M Y', strtotime($project['start_date'])) : '-';
					$timelineEnd = !empty($project['end_date']) ? date('d M Y', strtotime($project['end_date'])) : '-';
					?>
					<div class="project-suite-card">
						<div class="project-suite-card__header">
							<div>
								<div class="project-suite-card__title"><?=esc($project['name'])?></div>
								<div class="project-suite-card__subtitle"><?=esc($project['description'] ?: 'Tanpa deskripsi project')?></div>
							</div>
							<span class="project-suite-card__badge project-suite-card__badge--neutral"><?=esc($project['category_name'] ?: '-')?></span>
						</div>
						<div class="project-suite-card__meta">
							<div class="project-suite-card__meta-item project-suite-card__meta-item--full">
								<div class="project-suite-card__meta-label">Timeline</div>
								<div class="project-suite-card__meta-value"><?=$timelineStart?> - <?=$timelineEnd?></div>
							</div>
							<div class="project-suite-card__meta-item">
								<div class="project-suite-card__meta-label">Member</div>
								<div class="project-suite-card__meta-value"><?=number_format((int) $project['total_member'], 0, ',', '.')?></div>
							</div>
							<div class="project-suite-card__meta-item">
								<div class="project-suite-card__meta-label">Task</div>
								<div class="project-suite-card__meta-value"><?=number_format((int) $project['total_task'], 0, ',', '.')?></div>
							</div>
							<div class="project-suite-card__meta-item project-suite-card__meta-item--full">
								<div class="project-suite-card__meta-label">Total Token</div>
								<div class="project-suite-card__meta-value"><?=number_format((float) $project['total_token_used'], 0, ',', '.')?></div>
							</div>
						</div>
						<div class="project-suite-card__actions">
							<a href="<?=$module_url?>/detail?id=<?=$project['id_project']?>" class="btn btn-warning btn-sm">Detail</a>
							<a href="<?=$module_url?>/edit?id=<?=$project['id_project']?>" class="btn btn-success btn-sm">Edit</a>
							<a href="<?=$config->baseURL?>project-member?project_id=<?=$project['id_project']?>" class="btn btn-outline-primary btn-sm">Member</a>
							<a href="<?=$config->baseURL?>task-management?project_id=<?=$project['id_project']?>" class="btn btn-outline-info btn-sm">Task</a>
							<form method="post" action="<?=$module_url?>/delete" onsubmit="return confirm('Hapus project ini?')">
								<?=csrf_field()?>
								<input type="hidden" name="id" value="<?=$project['id_project']?>">
								<button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
							</form>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>
