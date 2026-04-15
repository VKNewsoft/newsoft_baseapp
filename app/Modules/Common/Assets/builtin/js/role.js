$(document).ready(function() {

	$('.select2').select2({
		theme: 'bootstrap-5'
	});

	let dataTables = '';

	if ($('#table-result').length) {
		const column = $.parseJSON($('#dataTables-column').html());
		const url = $('#dataTables-url').text();
		const scrolls = $('#dataTables-scrolls').text();

		const settings = {
			processing: true,
			serverSide: true,
			scrollX: true,
			scrollY: scrolls,
			ajax: {
				url: url,
				type: 'POST'
			},
			columns: column
		};

		const $add_setting = $('#dataTables-setting');
		if ($add_setting.length > 0) {
			const add_setting = $.parseJSON($add_setting.html());
			for (const k in add_setting) {
				settings[k] = add_setting[k];
			}
		}

		dataTables = $('#table-result').DataTable(settings);
	}

	function showSuccessToast(message) {
		const Toast = Swal.mixin({
			toast: true,
			position: 'top-end',
			showConfirmButton: false,
			timer: 2500,
			timerProgressBar: true,
			iconColor: 'white',
			customClass: {
				popup: 'bg-success text-light toast p-2'
			},
			didOpen: (toast) => {
				toast.addEventListener('mouseenter', Swal.stopTimer);
				toast.addEventListener('mouseleave', Swal.resumeTimer);
			}
		});

		Toast.fire({
			html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> ' + message + '</div>'
		});
	}

	function normalizeMessage(message) {
		if ($.isPlainObject(message)) {
			let html = '<ul class="mb-0 ps-3">';
			$.each(message, function(key, value) {
				html += '<li>' + value + '</li>';
			});
			html += '</ul>';
			return html;
		}

		return message;
	}

	function bindModalPlugins($bootbox) {
		$bootbox.find('.select2').select2({
			theme: 'bootstrap-5',
			dropdownParent: $('.bootbox')
		});
	}

	function showRoleForm(id) {
		const formUrl = $('#role-form-url').text();
		const title = id ? 'Edit Role' : 'Tambah Role';
		let requestUrl = formUrl;

		if (id) {
			requestUrl += '?id=' + id;
		}

		const $bootbox = bootbox.dialog({
			title: title,
			message: '<div class="loader-ring loader"></div>',
			size: 'large',
			centerVertical: true,
			buttons: {
				cancel: {
					label: 'Tutup',
					className: 'btn-light'
				},
				success: {
					label: 'Simpan',
					className: 'btn-success submit',
					callback: function() {
						$bootbox.find('.alert').remove();

						const $button = $bootbox.find('button').prop('disabled', true);
						const $buttonSubmit = $bootbox.find('button.submit');
						$buttonSubmit.prepend('<i class="fas fa-circle-notch fa-spin me-2 fa-lg"></i>');

						$.ajax({
							type: 'POST',
							url: $('#role-save-url').text(),
							data: $bootbox.find('form').serialize(),
							dataType: 'json',
							success: function(data) {
								$buttonSubmit.find('i').remove();
								$button.prop('disabled', false);

								if (data.status === 'ok') {
									$bootbox.modal('hide');
									showSuccessToast(data.message || 'Data berhasil disimpan');
									if (dataTables) {
										dataTables.draw(false);
									}
								} else {
									$bootbox.find('.modal-body').prepend(
										'<div class="alert alert-dismissible alert-danger" role="alert">' +
										normalizeMessage(data.message) +
										'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
										'</div>'
									);
								}
							},
							error: function(xhr) {
								$buttonSubmit.find('i').remove();
								$button.prop('disabled', false);
								show_alert('Error !!!', xhr.responseText, 'error');
							}
						});

						return false;
					}
				}
			}
		});

		$.get(requestUrl, function(html) {
			$bootbox.find('.modal-body').empty().append(html);
			bindModalPlugins($bootbox);
		}).fail(function(xhr) {
			$bootbox.modal('hide');
			show_alert('Error !!!', xhr.responseText, 'error');
		});
	}

	$('body').on('click', '.btn-add-role', function(e) {
		e.preventDefault();
		showRoleForm();
	});

	$('#table-result').delegate('.btn-edit', 'click', function(e) {
		e.preventDefault();
		showRoleForm($(this).attr('data-id'));
	});

	$('#table-result').delegate('.btn-delete', 'click', function(e) {
		e.preventDefault();
		const id = $(this).attr('data-id');
		const title = $(this).attr('data-delete-title');

		const $bootbox = bootbox.confirm({
			message: title,
			centerVertical: true,
			callback: function(confirmed) {
				const $button = $bootbox.find('button').prop('disabled', true);
				const $button_submit = $bootbox.find('button.bootbox-accept');

				if (confirmed) {
					const $spinner = $('<span class="spinner-border spinner-border-sm me-2"></span>');
					$spinner.prependTo($button_submit);
					$.ajax({
						type: 'POST',
						url: current_url + '/delete',
						data: 'id=' + id,
						dataType: 'json',
						success: function(data) {
							$button.prop('disabled', false);
							$spinner.remove();
							$bootbox.modal('hide');
							if (data.status === 'ok') {
								showSuccessToast('Data berhasil dihapus');
								if (dataTables) {
									dataTables.draw(false);
								}
							} else {
								show_alert('Error !!!', data.message, 'error');
							}
						},
						error: function(xhr) {
							$button.prop('disabled', false);
							$spinner.remove();
							show_alert('Error !!!', xhr.responseText, 'error');
						}
					});

					return false;
				}
			}
		});
	});
});
