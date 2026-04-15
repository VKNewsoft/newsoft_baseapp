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
		if (!toggle || !pwd) return;
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
	});
})();

jQuery(document).ready(function () {	
	bootbox.setDefaults({
		animate: false,
		centerVertical: true
	});
	
	$('form').submit(function(e) {
		e.preventDefault();
		let $button = $(this).find('button');
		$button.prop('disabled', true);
		let $form = $(this);

		$.ajax({
			url: base_url + 'login',
			type: 'POST',
			data: $form.serialize() + '&ajax=true',
			success: function(data) {
				let data_value;
				try {
					data_value = JSON.parse(data);
				} catch (err) {
					$button.prop('disabled', false);
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
					$button.prop('disabled', false);
				} else if (data_value.status === 'error') {
					Swal.fire({
						icon: 'error',
						title: 'Kesalahan',
						text: data_value.message || 'Terjadi kesalahan. Silakan coba lagi.',
						confirmButtonText: 'Tutup'
					});
					$button.prop('disabled', false);
				} else {
					$button.prop('disabled', false);
					window.location = base_url;
				}
			},
			error: function(xhr) {
				$button.prop('disabled', false);
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