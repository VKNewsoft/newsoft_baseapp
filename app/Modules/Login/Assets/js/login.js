// Small JS to add a little focus animation when user types -- enhances perceived responsiveness
(function(){
	var inputs = document.querySelectorAll('.modern-login-form .modern-input');
	inputs.forEach(function(inp){
		inp.addEventListener('input', function(){
			inp.style.transition = 'box-shadow .22s ease, transform .18s ease';
			inp.style.transform = 'translateY(-1px)';
			clearTimeout(inp._t);
			inp._t = setTimeout(function(){ inp.style.transform='none' }, 220);
		});
	});
})();

// Toggle visibility without jQuery
(function(){
	document.addEventListener('DOMContentLoaded', function(){
		var toggle = document.querySelector('.password-toggle-btn');
		var pwd = document.getElementById('password-field');
		var form = document.querySelector('.modern-login-form');
		var username = document.getElementById('username-field');
		var alertBox = document.getElementById('login-alert-message');
		var submitButton = document.getElementById('btn-submit-login');
		var submitLabel = submitButton ? submitButton.querySelector('.btn-login-label') : null;
		if (!toggle || !pwd) return;

		function showLoginError(message) {
			if (!alertBox) return;
			var text = alertBox.querySelector('span');
			if (text) {
				text.textContent = message;
			}
			alertBox.classList.remove('d-none');
		}

		function hideLoginError() {
			if (!alertBox) return;
			alertBox.classList.add('d-none');
		}

		function setLoadingState(isLoading) {
			if (!submitButton) return;
			submitButton.disabled = isLoading;
			submitButton.classList.toggle('is-loading', isLoading);
			if (submitLabel) {
				submitLabel.textContent = isLoading ? 'Memproses...' : 'Login';
			}
		}

		toggle.addEventListener('click', function(e){
			e.preventDefault();
			var isPwd = pwd.type === 'password';
			pwd.type = isPwd ? 'text' : 'password';
			toggle.setAttribute('aria-pressed', isPwd ? 'true' : 'false');
			toggle.setAttribute('aria-label', isPwd ? 'Sembunyikan password' : 'Tampilkan password');
			var icon = toggle.querySelector('i');
			if (icon) {
				icon.classList.toggle('fa-eye');
				icon.classList.toggle('fa-eye-slash');
			}
		});

		[username, pwd].forEach(function(field){
			if (!field) return;
			field.addEventListener('input', hideLoginError);
		});

		if (form) {
			form.addEventListener('submit', function(e){
				var userValue = username ? username.value.trim() : '';
				var passwordValue = pwd ? pwd.value.trim() : '';
				if (!userValue && !passwordValue) {
					e.preventDefault();
					showLoginError('Masukkan Username/Email & Password Dulu');
					setLoadingState(false);
					if (username) username.focus();
					return;
				}
				if (!userValue) {
					e.preventDefault();
					showLoginError('Masukkan Username/Email Dulu');
					setLoadingState(false);
					if (username) username.focus();
					return;
				}
				if (!passwordValue) {
					e.preventDefault();
					showLoginError('Masukkan Password Dulu');
					setLoadingState(false);
					pwd.focus();
					return;
				}

				hideLoginError();
				setLoadingState(true);
			});
		}
	});
})();

jQuery(document).ready(function () {	
	$('form.modern-login-form').submit(function(e) {
		const username = $.trim($('#username-field').val() || '');
		const password = $.trim($('#password-field').val() || '');
		const $alert = $('#login-alert-message');
		const $alertText = $alert.find('span');
		const $button = $('#btn-submit-login');
		const $label = $button.find('.btn-login-label');

		if (!username && !password) {
			e.preventDefault();
			$alert.removeClass('d-none');
			$alertText.text('Masukkan Username/Email & Password Dulu');
			$button.prop('disabled', false).removeClass('is-loading');
			$label.text('Login');
			return;
		}

		e.preventDefault();
		$button.prop('disabled', true);
		$button.addClass('is-loading');
		$label.text('Memproses...');
		let $form = $(this);

		window.requestAnimationFrame(function() {
		$.ajax({
			url: base_url + 'login',
			type: 'POST',
			data: $form.serialize() + '&ajax=true',
			success: function(data) {
				let data_value;
				try {
					data_value = JSON.parse(data);
				} catch (err) {
					$button.prop('disabled', false).removeClass('is-loading');
					$label.text('Login');
					Swal.fire({
						icon: 'error',
						title: 'Kesalahan',
						text: 'Respons server tidak valid. Silakan coba lagi.',
						confirmButtonText: 'Tutup'
					});
					return;
				}

				if (data_value.status === 'ok') {
					window.location = base_url;
				} else if (data_value.status === 'jamshift') {
					Swal.fire({
						icon: 'error',
						title: 'Kesalahan',
						text: data_value.message || 'Terjadi kesalahan terkait jadwal. Silakan hubungi administrator jika diperlukan.',
						confirmButtonText: 'Tutup'
					});
					$button.prop('disabled', false).removeClass('is-loading');
					$label.text('Login');
				} else if (data_value.status === 'error') {
					Swal.fire({
						icon: 'error',
						title: 'Kesalahan',
						text: data_value.message || 'Terjadi kesalahan. Silakan coba lagi.',
						confirmButtonText: 'Tutup'
					});
					$button.prop('disabled', false).removeClass('is-loading');
					$label.text('Login');
				} else {
					$button.prop('disabled', false).removeClass('is-loading');
					$label.text('Login');
					window.location = base_url;
				}
			},
			error: function(xhr) {
				$button.prop('disabled', false).removeClass('is-loading');
				$label.text('Login');
				Swal.fire({
					icon: 'error',
					title: 'Kesalahan Jaringan',
					text: 'Terjadi masalah koneksi. Periksa jaringan atau lihat konsol pengembang untuk detail.',
					confirmButtonText: 'Tutup'
				});
				console.error(xhr);
			}
		});
		});
	});
});
