jQuery(document).ready(function () {
	const $body = $('body');
	const $form = $('#form-setting');
	const $fontSize = $('#font-size');
	const $previewThemeName = $('#preview-theme-name');
	const $previewSidebar = $('#preview-sidebar');
	const $previewTopbar = $('#preview-topbar');
	const previewColorMap = {
		'blue-dark': '#183b77',
		'blue': '#1976d2',
		'green': '#169a54',
		'grey': '#475569',
		'purple': '#7c3aed',
		'red': '#dc2626',
		'yellow': '#d97706',
		'jnn': '#8b3035',
		'payday': '#013062'
	};

	if ($('html').attr('data-bs-theme') == 'dark') {
		bootbox.confirm({
			message: 'Halaman ini hanya dapat memberikan efek pada theme light. Apakah Anda ingin mengubah theme menjadi light?',
			buttons: {
				confirm: {
					label: 'Ya'
				},
				cancel: {
					label: 'Tidak'
				},
			},
			callback: function(result) {
				if (result) {
					$('button[data-theme-value="light"]').trigger('click');
				}
			}
		});
	}

	function updateFontPreview() {
		const $output = $fontSize.next('output');
		const box = $output.width() / 2;
		const current = (($fontSize.val() - 10) * 33) - box;
		const topPos = 22 + $fontSize.position().top;

		$body.css('font-size', $fontSize.val() + 'px');
		$output.css({
			left: current + 25,
			top: topPos
		}).text($fontSize.val());
	}

	function updateThemePreview(colorScheme) {
		const previewColor = previewColorMap[colorScheme] || previewColorMap.blue;
		const sidebarMode = $('#sidebar-color').val();
		const logoBackground = $('#logo-background-color').val();

		$previewThemeName.text((colorScheme || '').replace(/-/g, ' ').replace(/\b\w/g, function(match) {
			return match.toUpperCase();
		}));

		$previewTopbar.css('background', 'linear-gradient(135deg, ' + previewColor + ', ' + previewColor + 'dd)');

		if (sidebarMode === 'light') {
			$previewSidebar.css({
				background: '#f8fafc',
				color: '#0f172a'
			});
			$previewSidebar.find('.preview-sidebar-item').css('color', '#475569');
			$previewSidebar.find('.preview-sidebar-item.active').css({
				background: 'rgba(37, 99, 235, 0.12)',
				color: previewColor
			});
		} else {
			$previewSidebar.css({
				background: '#0f172a',
				color: '#ffffff'
			});
			$previewSidebar.find('.preview-sidebar-item').css('color', 'rgba(255,255,255,.72)');
			$previewSidebar.find('.preview-sidebar-item.active').css({
				background: 'rgba(255,255,255,.14)',
				color: '#ffffff'
			});
		}

		if (logoBackground === 'light') {
			$previewSidebar.find('.preview-sidebar-logo').css({
				background: 'rgba(255,255,255,.88)',
				color: previewColor
			});
		} else if (logoBackground === 'dark') {
			$previewSidebar.find('.preview-sidebar-logo').css({
				background: 'rgba(15,23,42,.88)',
				color: '#ffffff'
			});
		} else {
			$previewSidebar.find('.preview-sidebar-logo').css({
				background: 'rgba(255,255,255,.15)',
				color: '#ffffff'
			});
		}
	}

	$fontSize.on('input', updateFontPreview);
	updateFontPreview();
	updateThemePreview($('#input-color-scheme').val());

	$('#color-scheme').on('click', 'a', function() {
		const $this = $(this);
		if ($this.children('i').length > 0) {
			return false;
		}

		const classes = $this.attr('class');
		const split = classes.replace('-theme', '');
		const url = theme_url + '/css/color-schemes/' + split + '.css?r=' + Math.floor(Date.now() / 10000);
		const $elements = $('#color-scheme, #color-scheme-side');

		$('#style-switch').attr('href', url);
		$elements.each(function() {
			$elements.find('i').remove();
			$elements.find('a.' + classes).append('<i class="fa fa-check theme-check"></i>');
		});

		$('#input-color-scheme').val(split);
		updateThemePreview(split);
		return false;
	});

	$('#sidebar-color').change(function() {
		const url = theme_url + '/css/color-schemes/' + this.value + '-sidebar.css?r=' + Math.floor(Date.now() / 10000);
		$('#style-switch-sidebar').attr('href', url);
		updateThemePreview($('#input-color-scheme').val());
	});

	$('#logo-background-color').change(function() {
		const url = theme_url + '/css/color-schemes/' + this.value + '-logo-background.css?r=' + Math.floor(Date.now() / 10000);
		$('#logo-background-color-switch').attr('href', url);
		updateThemePreview($('#input-color-scheme').val());
	});

	$('#bootswatch-theme').change(function() {
		const url = base_url + '/public/vendors/bootswatch/' + this.value + '/bootstrap.min.css?r=' + Math.floor(Date.now() / 10000);
		$('#style-switch-bootswatch').attr('href', url);
	});

	$('#font').change(function() {
		const url = theme_url + '/css/fonts/' + $(this).val() + '.css';
		$('#font-switch').attr('href', url);
		$('.setting-layout-preview').css('font-family', $(this).find('option:selected').text().split(' ')[0]);
	});

	$('#font-size').on('change', function() {
		$('body').css('font-size', this.value + 'px');
	});

	$form.submit(function(e) {
		e.preventDefault();

		const $btn = $(this).find('button[type="submit"]').addClass('disabled').css('float', 'left');
		$btn.attr('disabled', 'disabled');

		const $loader = $('<div class="spinner-submit fa-3x"><i class="fas fa-circle-notch fa-spin"></i></div>').insertAfter($btn);
		$.ajax({
			url: module_url,
			method: 'POST',
			data: $(this).serialize() + '&submit=submit&ajax=ajax',
			success: function(data) {
				const msg = $.parseJSON(data);
				const title = msg.status == 'ok' ? 'SUKSES !!!' : 'ERROR !!!';
				const icon = msg.status == 'ok' ? 'success' : 'error';

				Swal.fire({
					text: msg.message,
					title: title,
					icon: icon,
					showCloseButton: true,
					confirmButtonText: 'OK'
				});

				$btn.removeAttr('disabled').removeClass('disabled');
				$loader.remove();
			},
			error: function() {
				Swal.fire({
					text: 'Request error, lihat log console',
					title: 'Error !!!',
					icon: 'error',
					showCloseButton: true,
					confirmButtonText: 'OK'
				});
				$btn.removeAttr('disabled').removeClass('disabled');
				$loader.remove();
			}
		});
	});
});
