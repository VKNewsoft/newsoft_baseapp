<?php
/**
 * Daftar anggota project menyediakan filter project agar assignment user per project mudah dikelola.
 */
helper('html');
$flashMessage = session()->getFlashdata('message');
?>
<style>
	/* Halaman member dibuat mengikuti tinggi viewport agar area tabel
	   tidak menggantung dan tetap penuh sampai bawah layar. */
	.project-member-page-shell {
		min-height: calc(100vh - 170px);
		display: flex;
		flex-direction: column;
	}

	/* Card list member dibuat flex supaya filter dan tabel bisa berbagi tinggi dengan rapi. */
	.project-member-page-card {
		flex: 1 1 auto;
		display: flex;
		flex-direction: column;
	}

	/* Body card ikut elastis agar region tabel mengisi sisa tinggi setelah area filter. */
	.project-member-page-card > .card-body {
		flex: 1 1 auto;
		display: flex;
		flex-direction: column;
	}

	/* Wrapper tabel dijaga full width dan scroll hanya di dalam container. */
	.project-member-page-card .card-table-wrap,
	.project-member-page-card .table-responsive {
		flex: 1 1 auto;
		width: 100%;
		max-width: 100%;
		overflow-x: auto;
	}

	/* Tabel member diberi minimum width agar kolom action dan nama tetap proporsional. */
	.project-member-page-card table {
		width: 100%;
		min-width: 860px;
	}
</style>
<div class="page-shell project-member-page-shell">
	<div class="page-hero">
		<div>
			<div class="page-kicker">Project Member</div>
			<h3 class="page-heading"><?=$current_module['judul_module']?></h3>
			<p class="page-copy mb-0">Kelola anggota project dari data user aktif tanpa mengubah struktur tabel core user.</p>
		</div>
		<div class="page-actions">
			<a href="<?=$module_url?>/add<?=($project_id > 0 ? '?project_id=' . $project_id : '')?>" class="btn btn-success btn-sm"><i class="fa fa-plus pe-1"></i> Tambah Member</a>
		</div>
	</div>

	<?php if (!empty($flashMessage)) { show_alert($flashMessage); } ?>

	<div class="card page-card project-suite-filter-card mb-4">
		<div class="card-body">
			<form method="get" class="row g-3">
				<div class="col-lg-4 col-md-6">
					<label class="form-label">Filter Project</label>
					<?=options(['name' => 'project_id', 'class' => 'form-select'], $project_options, $project_id)?>
				</div>
				<div class="col-lg-3 col-md-6">
					<div class="form-actions">
						<button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i> Filter</button>
						<a href="<?=$module_url?>" class="btn btn-outline-secondary">Reset</a>
					</div>
				</div>
			</form>
		</div>
	</div>

	<div class="card page-card project-member-page-card">
		<div class="card-body p-0">
			<div class="table-responsive card-table-wrap project-suite-table">
				<table class="table table-striped table-bordered table-hover align-middle mb-0" data-project-datatable="1" data-page-length="10" data-order='[[1,"asc"]]'>
					<thead>
						<tr>
							<th style="width: 60px;">No</th>
							<th>Project</th>
							<th>User</th>
							<th class="text-center">Total Task</th>
							<th style="width: 120px;">Action</th>
						</tr>
					</thead>
					<tbody>
						<?php if (!$members): ?>
							<tr>
								<td colspan="5" class="text-center text-muted py-4">Belum ada data anggota project</td>
							</tr>
						<?php endif; ?>

						<?php foreach ($members as $index => $member): ?>
							<?php
							$actions = [
								['type' => 'link', 'href' => $module_url . '/edit?id=' . $member['id_project_member'], 'icon' => 'fas fa-edit text-success', 'label' => 'Edit'],
								['separator' => true],
								['type' => 'form', 'action' => $module_url . '/delete', 'icon' => 'fas fa-trash text-danger', 'label' => 'Hapus', 'hidden' => ['id' => $member['id_project_member'], 'project_id' => $project_id], 'attrs' => ['onclick' => "return confirm('Hapus anggota project ini?')"]],
							];
							?>
							<tr>
								<td class="text-center"><?=($index + 1)?></td>
								<td><?=esc($member['project_name'])?></td>
								<td>
									<div class="fw-semibold"><?=esc($member['user_name'])?></div>
									<div class="text-muted small"><?=esc($member['username'])?></div>
								</td>
								<td class="text-center"><span class="badge bg-info text-dark"><?=$member['total_task']?></span></td>
								<td><?=btn_dropdown_actions($actions)?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<?php
			/**
			 * Card mobile tetap memakai filter project yang sama agar daftar anggota
			 * tetap sinkron dengan desktop tanpa menambah endpoint atau query baru.
			 */
			?>
			<div class="project-suite-card-list p-3">
				<?php if (!$members): ?>
					<div class="project-suite-empty">Belum ada data anggota project</div>
				<?php endif; ?>

				<?php foreach ($members as $member): ?>
					<div class="project-suite-card">
						<div class="project-suite-card__header">
							<div>
								<div class="project-suite-card__title"><?=esc($member['user_name'])?></div>
								<div class="project-suite-card__subtitle"><?=esc($member['username'])?></div>
							</div>
							<span class="project-suite-card__badge project-suite-card__badge--neutral"><?=number_format((int) $member['total_task'], 0, ',', '.')?></span>
						</div>
						<div class="project-suite-card__meta">
							<div class="project-suite-card__meta-item project-suite-card__meta-item--full">
								<div class="project-suite-card__meta-label">Project</div>
								<div class="project-suite-card__meta-value"><?=esc($member['project_name'])?></div>
							</div>
							<div class="project-suite-card__meta-item project-suite-card__meta-item--full">
								<div class="project-suite-card__meta-label">Total Task</div>
								<div class="project-suite-card__meta-value"><?=number_format((int) $member['total_task'], 0, ',', '.')?></div>
							</div>
						</div>
						<div class="project-suite-card__actions">
							<a href="<?=$module_url?>/edit?id=<?=$member['id_project_member']?>" class="btn btn-success btn-sm">Edit</a>
							<form method="post" action="<?=$module_url?>/delete" onsubmit="return confirm('Hapus anggota project ini?')">
								<?=csrf_field()?>
								<input type="hidden" name="id" value="<?=$member['id_project_member']?>">
								<input type="hidden" name="project_id" value="<?=$project_id?>">
								<button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
							</form>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>
