<?php
/**
 * Model Setting Registrasi
 * 
 * Mengelola pengaturan registrasi user baru seperti enable/disable registrasi,
 * metode aktivasi (email/manual), role default, dan module default untuk user baru.
 * 
 * @package    App\Models\Builtin
 * @author     Newsoft Developer
 * @copyright  2020-2023
 */

namespace App\Models\Builtin;

class SettingRegistrasiModel extends \App\Models\BaseModel
{
	/**
	 * Mendapatkan semua role
	 * 
	 * @return array Daftar semua role
	 */
	public function getRole() {
		return $this->db->table('core_role')->get()->getResultArray();
	}
	
	/**
	 * Mendapatkan setting registrasi
	 * 
	 * @return array Setting registrasi (enable, metode_aktivasi, id_role, id_module)
	 */
	public function getSettingRegistrasi() {
		return $this->db->table('core_setting')
			->where('type', 'register')
			->get()
			->getResultArray();
	}
	
	/**
	 * Mendapatkan daftar module beserta statusnya
	 * 
	 * @return array Daftar module dengan status
	 */
	public function getListModules() {
		return $this->db->table('core_module')
			->select('core_module.*, core_module_status.*')
			->join('core_module_status', 'core_module_status.id_module_status = core_module.id_module_status', 'left')
			->orderBy('nama_module', 'ASC')
			->get()
			->getResultArray();
	}
	
	/**
	 * Simpan setting registrasi
	 * 
	 * @return array Status dan pesan hasil operasi
	 */
	public function saveData() 
	{
		$dataDb = [];
		$dataDb[] = ['type' => 'register', 'param' => 'enable', 'value' => $this->request->getPost('enable')];
		$dataDb[] = ['type' => 'register', 'param' => 'metode_aktivasi', 'value' => $this->request->getPost('metode_aktivasi')];
		$dataDb[] = ['type' => 'register', 'param' => 'id_role', 'value' => $this->request->getPost('id_role')];
		$dataDb[] = ['type' => 'register', 'param' => 'id_module', 'value' => $this->request->getPost('id_module')];
		
		$this->db->transStart();
		$this->db->table('core_setting')->where('type', 'register')->delete();
		$this->db->table('core_setting')->insertBatch($dataDb);
		$this->db->transComplete();
		$queryResult = $this->db->transStatus();
		
		if ($queryResult) {
			$result = ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
		} else {
			$result = ['status' => 'error', 'message' => 'Data gagal disimpan'];
		}
		
		return $result;
	}
}
?>