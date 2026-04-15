<?php
helper('html');

$disabled = $request->getGet('id') ? 'readonly="readonly"' : '';
$list_module_status = [];
foreach ($module_status as $val) {
	$list_module_status[$val['id_module_status']] = $val['nama_status'];
}

$options = [0 => '- Pilih Halaman Default -'];
foreach ($list_module as $val) {
	$options[$val['id_module']] = $val['nama_module'] . ' | ' . $val['judul_module'] . ' (' . $list_module_status[$val['id_module_status']] . ')';
}

$id = '';
if (!empty($msg['id_role'])) {
	$id = $msg['id_role'];
} elseif ($request->getPost('id')) {
	$id = $request->getPost('id');
} elseif ($request->getGet('id')) {
	$id = $request->getGet('id');
}
?>
<div class="role-page-shell">
	<div class="role-page-hero">
		<div>
			<div class="role-page-kicker">Builtin / Role</div>
			<h3 class="role-page-heading"><?=$title?></h3>
			<p class="role-page-copy mb-0">Form role dibuat lebih padat supaya tidak ada ruang kosong berlebihan dan tetap nyaman dipakai saat tambah atau edit langsung.</p>
		</div>
		<div class="role-page-actions">
			<a href="<?=$module_url?>" class="btn btn-outline-secondary btn-sm"><i class="fa fa-arrow-circle-left pe-1"></i> Daftar Role</a>
			<a href="<?=$module_url?>/add" class="btn btn-success btn-sm"><i class="fa fa-plus pe-1"></i> Tambah Role</a>
		</div>
	</div>

	<?php if (!empty($msg)) {
		show_message($msg);
	} ?>

	<div class="card role-form-card">
		<div class="card-body p-4 p-lg-5">
			<form method="post" action="<?=current_url(true)?>" class="role-modal-form role-page-form">
				<div class="role-modal-intro">
					<div class="role-modal-badge">
						<i class="fas fa-shield-alt"></i>
					</div>
					<div>
						<h6 class="role-modal-title mb-1"><?=$title?></h6>
						<p class="role-modal-subtitle mb-0">Atur identitas role dan modul default dengan format yang konsisten.</p>
					</div>
				</div>

				<div class="role-modal-grid">
					<div class="role-modal-field">
						<label class="form-label">Nama Role</label>
						<input class="form-control" type="text" name="nama_role" value="<?=set_value('nama_role', $role['nama_role'] ?? '')?>" placeholder="Mis. supervisor_gudang" <?=$disabled?> required="required"/>
						<div class="form-text">Gunakan nama teknis yang konsisten dan mudah dikenali.</div>
					</div>

					<div class="role-modal-field">
						<label class="form-label">Judul Role</label>
						<input class="form-control" type="text" name="judul_role" value="<?=set_value('judul_role', $role['judul_role'] ?? '')?>" placeholder="Mis. Supervisor Gudang" required="required"/>
					</div>

					<div class="role-modal-field role-modal-field-full">
						<label class="form-label">Keterangan</label>
						<textarea class="form-control" name="keterangan" rows="3" placeholder="Jelaskan fungsi role ini secara singkat"><?=set_value('keterangan', $role['keterangan'] ?? '')?></textarea>
					</div>

					<div class="role-modal-field role-modal-field-full">
						<label class="form-label">Halaman Default</label>
						<?=options(['name' => 'id_module', 'class' => 'select2 form-select'], $options, $role['id_module'] ?? 0)?>
						<div class="form-text">Pastikan role memiliki permission pada modul yang dipilih.</div>
					</div>
				</div>

				<div class="role-page-submit">
					<input type="hidden" name="id" value="<?=$id?>"/>
					<button type="submit" name="submit" value="submit" class="btn btn-primary">Simpan Role</button>
					<?=$auth->createFormToken('form_role')?>
				</div>
			</form>
		</div>
	</div>
</div>
