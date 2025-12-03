<?php
/**
 * filepicker_helper.php
 * File Type Detection & Validation Helper
 * 
 * @author  VKNewsoft - Newsoft Developer, 2025
 */

function file_type() {
	
	return [
	
		'text/plain' => ['file_type' => 'document', 'extension' => 'txt'],
		
		// Image
		'image/jpg'		=> ['file_type' => 'image', 'extension' => 'jpg'],
		'image/jpeg'		=> ['file_type' => 'image', 'extension' => 'jpg'],
		'image/png'		=> ['file_type' => 'image', 'extension' => 'png'],
		'image/bmp'		=> ['file_type' => 'image', 'extension' => 'bmp'],
		'image/gif'		=> ['file_type' => 'image', 'extension' => 'gif'],

		// Media
		'audio/x-wav'		=> ['file_type' => 'audio', 'extension' => 'wav'],
		'audio/flac'		=> ['file_type' => 'audio', 'extension' => 'flac'],
		'audio/mpeg'		=> ['file_type' => 'audio', 'extension' => 'mp3'],
		
		'video/mp4'			=> ['file_type' => 'video', 'extension' => 'mp4'],
		'video/x-msvideo' 	=> ['file_type' => 'video', 'extension' => 'avi'],
		'video/quicktime' 	=> ['file_type' => 'video', 'extension' => 'mov'],
		'video/x-matroska' 	=> ['file_type' => 'video', 'extension' => 'mkv'],
		'video/x-ms-asf' 	=> ['file_type' => 'video', 'extension' => 'wmv'],

		// Document
		'application/pdf' => ['file_type' => 'document', 'extension' => 'pdf'],

		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['file_type' => 'document', 'extension' => 'xlsx'], //xlsx
		'application/vnd.ms-excel' => ['file_type' => 'document', 'extension' => 'xls'], // xls
		'application/vnd.oasis.opendocument.spreadsheet' => ['file_type' => 'document', 'extension' => 'ods'], // ods

		'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['file_type' => 'document', 'extension' => 'docx'], //docx
		'application/msword' => ['file_type' => 'document', 'extension' => 'doc'], // doc
		'application/vnd.oasis.opendocument.text' => ['file_type' => 'document', 'extension' => 'odt'],
		'text/rtf' => ['file_type' => 'document', 'extension' => 'rtf'],

		'application/vnd.openxmlformats-officedocument.presentationml.presentation' => ['file_type' => 'document', 'extension' => 'ppt'], // pptx
		'application/vnd.oasis.opendocument.presentation' => ['file_type' => 'document', 'extension' => 'odp'],
		'application/vnd.ms-powerpoint' => ['file_type' => 'document', 'extension' => 'ppt'], //ppt

		// Compression
		'application/x-rar'	=> ['file_type' => 'archive', 'extension' => 'rar'],
		'application/zip'	=> ['file_type' => 'archive', 'extension' => 'zip'],
		'application/gzip'	=> ['file_type' => 'archive', 'extension' => 'gz'],
		'application/x-7z-compressed' => ['file_type' => 'archive', 'extension' => '7z'],

		// Application
		'application/x-msi' => ['file_type' => 'application', 'extension' => 'msi'],
		'application/x-dosexec' => ['file_type' => 'application', 'extension' => 'exe']
	
	];
}

function file_type_alltypes() {	
	return ['image/png', 'image/jpeg', 'image/bmp', 'image/gif', 'image/jpg','text/plain','application/pdf','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/vnd.ms-excel','application/vnd.oasis.opendocument.spreadsheet','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/msword','application/vnd.oasis.opendocument.text','text/rtf','application/vnd.openxmlformats-officedocument.presentationml.presentation','application/vnd.oasis.opendocument.presentation','application/vnd.ms-powerpoint','application/x-rar','application/zip','application/gzip','application/x-7z-compressed','application/x-msi','application/x-dosexec','audio/x-wav','audio/flac','audio/mpeg','video/mp4','video/x-msvideo','video/quicktime','video/x-matroska','video/x-ms-asf'];
}

function file_type_images() {	
	return ['image/png', 'image/jpeg', 'image/bmp', 'image/gif', 'image/jpg'];
}

function file_type_documents() {	
	return ['text/plain','application/pdf','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/vnd.ms-excel','application/vnd.oasis.opendocument.spreadsheet','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/msword','application/vnd.oasis.opendocument.text','text/rtf','application/vnd.openxmlformats-officedocument.presentationml.presentation','application/vnd.oasis.opendocument.presentation','application/vnd.ms-powerpoint'];
}

function file_type_compressions() {	
	return ['application/x-rar','application/zip','application/gzip','application/x-7z-compressed'];
}

function file_type_applications() {	
	return ['application/x-msi','application/x-dosexec'];
}

function file_type_medias() {	
	return ['audio/x-wav','audio/flac','audio/mpeg','video/mp4','video/x-msvideo','video/quicktime','video/x-matroska','video/x-ms-asf'];
}
?>