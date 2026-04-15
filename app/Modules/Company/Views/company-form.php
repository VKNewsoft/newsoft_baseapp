<?php
helper('html');
?>
<form method="post" action="" class="form-horizontal p-3" enctype="multipart/form-data">
	<div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Nama Company</label>
			<div class="col-sm-9">
				<input class="form-control" type="text" name="nama_company" value="<?=@$tenant['nama_company']?>" required="required"/>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Lokasi Company</label>
			<div class="col-sm-9">
				<input class="form-control" type="text" name="kode_lokasi" value="<?=@$tenant['kode_lokasi']?>" required="required"/>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Profit Sharing</label>
			<div class="col-sm-9">
				<input class="form-control" type="number" name="rev_share" value="<?=@$tenant['rev_share']?>" required="required"/>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Deskripsi</label>
			<div class="col-sm-9">
				<textarea class="form-control" name="deskripsi" required="required"><?=@$tenant['deskripsi']?></textarea>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Status Company</label>
			<div class="col-sm-9">
				<div class="form-inline">
					<?=options(['name' => 'tenant_aktif'], ['N' => 'Tidak Aktif', 'Y' => 'Aktif'], @$tenant['tenant_aktif'])?>
				</div>
				<div class="text-muted">Default pilihan status ketika input form</div>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Bank</label>
			<div class="col-sm-9">
				<div class="form-inline">
					<?=options(['class' => 'select2', 'name' => 'id_bank'], $bank_list, @$id_bank)?>
				</div>
				<div class="text-muted">Default pilihan bank ketika input form</div>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Norek Company</label>
			<div class="col-sm-9">
				<input class="form-control" type="text" name="no_rekening" value="<?=@$tenant['no_rekening']?>" required="required"/>
			</div>
		</div>
	</div>
	<input type="hidden" name="id" value="<?= esc($request->getGet('id') ?? '') ?>"/>
</form>