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
		
		// Menghindari error pada set_value
		$list = ['background_logo', 'btn_login', 'deskripsi_web'];
		foreach ($list as $val) {
			if (empty($$val)) {
				$$val = '';
			}
		}

		?>
		<form method="post" action="" id="form-setting" enctype="multipart/form-data">
			<div class="tab-content">
				<div class="bg-lightgrey p-3 ps-4">
				<h5>Login</h5>
				</div>
				<hr/>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Logo Login</label>
					<div class="col-sm-5">
						<?php
						if (!empty($logo_login) && file_exists($config->imagesPath . $logo_login))
						echo '<div class="edit-logo-login-container"><img src="'. $config->imagesURL . $logo_login . '?r='.time().'"/></div>';
						
						?>
						<input type="file" class="file form-control" name="logo_login">
							<?php if (!empty($form_errors['logo_login'])) echo '<small class="alert alert-danger">' . $form_errors['logo_login'] . '</small>'?>
							<small class="form-text text-muted"><strong>Gunakan file PNG transparan</strong>. Maksimal 300Kb, tipe file: .JPG, .JPEG, .PNG</small>
						<div class="upload-img-thumb"><span class="img-prop"></span></div>
					</div>
					
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Background Logo</label>
					<div class="col-sm-5 form-inline">
						<input name="background_logo" class="form-control colorpicker" value="<?=set_value('background_logo', @$background_logo)?>" />
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Button</label>
					<div class="col-sm-5">
						<ul class="list-inline list-btn-login">
							<?php
							$list = ['btn-primary', 'btn-secondary', 'btn-success', 'btn-danger', 'btn-warning', 'btn-info', 'btn-light', 'btn-dark'];
							foreach ($list as $val) {
								$check = @$btn_login == $val ? '<i class="fa fa-check check"></i>' : ''; 
								echo '<li class="list-inline-item"><a data-class="'. $val . '" href="javascript:void(0)" class="theme-btn-login btn '.$val.'">' . $check . '</a></li>';
							}
							?>	
						</ul>
						<input type="hidden" name="btn_login" value="<?=set_value('btn_login', @$btn_login)?>">
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Footer</label>
					<div class="col-sm-5">
						<textarea class="form-control" name="footer_login"><?=set_value('footer_login', @$footer_login)?></textarea>
					</div>
				</div>
				<div class="bg-lightgrey p-3 mt-5 ps-4">
				<h5>Website</h5>
				</div>
				<hr/>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Judul Web</label>
					<div class="col-sm-5">
						<textarea class="form-control" name="judul_web"><?=set_value('judul_web', @$judul_web)?></textarea>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Deskripsi Web</label>
					<div class="col-sm-5">
						<textarea class="form-control" name="deskripsi_web"><?=set_value('deskripsi_web', @$deskripsi_web)?></textarea>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Fav Icon</label>
					<div class="col-sm-5">
						<?php
						if (!empty($favicon) && file_exists($config->imagesPath . $favicon))
						echo '<div style="margin:inherit;margin-bottom:10px"><img src="'. $config->imagesURL . $favicon . '?r='.time().'"/></div>';
						
						?>
						<input type="file" class="file form-control" name="favicon">
							<?php if (!empty($form_errors['favicon'])) echo '<small class="alert alert-danger">' . $form_errors['favicon'] . '</small>'?>
							<small class="form-text text-muted"><strong>Gunakan file PNG transparan, width dan height sama, misal: 64px x 64px</strong></small>
						<div class="upload-img-thumb"><span class="img-prop"></span></div>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Logo Aplikasi</label>
					<div class="col-sm-5">
						<?php
						if (!empty($logo_app) && file_exists($config->imagesPath . $logo_app))
						echo '<div style="margin:inherit;margin-bottom:10px"><img src="' . $config->imagesURL . $logo_app . '?r='.time().'"/></div>';
						
						?>
						<input type="file" class="file form-control" name="logo_app">
							<?php if (!empty($form_errors['logo_app'])) echo '<small class="alert alert-danger">' . $form_errors['logo_app'] . '</small>'?>
							<small class="form-text text-muted"><strong>Gunakan file PNG transparan</strong>. Maksimal 300Kb, Minimal 50px x 50px, Tipe file: .JPG, .JPEG, .PNG</small>
						<div class="upload-img-thumb"><span class="img-prop"></span></div>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Background Logo</label>
					<div class="col-sm-5">
						Ubah di menu setting tampilan
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Footer</label>
					<div class="col-sm-5">
						<textarea class="form-control" name="footer_app"><?=set_value('footer_app', @$footer_app)?></textarea>
					</div>
				</div>
				<div class="bg-lightgrey p-3 ps-4">
				<h5>Register</h5>
				</div>
				<hr/>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Logo Form Registrasi</label>
					<div class="col-sm-5">
						<?php
						if (!empty($logo_register) && file_exists($config->imagesPath . $logo_register))
						echo '<div style="margin:inherit;margin-bottom:10px"><img src="'. $config->imagesURL . $logo_register . '"/></div>';
						
						?>
						<input type="file" class="file form-control" name="logo_register">
							<?php if (!empty($form_errors['logo_register'])) echo '<small class="alert alert-danger">' . $form_errors['logo_register'] . '</small>'?>
							<small class="form-text text-muted"><strong>Gunakan file PNG transparan</strong>. Maksimal 300Kb, Minimal 50px x 50px, Tipe file: .JPG, .JPEG, .PNG</small>
						<div class="upload-img-thumb"><div class="img-prop"></div></div>
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