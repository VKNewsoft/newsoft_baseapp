<?= $this->extend('themes/modern/register/layout') ?>
<?= $this->section('content') ?>
<div class="login-glass-container">
	<div class="login-header">
		<div class="logo-wrapper">
			<img src="<?php echo $config->baseURL . '/public/images/' . $setting_aplikasi['logo_login']?>" alt="Logo" class="login-logo">
		</div>
		<?php if (!empty($desc)) {
			echo '<p class="login-subtitle">' . $desc . '</p>';
		}?>
	</div>
	<div class="login-body">
		<?php
		
		if (!empty($message)) {?>
			<div class="alert alert-danger modern-alert">
				<i class="fa fa-exclamation-circle"></i> <?=$message?>
			</div>
		<?php }
		?>
		<div class="login-form-wrapper">
			<form method="post" action="" class="modern-login-form" novalidate>
				<div class="form-field">
					<label for="username-field" class="field-label">
						<i class="fa fa-user"></i> Username
					</label>
					<input type="text" id="username-field" name="username" value="<?= esc(old('username', '')) ?>" class="modern-input" placeholder="Masukkan username" aria-label="Username" required>
				</div>
				<div class="form-field">
					<label for="password-field" class="field-label">
						<i class="fa fa-lock"></i> Password
					</label>
					<div class="password-input-wrapper">
						<input id="password-field" type="password" name="password" class="modern-input" placeholder="Masukkan password" aria-label="Password" required>
						<button type="button" class="password-toggle-btn" aria-pressed="false" aria-label="Tampilkan password" title="Tampilkan / Sembunyikan password">
							<i class="fa fa-eye" aria-hidden="true"></i>
						</button>
					</div>
				</div>

				<div class="form-actions">
					<button id="btn-submit-login" type="submit" class="btn-login-primary" name="submit">
						<span>Login</span>
						<i class="fa fa-arrow-right"></i>
					</button>
					<?php
						$form_token = $auth->generateFormToken('login_form_token');
					?>
					<?= csrf_formfield() ?>
				</div>
				<div class="login-links">
					<a href="<?=$config->baseURL?>recovery" class="link-recovery">
						<i class="fa fa-key"></i> Lupa Password?
					</a>
					<?php if ($setting_registrasi['enable'] == 'Y') { ?>
						<a href="<?=$config->baseURL?>register" class="link-register">
							<i class="fa fa-user-plus"></i> Daftar Akun
						</a>
					<?php }?>
				</div>
			</form>
		</div>
	</div>
</div>
<style>
/* Clean Modern Login - Light Theme */
:root {
	--login-primary: #4e73df;
	--login-primary-hover: #2e59d9;
	--login-text: #212529;
	--login-muted: #6c757d;
	--login-border: #e3e6f0;
	--login-bg: #ffffff;
}

.login-glass-container {
	background: #ffffff;
	border: 1px solid var(--login-border);
	border-radius: 12px;
	padding: 40px 32px;
	box-shadow: 0 0 20px rgba(0,0,0,0.08);
	position: relative;
	max-width: 400px;
}

.login-header {
	text-align: center;
	margin-bottom: 30px;
	position: relative;
}

.logo-wrapper {
	margin-bottom: 16px;
}

.login-logo {
	max-height: 80px;
	width: auto;
}

.login-title {
	font-size: 26px;
	font-weight: 700;
	color: var(--login-text);
	margin: 0 0 8px 0;
	letter-spacing: 0.3px;
}

.login-subtitle {
	color: var(--login-muted);
	font-size: 14px;
	margin: 0;
}

.login-body {
	position: relative;
}

.modern-alert {
	background: #f8d7da;
	border: 1px solid #f5c2c7;
	border-radius: 8px;
	color: #842029;
	padding: 12px 16px;
	margin-bottom: 20px;
	font-size: 14px;
}

.modern-alert i {
	margin-right: 8px;
}

.login-form-wrapper {
	position: relative;
}

.modern-login-form {
	display: flex;
	flex-direction: column;
	gap: 18px;
}

.form-field {
	display: flex;
	flex-direction: column;
	gap: 6px;
}

.field-label {
	color: var(--login-text);
	font-size: 13px;
	font-weight: 600;
	letter-spacing: 0.3px;
}

.field-label i {
	margin-right: 6px;
	color: var(--login-primary);
}

.modern-input {
	background: #ffffff;
	border: 1px solid var(--login-border);
	border-radius: 8px;
	padding: 12px 14px;
	color: var(--login-text);
	font-size: 15px;
	transition: all 0.2s ease;
	outline: none;
}

.modern-input::placeholder {
	color: #adb5bd;
}

.modern-input:focus {
	border-color: var(--login-primary);
	box-shadow: 0 0 0 3px rgba(78,115,223,0.1);
}

.password-input-wrapper {
	position: relative;
	display: flex;
	align-items: center;
}

.password-toggle-btn {
	position: absolute;
	right: 12px;
	background: transparent;
	border: none;
	color: var(--login-muted);
	font-size: 16px;
	cursor: pointer;
	padding: 6px;
	transition: color 0.2s;
}

.password-toggle-btn:hover {
	color: var(--login-primary);
}

.form-actions {
	margin-top: 8px;
}

.btn-login-primary {
	width: 100%;
	background: var(--login-primary);
	color: #ffffff;
	border: none;
	border-radius: 8px;
	padding: 14px;
	font-size: 15px;
	font-weight: 600;
	cursor: pointer;
	box-shadow: 0 2px 8px rgba(78,115,223,0.2);
	transition: all 0.2s ease;
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 8px;
}

.btn-login-primary:hover {
	background: var(--login-primary-hover);
	transform: translateY(-1px);
	box-shadow: 0 4px 12px rgba(78,115,223,0.3);
}

.btn-login-primary:active {
	transform: translateY(0);
}

.login-links {
	display: flex;
	justify-content: space-between;
	margin-top: 20px;
	gap: 16px;
	flex-wrap: wrap;
}

.login-links a {
	color: var(--login-primary);
	text-decoration: none;
	font-size: 13px;
	font-weight: 500;
	transition: all 0.2s;
	display: flex;
	align-items: center;
	gap: 5px;
}

.login-links a:hover {
	color: var(--login-primary-hover);
	text-decoration: underline;
}

/* Responsive */
@media (max-width: 576px) {
	.login-glass-container {
		padding: 30px 24px;
	}
	
	.login-title {
		font-size: 22px;
	}
	
	.login-links {
		flex-direction: column;
		align-items: center;
		gap: 10px;
	}
}
</style>

<?= $this->endSection() ?>