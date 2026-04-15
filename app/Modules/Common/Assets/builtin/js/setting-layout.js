jQuery(document).ready(function () {
	const $form = $('#form-setting');
	if (!$form.length) {
		return;
	}

	const $body = $('body');
	const $fontSize = $('#font-size');
	const $fontSizeProgress = $('#font-size-progress');
	const $fontSizeOutput = $('#font-size-output');
	const $font = $('#font');
	const $fontPreviewNote = $('#font-preview-note');
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
	const $previewGroupHeaders = $previewSidebar.find('.preview-sidebar-group-header');
	const $previewSidebarItems = $previewSidebar.find('.preview-sidebar-item');
	const FONT_MAP = window.FONT_MAP || {};
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
	const previewSidebarSchemeMap = {
		'blue-dark': { group: '#183b77', groupBg: '#e8eff8', groupMuted: '#6b7e99', itemHoverBg: '#edf4ff', itemHoverColor: '#183b77', itemActiveBg: '#2784c5', itemActiveHoverBg: '#2378b4', treeOpenBg: '#e8eff8', treeOpenColor: '#183b77' },
		'blue': { group: '#1976d2', groupBg: '#eaf4ff', groupMuted: '#6d89a6', itemHoverBg: '#edf5ff', itemHoverColor: '#1976d2', itemActiveBg: '#1976d2', itemActiveHoverBg: '#1164ac', treeOpenBg: '#eaf4ff', treeOpenColor: '#1976d2' },
		'green': { group: '#2e8332', groupBg: '#ecf8ee', groupMuted: '#6e8b70', itemHoverBg: '#e7f7e8', itemHoverColor: '#2e8332', itemActiveBg: '#43a047', itemActiveHoverBg: '#2e8332', treeOpenBg: '#ecf8ee', treeOpenColor: '#2e8332' },
		'grey': { group: '#475569', groupBg: '#eef2f7', groupMuted: '#64748b', itemHoverBg: '#f1f5f9', itemHoverColor: '#475569', itemActiveBg: '#64748b', itemActiveHoverBg: '#414659', treeOpenBg: '#eef2f7', treeOpenColor: '#475569' },
		'purple': { group: '#5a43a0', groupBg: '#f3ebff', groupMuted: '#7d6c9f', itemHoverBg: '#f1e8ff', itemHoverColor: '#5a43a0', itemActiveBg: '#735cb9', itemActiveHoverBg: '#473385', treeOpenBg: '#f3ebff', treeOpenColor: '#5a43a0' },
		'red': { group: '#dc2626', groupBg: '#feeeee', groupMuted: '#a36f6f', itemHoverBg: '#ffe5e5', itemHoverColor: '#dc2626', itemActiveBg: '#e53935', itemActiveHoverBg: '#de2e2a', treeOpenBg: '#feeeee', treeOpenColor: '#dc2626' },
		'yellow': { group: '#b45309', groupBg: '#fff5e8', groupMuted: '#9b7a4d', itemHoverBg: '#ffeed6', itemHoverColor: '#b45309', itemActiveBg: '#ffae00', itemActiveHoverBg: '#ffa40a', treeOpenBg: '#fff5e8', treeOpenColor: '#b45309' },
		'jnn': { group: '#8b3035', groupBg: '#f7ebec', groupMuted: '#936b6d', itemHoverBg: '#f3dfe0', itemHoverColor: '#8b3035', itemActiveBg: '#a84c51', itemActiveHoverBg: '#692629', treeOpenBg: '#f7ebec', treeOpenColor: '#8b3035' },
		'payday': { group: '#013062', groupBg: '#e8f0fb', groupMuted: '#617a99', itemHoverBg: '#dde8f7', itemHoverColor: '#013062', itemActiveBg: '#013062', itemActiveHoverBg: '#184474', treeOpenBg: '#e8f0fb', treeOpenColor: '#013062' }
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

	function getDefaultFontEntry() {
		return FONT_MAP['open-sans'] || Object.values(FONT_MAP)[0] || { family: '"Open Sans", "Segoe UI", Arial, sans-serif' };
	}

	function getFontEntry(fontValue) {
		const defaultEntry = getDefaultFontEntry();
		const value = String(fontValue || '').trim();
		if (!value) {
			return Object.assign({ key: 'open-sans' }, defaultEntry);
		}

		for (const [key, fontEntry] of Object.entries(FONT_MAP)) {
			if (key === value || fontEntry.family === value) {
				return Object.assign({ key: key }, fontEntry);
			}
		}

		return Object.assign({ key: 'open-sans' }, defaultEntry);
	}

	function applyFontFamily(fontValue) {
		const fontEntry = getFontEntry(fontValue);
		document.documentElement.style.setProperty('--app-font-family', fontEntry.family);
		if (document.body) {
			document.body.style.fontFamily = fontEntry.family;
		}
		$body.css('font-family', fontEntry.family);
		return fontEntry;
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

		const fontSize = parseFloat($fontSize.val()) || 14;
		const min = parseFloat($fontSize.attr('min')) || 10;
		const max = parseFloat($fontSize.attr('max')) || 18;
		const percent = ((fontSize - min) / (max - min)) * 100;
		const box = $fontSizeOutput.outerWidth() / 2;
		const current = (($fontSize.width() * percent) / 100) - box;
		const topPos = 22 + $fontSize.position().top;

		$body.css('font-size', fontSize + 'px');
		$previewRoot.css('font-size', fontSize + 'px');
		$fontSizeProgress.css('width', percent + '%');

		if ($fontSizeOutput.length) {
			$fontSizeOutput.css({
				left: current + 25,
				top: topPos
			}).text(fontSize);
		}
	}

	function updateThemePreview() {
		const colorScheme = $colorSchemeInput.val() || 'blue';
		const bootswatchTheme = $bootswatchTheme.val() || 'default';
		const previewColor = previewColorMap[colorScheme] || previewColorMap.blue;
		const sidebarTheme = previewSidebarSchemeMap[colorScheme] || previewSidebarSchemeMap.blue;
		const sidebarMode = $sidebarColor.val();
		const logoBackground = $logoBackgroundColor.val();
		const themePreview = bootswatchPreviewMap[bootswatchTheme] || bootswatchPreviewMap['default'];
		const themeLabel = $bootswatchTheme.find('option:selected').text();
		const colorLabel = getSelectedThemeLabel(colorScheme);
		const fontEntry = applyFontFamily($font.val());

		$previewThemeName.text(colorLabel + ' / ' + themeLabel);
		$previewRoot.css('font-family', fontEntry.family);
		$fontPreviewNote.css('font-family', fontEntry.family);
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
			$previewGroupHeaders.css({
				background: 'transparent',
				color: sidebarTheme.group
			});
			$previewGroupHeaders.filter('.active').css({
				background: sidebarTheme.groupBg,
				color: sidebarTheme.group
			});
			$previewSidebarItems.css({
				background: 'transparent',
				color: sidebarTheme.groupMuted
			});
			$previewSidebarItems.filter('.tree-open').css({
				background: sidebarTheme.treeOpenBg,
				color: sidebarTheme.treeOpenColor
			});
			$previewSidebarItems.not('.active').off('mouseenter mouseleave').hover(function() {
				if ($(this).hasClass('tree-open')) {
					$(this).css({
						background: sidebarTheme.itemActiveHoverBg,
						color: '#ffffff'
					});
					return;
				}
				$(this).css({
					background: sidebarTheme.itemHoverBg,
					color: sidebarTheme.itemHoverColor
				});
			}, function() {
				if ($(this).hasClass('tree-open')) {
					$(this).css({
						background: sidebarTheme.treeOpenBg,
						color: sidebarTheme.treeOpenColor
					});
					return;
				}
				$(this).css({
					background: 'transparent',
					color: sidebarTheme.groupMuted
				});
			});
			$previewSidebar.find('.preview-sidebar-item.active, .preview-sidebar-item.highlight').css({
				background: sidebarTheme.itemActiveBg,
				color: '#ffffff'
			});
		} else {
			$previewSidebar.css({
				background: '#0f172a',
				color: '#ffffff'
			});
			$previewGroupHeaders.css({
				background: 'rgba(255,255,255,.06)',
				color: '#d7e3f4'
			});
			$previewGroupHeaders.filter('.active').css({
				background: 'rgba(255,255,255,.12)',
				color: '#ffffff'
			});
			$previewSidebarItems.css({
				background: 'transparent',
				color: 'rgba(255,255,255,.72)'
			});
			$previewSidebarItems.filter('.tree-open').css({
				background: 'rgba(255,255,255,.12)',
				color: '#ffffff'
			});
			$previewSidebarItems.not('.active').off('mouseenter mouseleave').hover(function() {
				if ($(this).hasClass('tree-open')) {
					$(this).css({
						background: sidebarTheme.itemActiveHoverBg,
						color: '#ffffff'
					});
					return;
				}
				$(this).css({
					background: 'rgba(255,255,255,.08)',
					color: '#ffffff'
				});
			}, function() {
				if ($(this).hasClass('tree-open')) {
					$(this).css({
						background: 'rgba(255,255,255,.12)',
						color: '#ffffff'
					});
					return;
				}
				$(this).css({
					background: 'transparent',
					color: 'rgba(255,255,255,.72)'
				});
			});
			$previewSidebar.find('.preview-sidebar-item.active, .preview-sidebar-item.highlight').css({
				background: sidebarTheme.itemActiveBg,
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
		const fontEntry = applyFontFamily($(this).val());
		setStylesheetHref('#font-switch', buildAssetUrl(theme_url, 'css/fonts/' + fontEntry.key + '.css', false));
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
