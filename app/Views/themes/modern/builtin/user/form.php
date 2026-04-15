<?php
if ($request->getGet('mobile') == 'true') {
	echo $this->extend('themes/modern/layout-mobile');
	echo $this->section('content');
}
?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	<div class="card-body">
		<?php 
			helper('html');
			helper('builtin/util');
			if (empty($request->getGet('mobile'))) {
				if (in_array('create', $user_permission)) {
					echo btn_link(['attr' => ['class' => 'btn btn-success btn-xs'],
						'url' => $module_url . '/add',
						'icon' => 'fa fa-plus',
						'label' => 'Tambah User'
					]);
				}
				
				echo btn_link(['attr' => ['class' => 'btn btn-light btn-xs'],
					'url' => $module_url,
					'icon' => 'fa fa-arrow-circle-left',
					'label' => 'Daftar User'
				]);
				
				echo '<hr/>';
			}

			$form_errors = isset($form_errors) ? $form_errors : @$message;

            if (!empty($form_errors)) {
                if (isset($status) && $status=== 'error') {
                    echo '<div class="alert alert-danger" role="alert">';
                    echo $message;
                    echo '</div>';
                } else {
                    show_message($form_errors);
					// Hilangkan history agar refresh tidak mengirim ulang data form (membantu mencegah resubmit)
					echo '<script>
						(function(){
							if (window.history && window.history.replaceState) {
								var url = window.location.protocol + "//" + window.location.host + window.location.pathname + window.location.search;
								window.history.replaceState(null, "", url);
							}
						})();
					</script>';
                }
            }
		?>
		<form method="post" action="" enctype="multipart/form-data">
			<div class="row g-4">
				<!-- Left Column: Form Fields -->
				<div class="col-lg-8">
					<div class="card h-100">
						<div class="card-body">
							<div class="row g-3">
						<!-- Informasi Dasar -->
						<div class="col-12">
							<div class="border-bottom pb-2 mb-3">
								<h6 class="fw-bold mb-0 text-primary">
									<i class="fas fa-user me-2"></i>Informasi Dasar
								</h6>
							</div>
						</div>
						
						<?php
						if($id_company_utama == 0){
						?>
						<div class="col-md-6">
							<label class="form-label">Group</label>
							<?=options(['name' => 'id_company', 'class' => 'form-select'], $tenant, set_value('id_company', $user_edit['id_company']) )?>
						</div>
						<?php
						}
						?>
						
						<div class="col-md-6">
							<label class="form-label">Username</label>
							<?php 
							$readonly = 'readonly="readonly" class="form-control-plaintext"';
							if (@$user_permission['update_all']) {
								$readonly = 'class="form-control"';
							}
							?>
							<input <?=$readonly?> type="text" name="username" id="username" value="<?=set_value('username', @$user_edit['username'])?>" placeholder="" required="required"/>
							<div id="username-validation" class="validation-feedback mt-1"></div>
						</div>
						
						<div class="col-md-6">
							<label class="form-label">Nama</label>
							<input class="form-control" type="text" name="nama" value="<?=set_value('nama', @$user_edit['nama'])?>" placeholder="" required="required"/>
						</div>
						
						<div class="col-md-6">
							<label class="form-label">Email</label>
							<input class="form-control" type="email" id="email" name="email" value="<?=set_value('email', @$user_edit['email'])?>" placeholder="" required="required"/>
							<input type="hidden" name="email_lama" value="<?=set_value('email', @$user_edit['email'])?>" />
							<div id="email-validation" class="validation-feedback mt-1"></div>
						</div>
						<?php
						if (@$user_permission['update_all']) {
							?>
							<!-- Admin Settings -->
							<div class="col-12">
								<div class="border-bottom pb-2 mb-3 mt-4">
									<h6 class="fw-bold mb-0 text-primary">
										<i class="fas fa-cog me-2"></i>Pengaturan Admin
									</h6>
								</div>
							</div>

							<div class="col-md-6">
								<label class="form-label">Verified</label>
								<?php
								if (!isset($user_edit['verified']) && !key_exists('verified', $request->getPost() ?? []) ) {
									$selected = 1;
								} else {
									$selected = set_value('verified', @$user_edit['verified']);
								}
								?>
								<?php echo options(['name' => 'verified', 'class' => 'form-select'], [1=>'Ya', 0 => 'Tidak'], $selected); ?>
							</div>

							<div class="col-md-6">
								<label class="form-label">Status</label>
								<?php echo options(['name' => 'status', 'class' => 'form-select'], [1 => 'Aktif', 2 => 'Suspended', 3 => 'Deleted'], set_value('status', @$user_edit['status'])); ?>
							</div>

							<div class="col-md-6">
								<label class="form-label">Role</label>
								<?php
								foreach ($roles as $key => $val) {
									$options[$val['id_role']] = $val['judul_role'];
								}

								if (!empty($user_edit['role'])) {
									foreach ($user_edit['role'] as $val) {
										$id_role_selected[] = $val['id_role'];
									}
								}

								echo options(['name' => 'id_role[]', 'multiple' => 'multiple', 'class' => 'form-select'], $options, set_value('id_role', @$id_role_selected));
								?>
							</div>

							<div class="col-md-6">
								<label class="form-label">Data Akses</label>
								<?php
								foreach ($tenant_access as $key => $val) {
									$options_tenant[$val['id_company']] = $val['nama_company'];
								}

								$spliter_access = explode(",",@$user_edit['access_company']);

								if (!empty($spliter_access)) {
									foreach ($spliter_access as $val_split) {
										$id_access_selected[] = $val_split;
									}
								}

								echo options(['name' => 'access_company[]', 'multiple' => 'multiple', 'class' => 'form-select'], $options_tenant, set_value('access_company', @$id_access_selected));
								?>
							</div>

							<div class="col-12">
								<label class="form-label">Halaman Default</label>
								<?php
								foreach ($list_module as $val) {
									$options[$val['id_module']] = $val['nama_module'] . ' - ' . $val['judul_module'];
								}
								if (empty($user_edit) && !$request->getPost()) {
									$selected = $setting_registrasi['id_module'];
								} else {
									$selected = set_value('id_module', @$user_edit['id_module']);
								}
								echo options(['name' => 'id_module', 'class' => 'form-select'], $options, set_value('id_module', $selected));
								?>
								<small class="text-muted">Pastikan user memiliki hak akses ke module</small>
							</div>
						<?php
						}
						?>

						<!-- Password Section -->
						<div class="col-12">
							<div class="border-bottom pb-2 mb-3 mt-4">
								<h6 class="fw-bold mb-0 text-primary">
									<i class="fas fa-lock me-2"></i>Password
								</h6>
							</div>
						</div>

						<div class="col-md-6">
							<label class="form-label">Password Baru</label>
							<?php
							$required = empty($user_edit['id_user']) ? 'required="required"' : '';
							?>
							<input class="form-control" type="password" id="password" name="password" <?=$required?>/>
							<div id="password-strength" class="validation-feedback mt-1"></div>
							<small class="text-muted">Minimal 6 karakter untuk kemudahan penggunaan</small>
						</div>

						<div class="col-md-6">
							<label class="form-label">Ulangi Password Baru</label>
							<input class="form-control" type="password" id="ulangi_password" name="ulangi_password" <?=$required?>/>
							<div id="password-match" class="validation-feedback mt-1"></div>
						</div>
				
						<!-- Submit Button -->
						<div class="col-12 mt-4">
							<div class="d-flex justify-content-end gap-2">
								<a href="<?=$module_url?>" class="btn btn-outline-secondary">
									<i class="fas fa-arrow-left me-2"></i>Batal
								</a>
								<button type="submit" name="submit" value="submit" class="btn btn-primary">
									<i class="fas fa-save me-2"></i>Simpan
								</button>
							</div>
							<input type="hidden" name="id" value="<?=@$user_edit['id_user']?>"/>
						</div>
							</div>
						</div>
					</div>
				</div>

		<!-- Right Column: Photo -->
		<div class="col-lg-4">
			<div class="card h-100">
				<div class="card-body text-center d-flex flex-column">
					<div class="border-bottom pb-2 mb-4">
						<h6 class="fw-bold mb-0 text-primary">
							<i class="fas fa-camera me-2"></i>Foto Profil
						</h6>
					</div>

					<!-- Avatar Preview -->
					<div class="mb-4 flex-grow-1 d-flex align-items-center justify-content-center">
						<?php
						$avatar = @$_FILES['file']['name'] ?: @$user_edit['avatar'];
						if (!empty($avatar)) {
							echo '<div class="position-relative d-inline-block">
									<img src="'.$config->baseURL.'/public/images/user/'.$avatar.'?r='.time().'" class="rounded-circle shadow-sm" style="width: 140px; height: 140px; object-fit: cover; border: 4px solid #e9ecef;"/>
									<button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 rounded-circle shadow" onclick="removeImage()" title="Hapus Foto">
										<i class="fas fa-times"></i>
									</button>
								</div>';
						} else {
							echo '<div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 140px; height: 140px; border: 2px dashed #dee2e6;">
									<i class="fas fa-user text-muted" style="font-size: 3rem;"></i>
								</div>';
						}
						?>
					</div>

					<!-- Upload Controls -->
					<div class="mt-auto">
						<input type="hidden" class="avatar-delete-img" name="avatar_delete_img" value="0">
						<input type="file" class="form-control mb-2" name="avatar" accept="image/*">
						<?php if (!empty($form_errors['avatar'])) echo '<div class="text-danger small mb-2">' . $form_errors['avatar'] . '</div>'?>

						<small class="text-muted d-block">
							<i class="fas fa-info-circle me-1"></i>
							Maksimal 300KB, Minimal 100×100px<br>
							Format: JPG, JPEG, PNG
						</small>
						<div class="upload-img-thumb mt-2"><span class="img-prop text-muted small"></span></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
	</div>
</div>

<?php
if ($request->getGet('mobile') == 'true') {
	echo $this->endSection();
}
?>
