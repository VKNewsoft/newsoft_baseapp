<?php
helper('html');
?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title">Setting Header Data (Print Out Dokumen)</h5>
	</div>
	<div class="card-body">
		<?php
		if (!empty($message)) {
			show_message($message);
		}
		?>
		<form method="post" action="" class="form-horizontal p-3" enctype="multipart/form-data">
			<div>

				<div class="row mb-3">
					<label class="col-sm-2 col-form-label">Nama Perusahaan</label>
					<div class="col-sm-6">
						<input class="form-control" type="text" name="nama" value="<?=@$identitas['nama']?>" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-2 col-form-label">Alamat</label>
					<div class="col-sm-6">
						<textarea class="form-control" name="alamat"><?=@$identitas['alamat']?></textarea>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Propinsi</label>
					<div class="col-sm-6">
						<?=options(['name' => 'id_wilayah_propinsi', 'class' => 'propinsi select2'], $propinsi, set_value('id_wilayah_propinsi', $id_wilayah_propinsi) )?>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Kabupaten</label>
					<div class="col-sm-6">
						<?=options(['name' => 'id_wilayah_kabupaten', 'class' => 'kabupaten select2'], $kabupaten, set_value('id_wilayah_kabupaten', $id_wilayah_kabupaten))?>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Kecamatan</label>
					<div class="col-sm-6">
						<?=options(['name' => 'id_wilayah_kecamatan', 'class' => 'kecamatan select2'], $kecamatan, set_value('id_wilayah_kecamatan',$id_wilayah_kecamatan))?>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Kelurahan</label>
					<div class="col-sm-6" style="position:relative">
						<?=options(['name' => 'id_wilayah_kelurahan', 'class' => 'kelurahan select2'], $kelurahan, set_value('id_wilayah_kelurahan', $id_wilayah_kelurahan))?>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-2 col-form-label">Email</label>
					<div class="col-sm-6">
						<input class="form-control" type="text" name="email" value="<?=@$identitas['email']?>" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-2 col-form-label">No. Tlp</label>
					<div class="col-sm-6">
						<input class="form-control" type="text" name="no_telp" value="<?=@$identitas['no_telp']?>" required="required"/>
					</div>
				</div>
				<input type="submit" class="btn btn-primary" name="submit" value="Submit"/>
			</div>
		</form>
	</div>
</div>