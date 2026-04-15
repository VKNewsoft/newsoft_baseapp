<?php
if ($message) {
	show_message($message);
	exit;
}
helper('html');
?>
<form method="post" action="<?=base_url()?>/builtin/permission/ajaxEdit">
<div class="row mb-3">
	<div class="col-sm-12">
		<label>Nama Module</label>
		<?=options(['name' => 'id_module'], $modules, $result['id_module'])?>
	</div>
</div>
<div class="row mb-3">
	<div class="col-sm-12">
		<label>Nama Permission</label>
		<input type="text" class="form-control" name="nama_permission" value="<?=$result['nama_permission']?>"/>
		<input type="hidden" class="form-control" name="nama_permission_old" value="<?=$result['nama_permission']?>"/>
		<small>Nama permission sebaiknya diawali dengan create, read, update, atau delete, misal: read_all, read_own, dll. Namun bisa juga dengan nama lain, misal: send_email</small> 
	</div>
</div>
<div class="row mb-3">
	<div class="col-sm-12">
		<label>Judul Permission</label>
		<input type="text" class="form-control" name="judul_permission" value="<?=$result['judul_permission']?>"/>
	</div>
</div>
<div class="row mb-3">
	<div class="col-sm-12">
		<label>Keterangan</label>
		<textarea class="form-control" name="keterangan"><?=$result['keterangan']?></textarea>
	</div>
</div>
<input type="hidden" name="id" value="<?=$result['id_module_permission']?>"/>
<input type="hidden" name="id_module_old" value="<?=$result['id_module']?>"/>
<input type="hidden" name="generate_permission" value="manual"/>
<input type="hidden" name="generate" value="manual"/>
</form>