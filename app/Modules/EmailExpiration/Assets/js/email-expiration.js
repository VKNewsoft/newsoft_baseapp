/**
* Email Expiration Module Script
*
* Interaksi CRUD popup dan aksi renew dibuat mengikuti pattern module lain
* agar behaviour DataTable, modal, dan toast tetap konsisten di seluruh app.
*/

jQuery(document).ready(function () {
	let dataTables = '';

	function hideTableSkeleton() {
		if (window.NSModulePerformance) {
			window.NSModulePerformance.hideTableSkeleton('#table-data');
		}
	}

	/**
	 * Preview tgl_end dihitung realtime di form supaya user langsung melihat
	 * hasil kombinasi tgl_start dan expiration tanpa menunggu submit.
	 */
	function bindExpirationForm($context) {
		const $form = $context.find('#email-expiration-form');
		if (!$form.length) {
			return;
		}

		const $start = $form.find('[name="tgl_start"]');
		const $expiration = $form.find('[name="expiration_hari"]');
		const $endPreview = $form.find('[name="tgl_end_preview"]');

		const updateEndDate = function() {
			const startValue = $start.val();
			const expirationValue = parseInt($expiration.val(), 10) || 0;
			if (!startValue || expirationValue <= 0) {
				$endPreview.val('');
				return;
			}

			const startDate = new Date(startValue + 'T00:00:00');
			if (Number.isNaN(startDate.getTime())) {
				$endPreview.val('');
				return;
			}

			startDate.setDate(startDate.getDate() + expirationValue);
			const year = startDate.getFullYear();
			const month = String(startDate.getMonth() + 1).padStart(2, '0');
			const day = String(startDate.getDate()).padStart(2, '0');
			$endPreview.val(year + '-' + month + '-' + day);
		};

		$start.off('.emailExpiration').on('change.emailExpiration input.emailExpiration', updateEndDate);
		$expiration.off('.emailExpiration').on('change.emailExpiration input.emailExpiration', updateEndDate);
		updateEndDate();
	}

	if ($('#table-data').length) {
		const column = $.parseJSON($('#dataTables-column').html());
		const url = $('#dataTables-url').text();

		const settings = {
			processing: true,
			serverSide: true,
			scrollX: true,
			scrollY: window.WDIResultTable ? WDIResultTable.getScrollY('#table-data') : ($('#dataTables-scrolls').text() || '510'),
			ajax: {
				url: url,
				type: 'POST',
				error: function() {
					hideTableSkeleton();
				}
			},
			columns: column,
			initComplete: function() {
				hideTableSkeleton();
			}
		};

		const $addSetting = $('#dataTables-setting');
		if ($addSetting.length > 0) {
			const addSetting = $.parseJSON($addSetting.html());
			for (const key in addSetting) {
				settings[key] = addSetting[key];
			}
		}

		if (window.NSModulePerformance) {
			window.NSModulePerformance.defer(function() {
				dataTables = $('#table-data').DataTable(settings);
				if (window.WDIResultTable) {
					WDIResultTable.applyScrollBodyHeight(dataTables, '#table-data');
					WDIResultTable.bindResize(dataTables, '#table-data', 'email-expiration-table');
				}
			});
		} else {
			dataTables = $('#table-data').DataTable(settings);
			if (window.WDIResultTable) {
				WDIResultTable.applyScrollBodyHeight(dataTables, '#table-data');
				WDIResultTable.bindResize(dataTables, '#table-data', 'email-expiration-table');
			}
		}
	}

	$('body').delegate('.btn-edit', 'click', function(e) {
		e.preventDefault();
		showForm('edit', $(this).attr('data-id'));
	});

	$('body').delegate('.btn-add', 'click', function(e) {
		e.preventDefault();
		showForm('add');
	});

	$('#table-data').delegate('.btn-renew', 'click', function(e) {
		e.preventDefault();

		const id = $(this).attr('data-id');
		const email = $(this).attr('data-email');
		const $bootbox = bootbox.confirm({
			message: 'Perpanjang masa aktif akun <strong>' + email + '</strong> mulai hari ini?',
			callback: function(confirmed) {
				if (!confirmed) {
					return;
				}

				const $buttons = $bootbox.find('button').prop('disabled', true);
				const $submitButton = $bootbox.find('button.bootbox-accept');
				const $spinner = $('<span class="spinner-border spinner-border-sm me-2"></span>');
				$spinner.prependTo($submitButton);

				$.ajax({
					type: 'POST',
					url: current_url + '/ajaxRenew',
					data: {id: id},
					dataType: 'json',
					success: function(data) {
						$buttons.prop('disabled', false);
						$spinner.remove();
						if (data.status === 'ok') {
							$bootbox.modal('hide');
							const Toast = Swal.mixin({
								toast: true,
								position: 'top-end',
								showConfirmButton: false,
								timer: 2500,
								timerProgressBar: true,
								iconColor: 'white',
								customClass: {
									popup: 'bg-success text-light toast p-2'
								}
							});
							Toast.fire({
								html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> ' + data.message + '</div>'
							});
							dataTables.draw(false);
						} else {
							show_alert('Error !!!', data.message, 'error');
						}
					},
					error: function(xhr) {
						$buttons.prop('disabled', false);
						$spinner.remove();
						show_alert('Error !!!', xhr.responseText, 'error');
					}
				});
			},
			centerVertical: true
		});
	});

	$('#table-data').delegate('.btn-delete', 'click', function(e) {
		e.preventDefault();
		const id = $(this).attr('data-id');
		const $bootbox = bootbox.confirm({
			message: $(this).attr('data-delete-title'),
			callback: function(confirmed) {
				if (!confirmed) {
					return;
				}

				const $buttons = $bootbox.find('button').prop('disabled', true);
				const $submitButton = $bootbox.find('button.bootbox-accept');
				const $spinner = $('<span class="spinner-border spinner-border-sm me-2"></span>');
				$spinner.prependTo($submitButton);

				$.ajax({
					type: 'POST',
					url: current_url + '/ajaxDeleteData',
					data: {id: id},
					dataType: 'json',
					success: function(data) {
						$buttons.prop('disabled', false);
						$spinner.remove();
						$bootbox.modal('hide');
						if (data.status === 'ok') {
							const Toast = Swal.mixin({
								toast: true,
								position: 'top-end',
								showConfirmButton: false,
								timer: 2500,
								timerProgressBar: true,
								iconColor: 'white',
								customClass: {
									popup: 'bg-success text-light toast p-2'
								}
							});
							Toast.fire({
								html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> Data berhasil dihapus</div>'
							});
							dataTables.draw(false);
						} else {
							show_alert('Error !!!', data.message, 'error');
						}
					},
					error: function(xhr) {
						$buttons.prop('disabled', false);
						$spinner.remove();
						show_alert('Error !!!', xhr.responseText, 'error');
					}
				});
			},
			centerVertical: true
		});
	});

	function showForm(type = 'add', id = '') {
		const $bootbox = bootbox.dialog({
			title: 'Add/Edit Data',
			message: '<div class="text-center text-secondary"><div class="spinner-border"></div></div>',
			buttons: {
				cancel: {
					label: 'Cancel'
				},
				success: {
					label: 'Submit',
					className: 'btn-success submit',
					callback: function() {
						$bootbox.find('.alert').remove();
						$buttonSubmit.prepend('<i class="fas fa-circle-notch fa-spin me-2 fa-lg"></i>');
						$button.prop('disabled', true);

						const form = $bootbox.find('form')[0];
						$.ajax({
							type: 'POST',
							url: current_url + '/ajaxUpdateData',
							data: new FormData(form),
							processData: false,
							contentType: false,
							dataType: 'json',
							success: function(data) {
								if (data.status === 'ok') {
									const Toast = Swal.mixin({
										toast: true,
										position: 'top-end',
										showConfirmButton: false,
										timer: 2500,
										timerProgressBar: true,
										iconColor: 'white',
										customClass: {
											popup: 'bg-success text-light toast p-2'
										}
									});
									Toast.fire({
										html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> ' + data.message + '</div>'
									});
									if (type === 'edit') {
										dataTables.draw(false);
									} else {
										dataTables.draw();
									}
									$bootbox.modal('hide');
								} else {
									show_alert('Error !!!', data.message, 'error');
									$buttonSubmit.find('i').remove();
									$button.prop('disabled', false);
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

		const $button = $bootbox.find('button').prop('disabled', true);
		const $buttonSubmit = $bootbox.find('button.submit');

		$.get(current_url + '/ajaxGetFormData?id=' + id, function(html) {
			$button.prop('disabled', false);
			$bootbox.find('.modal-body').empty().append(html);
			bindExpirationForm($bootbox);
		});
	}
});
