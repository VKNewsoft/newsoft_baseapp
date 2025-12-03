jQuery(document).ready(function () {
	$('select').change( function() {
		$('#form-permission').find('button').click();
	});
	
	$('select[name="generate"]').change(function() {
		if (this.value == 'otomatis') {
			$('.input-container').hide();
		} else {
			$('.input-container').show();
		}
		
	});
	
	const column = $.parseJSON($('#dataTables-column').html());
	const url = $('#dataTables-url').text();
	scrolls = $('#dataTables-scrolls').text();
	
	const settings = {
        "processing": true,
        "serverSide": true,
		"scrollX": true,
		"scrollY": scrolls,
		"ajax": {
            "url": url,
            "type": "POST"
        },
        "columns": column
    }
	
	let $add_setting = $('#dataTables-setting');
	if ($add_setting.length > 0) {
		add_setting = $.parseJSON($('#dataTables-setting').html());
		for (k in add_setting) {
			settings[k] = add_setting[k];
		}
	}
	
	const dataTables =  $('#table-result').DataTable( settings );
	
	$('body').delegate('.edit', 'click', function(e) {
		e.preventDefault;
		$this = $(this);

		$bootbox =  bootbox.dialog({
			title: 'Edit Permission',
			message: '<div class="loader-ring loader"></div>',
			buttons: {
				cancel: {
					label: 'Cancel'
				},
				success: {
					label: 'Submit',
					className: 'btn-success submit',
					callback: function() 
					{
						$bootbox.find('.alert').remove();
						$button_submit.prepend('<i class="fas fa-circle-notch fa-spin me-2 fa-lg"></i>');
						$button.prop('disabled', true);
						$form_filled = $bootbox.find('form');
						url_edit = $form_filled.attr('action');

						$.ajax({
							type: 'POST',
							url: url_edit,
							data: $form_filled.serialize(),
							dataType: 'text',
							success: function (data) {
								// console.log(data);
								data = $.parseJSON(data);
								
								$bootbox.modal('hide');
								if (data.status == 'ok') 
								{									
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
										html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> Data berhasil disimpan</div>'
									})
									dataTables.draw(true);
								} else {
									$button_submit.find('i').remove();
									$button.prop('disabled', false);
									if (data.error_query != undefined) {
										Swal.fire({
											title: 'Error !!!',
											html: data.message,
											icon: 'error',
											showCloseButton: true,
											confirmButtonText: 'OK'
										})
									} else {
										$bootbox.find('.modal-body').prepend('<div class="alert alert-dismissible alert-danger" role="alert">' + data.message + '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
									}
								}
							},
							error: function (xhr) {
								// console.log(xhr.responseText);
							}
						})
						return false;
					}
				}
			}
		});
			

		var $button = $bootbox.find('button').prop('disabled', true);
		var $button_submit = $bootbox.find('button.submit');
		var id = $(this).attr('data-id-permission');
		$.get(current_url + '/ajaxFormEdit?id=' + id, function(html){
			$button.prop('disabled', false);
			$bootbox.find('.modal-body').empty().append(html);
		});
	});
	
	$('body').delegate('.delete', 'click', function() 
	{
		$this = $(this);
		$tr = $this.parents('tr');
		$td = $tr.find('td');
		nama_permission = $td.eq(2).html();
		nama_module = $td.eq(1).html();
									
		var $bootbox = bootbox.confirm({
			message: "Yakin akan menghapus permission <strong>" + nama_permission + "</strong> pada module <strong>" + nama_module + "</strong> ?",
			buttons: {
				confirm: {
					label: 'Yes',
					className: 'btn-success submit'
				},
				cancel: {
					label: 'No',
					className: 'btn-danger'
				}
			},
			callback: function(result) {
				if(result) {
					$button = $bootbox.find('button').prop('disabled', true);
					$button_submit = $bootbox.find('button.submit');
					$button_submit.prepend('<i class="fas fa-circle-notch fa-spin me-2 fa-lg"></i>');
					url_delete = base_url + 'builtin/permission/ajaxDelete';
					
					$.ajax({
						type: 'POST',
						url: url_delete,
						data: 'id=' + $this.attr('data-id-permission'),
						success: function(msg) {
							msg = $.parseJSON(msg);
							$bootbox.modal('hide');
							if (msg.status == 'ok') {
								dataTables.draw(true);
							} else {
								Swal.fire({
									title: 'Error !!!',
									html: msg.message,
									icon: 'error',
									showCloseButton: true,
									confirmButtonText: 'OK'
								})
							}
						},
						error: function(xhr) {
							$bootbox.modal('hide');
							Swal.fire({
								title: 'Error !!!',
								html: 'Ajax Error, cek console browser',
								icon: 'error',
								showCloseButton: true,
								confirmButtonText: 'OK'
							})
							// console.log(xhr.responseText);
						}
					})
					return false;
				}
			}
			
		});
	});
});