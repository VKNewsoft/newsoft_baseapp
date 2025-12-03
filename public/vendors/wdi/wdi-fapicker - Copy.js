$(document).ready(function() 
{
	$('[data-action="faPicker"]').click(function() 
	{
		'use strict';
		var elm = this;
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

		$close.click(function() {
			$modal.fadeOut('fast');
		});
		
		$.get('http://localhost/tes_iconpicker/fontawesome/metadata/icons.yml', function (data) {
			
			var list_icon = jsyaml.load(data);
			var icon_name;
			var icons = [];
			// console.log(list_icon);
			$loader.remove();
			$search_field.removeClass('disabled').removeAttr('disabled');
			for(icon_name in list_icon) 
			{
				var styles = list_icon[icon_name].styles;
				var i;
				var prefix;
				
				
				/* var icon = {
					prefix: prefix,
					title: list_icon[icon_name].label,
					icon: icon_name
				}
				
				icons[icon_name] = icon; */
				
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
			
			// var $icon_container = $('<div class="fapicker-icons">').appendTo($modal_body);
			
			/* var icon_name;
			for (icon_name in icons) 
			{
				var icon_item = '<a href="javascript:void(0)" title="'+icons[icon_name].title+'" data-icon="fa-'+icon_name+'" data-terms="'+icon_name+'"><i class="'+icons[icon_name].prefix+' fa-'+icon_name+'"></i></a>';
				$icon_container.append(icon_item);
			} */
			
			var $icon_filter = $icon_container.find('a[data-terms]');
			$icon_container.find('a').click(function() 
			{
				var icon_class = $(this).data('icon');
				$(elm).find('i').removeAttr('class').addClass(icon_class);
				$modal.fadeOut('fast');
			});
			
			// Hack the close button on input type search
			$('.fapicker-search').on('input', function() 
			{
				$icon_notfound.hide();
				var val = $.trim(this.value.toLowerCase());
				// console.log(val);
				$icon_filter.removeClass('fapicker-hidden');
				if (val) {
					$icon_filter.not('[data-terms *= "'+val+'"]').addClass("fapicker-hidden");
				}
				
				var $icon_found = $icon_filter.not('.fapicker-hidden');
				if (!$icon_found.length) {
					$icon_notfound.show();
				}
				// console.log($not_hidden.length);
			});
			// console.log(icons);
			// $('.wdi-modal-body').
		})
	});
});
