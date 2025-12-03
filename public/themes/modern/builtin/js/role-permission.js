$(document).ready(function() {

	$(document).delegate('.delete-all-permission', 'click', function() {
		id_role = $(this).attr('data-id-role');
		$bootbox = bootbox.confirm({
			message: $(this).attr('data-delete-title'),
			callback: function(confirmed) {
				if (confirmed) {
					$bootbox.find('button').attr('disabled', 'disabled');
					$bootbox.find('button.bootbox-accept').prepend('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>');
					$.ajax({
						type: 'POST',
						url: base_url + 'builtin/role-permission/ajaxDeleteAllPermission',
						data: 'id_role=' + id_role,
						dataType: 'json',
						success: function (data) {
							$bootbox.modal('hide');
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
									html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> Data berhasil dihapus</div>'
								})
								dataTables.draw();
							} else {
								show_alert('Error !!!', data.message, 'error');
							}
						},
						error: function (xhr) {
							show_alert('Error !!!', xhr.responseText, 'error');
							// console.log(xhr.responseText);
						}
					})	
					return false;
				}
			}
		});
		
		/* id_role
		$.ajax({
			type: 'POST',
			url: base_url + 'builtin/role-permission/ajaxDeleteAllPermission',
			data: 'id_role=' + id_role,
			dataType: 'text',
			success: function (data)
			{
				data = $.parseJSON(data);	
				if (data.status == 'ok') 
				{													
					dataTables.draw(true);
					
				} else {
					
					Swal.fire({
						title: 'Error !!!',
						html: data.message,
						icon: 'error',
						showCloseButton: true,
						confirmButtonText: 'OK'
					})
				}
			},
			error: function (xhr) {
				Swal.fire({
					title: 'Error !!!',
					html: 'Ajax Error, cek console browser',
					icon: 'error',
					showCloseButton: true,
					confirmButtonText: 'OK'
				})
				// console.log(xhr.responseText);
			}
		}) */
	});
	
	$('.assign-all-permission').click(function() {
		assignAll = $(this).is(':checked') ? 'Y' : 'N';
		id_role = $('#id-role').val();
		$.ajax({
			type: 'POST',
			url: base_url + 'builtin/role-permission/ajaxAssignAllPermission',
			data: 'assign_all=' + assignAll + '&id_role=' + id_role,
			dataType: 'text',
			success: function (data) {
				// console.log(data);
				data = $.parseJSON(data);
				
				if (data.status == 'ok') 
				{													
					dataTables.draw(true);
					
				} else {
					
					Swal.fire({
						title: 'Error !!!',
						html: data.message,
						icon: 'error',
						showCloseButton: true,
						confirmButtonText: 'OK'
					})
				}
			},
			error: function (xhr) {
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
	})
	
	$(document).delegate('.assign', 'click', function() {
		id_role = $('#id-role').val();
		id_module_permission = $(this).attr('data-id-module-permission');
		assign = $(this).is(':checked') ? 'Y' : 'N';
		post_data = 'id_role=' + id_role + '&id_module_permission=' + id_module_permission + '&assign=' + assign;
			
		$.ajax({
			type: 'POST',
			url: base_url + 'builtin/role-permission/ajaxAssignPermission',
			data: post_data,
			dataType: 'text',
			success: function (data) {
				// console.log(data);
				data = $.parseJSON(data);
				
				if (data.status == 'ok') 
				{									
					/* const Toast = Swal.mixin({
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
					dataTables.draw(true); */
					
					if (data.hasAllPermission) {
						$('.assign-all-permission').prop('checked', true);
					} else {
						$('.assign-all-permission').prop('checked', false);
					}
					
				} else {
					
					Swal.fire({
						title: 'Error !!!',
						html: data.message,
						icon: 'error',
						showCloseButton: true,
						confirmButtonText: 'OK'
					})
				}
			},
			error: function (xhr) {
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
	
	$('.checkall-module-permission').click(function(){
		$(this).parent().next().find('.permission').prop('checked', true);
	});
	
	$('.uncheckall-module-permission').click(function() {
		$(this).parent().next().find('.permission').prop('checked', false);
	});
	
	$('.check-all').click(function(){
		$('.module-name').prop('checked', true);
		$('.permission').prop('checked', true);
	})
	
	$('.uncheck-all').click(function(){
		$('.module-name').prop('checked', false);
		$('.permission').prop('checked', false);
	})
});