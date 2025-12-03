<?php
/**
 * LoginModel - Model untuk manajemen login dan autentikasi
 * 
 * Model ini menangani:
 * - Validasi login
 * - Remember me token
 * - Failed login attempt tracking
 * - IP blocking untuk keamanan
 * 
 * @package App\Models\Builtin
 * @year 2020-2025
 */

namespace App\Models\Builtin;
use App\Libraries\Auth;

class LoginModel extends \App\Models\BaseModel
{	
	/**
	 * Record login dan validasi apakah tenant aktif
	 * 
	 * @return array Status login
	 */
	public function recordLogin() 
	{
		$username = $this->request->getPost('username'); 
		
		$dataUser = $this->db->table('core_user')
			->select('core_user.id_user, core_user.id_company, core_company.tenant_aktif')
			->join('core_company', 'core_user.id_company = core_company.id_company')
			->where('core_company.tenant_aktif', 'Y')
			->where('core_user.username', $username)
			->get()
			->getRow();

		if (!$dataUser) {
			return [
				'status' => true,
				'error' => 'error',
				'pesan' => 'Perusahaan anda sedang tidak diaktifkan'
			];
		}
		
		return ['status' => false];
	}
	
	/**
	 * Set token untuk remember me cookie
	 * Token di-encrypt dan disimpan di database
	 * 
	 * @param array $user Data user
	 * @return void
	 */
	public function setUserToken($user) 
	{
		$auth = new Auth;
		$token = $auth->generateDbToken();
		$expiredTime = time() + (7 * 24 * 3600); // 7 hari
		
		// Set cookie remember me
		setcookie('remember', $token['selector'] . ':' . $token['external'], $expiredTime, '/');
		
		// Simpan token ke database
		$dataDb = [
			'id_user' => $user['id_user'],
			'selector' => $token['selector'],
			'token' => $token['db'],
			'action' => 'remember',
			'created' => date('Y-m-d H:i:s'),
			'expires' => date('Y-m-d H:i:s', $expiredTime)
		];

		$this->db->table('core_user_token')->insert($dataDb);
	}
	
	/**
	 * Hapus cookie autentikasi dan token dari database
	 * Dipanggil saat logout
	 * 
	 * @param int $idUser ID user
	 * @return void
	 */
	public function deleteAuthCookie($idUser) 
	{
		$this->db->table('core_user_token')->delete([
			'action' => 'remember', 
			'id_user' => $idUser
		]);
		
		// Hapus cookie
		setcookie('remember', '', time() - 360000, '/');	
	}
	
	/**
	 * Ambil setting registrasi dari database
	 * 
	 * @return array Setting registrasi
	 */
	public function getSettingRegistrasi() 
	{
		return $this->db->table('core_setting')
			->where('type', 'register')
			->get()
			->getResultArray();
	}

	/**
	 * Insert log percobaan login gagal berdasarkan device
	 * Untuk tracking security
	 * 
	 * @return void
	 */
	public function insertFalseAttemptDevice()
	{
		$deviceId = generateDeviceId();
		$dataDb = [
			'device_id' => $deviceId,
			'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
			'created_at' => date('Y-m-d H:i:s'),
			'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
			'request_url' => $_SERVER['REQUEST_URI'] ?? ''
		];
		
		$this->db->table('hrm_log_activity_block')->insert($dataDb);
	}

	/**
	 * Insert log IP untuk tracking akses
	 * 
	 * @return void
	 */
	public function insertLogIp()
	{
		$deviceId = generateDeviceId();
		$dataDb = [
			'device_id' => $deviceId,
			'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
			'created_at' => date('Y-m-d H:i:s'),
			'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
			'request_url' => 'cek_ip'
		];
		
		$this->db->table('hrm_log_activity_block')->insert($dataDb);
	}

	/**
	 * Block IP address yang mencurigakan
	 * Masukkan ke daftar blacklist IP
	 * 
	 * @return void
	 */
	public function insertBlokIp()
	{
		$deviceId = generateDeviceId();
		$dataDb = [
			'device_id' => $deviceId,
			'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
			'created_at' => date('Y-m-d H:i:s'),
			'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
		];
		
		$this->db->table('list_block_ip')->insert($dataDb);
	}

