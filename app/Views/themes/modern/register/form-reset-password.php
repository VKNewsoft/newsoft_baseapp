<?= $this->extend('themes/modern/register/layout') ?>
<?= $this->section('content') ?>
<style>
	.card-header img {
		width: 100%;
    	max-width: 100%;
	}
</style>
<div class="card-header">
	<div class="logo">
		<img src="<?php echo $config->baseURL . 'public/images/' . $setting_aplikasi['logo_login'] ?>?r=<?=time()?>">
	</div>
</div>
<div class="card-body">
	<?php
	if (!empty($message['message'])) {
		show_message($message);
	} ?>
	<p>Buat password baru</p>
	<form method="post" action="<?=current_url(true)?>">
	<div class="mb-3">
		<input type="password"  name="password" class="form-control register-input" placeholder="Password" aria-label="Password" required>
		<div class="pwstrength_viewport_progress"></div>
		<p class="small">Lindungi data Anda dengan membuat password yang kuat (indikator medium-strong), minimal 8 karakter, mengandung huruf kecil, huruf besar, dan angka.</p>
	</div>
	<div class="mb-3">
		<input type="password"  name="password_confirm" class="form-control register-input" placeholder="Confirm Password" aria-label="Confirm Password" required>
	</div>
<div class="mb-3" style="margin-bottom:0">
		<button type="submit" name="submit" value="submit" class="btn btn-success" style="display:block;width:100%">Submit</button>
		<?=csrf_formfield()?>
	</div>
	</form>
</div>

<?= $this->endSection() ?>