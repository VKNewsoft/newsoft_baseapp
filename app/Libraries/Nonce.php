<?php
namespace App\Libraries;

class Nonce 
{
	private $encrypter;
	
	public function __construct() {
		// $this->session = \Config\Services::session();
		$this->encrypter = \Config\Services::encrypter();
	}

	public function generateToken($n) {
		return random_bytes($n);
	}

	public function generateSessionNonce($nonce, $ciphertext) 
	{	
		// Menghindari konflik, kita tambahi prefix wdi
		$_SESSION['wdi_nonce'][$nonce] = $ciphertext;
	}
	
	public function getNonce($msg) 
	{
		$nonce = $this->generateToken(8); // Selector
		$nonce = bin2hex($nonce);
		$encrypt = $this->encrypter->encrypt($msg);
		
		
		/* $ivlen 		= openssl_cipher_iv_length($cipher="AES-128-CBC");
		$iv 		= openssl_random_pseudo_bytes($ivlen);
		$ciphertext_raw = openssl_encrypt($msg, $cipher, SECRET_KEY, $options=OPENSSL_RAW_DATA, $iv);
		$hmac 		= hash_hmac('sha256', $ciphertext_raw, $SECRET_KEY, $as_binary=true);
		$ciphertext = base64_encode( $iv.$hmac.$ciphertext_raw ); */
		
		$this->generateSessionNonce($nonce, $encrypt);
		return $nonce;
	}
	
	public function readNonce($nonce) 
	{
		if (!key_exists($nonce, $_SESSION['wdi_nonce']))
			return ['status' => 'error', 'content' => 'Token tidak ditemukan'];
		
		$chiper = $_SESSION['wdi_nonce'][$nonce];
		return ['status' => 'ok', 'content' => $this->encrypter->decrypt($chiper)];
		
		/* $ciphertext_enc = $SESSION['wdi_nonce'][$nonce];
		
		$ciphertext = base64_decode($ciphertext_enc);
		$ivlen 		= openssl_cipher_iv_length($cipher="AES-128-CBC");
		$iv 		= substr($ciphertext, 0, $ivlen);
		$hmac 		= substr($ciphertext, $ivlen, $sha2len=32);
		$ciphertext_raw = substr($ciphertext, $ivlen + $sha2len);
		$message 	= openssl_decrypt($ciphertext_raw, $cipher, SECRET_KEY, $options=OPENSSL_RAW_DATA, $iv);
		$calcmac 	= hash_hmac('sha256', $ciphertext_raw, SECRET_KEY, $as_binary=true);
		if (hash_equals($hmac, $calcmac))//PHP 5.6+ timing attack safe comparison
		{
			return $message;
		} else {
			return false;
		} */
		
		return false;
	}
}