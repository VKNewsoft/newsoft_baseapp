let flatpickr_instance = '';
let osRightPanel = '';
let toastTimer = '';
let $toast = '';
let processing_page = false;

function addBtnConfig() 
{
	$filter = $('#tabel-data_filter');
	if ( $('#setting-barang').length == 0) {
		$filter.append('<button class="setting-barang btn btn-outline-secondary" id="setting-barang"><i class="fas fa-cog"></i></button>');
	}
}

const dataTables_settings = 
{
	"processing": true,
	"serverSide": true,
	"scrollX": true,
	"ajax": {
		"url": '',
		"type": "POST",
		
	},
	"fnRowCallback": function (nRow, aData, iDisplayIndex) {
		var css = aData.status;
		var closing = aData.closing_by;
		// console.log(aData);
		if (css == "VOID" || css == "Y" || css == "FAILED") {
           $(nRow).addClass('void');
        }else{
			if(closing){
				$(nRow).addClass('done');
			}

			if(css == "SETTLEMENT"){
				$(nRow).addClass('done');
			}
		}
		// $(nRow).addClass(css);
	},
	"columns": '',
	'initComplete': function() {
		$('#tabel-data_wrapper').find('.tabel-data').css('opacity', 1);
		$('.dataTables_scrollBody').overlayScrollbars({ scrollbars : {autoHide: 'leave', autoHideDelay: 100}  });
		$('input[type="search"]').focus();
		// $('.dataTables_scrollHead').css('overflow', 'auto');		
	},
	 "bLengthChange": false,
	"bFilter": true,
	"bInfo": false,
	"fixedHeader": false,
	"language": { search: '', searchPlaceholder: "Cari..." },
	"sDom": "<'row'<'col-sm-12'<'form-group'<f>>>>tr<'row'<'col-sm-12'<'pull-left'i><'pull-right'p><'clearfix'>>>"
	// "dom": '<"row"<"col-sm-4"l><"col-sm-4 text-center"p><"col-sm-4"f>>tip'
}
	
function loadDataTables(url, add_btn_config = false) 
{
	const column = $.parseJSON($('#dataTables-column').html());
	dataTables_settings.ajax.url = url
	dataTables_settings.columns = column
	

	let $add_setting = $('#dataTables-setting');
	dataTables_settings.columnDefs = [];
	if ($add_setting.length > 0) {
		add_setting = $.parseJSON($('#dataTables-setting').html());
		for (k in add_setting) {
			dataTables_settings[k] = add_setting[k];
		}
	}
	
	dataTables_settings.drawCallback =  function( settings ) 
	{
		let $search = $('input[type="search"]');
		let search = $search.val();
		if (search.length == 13) {
			
			$detail = $('.detail-barang');
			if ($detail.length == 1) {
				$detail.trigger('click');
				$search.val('').focus().trigger('keyup');
			} else {
				bootbox.alert('Barang tidak ditemukan');
			}
		}
    }

	dataTables =  $('#tabel-data').DataTable( dataTables_settings );
	$filter = $('#tabel-data_filter');
	$input = $filter.find('input').eq(0);
	$filter.find('input').find('label').remove();
	$filter.find('label').hide();
	
	$filter.addClass('input-group flex-nowrap shadow-sm');
	$filter.append($input);
	
	$parent = $filter.parent();
	$parent.css('display', 'flex');
	
	
	if (add_btn_config) {
		addBtnConfig();
	}
	
	if ($parent.find('.btn-close-panel').length == 0) {
		$filter.append('<button class="btn btn-danger btn-close-panel rounded-1 ms-2" style="width:45px; height:40px; display:none; box-shadow: none;"><i class="fas fa-times"></i></button>');
	}
	
	$('.dataTables_paginate').parent().parent().parent().addClass('px-4');
	$('.dataTables_paginate').parent().parent().addClass('px-0');
}

// Untuk tombol spa dan HISTORY browser
function loadContent(url, callback = false) {

	$.get(url, function(data) 
	{
		$html = $('<div>');
		$html.append(data);
		
		$new_content = $html.find('#page-content').hide();
		$('#page-content').stop(true, true).fadeOut('fast', function() 
		{
			$('script[data-type="dynamic-resource-head"], link[data-type="dynamic-resource-head"]').remove();
			$resources = $html.find('[data-type="dynamic-resource-head"]');
			$resources.appendTo($('head'))
			$('#page-content').replaceWith($new_content);

			if ($('#dataTables-url').length) {
				url = $('#dataTables-url').text();
				page_type = $('#page-type').val();
				if ( page_type == 'kasir') {
					url = url + '&id_gudang=' + $('#id-gudang').val() + '&id_jenis_harga=' + $('#id-jenis-harga').val();
				}
				add_config = page_type == 'kasir' ? true : false;
				loadDataTables(url, add_config);
			}

			setTimeout( function() {
				$new_content.fadeIn('fast');
				
				processing_page = false;
			}, 100)

			
			if (callback) {
				callback();
			}
		});
	});
}


