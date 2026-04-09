<?php
/**
 * Model Customer
 * Mengelola data pelanggan/customer
 * 
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Modules\Register\Models;

class CustomerModel extends \App\Modules\Common\Models\BaseModel
{
	public function __construct() 
	{
		parent::__construct();
	}

	private function getDatatableColumns(array $allowedColumns): array
	{
		$columns = $this->request->getPost('columns');
		$result = [];

		if (!is_array($columns)) {
			return $result;
		}

		foreach ($columns as $column) {
			$columnName = $column['data'] ?? '';
			if (!$columnName || strpos($columnName, 'ignore') !== false || strpos($columnName, 'ignore_search') !== false) {
				continue;
			}

			if (in_array($columnName, $allowedColumns, true)) {
				$result[] = $columnName;
			}
		}

		return array_values(array_unique($result));
	}

	private function applyDatatableSearch($builder, array $searchColumns): void
	{
		$searchValue = trim((string) ($this->request->getPost('search')['value'] ?? ''));
		if ($searchValue === '' || empty($searchColumns)) {
			return;
		}

		$builder->groupStart();
		foreach ($searchColumns as $index => $columnName) {
			if ($index === 0) {
				$builder->like($columnName, $searchValue);
			} else {
				$builder->orLike($columnName, $searchValue);
			}
		}
		$builder->groupEnd();
	}

	private function applyDatatableOrderAndLimit($builder, array $allowedColumns, string $defaultOrder): void
	{
		$columns = $this->request->getPost('columns');
		$orderData = $this->request->getPost('order');
		$orderColumn = $defaultOrder;
		$orderDirection = 'ASC';

		if (is_array($orderData) && !empty($orderData[0])) {
			$columnIndex = (int) ($orderData[0]['column'] ?? 0);
			$columnName = is_array($columns) ? ($columns[$columnIndex]['data'] ?? '') : '';

			if (in_array($columnName, $allowedColumns, true)) {
				$orderColumn = $columnName;
			}

			if (strtoupper((string) ($orderData[0]['dir'] ?? 'ASC')) === 'DESC') {
				$orderDirection = 'DESC';
			}
		}

		$start = max(0, (int) ($this->request->getPost('start') ?? 0));
		$length = (int) ($this->request->getPost('length') ?? 10);
		if ($length < 1) {
			$length = 10;
		}

		$builder->orderBy($orderColumn, $orderDirection)->limit($length, $start);
	}

	private function getListDataBuilder()
	{
		return $this->db->table('pos_customer')
			->select('pos_customer.*, 
					  core_wilayah_kelurahan.kelurahan,
					  core_wilayah_kecamatan.kecamatan,
					  core_wilayah_kabupaten.kabupaten,
					  core_wilayah_propinsi.propinsi')
			->join('core_wilayah_kelurahan', 'core_wilayah_kelurahan.id_wilayah_kelurahan = pos_customer.id_wilayah_kelurahan', 'left')
			->join('core_wilayah_kecamatan', 'core_wilayah_kecamatan.id_wilayah_kecamatan = core_wilayah_kelurahan.id_wilayah_kecamatan', 'left')
			->join('core_wilayah_kabupaten', 'core_wilayah_kabupaten.id_wilayah_kabupaten = core_wilayah_kecamatan.id_wilayah_kabupaten', 'left')
			->join('core_wilayah_propinsi', 'core_wilayah_propinsi.id_wilayah_propinsi = core_wilayah_kabupaten.id_wilayah_propinsi', 'left');
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
	public function countAllData() 
	{
		return $this->db->table('pos_customer')->countAllResults();
	}
	
	/**
	 * Ambil data customer untuk DataTables dengan JOIN wilayah
	 * Mendukung searching dan ordering
	 * 
	 * @param string $where Kondisi WHERE tambahan (opsional)
	 * @return array Data customer dan total filtered
	 */
	public function getListData() 
	{
		$allowedColumns = [
			'id_customer', 'nama_customer', 'alamat_customer', 
			'no_telp', 'email', 'foto',
			'kelurahan', 'kecamatan', 'kabupaten', 'propinsi'
		];
		$searchColumns = $this->getDatatableColumns($allowedColumns);

		$countBuilder = $this->getListDataBuilder();
		$this->applyDatatableSearch($countBuilder, $searchColumns);
		$totalFiltered = $countBuilder->countAllResults();

		$dataBuilder = $this->getListDataBuilder();
		$this->applyDatatableSearch($dataBuilder, $searchColumns);
		$this->applyDatatableOrderAndLimit($dataBuilder, $allowedColumns, 'nama_customer');
		$data = $dataBuilder->get()->getResultArray();
				
		return ['data' => $data, 'total_filtered' => $totalFiltered];
	}
}
?>
