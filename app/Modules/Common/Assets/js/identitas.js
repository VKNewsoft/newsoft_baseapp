$(document).ready(function() {
	const $selects = $('.select2');
	if (!$selects.length) {
		return;
	}

	if (window.NSModulePerformance && typeof window.NSModulePerformance.initSelect2 === 'function') {
		window.NSModulePerformance.initSelect2($(document));
		return;
	}

	if ($.fn.select2) {
		$selects.select2({ theme: 'bootstrap-5' });
	}
});
