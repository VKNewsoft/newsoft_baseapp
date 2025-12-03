/**
*	App Name	: Base App Admin
*	Developed by: Newsoft Developer
*	Year		: 2020-2023
*/

jQuery(document).ready(function () {
	
	$(document).delegate('#btn-submit-edit-profile', 'click', function(e) {
		e.preventDefault();
		$button = $(this);
		$form = $button.parents('form').eq(0);
		
		$file = $form.find('input[type="file"]')[0];

		$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
		$button.prop('disabled', true);
		$button.prepend($spinner);
		
		formData = new FormData();
		formData.append($file.name, $file.files[0]);
		data = $form.serializeArray();
		$.each(data, function(i, elm) {
			formData.append(elm.name, elm.value);
		})
		formData.append('submit', 'submit');
		
		$.ajax({
			url: base_url + 'builtin/user/edit?mobile=true',
			method: 'post',
			data: formData,
			processData: false,
			contentType: false,
			success: function( data ) {
				$spinner.remove();
				$button.prop('disabled', false);
				
				data = JSON.parse(data);
				if (data.status == 'ok') {
					Swal.fire(
						'Sukses!',
						'Profil berhasil di ubah!',
						'success'
					);
					// const Toast = Swal.mixin({
					// 			toast: true,
					// 			position: 'top-end',
					// 			showConfirmButton: false,
					// 			timer: 2500,
					// 			timerProgressBar: true,
					// 			iconColor: 'white',
					// 			customClass: {
					// 				popup: 'bg-success text-light toast p-2'
					// 			},
					// 			didOpen: (toast) => {
					// 				toast.addEventListener('mouseenter', Swal.stopTimer)
					// 				toast.addEventListener('mouseleave', Swal.resumeTimer)
					// 			}
					// 		})
							
					// Toast.fire({
					// 	html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i>' + data.message + '</div>'
					// })
				} else {
					bootbox.alert('<div class="d-flex my-2"><span class="text-danger"><i class="fas fa-times-circle me-3" style="font-size:20px"></i></span>' + data.message + '</div>');
				}

			}, error: function (xhr) {
				$spinner.remove();
				$button.prop('disabled', false);
				show_alert('Error !!!', xhr.responseText, 'error');
				// console.log(xhr);
			}
		})
	});
	
	$(document).delegate('#btn-submit-edit-password', 'click', function(e) {
		e.preventDefault();
		$button = $(this);
		$form = $button.parents('form').eq(0);
		
		$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
		$button.prop('disabled', true);
		$button.prepend($spinner);
		
		$.ajax({
			url: base_url + 'builtin/user/edit-password?mobile=true',
			method: 'post',
			data: $form.serialize() + '&submit=submit',
			success: function( data ) {
				$spinner.remove();
				$button.prop('disabled', false);
				
				data = JSON.parse(data);
				if (data.status == 'ok') {
					const Toast = Swal.mixin({
								toast: true,
								position: 'top-end',
								showConfirmButton: false,
								timer: 2500,
								timerProgressBar: true,
								iconColor: 'white',
								customClass: {
									popup: 'bg-success text-light toast p-2'
								},
								didOpen: (toast) => {
									toast.addEventListener('mouseenter', Swal.stopTimer)
									toast.addEventListener('mouseleave', Swal.resumeTimer)
								}
							})
							
					Toast.fire({
						html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i>' + parse_message(data.message) + '</div>'
					})
				} else {
					bootbox.alert('<div class="d-flex my-2"><span class="text-danger"><i class="fas fa-times-circle me-3" style="font-size:20px"></i></span>' + parse_message(data.message) + '</div>');
				}

			}, error: function (xhr) {
				$spinner.remove();
				$button.prop('disabled', false);
				show_alert('Error !!!', xhr.responseText, 'error');
				// console.log(xhr);
			}
		})
	});
});