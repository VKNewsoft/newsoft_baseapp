jQuery(document).ready(function () {
	let dataTables = '';
	
	$('#table-result').delegate('a[data-action="remove-role"]', 'click', function() {
		$this = $(this);
		id_module = $this.attr('data-id-module');
		id_role = $this.attr('data-id-role');
		$.ajax({
			type: 'POST',
			url: base_url + 'builtin/module-role/delete',
			data: 'id_module='+ id_module +'&id_role=' + id_role,
			dataType: 'text',
			success: function (data) {
				// console.log(url_delete);
				data = $.parseJSON(data);
				if (data.status == 'ok') 
				{
					$this.parent().remove();
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
				// console.log(xhr.responseText);
			}
		})
	});
	
	if ($('#table-result').length) {
		column = $.parseJSON($('#dataTables-column').html());
		url = $('#dataTables-url').text();
		
		 var settings = {
			"processing": true,
			"serverSide": true,
			"scrollX": true,
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
	
	$('.toggle-role').click(function(e) {
		$this = $(this);
		if ($this.is(':checked')) {
			
			$this.parent().next().show();
		} else {
			$this.parent().next().hide();
		}
	});
});