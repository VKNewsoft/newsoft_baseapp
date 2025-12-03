<?php
namespace App\Libraries;
use App\Models\AuthModel;

class Auth 
{
	private $form_token = '';
	private $session;
	private $model;
	
	public function __construct() {
		$this->session = \Config\Services::session();
	}
	
	public function generateToken($n) {
		// PHP 7
		if (function_exists('random_bytes')) {
			return random_bytes($n);
		
		// Fallback to PHP 5
		} else {
			require_once APPPATH . "third_party/Random-compat/lib/random.php";
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
			return $string;
		}
	}
	
	public function generateSelector($n) {
		return $this->generateToken($n);
	}
	
	public function generateSessionFormToken($session_name = null, $token_string = '') 
	{
		$exp = explode (':', $token_string);
		$selector = $exp[0];
		$token = $exp[1];
		
		if ($session_name) {
			$_SESSION['token'][$session_name] = [$selector => hash('sha256', hex2bin($token))];
		} else {
			$_SESSION['token'] = [$selector => hash('sha256', hex2bin($token))];
		}
	}
	
	public function generateFormToken($session_name = null) 
	{
		$random_bytes = $this->generateToken(33);
		$form_token = bin2hex($random_bytes);
		
		$selector  = bin2hex($this->generateSelector(9));
		$token = $selector . ':' . $form_token;
		
		$this->generateSessionFormToken($session_name, $token);
		return $token;
	}
	
	public function generateDbToken()
	{
		$random_bytes = $this->generateToken(33);
		$selector  = $this->generateSelector(9);
		
		$token['selector'] = bin2hex($selector);
		$token['external'] = bin2hex($random_bytes);
		$token['db'] =hash('sha256', $random_bytes);
		
		return $token;
	}
	
	public function validateFormToken($session_name = null, $post_name = 'form_token') {

		$request = \Config\Services::request();
		$form_token = explode (':', $request->getVar($post_name));
		
		$form_selector = $form_token[0];
		$sess_index = $session_name ? $_SESSION['token'][$session_name] : $_SESSION['token'];
		
		if (!key_exists($form_selector, $sess_index))
				return false;
			
		return $this->validateToken($form_token[1], $sess_index[$form_selector]);
	}
	
	public function validateToken($provided_string, $hashed_token) 
	{
		if (!$provided_string || !$hashed_token) {
			return;
		}
		$hash = @hash('sha256', hex2bin($provided_string));
		return hash_equals($hashed_token, $hash);
	}
	
	public function createFormToken($name) {
		return '<input type="hidden" name="form_token" value="' . $this->generateFormToken($name) . '"/>';
	}
}