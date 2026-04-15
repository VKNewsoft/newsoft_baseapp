jQuery(document).ready(function () {
	$logo_login = $('input[name="logo_login"]');
	$image_preview = $logo_login.parent().find('.upload-img-thumb');
	$logo_container = $('.edit-logo-login-container');
	const $previewModal = $('#settingAppImagePreviewModal');
	const $previewTitle = $('#settingAppImagePreviewTitle');
	const $previewStage = $('#settingAppPreviewStage');
	const $previewImage = $('#settingAppPreviewImage');
	let previewModalInstance = null;
	let previewZoomScale = 1;
	const previewZoomActiveScale = 2.75;

	function resetPreviewZoom() {
		previewZoomScale = 1;
		$previewStage.removeClass('is-zooming').addClass('is-zoomable');
		$previewImage.css({
			transform: 'scale(1)',
			transformOrigin: 'center center'
		});
	}

	function openImagePreview(title, imageUrl) {
		if (!$previewModal.length || !$previewImage.length || !imageUrl) {
			return;
		}

		if (!previewModalInstance) {
			previewModalInstance = new bootstrap.Modal($previewModal.get(0), {
				backdrop: false,
				focus: true
			});
		}

		$previewTitle.text(title || 'Preview Gambar');
		$previewImage.attr('src', imageUrl);
		resetPreviewZoom();
		previewModalInstance.show();
	}

	$('.colorpicker').spectrum({
		change: function(color) {
			// alert(color);
			hex_color = color.toHexString(); // #ff0000
			// console.log(hex_color);
			$(this).val(color);
		}
	});
	$(".colorpicker").on('move.spectrum', function(e, tinycolor) {
		$image_preview.css('background-color', tinycolor.toRgbString());
		$logo_container.css('background-color', tinycolor.toRgbString());
		// $(e.target).val(tinycolor.toHexString());
		// console.log(tinycolor.toHexString());
	});
	
	$(".colorpicker").on('hide.spectrum', function(e, tinycolor) {
		$image_preview.css('background-color', tinycolor.toRgbString());
		$logo_container.css('background-color', tinycolor.toRgbString());
	})
	
	$(".list-btn-login a").click(function(e) {

		$this = $(this);
		$ul = $this.parent().parent();
		$elm = $ul.find('a');
		$elm.each(function(i, elm) {
			$elm.find('i').remove();
			$this.append('<i class="fa fa-check check"></i>');
		});
		
		$ul.next().val($this.attr('data-class'));
		// $('#input-color-scheme').val(split);
	});

	$('body').on('click', '.setting-app-preview-trigger', function(e) {
		e.preventDefault();
		openImagePreview($(this).data('preview-title'), $(this).data('preview-image'));
	});

	$('body').on('click', '.setting-app-current-image img, .setting-app-card .upload-img-thumb img', function(e) {
		e.preventDefault();
		const $image = $(this);
		const title = $image.closest('.setting-app-field').find('.form-label').first().text().trim() || 'Preview Gambar';
		openImagePreview(title, $image.attr('src'));
	});

	$previewImage.on('load', function() {
		resetPreviewZoom();
	});

	$previewStage.on('mouseenter', function() {
		if (!$previewImage.attr('src')) {
			return;
		}
		previewZoomScale = previewZoomActiveScale;
		$(this).removeClass('is-zoomable').addClass('is-zooming');
		$previewImage.css({
			transform: 'scale(' + previewZoomScale + ')',
			transformOrigin: '50% 50%'
		});
	});

	$previewStage.on('mousemove', function(e) {
		if (previewZoomScale <= 1) {
			return;
		}

		const bounds = this.getBoundingClientRect();
		const x = ((e.clientX - bounds.left) / bounds.width) * 100;
		const y = ((e.clientY - bounds.top) / bounds.height) * 100;
		$previewImage.css({
			transform: 'scale(' + previewZoomScale + ')',
			transformOrigin: x + '% ' + y + '%'
		});
	});

	$previewStage.on('click', function(e) {
		if (!$previewImage.attr('src')) {
			return;
		}

		const bounds = this.getBoundingClientRect();
		const x = ((e.clientX - bounds.left) / bounds.width) * 100;
		const y = ((e.clientY - bounds.top) / bounds.height) * 100;
		if (previewZoomScale > 1) {
			resetPreviewZoom();
			return;
		}

		previewZoomScale = previewZoomActiveScale;
		$(this).removeClass('is-zoomable').addClass('is-zooming');
		$previewImage.css({
			transform: 'scale(' + previewZoomScale + ')',
			transformOrigin: x + '% ' + y + '%'
		});
	});

	$previewStage.on('mouseleave', function() {
		resetPreviewZoom();
	});

	$previewModal.on('hidden.bs.modal', function() {
		resetPreviewZoom();
		$previewImage.attr('src', '');
	});
});
