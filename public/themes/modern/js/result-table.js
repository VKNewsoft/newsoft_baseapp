window.WDIResultTable = {
	getScrollY: function(tableSelector) {
		var fallbackNode = document.getElementById('dataTables-scrolls');
		var explicitHeight = fallbackNode ? parseInt(fallbackNode.textContent, 10) : 0;
		if (explicitHeight && explicitHeight > 0) {
			return explicitHeight + 'px';
		}

		var fallback = 510;

		var $table = $(tableSelector);
		if (!$table.length) {
			return fallback + 'px';
		}

		var $container = $table.closest('.result-table-region, .role-table-wrap, .result-table-wrap, .table-responsive');
		var topOffset = $container.length ? $container.offset().top : $table.offset().top;
		var viewportHeight = window.innerHeight || document.documentElement.clientHeight || fallback;
		var bottomSpacing = 42;
		var availableHeight = Math.floor(viewportHeight - topOffset - bottomSpacing);

		if (availableHeight < 260) {
			availableHeight = fallback;
		}

		return availableHeight + 'px';
	},

	applyScrollBodyHeight: function(dataTable, tableSelector) {
		if (!dataTable || !dataTable.table) {
			return;
		}

		var scrollY = this.getScrollY(tableSelector);
		$(dataTable.table().container()).find('.dataTables_scrollBody').css({
			height: scrollY,
			maxHeight: scrollY
		});

		dataTable.columns.adjust();
	},

	bindResize: function(dataTable, tableSelector, namespace) {
		var self = this;
		var eventName = 'resize' + (namespace ? '.' + namespace : '');

		$(window).off(eventName).on(eventName, function() {
			self.applyScrollBodyHeight(dataTable, tableSelector);
		});
	}
};
