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
        <img src="<?php echo $config->baseURL . 'public/images/' . $setting_aplikasi['logo_login'] ?>?r=<?=time()?>" style="width:300px;">
    </div>
</div>

<div class="card-body">
	<?php
	if (@$message) {
		show_message($message);
	}
	?>
	<p>Silakan input no WhatsApp Anda yang sudah terdaftar, kami akan mengirimkan link reset password ke no WhatsApp Anda.</p>

	<form method="post" action="<?=current_url()?>">
	<div class="mb-3">
		<input type="number"  name="nohp" value="<?=set_value('nohp')?>" class="form-control" placeholder="08xxxxxxxxx" aria-label="nohp" required>
	</div>
	<div class="row mb-3">
		<div class="col-md-12 d-flex justify-content-center">
			<button type="submit" name="submit" value="submit" class="btn btn-success w-25 me-2">Submit</button>
			<?=csrf_formfield()?>
			<button type="button" class="btn btn-danger w-25" onclick="window.location.href='<?=base_url()?>'">Batal</button>
		</div>
	</div>
	</form>
</div>
<?= $this->endSection() ?>