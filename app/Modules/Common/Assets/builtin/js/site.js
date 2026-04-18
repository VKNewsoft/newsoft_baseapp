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

	(function initMobileBottomNav(){
		var media = window.matchMedia('(max-width: 992px)');
		var navTrack = document.getElementById('mobileBottomNavTrack');
		var radialMenu = document.getElementById('mobileRadialMenu');
		var radialWheel = document.getElementById('mobileRadialWheel');
		var radialTitle = document.getElementById('mobileRadialTitle');
		var radialBack = document.getElementById('mobileRadialBack');
		var radialCenterBtn = document.getElementById('mobileRadialCenterBtn');
		var sourceLists = document.querySelectorAll('.sidebar-group .sidebar-menu > ul');
		var navItems = [];
		var radialState = { stack: [], page: 0, pages: [] };
		var swipeStartX = null;

		if (!navTrack || !radialMenu || !radialWheel || !sourceLists.length) {
			return;
		}

		function getText(el) {
			if (!el) return '';
			return (el.textContent || el.innerText || '').replace(/\s+/g, ' ').trim();
		}

		function getIconHtml(anchor) {
			var icon = anchor ? anchor.querySelector('.sidebar-menu-icon') : null;
			return icon ? icon.outerHTML : '<i class="bi bi-grid"></i>';
		}

		function normalizeUrl(url) {
			return (url || '').replace(/\/+$/, '');
		}

		function getNodeData(li) {
			if (!li) return null;
			var anchor = li.querySelector(':scope > a');
			if (!anchor) return null;
			var submenu = li.querySelector(':scope > ul.submenu');
			return {
				id: li.dataset.mobileNavId || ('mobile-nav-' + Math.random().toString(36).slice(2, 10)),
				title: getText(anchor.querySelector('.text')) || getText(anchor),
				href: anchor.getAttribute('href') || '#',
				iconHtml: getIconHtml(anchor),
				hasChildren: !!submenu,
				source: li
			};
		}

		function getChildren(li) {
			var submenu = li ? li.querySelector(':scope > ul.submenu') : null;
			if (!submenu) return [];
			return Array.prototype.slice.call(submenu.children).map(function(childLi){
				return getNodeData(childLi);
			}).filter(Boolean);
		}

		function isActiveNode(li) {
			if (!li) return false;
			return li.classList.contains('highlight') || !!li.querySelector(':scope > a.active, :scope > .submenu a.active');
		}

		function buildNavItems() {
			navItems = [];
			Array.prototype.forEach.call(sourceLists, function(list){
				Array.prototype.forEach.call(list.children, function(li){
					var data = getNodeData(li);
					if (!data) return;
					li.dataset.mobileNavId = data.id;
					data.id = li.dataset.mobileNavId;
					data.active = isActiveNode(li);
					navItems.push(data);
				});
			});
		}

		function renderBottomTabs() {
			navTrack.innerHTML = '';
			navItems.forEach(function(item){
				var el = document.createElement(item.hasChildren ? 'button' : 'a');
				el.className = 'mobile-bottom-nav-item' + (item.hasChildren ? ' has-children' : '') + (item.active ? ' is-active' : '');
				el.setAttribute('data-nav-id', item.id);
				el.setAttribute('aria-label', item.title);
				if (item.hasChildren) {
					el.type = 'button';
				} else {
					el.href = item.href;
				}
				el.innerHTML = item.iconHtml + '<span>' + item.title + '</span>';
				if (item.hasChildren) {
					el.addEventListener('click', function(){
						openRadial(item.source, item.title);
					});
				}
				navTrack.appendChild(el);
			});
		}

		function paginate(items, size) {
			var pages = [];
			for (var i = 0; i < items.length; i += size) {
				pages.push(items.slice(i, i + size));
			}
			return pages.length ? pages : [[]];
		}

		function updateBackButton() {
			radialBack.classList.toggle('is-hidden', radialState.stack.length <= 1);
		}

		function closeRadial() {
			radialMenu.classList.remove('is-open');
			radialMenu.setAttribute('aria-hidden', 'true');
			radialWheel.innerHTML = '';
			radialState = { stack: [], page: 0, pages: [] };
			document.body.classList.remove('mobile-radial-open');
		}

		function goToHref(href) {
			if (href && href !== '#') {
				window.location.href = href;
			}
		}

		function renderRadialPage() {
			var frame = radialState.stack[radialState.stack.length - 1];
			if (!frame) {
				closeRadial();
				return;
			}

			radialState.pages = paginate(frame.items, 6);
			if (radialState.page >= radialState.pages.length) {
				radialState.page = 0;
			}

			var visibleItems = radialState.pages[radialState.page];
			radialTitle.textContent = frame.title || 'Menu';
			radialWheel.innerHTML = '';
			updateBackButton();

			if (!visibleItems.length) {
				return;
			}

			var radius = visibleItems.length <= 4 ? 96 : 112;
			visibleItems.forEach(function(item, index){
				var angle = ((Math.PI * 2) / visibleItems.length) * index - (Math.PI / 2);
				var tx = Math.cos(angle) * radius;
				var ty = Math.sin(angle) * radius;
				var node = document.createElement('button');
				node.type = 'button';
				node.className = 'mobile-radial-node' + (item.hasChildren ? ' is-child' : '');
				node.style.setProperty('--tx', tx.toFixed(2) + 'px');
				node.style.setProperty('--ty', ty.toFixed(2) + 'px');
				node.style.transform = 'translate(' + tx.toFixed(2) + 'px, ' + ty.toFixed(2) + 'px)';
				node.innerHTML = item.iconHtml + '<span>' + item.title + '</span>';
				node.addEventListener('click', function(){
					if (item.hasChildren) {
						radialState.stack.push({
							title: item.title,
							source: item.source,
							items: getChildren(item.source)
						});
						radialState.page = 0;
						renderRadialPage();
						return;
					}

					closeRadial();
					goToHref(item.href);
				});
				radialWheel.appendChild(node);
			});
		}

		function openRadial(sourceLi, title) {
			radialState.stack = [{
				title: title,
				source: sourceLi,
				items: getChildren(sourceLi)
			}];
			radialState.page = 0;
			renderRadialPage();
			radialMenu.classList.add('is-open');
			radialMenu.setAttribute('aria-hidden', 'false');
			document.body.classList.add('mobile-radial-open');
		}

		function stepPage(direction) {
			if (radialState.pages.length <= 1) return;
			radialState.page = radialState.page + direction;
			if (radialState.page < 0) {
				radialState.page = radialState.pages.length - 1;
			}
			if (radialState.page >= radialState.pages.length) {
				radialState.page = 0;
			}
			renderRadialPage();
		}

		radialBack.addEventListener('click', function(){
			if (radialState.stack.length > 1) {
				radialState.stack.pop();
				radialState.page = 0;
				renderRadialPage();
			}
		});

		radialCenterBtn.addEventListener('click', function(){
			var frame = radialState.stack[radialState.stack.length - 1];
			if (!frame) {
				closeRadial();
				return;
			}

			if (radialState.stack.length > 1) {
				radialState.stack.pop();
				radialState.page = 0;
				renderRadialPage();
			} else {
				closeRadial();
			}
		});

		radialMenu.addEventListener('click', function(e){
			if (e.target && e.target.getAttribute('data-radial-close') === '1') {
				closeRadial();
			}
		});

		var viewport = document.getElementById('mobileRadialViewport');
		if (viewport) {
			viewport.addEventListener('touchstart', function(e){
				if (!e.changedTouches || !e.changedTouches.length) return;
				swipeStartX = e.changedTouches[0].clientX;
			}, { passive: true });

			viewport.addEventListener('touchend', function(e){
				if (swipeStartX === null || !e.changedTouches || !e.changedTouches.length) return;
				var diff = e.changedTouches[0].clientX - swipeStartX;
				swipeStartX = null;
				if (Math.abs(diff) < 35) return;
				stepPage(diff < 0 ? 1 : -1);
			}, { passive: true });
		}

		document.addEventListener('keydown', function(e){
			if (!radialMenu.classList.contains('is-open')) return;
			if (e.key === 'Escape') {
				closeRadial();
			} else if (e.key === 'ArrowRight') {
				stepPage(1);
			} else if (e.key === 'ArrowLeft') {
				stepPage(-1);
			}
		});

		function syncActiveTab() {
			var current = normalizeUrl(window.location.href);
			Array.prototype.forEach.call(navTrack.querySelectorAll('.mobile-bottom-nav-item'), function(itemEl){
				itemEl.classList.remove('is-active');
			});

			navItems.forEach(function(item){
				if (item.active || (!item.hasChildren && normalizeUrl(item.href) === current)) {
					var activeEl = navTrack.querySelector('[data-nav-id="' + item.id + '"]');
					if (activeEl) {
						activeEl.classList.add('is-active');
					}
				}
			});
		}

		function mount() {
			if (!media.matches) {
				closeRadial();
				return;
			}
			buildNavItems();
			renderBottomTabs();
			syncActiveTab();
		}

		if (typeof media.addEventListener === 'function') {
			media.addEventListener('change', mount);
		} else if (typeof media.addListener === 'function') {
			media.addListener(mount);
		}

		mount();
	})();
});
