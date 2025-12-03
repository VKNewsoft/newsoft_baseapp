<?php
/**
 * RegisterModel - Model untuk registrasi user baru
 * 
 * Model ini menangani proses registrasi user termasuk:
 * - Validasi email
 * - Pembuatan akun
 * - Pengiriman email aktivasi
 * - Verifikasi token
 * 
 * @package App\Models
 * @year 2020-2025
 */

namespace App\Models;
use App\Libraries\Auth;

class RegisterModel extends \App\Models\BaseModel
{
	/**
	 * Mendapatkan user berdasarkan email
	 * 
	 * @param string $email Email user
	 * @return array|null Data user
	 */
	public function getUserByEmail($email) 
	{
		return $this->db->table('core_user')
			->where('email', $email)
			->get()
			->getRowArray();
	}
	
	/**
	 * Kirim ulang link aktivasi ke email user
	 * 
	 * @return array Status dan pesan hasil operasi
	 */
	public function resendLink() 
	{
		$error = false;
		$message = ['status' => 'error'];
		
		$user = $this->getUserByEmail($this->request->getPost('email'));
					
		$this->db->transBegin();
		
		// Hapus token lama
		$this->db->table('core_user_token')->delete([
			'action' => 'activation', 
			'id_user' => $user['id_user']
		]);

		// Generate token baru
		$auth = new Auth;
		$token = $auth->generateDbToken();	
		
		$dataDb = [
			'selector' => $token['selector'],
			'token' => $token['db'],
			'action' => 'activation',
			'id_user' => $user['id_user'],
			'created' => date('Y-m-d H:i:s'),
			'expires' => date('Y-m-d H:i:s', strtotime('+1 hour'))
		];
		
		$insertToken = $this->db->table('core_user_token')->insert($dataDb);
		
		if ($insertToken) {
			$sendEmail = $this->sendConfirmEmail($token, $user, 'link_aktivasi');
						
			if ($sendEmail['status'] == 'ok') {
				$this->db->transCommit();
				$emailConfig = new \Config\EmailConfig;
				$message['status'] = 'ok';
				$message['message'] = '
				Link aktivasi berhasil dikirim ke alamat email: <strong>'. $this->request->getPost('email') . '</strong>, silakan gunakan link tersebut untuk aktivasi akun Anda<br/></br>Biasanya, email akan sampai kurang dari satu menit, namun jika lebih dari lima menit email belum sampai, coba cek folder spam. Jika email benar benar tidak sampai, silakan hubungi kami di <a href="mailto:'.$emailConfig->emailSupport.'" target="_blank">'.$emailConfig->emailSupport.'</a>';
			} else {
				$message['message'] = 'Error: Link aktivasi gagal dikirim... <strong>' . $sendEmail['message'] . '</strong>';
				$error = true;
			}
		} else {
			$emailConfig = new \Config\EmailConfig;
			$message['message'] = 'Gagal menyimpan data token, silakan hubungi kami di: <a href="mailto:'.$emailConfig->emailSupport.'" target="_blank">'.$emailConfig->emailSupport.'</a>';
			$error = true;
		}
		
		if ($error) {
			$this->db->transRollback();
		}
		
		return $message;
	}
	
	/**
	 * Cek apakah user exists berdasarkan ID
	 * 
	 * @param int $idUser ID user
	 * @return array|null Data user
	 */
	public function checkUserById($idUser) 
	{
		return $this->db->table('core_user')
			->where('id_user', $idUser)
			->get()
			->getRowArray();
	}
	
	/**
	 * Update user menjadi verified setelah aktivasi
	 * Hapus token yang sudah digunakan
	 * 
	 * @param array $dbtoken Data token dari database
	 * @return bool Status berhasil/gagal
	 */
	public function updateUser($dbtoken) 
	{
		$this->db->transStart();

		// Hapus token yang digunakan
		$this->db->table('core_user_token')->delete(['selector' => $dbtoken['selector']]);
		$this->db->table('core_user_token')->delete([
			'action' => 'register', 
			'id_user' => $dbtoken['id_user']
		]);
		
		// Set user verified
		$this->db->table('core_user')->update(
			['verified' => 1], 
			['id_user' => $dbtoken['id_user']]
		);
		
		return $this->db->transComplete();
	}
	
	/**
	 * Cek apakah token valid dan belum expired
	 * 
	 * @param string $selector Selector token
	 * @return array|null Data token
	 */
	public function checkToken($selector) 
	{
		return $this->db->table('core_user_token')
			->where('selector', $selector)
			->get()
			->getRowArray();
	}
	
