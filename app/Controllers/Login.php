<?php
/**
 * Login Controller - Menangani autentikasi user
 * 
 * Controller ini menangani:
 * - Proses login dan logout
 * - Validasi CSRF token
 * - Remember me functionality
 * - Failed login attempt tracking
 * 
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Controllers;

use App\Models\Builtin\LoginModel;
use Config\App;
use App\Libraries\Auth;
use CodeIgniter\HTTP\Exceptions\HTTPException;

class Login extends \App\Controllers\BaseController
{
	protected $model;
	
	/**
	 * Constructor - Inisialisasi controller
	 */
	public function __construct() 
	{
		parent::__construct();
		
		// Inisialisasi model
		$this->model = new LoginModel;
		
		// Set page title
		$this->data['site_title'] = 'Login ke akun Anda';
		
		// Load JavaScript untuk login
		$this->addJs($this->config->baseURL . 'public/themes/modern/js/login.js');
		
		// Load helper
		helper(['cookie', 'form']);
	}
	
	/**
	 * Halaman login utama
	 * Handle GET (tampil form) dan POST (proses login)
	 */
	public function index()
	{
		// Redirect jika sudah login
		$this->mustNotLoggedIn();
		
		$this->data['status'] = '';

		// Proses login jika ada POST password
		if ($this->request->getPost('password')) {
			// Cek apakah device sudah terlalu banyak failed attempt
			$cekFalse = $this->model->cekFalseAttemptDevice();
			
			if ($cekFalse['status'] == 0) {
				// Proses login
				$this->login();
				
				// Jika request via AJAX
				if ($this->request->getPost('ajax') == 'true') {
					if ($this->data['status'] == 'error') {
						// Record failed attempt
						$this->model->insertFalseAttemptDevice();
						
						echo json_encode([
							'status' => 'error',
							'message' => $this->data['message']
						]);
					} else {
						// Login berhasil, clear failed attempt
						$deviceId = generateDeviceId();
						$this->model->clearFalseAttempt($deviceId);
						
						echo json_encode(['status' => 'ok']);
					}
					exit;
				}
				
				// Redirect ke dashboard jika berhasil login
				if ($this->session->get('logged_in')) {
					return redirect()->to($this->config->baseURL);
				}
			} else {
				// Terlalu banyak failed attempt
				$this->data['status'] = 'error';
				$this->data['message'] = $cekFalse['message'];
				
				echo json_encode($this->data);
				exit;
			}
		}
		
		// Ambil setting registrasi
		$settingQuery = $this->model->getSettingRegistrasi();
		$this->data['setting_registrasi'] = [];
		
		foreach ($settingQuery as $val) {
			$this->data['setting_registrasi'][$val['param']] = $val['value'];
		}
		
		// Set header dan CSRF token
		$this->response->setHeader('Required-auth', '1');
		csrf_settoken();
		
		$this->data['style'] = ' style="max-width:375px"';
		
		// Tampilkan form login
		return view('themes/modern/builtin/login', $this->data);
	}
	
	/**
	 * Proses login - validasi username dan password
	 * 
	 * @return void
	 */
	private function login()
	{
		// Validasi CSRF token
		$validationMessage = csrf_validation();

		if ($validationMessage) {
			$this->data['status'] = 'error';
			$this->data['message'] = $validationMessage['message'];
			return;
		}
		
		// Cek user berdasarkan username
		$username = $this->request->getPost('username');
		$user = $this->model->checkUser($username);
		
		$error = false;
		$message = '';

		if ($user) {
			// Cek apakah ada username duplikat
			$cekUsername = $this->model->checkUsername($user['username']);
			if ($cekUsername > 1) {
				$message = 'Maaf, username yang Anda masukkan sudah terdaftar. Silakan hubungi admin untuk mendapatkan username yang baru.';
				$error = true;
			}
			
			// Cek apakah user sudah verified
			if (!$error && $user['verified'] == 0) {
				$message = 'Username belum aktif';
				$error = true;
			}
			
			// Validasi password
			$password = $this->request->getPost('password');
			if (!$error && !password_verify($password, $user['password'])) {
				$message = 'Password salah';
				$error = true;
			}
			
			// Cek apakah user sudah resign
			if (!$error && !empty($user['data_resign'])) {
				$message = 'Anda sudah resign, akun anda sudah dinonaktifkan';
				$error = true;
			}
		} else {
			$message = 'Username tidak ditemukan';
			$error = true;
		}

		// Jika ada error, set status dan message
		if ($error) {
			$this->data['status'] = 'error';
			$this->data['message'] = $message;
			return;
		}

		// Set remember me token jika dicentang
		if ($this->request->getPost('remember')) {
			$this->model->setUserToken($user);
		}

		// Set session user
		$this->session->set('user', $user);
		$this->session->set('logged_in', true);
	}
	
	/**
	 * Refresh data login user dari database
	 * Update session dengan data terbaru
	 * 
	 * @return void
	 */
	public function refreshLoginData() 
	{
		$email = $this->session->get('user')['email'];
		$result = $this->model->checkUser($email);
		$this->session->set('user', $result);
	}
	
	/**
	 * Logout user
	 * Hapus session dan cookie remember me
	 * 
	 * @return void
	 */
	public function logout() 
	{
		$user = $this->session->get('user');
		
		// Hapus cookie remember me
		if ($user) {
			$this->model->deleteAuthCookie($user['id_user']);
		}
		
		// Destroy session
		$this->session->destroy();
	
		// Redirect ke halaman login
		header('Location: ' . $this->config->baseURL . 'login');
		exit;
	}
}
