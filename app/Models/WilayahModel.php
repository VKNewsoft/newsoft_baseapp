<?php
/**
 * WilayahModel - Model untuk data wilayah Indonesia
 * 
 * Model ini menangani data wilayah hierarkis:
 * Propinsi -> Kabupaten -> Kecamatan -> Kelurahan
 * 
 * @package App\Models
 * @year 2020-2025
 */

namespace App\Models;

class WilayahModel extends \App\Models\BaseModel
{
	private $fotoPath;
	
	public function __construct() 
	{
		parent::__construct();
	}
	
	/**
	 * Mendapatkan semua propinsi dalam format array terindeks
	 * 
	 * @return array Daftar propinsi dengan key id_wilayah_propinsi
	 */
	public function getPropinsi() 
	{
		$query = $this->db->table('core_wilayah_propinsi')
			->get()
			->getResultArray();
		
		$result = [];
		foreach ($query as $val) {
			$result[$val['id_wilayah_propinsi']] = $val['nama_propinsi'];
		}
		
		return $result;
	}
	
	/**
	 * Mendapatkan kabupaten berdasarkan propinsi
	 * 
	 * @param int $idPropinsi ID propinsi
	 * @return array Daftar kabupaten
	 */
	public function getKabupatenByIdPropinsi($idPropinsi) 
	{
		$query = $this->db->table('core_wilayah_kabupaten')
			->where('id_wilayah_propinsi', $idPropinsi)
			->get()
			->getResultArray();
		
		$result = [];
		foreach ($query as $val) {
			$result[$val['id_wilayah_kabupaten']] = $val['nama_kabupaten'];
		}
		
		return $result;
	}
	
	/**
	 * Mendapatkan kecamatan berdasarkan kabupaten
	 * 
	 * @param int $idKabupaten ID kabupaten
	 * @return array Daftar kecamatan
	 */
	public function getKecamatanByIdKabupaten($idKabupaten) 
	{
		$query = $this->db->table('core_wilayah_kecamatan')
			->where('id_wilayah_kabupaten', $idKabupaten)
			->get()
			->getResultArray();
		
		$result = [];
		foreach ($query as $val) {
			$result[$val['id_wilayah_kecamatan']] = $val['nama_kecamatan'];
		}
		
		return $result;
	}
	
	/**
	 * Mendapatkan kelurahan berdasarkan kecamatan
	 * 
	 * @param int $idKecamatan ID kecamatan
	 * @return array Daftar kelurahan
	 */
	public function getKelurahanByIdKecamatan($idKecamatan) 
	{
		$query = $this->db->table('core_wilayah_kelurahan')
			->where('id_wilayah_kecamatan', $idKecamatan)
			->get()
			->getResultArray();
		
		$result = [];
		foreach ($query as $val) {
			$result[$val['id_wilayah_kelurahan']] = $val['nama_kelurahan'];
		}
		
		return $result;
	}
	
	/**
	 * Mendapatkan kecamatan berdasarkan kelurahan (reverse lookup)
	 * Jika id_kelurahan kosong, return kecamatan tengah dari database
	 * 
	 * @param int|null $idKelurahan ID kelurahan
	 * @return array Data kecamatan
	 */
	public function getKecamatanByIdKelurahan($idKelurahan) 
	{
		if (empty($idKelurahan)) {
			// Ambil kecamatan di tengah list sebagai default
			$count = $this->db->table('core_wilayah_kecamatan')
				->countAllResults();
			
			$offset = (int) ceil($count / 2);
			
			$result = $this->db->table('core_wilayah_kecamatan')
				->limit(1, $offset)
				->get()
				->getRowArray();
		} else {
			$result = $this->db->table('core_wilayah_kecamatan')
				->join('core_wilayah_kelurahan', 'core_wilayah_kecamatan.id_wilayah_kecamatan = core_wilayah_kelurahan.id_wilayah_kecamatan')
				->where('id_wilayah_kelurahan', $idKelurahan)
				->get()
				->getRowArray();
		}
		
		return $result;
	}
	
	/**
	 * Mendapatkan kabupaten berdasarkan kecamatan (reverse lookup)
	 * 
	 * @param int $idKecamatan ID kecamatan
	 * @return array Data kabupaten
	 */
	public function getKabupatenByIdKecamatan($idKecamatan) 
	{
		return $this->db->table('core_wilayah_kabupaten')
			->join('core_wilayah_kecamatan', 'core_wilayah_kabupaten.id_wilayah_kabupaten = core_wilayah_kecamatan.id_wilayah_kabupaten')
			->where('id_wilayah_kecamatan', $idKecamatan)
			->get()
			->getRowArray();
	}
	
	/**
	 * Mendapatkan propinsi berdasarkan kabupaten (reverse lookup)
	 * 
	 * @param int $idKabupaten ID kabupaten
	 * @return array Data propinsi
	 */
	public function getPropinsiByIdKabupaten($idKabupaten) 
	{
		return $this->db->table('core_wilayah_propinsi')
			->join('core_wilayah_kabupaten', 'core_wilayah_propinsi.id_wilayah_propinsi = core_wilayah_kabupaten.id_wilayah_propinsi')
			->where('id_wilayah_kabupaten', $idKabupaten)
			->get()
			->getRowArray();
	}
}
?>