	/**
	 * Cek pola IP untuk deteksi akses mencurigakan
	 * Jika terlalu banyak request dalam waktu singkat, block IP
	 * 
	 * @return array Status dan pesan
	 */
	public function cekPolaIp()
	{
		$result = ['status' => 0, 'message' => ''];
		
		// Cek apakah IP sudah di-block sebelumnya
		$cekBlok = $this->cekBlokIpList();
		
		if ($cekBlok == 0) {
			$this->insertLogIp();
			
			// Konfigurasi
			$batasWaktu = 10; // dalam detik
			$jatahAttempt = 3;
			$deviceId = generateDeviceId();
			
			// Hitung berapa kali IP ini mencoba akses dalam batas waktu
			$builder = $this->db->table('hrm_log_activity_block');
			$builder->where('request_url', 'cek_ip');
			$builder->where('device_id', $deviceId);
			$builder->where('is_deleted', 0);
			$builder->where("TIMESTAMPDIFF(SECOND, created_at, NOW()) <=", $batasWaktu, false);
			$count = $builder->countAllResults();
			
			// Jika melebihi batas, block IP
			if ($count >= $jatahAttempt) {
				$this->insertBlokIp();
				$result['status'] = 1;
				$result['message'] = 'Access Denied';
			}
		} else {
			$result['status'] = 1;
			$result['message'] = 'Access Denied';
		}
		
		return $result;
	}

	/**
	 * Cek apakah IP ada di daftar blacklist
	 * 
	 * @return int Jumlah IP yang di-block
	 */
	public function cekBlokIpList()
	{
		$ipAddr = $_SERVER['REMOTE_ADDR'] ?? '';
		
		return $this->db->table('list_block_ip')
			->where('ip_address', $ipAddr)
			->where('is_deleted', 0)
			->countAllResults();
	}

	/**
	 * Cek percobaan login gagal berdasarkan device
	 * Jika sudah 3x gagal, user harus menunggu beberapa menit
	 * 
	 * @return array Status dan pesan
	 */
	public function cekFalseAttemptDevice()
	{
		$deviceId = generateDeviceId();
		$batasWaktu = 10; // dalam menit
		$jatahAttempt = 3;
		$requestUri = $_SERVER['REQUEST_URI'] ?? '';
		
		$result = ['status' => 0, 'message' => ''];
		
		// Hitung total percobaan login gagal untuk device ini
		$count = $this->db->table('hrm_log_activity_block')
			->where('request_url', $requestUri)
			->where('device_id', $deviceId)
			->where('is_deleted', 0)
			->countAllResults();
		
		// Jika sudah melebihi jatah attempt
		if ($count >= $jatahAttempt) {
			// Cek kapan last attempt
			$lastAttempt = $this->db->table('hrm_log_activity_block')
				->select('id, TIMESTAMPDIFF(MINUTE, created_at, NOW()) as selisih_last')
				->where('request_url', $requestUri)
				->where('device_id', $deviceId)
				->where('is_deleted', 0)
				->orderBy('id', 'DESC')
				->limit(1)
				->get()
				->getRowArray();
			
			if ($lastAttempt) {
				// Jika sudah lewat batas waktu, reset attempt
				if ($lastAttempt['selisih_last'] >= $batasWaktu) {
					$this->clearFalseAttempt($deviceId);
					$result['status'] = 0;
				} else {
					$result['status'] = 1;
					$result['message'] = 'Gagal login sudah 3 kali. Anda harus menunggu ' . $batasWaktu . ' menit';
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * Clear/reset failed login attempt untuk device tertentu
	 * 
	 * @param string $deviceId Device ID
	 * @return void
	 */
	public function clearFalseAttempt($deviceId)
	{
		$this->db->table('hrm_log_activity_block')
			->update(
				['is_deleted' => 1], 
				['device_id' => $deviceId, 'is_deleted' => 0]
			);
	}
}
?>