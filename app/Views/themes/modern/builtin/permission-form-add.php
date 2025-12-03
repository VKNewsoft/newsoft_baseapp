<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<a href="<?=base_url()?>/builtin/permission/add" class="btn btn-xs btn-success me-3"><i class="fa fa-plus pe-1"></i> Tambah Permission</a>
		<a href="<?=base_url()?>/builtin/permission" class="btn btn-xs btn-light"><i class="fa fa-arrow-left"></i> Data Permission</a>
		<hr/>
		<?php
		if ($message) {
			show_message($message);
		}
		helper('html');
		?>
		<form method="post" action="" class="mb-5">
			<div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Module</label>
					<div class="col-sm-8">
						<?=options(['name' => 'id_module', 'class' => 'select2'], $modules, set_value('id_module', $request->getGet('id_module') ?? ''))?>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Permission</label>
					<div class="col-sm-8">
						<?=options(['name' => 'generate_permission']
									, ['crud_all' => 'CRUD All', 'crud_own' => 'CRUD Own', 'crud_all_crud_own' => 'CRUD All + CRUD Own', 'manual' => 'Manual']
									, set_value('generate_permission', @$generate_permission) 
								)?>
						<small>CRUD All: otomatis akan membuat permission CRUD All, yaitu: create, read_all, update_all, delete_all (jika permission sudah ada, maka tidak akan dibuat). CRUD Own berarti read_own, update_own, dan delete_own</small>
					</div>
				</div>
				<?php
				$display = $request->getPost('generate_permission') != 'manual' || !$request->getPost() ? 'style="display:none"' : '';
				?>
				<div class="input-container" <?=$display?>>
					<div class="row mb-3">
						<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama Permission</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" name="nama_permission" value="<?=set_value('nama_permission', '')?>"/>
							<small>Nama permission sebaiknya diawali dengan create, read, update, atau delete, misal: read_all, read_own, dll. Namun bisa juga dengan nama lain, misal: send_email</small> 
						</div>
					</div>
					<div class="row mb-3">
						<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Judul Permission</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" name="judul_permission" value="<?=set_value('judul_permission', '')?>"/>
						</div>
					</div>
					<div class="row mb-3">
						<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Keterangan</label>
						<div class="col-sm-8">
							<textarea class="form-control" name="keterangan"><?=set_value('keterangan', '')?></textarea>
						</div>
					</div>
				</div>
				<div class="row mb-3">
					<div class="col-sm-8 offset-sm-2">
						<button type="submit" name="submit" value="submit" class="btn btn-primary mt-2">Submit</button>
						<input type="hidden" name="id" value="<?=set_value('id', @$id)?>"/>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>