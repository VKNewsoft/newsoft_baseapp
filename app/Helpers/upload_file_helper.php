<?php
/**
 * upload_file_helper.php
 * File Upload & Image Processing Helper
 * 
 * @author  VKNewsoft - Newsoft Developer, 2025
 */

function get_filename($file_name, $path) {
	
	$file_name_path = $path . $file_name;
	// echo '-' . $file_name_path . '-';
	if ($file_name != "" && file_exists($file_name_path))
	{
		$file_ext = strrchr($file_name, '.');
		$file_basename = substr($file_name, 0, strripos($file_name, '.'));
		$num = 1;
		while (file_exists($file_name_path)){
			$file_name = $file_basename."_$num".$file_ext;
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
	$new_img = \create_image_mime($file_type, $image);
	imagecopyresized($resized_img, $new_img, 0, 0, 0, 0, $w, $h, $img_size[0], $img_size[1]);
	$do = create_image($file_type, $resized_img, $image);
	ImageDestroy ($resized_img);
	ImageDestroy ($new_img);
	return $do;
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


function upload_file($path, $file) 
{
	$new_name =  get_filename(stripslashes($file['name']), $path); ;
	$move = move_uploaded_file($file['tmp_name'], $path . $new_name);
	if ($move) 
		return $new_name;
	else
		return false;
}