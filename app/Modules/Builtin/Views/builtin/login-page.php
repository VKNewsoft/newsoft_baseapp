<!DOCTYPE HTML>
<html lang="en">
<?php
helper('setting_layout');
$faviconUrl = public_image_url($setting_aplikasi['favicon'] ?? '', 'logo_only.png');
$faviconVersion = public_image_version($setting_aplikasi['favicon'] ?? '', 'logo_only.png');
$loginLogoUrl = public_image_url($settingWeb->logo_login ?? '', 'logo_only.png');
$fontSettingValue = $app_layout['font_family'] ?? 'open-sans';
$currentFontKey = setting_layout_font_key($fontSettingValue);
$currentFont = setting_layout_font_entry($fontSettingValue);
$currentFontFamily = $currentFont['family'];
$currentFontCssPath = $currentFont['css_path'];
$fontPreloadFiles = setting_layout_font_preload_files($fontSettingValue);
$criticalFontCss = setting_layout_font_critical_css($fontSettingValue);
$bootstrapVersion = @filemtime(ROOTPATH . 'public/vendors/bootstrap/css/bootstrap.min.css');
$bootstrapCustomVersion = @filemtime(APPPATH . 'Modules/Common/Assets/builtin/css/bootstrap-custom.css');
$fontawesomeVersion = @filemtime(ROOTPATH . 'public/vendors/fontawesome/css/all.css');
$loginCssVersion = @filemtime(APPPATH . 'Modules/Common/Assets/builtin/css/login.css');
$loginHeaderCssVersion = @filemtime(APPPATH . 'Modules/Common/Assets/builtin/css/login-header.css');
$paceCssVersion = @filemtime(ROOTPATH . 'public/vendors/pace/pace-theme-default.css');
$jqueryVersion = @filemtime(ROOTPATH . 'public/vendors/jquery/jquery.min.js');
$bootstrapJsVersion = @filemtime(ROOTPATH . 'public/vendors/bootstrap/js/bootstrap.min.js');
$paceJsVersion = @filemtime(ROOTPATH . 'public/vendors/pace/pace.min.js');
$fontAssetVersion = @filemtime(APPPATH . 'Modules/Common/Assets/builtin/' . $currentFontCssPath);
?>
<head>
	<title><?=$site_title?></title>
	<meta name="descrition" content="<?=$site_title?>"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<style>:root{--app-font-family: <?=$currentFontFamily?>;}html,body{font-family:var(--app-font-family);}<?=$criticalFontCss?></style>
	<?php foreach ($fontPreloadFiles as $fontFile): ?>
	<link rel="preload" as="font" type="font/woff2" crossorigin fetchpriority="high" href="<?=$config->baseURL . 'module-assets/Common/builtin/fonts/'.$fontFile['file'].'?v='.@filemtime(APPPATH . 'Modules/Common/Assets/builtin/fonts/'.$fontFile['file'])?>" />
	<?php endforeach; ?>
	<link rel="preload" as="style" fetchpriority="high" href="<?=$config->baseURL . 'module-assets/Common/builtin/'.$currentFontCssPath.'?v='.$fontAssetVersion?>" />
	<link rel="stylesheet" id="font-switch" data-font-key="<?=esc($currentFontKey, 'attr')?>" type="text/css" href="<?=$config->baseURL . 'module-assets/Common/builtin/'.$currentFontCssPath.'?v='.$fontAssetVersion?>"/>
	<link rel="shortcut icon" href="<?=$faviconUrl . '?v=' . $faviconVersion?>" />
	<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/bootstrap/css/bootstrap.min.css?v='.$bootstrapVersion?>"/>
	<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'module-assets/Common/builtin/css/bootstrap-custom.css?v=' . $bootstrapCustomVersion?>"/>
	<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/fontawesome/css/all.css?v='.$fontawesomeVersion?>"/>
	<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'module-assets/Common/builtin/css/login.css?v='.$loginCssVersion?>"/>
	<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'module-assets/Common/builtin/css/login-header.css?v='.$loginHeaderCssVersion?>"/>
	<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/pace/pace-theme-default.css?v='.$paceCssVersion?>"/>

	<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/jquery/jquery.min.js?v='.$jqueryVersion?>"></script>
	<script defer type="text/javascript" src="<?=$config->baseURL . 'public/vendors/bootstrap/js/bootstrap.min.js?v='.$bootstrapJsVersion?>"></script>
	<script defer type="text/javascript" src="<?=$config->baseURL . 'public/vendors/pace/pace.min.js?v='.$paceJsVersion?>"></script>
	<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/bootbox/bootbox.min.js'?>"></script>
	<?php
	if (!empty($js)) {
		foreach($js as $file) {
			echo '<script defer type="text/javascript" src="'.$file.'"></script>';
		}
	}
	?>
</head>
<body style="font-family: <?=esc($currentFontFamily, 'attr')?>;">
	<div class="background"></div>
	<div class="backdrop"></div>
	<div class="login-container">
		<div class="login-header">
			<div class="logo">
				<img src="<?=$loginLogoUrl?>" alt="Logo Login">
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
</html>
