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
							<div id="username-validation" class="mt-1"></div>
						</div>
						
						<div class="col-md-6">
							<label class="form-label">Nama</label>
							<input class="form-control" type="text" name="nama" value="<?=set_value('nama', @$user_edit['nama'])?>" placeholder="" required="required"/>
						</div>
						
						<div class="col-md-6">
							<label class="form-label">Email</label>
							<input class="form-control" type="email" id="email" name="email" value="<?=set_value('email', @$user_edit['email'])?>" placeholder="" required="required"/>
							<input type="hidden" name="email_lama" value="<?=set_value('email', @$user_edit['email'])?>" />
							<div id="email-validation" class="mt-1"></div>
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
							<div id="password-strength" class="mt-1"></div>
							<small class="text-muted">Minimal 6 karakter untuk kemudahan penggunaan</small>
						</div>

						<div class="col-md-6">
							<label class="form-label">Ulangi Password Baru</label>
							<input class="form-control" type="password" id="ulangi_password" name="ulangi_password" <?=$required?>/>
							<div id="password-match" class="mt-1"></div>
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

<script>
function removeImage() {
	if (confirm('Apakah Anda yakin ingin menghapus foto profil?')) {
		$('.avatar-delete-img').val(1);
		$('input[name="avatar"]').val('');
		location.reload();
	}
}

// Password strength checker - Simplified for initial user creation
function checkPasswordStrength(password) {
	let strength = 0;
	let feedback = [];

	// Very basic validation - just minimum length
	if (password.length >= 6) {
		strength = 5; // Always show as strong for initial users
	} else if (password.length >= 4) {
		strength = 3; // Medium for 4-5 characters
	} else if (password.length > 0) {
		strength = 1; // Weak for 1-3 characters
		feedback.push('Minimal 6 karakter');
	}

	return { strength, feedback };
}

