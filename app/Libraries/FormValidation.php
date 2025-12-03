<?php
namespace App\Libraries;

class FormValidation 
{
	private $errors = [];
	
	public function checkEmail($name, $email) 
	{
		if (!strpos($email, '@gmail.com') && !strpos($email, '@yahoo.com')) {
			$this->errors[$name] = 'Gunakan gmail.com atau yahoo.com';
			return false;
		}
		return true;
	}
	
	public function checkEmailProvider($email, $provider = ['gmail.', 'yahoo.']) 
	{
		$error = true;
		foreach ($provider as $email_provider) {
			$exists = strpos($email, '@' . $email_provider);
			if ($exists) {
				$error = false;
			}
		}
	
		if ($error) {
			$this->errors['email'] = 'Alamat email yang diperbolehkan ' . join($provider, ' atau ');
			return false;
		}
		return true;
	}

	public function checkPassword($name, $password) 
	{
		/* preg_match_all("/[a-z]/", $password, $match);
		if (!$match[0]) {
			$this->errors[$name] = 'Password harus mengandung huruf kecil';
			return false;	
		}
		preg_match_all("/[A-Z]/", $password, $match);
		if (!$match[0]) {
			$this->errors[$name] = 'Password harus mengandung huruf besar';
			return false;
		}
		preg_match_all("/[0-9]/", $password, $match);
		if (!$match[0]) {
			$this->errors[$name] = 'Password harus mengandung angka';
			return false;
		} */
		
		return true;
	}
	
	public function getErrors() {
		return $this->errors;
	}
}