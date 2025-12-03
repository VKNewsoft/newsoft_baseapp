<?php
/**
 * functions_helper.php
 * General Purpose Helper Functions
 * 
 * @author  VKNewsoft - Newsoft Developer, 2025
 */

/* Create breadcrumb
$data: title as key, and url as value */ 

if (!function_exists('breadcrumb')) {
	function breadcrumb($data) 
	{
		$separator = '&raquo;';
		echo '<nav aria-label="breadcrumb">
	  <ol class="breadcrumb">';
		foreach ($data as $title => $url) {
			if ($url) {
				echo '<li class="breadcrumb-item"><a href="'.$url.'">'.$title.'</a></li>';
			} else {
				echo '<li class="breadcrumb-item active" aria-current="page">'.$title.'</li>';
			}
		}
		echo '
	  </ol>
	</nav>';
	}
}

function format_tanggal_db($date) 
{
	if ($date == '0000-00-00' || $date == '')
		return $date;
	
	$bulan = nama_bulan();
	$exp = explode('-', $date);
	return $exp[2] . ' ' . $bulan[ ($exp[1] * 1) ] . ' ' . $exp[0]; // * untuk mengubah 02 menjadi 2
}

if (!function_exists('current_url')) {
	function current_url() {
		return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}
}

if (!function_exists('set_value')) {
	/**
	 * Get POST value with default fallback
	 * @deprecated Use CodeIgniter's request->getPost() instead
	 */
	function set_value($field_name, $default = '') 
	{
		$request = \Config\Services::request();
		$search = $field_name;
		
		// If Array
		$is_array = false;
		if (strpos($search, '[')) {
			$is_array = true;
			$exp = explode('[', $field_name);
			$field_name = $exp[0];
		}
		
		$postData = $request->getPost($field_name);
		if (!empty($postData)) {
			
			if ($is_array) {
				$exp_close = explode(']', $exp[1]);
				$index = $exp_close[0];
				return $postData[$index] ?? $default;
			}
			return $postData;
		}

		return $default;
	}
}

function current_action_url() {
	$request = \Config\Services::request();
	$config = config('App');
	$module = $request->getGet('module') ?? '';
	$action = $request->getGet('action') ?? '';
	return $config->baseURL . '?module=' . $module . '&action=' . $action; 
}

function get_menu_db ($aktif = 'all', $show_all = false) 
{
	global $db;
	global $app_module;
	
	$result = [];
	$nama_module = $app_module['nama_module'];
	
	$where = ' ';
	$where_aktif = '';
	if ($aktif != 'all') {
		$where_aktif = ' AND aktif = '.$aktif;
	}
	
	$role = '';
	if (!$show_all) {
		$session = \Config\Services::session();
		$userId = $session->get('user')['id_role'] ?? 0;
		$role = ' AND id_role = ' . $userId;
	}
	
	$sql = 'SELECT * FROM core_menu 
				LEFT JOIN core_menu_role USING (id_menu)
				LEFT JOIN core_module USING (id_module)
			WHERE 1 = 1 ' . $role
				. $where_aktif.' 
			ORDER BY urut';
	
	$db->query($sql);
	
	$current_id = '';
	while ($row = $db->fetch()) 
	{
		$result[$row['id_menu']] = $row;
		$result[$row['id_menu']]['highlight'] = 0;
		$result[$row['id_menu']]['depth'] = 0;

		if ($nama_module == $row['nama_module']) {
			
			$current_id = $row['id_menu'];
			$result[$row['id_menu']]['highlight'] = 1;
		}
		
	}
	// echo '<pre>'; print_r($result);
	
	if ($current_id) {
		menu_current($result, $current_id);
	}
	
	return $result;
}

function menu_current( &$result, $current_id) 
{
	$parent = $result[$current_id]['id_parent'];

	$result[$parent]['highlight'] = 1; // Highlight menu parent
	if (@$result[$parent]['id_parent']) {
		menu_current($result, $parent);
	}
}

function create_image_mime ($tipe_file, $newfile)
{
	switch ($tipe_file)
	{
		case "image/gif":
			return imagecreatefromgif($newfile);
			
		case "image/png":
			return imagecreatefrompng($newfile);
			
		case "image/bmp":
			return imagecreatefrombmp($newfile);
			
		default:
			return imagecreatefromjpeg($newfile);		
	}
}
	
