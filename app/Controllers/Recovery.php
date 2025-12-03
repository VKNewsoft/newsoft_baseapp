<?php
/**
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Controllers;
use App\Models\RecoveryModel;
use \Config\App;
use App\Libraries\Auth;

class Recovery extends \App\Controllers\BaseController
{
	protected $model = '';
	
	public function __construct() {
		parent::__construct();
		$this->model = new RecoveryModel;	
		$this->data['site_title'] = 'Recovery Account';
		
		helper(['cookie', 'form', 'util', 'format_helper']);
		
		$this->addJs($this->config->baseURL . 'public/vendors/jquery/jquery.min.js');
		$this->addJs($this->config->baseURL . 'public/vendors/bootstrap/js/bootstrap.min.js');
										
		$this->addStyle($this->config->baseURL . 'public/vendors/bootstrap/css/bootstrap.min.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/fontawesome/css/fontawesome.min.css');
		$this->addStyle($this->config->baseURL . 'public/themes/modern/css/register.css');

		$this->addJs($this->config->baseURL . 'public/vendors/jquery.pwstrength.bootstrap/pwstrength-bootstrap.min.js');
		$this->addJs($this->config->baseURL . 'public/themes/modern/js/password-meter.js');
		
	}
	
	public function index()
	{
		$message = [];
		$this->data['title'] = 'Reset Password';
		
		if ($this->request->getPost('submit')) 
		{
			// Cek isian form
			$formError = $this->validateForm();
			
			$error = false;
			$message['status'] = 'error';
			if ($formError) {
				$message['message'] = $formError;
				$error = true;
			}
			
			// Submit data
			if (!$error) 
			{
				if ($message['status'] == 'error') {
					$message['message'] = 'Terjadi kesalahan dalam mengirim link reset password.';
					$error = false;
				}
				$message['status'] = 'success';
				$message['message'] = 'Link untuk mereset password sudah berhasil dikirim. Silakan Cek WhatsApp Anda!';
			}
		}
		
		$file = 'form-recovery';
		if ($this->request->getPost('submit') && !$error) {
			$file = 'show_message.php';
		}
		

		$this->data['message'] = $message;
		return view('themes/modern/register/' . $file, $this->data);
	}
	
	public function reset($user) 
	{
		
		$error = false;
		$message = [];
		$this->data['title'] = 'Reset Password';
		
		// if (empty($_GET['token'])) {
		// 	$message['message'] = 'Token tidak ditemukan';
		// 	$error = true;
		// } else {
		
		// 	@list($selector, $url_token) = explode(':', $_GET['token']);
		// 	if (!$selector || !$url_token) {
		// 		$message['message'] = 'Token tidak ditemukan';
		// 		$error = true;
		// 	}
		// }
		
		// if (!$error) {
						
		// 	$dbtoken = $this->model->checkToken($selector);
		// 	if ($dbtoken) 
		// 	{
		// 		$error = false;
		// 		$auth = new Auth;
		// 		if ($dbtoken['expires'] < date('Y-m-d H:i:s')) {
		// 			$message['message'] = 'Link expired, silakan request <a href="'. $this->config->baseURL.'/recovery">link reset password</a> yang baru';
		// 			$error = true;
		// 		} 
		// 		else if (!$auth->validateToken($url_token, $dbtoken['token'])) {
		// 			$message['message'] = 'Token invalid, silakan request <a href="'. $this->config->baseURL.'/recovery">link reset password</a> yang baru';
		// 			$error = true;
		// 		}
				
		// 	} else {
		// 		$message['message'] = 'Token tidak ditemukan, silakan request <a href="'. $this->config->baseURL .'/recovery">link reset password</a> yang baru';
		// 		$error = true;
		// 	}
		// }		

		if (!$error)
		{			
			if ($this->request->getPost('submit')) {
				// Cek isian form
				$formError = $this->validateFormReset();

				if ($formError) {
					$message['message'] = $formError;
					$error = true;
				}
				
				// Submit data
				if (!$error) {
					$update = $this->model->resetPassword(decrypt($user), $this->request->getPost('password'));
					
					if ($update) {
						$message['status'] = 'ok';
						$message['message'] = 'Password Anda berhasil diupdate, sekarang Anda dapat <a href="'.$this->config->baseURL.'login">Login</a> menggunakan password baru Anda';
					} else {
						$email_config = new \Config\EmailConfig;
						$message['message'] = 'Password gagal diupdate, silakan coba dilain waktu, atau hubungi <a href="mailto:' . $email_config->emailSupport . '" title="Hubungi kami via email">' . $email_config->emailSupport . '</a>';
						$error = true;
					}		
					
				}
			}
		}
		
		if ($error) {
			$message['status'] = 'error';
		}
		
		$file = 'form-reset-password.php';
		if ($this->request->getPost('submit') && !$error) {
			$file = 'show_message.php';
		}
		
		$this->data['message'] = $message;
		return view('themes/modern/register/' . $file, $this->data);
	}
	
	private function validateForm() 
	{
		$error = [];
		
		$validationMessage = csrf_validation();

		// Cek CSRF token
		if ($validationMessage) {
			return [$validationMessage['message']];
		}
		
		$validation =  \Config\Services::validation();
		$validation->setRules(
			[
				'nohp' => [
					'label'  => 'Nomor HP',
					'rules'  => 'trim|required|numeric',
					'errors' => [
						'required' => 'Nomor HP wajib diisi',
						'numeric'  => 'Nomor HP hanya boleh mengandung angka'
					]
				]
			]
		);

		$validate = $validation->withRequest($this->request)->run();
		
		if ($validate) 
		{		
		$nohp = $this->request->getPost('nohp');
		$user = $this->model->getUserByPhone($nohp);
			if ($user) {
				$cek = $this->model->checkPhoneUser($nohp);
				if($cek > 1){
					$error[] = 'Terdapat lebih dari satu akun yang menggunakan nomor tersebut, silakan hubungi HRD.';
				} else {
					$url = $this->config->baseURL . 'recovery/reset/' . encrypt($user['id_user']);
					sendWaResetPassword($nohp, $url);
				}
			} else {
				$error[] = 'Nomor belum terdaftar, silakan hubungi HRD.';
			}
		} else {
			$error = $validation->getErrors();
		}

		return $error;
	}

	private function validateFormReset() {
		
		$error = [];
	
		$validationMessage = csrf_validation();
		// Cek CSRF token
		if ($validationMessage) {
			return [$validationMessage['message']];
		}
		
		$validation =  \Config\Services::validation();
		$validation->setRules(
			[
				'password' => ['label' => 'Password', 'rules' => 'trim|required'],
				'password_confirm' => [
					'label'  => 'Ulangi Password',
					'rules'  => 'trim|required|matches[password]',
					'errors' => [
						'required' => 'Ulangi password tidak boleh kosong'
						, 'matches' => 'Ulangi confirm password tidak cocok dengan password baru'
					]
				]
			]
			
		);
		
		$validate = $validation->withRequest($this->request)->run();
		
		if ($validate) 
		{
			helper('form_requirement');			
			$invalid = password_requirements($this->request->getPost('password'));
			if ($invalid) {
				$error = array_merge($error, $invalid);
			}
		} else {
			$error = $validation->getErrors();
		}

		return $error;
	}
}
