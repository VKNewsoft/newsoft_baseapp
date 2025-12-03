<?php
/**
 * Model Customer
 * Mengelola data pelanggan/customer
 * 
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Models;

class CustomerModel extends \App\Models\BaseModel
{
	public function __construct() 
	{
		parent::__construct();
	}
	
	/**
	 * Hapus data customer
	 * 
	 * @return bool Status hasil penghapusan
	 */
	public function deleteData() 
	{
		$idCustomer = $this->request->getPost('id');
		$result = $this->db->table('pos_customer')->delete(['id_customer' => $idCustomer]);
		return $result;
	}
	
	/**
	 * Ambil data customer berdasarkan ID
	 * Termasuk data wilayah (kelurahan, kecamatan, kabupaten, provinsi)
	 * 
	 * @param int $id ID customer
	 * @return array Data customer
	 */
	public function getCustomerById($id) 
	{
		$builder = $this->db->table('pos_customer');
		$builder->select('*');
		$builder->join('core_wilayah_kelurahan', 'core_wilayah_kelurahan.id_wilayah_kelurahan = pos_customer.id_wilayah_kelurahan', 'left');
		$builder->join('core_wilayah_kecamatan', 'core_wilayah_kecamatan.id_wilayah_kecamatan = core_wilayah_kelurahan.id_wilayah_kecamatan', 'left');
		$builder->join('core_wilayah_kabupaten', 'core_wilayah_kabupaten.id_wilayah_kabupaten = core_wilayah_kecamatan.id_wilayah_kabupaten', 'left');
		$builder->join('core_wilayah_propinsi', 'core_wilayah_propinsi.id_wilayah_propinsi = core_wilayah_kabupaten.id_wilayah_propinsi', 'left');
		$builder->where('id_customer', trim($id));
		$result = $builder->get()->getRowArray();
		return $result;
	}
	
	/**
	 * Simpan data customer (insert/update)
	 * Menangani upload foto customer
	 * 
	 * @return array Status dan pesan hasil penyimpanan
	 */
	public function saveData() 
	{
		$dataDb = [
			'nama_customer' => $this->request->getPost('nama_customer'),
			'alamat_customer' => $this->request->getPost('alamat_customer'),
			'no_telp' => $this->request->getPost('no_telp'),
			'email' => $this->request->getPost('email'),
			'id_wilayah_kelurahan' => $this->request->getPost('id_wilayah_kelurahan')
		];
		
		$newName = '';
		$imgDb = ['foto' => ''];
		
		$path = ROOTPATH . 'public/images/foto/';
		$idCustomer = $this->request->getPost('id');
		
		// Jika update, ambil foto lama
		if (!empty($idCustomer)) {
			$builder = $this->db->table('pos_customer');
			$builder->select('foto');
			$builder->where('id_customer', $idCustomer);
			$imgDb = $builder->get()->getRowArray();
			$newName = $imgDb['foto'] ?? '';
			
			// Handle permintaan hapus foto
			if ($this->request->getPost('foto_delete_img')) {
				if ($imgDb['foto']) {
					$del = delete_file($path . $imgDb['foto']);
					$newName = '';
					if (!$del) {
						return [
							'status' => 'error',
							'message' => 'Gagal menghapus gambar lama'
						];
					}
				}
			}
		}
		
		// Handle upload foto baru
		$file = $this->request->getFile('foto');
		
		if ($file && $file->getName())
		{
			// Hapus foto lama jika ada
			if ($idCustomer && !empty($imgDb['foto'])) {
				if (file_exists($path . $imgDb['foto'])) {
					$unlink = delete_file($path . $imgDb['foto']);
					if (!$unlink) {
						return [
							'status' => 'error',
							'message' => 'Gagal menghapus gambar lama'
						];
					}
				}
			}
			
			// Upload foto baru
			helper('upload_file');
			$newName = get_filename($file->getName(), $path);
			$file->move($path, $newName);
				
			if (!$file->hasMoved()) {
				return [
					'status' => 'error',
					'message' => 'Error saat memproses gambar'
				];
			}
		}
		
		$dataDb['foto'] = $newName;
		
		// Simpan ke database
		if ($idCustomer) 
		{
			$query = $this->db->table('pos_customer')->update($dataDb, ['id_customer' => $idCustomer]);
		} else {
			$query = $this->db->table('pos_customer')->insert($dataDb);
			$idCustomer = $this->db->insertID();
		}
		
		if ($query) {
			return [
				'status' => 'ok',
				'message' => 'Data berhasil disimpan',
				'id_customer' => $idCustomer
			];
		} else {
			return [
				'status' => 'error',
				'message' => 'Data gagal disimpan'
			];
		}
	}
	
	/**
	 * Hitung total customer
	 * 
	 * @param string $where Kondisi WHERE dalam format Query Builder
	 * @return int Total customer
	 */
	public function countAllData($where = '') 
	{
		$builder = $this->db->table('pos_customer');
		$builder->select('COUNT(*) as jml');
		
		// Catatan: Parameter $where diasumsikan sudah aman dari controller
		// Sebaiknya gunakan Query Builder untuk kondisi dinamis
		if ($where) {
			$builder->where($where);
		}
		
		$result = $builder->get()->getRow();
		return $result->jml ?? 0;
	}
	
	/**
	 * Ambil data customer untuk DataTables dengan JOIN wilayah
	 * Mendukung searching dan ordering
	 * 
	 * @param string $where Kondisi WHERE tambahan (opsional)
	 * @return array Data customer dan total filtered
	 */
	public function getListData($where = '') 
	{
		$columns = $this->request->getPost('columns');
		
		// Whitelist kolom yang diizinkan untuk ORDER BY
		$allowedColumns = [
			'id_customer', 'nama_customer', 'alamat_customer', 
			'no_telp', 'email', 'foto',
			'kelurahan', 'kecamatan', 'kabupaten', 'propinsi'
		];

		// Build Query Builder
		$builder = $this->db->table('pos_customer');
		$builder->select('pos_customer.*, 
						  core_wilayah_kelurahan.kelurahan,
						  core_wilayah_kecamatan.kecamatan,
						  core_wilayah_kabupaten.kabupaten,
						  core_wilayah_propinsi.propinsi');
		$builder->join('core_wilayah_kelurahan', 'core_wilayah_kelurahan.id_wilayah_kelurahan = pos_customer.id_wilayah_kelurahan', 'left');
		$builder->join('core_wilayah_kecamatan', 'core_wilayah_kecamatan.id_wilayah_kecamatan = core_wilayah_kelurahan.id_wilayah_kecamatan', 'left');
		$builder->join('core_wilayah_kabupaten', 'core_wilayah_kabupaten.id_wilayah_kabupaten = core_wilayah_kecamatan.id_wilayah_kabupaten', 'left');
		$builder->join('core_wilayah_propinsi', 'core_wilayah_propinsi.id_wilayah_propinsi = core_wilayah_kabupaten.id_wilayah_propinsi', 'left');
		
		// Tambahkan kondisi WHERE jika ada
		if ($where) {
			$builder->where($where);
		}
		
		// Search global
		$searchAll = $this->request->getPost('search')['value'] ?? '';
		if ($searchAll) {
			$builder->groupStart();
			
			foreach ($columns as $val) {
				if (strpos($val['data'], 'ignore_search') !== false) 
					continue;
				
				if (strpos($val['data'], 'ignore') !== false)
					continue;
				
				$builder->orLike($val['data'], $searchAll);
			}
			
			$builder->groupEnd();
		}
		
		// Hitung total filtered (sebelum limit)
		$totalFiltered = $builder->countAllResults(false);
		
		// Order By dengan whitelist
		$orderData = $this->request->getPost('order');
		if (!empty($orderData)) {
			$orderColumnIndex = $orderData[0]['column'] ?? 0;
			$orderDir = strtoupper($orderData[0]['dir'] ?? 'ASC');
			
			if (isset($columns[$orderColumnIndex])) {
				$orderColumn = $columns[$orderColumnIndex]['data'];
				
				// Validasi kolom ada dalam whitelist dan bukan kolom ignore
				if (in_array($orderColumn, $allowedColumns) && 
					strpos($orderColumn, 'ignore_search') === false) {
					$builder->orderBy($orderColumn, $orderDir);
				}
			}
		}
		
		// Limit dan offset
		$start = (int) ($this->request->getPost('start') ?? 0);
		$length = (int) ($this->request->getPost('length') ?? 10);
		$builder->limit($length, $start);

		// Query Data
		$data = $builder->get()->getResultArray();
				
		return ['data' => $data, 'total_filtered' => $totalFiltered];
	}
}
?>