	/**
	 * Proses registrasi user baru
	 * Mendukung 3 metode aktivasi: langsung, manual, email
	 * 
	 * @return array Status dan pesan hasil operasi
	 */
	public function insertUser() 
	{
		$error = false;
		$message = ['status' => 'error'];
		
		$this->db->transBegin();
		
		// Ambil setting registrasi
		$settingRegister = $this->getSettingRegistrasi();
		$verified = $settingRegister['metode_aktivasi'] == 'langsung' ? 1 : 0;
		
		// Siapkan data user
		$dataDb = [
			'nama' => $this->request->getPost('nama'),
			'email' => $this->request->getPost('email'),
			'username' => $this->request->getPost('username'),
			'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
			'verified' => $verified,
			'status' => 1,
			'created' => date('Y-m-d H:i:s'),
			'id_module' => $settingRegister['id_module']
		];

		$insertUser = $this->db->table('core_user')->insert($dataDb);
		$idUser = $this->db->insertID();
		
		if (!$idUser) {
			$message['message'] = 'System error, please try again later...';
			$error = true;
		} else {
			// Assign default role
			$setting = $this->db->table('core_setting')
				->where('type', 'register')
				->where('param', 'id_role')
				->get()
				->getRowArray();
			
			$idRole = $setting['value'];
			
			$this->db->table('core_user_role')->insert([
				'id_user' => $idUser,
				'id_role' => $idRole
			]);
			
			// Proses berdasarkan metode aktivasi
			if ($settingRegister['metode_aktivasi'] == 'manual') {
				$message['message'] = 'Terima kasih telah melakukan registrasi, aktivasi akun Anda menunggu persetujuan Administrator. Terima Kasih';
				
			} elseif ($settingRegister['metode_aktivasi'] == 'langsung') {
				$message['message'] = 'Terima kasih telah melakukan registrasi, akun Anda otomatis aktif dan langsung dapat digunakan, silakan <a href="' . base_url() . '/login">login disini</a>';
				
			} elseif ($settingRegister['metode_aktivasi'] == 'email') {
				// Generate token untuk email aktivasi
				$auth = new Auth;
				$token = $auth->generateDbToken();					
				
				$tokenData = [
					'selector' => $token['selector'],
					'token' => $token['db'],
					'action' => 'register',
					'id_user' => $idUser,
					'created' => date('Y-m-d H:i:s'),
					'expires' => date('Y-m-d H:i:s', strtotime('+1 hour'))
				];
				
				$this->db->table('core_user_token')->insert($tokenData);
				
				// Kirim email konfirmasi
				$sendEmail = $this->sendConfirmEmail($token, [
					'nama' => $this->request->getPost('nama'),
					'email' => $this->request->getPost('email')
				]);
			
				if ($sendEmail['status'] == 'error') {
					$message['message'] = 'Error: Link konfirmasi gagal dikirim... <strong>' . $sendEmail['message'] . '</strong>';
					$error = true;
				} else {
					$message['message'] = 'Terima kasih telah melakukan registrasi, untuk memastikan bahwa kamu adalah pemilik alamat email <strong>' . $this->request->getPost('email') . '</strong>, mohon klik link konfirmasi yang baru saja kami kirimkan ke alamat email tersebut<br/><br/>Biasanya, email akan sampai kurang dari satu menit, namun jika lebih dari lima menit email belum sampai, coba cek folder spam. Jika email benar benar tidak sampai, silakan hubungi kami di support@newsoftdev.com';
				}
			}
		}
		
		if ($error) {
			$this->db->transRollback();
		} else {
			$this->db->transCommit();
			$message['status'] = 'ok';
		}
	
		return $message;
	}
	
	/**
	 * Kirim email konfirmasi registrasi
	 * 
	 * @param array $token Token data (selector, external)
	 * @param array $user Data user (nama, email)
	 * @param string $type Tipe email: email_confirm atau link_aktivasi
	 * @return array Status pengiriman email
	 */
	private function sendConfirmEmail($token, $user, $type = 'email_confirm')
	{
		helper('email_registrasi');
		
		if ($type == 'email_confirm') {
			$emailText = email_registration_content();
		} else {
			$emailText = email_resendlink_content();
		}
		
		$urlToken = $token['selector'] . ':' . $token['external'];
		$url = base_url() .'/register/confirm?token='.$urlToken;
		
		$emailContent = str_replace('{{NAME}}', $user['nama'], $emailText);
		$emailContent = str_replace('{{url}}', $url, $emailContent);
		
		$emailConfig = new \Config\EmailConfig;
		$emailData = [
			'from_email' => $emailConfig->from,
			'from_title' => 'Newsoftdev',
			'to_email' => $user['email'],
			'to_name' => $user['nama'],
			'email_subject' => 'Konfirmasi Registrasi Akun',
			'email_content' => $emailContent,
			'images' => ['logo_text' => ROOTPATH . 'public/images/logo_text.png']
		];
		
		require_once('app/Libraries/SendEmail.php');

		$emaillib = new \App\Libraries\SendEmail;
		$emaillib->init();
		$emaillib->setProvider($emailConfig->provider);
		
		return $emaillib->send($emailData);
	}
}
?>