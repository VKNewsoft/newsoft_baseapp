<?php
/**
 * IdentitasModel - Model untuk manajemen identitas company
 * 
 * Model ini menangani informasi identitas perusahaan/organisasi
 * termasuk nama, alamat, kontak, dan wilayah
 * 
 * @package App\Models
 * @year 2020-2025
 */

namespace App\Models;

class IdentitasModel extends \App\Models\BaseModel
{
	/**
	 * Hitung jumlah identitas untuk company tertentu
	 * 
	 * @return int Jumlah identitas
	 */
	public function getCountIdentitas() 
	{
		return $this->db->table('core_identitas')
			->where('id_company', $this->session->user['id_company'])
			->countAllResults();
	}
	
	/**
	 * Simpan data identitas (insert atau update)
	 * Satu company hanya boleh punya satu identitas
	 * 
	 * @return array Status dan pesan hasil operasi
	 */
	public function saveData() 
	{
		$count = $this->getCountIdentitas();
		$idCompany = $this->session->user['id_company'];
		
		$dataDb = [
			'nama' => $this->request->getPost('nama'),
			'alamat' => $this->request->getPost('alamat'),
			'id_wilayah_kelurahan' => $this->request->getPost('id_wilayah_kelurahan'),
			'email' => $this->request->getPost('email'),
			'no_telp' => $this->request->getPost('no_telp'),
			'id_company' => $idCompany
		];
		
		if ($count != 0) {
			$query = $this->db->table('core_identitas')
				->update($dataDb, ['id_company' => $idCompany]);
		} else {
			$query = $this->db->table('core_identitas')->insert($dataDb);
		}

		if ($query) {
			return ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
		}
		
		return ['status' => 'error', 'message' => 'Data gagal disimpan'];
	}
}
?>