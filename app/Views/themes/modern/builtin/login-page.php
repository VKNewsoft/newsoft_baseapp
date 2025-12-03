<!DOCTYPE HTML>
<html lang="en">
<title><?=$site_title?></title>
<meta name="descrition" content="<?=$site_title?>"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="<?=$config->baseURL . 'public/images/'.$setting_aplikasi['favicon'].'?r='.time()?>" />
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/bootstrap/css/bootstrap.min.css?r='.time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/themes/modern/builtin/css/bootstrap-custom.css?r=' . time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/fontawesome/css/all.css?r='.time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/themes/modern/builtin/css/login.css?r='.time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/themes/modern/builtin/css/login-header.css?r='.time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/pace/pace-theme-default.css?r='.time()?>"/>

<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/jquery/jquery.min.js?r='.time()?>"></script>
<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/bootstrap/js/bootstrap.min.js?r='.time()?>"></script>
<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/pace/pace.min.js?r='.time()?>"></script>
<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/bootbox/bootbox.min.js'?>"></script>
<?php
if (!empty($js)) {
	foreach($js as $file) {
		echo '<script type="text/javascript" src="'.$file.'?r='.time().'"></script>';
	}
}

?>
</html>
<body>
	<div class="background"></div>
	<div class="backdrop"></div>
	<div class="login-container">
		<div class="login-header">
			<div class="logo">
				<img src="<?php echo $config->baseURL . '/public/images/' . $settingWeb->logo_login?>">
			</div>
			
			<?php if (!empty($desc)) {
				echo '<p>' . $desc . '</p>';
			}?>
		</div>
		<div class="login-body">
			<?php
			
			if (!empty($message)) {?>
				<div class="alert alert-danger">
					<?=$message?>
				</div>
			<?php }
			//echo password_hash('admin', PASSWORD_DEFAULT);
			?>
			<form method="post" action="" class="form-horizontal form-login">
			<div class="input-group mb-3">
				<div class="input-group-prepend login-input">
					<span class="input-group-text">
						<i class="fa fa-user"></i>
					</span>
				</div>
		
				<input type="text" name="username" value="<?= esc(old('username', '')) ?>" class="form-control login-input" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1" required>
			</div>
			<div class="input-group mb-3">
				<div class="input-group-prepend login-input">
					<span class="input-group-text" id="basic-addon1">
						<i class="fa fa-lock" style="font-size:22px"></i>
					</span>
				</div>
				<input type="password"  name="password" class="form-control login-input" placeholder="Password" aria-label="Password" aria-describedby="basic-addon1" required>
			</div>
			<div class="checkbox mb-3">
				<label style="font-weight:normal"><input name="remember" value="1" type="checkbox">&nbsp;&nbsp;Remember me</label>
			</div>
			<div class="mb-3" style="margin-bottom:7px">
				<button type="submit" id="btn-submit-login" class="form-control btn <?=$settingWeb->btn_login?>" name="submit">Submit</button>
				<?php
					$form_token = $auth->generateFormToken('login_form_token');
				?>
				<?= csrf_formfield() ?>
			</div>
			<div class="login-footer">
				<p>Lupa Password? <a href="<?=$config->baseURL?>recovery">Request reset password</a></p>
				<?php if ($setting_registrasi['enable'] == 'Y') { ?>
					<p>Belum punya akun? <a href="<?=$config->baseURL?>register">Daftar akun</a></p>
				<?php }?>
				<p>Tidak menerima link aktivasi? <a href="<?=$config->baseURL?>register/resendlink">Kirim ulang</a></p>
			</div>
		</div>
		<div class="copyright">
			<?php
				$footer_login = $settingWeb->footer_login ? str_replace('{{YEAR}}', date('Y'), $settingWeb->footer_login) : '';
				echo html_entity_decode($footer_login);
			?>
		</div>
	</div><!-- login container -->
</body>
</html>