<?php
/**
 * csrf_helper.php
 * CSRF Token Management Helper
 * 
 * @author  VKNewsoft - Newsoft Developer, 2025
 */

function generate_token($n) {
	// PHP 7
	if (function_exists('random_bytes')) {
		return bin2hex(random_bytes($n));
	
	// Fallback to PHP 5
	} else {
		require_once BASEPATH . "system/libraries/vendors/paragonie/random-compat/lib/random.php";
		try {
			$string = random_bytes($n);
		} catch (TypeError $e) {
			// Well, it's an integer, so this IS unexpected.
			// die("An unexpected error has occurred"); 
			$string = null;
		} catch (Error $e) {
			// This is also unexpected because 32 is a reasonable integer.
			// die("An unexpected error has occurred");
			$string = null;
		} catch (Exception $e) {
			// If you get this message, the CSPRNG failed hard.
			// die("Could not generate a random string. Is our OS secure?");
			$string = null;
		}
		return bin2hex($string);
	}
}

// Mencegah token di generate dua kali dalam satu request
$csrf_settoken = false;
function csrf_settoken() 
{
	global $csrf_settoken;
	
	$current_url = uri_string();
	$config = config('App');
	
	if (!$config->csrf['enable'])
		return;
	
	if ($csrf_settoken) {
		return false;
	} 
	// Cek apakah url yang diakses adalah static resource, jika ya maka skip
	else if (strpos($current_url, 'public/') !== false) { 
		return false;
	}
	
	setcookie($config->csrf['name'], '', time() - 360000, '/');
	$token = generate_token(32);
	setcookie($config->csrf['name'], $token, time() + $config->csrf['expire'], '/');
	session()->set('csrf_token', ['token' => $token]);
	$csrf_settoken = true;
}

function csrf_gettoken() 
{
	return session()->get('csrf_token')['token'];
}

function csrf_formfield() 
{
	$config = config('App');
	if (!$config->csrf['enable'])
		return;
	
	$token = session()->get('csrf_token')['token'];
	
	if (!empty($token)) {
		$field = '<input type="hidden" name="' . $config->csrf['name'] . '" value="' . $token . '"/>';
	} else {
		$field = '<!-- CSRF Token disabled -->';
	}
	echo $field;
}

function csrf_validation() 
{
	$csrf_check = [];
	$config = config('App');
	if (!$config->csrf['enable'])
		return;
			
	if (!empty($_POST))
	{
		$error = false;
		
		if ( empty($_COOKIE[$config->csrf['name']]) || empty($_POST[$config->csrf['name']]) ) {
			$csrf_check['status'] = 'error';
			$csrf_check['error_type'] = 'token_notfound';
			$csrf_check['message'] = 'Token tidak ditemukan';
			$error = true;
		}

		if ( !empty($_POST[ $config->csrf['name'] ]) && !empty($_COOKIE[ $config->csrf['name']] ) ) {
			if ($_COOKIE[ $config->csrf['name'] ] != @$_POST[ $config->csrf['name'] ]) {
			
				$csrf_check['status'] = 'error';
				$csrf_check['error_type'] = 'token_missmatch';
				$csrf_check['message'] = 'Token tidak sesuai';
				$error = true;
			}
		}
	}
	
	return $csrf_check;
}

function generateDeviceId() {
    // Kombinasi beberapa informasi perangkat
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    $acceptEncoding = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';
    
    // Membuat string unik berdasarkan informasi di atas
    $deviceString = $userAgent . $ipAddress . $acceptLanguage . $acceptEncoding;
    
    // Hash string tersebut untuk dijadikan device ID
    $deviceId = hash('sha256', $deviceString);
    
    return $deviceId;
}
?>