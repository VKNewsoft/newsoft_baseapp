<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$current_module['judul_module']?> - <?=$title?></h5>
	</div>
	<div class="card-body">
		<?php
		helper('html');
		
		// Tombol Kembali
		echo btn_link([
			'attr' => ['class' => 'btn btn-light btn-xs mb-3'],
			'url' => str_replace('/add', '', str_replace('/edit', '', current_url())),
			'icon' => 'fa fa-arrow-circle-left',
			'label' => 'Kembali ke Daftar'
		]);
		
		// Tampilkan pesan jika ada
		if (!empty($message)) {
			show_message($message);
		}
		?>
		
		<form method="post" action="" class="needs-validation" novalidate>
			<?= csrf_field() ?>
			
			<!-- Hidden ID untuk update -->
			<?php if (!empty($kelurahan_edit['id_wilayah_kelurahan'])): ?>
				<input type="hidden" name="id" value="<?=$kelurahan_edit['id_wilayah_kelurahan']?>">
			<?php endif; ?>
			
			<div class="row">
				<div class="col-md-6">
					<!-- Provinsi -->
					<div class="mb-3">
						<label for="id_wilayah_propinsi" class="form-label">Provinsi <span class="text-danger">*</span></label>
						<select class="form-select select2" 
								id="id_wilayah_propinsi" 
								name="id_wilayah_propinsi">
							<option value="">-- Pilih Provinsi --</option>
							<?php foreach ($list_provinsi as $id => $nama): ?>
								<option value="<?=$id?>" <?= set_select('id_wilayah_propinsi', $id, ($kelurahan_edit['id_wilayah_propinsi'] ?? '') == $id) ?>>
									<?=$nama?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					
					<!-- Kabupaten/Kota -->
					<div class="mb-3">
						<label for="id_wilayah_kabupaten" class="form-label">Kabupaten/Kota <span class="text-danger">*</span></label>
						<select class="form-select select2" 
								id="id_wilayah_kabupaten" 
								name="id_wilayah_kabupaten">
							<option value="">-- Pilih Kabupaten/Kota --</option>
							<?php foreach ($list_kabupaten as $id => $nama): ?>
								<option value="<?=$id?>" <?= set_select('id_wilayah_kabupaten', $id, ($kelurahan_edit['id_wilayah_kabupaten'] ?? '') == $id) ?>>
									<?=$nama?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					
					<!-- Kecamatan -->
					<div class="mb-3">
						<label for="id_wilayah_kecamatan" class="form-label">Kecamatan <span class="text-danger">*</span></label>
						<select class="form-select select2 <?= isset($message['form_errors']['id_wilayah_kecamatan']) ? 'is-invalid' : '' ?>" 
								id="id_wilayah_kecamatan" 
								name="id_wilayah_kecamatan" 
								required>
							<option value="">-- Pilih Kecamatan --</option>
							<?php foreach ($list_kecamatan as $id => $nama): ?>
								<option value="<?=$id?>" <?= set_select('id_wilayah_kecamatan', $id, ($kelurahan_edit['id_wilayah_kecamatan'] ?? '') == $id) ?>>
									<?=$nama?>
								</option>
							<?php endforeach; ?>
						</select>
						<?php if (isset($message['form_errors']['id_wilayah_kecamatan'])): ?>
							<div class="invalid-feedback">
								<?= $message['form_errors']['id_wilayah_kecamatan'] ?>
							</div>
						<?php endif; ?>
					</div>
					
					<!-- Nama Kelurahan -->
					<div class="mb-3">
						<label for="nama_kelurahan" class="form-label">Nama Kelurahan/Desa <span class="text-danger">*</span></label>
						<input type="text" 
							   class="form-control <?= isset($message['form_errors']['nama_kelurahan']) ? 'is-invalid' : '' ?>" 
							   id="nama_kelurahan" 
							   name="nama_kelurahan" 
							   value="<?= set_value('nama_kelurahan', $kelurahan_edit['nama_kelurahan'] ?? '') ?>" 
							   placeholder="Masukkan nama kelurahan/desa"
							   required>
						<?php if (isset($message['form_errors']['nama_kelurahan'])): ?>
							<div class="invalid-feedback">
								<?= $message['form_errors']['nama_kelurahan'] ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
			
			<div class="row mt-3">
				<div class="col-md-12">
					<button type="submit" name="submit" value="1" class="btn btn-primary">
						<i class="fas fa-save"></i> Simpan Data
					</button>
					<a href="<?= str_replace('/add', '', str_replace('/edit', '', current_url())) ?>" class="btn btn-secondary">
						<i class="fas fa-times"></i> Batal
					</a>
				</div>
			</div>
		</form>
	</div>
</div>

<script>
// Initialize Select2
$(document).ready(function() {
	$('.select2').select2({
		theme: 'bootstrap-5',
		width: '100%'
	});
	
	// Cascading dropdown: Provinsi -> Kabupaten
	$('#id_wilayah_propinsi').on('change', function() {
		var idProvinsi = $(this).val();
		var $kabupatenSelect = $('#id_wilayah_kabupaten');
		var $kecamatanSelect = $('#id_wilayah_kecamatan');
		
		// Reset dropdown kabupaten dan kecamatan
		$kabupatenSelect.empty().append('<option value="">-- Pilih Kabupaten/Kota --</option>');
		$kecamatanSelect.empty().append('<option value="">-- Pilih Kecamatan --</option>');
		
		if (idProvinsi) {
			// Load kabupaten berdasarkan provinsi
			$.ajax({
				url: '<?= base_url('builtin/wilayah/ajaxGetKabupatenByProvinsi') ?>',
				type: 'GET',
				data: { id: idProvinsi },
				dataType: 'json',
				success: function(data) {
					$.each(data, function(key, value) {
						$kabupatenSelect.append('<option value="' + key + '">' + value + '</option>');
					});
				}
			});
		}
	});
	
	// Cascading dropdown: Kabupaten -> Kecamatan
	$('#id_wilayah_kabupaten').on('change', function() {
		var idKabupaten = $(this).val();
		var $kecamatanSelect = $('#id_wilayah_kecamatan');
		
		// Reset dropdown kecamatan
		$kecamatanSelect.empty().append('<option value="">-- Pilih Kecamatan --</option>');
		
		if (idKabupaten) {
			// Load kecamatan berdasarkan kabupaten
			$.ajax({
				url: '<?= base_url('builtin/wilayah/ajaxGetKecamatanByKabupaten') ?>',
				type: 'GET',
				data: { id: idKabupaten },
				dataType: 'json',
				success: function(data) {
					$.each(data, function(key, value) {
						$kecamatanSelect.append('<option value="' + key + '">' + value + '</option>');
					});
				}
			});
		}
	});
});

// Bootstrap form validation
(function () {
	'use strict'
	var forms = document.querySelectorAll('.needs-validation')
	Array.prototype.slice.call(forms)
		.forEach(function (form) {
			form.addEventListener('submit', function (event) {
				if (!form.checkValidity()) {
					event.preventDefault()
					event.stopPropagation()
				}
				form.classList.add('was-validated')
			}, false)
		})
})()
</script>
