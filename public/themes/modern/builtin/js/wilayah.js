/**
 * Wilayah Module JavaScript
 * Handle DataTables and form interactions for Wilayah Management
 * 
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

jQuery(document).ready(function () {
	
	// ========================================================================
	// DataTables Configuration
	// ========================================================================
	
	// Check if DataTables elements exist on page
	if ($('#dataTables-column').length > 0) {
		
		// Parse column configuration dari hidden span
		var column = $.parseJSON($('#dataTables-column').html());
		var $setting = $('#dataTables-setting');
		var order = "";
		
		// Parse additional settings jika ada
		if ($setting.length > 0) {
			var setting = $.parseJSON($('#dataTables-setting').html());
			order = setting.order;
			
			// Apply columnDefs jika ada
			var columnDefs = setting.columnDefs || [];
		}
		
		// Get DataTables URL untuk AJAX request
		var url = $('#dataTables-url').html();
		
		// Initialize DataTables
		var table = $('#table-result').DataTable({
			"processing": true,
			"serverSide": true,
			"scrollX": true,
			"order": order,
			"ajax": {
				"url": url,
				"type": "POST"
			},
			"columns": column,
			"columnDefs": columnDefs,
			"language": {
				"processing": "Memproses...",
				"lengthMenu": "Tampilkan _MENU_ data per halaman",
				"zeroRecords": "Data tidak ditemukan",
				"info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
				"infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
				"infoFiltered": "(disaring dari _MAX_ total data)",
				"search": "Cari:",
				"paginate": {
					"first": "Pertama",
					"last": "Terakhir",
					"next": "Selanjutnya",
					"previous": "Sebelumnya"
				}
			},
			"initComplete": function(settings, json) {
				// Callback setelah DataTables selesai di-render
				console.log('DataTables initialized successfully');
			}
		});
	}
	
	// ========================================================================
	// Delete Confirmation Handler
	// ========================================================================
	
	// Handle delete action dengan SweetAlert confirmation
	$(document).on('click', '[data-action="delete-data"]', function(e) {
		e.preventDefault();
		
		var $form = $(this).closest('form');
		var deleteTitle = $(this).data('delete-title') || 'Apakah Anda yakin ingin menghapus data ini?';
		
		// Show confirmation dialog
		Swal.fire({
			title: 'Konfirmasi Hapus',
			html: deleteTitle,
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#d33',
			cancelButtonColor: '#3085d6',
			confirmButtonText: 'Ya, Hapus!',
			cancelButtonText: 'Batal'
		}).then((result) => {
			if (result.isConfirmed) {
				// Submit form jika user confirm
				$form.submit();
			}
		});
	});
	
	// ========================================================================
	// Form Utilities
	// ========================================================================
	
	// Auto-focus pada field pertama saat halaman dimuat
	if ($('form.needs-validation').length > 0) {
		$('form.needs-validation input:not([type=hidden]):first').focus();
	}
	
	// Submit button loading state
	$('form').on('submit', function() {
		var $submitBtn = $(this).find('button[type="submit"]');
		var originalText = $submitBtn.html();
		
		$submitBtn.prop('disabled', true)
			.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
		
		// Reset button setelah 5 detik (fallback)
		setTimeout(function() {
			$submitBtn.prop('disabled', false).html(originalText);
		}, 5000);
	});
	
});
