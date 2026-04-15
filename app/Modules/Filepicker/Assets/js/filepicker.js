$(document).ready(function() {
		
	var select_files_mode = false;
	var selected_files = {};

	// var item_per_page = ; // lihat file result.php
	
	list_filter_tgl = {};
	$('.filter-tgl').children().each(function(i, elm) {
		list_filter_tgl[$(elm).attr('value')] = $(elm).text();
	});
	
	// Select Files
	$('.btn-select-files').click(function() {
		$(this).hide();
		$('.btn-cancel-select-files').show();
		$('.btn-delete-checked').show();
		select_files_mode = true;
		$('.list-file-container').addClass('img-blur');
	});
	
	// Cancel Select Files
	$('.btn-cancel-select-files').click(function() 
	{
		$(this).hide();
		$('.btn-select-files').show();
		select_files_mode = false;
		$('.file-checked').hide();
		$('.item-hover').removeClass('item-hover');
		$('.btn-delete-checked').hide();
		$('.btn-delete-checked').find('.num-files').empty();
		selected_files = {};
		$('.list-file-container').removeClass('img-blur');
	});
	
	// Upload files button
	$('.btn-upload-files').click(function() 
	{
		$form_dropzone = $('#dropzone-container');
		if ($form_dropzone.is(':visible')) {
			$form_dropzone.stop(true, true).slideUp('fast');
		} else {
			$form_dropzone.stop(true, true).slideDown('fast');
		}
	});
	
	// Delete all checked
	$('.btn-delete-checked').click(function() {

		$button = $('.nav-util').find('button, select');
		bootbox.confirm({
			title: 'Hapus File',
			message: 'Hapus semua file yang tercentang ( ' + Object.keys(selected_files).length + ' file) ?',
			callback: function(confirmed) {
				if (confirmed) {
					$button.addClass('disabled').attr('disabled', 'disabled');
					$.ajax({
						type : 'post',
						url : filepicker_server_url + 'ajaxDeleteFile',
						data : 'submit=submit&id=' + JSON.stringify(selected_files),
						dataType : 'JSON',
						success : function(data) {
							$button.removeClass('disabled').removeAttr('disabled');
							
							if (data.status == 'error') {
								show_alert('Error !!!', data.message, 'error');
							} else {
								$.each(selected_files, function (i, v) {
									
									$('#file-' + v).fadeOut('fast', function() {
										$(this).click().remove();
									})
								})
							}
						}, error : function (xhr) {
							$button.removeClass('disabled').removeAttr('disabled');
							show_alert('Ajax Error !!!', xhr.responseJSON.message + '<br/><strong>Note</strong>: Silakan cek detail error di console browser', 'error');
							// console.log(xhr );
						}
					})
				}
			}
		});
	});
	
	// Edit image
	$('.gallery-container').delegate('.thumbnail-item', 'click', function() {
		 
		$this = $(this);
		id = get_id_file($this);

		if (select_files_mode) {
			if ($this.hasClass('item-hover')) 
			{
				delete selected_files[id];
				text_num = Object.keys(selected_files).length ? ' ( ' + Object.keys(selected_files).length + ' File)' : '';
				$('.btn-delete-checked').find('.num-files').html(text_num);
				$this.removeClass('item-hover');
				$this.find('.file-checked').hide();
				if ($('.item-hover').length == 0) {
					$('.btn-delete-checked').attr('disabled', 'disabled').addClass('disabled');
				}
			} else {
				$this.addClass('item-hover');
				$this.find('.file-checked').show();
				$('.btn-delete-checked').removeAttr('disabled').removeClass('disabled');
				selected_files[id] = id;
				$('.btn-delete-checked').find('.num-files').html(' ( ' + Object.keys(selected_files).length + ' File)');
			}

			return;
		}
		
		id_image = get_id_file($(this));

		nsdfilepicker.init({
			title : 'Edit File Properties',
			id_file : id_image,
			onSelect: function ($elm) {
				$this.find('.text').hide();
				$this.find('img').remove();
				
				$clone = $ul.find('li').eq(0).clone();
				$clone.find('img').replaceWith($elm.find('img'));
				$ul.append($clone);
				
				meta_file = JSON.parse($elm.find('.meta-file').html());
				$.ajax({
					type : 'post',
					url	: filepicker_server_url + 'ajax-update-file',
					data : 'submit=submit?id_file_picker=' + meta_file.id_file_picker,
					dataType : 'JSON',
					success : function(data) {
						if (data.status == 'error') {
							show_alert('Error !!!', data.message, 'error');
						}
					}, error : function (xhr) {
						show_alert('Ajax Error !!!', 'Silakan cek di console browser', 'error');
						// console.log(xhr);
					}
				});
			}
		});
	});
	
	var page = 1;
	
	/* CHANGE EVENT */
	
	$gallery_container = $('.gallery-container');
	$filter_file = $('.filter-file');
	$filter_tgl = $('.filter-tgl');
	$search = $('.search-file');
	
	// Search Files
	var timer;
	$('body').delegate('.search-file', 'keyup search', function() 
	{
		window.clearTimeout(timer);
		timer = setTimeout(function() 
		{
			$('.gallery-container').find('.error-data-notfound').remove();
			$loader = $('<div class="loader-ring">').appendTo($gallery_container);
			page = 0;
			$ul.empty();
			$.getJSON(filepicker_server_url + 'index?' + url_filter(), function(data) 
			{
				$loader.remove();
				total_item = data.total_item;
				loading_item = data.loaded_item;
				if (data.data.length == 0)
				{
					$('<div class="alert alert-danger error-data-notfound">Data tidak ditemukan</div>').appendTo($('.thumb-container'));
						return;
				}
				
				load_file (data.data);				
			});
		}, 1000);
	});
	
	// Filter Tgl, File
	$('body').delegate('.filter-tgl, .filter-file ', 'change', function() 
	{
		$('.gallery-container').find('.error-data-notfound').remove();
		$loader = $('<div class="loader-ring">').appendTo($gallery_container);
		page = 0;
		$ul.empty();
		$.getJSON(filepicker_server_url + 'index?' + url_filter(), function(data) 
		{
			$loader.remove();
			total_item = data.total_item;
			loading_item = data.loaded_item;
			if (data.data.length == 0)
			{
				$('<div class="alert alert-danger error-data-notfound">Data tidak ditemukan</div>').appendTo($('.gallery-container'));
					return;
			}
			
			load_file (data.data);				
		});
	});
	
	function url_filter() {
		return 'filter_file=' + $filter_file.val() + '&filter_tgl=' + $filter_tgl.val() + '&q=' + $search.val() + '&ajax=true';
	}

	// SCROLL
	var $loading_status = $('.loading-status');
	var $ul = $('#list-file-container');
	var $li = $ul.children().eq(0).clone();
	
	function scroll_item() {
		
		var scrollHeight = $(document).height() - 100;
		var scrollPos = $(window).height() + $(window).scrollTop();
	
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
				loaded = item_per_page * page;
				showing = loaded + item_per_page
				if (total_item < showing) {
					showing = total_item;
				}
				
				$loading_status.find('.loading-item').html(showing);
				$loading_status.find('.total-item').html(total_item);
				
				$.getJSON(filepicker_server_url + 'index?page=' + (page + 1) + '&' + url_filter() + '&item_per_page=' +  item_per_page , function(data) 
				{
					loading_item = data.loaded_item;
					if (data.data.length == 0)
						return;
					
					load_file (data.data);				
				});
			}
		}
	};
	
	$(window).on("scroll", scroll_item);
	
	function load_file(data)
	{
		item = data[0];
			
		$li_new = $li.clone();
		$li_new.hide();
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
		
		$img.attr('src', item.thumbnail.url).on('load', function() {
			$li_new.fadeIn('fast');

		}).on('error', function(e){
			$this = $(this);
			url_alternative_image = filepicker_icon_url + 'file_not_found.png';
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
	
	$toolbox_left = $('.toolbox-left');
	$toolbox_right = $('.toolbox-right');
	$gallery_container = $('.gallery-container');
	html_error_data_notfound = '<div class="alert alert-danger error-data-notfound">Data tidak ditemukan</div>';

	$('#list-file-container').delegate('.btn-delete-file', 'click', function(e) {
		e.stopPropagation();
		$li = $(this).parents('.thumbnail-item').eq(0);
		$.ajax({
			type : 'post',
			url : filepicker_server_url + 'ajaxDeleteFile',
			data : 'submit=submit&id=' + get_id_file($li),
			dataType : 'JSON',
			success : function(data) {
				if (data.status == 'error') {
					show_alert('Error !!!', data.message, 'error');
				} else {
					$li.fadeOut('fast', function() {
						// console.log($li.parent().children().length);
						if ($li.parent().children().length == 1) {
							$li.attr('data-initial-item', 'true');
							$toolbox_left.hide();
							$gallery_container.find('.filter-tgl').children().slice(1).remove();
							$('#list-file-container').parent().append(html_error_data_notfound);
							list_filter_tgl = {};
						} else {
							$(this).remove();
						}
					})
				}
			}, error : function (xhr, status, msg) {
				show_alert('Ajax Error !!!', xhr.responseJSON.message + '<br/><strong>Note</strong>: Silakan cek detail error di console browser', 'error');
				// console.log(xhr );
			}
		})
	});
	
	$gallery_container.delegate('.btn-delete-all', 'click', function(e) 
	{
		$this = $(this);
		$nav_util = $this.parents('.nav-util').eq(0);
		$toolbox_left.children().attr('disabled', 'disabled');
		$toolbox_right.children().attr('disabled', 'disabled');
		$loader = $('<span class="spinner-border spinner-border-sm ms-2 spinner" role="status" aria-hidden="true"></span>').appendTo($this);
		$(window).off('scroll');

		bootbox.confirm({
			message: 'Hapus semua file?',
			callback: function(confirmed) {
				if (confirmed) {
					$.ajax({
						type: 'POST',
						url: filepicker_server_url + 'ajaxDeleteAll',
						data: 'submit=submit&ajax=ajax',
						dataType: 'json',
						success: function(msg) {
							if (msg.status == 'error') {
								Swal.fire({
									title: 'Error !!!',
									text: msg.message,
									icon: 'error',
									showCloseButton: true,
									confirmButtonText: 'OK'
								})
							} else {
								$('.list-file-container').fadeOut('fast', function() {
									$elm = $(this);
									$elm.children().slice(1).remove();
									$elm.children().eq(0).hide().attr('data-initial-item', 'true');
									$elm.show();
									
									$toolbox_left.hide();
									$gallery_container.find('.filter-tgl').children().slice(1).remove();
									$('#list-file-container').parent().append(html_error_data_notfound);
									list_filter_tgl = {};
								});
								$loader.remove();
								$toolbox_left.children().removeAttr('disabled');
								$toolbox_right.children().removeAttr('disabled');
								$(window).on('scroll', scroll_item);
							}
						},
						error: function(xhr) {
							// console.log(xhr);
							$loader.remove();
							$toolbox_left.children().removeAttr('disabled');
							$toolbox_right.children().removeAttr('disabled');
							$(window).on('scroll', scroll_item);
							show_alert('Ajax Error !!!', xhr.responseJSON.message + '<br/><strong>Note</strong>: Silakan cek detail error di console browser', 'error');
							// console.log(xhr );
						}
					})
				} else {
					$loader.remove();
					$toolbox_left.children().removeAttr('disabled');
					$toolbox_right.children().removeAttr('disabled');
					$(window).on('scroll', scroll_item);
				}
			}
		});
	});

	// DROPZONE
	var $preview = $("#dropzone-preview-template").removeAttr('id'),
				$warning = $("#nsd-dz-error");
				
				$clone = $("#dropzone-preview-template").clone().show();
				$clone.attr('id', "");
				
	var target = '.dropzone-area';
	var previewTemplate = $preview.parent().html();
		$preview.remove();
			
			Dropzone.autoDiscover = false;
	var FileDropzone = new Dropzone(target, 
	{
		url: $(target).attr("action"),
		// maxFiles: 1,
		maxFilesize: 20,
		// acceptedFiles: "image/*,application/pdf,.doc,.docx,.xls,.xlsx,.csv,.tsv,.ppt,.pptx,.pages,.odt,.rtf",
		acceptedFiles: "image/png, image/gif, image/jpeg",
		previewTemplate: previewTemplate,
		previewsContainer: "#file-previews",
		clickable: true,
		dictFallbackMessage: "Browser Anda tidak support drag'n'drop file uploads.",
		dictFileTooBig: "Ukuran file Anda terlalu besar: ({{filesize}}MiB). Ukuran maksimal file yang diperkenankan: {{maxFilesize}}MiB.",
		// dictInvalidFileType: "You can't upload files of this type.", // Default: You can't upload files of this type.
		dictResponseError: "Server error code: {{statusCode}}.",
		// dictMaxFilesExceeded: "Maksimal 3 file sekali upload.",
		dictFileSizeUnits: {tb: "TB", gb: "GB", mb: "MB", kb: "KB", b: "b"},
	});
				
	/* function fileType (fileName) {
		var fileType = (/[.]/.exec(fileName)) ? /[^.]+$/.exec(fileName) : undefined;
		return fileType[0];
	} */
	
	$('.dropzone-area').on('dragover', function() {
		$(this).addClass("dropzone-hover");
	})
	
	list_upload = {};
	FileDropzone.on("addedfile", function(file) 
	{
		list_upload[file.name] = file.name;
		$(window).off('scroll');
		
		$(target).removeClass("dropzone-hover");
		$('.preview-container').show();
		$warning.empty();
		
		filename = file.name.toLowerCase();
		ext = filename.split('.').pop();
		mime = file.type;
		if(mime != 'image/png' && mime != 'image/jpg' && mime != 'image/jpeg' && mime != 'image/bmp') {
		  $(file.previewElement).find('img').attr('src', filepicker_server_url + 'ajax-file-icon?mime=' + mime + '&ext=' + ext);
		}
	});

	FileDropzone.on("totaluploadprogress", function (progress) {
		var $prog = $(".progress .determinate");
		if ($prog === undefined || $prog === null) return;

		$prog.css(progress + "%");
		$(".progress-text").html(' - ' + progress + '%');
		
	});

	FileDropzone.on('dragenter', function () {
		// $(target).addClass("dropzone-hover");
	});

	FileDropzone.on('dragleave', function () {
		$(target).removeClass("dropzone-hover");			
	});

	FileDropzone.on('drop', function () {
		$(target).removeClass("-dropzone-hover");	
	});
	
	FileDropzone.on('error', function (file, response) {
		// console.log(file.previewElement);
		$('#previews').children('.dz-success, .dz-complete').remove();
		$(file.previewElement).find('.details').remove();
		$(file.previewElement).find('.dz-error-message')
			.html('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
					  '<strong>Error</strong> ' + response + 
					  '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
					'</div>');
		
	});
	
	FileDropzone.on("success", function(file, response) 
	{
		// $gallery_container.find('.filter-tgl').val('');
		// $gallery_container.find('.search-file').val('');
		// $gallery_container.find('.filter-file').val('').change();
				
		let parsedResponse = JSON.parse(response);
		let meta_file = JSON.parse(parsedResponse.file_info.meta_file);
		
		// File Browser
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
		
		$gallery_container.find('.error-data-notfound').remove();
		$gallery_container.find('.list-file-container').show();
		$gallery_container.find('.toolbox-left').show();
		
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
		
		// Progress bar
		$(file.previewElement).find('.progress-bar').addClass('progress-bar-success');
			
		for (k in parsedResponse.file_info['bulan_upload']) {
			if ( !(k in list_filter_tgl) ) { 
				$('<option value="' + k + '">' + parsedResponse.file_info['bulan_upload'][k] + '</option>').insertAfter($gallery_container.find('.filter-tgl').children().eq(0));
				list_filter_tgl[k] = parsedResponse.file_info['bulan_upload'][k];
			}
		}
				
		$(file.previewElement).fadeOut('fast', function()
		{
			$(this).remove();
			delete list_upload[file.name];
			if (Object.keys(list_upload).length == 0) 
			{
				$gallery_container.find('.filter-tgl').val('');
				$gallery_container.find('.search-file').val('');
				$gallery_container.find('.filter-file').val('').change();
				setTimeout(function(){
					$(window).on("scroll", scroll_item);
				}, 3000);
			}
		});
		
		if ( parsedResponse.status == 'error' ) {
			$warning.html('<div class="alert alert-danger">' + parsedResponse.message + '</div>');
		}
	});
	
	// FUNCTIONS
	function get_id_file($elm) 
	{
		if ($elm.hasClass('thumbnail-item')) {
			id_file = $elm.attr('id');
		} else {
			id_file = $elm.parents('.thumbnail-item').eq(0).attr('id');
		}
		return id_file.replace('file-', '');
	}
});