// Real-time validation for username and email
$(document).ready(function() {
	// Username validation
	$('#username').on('input', function() {
		validateUsername();
	});

	$('#username').on('blur', function() {
		checkUsernameUniqueness();
	});

	// Email validation
	$('#email').on('input', function() {
		validateEmail();
	});

	$('#email').on('blur', function() {
		checkEmailUniqueness();
	});

	$('#password').on('input', function() {
		const password = $(this).val();
		const result = checkPasswordStrength(password);
		let strengthText = '';
		let strengthClass = '';
		let progressWidth = '0%';
		let progressClass = 'bg-secondary';

		if (password.length === 0) {
			strengthText = '';
			strengthClass = '';
		} else {
			progressWidth = (result.strength / 5 * 100) + '%';

			if (result.strength < 2) {
				strengthText = 'Sangat Lemah';
				strengthClass = 'text-danger';
				progressClass = 'bg-danger';
			} else if (result.strength < 4) {
				strengthText = 'Cukup';
				strengthClass = 'text-warning';
				progressClass = 'bg-warning';
			} else {
				strengthText = 'Baik';
				strengthClass = 'text-success';
				progressClass = 'bg-success';
			}

			// Show progress bar
			strengthText += `<div class="progress mt-1" style="height: 4px;">
				<div class="progress-bar ${progressClass}" style="width: ${progressWidth}"></div>
			</div>`;
		}

		$('#password-strength').html(strengthText).attr('class', 'mt-1 ' + strengthClass);
		checkPasswordMatch();
	});

	$('#ulangi_password').on('input', function() {
		checkPasswordMatch();
	});

	function checkPasswordMatch() {
		const password = $('#password').val();
		const confirmPassword = $('#ulangi_password').val();
		const matchDiv = $('#password-match');
		$('button[name="submit"]').addClass('d-none');

		if (confirmPassword.length === 0) {
			matchDiv.html('').attr('class', 'mt-1');
			return;
		}

		if (password === confirmPassword) {
			matchDiv.html('<i class="fas fa-check-circle me-1"></i>Password cocok').attr('class', 'mt-1 text-success small');
			$('button[name="submit"]').removeClass('d-none');
		} else {
			matchDiv.html('<i class="fas fa-times-circle me-1"></i>Password tidak cocok').attr('class', 'mt-1 text-danger small');
		}
	}

	function validateUsername() {
		const username = $('#username').val();
		const usernameDiv = $('#username-validation');
		$('button[name="submit"]').addClass('d-none');

		// Clear previous validation
		usernameDiv.html('').attr('class', 'mt-1');

		if (username.length === 0) return;

		// Check format (alphanumeric, underscore, dash, min 3 chars)
		const usernameRegex = /^[a-zA-Z0-9_-]{3,}$/;
		
		if (!usernameRegex.test(username)) {
			if (username.length < 3) {
				usernameDiv.html('<i class="fas fa-times-circle me-1"></i>Minimal 3 karakter').attr('class', 'mt-1 text-danger small');
				$('button[name="submit"]').addClass('d-none');
			} else {
				usernameDiv.html('<i class="fas fa-times-circle me-1"></i>Hanya huruf, angka, underscore (_), dan dash (-)').attr('class', 'mt-1 text-danger small');
				$('button[name="submit"]').addClass('d-none');
			}
			return false;
		} else {
			usernameDiv.html('<i class="fas fa-check-circle me-1"></i>Format username valid').attr('class', 'mt-1 text-success small');
			$('button[name="submit"]').removeClass('d-none');
			return true;
		}
	}

	function checkUsernameUniqueness() {
		const username = $('#username').val();
		const usernameDiv = $('#username-validation');
		const currentUsername = '<?=@$user_edit['username']?>'; // Username saat ini
		const isEdit = $('input[name="id"]').val() > 0;
		$('button[name="submit"]').addClass('d-none');

		if (!validateUsername() || username.length === 0) return;

		// Jika sedang edit dan username sama dengan yang lama, tidak perlu check
		if (isEdit && username === currentUsername) {
			usernameDiv.html('<i class="fas fa-check-circle me-1"></i>Username valid').attr('class', 'mt-1 text-success small');
			$('button[name="submit"]').removeClass('d-none');
			return;
		}

		// Show loading
		usernameDiv.html('<i class="fas fa-spinner fa-spin me-1"></i>Mengecek ketersediaan...').attr('class', 'mt-1 text-info small');

		// AJAX call to check uniqueness
		$.ajax({
			url: '<?=$config->baseURL?>builtin/user/ajaxCheckUsername',
			type: 'POST',
			data: {
				username: username,
				id_user: $('input[name="id"]').val() || 0
			},
			success: function(response) {
				try {
					const result = JSON.parse(response);
					if (result.available) {
						usernameDiv.html('<i class="fas fa-check-circle me-1"></i>Username tersedia').attr('class', 'mt-1 text-success small');
						$('button[name="submit"]').removeClass('d-none');
					} else {
						usernameDiv.html('<i class="fas fa-times-circle me-1"></i>Username sudah digunakan').attr('class', 'mt-1 text-danger small');
						$('button[name="submit"]').addClass('d-none');
					}
				} catch (e) {
					usernameDiv.html('<i class="fas fa-exclamation-triangle me-1"></i>Gagal memeriksa username').attr('class', 'mt-1 text-warning small');
					$('button[name="submit"]').addClass('d-none');
				}
			},
			error: function() {
				usernameDiv.html('<i class="fas fa-exclamation-triangle me-1"></i>Gagal memeriksa username').attr('class', 'mt-1 text-warning small');
				$('button[name="submit"]').addClass('d-none');
			}
		});
	}

	function validateEmail() {
		const email = $('#email').val();
		const emailDiv = $('#email-validation');

		// Clear previous validation
		emailDiv.html('').attr('class', 'mt-1');

		if (email.length === 0) return;

		// Check email format
		const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

		if (!emailRegex.test(email)) {
			emailDiv.html('<i class="fas fa-times-circle me-1"></i>Format email tidak valid').attr('class', 'mt-1 text-danger small');
			$('button[name="submit"]').addClass('d-none');
			return false;
		} else {
			emailDiv.html('<i class="fas fa-check-circle me-1"></i>Format email valid').attr('class', 'mt-1 text-success small');
			$('button[name="submit"]').removeClass('d-none');
			return true;
		}
	}

	function checkEmailUniqueness() {
		const email = $('#email').val();
		const emailDiv = $('#email-validation');
		const currentEmail = '<?=@$user_edit['email']?>'; // Email saat ini
		const isEdit = $('input[name="id"]').val() > 0;
		$('button[name="submit"]').addClass('d-none');

		if (!validateEmail() || email.length === 0) return;

		// Jika sedang edit dan email sama dengan yang lama, tidak perlu check
		if (isEdit && email === currentEmail) {
			emailDiv.html('<i class="fas fa-check-circle me-1"></i>Email valid').attr('class', 'mt-1 text-success small');
			$('button[name="submit"]').removeClass('d-none');
			return;
		}

		// Show loading
		emailDiv.html('<i class="fas fa-spinner fa-spin me-1"></i>Mengecek ketersediaan...').attr('class', 'mt-1 text-info small');

		// AJAX call to check uniqueness
		$.ajax({
			url: '<?=$config->baseURL?>builtin/user/ajaxCheckEmail',
			type: 'POST',
			data: {
				email: email,
				email_lama: $('input[name="email_lama"]').val() || ''
			},
			success: function(response) {
				try {
					const result = JSON.parse(response);
					if (result.available) {
						emailDiv.html('<i class="fas fa-check-circle me-1"></i>Email tersedia').attr('class', 'mt-1 text-success small');
						$('button[name="submit"]').removeClass('d-none');
					} else {
						emailDiv.html('<i class="fas fa-times-circle me-1"></i>Email sudah digunakan').attr('class', 'mt-1 text-danger small');
						$('button[name="submit"]').addClass('d-none');
					}
				} catch (e) {
					emailDiv.html('<i class="fas fa-exclamation-triangle me-1"></i>Gagal memeriksa email').attr('class', 'mt-1 text-warning small');
					$('button[name="submit"]').addClass('d-none');
				}
			},
			error: function() {
				emailDiv.html('<i class="fas fa-exclamation-triangle me-1"></i>Gagal memeriksa email').attr('class', 'mt-1 text-warning small');
				$('button[name="submit"]').addClass('d-none');
			}
		});
	}
});
</script>

<?php
if ($request->getGet('mobile') == 'true') {
	echo $this->endSection();
}
?>