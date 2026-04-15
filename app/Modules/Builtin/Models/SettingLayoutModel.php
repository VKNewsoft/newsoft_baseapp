<?php
/**
 * Model Setting Layout
 * 
 * Mengelola pengaturan tampilan aplikasi seperti color scheme, sidebar color,
 * bootswatch theme, font family, font size. Mendukung setting global dan per-user.
 * 
 * @package    App\Models\Builtin
 * @author     Newsoft Developer
 * @copyright  2020-2023
 */

namespace App\Modules\Builtin\Models;

class SettingLayoutModel extends \App\Modules\Common\Models\BaseModel
{
	/**
	 * Mendapatkan setting layout default (global)
	 * 
	 * @return array Setting layout default
	 */
	public function getDefaultSetting() {
		return $this->db->table('core_setting')
			->where('type', 'layout')
			->get()
			->getResultArray();
	}
	
	/**
	 * Mendapatkan setting layout untuk user tertentu
	 * 
	 * @return array Setting layout user
	 */
	public function getUserSetting() {
		$userSession = session()->get('user');
		$idUser = $userSession['id_user'] ?? 0;
		
		return $this->db->table('core_setting_user')
			->where('id_user', $idUser)
			->where('type', 'layout')
			->get()
			->getRowArray();
	}
	
	/**
	 * Simpan setting layout (global atau per-user berdasarkan permission)
	 * 
	 * @return bool Status transaksi
	 */
	public function saveData() 
	{
		helper('setting_layout');

		$userSession = session()->get('user');
		$permissions = $userSession['permission'] ?? [];
		$idUser = $userSession['id_user'] ?? 0;
		
		// Parameter layout yang bisa diatur
		$params = [
			'color_scheme' => 'Color Scheme',
			'sidebar_color' => 'Sidebar Color',
			'bootswatch_theme' => 'Bootswatch Theme',
			'logo_background_color' => 'Background Logo',
			'font_family' => 'Font Family',
			'font_size' => 'Font Size'
		];
		
		$dataDb = [];
		$arr = [];
		foreach ($params as $param => $title) {
			$value = $this->request->getPost($param);
			if ($param === 'font_family') {
				$value = setting_layout_normalize_font_family($value);
			}
			$dataDb[] = ['type' => 'layout', 'param' => $param, 'value' => $value];
			$arr[$param] = $value;
		}
		
		if (in_array('update_all', $permissions, true)) {
			// Update setting global
			$this->db->transStart();
			$this->db->table('core_setting')->where('type', 'layout')->delete();
			$this->db->table('core_setting')->insertBatch($dataDb);
			$this->db->transComplete();
			$result = $this->db->transStatus();
			
			if ($result) {
				// Generate CSS file untuk font size
				$fontSize = $this->request->getPost('font_size');
				$fileName = APPPATH . 'Modules/Common/Assets/builtin/css/fonts/font-size-' . $fontSize . '.css';
				
				if (!file_exists($fileName)) {
					file_put_contents($fileName, 'html, body { font-size: ' . $fontSize . 'px }');
				}
			}
		} elseif (in_array('update_own', $permissions, true)) {
			// Update setting personal user
			$this->db->transStart();
			$this->db->table('core_setting_user')
				->where('id_user', $idUser)
				->where('type', 'layout')
				->delete();
			
			$this->db->table('core_setting_user')->insert([
				'id_user' => $idUser,
				'param' => json_encode($arr),
				'type' => 'layout'
			]);
			
			$this->db->transComplete();
			$result = $this->db->transStatus();
		} else {
			// Tidak punya permission
			$result = false;
		}
		
		return $result;
	}
}
?>
