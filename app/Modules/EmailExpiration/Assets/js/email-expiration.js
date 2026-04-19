/**
* Email Expiration Module Script
*
* Interaksi CRUD popup dan aksi renew dibuat mengikuti pattern module lain
* agar behaviour DataTable, modal, dan toast tetap konsisten di seluruh app.
*/

jQuery(document).ready(function () {
	let dataTables = '';
	let mobileListState = {
		offset: 0,
		limit: 6,
		hasMore: false,
		loading: false
	};

	function hideTableSkeleton() {
		if (window.NSModulePerformance) {
			window.NSModulePerformance.hideTableSkeleton('#table-data');
		}
	}

	/**
	 * Filter disatukan agar perubahan dari web maupun mobile tetap memakai
	 * parameter server-side yang identik.
	 */
	function getFilterState() {
		const renewStatus = $('#filter-renew-status').val() || $('#mobile-filter-renew-status').val() || 'all';
		const sortExpiration = $('#filter-sort-expiration').val() || $('#mobile-filter-sort-expiration').val() || 'nearest';

		return {
			renew_status: renewStatus,
			sort_expiration: sortExpiration
		};
	}

	/**
	 * Sinkronisasi select filter dijaga dua arah supaya tampilan desktop dan
	 * mobile selalu menunjukkan state filter yang sama.
	 */
	function syncFilterInputs(source) {
		const filters = getFilterState();
		if (source !== 'web') {
			$('#filter-renew-status').val(filters.renew_status);
			$('#filter-sort-expiration').val(filters.sort_expiration);
		}

		if (source !== 'mobile') {
			$('#mobile-filter-renew-status').val(filters.renew_status);
			$('#mobile-filter-sort-expiration').val(filters.sort_expiration);
		}
	}

	function escapeHtml(value) {
		return $('<div>').text(value || '').html();
	}

	function renderMobileCard(item) {
		const remainingClass = item.is_renew_ready ? 'is-expired' : 'is-active';
		const renewButtonClass = item.is_renew_ready ? 'btn-success' : 'btn-outline-success';
		const badgeClass = item.status_badge_class || 'bg-secondary';

		return '<div class="email-expiration-mobile__card">'
			+ '<div class="email-expiration-mobile__top">'
				+ '<div>'
					+ '<div class="email-expiration-mobile__subscription">' + escapeHtml(item.subscription) + '</div>'
					+ '<div class="email-expiration-mobile__email">' + escapeHtml(item.email_akun) + '</div>'
				+ '</div>'
				+ '<span class="badge ' + badgeClass + '">' + escapeHtml(item.status_label) + '</span>'
			+ '</div>'
			+ '<div class="email-expiration-mobile__meta">'
				+ '<div>'
					+ '<span class="email-expiration-mobile__meta-label">Sisa Hari</span>'
					+ '<span class="email-expiration-mobile__meta-value email-expiration-mobile__remaining ' + remainingClass + '">' + escapeHtml(item.remaining_label) + '</span>'
				+ '</div>'
				+ '<div>'
					+ '<span class="email-expiration-mobile__meta-label">Tanggal Berakhir</span>'
					+ '<span class="email-expiration-mobile__meta-value">' + escapeHtml(item.tgl_end_label) + '</span>'
				+ '</div>'
			+ '</div>'
			+ '<button type="button" class="btn ' + renewButtonClass + ' w-100 btn-renew" data-id="' + item.id_email_expiration + '" data-email="' + escapeHtml(item.renew_attr_email) + '">'
				+ '<i class="fas fa-rotate-right me-2"></i>Renew'
			+ '</button>'
		+ '</div>';
	}

	/**
	 * Load more mobile memakai offset agar render card tetap ringan dan tidak
	 * melakukan full load seperti DataTable desktop.
	 */
	function loadMobileList(resetList) {
		const $list = $('#email-expiration-mobile-list');
		const $loadMore = $('#email-expiration-load-more');
		const $empty = $('#email-expiration-mobile-empty');
		const mobileUrl = $('#mobile-list-url').text();

		if (!mobileUrl || mobileListState.loading) {
			return;
		}

		if (resetList) {
			mobileListState.offset = 0;
			mobileListState.hasMore = false;
			$list.empty();
			$empty.addClass('d-none');
		}

		mobileListState.loading = true;
		$loadMore.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Memuat...');

		$.ajax({
			url: mobileUrl,
			type: 'GET',
			dataType: 'json',
			data: $.extend({}, getFilterState(), {
				offset: mobileListState.offset,
				limit: mobileListState.limit
			}),
			success: function(response) {
				if (!response || response.status !== 'ok') {
					$empty.removeClass('d-none').text('Data email tidak berhasil dimuat.');
					return;
				}

				if (resetList && !response.data.length) {
					$empty.removeClass('d-none').text('Data email tidak ditemukan untuk filter saat ini.');
				}

				$.each(response.data, function(index, item) {
					$list.append(renderMobileCard(item));
				});

				mobileListState.offset = response.next_offset || (mobileListState.offset + response.data.length);
				mobileListState.hasMore = !!response.has_more;
				$loadMore.toggleClass('d-none', !mobileListState.hasMore);
			},
			error: function(xhr) {
				$empty.removeClass('d-none').text('Terjadi kesalahan saat memuat data mobile.');
				show_alert('Error !!!', xhr.responseText, 'error');
			},
			complete: function() {
				mobileListState.loading = false;
				$loadMore.prop('disabled', false).html('Load More');
			}
		});
	}

	function refreshAllLists(resetPaging) {
		if (dataTables && typeof dataTables.ajax !== 'undefined') {
			if (resetPaging) {
				dataTables.draw();
			} else {
				dataTables.draw(false);
			}
		}

		loadMobileList(true);
	}

	function openRenewDialog(id, email) {
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
							refreshAllLists(false);
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
				data: function(d) {
					const filters = getFilterState();
					d.renew_status = filters.renew_status;
					d.sort_expiration = filters.sort_expiration;
				},
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

	syncFilterInputs();
	loadMobileList(true);

	$('#filter-renew-status, #filter-sort-expiration').on('change', function() {
		syncFilterInputs('web');
		refreshAllLists(true);
	});

	$('#mobile-filter-renew-status, #mobile-filter-sort-expiration').on('change', function() {
		syncFilterInputs('mobile');
	});

	$('#mobile-apply-filter').on('click', function() {
		syncFilterInputs('mobile');
		refreshAllLists(true);
	});

	$('#email-expiration-load-more').on('click', function() {
		loadMobileList(false);
	});

	$('body').delegate('.btn-edit', 'click', function(e) {
		e.preventDefault();
		showForm('edit', $(this).attr('data-id'));
	});

	$('body').delegate('.btn-add', 'click', function(e) {
		e.preventDefault();
		showForm('add');
	});

	$('body').delegate('.btn-renew', 'click', function(e) {
		e.preventDefault();
		openRenewDialog($(this).attr('data-id'), $(this).attr('data-email'));
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
							refreshAllLists(false);
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
									refreshAllLists(type !== 'edit');
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
