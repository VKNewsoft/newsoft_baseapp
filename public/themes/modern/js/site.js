jQuery(document).ready(function () 
{
	$('.has-children').mouseenter(function(){
		if ($('.nav-header').offset().left > 0) { 
			$(this).children('ul').stop(true, true).fadeIn('fast');
		}
	}).mouseleave(function(){
		if ($('.nav-header').offset().left > 0) { 
			$(this).children('ul').stop(true, true).fadeOut('fast');
		}
	});
	
	$('a.has-children').click(function(e){
		if ($('.nav-header').offset().left == 0) {
			$(this).parent().toggleClass('tree-open');
			$(this).next().stop(true, true).slideToggle();
		}
		return false;
	});
	
	$('.has-mobile-children').click(function(){
		$(this).next().stop(true, true).slideToggle();
		return false;
	});
	$('.has-mobile-children').click(function(){
		$(this).parent().toggleClass('tree-open');
	});
	
	$('#mobile-menu-btn').click(function(){
		$('body').toggleClass('mobile-menu-show');
		return false;
	});
	$('.account-menu-btn').click(function()
	{
		if ($('.nav-header').offset().left == 0) {
			$(this).next().stop(true, true).slideToggle();
		} else {
			$(this).next().stop(true, true).fadeToggle();
		}
		return false;
	});
		
	$date_picker = $(".input-datepicker");
	if ($date_picker.length > 0) 
	{
		if ($date_picker.hasClass('default')) {
			$date_picker.flatpickr({
				dateFormat: "d-m-Y"
			});
		}
		else {
			$date_picker.flatpickr({
				dateFormat: "d-m-Y",
				minDate: new Date().fp_incr(-5),
				maxDate: "today",
			});
		}
	}
	
	$('.buy').on('click', function() {
		
	});
	
	$form_loader = $('form.with-loader');
	if ($form_loader.length > 0) 
	{
		$form_loader.append('<div class="loader-icon"><i class="fa fa-circle-o-notch fa-spin fa-fw"></i>&nbsp;Loading...</div>');
		$form_loader.submit(function(e)
		{
			if ($(this).hasClass('disabled'))
				return false;
			
			$(this).addClass('disabled');
			
			$form_loader.find('button[type="submit"], input[type="submit"]')
				.addClass('disabled');
				
			$form_loader.find('.loader-icon').show();
		});
	}
	
	$('.format-ribuan').keyup(function() {
		this.value = format_ribuan(this.value);
	});
	
	function format_ribuan(nilai) {
		nilai = nilai.toString();
		var number_string = nilai.replace(/[^,\d]/g, ''),
			split	= number_string.split(','),
			sisa 	= split[0].length % 3,
			rupiah 	= split[0].substr(0, sisa),
			ribuan 	= split[0].substr(sisa).match(/\d{1,3}/gi);
			
		if (ribuan) {
			separator = sisa ? '.' : '';
			rupiah += separator + ribuan.join('.');
		}
		
		rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
		return rupiah;
	}
	
	$('input').keyup(function(e){
		if ( (e.keyCode >= 48 && e.keyCode <= 57) 
				|| (e.keyCode >= 65 && e.keyCode <= 90)
				|| (e.keyCode >= 96 && e.keyCode <= 105)
			) 
		{
			$(this).nextAll('.alert').remove();
		}
	})
	
	$('#nohp').keyup(function() {
		this.value = this.value.replace(/\D/g, '');
	});
	if ($('.owl-carousel').length > 0) {
		$('.owl-carousel').owlCarousel({
			loop:false,
			margin:0,
			nav:false,
			responsive: {
				  0: {
					items: 1
				  },
				  600: {
					items: 2
				  },
				  800: {
					items: 3
				  },
				  1130: {
					items: 4
				  }
			}
		})
	}
	
});