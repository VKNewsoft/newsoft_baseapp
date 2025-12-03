/**
* Font Awesome 5 Icon Picker
* @Copyright Rangga Afriansyah
* @website https://webdev.id/
* @relesase 209-02-07
*/
(function() {
	this.fapicker = function (options = {}) 
	{
		var defaults = {
			iconUrl : '',
			onSelect: function(){}
		}
		
		var options = $.extend({}, defaults, options);
		
		if (!options.iconUrl) {
			console.log('faplugin: iconUrl must be defned');
		}
		
		$elm = $('[tabindex]').attr('data-tabindex', "-1");
		$elm.removeAttr('tabindex');
				
		var $modal = $('.fapicker-modal');
		if ($modal.length == 0) {
			var $modal = $('<div class="wdi-modal fapicker-modal">')
					.append('<div class="wdi-modal-overlay">')
					.appendTo('body');
						
			var $modal_container = $('<div class="wdi-modal-content">').appendTo($modal);
			var $modal_header = $('<div class="wdi-modal-header">').appendTo($modal_container);
			var $modal_body = $('<div class="wdi-modal-body">').appendTo($modal_container);
			var $search_field = $('<input type="search" placeholder="Search..." class="fapicker-search" />')
								.attr('disabled', 'disabled')
								.addClass('disabled')
								.appendTo($modal_header);
			var $close = $('<button class="close"></button>').appendTo($modal_header);
			var $icon_container = $('<div class="fapicker-icons-container">').appendTo($modal_body);
			var $loader = $('<div class="loader-ring">').appendTo($icon_container);
			var $icon_notfound = $('<div class="fapicker-notfound">Icon not found</a>').hide().appendTo($icon_container);
			$.get(options.iconUrl, function (data) {
			
				var list_icon = jsyaml.load(data);
				var icon_name;
				var icons = [];
				
				$loader.hide();
				$search_field.removeClass('disabled').removeAttr('disabled');
				for(icon_name in list_icon) 
				{
					var styles = list_icon[icon_name].styles;
					var i;
					var prefix;
					
					for (i in styles) 
					{
						prefix = '';
						
						switch (styles[i]) {
							case 'solid':
								prefix = 'fas';
								break;
							case 'brands':
								prefix = 'fab';
								break;
							case 'regular':
								prefix = 'far';
								break;
							case 'light':
								prefix = 'far';
								break;
						}
						
						if (prefix) {
							var j;
							var terms = list_icon[icon_name].search.terms.join() + ',' + icon_name;
							var class_name = prefix + ' fa-' + icon_name;
							
							var icon_item = '<a href="javascript:void(0)" title="'+list_icon[icon_name].label+' ('+class_name+')" data-icon="'+class_name+'" data-terms="'+terms+'"><i class="'+prefix+' fa-'+icon_name+'"></i></a>';
							$icon_container.append(icon_item);
						}
					}
				}
				
				// var $icon_filter = $icon_container.find('a[data-terms]');
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
		
		$('.fapicker-icons-container').on('click', 'a', function() 
		{
			options.onSelect(this);
			$modal.fadeOut('fast');
		});
		
		$modal.find('.close').click(function() {
			options.onClose();
			$modal.fadeOut('fast');
		});
	}	
}());