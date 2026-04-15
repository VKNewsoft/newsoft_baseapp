jQuery(document).ready(function () {
	const $form = $('#form-setting');
	if (!$form.length) {
		return;
	}

	const $body = $('body');
	const $fontSize = $('#font-size');
	const $font = $('#font');
	const $bootswatchTheme = $('#bootswatch-theme');
	const $sidebarColor = $('#sidebar-color');
	const $logoBackgroundColor = $('#logo-background-color');
	const $colorSchemeInput = $('#input-color-scheme');
	const $previewRoot = $('.setting-layout-preview');
	const $previewThemeName = $('#preview-theme-name');
	const $previewSidebar = $('#preview-sidebar');
	const $previewTopbar = $('#preview-topbar');
	const $previewMain = $('.preview-main');
	const $previewCard = $('.preview-card');
	const $previewButton = $('.preview-button');
	const $previewChip = $('.preview-chip');
	const $previewLogo = $previewSidebar.find('.preview-sidebar-logo');
	const $previewSidebarItems = $previewSidebar.find('.preview-sidebar-item');
	const fontFamilyMap = {
		'open-sans': '"Open Sans", "Segoe UI", Arial, sans-serif',
		'roboto': '"Roboto", "Segoe UI", Arial, sans-serif',
		'montserrat': '"Montserrat", "Segoe UI", Arial, sans-serif',
		'poppins': '"Poppins", "Segoe UI", Arial, sans-serif',
		'arial': 'Arial, "Helvetica Neue", sans-serif',
		'verdana': 'Verdana, Geneva, sans-serif'
	};
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
	const bootswatchPreviewMap = {
		'default': { surface: '#f8fafc', card: '#ffffff', button: '#2563eb', chip: 'rgba(37, 99, 235, 0.12)' },
		'cerulean': { surface: '#eef6fb', card: '#ffffff', button: '#2fa4e7', chip: 'rgba(47, 164, 231, 0.14)' },
		'cosmo': { surface: '#eef4f8', card: '#ffffff', button: '#2780e3', chip: 'rgba(39, 128, 227, 0.14)' },
		'flatty': { surface: '#ecf7f0', card: '#ffffff', button: '#18bc9c', chip: 'rgba(24, 188, 156, 0.16)' },
		'journal': { surface: '#faf6f2', card: '#ffffff', button: '#eb6864', chip: 'rgba(235, 104, 100, 0.15)' },
		'litera': { surface: '#f7f9fc', card: '#ffffff', button: '#4582ec', chip: 'rgba(69, 130, 236, 0.14)' },
		'lumen': { surface: '#f8faf8', card: '#ffffff', button: '#158cba', chip: 'rgba(21, 140, 186, 0.14)' },
		'minty': { surface: '#edf8f4', card: '#ffffff', button: '#78c2ad', chip: 'rgba(120, 194, 173, 0.18)' },
		'pulse': { surface: '#f7f1fb', card: '#ffffff', button: '#593196', chip: 'rgba(89, 49, 150, 0.16)' },
		'sandstone': { surface: '#f9f6f2', card: '#ffffff', button: '#325d88', chip: 'rgba(50, 93, 136, 0.14)' },
		'simplex': { surface: '#faf7f3', card: '#ffffff', button: '#d9230f', chip: 'rgba(217, 35, 15, 0.12)' },
		'spacelab': { surface: '#edf3fa', card: '#ffffff', button: '#446e9b', chip: 'rgba(68, 110, 155, 0.14)' },
		'united': { surface: '#fbf5ef', card: '#ffffff', button: '#e95420', chip: 'rgba(233, 84, 32, 0.14)' },
		'yeti': { surface: '#f2f6f8', card: '#ffffff', button: '#008cba', chip: 'rgba(0, 140, 186, 0.14)' },
		'zephyr': { surface: '#f4fbfa', card: '#ffffff', button: '#3459e6', chip: 'rgba(52, 89, 230, 0.14)' }
	};

	function getCacheVersion() {
		return Math.floor(Date.now() / 10000);
	}

	function buildAssetUrl(basePath, relativePath, withCache) {
		if (!basePath) {
			return '';
		}

		let url = String(basePath).replace(/\/+$/, '') + '/' + String(relativePath).replace(/^\/+/, '');
		if (withCache) {
			url += '?r=' + getCacheVersion();
		}
		return url;
	}

	function setStylesheetHref(selector, href) {
		if (href && $(selector).length) {
			$(selector).attr('href', href);
		}
	}

	function getSelectedFontFamily() {
		return fontFamilyMap[$font.val()] || fontFamilyMap['open-sans'];
	}

	function getSelectedThemeLabel(colorScheme) {
		const $selectedColor = $('#color-scheme').find('[data-color-scheme="' + colorScheme + '"] .theme-option-label');
		return $.trim($selectedColor.text()) || 'Default';
	}

	function updateSelectedColorScheme(colorScheme) {
		$('#color-scheme').find('i.theme-check').remove();
		const $selectedItem = $('#color-scheme').find('[data-color-scheme="' + colorScheme + '"]');
		if ($selectedItem.length && !$selectedItem.find('i.theme-check').length) {
			$selectedItem.append('<i class="fa fa-check theme-check"></i>');
		}
	}

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
		if (!$fontSize.length) {
			return;
		}

		const $output = $fontSize.next('output');
		const fontSize = parseFloat($fontSize.val()) || 14;
		const box = $output.outerWidth() / 2;
		const current = ((fontSize - 10) * 33) - box;
		const topPos = 22 + $fontSize.position().top;

		$body.css('font-size', fontSize + 'px');
		$previewRoot.css('font-size', fontSize + 'px');

		if ($output.length) {
			$output.css({
				left: current + 25,
				top: topPos
			}).text(fontSize);
		}
	}

	function updateThemePreview() {
		const colorScheme = $colorSchemeInput.val() || 'blue';
		const bootswatchTheme = $bootswatchTheme.val() || 'default';
		const previewColor = previewColorMap[colorScheme] || previewColorMap.blue;
		const sidebarMode = $sidebarColor.val();
		const logoBackground = $logoBackgroundColor.val();
		const themePreview = bootswatchPreviewMap[bootswatchTheme] || bootswatchPreviewMap['default'];
		const themeLabel = $bootswatchTheme.find('option:selected').text();
		const colorLabel = getSelectedThemeLabel(colorScheme);

		$previewThemeName.text(colorLabel + ' / ' + themeLabel);
		$previewRoot.css('font-family', getSelectedFontFamily());
		$previewMain.css('background', themePreview.surface);
		$previewCard.css('background', themePreview.card);
		$previewButton.css('background', 'linear-gradient(135deg, ' + themePreview.button + ', ' + previewColor + ')');
		$previewChip.css({
			background: themePreview.chip,
			color: previewColor
		});

		$previewTopbar.css('background', 'linear-gradient(135deg, ' + previewColor + ', ' + previewColor + 'dd)');

		if (sidebarMode === 'light') {
			$previewSidebar.css({
				background: '#f8fafc',
				color: '#0f172a'
			});
			$previewSidebarItems.css('color', '#475569');
			$previewSidebar.find('.preview-sidebar-item.active').css({
				background: 'rgba(37, 99, 235, 0.12)',
				color: previewColor
			});
		} else {
			$previewSidebar.css({
				background: '#0f172a',
				color: '#ffffff'
			});
			$previewSidebarItems.css('color', 'rgba(255,255,255,.72)');
			$previewSidebar.find('.preview-sidebar-item.active').css({
				background: 'rgba(255,255,255,.14)',
				color: '#ffffff'
			});
		}

		if (logoBackground === 'light') {
			$previewLogo.css({
				background: 'rgba(255,255,255,.88)',
				color: previewColor
			});
		} else if (logoBackground === 'dark') {
			$previewLogo.css({
				background: 'rgba(15,23,42,.88)',
				color: '#ffffff'
			});
		} else {
			$previewLogo.css({
				background: 'rgba(255,255,255,.15)',
				color: '#ffffff'
			});
		}
	}

	$fontSize.on('input change', updateFontPreview);
	updateFontPreview();
	updateSelectedColorScheme($colorSchemeInput.val());
	updateThemePreview();

	$('#color-scheme').on('click', '[data-color-scheme]', function(e) {
		e.preventDefault();
		const $this = $(this);
		const colorScheme = $this.data('color-scheme');
		if (!colorScheme) {
			return false;
		}

		setStylesheetHref('#style-switch', buildAssetUrl(theme_url, 'css/color-schemes/' + colorScheme + '.css', true));
		$colorSchemeInput.val(colorScheme);
		updateSelectedColorScheme(colorScheme);
		updateThemePreview();
		return false;
	});

	$sidebarColor.on('change', function() {
		setStylesheetHref('#style-switch-sidebar', buildAssetUrl(theme_url, 'css/color-schemes/' + this.value + '-sidebar.css', true));
		updateThemePreview();
	});

	$logoBackgroundColor.on('change', function() {
		setStylesheetHref('#logo-background-color-switch', buildAssetUrl(theme_url, 'css/color-schemes/' + this.value + '-logo-background.css', true));
		updateThemePreview();
	});

	$bootswatchTheme.on('change', function() {
		setStylesheetHref('#style-switch-bootswatch', buildAssetUrl(base_url, 'public/vendors/bootswatch/' + this.value + '/bootstrap.min.css', true));
		updateThemePreview();
	});

	$font.on('change', function() {
		setStylesheetHref('#font-switch', buildAssetUrl(theme_url, 'css/fonts/' + $(this).val() + '.css', false));
		updateThemePreview();
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