function create_image ($tipe_file, $resized_img, $newfile)
{
	switch ($tipe_file)
	{
		case "image/gif":
			return imagegif ($resized_img,$newfile, 85);
			
		case "image/png":
			imagesavealpha($resized_img, true);
			$color = imagecolorallocatealpha($resized_img, 0,0,0,127);
			imagefill($resized_img, 0,0, $color);
			return imagepng ($resized_img,$newfile, 9);
			
		case "image/bmp":
			return imagecreatefrombmp($newfile);
			
		default:
			return imagejpeg ($resized_img,$newfile, 85);
			
	}
}

function get_filename($file_name, $path) {
	
	$file_name_path = $path . $file_name;
	if ($file_name != "" && file_exists($file_name_path))
	{
		$file_ext = strrchr($file_name, '.');
		$file_basename = substr($file_name, 0, strripos($file_name, '.'));
		$num = 1;
		while (file_exists($file_name_path)){
			$file_name = $file_basename."($num)".$file_ext;
			$num++;
			$file_name_path = $path . $file_name;
		}
		
		return $file_name;
	}
	return $file_name;
}

function upload_image($path, $file, $max_w = 500, $max_h = 500) 
{
	
	$file_type = $file['type'];
	$new_name =  get_filename(stripslashes($file['name']), $path); ;
	$move = move_uploaded_file($file['tmp_name'], $path . $new_name);
	
	$save_image = false;
	if ($move) {
		$dim = image_dimension($path . $new_name, $max_w, $max_h);
		$save_image = save_image($path . $new_name, $file_type, $dim[0], $dim[1]);
	}
	
	if ($save_image)
		return $new_name;
	else
		return false;
}

function image_dimension($images, $maxw=null, $maxh=null)
{
	if($images)
	{
		$img_size = @getimagesize($images);
		$w = $img_size[0];
		$h = $img_size[1];
		$dim = array('w','h');
		foreach($dim AS $val){
			$max = "max{$val}";
			if(${$val} > ${$max} && ${$max}){
				$alt = ($val == 'w') ? 'h' : 'w';
				$ratio = ${$alt} / ${$val};
				${$val} = ${$max};
				${$alt} = ${$val} * $ratio;
			}
		}
		return array($w,$h);
	}
}

function save_image($image, $file_type, $w, $h) 
{
	$img_size = @getimagesize($image);
	
	$resized_img = imagecreatetruecolor($w,$h);
	$new_img = create_image_mime($file_type, $image);
	imagecopyresized($resized_img, $new_img, 0, 0, 0, 0, $w, $h, $img_size[0], $img_size[1]);
	$do = create_image($file_type, $resized_img, $image);
	ImageDestroy ($resized_img);
	ImageDestroy ($new_img);
	return $do;
}

function upload_file($path, $file) 
{
	$new_name =  get_filename(stripslashes($file['name']), $path); ;
	$move = move_uploaded_file($file['tmp_name'], $path . $new_name);
	if ($move) 
		return $new_name;
	else
		return false;
}

function get_dimensi_kartu($ori_panjang, $ori_lebar, $dpi) {
	// print_r($ori_panjang); die;
	$px = 0.393700787; 
	$panjang = $ori_panjang * $dpi * $px;
	$lebar = $ori_lebar * $dpi * $px;
	return ['w' => $panjang, 'h' => $lebar];
}

function generateQRCode($version, $ecc, $text, $module_width) {
	
	require APPPATH . 'ThirdParty\qrcode\qrcode_extended.php';
	$qr = new QRCodeExtended();
	$ecc_code = ['L' => QR_ERROR_CORRECT_LEVEL_L
		, 'M' => QR_ERROR_CORRECT_LEVEL_M
		, 'Q' => QR_ERROR_CORRECT_LEVEL_Q
		, 'H' => QR_ERROR_CORRECT_LEVEL_H
	];
	$qr->setErrorCorrectLevel($ecc_code[$ecc]);
	$qr->setTypeNumber($version);
	$qr->addData($text);
	$qr->make();
	return $qr->saveHtml($module_width);
}