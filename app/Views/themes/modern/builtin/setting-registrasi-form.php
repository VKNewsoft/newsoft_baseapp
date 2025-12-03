<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<?php 
			helper ('html');
		if (!empty($message)) {
			show_message($message);
		}
		?>
		<form method="post" action="" id="form-setting" enctype="multipart/form-data">
			<div class="tab-content">
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Perbolehkan</label>
					<div class="col-sm-5">
						<?php 
						echo options(['name' => 'enable'], ['Y' => 'Ya', 'N' => 'Tidak'], set_value('enable', @$setting['enable']));
						?>
						<small>Perbolehkan user register akun baru?</small>
					</div>
				</div>
				<?php
				$display = @$setting['enable'] == 'N' ? ' style="display:none"' : '';
				?>
				<div class="detail-container"<?=$display?>>
					<div class="row mb-3">
						<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Aktivasi</label>
						<div class="col-sm-5">
							<?php 
							echo options(['name' => 'metode_aktivasi'], ['langsung' => 'Langsung Aktif', 'manual' => 'Manual Oleh Admin', 'email' => 'Via Email Konfirmasi'], set_value('metode_aktivasi', @$setting['metode_aktivasi']));
							?>
							<small>Langsung: setelah register, user langsung aktif dan bisa login. Manual: setelah register, Admin mengaktivasi akun melalui menu edit user. Email Konfirmasi: setelah register, link aktivasi akan dikirim via email.</small>
						</div>
					</div>
					<div class="row mb-3">
						<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Role</label>
						<div class="col-sm-5">
							<?php
							foreach ($role as $key => $val) {
								$options[$val['id_role']] = $val['judul_role'];
							}
							echo options(['name' => 'id_role'], $options, set_value('id_role', @$setting['id_role']));
							?>
							<small>Role untuk user baru yang melakukan registrasi.</small>
						</div>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Halaman Default</label>
					<div class="col-sm-8 col-md-6 col-lg-4">
						<?php
						foreach ($list_module as $val) {
							$options[$val['id_module']] = $val['nama_module'] . ' - ' . $val['judul_module'];
						}
						echo options(['name' => 'id_module'], $options, set_value('id_module', @$setting['id_module'])); 
						?>
						<span class="text-muted">Pastikan user memiliki hak akses ke module</span>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-5">
						<button type="submit" name="submit" id="btn-submit" value="submit" class="btn btn-primary">Submit</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>