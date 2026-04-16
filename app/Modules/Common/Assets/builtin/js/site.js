/**
* Written by: IT-IPI
* Year		: Last Oktober 2023
* Company : Indopasifik Indahtama
*/

jQuery(function () {
	const $body = $('body');
	const initCriticalUI = function() {
		$body.addClass('theme-ready');

		$(document)
			.on('mouseenter', '.has-children', function() {
				$(this).children('ul').stop(true, true).fadeIn('fast');
			})
			.on('mouseleave', '.has-children', function() {
				$(this).children('ul').stop(true, true).fadeOut('fast');
			})
			.on('click', '.has-children', function() {
				var $this = $(this);
				$(this).next().stop(true, true).slideToggle('fast', function() {
					$this.parent().toggleClass('tree-open');
				});
				return false;
			})
			.on('click', '#mobile-menu-btn', function() {
				$body.toggleClass('mobile-menu-show');
				Cookies.set('nsd_adm_mobile', $body.hasClass('mobile-menu-show') ? '1' : '0');
				return false;
			})
			.on('mouseenter', '.sidebar-guide', function() {
				$body.addClass('show-sidebar');
			})
			.on('mouseleave', '.sidebar', function() {
				$body.removeClass('show-sidebar');
			})
			.on('click', '#mobile-menu-btn-right', function() {
				$('header').toggleClass('mobile-right-menu-show');
				return false;
			})
			.on('keyup', '.number-only', function() {
				this.value = this.value.replace(/\D/i, '');
			});

		$('form').each(function() {
			var $form = $(this);
			if (!$form.hasClass('form-shell')) {
				$form.addClass('form-shell');
			}
		});
	};

	const initDeferredUI = function() {
		bootbox.setDefaults({
			animate: false,
			centerVertical : true
		});

		$('table').on('click', '[data-action="delete-data"]', function(e){
			e.preventDefault();
			var $this =  $(this)
				, $form = $this.parents('form:eq(0)');
			bootbox.confirm({
				message: $this.attr('data-delete-title'),
				callback: function(confirmed) {
					if (confirmed) {
						$form.submit();
					}
				},
				centerVertical: true
			});
		});

		if ($.fn.overlayScrollbars && $('.sidebar').length) {
			$('.sidebar').overlayScrollbars({scrollbars : {autoHide: 'leave', autoHideDelay: 100} });
		}

		if ($.fn.dataTable) {
			$.extend($.fn.dataTable.defaults, {
				"language": {
					"processing": '<span><span class="spinner-border text-secondary" role="status"></span></span>',
				}
			});
		}
	};

	initCriticalUI();
	if ('requestIdleCallback' in window) {
		requestIdleCallback(initDeferredUI, { timeout: 1200 });
	} else {
		setTimeout(initDeferredUI, 250);
	}
});
