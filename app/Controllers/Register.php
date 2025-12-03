<?php
/**
 * Register Controller
 * Menangani pendaftaran user baru dengan email activation
 * 
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Controllers;
use App\Models\RegisterModel;
use \Config\App;
use App\Libraries\Auth;

class Register extends \App\Controllers\BaseController
{
	protected $model = '';
	
	public function __construct() {
		parent::__construct();
		$this->model = new RegisterModel;	
		$this->data['site_title'] = 'Register Akun';
		
		helper(['cookie', 'form']);
		
		$this->addJs($this->config->baseURL . 'public/vendors/jquery/jquery.min.js');
		$this->addJs($this->config->baseURL . 'public/vendors/bootstrap/js/bootstrap.min.js');
										
		$this->addStyle($this->config->baseURL . 'public/vendors/bootstrap/css/bootstrap.min.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/fontawesome/css/fontawesome.min.css');
		$this->addStyle($this->config->baseURL . 'public/themes/modern/css/register.css');

		$this->addJs($this->config->baseURL . 'public/vendors/jquery.pwstrength.bootstrap/pwstrength-bootstrap.min.js');
		$this->addJs($this->config->baseURL . 'public/themes/modern/js/password-meter.js');
		
	}
	
	/**
	 * Halaman registrasi user baru
	 * Handle form submission dan validasi
	 */
	public function index()
	{
		$this->mustNotLoggedIn();
		$this->data['title'] = 'Register Akun';
		$message = [];
		$error = false;
		
		if ($this->request->getPost('submit')) 
		{
			$message['status'] = 'error';
			
			// Validasi form
			$formError = $this->validateForm();

			if ($formError) {
				$message['message'] = $formError;
				$error = true;
			}
				
			// Submit data jika tidak ada error
			if (!$error) {		
				$message = $this->model->insertUser();
				if ($message['status'] == 'error') {
					$error = true;
				}
			}	
		}
		
		// Tampilkan form atau success message
		$file = 'form.php';
		if ($this->request->getPost('submit') && !$error) {
			$file = 'show_message.php';
		}
		
		$this->data['message'] = $message;
		$this->data['style'] = ' style="max-width:500px; margin-top:50px"';
		return view('themes/modern/register/' . $file, $this->data);
	}
	
	/**
	 * Konfirmasi email activation
	 * Validasi token dan aktifkan akun
	 */
	public function confirm() 
	{
		$this->data['title'] = 'Konfirmasi Alamat Email';
		
		$error = false;
		$message = ['status' => 'error'];
		
		// Validasi token dari URL
		$token = $this->request->getGet('token');
		if (empty($token)) {
			$message['message'] = 'Token tidak ditemukan';
			$error = true;
		} else {
			@list($selector, $urlToken) = explode(':', $token);
			if (!$selector || !$urlToken) {
				$message['message'] = 'Token tidak valid';
				$error = true;
			}
		}
		
		if (!$error) {
						
			$dbtoken = $this->model->checkToken($selector);
			
			if ($dbtoken) 
			{
				$user = $this->model->checkUserById($dbtoken['id_user']);
				$auth = new Auth;
				
				if ($user['verified'] == 1) {
					$message['message'] = 'Akun sudah pernah diaktifkan';
					$error = true;
				} 
				else if ($dbtoken['expires'] < date('Y-m-d H:i:s')) {
					$message['message'] = 'Link expired, silakan request <a href="'. $this->config->baseURL .'register/resendlink">link aktivasi</a> yang baru';
					$error = true;
				} 
				else if (!$auth->validateToken($urlToken, $dbtoken['token'])) {
					$message['message'] = 'Token invalid, silakan <a href="'. $this->config->baseURL.'register">register</a> ulang atau request <a href="'. $this->config->baseURL.'register/resendlink">link aktivasi</a> yang baru';
					$error = true;
				}
				
			} else {
				$message['message'] = 'Token tidak ditemukan atau akun sudah pernah diaktifkan';
				$error = true;
			}
		}
		
		if (!$error)
		{
			$update = $this->model->updateUser($dbtoken);
		
			if ($update) {
				$message['status'] = 'ok';
				$message['message'] = 'Selamat!!!, akun Anda berhasil diaktifkan, Anda sekarang dapat <a href="'.$this->config->baseURL.'login">Login</a> menggunakan akun Anda';
			} else {
				$emailConfig = new \Config\EmailConfig;
				$message['status'] = 'error';
				$message['message'] = 'Token ditemukan tetapi saat ini akun tidak dapat diaktifkan karena ada gangguan pada sistem, silakan coba dilain waktu, atau hubungi <a href="mailto:' . $emailConfig->emailSupport . '" title="Hubungi kami via email">' . $emailConfig->emailSupport . '</a>';
			}					
		}
		
		$this->data['message'] = $message;
		return view('themes/modern/register/show_message.php', $this->data);
	}
	
	/**
	 * Kirim ulang link aktivasi akun
	 * Untuk user yang belum aktivasi atau link expired
	 */
	public function resendlink() 
	{
		$this->data['title'] = 'Kirim Ulang Link Aktivasi Akun';
		$message = [];
		$error = false;
		
		helper('registrasi');
		$settingRegister = $this->model->getSettingRegistrasi();
		
		// Cek metode aktivasi
		if ($settingRegister['metode_aktivasi'] != 'email') {
			$emailConfig = new \Config\EmailConfig;
			$message['status'] = 'error';
			$message['message'] = 'Metode aktivasi yang digunakan bukan melalui email. Untuk mengaktifkan akun, silakan hubungi administrator di: <a href="mailto:' . $emailConfig->emailSupport . '" title="Hubungi Support">' . $emailConfig->emailSupport . '</a>';
		
		} else {
			if ($this->request->getPost('submit')) 
			{
				// Validasi form
				$formError = $this->validateFormResendlink();
				
				$message['status'] = 'error';
				if ($formError) {
					$message['message'] = $formError;
					$error = true;
				}

				// Submit data
				if (!$error) {
					$message = $this->model->resendLink();
					if ($message['status'] == 'error') {
						$error = true;
					}
				}
			}
		}
		
		// Tampilkan form atau success message
		$file = 'form-resendlink.php';
		if ($settingRegister['metode_aktivasi'] != 'email' || (!$error && $this->request->getPost('submit'))) {
			$file = 'show_message.php';
		}
		
		$this->data['message'] = $message;
		return view('themes/modern/register/' . $file, $this->data);
	}
	
	/**
	 * Validasi form registrasi
	 * Cek CSRF, email unique, password requirements
	 * 
	 * @return array Error messages
	 */
	private function validateForm() 
	{
		helper('form_requirement');
		
		$error = [];
		
		$validationMessage = csrf_validation();

		// Cek CSRF token
		if ($validationMessage) {
			return [$validationMessage['message']];
		}
		
		// Cek email belum diaktifkan
		$email = trim($this->request->getPost('email'));
		if ($email) {
			if ($this->model->getUserByEmail($email)) {
				$error['message'] = 'Email sudah terdaftar tetapi belum diaktifkan, silakan <a href="' . $this->config->baseURL . 'register/resendlink" title="Kirim ulang link aktivasi">aktifkan disini</a>';
				return $error;
			}
		}
		
		$validation =  \Config\Services::validation();
		$validation->setRules(
			[
				'nama' => ['label' => 'Nama', 'rules' => 'trim|required|min_length[5]'],
				'password' => ['label' => 'Password', 'rules' => 'trim|required'],
				'email' => [
					'label'  => 'Email',
					'rules'  => 'trim|required|valid_email|is_unique[core_user.email]',
					'errors' => [
						'is_unique' => 'Email sudah digunakan',
						'valid_email' => 'Alamat email tidak valid'
					]
				],
				'password_confirm' => [
					'label'  => 'Ulangi Password',
					'rules'  => 'trim|required|matches[password]',
					'errors' => [
						'required' => 'Ulangi password tidak boleh kosong',
						'matches' => 'Ulangi password tidak cocok dengan password'
					]
				]
			]
		);

		$validate = $validation->withRequest($this->request)->run();
		
		if ($validate) 
		{
			// Validasi password requirements
			$password = $this->request->getPost('password');
			$invalid = password_requirements($password);
			if ($invalid) {
				$error = array_merge($error, $invalid);
			}
			
			// Validasi email requirements
			$invalid = email_requirements($email);
			if ($invalid) {
				$error = array_merge($error, $invalid);
			}
		} else {
			$error = $validation->getErrors();
		}

		return $error;
	}
	
	/**
	 * Validasi form resend link
	 * 
	 * @return array Error messages
	 */
	private function validateFormResendlink() 
	{
		$error = false;
		
		$validationMessage = csrf_validation();

		// Cek CSRF token
		if ($validationMessage) {
			return [$validationMessage['message']];
		}
		
		$validation =  \Config\Services::validation();
		$validation->setRules(
			[
				'email' => [
					'label'  => 'Email',
					'rules'  => 'trim|required|valid_email',
					'errors' => [
						'valid_email' => 'Alamat email tidak valid'
					]
				]
			]
		);

		$validate = $validation->withRequest($this->request)->run();
		
		if ($validate) 
		{		
			$email = $this->request->getPost('email');
			$user = $this->model->getUserByEmail($email);
			if ($user) {
				if ($user['verified'] == 1) {
					$error[] = 'Akun sudah pernah diaktifkan, silakan <a href="' . $this->config->baseURL . 'login" title="Login">login</a> ke akun Anda';
				}
			} else {
				$error[] = 'Email belum terdaftar, silakan <a href="' . $this->config->baseURL . 'register" title="Register Akun">register akun disini</a>';
			}
		} else {
			$error = $validation->getErrors();
		}

		return $error;
	}
}
