/**
*	App Name	: Base App Admin
*	Developed by: Newsoft Developer
*	Year		: 2020-2023
*/

$(document).ready(function() 
{
	// Prevent hit enter on form kategori/menu
	$('body').delegate('form', 'submit', function(e) {
		e.preventDefault();
		return false;
	})
	
	$('#list-menu').wdiMenuEditor({
		expandBtnHTML   : '<button data-action="expand" class="fa fa-plus" type="button">Expand</button>',
        collapseBtnHTML : '<button data-action="collapse" class="fa fa-minus" type="button">Collapse</button>',
		editBtnCallback : function($list) 
		{
			$bootbox = showForm('edit', $list.data('id'));			
		},
		beforeRemove: function(item, plugin) {
			var $bootbox = bootbox.confirm({
				message: "Yakin akan menghapus menu?<br/>Semua submenu (jika ada) akan ikut terhapus",
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
						
						list_data = $('#list-menu').wdiMenuEditor('serialize');
						menu_tree = JSON.stringify(list_data);
						
						$.ajax({
							type: 'POST',
							url: base_url + 'builtin/menu/ajaxDeleteMenu',
							data: 'id=' + item.attr('data-id') + '&menu_tree=' + menu_tree,
							success: function(msg) {
								msg = $.parseJSON(msg);
								if (msg.status == 'ok') {
									plugin.deleteList(item);
									if ($('#list-menu').find('li').length == 0) {
										$('#list-kategori').find('.list-group-item-primary').click();
									}
								} else {
									Swal.fire({
										title: 'Error !!!',
										text: msg.message,
										icon: 'error',
										showCloseButton: true,
										confirmButtonText: 'OK'
									})
								}
							},
							error: function() {
								
							}
						})
					}
				}
				
			});
		},
		
		// Drag end
		onChange: function(el) {
			list_data = $('#list-menu').wdiMenuEditor('serialize');
			data = JSON.stringify(list_data) + '&id_menu_kategori=' + $('.list-group-item-primary').attr('data-id-kategori');
			$.ajax({
				url: base_url + 'builtin/menu/ajaxUpdateUrut',
				type: 'post',
				dataType: 'json',
				data: 'data=' + data,
				success: function(result) {
					if (result.status == 'error') {
						show_alert('Error !!!', data.message, 'error');
					}
				}, 
				error: function (xhr) {
					show_alert('Error !!!', 'Ajax error, untuk detailnya bisa di cek di console browser', 'error');
					// console.log(xhr);
				}
			});
		}
	});
		
	$('#save-menu').submit(function(e) 
	{
		list_data = $('#list-menu').wdiMenuEditor('serialize');
		data = JSON.stringify(list_data);
		$('#menu-data').empty().text(data);
	})
	
	$(document).on('change', 'select[name="use_icon"]', function(){
		$this = $(this);
		if (this.value == 1) 
		{
			$icon_preview = $this.next().show();
			$this.next().show();
			var calass_name = $icon_preview.find('i').attr('class');
			$this.parent().find('[name="icon_class"]').val(calass_name);
		} else {
			$this.next().hide();
		}
	});
	
	$('#add-menu').click(function(e) 
	{
		e.preventDefault();
		var $add_form = $('#form-edit').clone();
		var id = 'id_' + Math.random();
		$checkbox = $add_form.find('[type="checkbox"]').attr('id', id);
		$checkbox.siblings('label').attr('for', id);
		$bootbox = showForm('add');
	});
	
	function showForm(type='add', id='') 
	{
		var $button = '';
		var $button_submit = '';
			
		$bootbox =  bootbox.dialog({
			title: type == 'edit' ? 'Edit Menu' : 'Tambah Menu',
			message: '<div class="text-center"><div class="spinner-border text-secondary" role="status"></div>',
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
						
						list_data = $('#list-menu').wdiMenuEditor('serialize');
						menu_tree = JSON.stringify(list_data);
						
						$.ajax({
							type: 'POST',
							url: base_url + 'builtin/menu/editMenu',
							data: $form_filled.serialize() + '&menu_tree=' + menu_tree,
							dataType: 'text',
							success: function (data) {
								
								data = $.parseJSON(data);
								
								if (data.status == 'ok') 
								{
									var nama_menu = $form_filled.find('input[name="nama_menu"]').val();
									var id = $form_filled.find('input[name="id"]').val();
									var use_icon = $form_filled.find('select[name="use_icon"]').val();
									var icon_class = $form_filled.find('input[name="icon_class"]').val();
									// edit
									if (id) {
										$menu = $('#list-menu').find('[data-id="'+id+'"]');
										$menu.find('.menu-title:eq(0)').text(nama_menu);
									} 
									// add
									else {
										$menu_container = $('#list-menu').children();
										$menu = $menu_container.children(':eq(0)').clone();
										$menu.find('ol, ul').remove();
										$menu.find('[data-action="collapse"]').remove();
										$menu.find('[data-action="expand"]').remove();
										$menu.attr('data-id', data.id_menu);
										$menu.find('.menu-title').text(nama_menu);
									}
									
									$handler = $menu.find('.dd-handle:eq(0)');
									$handler.find('i').remove();
									
									if (use_icon == 1) {
										$handler.prepend('<i class="'+icon_class+'"></i>');
									}
									
									if (!id) {
										$menu_container.prepend($menu);
									}
										
									$bootbox.modal('hide');
									// bootbox.alert(data.message);
									$('.menu-kategori-container').find('.list-group-item-primary').click();
									Swal.fire({
										title: 'Sukses !!!',
										text: data.message,
										icon: 'success',
										showCloseButton: true,
										confirmButtonText: 'OK'
									})
									
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
		
		$button = $bootbox.find('button').prop('disabled', true);
		$button_submit = $bootbox.find('button.submit');
		$button.prop('disabled', true);
		
		const url = base_url + 'builtin/menu/ajaxGetMenuForm?id='+ id;
		$.get(url, function(result) 
		{
			$button.prop('disabled', false);
			$bootbox.find('.modal-body').html(result);
			$('.select2').select2({theme: 'bootstrap-5', dropdownParent: $(".bootbox")});
			if (type == 'add') {
				id_menu_kategori = $('#list-kategori').find('.list-group-item-primary').attr('data-id-kategori');
				$bootbox.find('select[name="id_menu_kategori"]').val(id_menu_kategori);
			}
		})
		
		return $bootbox;
	}
	
	$(document).on('click', '.icon-preview', function() {
		$bootbox.hide();
		$this = $(this);
		fapicker({
			iconUrl: base_url + 'public/vendors/fontawesome/metadata/icons.yml',
			onSelect: function (elm) {
				$bootbox.show();
				var icon_class = $(elm).data('icon');
				$this.find('i').removeAttr('class').addClass(icon_class);
				$this.parent().find('[name="icon_class"]').val(icon_class);
			},
			onClose: function() {
				$bootbox.show();
			}
		});
	});
	
	// Kategori
	$('.kategori-container').delegate('.kategori-item', 'click', function() 
	{
		var $this = $(this);
		if ($this.hasClass('processing'))
			return false;
		
		var id_kategori = $this.attr('data-id-kategori');
		var $list_menu = $('#list-menu');
		var $group_container = $('.menu-kategori-container');
		var $btn = $('.card-body').find('li');
		
		$btn.addClass('processing');
		$group_container.find('li').removeClass('list-group-item-primary');
		$this.addClass('list-group-item-primary');

		$list_menu.empty();
		$loader = $('<div class="text-center"><div class="spinner-border text-secondary" role="status"></div></div>').appendTo($list_menu);
		$.get(base_url + '/builtin/menu/ajaxGetMenuByIdKategori?id_menu_kategori=' + id_kategori, function(data) 
		{
			$loader.remove();
			$btn.removeClass('processing');
			if (data) {
				$('#list-menu').html(data);
			} else {
				$('#list-menu').html('<div class="alert alert-danger">Data tidak ditemukan</div>');
			}
			
			$('#list-menu').wdiMenuEditor('customInit');
		})
		
	});
	
	$('.kategori-container').delegate('.btn-edit', 'click', function(e) 
	{
		e.stopPropagation();
		if ($(this).hasClass('disabled'))
			return false;
		
		$bootbox = showFormKategori('edit', $(this).parents('li').eq(1).attr('data-id-kategori'));
		return false;
	});
	
	$('.kategori-container').delegate('.btn-remove', 'click', function(e) 
	{
		e.stopPropagation();
		$this = $(this);		
		$li = $this.parents('li').eq(1);
		$li.addClass('processing list-group-item-secondary');
		$li.find('a').prop('disabled', true);
		$li.find('a').addClass('disabled');
		$li.prepend('<i class="fas fa-circle-notch fa-spin me-2 fa-lg me-2 text-muted"></i>');
		
		refresh = false;
		if ($li.hasClass('list-group-item-primary')) {
			refresh = true;
		}
		
		$.ajax({
			type: 'post',
			dataType: 'json',
			url: base_url + 'builtin/menu/ajaxDeleteKategori',
			data: 'id=' + $li.attr('data-id-kategori'),
			success: function (data) {
				
				if (data.status == 'error') {
					show_alert('Error !!!', data.message, 'error');
				} else {
					$li.fadeOut('fast', function() {
						$li.remove();

						$li.remove();
						if (refresh) {
							$('#list-kategori').find('li').eq(0).click();
						}
					});
				}
			},
			error: function(xhr) {
				show_alert('Error !!!', xhr.responseText, 'error');
			}
		})
	});
	
	$('#add-kategori').click(function(e) 
	{
		e.preventDefault();
		$bootbox = showFormKategori();		
	});
	
	function showFormKategori(type='add', id = '') 
	{
		var $button = '';
		var $bootbox = '';
		var $button_submit = '';	
		
		// current_id = $('#list-kategori').find('.list-group-item-primary').attr('data-id-kategori');
		$bootbox =  bootbox.dialog({
			title: type == 'edit' ? 'Edit Kategori' : 'Tambah Kategori',
			message: '<div class="text-center"><div class="spinner-border text-secondary" role="status"></div>',
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
						$button_submit.prepend('<i class="fas fa-circle-notch fa-spin me-2 fa-lg me-2"></i>');
						$button.prop('disabled', true);
						$form_filled = $bootbox.find('form');
						$.ajax({
							type: 'POST',
							url: base_url + 'builtin/menu/ajaxSaveKategori?id=' + id,
							data: $form_filled.serialize(),
							dataType: 'json',
							success: function (data) {
								$button_submit.find('i').remove();
								$button.prop('disabled', false);
								
								if (data.status == 'ok') 
								{
									$bootbox.modal('hide');
									nama_kategori = $form_filled.find('[name="nama_kategori"]').val();

									if(data.aktif == 'Y'){
										colorsnya = '#c0f5bc';
									}else{
										colorsnya = '#f5bcbc';
									}
									
									if (type == 'edit') {
										$('#list-kategori').find('li[data-id-kategori="' + id + '"]').find('.text').html(nama_kategori);
										$('#list-kategori').find('li[data-id-kategori="' + id + '"]').css('background-color', colorsnya);
										show_alert('Sukses !!!', data.message, 'success');
									} else {
										$template = $('#kategori-item-template').clone();
										$template.removeAttr('id');
										$template.attr('data-id-kategori', data.id_kategori);
										$template.find('.text').html(nama_kategori);
										$template.insertBefore('.uncategorized');
										$template.fadeIn('fast');
									}
									
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
			}
		});
				
		$button = $bootbox.find('button').prop('disabled', true);
		$button_submit = $bootbox.find('button.submit');
		$button.prop('disabled', true);
		
		const url = base_url + 'builtin/menu/ajaxGetKategoriForm?id='+ id;
		$.get(url, function(result) 
		{
			$button.prop('disabled', false);
			$bootbox.find('.modal-body').html(result);
		})
		return $bootbox;
	}
	
	//-- Kategori
	
	dragKategori = dragula([document.getElementById('list-kategori-container')], {
		accepts: function (el, target, source, sibling) {
			if (!sibling) return false;
			return true;
		},
		moves: function(el, container, handle) {
		  return  !el.classList.contains('uncategorized')
		}
	});
	
	dragKategori.on('dragend', function(el)
	{	
		urut = [];
		$('#list-kategori').find('li').each(function(i, el) {
			id_kategori = $(el).attr('data-id-kategori');
			if (id_kategori) {
				urut.push(id_kategori);
			}
		});
		
		$.ajax({
			type: 'POST',
			url: base_url + 'builtin/menu/ajaxUpdateKategoriUrut',
			data: 'id=' + JSON.stringify(urut),
			dataType: 'json',
			success: function (data) {
				if (data.status == 'error') {
					show_alert('Error !!!', data.message, 'error');
				}
			},
			error: function (xhr) {
				show_alert('Error !!!', xhr.responseText, 'error');
				// console.log(xhr.responseText);
			}
		})
	});
});