<?php
/**
 * email_registrasi_helper.php
 * Email Template Helper for Registration & Notifications
 * 
 * @author  VKNewsoft - Newsoft Developer, 2025
 */

function email_head() {
	
	return '
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Nunito+Sans" rel="stylesheet">
	<style>
body{
	font-family: "Nunito Sans", "open sans", "segoe ui", arial;
	font-size: 16px;
}
h1 {
	font-size: 22px;
}
.logo-container {
	text-align:center;
}
.logo {
	display: inline-block;
	margin: auto;
}
.wrapper {
	max-width: 650px;
	margin: auto;
	margin-top: 20px;
}
.button {
	text-decoration:none;
	display:inline-block;
	margin-bottom:0;
	font-weight:normal;
	text-align:center;
	vertical-align:middle;
	background-image:none;
	border:1px solid transparent;
	white-space:nowrap;
	padding:7px 15px;
	line-height:1.5384616;
	background-color:#0277bd;
	border-color:#0277bd;
	color:#FFFFFF;
}
.button span {
	font-family:arial,helvetica,sans-serif;
	font-size: 16px;
	color:#FFFFFF;
}
p {
	font-size: 16px;
}
.alert {
    display: inline-block;
    margin-bottom: 0;
    font-weight: normal;
    text-align: left;
    vertical-align: middle;
    background-image: none;
    border: 1px solid transparent;
    padding: 7px 15px;
    line-height: 1.5384616;
    background-color: #ffb4b4;
    border-color: #ff9c9c;
	color: #c34949;
	font-size: 16px;
}
</style>
';
}

function email_resendlink_content() {

$config = new \Config\EmailConfig;

	return '
<html>
<head>'. email_head() .'</head>
<body>
<div class="wrapper">
	<div class="logo-container">
		<img class="logo" alt="logo" src="cid:logo_text"/>
	</div>
	<h1>Link Aktivasi Akun</h1>
	<p>
	Hi {{NAME}},  kami mengirim email ini karena kami mendapat permintaan kirim ulang link aktivasi akun, silakan klik tombol berikut untuk mengaktifkan akun Anda:
	</p>
	<p>
		<a class="button" href="{{url}}" target="_blank" >
		<span style="">Aktifkan Akun Saya</span></a>
	</p>
	<p>
	Jika tombol tersebut tidak berfungsi, silakan copy paste link berikut ini ke browser Anda:<br/><a href="{{url}}" target="_blank" >{{url}}</a></p>
	<p>
	Jika Anda merasa tidak melakukan permintaan ini, mohon abaikan email ini.
	</p>
	<p>Jika ada pertanyaan mengenai email ini, silakan kontak:<br/>
	<a href="mailto:'.$config->emailSupport.'" target="_blank">'.$config->emailSupport.'</a></p>
	<p>Regards,<br/>Newsoftdev Team</p>
</div>
</body>
</html>
';
}

function email_registration_content() {

$config = new \Config\EmailConfig;
	
	return '
<html>
<head>'. email_head() .'</head>
<body>
<div class="wrapper">
	<div class="logo-container">
		<img class="logo" alt="logo" src="cid:logo_text"/>
	</div>
	<h1>Link Aktivasi Akun</h1>
	<p>Hi, {{NAME}}, Anda baru saja mendaftar di aplikasi PHP Admin Template Newsoftdev. Untuk menyelesaikan proses pendaftaran, konfirmasi alamat email Anda dengan mengklik tombol berikut ini:</p>
	<p>
		<a class="button" href="{{url}}" target="_blank" >
		<span>Ya, konfirmasi alamat email saya</span></a>
	</p>
	<p>
	Jika tombol tersebut tidak berfungsi, silakan copy dan paste link berikut ini ke browser anda<br/>
	<a href="{{url}}" target="_blank">{{url}}</a>
	</p>
	<p>
	Jika Anda tidak merasa melakukan pendaftaran, mohon abaikan email ini.</p>
	<p>Jika ada pertanyaan lebih lanjut mengenai email ini, silakan kontak kami di:<br/>
	<a href="mailto:'.$config->emailSupport.'" target="_blank">'.$config->emailSupport.'</a></p>
	<p>Regards,<br/>Newsoftdev Team</p>
</div>
</body>
</html>
';
}

function email_recovery_content() 
{
	$config = new \Config\EmailConfig;
	
	return '
<html>
<head>'. email_head() .'</head>
<body>
<div class="wrapper">
	<div class="logo-container">
		<img class="logo" alt="logo" src="cid:logo_text"/>
	</div>
	<h1>Reset Password</h1>
	<p>
	Hi, kami mengirim email ini karena kami mendapat permintaan reset password, silakan klik tombol berikut untuk membuat password baru:
	</p>
	<p>
		<a class="button" href="{{url}}" target="_blank" >
		<span style="">Reset Password Saya</span></a>
	</p>
	<p>
	Jika tombol tersebut tidak berfungsi, silakan copy paste link berikut ini ke browser Anda:<br/><a href="{{url}}" target="_blank" >{{url}}</a></p>
	<p>
	Jika Anda merasa tidak melakukan permintaan ini, mohon abaikan email ini.
	</p>
	<p>Jika ada pertanyaan mengenai email ini, silakan kontak:<br/>
	<a href="mailto:'.$config->emailSupport.'" target="_blank">'.$config->emailSupport.'</a></p>
	<p>Regards,<br/>Newsoftdev Team</p>
</div>
</body>
</html>';
}