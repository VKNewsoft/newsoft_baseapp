<?php
/**
 * form_requirement_helper.php
 * Form Validation Requirements Helper
 * 
 * @author  VKNewsoft - Newsoft Developer, 2025
 */

function password_requirements($password, $field_title = 'Password') {
	
	$error = [];
	if (strlen($password) <= 8) {
		$error[] = $field_title . ' minimal 8 karakter';
	}
	
	preg_match_all("/[a-z]/", $password, $match);
	if (!$match[0]) {
		$error[] = $field_title . ' harus mengandung huruf kecil';	
	}
	preg_match_all("/[A-Z]/", $password, $match);
	if (!$match[0]) {
		$error[] = $field_title . ' harus mengandung huruf besar';
	}
	preg_match_all("/[0-9]/", $password, $match);
	if (!$match[0]) {
		$error[] = $field_title . ' harus mengandung angka';
	}
	
	return $error;
}

function email_requirements($email, $field_title = 'Email') {
	
	$error = [];
	// Validate email domain
	if (!strpos($email, '@gmail') && !strpos($email, '@yahoo')) {
		$error[] = 'Gunakan email Gmail atau Yahoo';
	}
	return $error;
}