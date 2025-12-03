<?= $this->extend('themes/modern/register/layout') ?>
<?= $this->section('content') ?>
<div class="card-header pb-3">
	<div class="logo">
		<img src="<?php echo $config->baseURL . 'public/images/' . $setting_aplikasi['logo_register'] ?>?r=<?=time()?>">
	</div>
</div>
<div class="card-body">
	<?php
	if (!empty($message)) {
		show_message($message);
	}
	
	helper('form');
	?>
	<form action="<?=current_url()?>" method="post" accept-charset="utf-8">
	
		<p style="text-align:center">Komitmen kami: kami akan menyimpan data Anda dengan aman dan <strong>tidak akan membagi data Anda</strong> ke siapapun</p>
		<div class="mb-3">
			<label class="mb-2">Bagaimana kami memanggil Anda?</label>
			<div class="row">
				<div class="col-auto">
					<select name="gender" class="form-select register-input">
						<option value="L" <?=set_select('gender', 'L')?>>Bapak/Mas</option>
						<option value="P" <?=set_select('gender', 'P')?>>Ibu/Mbak</option>
					</select>
				</div>
				<div class="col ps-0">
					<input type="text" name="nama" value="<?=set_value('nama')?>" class="form-control register-input" placeholder="Nama" aria-label="Nama" required>
				</div>
			</div>
		</div>
		<div class="mb-3">
			<label class="mb-2">Email</label>
			<input type="email"  name="email" value="<?=set_value('email', '')?>" class="form-control register-input" placeholder="Email" aria-label="Email" required>
			<p class="small">Kemana kami akan menginformasikan promo, kupon, produk baru, dan info menarik lainnya ?</p>
		</div>
		<div class="mb-3">
			<label class="mb-2">Username</label>
			<input type="text"  name="username" value="<?=set_value('username', '')?>" class="form-control register-input" placeholder="Username" aria-label="Username" required>
			<p class="small">Username untuk login</p>
		</div>
		<div class="mb-3">
			<label class="mb-2">Password</label>
			<input type="password"  name="password" class="form-control register-input" placeholder="Password" aria-label="Password" required>
			<div class="pwstrength_viewport_progress"></div>
			<p class="small">Bantu kami untuk melindungi data Anda dengan membuat password yang kuat, indikator: medium-strong, min 9 karakter, paling sedikit mengandung huruf kecil, huruf besar, dan angka.</p>
		</div>
		<div class="mb-3">
			<label class="mb-2">Confirm Password</label>
			<input type="password"  name="password_confirm" class="form-control register-input" placeholder="Confirm Password" aria-label="Confirm Password" required>
		</div>
		<div class="mb-3" style="margin-bottom:0">
			<button type="submit" name="submit" value="submit" class="btn btn-success" style="display:block;width:100%">Register</button>
			<?=csrf_formfield()?>
		</div>
	</form>
</div>
<?= $this->endSection() ?>