let show_login_page = false;
$(document).ajaxStart(function() { Pace.restart(); });
$(document).ajaxSuccess(function(event, request, settings) {
	if (request.getResponseHeader('required-auth') == '1') {
		// document.write('');
		if ( !show_login_page ) {
			let url = base_url + 'login';
			window.location = base_url;
			history.pushState( url,'',url);
			show_login_page = 1;
			
			/* $.get(url, function(data) 
			{
				document.write(data);
				history.pushState( url,'',url);
				show_login_page = 1;
			}); */
		}
	}
});
$(document).ready(function() {
	
	if ($('#setting-kasir').length > 0) {
		setting_kasir = JSON.parse($('#setting-kasir').html());
	}

	$.extend( $.fn.dataTable.defaults, {
		"language": {
			"processing": '<span><span class="spinner-border text-primary" role="status"></span></span>',
			"previous": "Prev"
		}
	});

	bootbox.setDefaults({
		animate: false,
		centerVertical : true
	});
	
	let offcanvas_el = document.getElementById("offcanvasExample");
	let offcanvas = new bootstrap.Offcanvas(offcanvas_el);
		
	$('#close-sidebar').click(function() {
		offcanvas.hide();
	});
	
	$(document).undelegate('.show-left-panel', 'click').delegate('.show-left-panel', 'click', function() {
		$left_panel = $('.left-panel');
		$left_panel.css('z-index', 2);
		$left_panel.hide();
		$left_panel.css('opacity', 1);
		$left_panel.fadeIn('fast');
		$left_panel.css('background', '#FFFFFF');
	})
	
	$(document).undelegate('.btn-close-panel', 'click').delegate('.btn-close-panel', 'click', function() {
		
		$left_panel = $('.left-panel');
		$left_panel.fadeOut('fast', function() {
			$this = $(this);
			$this.css('z-index', 0);
			$this.css('opacity', 1);
			$this.css('background', 'none');
			$this.show();
			
		})
		if ($toast) {
			$toast.hide();
		}
	})
	
	if ($('#dataTables-url').length) {
		
		let query_string = '';
		let add_btn_config = false;
		if ($('#page-type').val() == 'kasir') {
			if (setting_kasir.item_layout == 'grid') {
				dataTables_settings.pageLength = setting_kasir.item_layout_grid_length;
			} else {
				dataTables_settings.pageLength = 10;
			}
						
			query_string = '&id_gudang=' + $('#id-gudang').val() + '&id_jenis_harga=' + $('#id-jenis-harga').val();
			add_btn_config = true;
		}

		if ($('#page-type').val() == 'mobile-length') {
			if (setting_kasir.item_layout == 'grid') {
				dataTables_settings.pageLength = setting_kasir.item_layout_grid_length;
			} else {
				dataTables_settings.pageLength = 10;
			}
		}

		url = $('#dataTables-url').text() + query_string;
		loadDataTables(url, add_btn_config);
	}
	
	window.addEventListener('popstate', function(e) {
		if (e.state) {
			loadContent(e.state);
		}
	});
	
	history.pushState( window.location.href,'',window.location.href);
	
	$(document).delegate('.link-spa', 'click', function(e) {
		e.preventDefault();

		if (processing_page) {
			return false;
		}
		
		processing_page = true;
		if (flatpickr_instance) {
			flatpickr_instance.map(function (instance) {
				instance.destroy();
			})
		}
		
		offcanvas.hide();

		url = $(this).attr('href');
		history.pushState( url,'',url);
		loadContent(url);
	});
	
	/* $(document).delegate('.link-dashboard', 'click', function(e) {
		e.preventDefault();

		if (processing_page) {
			return false;
		}
		
		processing_page = true;
		if (flatpickr_instance) {
			flatpickr_instance.destroy();
		}
		
		offcanvas.hide();

		url = $(this).attr('href');
		history.pushState( url,'',url);
		
		
		$.get(url, function(data) 
		{
			$html = $('<div>');
			$html.append(data);
			
			document.write('');
			document.write(data);
			$('head').find('script').remove();
			$html.find('script').appendTo($('head'));
			
		});
		
	}); */
	
	$(document).delegate('.number', 'keyup', function () {
		this.value = format_ribuan(this.value);
	})
	
	$('.sidebar-mobile').find('.nav-link').click(function() {
		if (processing_page) {
			return false;
		}
		$('.navbar-footer').find('.active').removeClass('active');
	})
	
	$('.navbar-footer').find('.nav-link').click(function() {
		$this = $(this);
		if ($this.hasClass('nav-menu-mobile')) {
			return;
		}
		if (processing_page) {
			return false;
		}
		$('.navbar-footer').find('.active').removeClass('active');
		$this.addClass('active');
	});
})