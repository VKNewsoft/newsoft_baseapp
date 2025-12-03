/**
* App		: PHP Admin Template
* Author	: IP IT Team
* Year		: Last Oktober 2023
* Company : Indopasifik Indahtama
*/

jQuery(document).ready(function () {
	function bytesToSize(bytes) {
		var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
		if (bytes == 0) return 'n/a';
		var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
		return (bytes / Math.pow(1024, i)).toFixed(2) + ' ' + sizes[i];
	};
	
	$('.remove-img').click(function(){
		$container = $(this).parent().parent().parent();
		input_file_name = $container.find('.file').attr('name');
		
		$(this).parent().parent().remove();
		$('.' + input_file_name + '-delete-img').val(1);
	});
	
	$('.file').change(function(e) 
	{
		file = this.files[0];
		$this = $(this);
		
		$this.parent().find('.alert-danger').remove();
		$upload_img = $this.parent().children('.upload-img-thumb');
		
		$upload_img.find('img').remove();
		$upload_img.find('.img-prop').empty();
		$upload_img.hide();
		if ($this.val() == '')
			return false;
		
		name = $this.attr('name');
		max_size = 1024 * 1024 * 2;
		$max_size_elm = $('.' + name + '-max-size');
		if ($max_size_elm.length > 0) {
			max_size = parseInt($max_size_elm.val());
		}

		var reader = new FileReader();

		// Closure to capture the file information.
		
		
		reader.onload = (function(e) {
			
			// Render thumbnail.
			var thumb = '<img class="thumb" src="' + e.target.result +
                            '" title="' + escape(file.name) + '"/>';
			$upload_img.find('.img-prop').before(thumb);
			
			var img = new Image;
			img.src = reader.result;
            img.onload = function() {
				var file_prop = '<ul><li><small>Name: ' + file.name + '</small></li><li><small>Size: ' + file_size + '</small></li><li><small>Dimension (W x H): ' + img.width + 'px X ' + img.height + 'px</small></li><li><small>Type: ' + file.type + '</small></li></ul>';
				$upload_img.show().find('.img-prop').html(file_prop);
            };
		});
		
		reader.readAsDataURL(file); 
		size = file.size;
		
		file_size = size + ' Bytes';
		if (size > 1024 * 1024) {
			file_size = parseFloat(size / (1024 * 1024)).toFixed(2) + ' Mb';
		} else if (size > 1024) {
			file_size = parseFloat(size / 1024).toFixed(2) + ' Kb';
		}
		
		if (size > max_size) {
			$('<small class="alert alert-danger mt-1" style="display:block">Ukuran file maksimal: ' + bytesToSize(max_size) + ', file Anda ' + file_size + '</small>').insertBefore($upload_img);
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
		// console.log($upload_img.attr('class'));
		/* var file_prop = '<ul><li><small>Name: ' + file.name + '</small></li><li><small>Size: ' + file_size + '</small></li><li><small>Type: ' + file.type + '</small></li></ul>';
		$upload_img.show().find('span').html(file_prop); */
	});
});