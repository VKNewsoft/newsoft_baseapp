<?php
$emailExpiration = $email_expiration ?? [];
$defaultStart = $emailExpiration['tgl_start'] ?? date('Y-m-d');
$defaultExpiration = (int) ($emailExpiration['expiration_hari'] ?? 30);
$defaultEnd = $emailExpiration['tgl_end'] ?? date('Y-m-d', strtotime($defaultStart . ' +' . $defaultExpiration . ' days'));
?>
<form method="post" action="" class="form-horizontal p-3" id="email-expiration-form">
	<div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Subscription</label>
			<div class="col-sm-9">
				<input class="form-control" type="text" name="subscription" value="<?=$emailExpiration['subscription'] ?? ''?>" required="required"/>
				<div class="text-muted">Masukkan nama subscriptionnya.</div>
			</div>
		</div>

		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Email / Akun</label>
			<div class="col-sm-9">
				<input class="form-control" type="email" name="email_akun" value="<?=esc($emailExpiration['email_akun'] ?? '')?>" required="required"/>
				<div class="text-muted">Masukkan akun email yang masa aktifnya ingin dipantau.</div>
			</div>
		</div>

		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Expiration (Hari)</label>
			<div class="col-sm-9">
				<input class="form-control" type="number" min="1" name="expiration_hari" value="<?=$defaultExpiration?>" required="required"/>
				<div class="text-muted">Jumlah hari akan dipakai untuk menghitung ulang tanggal berakhir secara otomatis.</div>
			</div>
		</div>

		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Tanggal Mulai</label>
			<div class="col-sm-9">
				<input class="form-control" type="date" name="tgl_start" value="<?=$defaultStart?>" required="required"/>
				<div class="text-muted">Tanggal mulai menjadi dasar perhitungan tgl_end dan aksi renew.</div>
			</div>
		</div>

		<div class="row mb-0">
			<label class="col-sm-3 col-form-label">Tanggal Berakhir</label>
			<div class="col-sm-9">
				<input class="form-control" type="date" name="tgl_end_preview" value="<?=$defaultEnd?>" readonly="readonly"/>
				<div class="text-muted">Field ini dihitung otomatis dari Tanggal Mulai + Expiration dan akan disimpan konsisten oleh server.</div>
			</div>
		</div>
	</div>
	<input type="hidden" name="id" value="<?=esc($emailExpiration['id_email_expiration'] ?? '')?>"/>
</form>
