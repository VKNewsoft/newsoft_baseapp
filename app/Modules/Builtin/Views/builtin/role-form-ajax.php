<?php
helper('html');

$disabled = !empty($role['id_role']) ? 'readonly="readonly"' : '';
$list_module_status = [];
foreach ($module_status as $val) {
	$list_module_status[$val['id_module_status']] = $val['nama_status'];
}

$options = [0 => '- Pilih Halaman Default -'];
foreach ($list_module as $val) {
	$options[$val['id_module']] = $val['nama_module'] . ' | ' . $val['judul_module'] . ' (' . $list_module_status[$val['id_module_status']] . ')';
}

$id = '';
if (!empty($role['id_role'])) {
	$id = $role['id_role'];
}
?>
<form method="post" action="<?=$module_url?>/ajaxSave" class="role-modal-form">
	<div class="role-modal-intro">
		<div class="role-modal-badge">
			<i class="fas fa-shield-alt"></i>
		</div>
		<div>
			<h6 class="role-modal-title mb-1"><?=$title?></h6>
			<p class="role-modal-subtitle mb-0">Atur identitas role dan tentukan halaman default yang akan dibuka setelah user login.</p>
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
			<div class="form-text">Pastikan role ini sudah memiliki permission pada modul yang dipilih.</div>
		</div>
	</div>

	<input type="hidden" name="id" value="<?=$id?>"/>
	<input type="hidden" name="submit" value="submit"/>
	<?=$auth->createFormToken('form_role')?>
</form>
