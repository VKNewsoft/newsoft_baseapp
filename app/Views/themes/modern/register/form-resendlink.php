<?= $this->extend('themes/modern/register/layout') ?>
<?= $this->section('content') ?>
<div class="card-header">
	<div class="logo">
		<img src="<?php echo $config->baseURL . 'public/images/' . $setting_aplikasi['logo_register'] ?>?r=<?=time()?>">
	</div>
</div>
<div class="card-body">
	<?php
	if (@$message) {
		show_message($message);
	}
	
	if (@$message['type'] != 'result_message') {
		?>
		<p>Halaman ini digunakan untuk kirim ulang link aktivasi akun. Silakan isikan alamat email Anda pada form di bawah ini, kami akan mengirimkan link aktivasi ke alamat email tersebut.</p><p><strong>Note</strong>: Link aktivasi sebelumnya menjadi tidak berfungsi</p>

		<form method="post" action="<?=current_url()?>">
		<div class="mb-3">
			<input type="email"  name="email" value="<?=set_value('email')?>" class="form-control register-input" placeholder="Email" aria-label="Email" required>
		</div>
		<div class="mb-3" style="margin-bottom:0">
			<button type="submit" name="submit" value="submit" class="btn btn-success" style="display:block;width:100%">Submit</button>
			<?=csrf_formfield()?>
		</div>
		</form>
	<?php
	}
	?>
</div>
<?= $this->endSection() ?>