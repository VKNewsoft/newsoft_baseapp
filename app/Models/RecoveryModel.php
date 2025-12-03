<?php
/**
 * RecoveryModel - Model untuk password recovery
 * 
 * Model ini menangani proses reset password melalui email
 * termasuk generate token, validasi, dan update password
 * 
 * @package App\Models
 * @year 2020-2025
 */

namespace App\Models;
use App\Libraries\Auth;

class RecoveryModel extends \App\Models\BaseModel
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
	 * Mendapatkan user berdasarkan nomor HP dari employee detail
	 * 
	 * @param string $nohp Nomor HP
	 * @return array|null Data user
	 */
	public function getUserByPhone($nohp) 
	{
		return $this->db->table('core_user a')
			->select('a.*')
			->join('hrm_employee_detail e', 'a.id_user = e.id_user', 'left')
			->where('e.phone', $nohp)
			->get()
			->getRowArray();
	}

	/**
	 * Cek apakah nomor HP terdaftar dan user masih aktif (belum resign)
	 * 
	 * @param string $nohp Nomor HP
	 * @return int Jumlah user yang cocok
	 */
	public function checkPhoneUser($nohp) 
	{
		$query = $this->db->table('hrm_employee_detail a')
			->selectCount('*', 'jml')
			->join('hrm_resign b', 'a.id_karyawan = b.id_karyawan AND b.isDeleted = 0 AND b.tgl_resign < CURDATE()', 'left')
			->where('a.phone', $nohp)
			->where('a.isDeleted', 0)
			->where('b.id_resign IS NULL')
			->get()
			->getRowArray();
		
		return $query['jml'];
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
	 * Kirim link reset password ke email user
	 * 
	 * @return array Status dan pesan hasil operasi
	 */
	public function sendLink() 
	{
		$error = false;
		$message = ['status' => 'error'];
		
		$email = $this->request->getPost('email');
		$user = $this->getUserByEmail($email);
		
		$this->db->transStart();
		
		// Hapus token recovery lama
		$this->db->table('core_user_token')->delete([
			'action' => 'recovery', 
			'id_user' => $user['id_user']
		]);
		
		// Generate token baru
		$auth = new Auth;
		$token = $auth->generateDbToken();
		
		$dataDb = [
			'selector' => $token['selector'],
			'token' => $token['db'],
			'action' => 'recovery',
			'id_user' => $user['id_user'],
			'created' => date('Y-m-d H:i:s'),
			'expires' => date('Y-m-d H:i:s', strtotime('+1 hour'))
		];
		
		$insertToken = $this->db->table('core_user_token')->insert($dataDb);
		
		if ($insertToken) {
			$urlToken = $token['selector'] . ':' . $token['external'];
			$url = base_url().'/recovery/reset?token='.$urlToken;
			
			helper('email_registrasi');
			$emailConfig = new \Config\EmailConfig;
			
			$emailData = [
				'from_email' => $emailConfig->from,
				'from_title' => 'Newsoftdev',
				'to_email' => $email,
				'to_name' => $email,
				'email_subject' => 'Reset Password',
				'email_content' => str_replace('{{url}}', $url, email_recovery_content()),
				'images' => ['logo_text' => ROOTPATH . 'public/images/logo_text.png']
			];
			
			require_once('app/Libraries/SendEmail.php');
			$emaillib = new \App\Libraries\SendEmail;
			$emaillib->init();
			$emaillib->setProvider($emailConfig->provider);
			$sendEmail = $emaillib->send($emailData);
		
			if ($sendEmail['status'] == 'ok') {
				$this->db->transCommit();
				$message['status'] = 'ok';
				$message['message'] = '
				Link reset password berhasil dikirim ke alamat email: <strong>'. $email . '</strong>, silakan gunakan link tersebut untuk mereset password Anda<br/></br>Biasanya, email akan sampai kurang dari satu menit, namun jika lebih dari lima menit email belum sampai, coba cek folder spam. Jika email benar benar tidak sampai, silakan hubungi kami di <a href="mailto:'.$emailConfig->emailSupport.'" target="_blank">'.$emailConfig->emailSupport .'</a>';
			} else {
				$message['message'] = 'Error: Link reset password gagal dikirim... <strong>' . $sendEmail['message'] . '</strong>';
				$error = true;
			}
		} else {
			$emailConfig = new \Config\EmailConfig;
			$message['message'] = 'Gagal menyimpan data token, silakan hubungi kami di: <a href="mailto:'.$emailConfig->emailSupport .'" target="_blank">'.$emailConfig->emailSupport.'</a>';
			$error = true;
		}
		
		if ($error) {
			$this->db->transRollback();
		}
		
		return $message;
	}
	
	/**
	 * Update password user setelah reset
	 * 
	 * @param array $dbtoken Data token dari database
	 * @return bool Status update
	 */
	public function updatePassword($dbtoken) 
	{
		$this->db->table('core_user_token')->delete(['selector' => $dbtoken['selector']]);
		
		return $this->db->table('core_user')->update(
			['password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT)], 
			['id_user' => $dbtoken['id_user'], 'isDeleted' => 0]
		);
	}

	/**
	 * Reset password berdasarkan nomor HP
	 * 
	 * @param int $noHp ID user (bukan nomor HP sebenarnya)
	 * @param string $newPass Password baru
	 * @return bool Status update
	 */
	public function resetPassword($noHp, $newPass) 
	{
		return $this->db->table('core_user')->update(
			['password' => password_hash($newPass, PASSWORD_DEFAULT)], 
			['id_user' => $noHp, 'isDeleted' => 0]
		);
	}
}
?>