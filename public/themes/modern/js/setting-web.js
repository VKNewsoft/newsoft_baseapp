jQuery(document).ready(function () 
{
	$('.file').change(function(e) 
	{
		file = this.files[0];
		$this = $(this);

		var reader = new FileReader();

		// Closure to capture the file information.
		var $upload_img = $this.parent().children('.upload-img-thumb');
		
		reader.onload = (function(e) {

			// Render thumbnail.
			$upload_img.find('img').remove();
			var thumb = '<img class="thumb" src="' + e.target.result +
                            '" title="' + escape(file.name) + '"/>';
			$upload_img.find('span').before(thumb);
			
		});
		
		reader.readAsDataURL(file); 
		size = file.size;
		
		file_size = size + ' Bytes';
		if (size > 1024 * 1024) {
			file_size = parseFloat(size / (1024 * 1024)).toFixed(2) + ' Mb';
		} else if (size > 1024) {
			file_size = parseFloat(size / 1024).toFixed(2) + ' Kb';
		}
		// console.log(file);
		if (size > 1024 * 1024 * 2) {
			$('<small class="alert alert-danger">Ukuran file maksimal: 2Mb, file Anda ' + file_size + '</small>').insertAfter($this);
			return;
		}
		
		/* if (file.type != 'application/vnd.ms-excel' 
				&& file.type != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' 
				&& file.type != 'application/pdf'
				&& file.type != 'application/msword'
				&& file.type != 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
			) {
			$('<small class="alert alert-danger">Tipe file yang diperbolehkan: .doc, .docx, .xls, .xlsx, dan .pdf</small>').insertAfter($this);
			return;
		} */
		
		var file_prop = '<ul><li><small>Name: ' + file.name + '</small></li><li><small>Size: ' + file_size + '</small></li><li><small>Type: ' + file.type + '</small></li></ul>';
		$upload_img.show().find('span').html(file_prop);
	});
});