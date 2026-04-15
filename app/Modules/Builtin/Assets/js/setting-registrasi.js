jQuery(document).ready(function () {
	$('select[name="enable"]').change(function(){
		if (this.value == 'N') {
			$('.detail-container').hide();
		} else {
			$('.detail-container').show();
		}
	});
});
