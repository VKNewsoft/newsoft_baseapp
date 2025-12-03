/**
* Written by: IT-IPI
* Year		: Last Oktober 2023
* Company : Indopasifik Indahtama
*/

jQuery(document).ready(function () {
	// console.log(current_url + '/getUserDT');
	column = $.parseJSON($('#dataTables-column').html());
	$setting =$('#dataTables-setting');
	var order = "";
	if ($setting.length > 0) {
		setting = $.parseJSON($('#dataTables-setting').html());
		order = setting.order;
	}
	url = $('#dataTables-url').html();
	table =  $('#table-result').DataTable( {
        "processing": true,
        "serverSide": true,
		"scrollX": true,
		"order" : order,
		"ajax": {
            "url": url,
            "type": "POST"
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
    } );

});