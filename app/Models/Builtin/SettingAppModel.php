<?php
/**
 * Model Setting Aplikasi
 * 
 * Mengelola pengaturan aplikasi termasuk logo login, logo app, favicon,
 * footer, dan konfigurasi tampilan lainnya.
 * 
 * @package    App\Models\Builtin
 * @author     Newsoft Developer
 * @copyright  2020-2023
 */

namespace App\Models\Builtin;

class SettingAppModel extends \App\Models\BaseModel
{
	/**
	 * Mendapatkan semua setting aplikasi
	 * 
	 * @return array Data setting aplikasi dengan type='app'
	 */
	public function getSettingAplikasi() {
		return $this->db->table('core_setting')
			->where('type', 'app')
			->get()
			->getResultArray();
	}
	
	/**
	 * Mendapatkan setting user (layout preferences)
	 * 
	 * @return array Data setting user untuk layout
	 */
	public function getUserSetting() {
		$userSession = session()->get('user');
		$idUser = $userSession['id_user'] ?? 0;
		
		return $this->db->table('core_setting_user')
			->where('id_user', $idUser)
			->where('type', 'layout')
			->get()
			->getResultArray();
	}
	
	/**
	 * Simpan data setting aplikasi termasuk upload file gambar
	 * 
	 * @return array Status dan pesan hasil operasi
	 */
	public function saveData() 
	{
		helper(['util', 'upload_file']);
		
		// Ambil data setting saat ini
		$query = $this->db->table('core_setting')
			->where('type', 'app')
			->get()
			->getResultArray();
		
		$currDb = [];
		foreach($query as $val) {
			$currDb[$val['param']] = $val['value'];
		}
		
		$path = ROOTPATH . 'public/images/';
		
		// Handle upload Logo Login
		$logoLogin = $currDb['logo_login'] ?? '';
		$fileLogoLogin = $this->request->getFile('logo_login');
		if ($fileLogoLogin && $fileLogoLogin->isValid() && $fileLogoLogin->getName()) {
			// Hapus file lama
			if (!empty($currDb['logo_login']) && file_exists($path . $currDb['logo_login'])) {
				$unlink = delete_file($path . $currDb['logo_login']);
				if (!$unlink) {
					return ['status' => 'error', 'message' => 'Gagal menghapus gambar logo login lama'];
				}
			}
			$logoLogin = \upload_file($path, $fileLogoLogin);
		}
		
		// Handle upload Logo App
		$logoApp = $currDb['logo_app'] ?? '';
		$fileLogoApp = $this->request->getFile('logo_app');
		if ($fileLogoApp && $fileLogoApp->isValid() && $fileLogoApp->getName()) {
			if (!empty($currDb['logo_app']) && file_exists($path . $currDb['logo_app'])) {
				$unlink = delete_file($path . $currDb['logo_app']);
				if (!$unlink) {
					return ['status' => 'error', 'message' => 'Gagal menghapus gambar logo app lama'];
				}
			}
			$logoApp = \upload_file($path, $fileLogoApp);
		}
		
		// Handle upload Favicon
		$favicon = $currDb['favicon'] ?? '';
		$fileFavicon = $this->request->getFile('favicon');
		if ($fileFavicon && $fileFavicon->isValid() && $fileFavicon->getName()) {
			if (!empty($currDb['favicon']) && file_exists($path . $currDb['favicon'])) {
				$unlink = delete_file($path . $currDb['favicon']);
				if (!$unlink) {
					return ['status' => 'error', 'message' => 'Gagal menghapus favicon lama'];
				}
			}
			$favicon = \upload_file($path, $fileFavicon);
		}
		
		// Handle upload Logo Register
		$logoRegister = $currDb['logo_register'] ?? '';
		$fileLogoRegister = $this->request->getFile('logo_register');
		if ($fileLogoRegister && $fileLogoRegister->isValid() && $fileLogoRegister->getName()) {
			if (!empty($currDb['logo_register']) && file_exists($path . $currDb['logo_register'])) {
				$unlink = delete_file($path . $currDb['logo_register']);
				if (!$unlink) {
					return ['status' => 'error', 'message' => 'Gagal menghapus gambar logo register lama'];
				}
			}
			$logoRegister = \upload_file($path, $fileLogoRegister);
		}
		
		// Validasi semua file berhasil
		if ($logoLogin && $logoApp && $favicon && $logoRegister) {
			$dataDb = [];
			$dataDb[] = ['type' => 'app', 'param' => 'logo_login', 'value' => $logoLogin];
			$dataDb[] = ['type' => 'app', 'param' => 'logo_app', 'value' => $logoApp];
			$dataDb[] = ['type' => 'app', 'param' => 'footer_login', 'value' => htmlentities($this->request->getPost('footer_login'))];
			$dataDb[] = ['type' => 'app', 'param' => 'btn_login', 'value' => $this->request->getPost('btn_login')];
			$dataDb[] = ['type' => 'app', 'param' => 'footer_app', 'value' => htmlentities($this->request->getPost('footer_app'))];
			$dataDb[] = ['type' => 'app', 'param' => 'background_logo', 'value' => $this->request->getPost('background_logo')];
			$dataDb[] = ['type' => 'app', 'param' => 'judul_web', 'value' => $this->request->getPost('judul_web')];
			$dataDb[] = ['type' => 'app', 'param' => 'deskripsi_web', 'value' => $this->request->getPost('deskripsi_web')];
			$dataDb[] = ['type' => 'app', 'param' => 'favicon', 'value' => $favicon];
			$dataDb[] = ['type' => 'app', 'param' => 'logo_register', 'value' => $logoRegister];
			
			$this->db->transStart();
			$this->db->table('core_setting')->where('type', 'app')->delete();
			$this->db->table('core_setting')->insertBatch($dataDb);
			$this->db->transComplete();
			$queryResult = $this->db->transStatus();
			
			if ($queryResult) {
				// Generate CSS file untuk background login
				$fileName = ROOTPATH . 'public/themes/modern/builtin/css/login-header.css';
				$backgroundLogo = $this->request->getPost('background_logo');
				$css = '.login-header {background-color: ' . $backgroundLogo . ';}.edit-logo-login-container {background: ' . $backgroundLogo . ';}';
				
				if (file_exists($fileName)) {
					file_put_contents($fileName, $css);
				}
				
				$result = ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
			} else {
				$result = ['status' => 'error', 'message' => 'Data gagal disimpan'];
			}
		} else {
			$result = ['status' => 'error', 'message' => 'Error saat memproses gambar'];
		}

		return $result;
	}
}
?>
