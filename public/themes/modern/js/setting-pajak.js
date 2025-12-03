jQuery(document).ready(function () {
	$('#tarif').keyup(function() {
		let value = this.value.replace(/\D/g, '');
		if (value > 100) {
			value = 100;
		} else if (value < 0) {
			value = 0;
		}
		
		this.value = value;
	})
});
