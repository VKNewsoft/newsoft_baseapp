function initSidebarSearch() {
	const search = document.getElementById('sidebarSearch');
	const clear = document.getElementById('sidebarSearchClear');
	const groups = Array.from(document.querySelectorAll('.sidebar-group'));
	const links = document.querySelectorAll('.sidebar-menu a[href]');
	const openedAccordions = new Set();

	function normalize(url) {
		try {
			return url.replace(/\/+$/, '');
		} catch (e) {
			return url;
		}
	}

	function markActiveLinks() {
		if (!links.length) {
			return;
		}

		const currentUrl = normalize(window.location.href);
		const moduleUrl = typeof module_url !== 'undefined' ? normalize(module_url) : null;

		links.forEach(function(link) {
			const href = normalize(link.href || '');
			if (!href) {
				return;
			}

			if (href === currentUrl || (moduleUrl && href === moduleUrl) || (currentUrl.indexOf(href) === 0 && href.length > 0)) {
				link.classList.add('active');
				const group = link.closest('.sidebar-group');
				if (group) {
					const header = group.querySelector('.sidebar-group-header');
					if (header) {
						header.classList.add('active-group');
					}
					group.style.display = '';
				}
			}
		});
	}

	function resetSearchStates() {
		groups.forEach(function(group) {
			group.style.display = '';
			group.querySelectorAll('a, li').forEach(function(item) {
				item.style.display = '';
			});

			group.querySelectorAll('.search-open').forEach(function(element) {
				if (!element.querySelector('.active, [aria-current]')) {
					element.classList.remove('search-open', 'tree-open');
				} else {
					element.classList.remove('search-open');
				}
			});

			group.querySelectorAll('.submenu').forEach(function(submenu) {
				submenu.style.display = submenu.querySelector('.active, [aria-current]') ? 'block' : '';
			});
		});

		openedAccordions.forEach(function(id) {
			const collapseElement = document.getElementById(id);
			if (!collapseElement) {
				return;
			}

			const button = document.querySelector('[data-bs-target="#' + id + '"]');
			if (!collapseElement.querySelector('.active, [aria-current]')) {
				collapseElement.classList.remove('show');
				if (button) {
					button.classList.add('collapsed');
					button.setAttribute('aria-expanded', 'false');
				}
			} else if (button) {
				button.classList.remove('collapsed');
				button.setAttribute('aria-expanded', 'true');
			}
		});

		openedAccordions.clear();
	}

	function initSearch() {
		if (!search || !groups.length) {
			return;
		}

		search.addEventListener('input', function() {
			const query = this.value.trim().toLowerCase();
			if (query === '') {
				resetSearchStates();
				return;
			}

			groups.forEach(function(group) {
				const menu = group.querySelector('.sidebar-menu');
				if (!menu) {
					group.style.display = 'none';
					return;
				}

				let anyVisible = false;
				const items = Array.from(menu.querySelectorAll('a, li'));
				items.forEach(function(item) {
					const text = (item.textContent || item.innerText || '').toLowerCase();
					if (text.indexOf(query) !== -1) {
						item.style.display = '';
						anyVisible = true;

						const collapseAncestor = item.closest('.accordion-collapse');
						if (collapseAncestor && !collapseAncestor.classList.contains('show')) {
							collapseAncestor.classList.add('show');
							openedAccordions.add(collapseAncestor.id);

							const button = document.querySelector('[data-bs-target="#' + collapseAncestor.id + '"]');
							if (button) {
								button.classList.remove('collapsed');
								button.setAttribute('aria-expanded', 'true');
							}
						}

						let parentSubmenu = item.closest('.submenu');
						while (parentSubmenu) {
							parentSubmenu.style.display = 'block';
							const parentItem = parentSubmenu.closest('li');
							if (parentItem) {
								parentItem.classList.add('tree-open', 'search-open');
							}
							parentSubmenu = parentItem ? parentItem.closest('.submenu') : null;
						}
					} else {
						item.style.display = 'none';
					}
				});

				group.style.display = anyVisible ? '' : 'none';
			});
		});

		if (clear) {
			clear.addEventListener('click', function() {
				search.value = '';
				search.dispatchEvent(new Event('input'));
			});
		}
	}

	markActiveLinks();
	if ('requestIdleCallback' in window) {
		requestIdleCallback(initSearch, { timeout: 900 });
	} else {
		setTimeout(initSearch, 120);
	}
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', initSidebarSearch, { once: true });
} else {
	initSidebarSearch();
}
