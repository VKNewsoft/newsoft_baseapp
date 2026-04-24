/**
 * Inisialisasi DataTable desktop untuk rangkaian modul project dilakukan
 * terpusat agar list view tetap seragam tanpa mengubah alur backend lama.
 */
(function() {
	function parseOrder(table) {
		var rawOrder = table ? table.getAttribute('data-order') : '';
		if (!rawOrder) {
			return [];
		}

		try {
			return JSON.parse(rawOrder);
		} catch (error) {
			return [];
		}
	}

	function initProjectSuiteTables() {
		if (typeof window.jQuery === 'undefined' || !window.jQuery.fn || !window.jQuery.fn.DataTable) {
			return;
		}

		var isDesktop = window.matchMedia('(min-width: 992px)').matches;
		if (!isDesktop) {
			return;
		}

		window.jQuery('[data-project-datatable]').each(function() {
			var table = this;
			if (window.jQuery.fn.dataTable.isDataTable(table)) {
				window.jQuery(table).DataTable().columns.adjust();
				return;
			}

			var pageLength = parseInt(table.getAttribute('data-page-length') || '10', 10);
			window.jQuery(table).DataTable({
				autoWidth: false,
				scrollX: true,
				pageLength: pageLength > 0 ? pageLength : 10,
				order: parseOrder(table),
				language: {
					search: '',
					searchPlaceholder: 'Cari data...'
				}
			});
		});
	}

	document.addEventListener('DOMContentLoaded', function() {
		var resizeTimer = null;
		initProjectSuiteTables();

		window.addEventListener('resize', function() {
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(initProjectSuiteTables, 140);
		});
	});
})();
