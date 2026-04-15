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
			<?php if (!empty($kabupaten_edit['id_wilayah_kabupaten'])): ?>
				<input type="hidden" name="id" value="<?=$kabupaten_edit['id_wilayah_kabupaten']?>">
			<?php endif; ?>
			
			<div class="row">
				<div class="col-md-6">
					<!-- Provinsi -->
					<div class="mb-3">
						<label for="id_wilayah_propinsi" class="form-label">Provinsi <span class="text-danger">*</span></label>
						<select class="form-select select2 <?= isset($message['form_errors']['id_wilayah_propinsi']) ? 'is-invalid' : '' ?>" 
								id="id_wilayah_propinsi" 
								name="id_wilayah_propinsi" 
								required>
							<option value="">-- Pilih Provinsi --</option>
							<?php foreach ($list_provinsi as $id => $nama): ?>
								<option value="<?=$id?>" <?= set_select('id_wilayah_propinsi', $id, ($kabupaten_edit['id_wilayah_propinsi'] ?? '') == $id) ?>>
									<?=$nama?>
								</option>
							<?php endforeach; ?>
						</select>
						<?php if (isset($message['form_errors']['id_wilayah_propinsi'])): ?>
							<div class="invalid-feedback">
								<?= $message['form_errors']['id_wilayah_propinsi'] ?>
							</div>
						<?php endif; ?>
					</div>
					
					<!-- Nama Kabupaten -->
					<div class="mb-3">
						<label for="nama_kabupaten" class="form-label">Nama Kabupaten/Kota <span class="text-danger">*</span></label>
						<input type="text" 
							   class="form-control <?= isset($message['form_errors']['nama_kabupaten']) ? 'is-invalid' : '' ?>" 
							   id="nama_kabupaten" 
							   name="nama_kabupaten" 
							   value="<?= set_value('nama_kabupaten', $kabupaten_edit['nama_kabupaten'] ?? '') ?>" 
							   placeholder="Masukkan nama kabupaten/kota"
							   required>
						<?php if (isset($message['form_errors']['nama_kabupaten'])): ?>
							<div class="invalid-feedback">
								<?= $message['form_errors']['nama_kabupaten'] ?>
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
