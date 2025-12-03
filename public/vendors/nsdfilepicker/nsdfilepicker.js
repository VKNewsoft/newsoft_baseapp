/**
* NSD File Picker
* @Copyright Rangga Afriansyah
* @website https://newsoftdev.com
* @relesase 2021-05-31
* @update 2021-08-17
*/


(function($) {
	
	this.nsdfilepicker = {};
	
	var defaults = {
		title : 'File Uploads',
		filter_file : '',
		id_file : '', // ID Image
		id_company : 0, // ID Tenant
		show_title : true,
		use_backdrop : true,
		show_close_btn : true,
		item_per_page : 22,
		margin_style : 'default',
		
		// Url
		server_url : '',
		icon_url : '',
		
		// Dropdown menu filter files
		list_filter : {'image':'Gambar','video':'Video','document':'Dokumen','archive':'Archive'},
		onSelect: function(){}
	}
	
	this.nsdfilepicker.show_alert = function (icon, title, message) 
	{
		Swal.fire({
			title: title,
			text: message,
			icon: icon,
			showCloseButton: true,
			confirmButtonText: 'OK'
		})
	}

	this.nsdfilepicker.setDefaults = function(options) 
	{
		defaults = $.extend( {}, defaults,  options);
	}
	
	this.nsdfilepicker.init = function (options = {})
	{
		var options = $.extend( {}, defaults, options );
		var $id_company = $('#form-barang').find('#id-tenant').val();
		
		if(!$id_company){
			$id_company = 0;
		}
		// console.log('id tenant = '+$id_company);
		
		if (options.server_url == '' || options.icon_url == '') {
			var message = 'nsdfilepicker: server_url and icon_url must not empty !!!';
			nsdfilepicker.show_alert('error', 'Error !!', message);
			// console.log(options);
			return;
		}
		
		// Cleanup Space
		options.filter_file = $.trim(options.filter_file);
		split = options.filter_file.split(' ');
		for (k in split) {
			if ($.trim(split[k] == '')) {
				continue;
			}
			split[k] = $.trim(split[k]);
		}
		options.filter_file = split.join(' ');
		// -- // Cleanup Space
		
		var html_error_data_notfound = '<div class="alert alert-danger error-data-notfound">Data tidak ditemukan</div>';
		
		var options = $.extend({}, defaults, options);
		var $modal = $('<div class="wdi-modal nsd-file-picker-modal">');
	
		if (options.use_backdrop === true) {
			$modal.append('<div class="wdi-modal-overlay">');
		}
		
		$modal.appendTo('body');
				
		var $modal_container = $('<div class="wdi-modal-content ' + options.margin_style + '">').appendTo($modal);
		var $modal_header = $('<div class="wdi-modal-header">').appendTo($modal_container);
		var $modal_body = $('<div class="wdi-modal-body">' +
								'<div class="tab-content" id="myTabContent">' +
									 '<div class="tab-pane fade show active" id="file-browser" role="tabpanel" aria-labelledby="file-browser-tab"></div>' +
									'<div class="tab-pane fade" id="upload" role="tabpanel" aria-labelledby="upload-tab"></div>' +
								'</div>' +
							'</div>').appendTo($modal_container);
		
		// FOOTER
		var $modal_footer = $('<div class="wdi-modal-footer"></div>').appendTo($modal_container);
		if (options.id_file == '') {
			text = 'Pilih File';
			if (options.filter_file != '') 
			{
				split = options.filter_file.split(' ');
				if (split.length == 1) {
					text = 'Pilih ' + options.list_filter[options.filter_file];
				}
			}
			$('<button class="btn btn-primary btn-choose-file disabled" disabled>'+ text +'</button>').appendTo($modal_footer);
		}
		
		$('<button class="btn btn-secondary nav-mobile-file-properties disabled" disabled>File Properties <i class="fas fa-chevron-up arrow-up"></i></button>').appendTo($modal_footer);
		
		// Header
		html_header = '';
		
		if (options.show_title === true) {
			html_header = '<h2 class="modal-title">' + options.title + '</h2>';
		}
		
		// Header Tab Menu
		// console.log(options.id_file);
		tab_title = options.id_file == '' ? 'File Browser' : 'File Preview';
		html_header += '<ul class="nav nav-tabs" id="myTab" role="tablist">' +
							'<li class="nav-item" role="file-picker">' +
								'<button class="nav-link active" id="file-browser-tab" data-bs-toggle="tab" data-bs-target="#file-browser" type="button" role="tab" aria-controls="file browser" aria-selected="false">' + tab_title + '</button>' +
								// '<a class="nav-link active" id="file-browser-tab" data-toggle="tab" href="#file-browser" role="tab" aria-controls="file browser" aria-selected="false">' + tab_title + '</a>' +
							'</li>';
							
							if (options.id_file == '') {
								html_header += '<li class="nav-item" role="file-picker">' +
									'<button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload" type="button" role="tab" aria-controls="upload" aria-selected="true">Upload</button>' +
									// '<a class="nav-link" id="upload-tab" data-toggle="tab" href="#upload" role="tab" aria-controls="upload" aria-selected="true">Upload</a>' +
								'</li>';
							}
							
		html_header += '</ul>';
		
		if (options.show_close_btn === true) {
			html_header += '<button class="close"></button>';
		}
		
		$modal_header.append(html_header);
		
		// Body
		// Upload
		$dropzone = $('<form action="' + options.server_url + 'ajaxUploadFile?id_company='+$id_company+'" class="dropzone" id="file-dropzone">'+
						'<div class="dz-message dz-default needsclick">' +
							'<div><i class="fas fa-cloud-upload-alt"></i></div>' +
							'<div>Drag &amp; Drop File Disini <br> (Hanya Type Images Yang Akan Terupload) <br> <font color="red">Jika berhasil terupload bar progress akan berwarna hijau</font></div>' +
						'</div>' +
						'<div class="preview-container dz-preview uploaded-files" style="display:none">' +
							'<div id="previews">' +
								'<div id="dropzone-template">' +
									'<div class="dropzone-info">' +
										'<div class="uploaded-thumb"><img data-dz-thumbnail/></div>' +
										'<div class="details">' +
											'<div class="file-info">' +
												'<span data-dz-name>fileiopload.png</span>(<span data-dz-size>100Kb</span>)<span class="progress-text"> - 100%</span>' +
											'</div>' +
											'<div class="dz-progress progress"><div class="dz-upload progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width:0" data-dz-uploadprogress></div></div>' +
											'<button class="btn btn-close" style="display:none" data-dz-remove><i class="fas fa-times"></i></button>' +
										'</div>' +
										'<div class="dz-error-message"><span data-dz-errormessage></span></div>' +
									'</div>' +
								'</div>' +
							'</div>' +
						'</div>' +
						'<div id="nsd-dz-error">' +
						'</div>' +
					'</form>')
		
					.appendTo($modal_body.find('#upload'));
		
		// File browser
		var $container = $('<div class="nsd-file-picker-container row"></div>').appendTo($modal_body.find('#file-browser'));
		var $file_container = $('<div class="col-xs-12 col-md-7 col-lg-9 left-panel"></div>').appendTo($container);

		var $meta_container = $('<div class="col-xs-12 col-md-5 col-lg-3 right-panel">' +
									'<div class="meta-container">' +
										'<div class="meta-file-wrapper" style="display:none">'+
											'<div class="meta-file-detail">' +
												'<div class="thumbnail">' +
												
												'</div>' +
												'<div class="img-description">' +
													'<div class="filename"></div>' +
													'<div class="uploaded"></div>' +
													'<div class="filesize"></div>' +
													'<div class="dimension"></div>' +
												'</div>' +
											'</div>' +
											'<div class="file-exists alert alert-danger" style="display:none">' +
												
											'</div>' +
											'<button data-delete-title="" class="btn btn-danger btn-xs btn-delete-file">Delete File</button>' +
											'<hr/>' +
											'<div class="file-properties">' +
												'<div class="row mb-3">' +
													'<div class="col-sm-4">Alt. Text</div>' +
													'<div class="col-sm-8"><input type="text" name="alt_text" class="form-control nsd-alt-text" data-file-prop="alt_text"/></div>' +
												'</div>' +
												'<div class="row mb-3">' +
													'<div class="col-sm-4">Title</div>' +
													'<div class="col-sm-8"><input type="text" name="title" class="form-control nsd-title" data-file-prop="title"/></div>' +
												'</div>' +
												'<div class="row mb-3">' +
													'<div class="col-sm-4">Caption</div>' +
													'<div class="col-sm-8"><textarea name="caption" class="form-control nsd-caption" data-file-prop="caption"></textarea></div>' +
												'</div>' +
												'<div class="row mb-3">' +
													'<div class="col-sm-4">Description</div>' +
													'<div class="col-sm-8"><textarea name="description" class="form-control nsd-description" data-file-prop="description"></textarea></div>' +
												'</div>' +
												'<div class="row">' +
													'<div class="col-sm-4">URL File</div>' +
													'<div class="col-sm-8"><input type="text" name="url_file" class="form-control readonly nsd-url-file" readonly/><button type="button" class="btn btn-xs btn-outline-secondary nsd-btn-copy">Copy Url</button></div>' +
												'</div>' +
											'</div>' +
										'</div>' +
									'</div>' +
								'</div>').appendTo($container);
			var $loader = $('<div class="loader-ring">').appendTo($file_container);
			
			var loading_item, total_item, $loading_status, $search, $filter_file, $ul, $li, $filter_tgl, list_filter_tgl;
			
			// Get Data Filter
			/* list_month = [];
			list_month[1] = 'Januari';
			list_month[2] = 'Februari';
			list_month[3] = 'Maret';
			list_month[4] = 'April';
			list_month[5] = 'Mei';
			list_month[6] = 'Juni';
			list_month[7] = 'Juli';
			list_month[8] = 'Agustus';
			list_month[9] = 'September';
			list_month[10] = 'Oktober';
			list_month[11] = 'November';
			list_month[12] = 'Desember'; */
			
			$.getJSON( options.server_url + 'index?id_company='+$id_company+'&ajax=true&filter_file=' + options.filter_file + '&id_file_picker=' + options.id_file, function (data) 
			{
				$loader.remove();
				item = data.data;
				empty = false; 
				// console.log(options);

				html = '<div class="loading-status text-light bg-success shadow-sm" style="display:none">Showing: <span class="loading-item">' + data.loaded_item + '</span> / <span class="total-item">' + data.total_item + '</span></div>'+
						'<div class="left-panel-menu form-inline">' + 
							'<!--<select class="form-control filter-file" name="filter">';
				
								form_options = '';
								if (options.filter_file != '') 
								{
									split = options.filter_file.split(' ');
									for (k in split) {
										if (options.list_filter != undefined) {
											form_options += '<option value="' + split[k] + '">' + options.list_filter[split[k]] + '</option>';
										}
									}
								}
								
								if (form_options) {
									form_options = '<option value="' + options.filter_file + '" selected>All Filters</option>' + form_options;
								} else {
									form_options = '<option value="" selected>All Files</option>';
									for (k in options.list_filter) {
										form_options += '<option value="' + k + '">' + options.list_filter[k] + '</option>';
									}
								}
								
				html += form_options + '</select>' +
						'<select class="form-control filter-tgl" name="Tanggal"><option value="" selected>Semua Tanggal</option>';
						
						//Tgl
						list_filter_tgl = data.filter_tgl;
						for (k in data.filter_tgl) {
							// split = data.filter_tgl[k].split('-');
							html += '<option value="' + k + '">' + data.filter_tgl[k] + '</option>';
						}
							
						html += '</select>' +
						'<input type="search" class="form-control search-files" name="search" placeholder="Search..."/>-->' +
						
					'</div>' +
					'<div class="thumb-container">' +
						'<ul class="list-file-container">';
						
							if (item.length == 0) {
								empty = true;
								item[0] = new Array;
								item[0]['thumbnail'] = [];
								item[0]['thumbnail']['url'] = '';
								item[0]['file_type'] = 'image';
								item[0]['file_not_found'] = 'false';
								item[0]['url'] = '';
								item[0]['id_file_picker'] = '';
								item[0]['nama_file'] = '';
							}
							
							for (k in item) {
								
								src = item[k]['thumbnail']['url'];
								var img_class = ' file-thumbnail';
								if (item[k]['file_type'] == 'image' && item[k]['file_not_found'] == 'false') {
									var img_class = '';
									if (options.id_file) {
										var src = item[k]['url'];
									}
								} 
								
								style = item[k]['file_type'] == 'image' ? ' style="display:none"' : '';
								initial_item = empty ? 'data-initial-item="true"' : '';
								html += '<li class="nsd-thumbnail-item" id="file-' + item[k]['id_file_picker'] + '" ' + initial_item + '>' +
											'<div class="filename-container"' + style + '>' +
												'<span class="filename">' + item[k].nama_file + '</span>' +
												'<span class="filename-backdrop"></span>' +
											'</div>' + 
											'<div class="img-container">' +
												'<img class="nsd-img-thumbnail' + img_class + '" src="'+ src + '">' +
												'<span class="meta-file" style="display:none">' + JSON.stringify(item[k]) + '</span>' +
											'</div>' +
											'</li>';					
							}
				html += '</ul>' +
				'</div>';
				
				$file_container.html(html);
				
				if (empty) {
					$file_container.find('.left-panel-menu').hide();
					$file_container.find('.list-file-container').hide();
					$(html_error_data_notfound).appendTo($file_container);
							return;
				}

				// For Scrolling
				loading_item = parseInt($modal_body.find('.loading-item').html());
				total_item = parseInt($modal_body.find('.total-item').html());
				$loading_status = $modal_body.find('.loading-status');
				$filter_file = $modal_body.find('.filter-file');
				$filter_tgl = $modal_body.find('.filter-tgl');
				$ul = $modal_body.find('.list-file-container');
				$li = $ul.children().eq(0).clone();
				
				// For Filters
				$search = $modal_body.find('.search-files').eq(0);
				
				if (options.id_file) {
					$('.left-panel').addClass('img-single');
					$('.nsd-thumbnail-item').click();
					$('.meta-container').show();
				}
			}).fail(function( jqxhr, textStatus, error ) {
				// console.log(jqxhr.responseText);
				var err = textStatus + ", " + error;
				// console.log( "Ajax error: " + err );
			});
		
		var $btn_choose_file = $modal.find('.btn-choose-file');
		
		var file_prop = {
			'id' : '',
			'alt_text' : '',
			'title' : '',
			'caption' : '',
			'description' : ''
		}
		
		// Copy link 
		$('.nsd-btn-copy').click(function() {
			var $this = $(this);
			$this.find('.text-success').remove();
			textelm = $(this).prev();
			textelm[0].focus();
			textelm[0].select();
			textelm[0].setSelectionRange(0, 99999);
			document.execCommand("copy");
			$this.parent().find('.text-copied').remove();
			var $text_success = $('<span class="text-copied text-success ms-2">Link copied...</span>').insertAfter($this);
			setTimeout(function() {
				$text_success.fadeOut('fast', function() {
					$text_success.remove();
				});
			}, 3000)
			return false;
		});
		
		// Thumbnail Highlighted
		$('body').delegate('.nsd-thumbnail-item', 'click', function(){
			
			$this = $(this);

			// Button
			$ul = $this.parent();
			$ul.find('.list-highlighted').removeClass('list-highlighted');
			$this.addClass('list-highlighted');
			$thumb = $this.find('.img-container').html();
			meta = JSON.parse($this.find('.meta-file').html());
			$btn_choose_file.attr('disabled');
			$btn_choose_file.addClass('disabled');
			show_btn = false;
			if (options.filter_file == 'image') {
				if (meta.file_type == 'image') {
					show_btn = true;
				} 
			} else if (options.filter_file == '') {
				show_btn = true;
			}
			
			if (show_btn) {
				$btn_choose_file.removeAttr('disabled');
				$btn_choose_file.removeClass('disabled');
			}
			
			// Meta - Right Panel
			$meta_container = $('.meta-file-wrapper').show();
			meta_file = JSON.parse(meta.meta_file)
			$meta_wrapper = $('.meta-file-wrapper');
			$img_desc_thumb = $meta_wrapper.find('.thumbnail');
			$img_desc_thumb.empty().append($thumb);
			
			if (meta.file_type != 'image') {
				$img_desc_thumb.find('.filename').hide();
				// $img_desc_thumb.find('.extension-badge').show();
			}
			// console.log($meta_wrapper);
			$meta_wrapper.find('.btn-delete-file').attr('data-delete-title', 'Hapus permanen file <strong>' + meta.nama_file + '</strong> ?')
									.attr('data-id', meta.id_file_picker);
						
			$meta_wrapper.find('.filename').html(meta.nama_file);
			$meta_wrapper.find('.uploaded').html(meta.tgl_uploaded);
			$meta_wrapper.find('.filesize').html(formatBytes(meta.size));
			if (meta_file.default !== undefined) {
				if (meta_file.default.width !== undefined) {
					$meta_wrapper.find('.dimension').html('w: ' + meta_file.default.width + ' x h: ' + meta_file.default.height );
				}
			}
			
			// File exists
			file_not_found = '';
			if (meta.file_exists.original == 'not_found') {
				file_not_found += '<li>' + meta.nama_file + '</li>';
			}
			for ( k in meta.file_exists.thumbnail ) {
				if (meta.file_exists.thumbnail[k] == 'not_found') {
					file_not_found += '<li>' + meta_file.thumbnail[k]['filename'] + '</li>';
				}
			}
			
			if (file_not_found) {
				$('.file-exists').empty().show().append('File not found: <ul>' + file_not_found + '</ul>');
			} else {
				$('.file-exists').empty().hide();
			}
			
			
			// Properties -Right Panel
			$meta_container.find('.spinner, .result-badge').remove();
			$alt_text = $meta_container.find('.nsd-alt-text');
			$alt_text.val(meta.alt_text);
			if (meta.file_type == 'image') {
				
				$alt_text.parents('.row').eq(0).show();
			} else{
				$alt_text.parents('.row').eq(0).hide();
			}
			$meta_container.find('.nsd-title').val(meta.title);
			$meta_container.find('.nsd-description').val(meta.description);
			$meta_container.find('.nsd-caption').val(meta.caption);
			$meta_container.find('.nsd-url-file').val(meta.url);
			
			file_prop['id'] = $this.attr('id').split('-')[1];
			file_prop['alt_text'] = meta.alt_text;
			file_prop['title'] = meta.title;
			file_prop['caption'] = meta.caption;
			file_prop['description'] = meta.description;
			
			// File properties button
			$nav_properties = $('.nav-mobile-file-properties');
			$nav_properties.removeClass('disabled');
			$nav_properties.removeAttr('disabled');
		});
		
		$('.nsd-alt-text, .nsd-title, .nsd-description, .nsd-caption').blur(function()
		{
			new_value = this.value;
			if ($.trim(new_value) == '') {
				return;
			}
			
			$this = $(this);
			$this.parent().find('.spinner, .result-badge').remove();
			data_file_prop = $this.attr('data-file-prop');
			if (file_prop[data_file_prop] != new_value) {
				
				$loader = $('<span class="spinner-border spinner spinner-border-sm" role="status" aria-hidden="true"></span>').insertAfter($this);
				value = $this.val();
				name = $this.attr('name');
				$.ajax({
					type: 'POST',
					url: options.server_url + 'ajaxUpdateFile',
					data: 'id=' + file_prop['id'] + '&name=' + name + '&value=' + value,
					success: function(message) {
						$loader.remove();
						message = $.parseJSON(message);
						if (message.status == 'ok') 
						{
							file_prop[data_file_prop] = new_value;
							var $meta_file = $this.parents('.meta-container').eq(0).find('.meta-file'); 
							var	meta_string = $meta_file.html();
							var meta_json = JSON.parse(meta_string);
							meta_json[name] = value;
							$meta_file.html(JSON.stringify(meta_json));
							
							$('.list-file-container').find('#file-' + meta_json.id_file_picker).find('.meta-file').html(JSON.stringify(meta_json));
							
							var $result = $('<span class="result-badge text-light bg-success">Saved</span>').hide().insertAfter($this);
							$result.fadeIn('fast', function()
							{
								setTimeout(function(){ 
									$result.fadeOut('fast', function() {
										$result.remove();
									});
								}, 1000);
							})
						} else {
							$('<span class="result-badge bg-danger">Error...</span>').insertAfter($this);
						}
					},
					error: function() {
						
					}
				})
			}
		});
		
		function formatBytes(bytes, decimals) 
		{
		   if(bytes == 0) return '0 Bytes';
		   var k = 1024,
			   dm = decimals || 2,
			   sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
			   i = Math.floor(Math.log(bytes) / Math.log(k));
		   return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
		}
		
		$('body').delegate('.btn-delete-file', 'click', function() 
		{
			var $this = $(this),
				id = $this.attr('data-id'),
				$loader = $('<span class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>').appendTo($this);
				
			$this.addClass('disabled');
			$this.attr('disabled');
			bootbox.confirm({
				message: $this.attr('data-delete-title'),
				callback: function(confirmed) {
					if (confirmed) {
						$.ajax({
							type: 'POST',
							url: options.server_url + 'ajaxDeleteFile',
							data: 'submit=submit&ajax=ajax&id=' + $this.attr('data-id'),
							success: function(msg) {
								msg = $.parseJSON(msg);
								if (msg.status == 'error') {
									Swal.fire({
										title: 'Error !!!',
										text: msg.message,
										type: 'error',
										showCloseButton: true,
										confirmButtonText: 'OK'
									})
								} else {
									$('#file-' + id).fadeOut('fast', function(){
										if ($(this).parent().children().length == 1) {
											$(this).attr('data-initial-item', 'true');
											$modal_body.find('.left-panel-menu').hide();
											$modal_body.find('.filter-tgl').children().slice(1).remove();
											$modal_body.find('.left-panel').append(html_error_data_notfound);
											$btn_choose_file.attr('disabled', 'disabled');
										} else {
											$(this).remove();
										}
									});
									$loader.remove();
									$this.removeClass('disabled');
									$this.removeAttr('disabled');
									$('.meta-file-wrapper').hide();
								}
							},
							error: function() {
								
							}
						})
					} else {
						$loader.remove();
						$this.removeClass('disabled');
						$this.removeAttr('disabled');
					}
				}
			});
		});
		
		$('.btn-choose-file').off( "click").on('click', function() 
		{
			$elm = $('.list-file-container').find('.list-highlighted').find('.img-container');
			options.onSelect($elm.clone());
			$modal.find('.close').click();
		});
		
		$modal.find('.close').click(function() {
			$modal.fadeOut('fast', function() {
				$modal.remove();
			});
		});
		
		$('body').delegate('#myTab button, #myTab a', 'click', function(){
			id = $(this).attr('id');
			if (id == 'upload-tab') {
				$btn_choose_file.attr('disabled');
				$btn_choose_file.addClass('disabled');
			} else {
				$highlighted = $('.list-file-container').find('.list-highlighted');
				if ($highlighted.length > 0) {
					$btn_choose_file.removeAttr('disabled');
					$btn_choose_file.removeClass('disabled');
				}
			}
		});
		
		// MOBILE
		$('body').delegate('.nav-mobile-file-properties', 'click', function() 
		{
			$right_panel = $('.right-panel');
			z_index =$right_panel.css('z-index');

			if (z_index == undefined || z_index == 0 ) {
				$right_panel.hide().css('z-index', 1).fadeIn('fast', function() {
					$('.wdi-modal-footer').find('.arrow-up').css({'transform': 'rotate(180deg)'});
				});
				
			} else {
				$right_panel.fadeOut('fast', function () {
					$('.wdi-modal-footer').find('.arrow-up').css({'transform': ''});
					$right_panel.css('z-index', 0).show(); 
				});
			}
		});
		
		/*
			CHANGE EVENT
		*/
		
		// SCROLL
		var page = 1;
	
		document.addEventListener('scroll', function (event) {
			
			$elm = $(event.target);
			if (!$elm.hasClass('thumb-container')) {
				return;
			}
			
			var scrollHeight = $elm.children('ul').height() - 50;
			var scrollPos = $elm.height() + $elm.scrollTop();

			if( ( (scrollHeight) >= scrollPos) / scrollHeight == 0 ) 
			{
				// console.log('bottom-scroll');
				if ($loading_status.is(':visible')) {
					return;
				}
				
				if (loading_item < total_item) 
				{
					// console.log(page);
					$loading_status.fadeIn('fast');
					loaded = options.item_per_page * page;
					showing = loaded + options.item_per_page
					if (total_item < showing) {
						showing = total_item;
					}
					
					$loading_status.find('.loading-item').html(showing);
					$loading_status.find('.total-item').html(total_item);
					
					$.getJSON(options.server_url + 'index?page=' + (page + 1) + '&' + url_filter() + '&item_per_page=' + options.item_per_page, function(data) 
					{
						loading_item = data.loaded_item;
						if (data.data.length == 0)
							return;
						
						load_file (data.data);										
					});
				}
			}
		}, true);
		
		// Search Files
		var timer;
		$('body').delegate('.search-files', 'keyup search', function() 
		{
			window.clearTimeout(timer);
			timer = setTimeout(function() 
			{
				$modal_body.find('.error-data-notfound').remove();
				$btn_choose_file.removeAttr('disabled');
				$loader = $('<div class="loader-ring">').appendTo($file_container);
				$('.meta-file-wrapper').hide();
				page = 0;
				$ul.empty();
				$.getJSON(options.server_url + 'index?' + url_filter(), function(data) 
				{
					$loader.remove();
					total_item = data.total_item;
					loading_item = data.loaded_item;
					if (data.data.length == 0)
					{
						$btn_choose_file.attr('disabled', 'disabled');
						$(html_error_data_notfound).appendTo($('.thumb-container'));
							return;
					}
					
					load_file (data.data);				
				});
			}, 1000);
		});
		
		// Filter Tgl, File
		$('body').delegate('.filter-tgl, .filter-file ', 'change', function() 
		{
			$modal_body.find('.error-data-notfound').remove();
			$btn_choose_file.removeAttr('disabled');
			$loader = $('<div class="loader-ring">').appendTo($file_container);
			$('.meta-file-wrapper').hide();
			page = 0;
			$ul.empty();
			$.getJSON(options.server_url + 'index?' + url_filter(), function(data) 
			{
				$loader.remove();
				total_item = data.total_item;
				loading_item = data.loaded_item;
				if (data.data.length == 0)
				{
					$btn_choose_file.attr('disabled', 'disabled');
					$(html_error_data_notfound).appendTo($('.thumb-container'));
						return;
				}
				
				load_file (data.data);				
			});
		});
		
		$('body').delegate('.filter-file ', 'change', function() 
		{
			key = $.trim(this.value);
			text = 'Pilih File';
			if (key != '' && key.split(' ').length == 1) {
				text = 'Pilih ' + options.list_filter[key];
			}
			$('.btn-choose-file').html(text);
		})
		
		function url_filter() {
			return 'filter_file=' + $filter_file.val() + '&filter_tgl=' + $filter_tgl.val() + '&q=' + $search.val() + '&ajax=true';
		}

		function load_file(data) 
		{
			item = data[0];
			
			$li_new = $li.clone().hide();
			$li_new.attr('id', 'file-' + item.id_file_picker);
			$li_new.find('.filename-container').hide();
			$li_new.find('.filename').html(item.nama_file);
			$li_new.find('.meta-file').html(JSON.stringify(item));

			$img = $li_new.find('.nsd-img-thumbnail');
			$img.removeClass('file-thumbnail');
			
			if (item.file_type != 'image') {
				$li_new.find('.filename-container').show();
				$img.addClass('file-thumbnail');
			}
			if (item.file_not_found == 'true') {
				$img.addClass('file-thumbnail');
			}
			
			$li_new.appendTo($ul);
			$li_new.show();
			
			$img.attr('src', item.thumbnail.url).on('load', function() {
				

			}).on('error', function(e){
				// console.log(e);
				$this = $(this);
				url_alternative_image = options.icon_url + 'file_not_found.png';
				$.get(url_alternative_image, function() {
					$this.attr('src', url_alternative_image);
					$this.addClass('file-thumbnail');
				}) 
				
			});
			
			data.shift();
			if (data.length > 0) {
				load_file(data);
			} else {
				
				page = page + 1;
				$loading_status.fadeOut('fast');
				
			}
		}
		
		/*
			-- CHANGE EVENT
		*/
		
		// -- Scrolling
		
		// DROPZONE
		var $preview = $("#dropzone-template").removeAttr('id'),
			$warning = $("#nsd-dz-error");
			
			$clone = $("#dropzone-template").clone().show();
			$clone.attr('id', "");

		var target = '.dropzone';
		var previewTemplate = $preview.parent().html();
			$preview.remove();
		
		Dropzone.autoDiscover = false;
		var nsdDropzone = new Dropzone(target, 
		{
			url: $(target).attr("action"),
			// maxFiles: 1,
			// acceptedFiles: "image/*,application/pdf,.doc,.docx,.xls,.xlsx,.csv,.tsv,.ppt,.pptx,.pages,.odt,.rtf",
			acceptedFiles: "image/png, image/jpeg",
			maxFilesize: 20,
			previewTemplate: previewTemplate,
			previewsContainer: "#previews",
			clickable: true,
			dictFallbackMessage: "Browser Anda tidak support drag'n'drop file uploads.",
			dictFileTooBig: "Ukuran file Anda terlalu besar: ({{filesize}}MiB). Ukuran maksimal file yang diperkenankan: {{maxFilesize}}MiB.",
			// dictInvalidFileType: "You can't upload files of this type.", // Default: You can't upload files of this type.
			dictResponseError: "Server error code: {{statusCode}}.",
			dictMaxFilesExceeded: "Maksimal 3 file sekali upload.",
			dictFileSizeUnits: {tb: "TB", gb: "GB", mb: "MB", kb: "KB", b: "b"},
		});
					
		function fileType (fileName) {
			var fileType = (/[.]/.exec(fileName)) ? /[^.]+$/.exec(fileName) : undefined;
			return fileType[0];
		}
		
		$('.dropzone').on('dragover', function() {
			$(this).addClass("dropzone-hover");
		})
		
		
		nsdDropzone.on("addedfile", function(file) 
		{
			$(target).removeClass("dropzone-hover");
			$('.preview-container').show();
			$warning.empty();
			
			filename = file.name.toLowerCase();
			ext = filename.split('.').pop();
			mime = file.type;
			// console.log(file);
			if(mime != 'image/png' && mime != 'image/jpg' && mime != 'image/jpeg' && mime != 'image/bmp') {
			  $(file.previewElement).find('img').attr('src', options.server_url + 'ajax-file-icon?mime=' + mime + '&ext=' + ext);
			}
		});

		nsdDropzone.on("totaluploadprogress", function (progress) {
			var $prog = $(".progress .determinate");
			if ($prog === undefined || $prog === null) return;

			$prog.css(progress + "%");
			$(".progress-text").html(' - ' + progress + '%');
			
		});

		nsdDropzone.on('dragenter', function () {
			// $(target).addClass("dropzone-hover");
		});

		nsdDropzone.on('dragleave', function () {
			$(target).removeClass("dropzone-hover");			
		});

		nsdDropzone.on('drop', function () {
			$(target).removeClass("-dropzone-hover");	
		});
		
		nsdDropzone.on('error', function (file, response) {
			// console.log(response);
			$('#previews').children('.dz-success, .dz-complete').remove();
			$(file.previewElement).find('.details').remove();
			$(file.previewElement).find('.uploaded-thumb').remove();
			$warning
				.html('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
						  '<strong>Error</strong> ' + response + 
						  '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
						'</div>');
			
		});

		nsdDropzone.on("success", function(file, response) 
		{
			let parsedResponse = JSON.parse(response);
			let meta_file = JSON.parse(parsedResponse.file_info.meta_file);

			var $ul = $('.list-file-container');
			var $li_first = $ul.find('li').eq(0);
			var $li = $li_first.clone();
				$li.show();
				$li.removeAttr('data-initial-item');
				$li.removeClass('list-highlighted');
			var $img_cont = $li.find('.img-container');
			
			if ($li_first.attr('data-initial-item') == 'true') {
				$li_first.remove();
			}
			
			$modal_body.find('.error-data-notfound').remove();
			$modal_body.find('.list-file-container').show();
			$modal_body.find('.left-panel-menu').show();
			
			$li.find('.filename').html(parsedResponse.file_info['nama_file']);
			$li.attr('id', 'file-' + parsedResponse.file_info['id_file_picker']);
			$img_cont.find('img').attr('src', parsedResponse.file_info['thumbnail']['url']);
			$img_cont.find('.meta-file').html(JSON.stringify(parsedResponse.file_info));
			
			$img_cont.find('img').removeClass('file-thumbnail');
			if (parsedResponse.file_info['file_type'] != 'image') {
				$img_cont.find('img').addClass('file-thumbnail');
				$li.find('.filename-container').show();
			}
			
			$ul.prepend($li);
			
			// Filter Tanggal
			// Jika mime file yang diupload sesuai filter dan bulan upload belum ada di list filter tanggal
			
			var filter_match = true;
			if (options.filter_file != '') {
				
				filter_match = false;
				split = options.filter_file.split(' ');
				if ( split.indexOf(parsedResponse.file_info['file_type']) != -1 ) {
					filter_match = true;
				}
			}
			
			if (filter_match) {
				
				for (k in parsedResponse.file_info['bulan_upload']) {
					if ( !(k in list_filter_tgl) ) { 
						$('<option value="' + k + '">' + parsedResponse.file_info['bulan_upload'][k] + '</option>').insertAfter($modal_body.find('.filter-tgl').children().eq(0));
						list_filter_tgl[k] = parsedResponse.file_info['bulan_upload'][k];
					}
				}
			}
			
			// -- Filter Tanggal
			
			// Progress bar
			$(file.previewElement).find('.btn-close').show();
			
			$(file.previewElement).find('.progress-bar').addClass('progress-bar-success');

			if ( parsedResponse.status == 'error' ) {
				$warning.html('<div class="alert alert-danger">' + parsedResponse.message + '</div>');
			}
		});
	}
}(jQuery));