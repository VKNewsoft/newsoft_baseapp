<?php
if ($request->getGet('mobile') == 'true') {
	echo $this->extend('themes/modern/layout-mobile');
	echo $this->section('content');
}
?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?= htmlspecialchars($title) ?></h5>
	</div>
	<div class="card-body">
		<?php
		if (!empty($message)) {
			// show_message is assumed to output safe HTML
			show_message($message);
		}
		?>
		<form method="post" action="" class="form-horizontal" id="form-edit-password" novalidate>
			<div class="mb-4 row">
				<label for="nama" class="col-sm-3 col-form-label">Nama</label>
				<div class="col-sm-9">
					<input id="nama" class="form-control-plaintext" readonly value="<?= htmlspecialchars($user['nama'] ?? '') ?>"/>
				</div>
			</div>

			<div class="mb-3 row">
				<label for="password_old" class="col-sm-3 col-form-label">Password Lama</label>
				<div class="col-sm-9">
					<input id="password_old" class="form-control" type="password" name="password_old" required />
					<div class="invalid-feedback">Masukkan password lama Anda.</div>
				</div>
			</div>

			<div class="mb-3 row">
				<label for="password_new" class="col-sm-3 col-form-label">Password Baru</label>
				<div class="col-sm-9">
					<input id="password_new" class="form-control" type="password" name="password_new" minlength="8" required />
					<small id="passwordHelp" class="form-text text-muted">Minimal 8 karakter.</small>
					<div class="invalid-feedback" id="password_new_feedback"></div>
				</div>
			</div>

			<div class="mb-4 row">
				<label for="password_new_confirm" class="col-sm-3 col-form-label">Ulangi Password Baru</label>
				<div class="col-sm-9">
					<input id="password_new_confirm" class="form-control" type="password" name="password_new_confirm" required />
					<div class="invalid-feedback" id="password_confirm_feedback">Password tidak cocok.</div>
				</div>
			</div>

			<div class="row">
				<div class="col-sm-9 offset-sm-3">
					<button id="btn-submit-edit-password" type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
					<input type="hidden" name="id" value="<?= esc($request->getGet('id') ?? '') ?>"/>
				</div>
			</div>
		</form>
	</div>
</div>

<script>
// Client-side validation: ensure new password length and match confirmation.
// This improves UX; server-side validation above is authoritative.
(function () {
	var form = document.getElementById('form-edit-password');
	var pw = document.getElementById('password_new');
	var pwConfirm = document.getElementById('password_new_confirm');
	var pwFeedback = document.getElementById('password_new_feedback');
	var pwConfirmFeedback = document.getElementById('password_confirm_feedback');

	function validatePasswords() {
		var valid = true;
		// Clear custom messages
		pw.classList.remove('is-invalid');
		pwConfirm.classList.remove('is-invalid');

		// Check length
		if (pw.value.length < 8) {
			pw.classList.add('is-invalid');
			pwFeedback.textContent = 'Password baru minimal 8 karakter.';
			valid = false;
		}

		// Check match
		if (pw.value !== pwConfirm.value) {
			pwConfirm.classList.add('is-invalid');
			pwConfirmFeedback.textContent = 'Ulangi password tidak cocok.';
			valid = false;
		}

		return valid;
	}

	pw.addEventListener('input', validatePasswords);
	pwConfirm.addEventListener('input', validatePasswords);

	form.addEventListener('submit', function (e) {
		// let browser perform built-in validation first
		if (!form.checkValidity()) {
			form.classList.add('was-validated');
			e.preventDefault();
			e.stopPropagation();
			return;
		}

		if (!validatePasswords()) {
			e.preventDefault();
			e.stopPropagation();
			return;
		}

		// allow submit — the server-side code will re-validate
	}, false);
})();
</script>

<?php
if ($request->getGet('mobile') == 'true') {
	echo $this->endSection();
}
?>