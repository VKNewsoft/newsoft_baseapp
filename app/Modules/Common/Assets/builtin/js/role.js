$(document).ready(function() {

	let dataTables = '';
	let select2ResourcePromise = null;

	function appendStylesheetOnce(href) {
		if (!href || document.querySelector('link[href^="' + href + '"]')) {
			return;
		}

		const link = document.createElement('link');
		link.rel = 'stylesheet';
		link.href = href;
		document.head.appendChild(link);
	}

	function appendScriptOnce(src) {
		return new Promise(function(resolve, reject) {
			const existing = document.querySelector('script[src^="' + src + '"]');
			if (existing) {
				if (typeof window.jQuery !== 'undefined' && typeof jQuery.fn.select2 === 'function') {
					resolve();
					return;
				}

				existing.addEventListener('load', resolve, { once: true });
				existing.addEventListener('error', reject, { once: true });
				return;
			}

			const script = document.createElement('script');
			script.src = src;
			script.defer = true;
			script.onload = resolve;
			script.onerror = reject;
			document.body.appendChild(script);
		});
	}

	function ensureSelect2Assets() {
		if (typeof window.jQuery !== 'undefined' && typeof jQuery.fn.select2 === 'function') {
			return Promise.resolve();
		}

		if (select2ResourcePromise) {
			return select2ResourcePromise;
		}

		// Asset Select2 ditunda sampai form benar-benar dibutuhkan agar halaman list
		// tidak ikut memuat dependency modal pada initial render.
		appendStylesheetOnce(base_url + 'public/vendors/jquery.select2/css/select2.min.css');
		appendStylesheetOnce(base_url + 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css');
		select2ResourcePromise = appendScriptOnce(base_url + 'public/vendors/jquery.select2/js/select2.full.min.js');

		return select2ResourcePromise;
	}

	function initSelect2($context) {
		const $root = $context && $context.length ? $context : $(document);
		const $select2 = $root.find('.select2');
		if (!$select2.length) {
			return Promise.resolve();
		}

		return ensureSelect2Assets().then(function() {
			$select2.each(function() {
				const $element = $(this);
				if ($element.data('select2')) {
					return;
				}

				const options = {
					theme: 'bootstrap-5'
				};

				const $modal = $element.closest('.bootbox');
				if ($modal.length) {
					options.dropdownParent = $modal;
				}

				$element.select2(options);
			});
		});
	}

	function hideTableSkeleton() {
		// DataTable role memakai scroll header clone, jadi class loaded juga
		// perlu diterapkan ke tabel clone agar judul kolom tetap terlihat.
		const $table = $('#table-result');
		const $tableWrapper = $table.closest('.dataTables_wrapper');

		$('#role-table-skeleton').addClass('role-table-skeleton--hidden');
		$table.addClass('role-table-ready--loaded');
		$tableWrapper.find('.dataTables_scrollHead table, .dataTables_scrollBody table').addClass('role-table-ready--loaded');

		// Penyesuaian ulang kolom memastikan header dan body tetap sejajar
		// setelah area skeleton dilepas dari layout awal.
		if ($table.length && $.fn.dataTable && $.fn.dataTable.isDataTable($table.get(0))) {
			const dataTable = $table.DataTable();
			if (window.requestAnimationFrame) {
				window.requestAnimationFrame(function() {
					dataTable.columns.adjust();
				});
			} else {
				window.setTimeout(function() {
					dataTable.columns.adjust();
				}, 16);
			}

			window.setTimeout(function() {
				dataTable.columns.adjust();
			}, 120);
		}
	}

	function initRoleTable() {
		if (!$('#table-result').length) {
			return;
		}

		const column = $.parseJSON($('#dataTables-column').html());
		const url = $('#dataTables-url').text();
		const scrolls = $('#dataTables-scrolls').text();
		const tableScrollY = window.WDIResultTable && typeof window.WDIResultTable.getScrollY === 'function'
			? window.WDIResultTable.getScrollY('#table-result')
			: scrolls;

		const settings = {
			processing: true,
			serverSide: true,
			scrollX: true,
			scrollY: tableScrollY,
			searchDelay: 350,
			autoWidth: false,
			ajax: {
				url: url,
				type: 'POST',
				error: function() {
					// Skeleton tetap dilepas saat request gagal supaya halaman tidak
					// terlihat stuck dan user masih bisa melihat header tabel.
					hideTableSkeleton();
				}
			},
			columns: column,
			initComplete: function() {
				// Skeleton dilepas saat tabel siap agar user langsung melihat konten utama.
				hideTableSkeleton();
			}
		};

		const $add_setting = $('#dataTables-setting');
		if ($add_setting.length > 0) {
			const add_setting = $.parseJSON($add_setting.html());
			for (const k in add_setting) {
				settings[k] = add_setting[k];
			}
		}

		dataTables = $('#table-result').DataTable(settings);
		if (window.WDIResultTable && typeof window.WDIResultTable.bindResize === 'function') {
			window.WDIResultTable.bindResize(dataTables, '#table-result');
		}
	}

	if ($('#table-result').length) {
		if ('requestAnimationFrame' in window) {
			requestAnimationFrame(function() {
				initRoleTable();
			});
		} else {
			setTimeout(function() {
				initRoleTable();
			}, 16);
		}
	}

	initSelect2();

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
		initSelect2($bootbox).catch(function() {
			show_alert('Error !!!', 'Asset form role gagal dimuat', 'error');
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
