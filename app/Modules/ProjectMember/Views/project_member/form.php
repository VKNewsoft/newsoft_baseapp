<?php
/**
 * Form anggota project menghubungkan project dengan user aktif untuk mendukung validasi assignment task.
 */
$memberData = $member ?? [];
$formErrors = $message['form_errors'] ?? [];
?>
<div class="card">
	<div class="card-header d-flex justify-content-between align-items-center">
		<h5 class="card-title mb-0"><?=$title?></h5>
		<a href="<?=$module_url . ($project_id > 0 ? '?project_id=' . $project_id : '')?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
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
					<label class="form-label">Project</label>
					<?=options(['name' => 'project_id', 'class' => 'form-select'], $project_options, $memberData['project_id'] ?? $project_id)?>
				</div>
				<div class="col-md-6">
					<label class="form-label">User</label>
					<?=options(['name' => 'user_id', 'class' => 'form-select'], $user_options, $memberData['user_id'] ?? '')?>
				</div>
				<div class="col-12">
					<div class="alert alert-light border mb-0">
						User yang dipilih akan menjadi kandidat assignment task pada project terkait.
					</div>
				</div>
				<div class="col-12 d-flex justify-content-end gap-2">
					<a href="<?=$module_url . ($project_id > 0 ? '?project_id=' . $project_id : '')?>" class="btn btn-outline-secondary">Batal</a>
					<button type="submit" name="submit" value="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan</button>
				</div>
			</div>
		</form>
	</div>
</div>
