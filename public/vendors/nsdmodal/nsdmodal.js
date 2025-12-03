/**
* Data Tables Ajax Modal
* @Copyright Rangga Afriansyah
* @website https://newsoftdev.com
* @relesase 209-02-07
*/
(function() {
	this.nsdmodal = function (options = {}) 
	{
		var defaults = {
			url : '',
		}
		
		var options = $.extend({}, defaults, options);
		
		$elm = $('[tabindex]').attr('data-tabindex', "-1");
		$elm.removeAttr('tabindex');
				
		var $modal = $('.fapicker-modal');
		if ($modal.length == 0) {
			var $modal = $('<div class="nsd-modal fapicker-modal">')
					.append('<div class="nsd-modal-overlay">')
					.appendTo('body');
						
			var $modal_container = $('<div class="nsd-modal-content">').appendTo($modal);
			var $modal_header = $('<div class="nsd-modal-header">'+ options.title+'</div>').appendTo($modal_container);
			var $header_panel = $('<div class="nsd-modal-header-panel header-panel"></div>').appendTo($modal_container);
			var $modal_body = $('<div class="nsd-modal-body">').appendTo($modal_container);
	
			var $icon_container = $('<div class="fapicker-icons-container">').appendTo($modal_body);
			var $loader = $('<div class="text-center" style="height:40px"><<div class="spinner-border text-secondary" role="status"><span class="visually-hidden">Loading...</span></div></div>').appendTo($icon_container);
			var $icon_notfound = $('<div class="fapicker-notfound">Icon not found</a>').hide().appendTo($icon_container);
			
			if (options.width) {
				$modal_container.css('max-width', options.width);
				$modal_container.width(options.width);
			}
			
			$.get(options.url, function (data) 
			{
				var $close = $('<button class="close"></button>').appendTo($modal_header);
				$loader.hide();
				$icon_container.append(data);
				
				column = $.parseJSON($('#nsdmodal-dataTables-column').html());
				url = $('#nsdmodal-dataTables-url').text();
				
				 var nsdmodal_dataTables_settings = {
					"processing": true,
					"serverSide": true,
					"scrollX": true,
					"ajax": {
						"url": url,
						"type": "POST"
					},
					"columns": column,
					"initComplete": function( settings, json ) {
						table.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
							$row = $(this.node());
						} );
					 }
				}
				
				$add_setting = $('#nsdmodal-dataTables-setting');
				if ($add_setting.length > 0) {
					add_setting = $.parseJSON($('#nsdmodal-dataTables-setting').html());
					for (k in add_setting) {
						nsdmodal_dataTables_settings[k] = add_setting[k];
					}
				}
				
				$.extend( $.fn.dataTable.defaults, {
					"language": {
						"processing": '<span><span class="spinner-border text-secondary" role="status"></span></span>',
					}
				});
				
				table =  $('#nsdmodal-table-result').DataTable( nsdmodal_dataTables_settings );
				
				options.action();
			})
		} else {

			var $icon_container = $modal.find('.fapicker-icons-container');
			var $icon_notfound =  $modal.find('.fapicker-notfound');
			// var $icon_filter = $icon_container.find('a[data-terms]');
			$modal.fadeIn('fast');
		}
		
		// Hack the close button on input type search
		var $icon_filter;
		$('.fapicker-search').on('input', function() 
		{
			if (!$icon_filter) {
				$icon_filter = $icon_container.find('a[data-terms]');
			}
			
			$icon_notfound.hide();
			var val = $.trim(this.value.toLowerCase());
			$icon_filter.removeClass('fapicker-hidden');
			if (val) {
				$icon_filter.not('[data-terms *= "'+val+'"]').addClass("fapicker-hidden");
			}
			
			var $icon_found = $icon_filter.not('.fapicker-hidden');
			if (!$icon_found.length) {
				$icon_notfound.show();
			}
		});
		
		$modal.delegate('.close', 'click', function() {
			// $modal.fadeOut('fast');
			$modal.remove();
		});
		
		$('body').delegate('.nsd-modal-overlay', 'click', function() {
			$modal.remove();
		})
		
		return $modal;
	}	
}());