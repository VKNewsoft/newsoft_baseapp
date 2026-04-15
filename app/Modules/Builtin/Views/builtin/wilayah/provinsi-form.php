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
            'url' => base_url('builtin/wilayah'),
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
			<?php if (!empty($provinsi_edit['id_wilayah_propinsi'])): ?>
				<input type="hidden" name="id" value="<?=$provinsi_edit['id_wilayah_propinsi']?>">
			<?php endif; ?>
			
			<div class="row">
				<div class="col-md-6">
					<!-- Nama Provinsi -->
					<div class="mb-3">
						<label for="nama_propinsi" class="form-label">Nama Provinsi <span class="text-danger">*</span></label>
						<input type="text" 
							   class="form-control <?= isset($message['form_errors']['nama_propinsi']) ? 'is-invalid' : '' ?>" 
							   id="nama_propinsi" 
							   name="nama_propinsi" 
							   value="<?= set_value('nama_propinsi', $provinsi_edit['nama_propinsi'] ?? '') ?>" 
							   placeholder="Masukkan nama provinsi"
							   required>
						<?php if (isset($message['form_errors']['nama_propinsi'])): ?>
							<div class="invalid-feedback">
								<?= $message['form_errors']['nama_propinsi'] ?>
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
                    <a href="<?= base_url('builtin/wilayah') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
				</div>
			</div>
		</form>
	</div>
</div>

<script>
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
