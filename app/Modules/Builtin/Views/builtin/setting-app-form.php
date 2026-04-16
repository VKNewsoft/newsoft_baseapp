<div class="card setting-layout-card setting-app-card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<?php 
		helper ('html');
		if (!empty($message)) {
			show_message($message);
		}
		
		$list = ['background_logo', 'btn_login', 'deskripsi_web'];
		foreach ($list as $val) {
			if (empty($$val)) {
				$$val = '';
			}
		}

		$buttonList = ['btn-primary', 'btn-secondary', 'btn-success', 'btn-danger', 'btn-warning', 'btn-info', 'btn-light', 'btn-dark'];
		?>
		<form method="post" action="" id="form-setting" enctype="multipart/form-data" class="form-shell">
			<div class="tab-content setting-app-shell">
				<div class="form-shell-section page-defer-section">
					<div class="form-shell-section-title">
						<h5>Brand Assets</h5>
					</div>
					<div class="row g-4">
						<div class="col-md-6">
							<div class="setting-app-field">
								<label class="form-label">Logo Aplikasi</label>
								<div class="setting-app-upload">
									<?php
									if (!empty($logo_app) && file_exists($config->imagesPath . $logo_app)) {
										echo '<div class="setting-app-preview-wrap">
											<button type="button" class="setting-app-current-image setting-app-preview-trigger" data-preview-image="' . $config->imagesURL . $logo_app . '?r='.time().'" data-preview-title="Logo Aplikasi"><img src="' . $config->imagesURL . $logo_app . '?r='.time().'"/></button>
											<button type="button" class="setting-app-remove-btn" data-target-input="logo_app_delete_img" data-target-file="logo_app" title="Hapus Logo Aplikasi"><i class="fas fa-times"></i></button>
										</div>';
									}
									?>
									<input type="hidden" name="logo_app_delete_img" value="0">
									<input type="file" class="file form-control" name="logo_app">
									<?php if (!empty($form_errors['logo_app'])) echo '<small class="alert alert-danger d-block mb-0">' . $form_errors['logo_app'] . '</small>'?>
									<small class="form-text text-muted"><strong>Gunakan file PNG transparan</strong>. Maksimal 300Kb, Minimal 50px x 50px, Tipe file: .JPG, .JPEG, .PNG</small>
									<div class="upload-img-thumb"><span class="img-prop"></span></div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="setting-app-field">
								<label class="form-label">Fav Icon</label>
								<div class="setting-app-upload">
									<?php
									if (!empty($favicon) && file_exists($config->imagesPath . $favicon)) {
										echo '<div class="setting-app-preview-wrap">
											<button type="button" class="setting-app-current-image setting-app-current-image-sm setting-app-preview-trigger" data-preview-image="'. $config->imagesURL . $favicon . '?r='.time().'" data-preview-title="Fav Icon"><img src="'. $config->imagesURL . $favicon . '?r='.time().'"/></button>
											<button type="button" class="setting-app-remove-btn" data-target-input="favicon_delete_img" data-target-file="favicon" title="Hapus Fav Icon"><i class="fas fa-times"></i></button>
										</div>';
									}
									?>
									<input type="hidden" name="favicon_delete_img" value="0">
									<input type="file" class="file form-control" name="favicon">
									<?php if (!empty($form_errors['favicon'])) echo '<small class="alert alert-danger d-block mb-0">' . $form_errors['favicon'] . '</small>'?>
									<small class="form-text text-muted"><strong>Gunakan file PNG transparan, width dan height sama, misal: 64px x 64px</strong></small>
									<div class="upload-img-thumb"><span class="img-prop"></span></div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="setting-app-field">
								<label class="form-label">Logo Login</label>
								<div class="setting-app-upload">
									<?php
									if (!empty($logo_login) && file_exists($config->imagesPath . $logo_login)) {
										echo '<div class="setting-app-preview-wrap">
											<button type="button" class="setting-app-current-image edit-logo-login-container setting-app-preview-trigger" data-preview-image="'. $config->imagesURL . $logo_login . '?r='.time().'" data-preview-title="Logo Login"><img src="'. $config->imagesURL . $logo_login . '?r='.time().'"/></button>
											<button type="button" class="setting-app-remove-btn" data-target-input="logo_login_delete_img" data-target-file="logo_login" title="Hapus Logo Login"><i class="fas fa-times"></i></button>
										</div>';
									}
									?>
									<input type="hidden" name="logo_login_delete_img" value="0">
									<input type="file" class="file form-control" name="logo_login">
									<?php if (!empty($form_errors['logo_login'])) echo '<small class="alert alert-danger d-block mb-0">' . $form_errors['logo_login'] . '</small>'?>
									<small class="form-text text-muted"><strong>Gunakan file PNG transparan</strong>. Maksimal 300Kb, tipe file: .JPG, .JPEG, .PNG</small>
									<div class="upload-img-thumb"><span class="img-prop"></span></div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="setting-app-field">
								<label class="form-label">Logo Form Registrasi</label>
								<div class="setting-app-upload">
									<?php
									if (!empty($logo_register) && file_exists($config->imagesPath . $logo_register)) {
										echo '<div class="setting-app-preview-wrap">
											<button type="button" class="setting-app-current-image setting-app-preview-trigger" data-preview-image="'. $config->imagesURL . $logo_register . '" data-preview-title="Logo Form Registrasi"><img src="'. $config->imagesURL . $logo_register . '"/></button>
											<button type="button" class="setting-app-remove-btn" data-target-input="logo_register_delete_img" data-target-file="logo_register" title="Hapus Logo Form Registrasi"><i class="fas fa-times"></i></button>
										</div>';
									}
									?>
									<input type="hidden" name="logo_register_delete_img" value="0">
									<input type="file" class="file form-control" name="logo_register">
									<?php if (!empty($form_errors['logo_register'])) echo '<small class="alert alert-danger d-block mb-0">' . $form_errors['logo_register'] . '</small>'?>
									<small class="form-text text-muted"><strong>Gunakan file PNG transparan</strong>. Maksimal 300Kb, Minimal 50px x 50px, Tipe file: .JPG, .JPEG, .PNG</small>
									<div class="upload-img-thumb"><div class="img-prop"></div></div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="setting-app-field">
								<label class="form-label">Background Logo</label>
								<input name="background_logo" class="form-control colorpicker" value="<?=set_value('background_logo', @$background_logo)?>" />
							</div>
						</div>
						<div class="col-xl-6">
							<div class="theme-preview-surface setting-app-note">
								<span class="setting-layout-preview-badge">Info</span>
								<h6>Identitas Visual</h6>
								<p>Kumpulkan semua aset brand dalam satu tempat agar pengelolaan tampilan aplikasi lebih cepat dan konsisten.</p>
								<ul class="setting-app-summary">
									<li>Logo aplikasi & favicon</li>
									<li>Logo login & register</li>
									<li>Background logo utama</li>
								</ul>
							</div>
						</div>
					</div>
				</div>

				<div class="form-shell-section page-defer-section">
					<div class="form-shell-section-title">
						<h5>Text Content</h5>
					</div>
					<div class="row g-4">
						<div class="col-md-6">
							<div class="setting-app-field">
								<label class="form-label">Judul Web</label>
								<textarea class="form-control" name="judul_web" rows="3"><?=set_value('judul_web', @$judul_web)?></textarea>
							</div>
						</div>
						<div class="col-md-6">
							<div class="setting-app-field">
								<label class="form-label">Deskripsi Web</label>
								<textarea class="form-control" name="deskripsi_web" rows="3"><?=set_value('deskripsi_web', @$deskripsi_web)?></textarea>
							</div>
						</div>
						<div class="col-md-6">
							<div class="setting-app-field">
								<label class="form-label">Footer Login</label>
								<textarea class="form-control" name="footer_login" rows="3"><?=set_value('footer_login', @$footer_login)?></textarea>
							</div>
						</div>
						<div class="col-md-6">
							<div class="setting-app-field">
								<label class="form-label">Footer Aplikasi</label>
								<textarea class="form-control" name="footer_app" rows="3"><?=set_value('footer_app', @$footer_app)?></textarea>
							</div>
						</div>
						<div class="col-md-6">
							<div class="setting-app-field">
								<label class="form-label">Button Login</label>
								<div class="setting-app-button-grid list-btn-login">
									<?php
									foreach ($buttonList as $val) {
										$check = @$btn_login == $val ? '<i class="fa fa-check check"></i>' : ''; 
										echo '<a data-class="'. $val . '" href="javascript:void(0)" class="theme-btn-login btn '.$val.'">' . $check . '</a>';
									}
									?>	
								</div>
								<input type="hidden" name="btn_login" value="<?=set_value('btn_login', @$btn_login)?>">
							</div>
						</div>
					</div>
				</div>

				<div class="sticky-form-actions">
					<div class="d-flex justify-content-end">
						<button type="submit" name="submit" id="btn-submit" value="submit" class="btn btn-primary px-4">Submit</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<div class="modal fade" id="settingAppImagePreviewModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-xl setting-app-preview-modal">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="settingAppImagePreviewTitle">Preview Gambar</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="setting-app-preview-stage" id="settingAppPreviewStage">
					<img src="" alt="Preview" id="settingAppPreviewImage">
				</div>
				<div class="setting-app-preview-hint">Gerakkan cursor di atas gambar untuk zoom detail.</div>
			</div>
		</div>
	</div>
</div>
