/**
*	App Name	: Base App Admin
*	Developed by: Newsoft Developer
*	Year		: 2020-2023
*/

jQuery(document).ready(function () {
	function hideTableSkeleton() {
		if (window.NSModulePerformance) {
			window.NSModulePerformance.hideTableSkeleton('#table-result');
		}
	}

	window.removeImage = function() {
		if (confirm('Apakah Anda yakin ingin menghapus foto profil?')) {
			$('.avatar-delete-img').val(1);
			$('input[name="avatar"]').val('');
			location.reload();
		}
	};

	function checkPasswordStrength(password) {
		let strength = 0;
		let feedback = [];

		if (password.length >= 6) {
			strength = 5;
		} else if (password.length >= 4) {
			strength = 3;
		} else if (password.length > 0) {
			strength = 1;
			feedback.push('Minimal 6 karakter');
		}

		return { strength, feedback };
	}

	function updateSubmitVisibility(shouldShow) {
		$('button[name="submit"]').toggleClass('d-none', !shouldShow);
	}

	function setFeedback($element, message, className) {
		$element.html(message).attr('class', 'validation-feedback mt-1 ' + className);
	}

	if ($('#table-result').length) {
		column = $.parseJSON($('#dataTables-column').html());
		url = $('#dataTables-url').text();
		scrolls = $('#dataTables-scrolls').text();

		var settings = {
			"processing": true,
			"serverSide": true,
			"scrollX": true,
			"scrollY": scrolls,
			"ajax": {
				"url": url,
				"type": "POST",
				"error": function() {
					hideTableSkeleton();
				}
			},
			"columns": column,
			"initComplete": function(settings, json) {
				hideTableSkeleton();
				table.rows().every(function(rowIdx, tableLoop, rowLoop) {
					$row = $(this.node());
				});
			}
		};

		$add_setting = $('#dataTables-setting');
		if ($add_setting.length > 0) {
			add_setting = $.parseJSON($('#dataTables-setting').html());
			for (k in add_setting) {
				settings[k] = add_setting[k];
			}
		}

		if (window.NSModulePerformance) {
			window.NSModulePerformance.defer(function() {
				table = $('#table-result').DataTable(settings);
				if (window.WDIResultTable) {
					WDIResultTable.applyScrollBodyHeight(table, '#table-result');
					WDIResultTable.bindResize(table, '#table-result', 'user-table');
				}
			});
		} else {
			table = $('#table-result').DataTable(settings);
			if (window.WDIResultTable) {
				WDIResultTable.applyScrollBodyHeight(table, '#table-result');
				WDIResultTable.bindResize(table, '#table-result', 'user-table');
			}
		}
	}

	if (window.NSModulePerformance) {
		window.NSModulePerformance.initSelect2($(document)).catch(function() {
			show_alert('Error !!!', 'Asset form user gagal dimuat', 'error');
		});
	}

	if (!$('#username').length) {
		return;
	}

	$('#username').attr('data-current', $('#username').val());

	$('#username').on('input', function() {
		validateUsername();
	});

	$('#username').on('blur', function() {
		checkUsernameUniqueness();
	});

	$('#email').on('input', function() {
		validateEmail();
	});

	$('#email').on('blur', function() {
		checkEmailUniqueness();
	});

	$('#password').on('input', function() {
		const password = $(this).val();
		const result = checkPasswordStrength(password);
		let strengthText = '';
		let strengthClass = '';
		let progressWidth = '0%';
		let progressClass = 'bg-secondary';

		if (password.length !== 0) {
			progressWidth = (result.strength / 5 * 100) + '%';

			if (result.strength < 2) {
				strengthText = 'Sangat Lemah';
				strengthClass = 'text-danger';
				progressClass = 'bg-danger';
			} else if (result.strength < 4) {
				strengthText = 'Cukup';
				strengthClass = 'text-warning';
				progressClass = 'bg-warning';
			} else {
				strengthText = 'Baik';
				strengthClass = 'text-success';
				progressClass = 'bg-success';
			}

			strengthText += '<div class="progress mt-1" style="height: 4px;"><div class="progress-bar ' + progressClass + '" style="width: ' + progressWidth + '"></div></div>';
		}

		setFeedback($('#password-strength'), strengthText, strengthClass);
		checkPasswordMatch();
	});

	$('#ulangi_password').on('input', function() {
		checkPasswordMatch();
	});

	function checkPasswordMatch() {
		const password = $('#password').val();
		const confirmPassword = $('#ulangi_password').val();
		const $matchDiv = $('#password-match');
		updateSubmitVisibility(false);

		if (confirmPassword.length === 0) {
			setFeedback($matchDiv, '', '');
			return;
		}

		if (password === confirmPassword) {
			setFeedback($matchDiv, '<i class="fas fa-check-circle me-1"></i>Password cocok', 'text-success small');
			updateSubmitVisibility(true);
		} else {
			setFeedback($matchDiv, '<i class="fas fa-times-circle me-1"></i>Password tidak cocok', 'text-danger small');
		}
	}

	function validateUsername() {
		const username = $('#username').val();
		const $usernameDiv = $('#username-validation');
		updateSubmitVisibility(false);
		setFeedback($usernameDiv, '', '');

		if (username.length === 0) {
			return;
		}

		const usernameRegex = /^[a-zA-Z0-9_-]{3,}$/;

		if (!usernameRegex.test(username)) {
			if (username.length < 3) {
				setFeedback($usernameDiv, '<i class="fas fa-times-circle me-1"></i>Minimal 3 karakter', 'text-danger small');
			} else {
				setFeedback($usernameDiv, '<i class="fas fa-times-circle me-1"></i>Hanya huruf, angka, underscore (_), dan dash (-)', 'text-danger small');
			}
			return false;
		}

		setFeedback($usernameDiv, '<i class="fas fa-check-circle me-1"></i>Format username valid', 'text-success small');
		updateSubmitVisibility(true);
		return true;
	}

	function checkUsernameUniqueness() {
		const username = $('#username').val();
		const $usernameDiv = $('#username-validation');
		const currentUsername = $('#username').attr('data-current') || '';
		const isEdit = $('input[name="id"]').val() > 0;
		updateSubmitVisibility(false);

		if (!validateUsername() || username.length === 0) {
			return;
		}

		if (isEdit && username === currentUsername) {
			setFeedback($usernameDiv, '<i class="fas fa-check-circle me-1"></i>Username valid', 'text-success small');
			updateSubmitVisibility(true);
			return;
		}

		setFeedback($usernameDiv, '<i class="fas fa-spinner fa-spin me-1"></i>Mengecek ketersediaan...', 'text-info small');

		$.ajax({
			url: base_url + 'builtin/user/ajaxCheckUsername',
			type: 'POST',
			data: {
				username: username,
				id_user: $('input[name="id"]').val() || 0
			},
			success: function(response) {
				try {
					const result = JSON.parse(response);
					if (result.available) {
						setFeedback($usernameDiv, '<i class="fas fa-check-circle me-1"></i>Username tersedia', 'text-success small');
						updateSubmitVisibility(true);
					} else {
						setFeedback($usernameDiv, '<i class="fas fa-times-circle me-1"></i>Username sudah digunakan', 'text-danger small');
					}
				} catch (e) {
					setFeedback($usernameDiv, '<i class="fas fa-exclamation-triangle me-1"></i>Gagal memeriksa username', 'text-warning small');
				}
			},
			error: function() {
				setFeedback($usernameDiv, '<i class="fas fa-exclamation-triangle me-1"></i>Gagal memeriksa username', 'text-warning small');
			}
		});
	}

	function validateEmail() {
		const email = $('#email').val();
		const $emailDiv = $('#email-validation');
		setFeedback($emailDiv, '', '');

		if (email.length === 0) {
			return;
		}

		const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

		if (!emailRegex.test(email)) {
			setFeedback($emailDiv, '<i class="fas fa-times-circle me-1"></i>Format email tidak valid', 'text-danger small');
			updateSubmitVisibility(false);
			return false;
		}

		setFeedback($emailDiv, '<i class="fas fa-check-circle me-1"></i>Format email valid', 'text-success small');
		updateSubmitVisibility(true);
		return true;
	}

	function checkEmailUniqueness() {
		const email = $('#email').val();
		const $emailDiv = $('#email-validation');
		const currentEmail = $('input[name="email_lama"]').val() || '';
		const isEdit = $('input[name="id"]').val() > 0;
		updateSubmitVisibility(false);

		if (!validateEmail() || email.length === 0) {
			return;
		}

		if (isEdit && email === currentEmail) {
			setFeedback($emailDiv, '<i class="fas fa-check-circle me-1"></i>Email valid', 'text-success small');
			updateSubmitVisibility(true);
			return;
		}

		setFeedback($emailDiv, '<i class="fas fa-spinner fa-spin me-1"></i>Mengecek ketersediaan...', 'text-info small');

		$.ajax({
			url: base_url + 'builtin/user/ajaxCheckEmail',
			type: 'POST',
			data: {
				email: email,
				email_lama: currentEmail
			},
			success: function(response) {
				try {
					const result = JSON.parse(response);
					if (result.available) {
						setFeedback($emailDiv, '<i class="fas fa-check-circle me-1"></i>Email tersedia', 'text-success small');
						updateSubmitVisibility(true);
					} else {
						setFeedback($emailDiv, '<i class="fas fa-times-circle me-1"></i>Email sudah digunakan', 'text-danger small');
					}
				} catch (e) {
					setFeedback($emailDiv, '<i class="fas fa-exclamation-triangle me-1"></i>Gagal memeriksa email', 'text-warning small');
				}
			},
			error: function() {
				setFeedback($emailDiv, '<i class="fas fa-exclamation-triangle me-1"></i>Gagal memeriksa email', 'text-warning small');
			}
		});
	}
});
