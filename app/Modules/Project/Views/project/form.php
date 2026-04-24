<?php
/**
 * Form project memusatkan field timeline dan kategori agar input master project tetap konsisten.
 */
$projectData = $project ?? [];
$formErrors = $message['form_errors'] ?? [];
?>
<div class="card">
	<div class="card-header d-flex justify-content-between align-items-center">
		<h5 class="card-title mb-0"><?=$title?></h5>
		<div class="d-flex gap-2">
			<a href="<?=$module_url?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
			<?php if (!empty($projectData['id_project'])): ?>
				<a href="<?=$module_url . '/detail?id=' . $projectData['id_project']?>" class="btn btn-outline-dark btn-sm"><i class="fas fa-chart-column me-1"></i> Detail</a>
				<a href="<?=$config->baseURL . 'project-member?project_id=' . $projectData['id_project']?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-users me-1"></i> Kelola Member</a>
			<?php endif; ?>
		</div>
	</div>
	<div class="card-body">
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
					<label class="form-label">Nama Project</label>
					<input type="text" name="name" class="form-control" value="<?=esc($projectData['name'] ?? '')?>" required>
				</div>
				<div class="col-md-6">
					<label class="form-label">Kategori</label>
					<?=options(['name' => 'category_id', 'class' => 'form-select'], $category_options, $projectData['category_id'] ?? '')?>
				</div>
				<div class="col-md-6">
					<label class="form-label">Tanggal Mulai</label>
					<input type="date" name="start_date" class="form-control" value="<?=esc($projectData['start_date'] ?? '')?>">
				</div>
				<div class="col-md-6">
					<label class="form-label">Tanggal Selesai</label>
					<input type="date" name="end_date" class="form-control" value="<?=esc($projectData['end_date'] ?? '')?>">
				</div>
				<div class="col-12">
					<label class="form-label">Deskripsi</label>
					<textarea name="description" class="form-control" rows="5"><?=esc($projectData['description'] ?? '')?></textarea>
				</div>
				<div class="col-12">
					<div class="alert alert-light border mb-0">
						<span class="fw-semibold">Catatan timeline:</span>
						Tanggal selesai wajib lebih besar atau sama dengan tanggal mulai agar tampilan project tetap valid.
					</div>
				</div>
				<div class="col-12 d-flex justify-content-end gap-2">
					<a href="<?=$module_url?>" class="btn btn-outline-secondary">Batal</a>
					<button type="submit" name="submit" value="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan</button>
				</div>
			</div>
		</form>
	</div>
</div>
