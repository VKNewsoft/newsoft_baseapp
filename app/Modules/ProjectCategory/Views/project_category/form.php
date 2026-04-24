<?php
/**
 * Form kategori dijaga sederhana karena perubahannya langsung memengaruhi dropdown project.
 */
$categoryData = $category ?? [];
$formErrors = $message['form_errors'] ?? [];
?>
<div class="card">
	<div class="card-header d-flex justify-content-between align-items-center">
		<h5 class="card-title mb-0"><?=$title?></h5>
		<a href="<?=$module_url?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
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
				<div class="col-md-8">
					<label class="form-label">Nama Category</label>
					<input type="text" name="name" class="form-control" value="<?=esc($categoryData['name'] ?? '')?>" required>
				</div>
				<div class="col-12">
					<div class="alert alert-light border mb-0">
						Nama kategori harus unik agar pemilihan kategori pada project tetap jelas dan tidak duplikat.
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
