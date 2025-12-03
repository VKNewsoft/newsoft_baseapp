$(document).ready(function() {
	
	let dataTables = '';
	$('#table-result').delegate('.switch', 'click', function()
	{
		var id_module = $(this).data('module-id');
		var id_result = $(this).is(':checked') ? 1 : 2;
		$.ajax({
			type: "POST",
			url: base_url + 'builtin/module/ajaxSwitchModuleStatus',
			data: 'id_module=' + id_module + '&id_result=' + id_result + '&switch_type=aktif&change_module_attr=1&ajax=true',
			dataType: 'text',
			success: function(data) {
				if (data == 'ok') {
					var text = id_result == 1 ? 'Aktif' : 'Non Aktif';
					$('[data-status-text="'+id_module+'"]').html(text);
					
				}
			},
			error: function(xhr) {
				// console.log(xhr);
			}
		});
	})
	
	if ($('#table-result').length) {
		column = $.parseJSON($('#dataTables-column').html());
		url = $('#dataTables-url').text();
		scrolls = $('#dataTables-scrolls').text();
		
		 var settings = {
			"processing": true,
			"serverSide": true,
			"scrollX": true,
			"scrollY": scrolls,
			"ajax": {
				"url": url,
				"type": "POST",
				/* "dataSrc": function (json) {
					// console.log(json)
				} */
			},
			"columns": column
		}
		
		$add_setting = $('#dataTables-setting');
		if ($add_setting.length > 0) {
			add_setting = $.parseJSON($('#dataTables-setting').html());
			for (k in add_setting) {
				settings[k] = add_setting[k];
			}
		}
		
		dataTables =  $('#table-result').DataTable( settings );
	}
	
	$('#table-result').delegate('.btn-delete', 'click', function(e) {
		e.preventDefault();
		id = $(this).attr('data-id');
		$bootbox = bootbox.confirm({
			message: $(this).attr('data-delete-title'),
			callback: function(confirmed) {
				let $button = $bootbox.find('button').prop('disabled', true);
				let $button_submit = $bootbox.find('button.bootbox-accept');
				if (confirmed) {
					$spinner = $('<span class="spinner-border spinner-border-sm me-2"></span>');
					$spinner.prependTo($button_submit);
					$.ajax({
						type: 'POST',
						url: current_url + '/delete',
						data: 'id=' + id,
						dataType: 'json',
						success: function (data) {
							$button.prop('disabled', false);
							$spinner.remove();
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
							$button.prop('disabled', false);
							$spinner.remove();
							show_alert('Error !!!', xhr.responseText, 'error');
							// console.log(xhr.responseText);
						}
					})
					
					return false;
				}
			},
			centerVertical: true
		});
	})
	
	$('body').delegate('.check-all-permission', 'click', function(){
		$(this).parents('form').eq(0).find('input[type="checkbox"]').prop('checked', true);
	});
	
	$('body').delegate('.uncheck-all-permission', 'click', function(){
		$(this).parents('form').eq(0).find('input[type="checkbox"]').prop('checked', false);
	});
	
	$('body').delegate('.generate-permission', 'change', function() {
		if (this.value == 'manual') {
			$('.input-manual').show();
		} else {
			$('.input-manual').hide();
		}
	});
	
	// MODULE PERMISSION
	$('.add-module-permission').click(function(e) {
		$this = $(this);
		e.preventDefault();
		
		if ($this.hasClass('disabled'))
			return;
		
		$bootbox =  bootbox.dialog({
			title: 'Add Permission',
			message: '<form method="post" action="">' + 
						'<div class="row mb-3">' +
						'<div class="col-12">' + 
							'<label class="form-label">Add Permission</label>' + 
							'<select name="generate_permission" class="form-select generate-permission"><option value="crud_all">CRUD ALL</option><option value="crud_own">CRUD Own</option><option value="crud_all_crud_own">CRUD ALL + CRUD Own</option><option value="manual">Manual</option></select><small>CRUD All: create, read_all, update_all, delete_all (jika permission sudah ada, maka tidak akan dibuat). CRUD Own: read_own, update_own, dan delete_own</small>' + 
						'</div>' +
					'</div>' +
					'<div class="row input-manual" style="display:none">' + 
						'<div class="col-12">' + 
							'<div class="mb-2"><label class="form-label">Nama Permission</label>' + 
							'<input class="form-control" type="text" name="nama_permission"/><small>Nama permission sebaiknya diawali dengan create, read, update, atau delete, misal: read_all, read_own, dll. Namun bisa juga dengan nama lain, misal: send_email</small></div>' +
							'<div class="mb-2"><label class="form-label">Judul Permission</label>' + 
							'<input class="form-control" type="text" name="judul_permission"/></div>' +
							'<div class="mb-2"><label class="form-label">Keterangan</label>' + 
							'<textarea class="form-control" name="keterangan"></textarea></div>' +
						'</div>' + 
					'</div>' + 
					'<input type="hidden" name="id_module" value="' + $this.attr('data-id-module') + '">' +
					'</form>',
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
						$button = $bootbox.find('button').prop('disabled', true);
						$button_submit = $bootbox.find('button.submit');
						$button_submit.prepend('<i class="fas fa-circle-notch fa-spin me-2 fa-lg"></i>');
						$button.prop('disabled', true);
						
						$.ajax({
							type: 'POST',
							url: base_url + 'builtin/permission/ajaxAdd',
							data: $bootbox.find('form').serialize() + '&submit=submit',
							dataType: 'text',
							success: function (data) {
								// console.log(data); return false;
								data = $.parseJSON(data);
								
								if (data.status == 'ok') 
								{
									var li = '';
									num_permission = 0;
									$.each(data.data, function (i, v) {
										$.each(v, function(j, val) {
											li += '<li><small>' + val['nama_permission'] + ' (' + val['judul_permission'] + ')</small> <a href="javascript:void(0)" title="Hapus permission ' + val['nama_permission'] + '" class="delete-module-permission" data-url="' + base_url + 'builtin/permission/ajaxDelete" data-id-permission="' + val['id_module_permission'] + '">' + 
												'<i class="ms-2 text-danger fas fa-times"></i>' +
											'</a></li>';
											num_permission++;
										});
									});
									$('.module-permission-container').show();
									$('.module-permission').empty().append(li);
									// console.log(num_permission);
									if (num_permission > 1) {
										$('.module-permission-container').find('.delete-all-module-permission').show();
									}
									$bootbox.modal('hide');
								} else {
									$button_submit.find('i').remove();
									$button.prop('disabled', false);
									list = '<ul class="list-circle">';
									for (k in data.message) {
										list += '<li>' + data.message[k] + '</li>';
									}
									list += '</ul>';
									Swal.fire({
										title: 'Error !!!',
										html: list,
										type: 'error',
										showCloseButton: true,
										confirmButtonText: 'OK'
									})
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
	});
	
	$('.delete-all-module-permission').click(function(e) {
		$this = $(this);
		if ($this.hasClass('disabled'))
			return;
		
		var $bootbox = bootbox.confirm({
			message: 'Hapus semua permission pada module: ' + $('#judul-module').html() + ' ?',
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
			callback: function(result) 
			{
				if(result) {
					$a = $this.parent().parent().find('a');
					$a.addClass('disabled');
					$close_icon = $this.find('.fa-times').hide();
					$loader_icon = $('<i class="fas fa-circle-notch fa-spin"></i>');
					$this.prepend($loader_icon);
					
					$.ajax({
						type: 'POST',
						url: base_url + 'builtin/permission/ajaxDeletePermissionByModule',
						data: 'submit=submit&id=' + $this.attr('data-id-module'),
						success: function(msg) {
							$close_icon.show();
							$loader_icon.remove();
							$a.removeClass('disabled');
							msg = $.parseJSON(msg);
							if (msg.status == 'ok') {
								$('.module-permission').empty();
								$('.role-module-permission-container').children('ul').empty();
								$('.role-module-permission-container').find('a').hide();
								$('.module-permission-container').hide();
								$this.hide();
							} else {
								Swal.fire({
									title: 'Error !!!',
									text: msg.message,
									type: 'error',
									showCloseButton: true,
									confirmButtonText: 'OK'
								})
							}
						},
						error: function(xhr) {
							$close_icon.show();
							$loader_icon.remove();
							$a.removeClass('disabled');
							msg = $.parseJSON(xhr.responseText);
							Swal.fire({
								title: 'Error !!!',
								html: '<strong>Message</strong>: ' + msg.message + '<hr/><strong>File</strong>: ' + msg.file + '<hr/><strong>Line</strong>: ' + msg.line,
								type: 'error',
								showCloseButton: true,
								confirmButtonText: 'OK'
							})
						}
					})
				}
			}
			
		});
	});
	
	$('body').delegate('.delete-module-permission', 'click', function() 
	{
		$this = $(this);
		if ($this.hasClass('disabled'))
			return;
	
		var $bootbox = bootbox.confirm({
			message: "Yakin akan menghapus permission <strong>" + $this.prev().html() + "</strong> ?",
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
					$this.find('.fa-times').hide();
					$this.addClass('disabled');
					$loader_icon = $('<i class="fas fa-circle-notch fa-spin ms-2 text-secondary"></i>');
					$this.prepend($loader_icon);
					$ul = $this.parents('ul').eq(0);
					
					url_delete = $this.attr('data-url');
					$.ajax({
						type: 'POST',
						url: url_delete,
						data: 'id=' + $this.attr('data-id-permission'),
						success: function(msg) {
							msg = $.parseJSON(msg);
							if (msg.status == 'ok') {
								id_permission = $this.attr('data-id-permission');
								$this.parent().fadeOut('fast', function() {
									$('li[data-id-permission="'+ id_permission + '"').remove();
									$(this).remove();
									if ($ul.children().length == 0) {
										$('.module-permission-container').hide();
									}
									
									if ($ul.children().length < 2) {
										$ul.parent().find('.delete-all-module-permission').hide();
									}										
								});
							} else {
								$this.find('.fa-times').show();
								$this.removeClass('disabled');
								$loader_icon.remove();
								Swal.fire({
									title: 'Error !!!',
									text: msg.message,
									type: 'error',
									showCloseButton: true,
									confirmButtonText: 'OK'
								})
							}
						},
						error: function(xhr) {
							$this.find('.fa-times').show();
							$this.removeClass('disabled');
							$loader_icon.remove();
							msg = $.parseJSON(xhr.responseText);
							Swal.fire({
								title: 'Error !!!',
								html: '<strong>Message</strong>: ' + msg.message + '<hr/><strong>File</strong>: ' + msg.file + '<hr/><strong>Line</strong>: ' + msg.line,
								type: 'error',
								showCloseButton: true,
								confirmButtonText: 'OK'
							})
						}
					})
				}
			}
			
		});
	})
	//-- MODULE PERMISSION
	
	// ROLE PERMISSION
	$('.add-role-module-permission').click(function(e){
		$this = $(this);
		e.preventDefault();
		let id = $this.attr('data-id-module');
		let id_role = $this.attr('data-id-role');
		
		msg = '<div class="text-center"><div class="spinner-border text-secondary"></div></div>';
		$bootbox =  bootbox.dialog({
			title: 'Edit Role Permission',
			message: msg,
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
						let form_serialize = $bootbox.find('form').serialize();
						let data = form_serialize + '&submit=submit&id_module=' + id + '&id=' + id_role;
						if (form_serialize) {
							
							$checkbox = $bootbox.find('input[type="checkbox"]').prop('disabled', true);
							$button_submit.prepend('<i class="fas fa-circle-notch fa-spin me-2 fa-lg"></i>');
							$button.prop('disabled', true);
							$.ajax({
								type: 'POST',
								url: base_url + '/builtin/role-permission/ajaxEdit',
								data: data,
								dataType: 'text',
								success: function (data) {

									data = $.parseJSON(data);
									if (data.status == 'ok') 
									{
										let li = '';
										num_permission = 0;
										$checkbox.each(function(i, elm) 
										{
											$elm = $(elm);
											if ($elm.is(':checked')) 
											{					
												id_permission = $elm.val();
												nama_permission = $elm.attr('data-nama-permission');
												judul_role = $('#judul-role-' + id_role).html();
												nama_module = $('#judul-module').html();
												li += '<li data-id-permission="' + id_permission + '"><small>' + nama_permission + '</small>' +
															'<a href="javascript:void(0)" title="Hapus permission ' + nama_permission + ' dari role ' + judul_role + ' pada module ' + nama_module + '" class="delete-role-module-permission" data-url="' + base_url + 'builtin/role-permission/ajaxDeletePermission" data-id-permission="' + id_permission + '">' +
																'<i class="ms-2 text-danger fas fa-times"></i>' +
															'</a>' +  
														'</li>';
												num_permission++;
											}
										});
										$ul = $('#role-permission-' + id_role);
										$ul.empty().append(li);
										if (num_permission > 1) {
											$ul.parent().find('.delete-all-role-module-permission').show();
										}
										$bootbox.modal('hide');
										
									} else {
										$checkbox.prop('disabled', false);
										$button_submit.find('i').remove();
										$button.prop('disabled', false);
										Swal.fire({
											title: 'Error !!!',
											html: data.message,
											type: 'error',
											showCloseButton: true,
											confirmButtonText: 'OK'
										})
									}
								},
								error: function (xhr) {
									// console.log(xhr.responseText);
								}
							})
						} else {
							bootbox.alert('Permission belum dipilih');
						}
						return false;
					}
				}
			}
		});
		let $button = $bootbox.find('button').prop('disabled', true);
		let $button_submit = $bootbox.find('button.submit');
		$.get(base_url + 'builtin/permission/ajaxGetModulePermissionCheckbox?id=' + id + '&id_role=' + id_role, function(html){
			$button.prop('disabled', false);
			$bootbox.find('.modal-body').empty().append(html);
			if ($(html).hasClass('alert')) {
				$button_submit.remove();
			}
		});
	});
	
	$('body').delegate('.delete-role-module-permission', 'click', function() 
	{
		$this = $(this);
		if ($this.hasClass('disabled'))
			return;
		
		var $bootbox = bootbox.confirm({
			message: $this.attr('title') + " ?",
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
				$this.find('i.text-danger').hide();
				$this.attr('disabled', 'disabled');
				
				if(result) {
					$this.find('.fa-times').hide();
					$this.addClass('disabled');
					$loader_icon = $('<i class="fas fa-circle-notch fa-spin ms-2 text-secondary"></i>');
					$this.prepend($loader_icon);
					$ul = $this.parents('ul').eq(0);

					url_delete = $this.attr('data-url');
					$.ajax({
						type: 'POST',
						url: url_delete,
						data: 'id_permission=' + $this.attr('data-id-permission') + '&id_role=' + $this.attr('data-id-role'),
						success: function(msg) {
							msg = $.parseJSON(msg);
							if (msg.status == 'ok') {
								$this.parent().fadeOut('fast', function() {
									$(this).remove();
									if ($ul.children().length < 2) {
										$ul.parent().find('.delete-all-role-module-permission').hide();
									}
								});
							} else {
								$this.find('.fa-times').show();
								$this.removeClass('disabled');
								$loader_icon.remove();
								Swal.fire({
									title: 'Error !!!',
									text: msg.message,
									type: 'error',
									showCloseButton: true,
									confirmButtonText: 'OK'
								})
							}
						},
						error: function(xhr) {
							$this.find('.fa-times').show();
							$this.removeClass('disabled');
							$loader_icon.remove();
							msg = $.parseJSON(xhr.responseText);
							Swal.fire({
								title: 'Error !!!',
								html: '<strong>Message</strong>: ' + msg.message + '<hr/><strong>File</strong>: ' + msg.file + '<hr/><strong>Line</strong>: ' + msg.line,
								type: 'error',
								showCloseButton: true,
								confirmButtonText: 'OK'
							})
						}
					})
				}
			}
			
		});
	})
	
	$('.delete-all-role-module-permission').click(function(e) {
		let $this = $(this);
		if ($this.hasClass('disabled'))
			return;
		
		let id_role = $this.attr('data-id-role');
		let $bootbox = bootbox.confirm({
			message: 'Hapus semua permission role: '+ $('#judul-role-' + id_role).html() + ' pada module: ' + $('#judul-module').html() + ' ?',
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
			callback: function(result) 
			{
				if(result) {
					let $a = $this.parent().find('a').addClass('disabled');
					let $close_icon = $this.find('.fa-times').hide();
					let $loader_icon = $('<i class="fas fa-circle-notch fa-spin"></i>');
					$this.prepend($loader_icon);
					
					$.ajax({
						type: 'POST',
						url: base_url + 'builtin/role-permission/ajaxDeleteRolePermissionByModule',
						data: 'submit=submit&id_role=' + id_role + '&id_module=' + $this.attr('data-id-module'),
						success: function(msg) {
							msg = $.parseJSON(msg);
							if (msg.status == 'ok') {
								$loader_icon.remove();
								$close_icon.show();
								$('#role-permission-' + id_role).empty();
								$this.hide();
							} else {
								$close_icon.show();
								$loader_icon.remove();
								$close_icon.show();
								$a.removeClass('disabled');
								Swal.fire({
									title: 'Error !!!',
									text: msg.message,
									type: 'error',
									showCloseButton: true,
									confirmButtonText: 'OK'
								})
							}
						},
						error: function(xhr) {
							$close_icon.show();
							$loader_icon.remove();
							$close_icon.show();
							$a.removeClass('disabled');
							msg = $.parseJSON(xhr.responseText);
							Swal.fire({
								title: 'Error !!!',
								html: '<strong>Message</strong>: ' + msg.message + '<hr/><strong>File</strong>: ' + msg.file + '<hr/><strong>Line</strong>: ' + msg.line,
								type: 'error',
								showCloseButton: true,
								confirmButtonText: 'OK'
							})
						}
					})
				}
			}
			
		});
	});
	//-- Role Module Permission
});