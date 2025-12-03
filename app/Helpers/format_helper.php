<?php
/**
 * format_helper.php
 * Data Formatting & Encryption Helper
 * 
 * @author  VKNewsoft - Newsoft Developer, 2025
 */

function encrypt($variabel){
	$simple_string = $variabel;

	$ciphering = "AES-128-CTR";
	$iv_length = openssl_cipher_iv_length($ciphering);
	$options = 0;
	$encryption_iv = "NewsoftDeveloper";
	$encryption_key = openssl_digest("Newsoft Developer Encryptest", 'MD5', TRUE);

	// Encryption of string process starts
	$encryption = openssl_encrypt($simple_string,$ciphering,$encryption_key,$options,$encryption_iv);
    $better_encryption = str_replace(['+', '/', '==','='], ["N".date('d'), "D".date('d'), "n".date('d'), "d".date('d')], $encryption);
	return $better_encryption;
}

function decrypt($variabel){
	$ciphering = "AES-128-CTR";
	$iv_length = openssl_cipher_iv_length($ciphering);
	$options = 0;
	$decryption_iv = "NewsoftDeveloper";
	$decryption_key = openssl_digest("Newsoft Developer Encryptest", 'MD5', TRUE);

	// Descrypt the string
    $better_decryption = str_replace(["N".date('d'), "D".date('d'), "n".date('d'), "d".date('d')], ['+', '/', '==','='], $variabel);
	$decryption = openssl_decrypt ($better_decryption, $ciphering,$decryption_key,$options,$decryption_iv);
	return $decryption;
}

function encrypt_url($data) {
    $secret_key = "newsoft_url_developer";
    $ciphering = "AES-128-CTR";
    $options = 0;
    $encryption_iv = '1234567891011121';

    // Encrypt the data
    $encrypted = openssl_encrypt($data, $ciphering, $secret_key, $options, $encryption_iv);

    // Convert to URL-safe base64
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($encrypted));
}

function decrypt_url($encrypted_data) {
    $secret_key = "newsoft_url_developer";
    $ciphering = "AES-128-CTR";
    $options = 0;
    $encryption_iv = '1234567891011121';

    // Convert back from URL-safe base64
    $encrypted_data = str_replace(['-', '_'], ['+', '/'], $encrypted_data);
    $encrypted_data = base64_decode($encrypted_data);

    // Decrypt the data
    return openssl_decrypt($encrypted_data, $ciphering, $secret_key, $options, $encryption_iv);
}

function format_ribuan($value)
{
	if (!$value)
		return 0;
	return number_format($value, 0, ',', '.');
}

function nmbulan($bulan)
{
	switch ($bulan) {
		case 1:
			$bulan = "Jan";
			break;
		case 2:
			$bulan = "Feb";
			break;
		case 3:
			$bulan = "Mar";
			break;
		case 4:
			$bulan = "Apr";
			break;
		case 5:
			$bulan = "Mei";
			break;
		case 6:
			$bulan = "Jun";
			break;
		case 7:
			$bulan = "Jul";
			break;
		case 8:
			$bulan = "Agu";
			break;
		case 9:
			$bulan = "Sep";
			break;
		case 10:
			$bulan = "Okt";
			break;
		case 11:
			$bulan = "Nov";
			break;
		case 12:
			$bulan = "Des";
			break;
	}
	return $bulan;
}

