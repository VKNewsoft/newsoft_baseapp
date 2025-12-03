/**
*	App Name	: Base App Admin
*	Developed by: Newsoft Developer
*	Year		: 2020-2023
*/

jQuery(document).ready(function () {
	
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
			"columns": column,
			"initComplete": function( settings, json ) {
				table.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
					$row = $(this.node());
					/* this
						.child(
							$(
								'<tr>'+
									'<td>'+rowIdx+'.1</td>'+
									'<td>'+rowIdx+'.2</td>'+
									'<td>'+rowIdx+'.3</td>'+
									'<td>'+rowIdx+'.4</td>'+
								'</tr>'
							)
						)
						.show(); */
				} );
			 }
		}
		
		$add_setting = $('#dataTables-setting');
		if ($add_setting.length > 0) {
			add_setting = $.parseJSON($('#dataTables-setting').html());
			for (k in add_setting) {
				settings[k] = add_setting[k];
			}
		}
		
		table =  $('#table-result').DataTable( settings );
	}
	
	$('.select2, .form-select').select2({
		theme: 'bootstrap-5'
	})
});