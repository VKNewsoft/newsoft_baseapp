<?php
helper('html');
?>
<form method="post" class="modal-form" id="add-form" action="<?=current_url()?>" >
	<div class="row mb-3">
		<label class="col-sm-3 col-form-label">Nama Kategori</label>
		<div class="col-sm-8">
			<input class="form-control" type="text" name="nama_kategori" value="<?=@$kategori['nama_kategori']?>" placeholder="Nama Kategori" required="required"/>
		</div>
	</div>
	<div class="row mb-3">
		<label class="col-sm-3 col-form-label">Deskripsi</label>
		<div class="col-sm-8">
			<textarea class="form-control" name="deskripsi"><?=@$kategori['deskripsi']?></textarea>
		</div>
	</div>
	<div class="row mb-3">
		<label class="col-sm-3 col-form-label">Aktif</label>
		<div class="col-sm-8">
			<?php
			echo options(['name' => 'aktif'], [ 'Y' => 'Ya', 'N' => 'Tidak' ], @$kategori['aktif'])
			?>
		</div>
	</div>
	<div class="row mb-3">
		<label class="col-sm-3 col-form-label">Show Title</label>
		<div class="col-sm-8">
			<?php
			echo options(['name' => 'show_title'], [ 'Y' => 'Ya', 'N' => 'Tidak' ], @$kategori['show_title'])
			?>
			<small class="form-text text-muted"><em>Tampilkan nama kategori di menu</em></small>
		</div>
	</div>
	<input type="hidden" name="id" value="<?= esc($request->getGet('id') ?? '') ?>"/>
</form>