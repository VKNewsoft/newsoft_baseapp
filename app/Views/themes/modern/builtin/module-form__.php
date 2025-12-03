<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<?php
		helper ('html');
		echo btn_link([
			'attr' => ['class' => 'btn btn-success btn-xs'],
			'url' => $config->baseURL . 'builtin/module/add',
			'icon' => 'fa fa-plus',
			'label' => 'Tambah Module'
		]);
		
		echo btn_link([
			'attr' => ['class' => 'btn btn-light btn-xs'],
			'url' => $config->baseURL . 'builtin/module',
			'icon' => 'fa fa-arrow-circle-left',
			'label' => 'Daftar Module'
		]);
		?>
		<hr/>
		<?php
		if (!empty($message)) {
			show_message($message);
		}
		
		if (empty($nama_module)) {
			$fields = ['nama_module', 'judul_module', 'deskripsi', 'id_module_status'];
			foreach ($fields as $val) {
				$$val = '';
			}
		}
		?>
		<form method="post" class="modal-form" id="add-form" action="" >
			<div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama Module</label>
					<div class="col-sm-4">
						<input class="form-control" type="text" name="nama_module" value="<?=set_value('nama_module', @$nama_module)?>" placeholder="Nama Module" required/>
						<small>Sesuai nama yang ada di URL.</small>
						<input type="hidden" name="nama_module_old" value="<?=set_value('nama_module', @$nama_module)?>">
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Judul Module</label>
					<div class="col-sm-4">
						<input class="form-control" type="text" name="judul_module" value="<?=set_value('judul_module', @$judul_module)?>" placeholder="Nama Module" required/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Deskripsi</label>
					<div class="col-sm-4">
						<input class="form-control" type="text" name="deskripsi" value="<?=set_value('deskripsi', @$deskripsi)?>" placeholder="Deskripsi"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Login</label>
					<div class="col-sm-4">
						<?php
						echo options(['name' => 'login'], ['Y' => 'Ya', 'N' => 'Tidak', 'R' => 'Restrict'], ['login', @$login])?>
						<small>Apakah untuk mengakses module perlu login? Restrict berarti untuk mengakses module, posisi tidak boleh login, jika posisi sedang login, module tidak bisa diakses (halaman akan diarahkan ke default module), contoh module login dan register.<span class="text-danger">Jika module tidak perlu login, maka harus dibuat layout yang aman, contoh pada menu "Tanpa Login"</span></small>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Status</label>
					<div class="col-sm-4">
						<?php 
						foreach ($module_status as $item) {
							$options[$item['id_module_status']] = $item['nama_status'];
						}
						echo options(['name' => 'id_module_status'], $options, ['id_module_status', @$id_module_status])?>
					</div>
				</div>
				
				<?php 
				$id = '';
				if (!empty($request->getGet('id'))) {
					$id = $request->getGet('id');
				} elseif (!empty($msg['module_id'])) { // ADD Auto Increment
					$id = $msg['module_id'];
				} ?>
				<input type="hidden" name="id" value="<?=$id?>"/>
				<button type="submit" name="submit" value="submit" class="btn btn-primary mt-2">Save</button>
			</div>
		</form>
	</div>
</div>