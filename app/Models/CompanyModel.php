<?php
/**
 * CompanyModel - Model untuk manajemen company
 * 
 * Model ini menangani operasi CRUD company termasuk:
 * - Multi-company management
 * - Revenue sharing configuration
 * - Bank account setup
 * - Active company switching
 * 
 * @package App\Models
 * @year 2020-2025
 */

namespace App\Models;

class CompanyModel extends \App\Models\BaseModel
{
	/**
	 * Soft delete data company
	 * 
	 * @return bool Status update
	 */
	public function deleteData() 
	{
		$dataDb = [
			'tgl_edit' => date("Y-m-d H:i:s"),
			'id_user_edit' => $this->user['id_user'],
			'isDeleted' => 1
		];

		return $this->db->table('core_company')
			->update($dataDb, ['id_company' => $this->request->getPost('id')]);
	}
	
	/**
	 * Mendapatkan company berdasarkan ID
	 * 
	 * @param int $id ID company
	 * @return array|null Data company
	 */
	public function getCompanyById($id) 
	{
		return $this->db->table('core_company')
			->where('id_company', $id)
			->get()
			->getRowArray();
	}
	
	/**
	 * Toggle status company aktif/non-aktif
	 * 
	 * @return array Status dan pesan hasil operasi
	 */
	public function switchDefault() 
	{
		$idCompany = $this->request->getPost('id');
		
		$result = $this->db->table('core_company')
			->select('tenant_aktif AS status')
			->where('id_company', $idCompany)
			->get()
			->getRowArray();
		
		$dataDb = [
			'tenant_aktif' => $result['status'] == 'Y' ? 'N' : 'Y',
			'tgl_edit' => date("Y-m-d H:i:s"),
			'id_user_edit' => $this->user['id_user']
		];
		
		$this->db->transStart();
		$this->db->table('core_company')->update($dataDb, ['id_company' => $idCompany]);
		$this->db->transComplete();
		
		if ($this->db->transStatus()) {
			return [
				'status' => 'ok',
				'message' => 'Data berhasil disimpan'
			];
		}
		
		return [
			'status' => 'error',
			'message' => 'Data gagal disimpan'
		];
	}
	
	/**
	 * Simpan data tenant/company
	 * 
	 * @return array Status dan pesan hasil operasi
	 */
	public function saveData() 
	{
		$this->db->transStart();
		
		$dataDb = [
			'nama_company' => $this->request->getPost('nama_company'),
			'rev_share' => $this->request->getPost('rev_share'),
			'kode_lokasi' => $this->request->getPost('kode_lokasi'),
			'deskripsi' => $this->request->getPost('deskripsi'),
			'tenant_aktif' => $this->request->getPost('tenant_aktif'),
			'id_bank' => $this->request->getPost('id_bank'),
			'no_rekening' => $this->request->getPost('no_rekening'),
			'sistem' => 'pos'
		];
		
		$idCompany = $this->request->getPost('id');
		
		if ($idCompany) {
			$dataDb['tgl_edit'] = date("Y-m-d H:i:s");
			$dataDb['id_user_edit'] = $this->user['id_user'];
			$this->db->table('core_company')->update($dataDb, ['id_company' => $idCompany]);
		} else {
			$dataDb['tgl_input'] = date("Y-m-d H:i:s");
			$dataDb['id_user_input'] = $this->user['id_user'];
			$this->db->table('core_company')->insert($dataDb);
		}
		
		$this->db->transComplete();
		
		if ($this->db->transStatus()) {
			return [
				'status' => 'ok',
				'message' => 'Data berhasil disimpan'
			];
		}
		
		return [
			'status' => 'error',
			'message' => 'Data gagal disimpan'
		];
	}
	
	/**
	 * Hitung total company
	 * 
	 * @return int Jumlah company
	 */
	public function countAllData() 
	{
		return $this->db->table('core_company')
			->where('isDeleted', 0)
			->whereIn('sistem', ['core', 'pos'])
			->countAllResults();
	}
	
	/**
	 * Mendapatkan list data company untuk DataTables
	 * Menggunakan parameter binding untuk mencegah SQL injection
	 * 
	 * @return array Data company dengan total filtered
	 */
	public function getListData() 
	{
		$columns = $this->request->getPost('columns');
		
		// Whitelist kolom yang diizinkan
		$allowedColumns = ['id_company', 'nama_company', 'rev_share', 'kode_lokasi', 'deskripsi', 'tenant_aktif', 'id_bank', 'nama_bank', 'no_rekening'];
		
		// Build query dengan Query Builder
		$builder = $this->db->table('core_company')
			->join('core_bank', 'core_company.id_bank = core_bank.id_bank', 'left')
			->where('isDeleted', 0)
			->whereIn('sistem', ['core', 'pos']);
		
		// Search
		$searchValue = $this->request->getPost('search')['value'] ?? '';
		if ($searchValue) {
			$builder->groupStart();
			foreach ($columns as $column) {
				$columnData = $column['data'];
				
				if (strpos($columnData, 'ignore_search') !== false ||
					strpos($columnData, 'ignore') !== false ||
					!in_array($columnData, $allowedColumns)) {
					continue;
				}
				
				$builder->orLike($columnData, $searchValue);
			}
			$builder->groupEnd();
		}
		
		// Hitung total filtered
		$totalFiltered = $builder->countAllResults(false);
		
		// Order By
		$orderData = $this->request->getPost('order');
		if ($orderData && isset($columns[$orderData[0]['column']])) {
			$orderColumn = $columns[$orderData[0]['column']]['data'];
			$orderDir = strtoupper($orderData[0]['dir']) === 'DESC' ? 'DESC' : 'ASC';
			
			if (strpos($orderColumn, 'ignore_search') === false && in_array($orderColumn, $allowedColumns)) {
				$builder->orderBy($orderColumn, $orderDir);
			}
		}
		
		// Limit dan Offset
		$start = (int) ($this->request->getPost('start') ?? 0);
		$length = (int) ($this->request->getPost('length') ?? 10);
		$builder->limit($length, $start);
		
		// Eksekusi query
		$data = $builder->get()->getResultArray();
		
		return [
			'data' => $data,
			'total_filtered' => $totalFiltered
		];
	}

	/**
	 * Mendapatkan daftar skema (indexed by id_skema)
	 * 
	 * @return array Daftar skema
	 */
	public function getSkema() 
	{
		$query = $this->db->table('skema')
			->where('isDeleted', 0)
			->get()
			->getResultArray();
		
		$result = [];
		foreach ($query as $val) {
			$result[$val['id_skema']] = $val['nama_skema'];
		}
		
		return $result;
	}

	/**
	 * Mendapatkan daftar bank (indexed by id_bank)
	 * 
	 * @return array Daftar bank
	 */
	public function getBank() 
	{
		$query = $this->db->table('core_bank')
			->get()
			->getResultArray();
		
		$result = [];
		foreach ($query as $val) {
			$result[$val['id_bank']] = $val['nama_bank'];
		}
		
		return $result;
	}

	/**
	 * Mendapatkan semua company yang tidak dihapus
	 * 
	 * @return array Daftar company
	 */
	public function list_company()
	{
		return $this->db->table('core_company')
			->where('isDeleted', 0)
			->get()
			->getResultArray();
	}
}
?>