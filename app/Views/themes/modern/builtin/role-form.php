<?php

helper('html');
?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<a href="<?=$module_url?>/add" class="btn btn-success btn-xs" id="add-menu"><i class="fa fa-plus pe-1"></i> Tambah Role</a>
		<a href="<?=$module_url?>" class="btn btn-light btn-xs" id="add-menu"><i class="fa fa-arrow-circle-left pe-1"></i> Daftar Role</a>
		<hr/>
		<?php
		if (!empty($msg)) {
			show_message($msg);
		}
		
		$disabled = $request->getGet('id') ? 'readonly="readonly"' : '';
		?>
		<form method="post" action="<?=current_url(true)?>" >
			<div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama Role</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="nama_role" value="<?=set_value('nama_role', @$role['nama_role'] ?: '')?>" placeholder="Nama Role" <?=$disabled?> required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Judul Role</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="judul_role" value="<?=set_value('judul_role', @$role['judul_role'] ?: '')?>" placeholder="Judul Role" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Keterangan</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="keterangan" value="<?=set_value('keterangan', @$role['keterangan'] ?: '')?>" placeholder="Keterangan"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Halaman Default</label>
					<div class="col-sm-8">
						<?php
						$list_module_status = [];
						foreach ($module_status as $val) {
							$list_module_status[$val['id_module_status']] = $val['nama_status'];
						}
						$options = [];
						foreach ($list_module as $val) {
							$options[$val['id_module']] = $val['nama_module'] . ' | ' . $val['judul_module'] . ' (' . $list_module_status[$val['id_module_status']] . ')';
						}
						echo options(['name' => 'id_module', 'class' => 'select2'], $options, @$role['id_module']);
						?>
						<p class="mt-0"><em>Halaman awal sesaat setelah user login. Pastikan role memiliki permission pada halaman yang dipilih</p>
					</div>
				</div>
				<div class="row mb-3">
					<?php 
					$id = '';
					if (!empty($msg['id_role'])) {
						$id = $msg['id_role'];
					} 
					elseif ($request->getPost('id')) {
						$id = $request->getPost('id');
					}
					elseif ($request->getGet('id')) {
						$id = $request->getGet('id');
					} ?>
					<input type="hidden" name="id" value="<?=$id?>"/>
					<div class="col-sm-8 offset-sm-2">
						<button type="submit" name="submit" value="submit" class="btn btn-primary mt-2">Save</button>
						<?=$auth->createFormToken('form_role')?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>