// Fungsi untuk mengkompres gambar
function compressImageControl($source, $destination, $quality, $maxWidth, $backup = false)
{
    // Buat backup jika diperlukan
    if ($backup) {
        $backupDir = dirname($source) . '/backup/';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        copy($source, $backupDir . basename($source));
    }
    
    $info = getimagesize($source);
    $originalSize = filesize($backupDir . basename($source));
    
    if ($info['mime'] == 'image/jpeg') {
        $image = imagecreatefromjpeg($source);
    } elseif ($info['mime'] == 'image/png') {
        $image = imagecreatefrompng($source);
    } else {
        return [
            'status' => 'error',
            'message' => 'Format tidak didukung: ' . basename($source)
        ];
    }
    
    // Dapatkan dimensi asli
    $width = imagesx($image);
    $height = imagesy($image);
    $resized = false;
    
    // Resize jika diperlukan
    if ($maxWidth > 0 && $width > $maxWidth) {
        $newHeight = (int)($height * $maxWidth / $width);
        $scaledImage = imagescale($image, $maxWidth, $newHeight);
        imagedestroy($image);
        $image = $scaledImage;
        $resized = true;
    }
    
    // Simpan gambar dengan kualitas yang ditentukan
    if ($info['mime'] == 'image/jpeg') {
        $success = imagejpeg($image, $destination, $quality);
    } elseif ($info['mime'] == 'image/png') {
        $pngQuality = (int)(9 - (($quality / 100) * 9));
        $success = imagepng($image, $destination, $pngQuality);
    }
    
    imagedestroy($image);
    
    if ($success) {
        $newSize = filesize($destination);
        $saved = $originalSize - $newSize;
        $reduction = $originalSize > 0 ? round(($saved / $originalSize) * 100, 2) : 0;
        
        return [
            'status' => 'success',
            'file' => basename($source),
            'original_size' => $originalSize,
            'new_size' => $newSize,
            'saved' => $saved,
            'reduction' => $reduction,
            'resized' => $resized
        ];
    } else {
        return [
            'status' => 'error',
            'message' => 'Gagal mengkompresi: ' . basename($source)
        ];
    }
}

function upload_to_storage_server($filePath, $fileName = null)
{
    $storageServerUrl = '-'; // Ganti dengan server storage
    $apiKey = '-'; // Sama dengan di server storage
    
    if (!file_exists($filePath)) {
        return ['status' => 'error', 'message' => 'File not found'];
    }
    $postData = [
        'image' => new CURLFile($filePath, mime_content_type($filePath), $fileName ?: basename($filePath))
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $storageServerUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode === 200) {
        return json_decode($response, true);
    } else {
        return [
            'status' => 'error', 
            'message' => 'HTTP Error: ' . $httpCode,
            'response' => $response
        ];
    }
}

function delete_from_storage_server($fileName)
{
    // Tambahkan endpoint delete di server storage jika diperlukan
    // Implementasi sesuai kebutuhan
    return true;
}

function get_storage_server_url($fileName)
{
    return 'http://103.56.235.50:8080/payday_storage/payday_ipi/leavepic/' . $fileName;
}

function compressImage($source, $destination, $quality = 75, $maxWidth = 1200) {
	$info = getimagesize($source);
	
	if ($info['mime'] == 'image/jpeg') {
		$image = imagecreatefromjpeg($source);
	} elseif ($info['mime'] == 'image/png') {
		$image = imagecreatefrompng($source);
	} else {
		return false;
	}
	
	// Dapatkan dimensi asli
	$width = imagesx($image);
	$height = imagesy($image);
	
	// Hitung rasio scaling jika diperlukan
	if ($width > $maxWidth) {
		$newHeight = (int)($height * $maxWidth / $width);
		$scaledImage = imagescale($image, $maxWidth, $newHeight);
		imagedestroy($image);
		$image = $scaledImage;
	}
	
	// Simpan gambar dengan kualitas yang ditentukan
	if ($info['mime'] == 'image/jpeg') {
		imagejpeg($image, $destination, $quality);
	} elseif ($info['mime'] == 'image/png') {
		// Konversi PNG ke JPG untuk kompresi lebih baik
		imagejpeg($image, str_replace('.png', '.jpg', $destination), $quality);
	}
	
	imagedestroy($image);
	return true;
}

if (!function_exists('time_ago')) {
	function time_ago($datetime, $full = false) {
		$now = new DateTime();
		$ago = new DateTime($datetime);
		$diff = $now->diff($ago);

		$diff->w = floor($diff->d / 7);
		$diff->d -= $diff->w * 7;

		$string = array(
			'y' => 'tahun',
			'm' => 'bulan',
			'w' => 'minggu',
			'd' => 'hari',
			'h' => 'jam',
			'i' => 'menit',
			's' => 'detik',
		);
		foreach ($string as $k => &$v) {
			if ($diff->$k) {
				$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? '' : '');
			} else {
				unset($string[$k]);
			}
		}

		if (!$full) $string = array_slice($string, 0, 1);
		return $string ? implode(', ', $string) . ' lalu' : 'baru saja';
	}
}