jQuery(document).ready(function () {
	
	if ($('html').attr('data-bs-theme') == 'dark') {
		bootbox.confirm({
			message: 'Halaman ini hanya dapat memberikan efek pada theme light. Apakah Anda ingin mengubah theme menjadi light?',
			buttons: {
				confirm: {
					label: 'Ya'
				},
				cancel: {
					label: 'Tidak'
				},
			},
			callback: function(result) {
				if (result) {
					$('button[data-theme-value="light"]').trigger('click');
				}
			}
		})
	}
	
	var $body =$('body');
	$('.range-slider').on('input', function(){
		
		 // Cache this for efficiency
		el = $(this);
		$("body").css('font-size', el.val() + 'px');
		$output = el.next("output");
		box = $output.width() / 2;
		
		var init = 25;
		var curr = ( (el.val() - 10 ) * 33 ) - box;
		var top_pos = 22 + el.position().top;
		
		el
		.next("output")
		.css({ 
			left: curr + init,
			top: top_pos
			
		})
		.text(el.val());
	})

	$('#color-scheme').delegate('a', 'click', function() {
		// alert();
		$this = $(this);
		if ($this.children('i').length > 0) {
			return false;
		}
		classes = $this.attr('class');
		split = classes.replace('-theme','');
		
		url = theme_url  + '/css/color-schemes/' + split  + '.css?r=' + Math.floor(Date.now() /10000);
		$('#style-switch').attr('href', url);
		
		$elm = $('#color-scheme, #color-scheme-side');
		$elm.each(function(i, elm) {
			$elm.find('i').remove();
			$elm.find('a.' + classes).append('<i class="fa fa-check theme-check"></i>');
		});
		
				
		$('#input-color-scheme').val(split);
	});
	
	$('#sidebar-color').change(function() {
		url = theme_url + '/css/color-schemes/' + this.value  + '-sidebar.css?r=' + Math.floor(Date.now() /10000);
		// console.log(url);
		$('#style-switch-sidebar').attr('href', url);
		
		$('#sidebar-color').val(this.value);
	});
	
	$('#logo-background-color').change(function() {
		url = theme_url + '/css/color-schemes/' + this.value  + '-logo-background.css?r=' + Math.floor(Date.now() /10000);
		// console.log(url);
		$('#logo-background-color-switch').attr('href', url);
	});
	
	$('#bootswatch-theme').change(function() {
		url = base_url + '/public/vendors/bootswatch/' + this.value  + '/bootstrap.min.css?r=' + Math.floor(Date.now() /10000);
		// console.log(url);
		$('#style-switch-bootswatch').attr('href', url);
	});
	
	
	$('#font').change(function() {
		url = theme_url + '/css/fonts/' + $(this).val() + '.css';
		$('#font-switch').attr('href', url);
		$('#font').val(this.value);
	});
	
	$('#font-size').on('change', function() {
		$('body').css('font-size', this.value);
	});
	
	$('#form-setting').submit(function(e) {
		e.preventDefault();
		$btn = $(this).find('button[type="submit"]').addClass('disabled').css('float', 'left');
		
		$btn.attr('disabled', 'disabled');
		$loader = $('<div class="spinner-submit fa-3x"><i class="fas fa-circle-notch fa-spin"></i></div>').insertAfter($btn);
		$.ajax({
			'url' : module_url
			, 'method': 'POST'
			, 'data': $(this).serialize() + '&submit=submit&ajax=ajax'
			, 'success' : function(data) {
				// console.log(data);
				msg = $.parseJSON(data);
				title = msg.status == 'ok' ? 'SUKSES !!!' : 'ERROR !!!';
				icon = msg.status == 'ok' ? 'success' : 'error';
				Swal.fire({
					text: msg.message,
					title: title,
					icon: icon,
					showCloseButton: true,
					confirmButtonText: 'OK'
				})
				$btn.removeAttr('disabled').removeClass('disabled');
				$loader.remove();
			}, 'error' : function(xhr) {
				Swal.fire({
					text: 'Request error, lihat log console',
					title: 'Error !!!',
					icon: 'error',
					showCloseButton: true,
					confirmButtonText: 'OK'
				})
				// console.log(xhr);
			}
		})
		
	});
});