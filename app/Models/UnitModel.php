<?php
/**
 * Model Unit Satuan
 * Mengelola data satuan unit produk (pcs, box, lusin, dll)
 * 
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Models;

class UnitModel extends \App\Models\BaseModel
{
	/**
	 * Hapus data satuan unit
	 * 
	 * @return array Status dan pesan hasil penghapusan
	 */
	public function deleteData() 
	{
		$idSatuanUnit = $this->request->getPost('id');
		$delete = $this->db->table('pos_satuan_unit')->delete(['id_satuan_unit' => $idSatuanUnit]);
		
		if ($delete) {
			return [
				'status' => 'ok',
				'message' => 'Data berhasil dihapus'
			];
		} else {
			return [
				'status' => 'error',
				'message' => 'Data gagal dihapus'
			];
		}
	}
	
	/**
	 * Ambil data satuan unit berdasarkan ID
	 * 
	 * @param int $id ID satuan unit
	 * @return array Data satuan unit
	 */
	public function getUnitById($id) 
	{
		$builder = $this->db->table('pos_satuan_unit');
		$builder->where('id_satuan_unit', trim($id));
		$result = $builder->get()->getRowArray();
		return $result;
	}
	
	/**
	 * Simpan data satuan unit (insert/update)
	 * 
	 * @return array Status dan pesan hasil penyimpanan
	 */
	public function saveData() 
	{		
		$dataDb = [
			'nama_satuan' => $this->request->getPost('nama_satuan'),
			'satuan' => $this->request->getPost('satuan')
		];
		
		$idSatuanUnit = $this->request->getPost('id');
		
		if ($idSatuanUnit) 
		{
			$query = $this->db->table('pos_satuan_unit')->update($dataDb, ['id_satuan_unit' => $idSatuanUnit]);	
		} else {
			$query = $this->db->table('pos_satuan_unit')->insert($dataDb);
		}
		
		if ($query) {
			return [
				'status' => 'ok',
				'message' => 'Data berhasil disimpan'
			];
		} else {
			return [
				'status' => 'error',
				'message' => 'Data gagal disimpan'
			];
		}
	}
	
	/**
	 * Hitung total semua satuan unit
	 * 
	 * @return int Total satuan unit
	 */
	public function countAllData() 
	{
		$builder = $this->db->table('pos_satuan_unit');
		$builder->select('COUNT(*) as jml');
		$result = $builder->get()->getRow();
		return $result->jml ?? 0;
	}
	
	/**
	 * Ambil data satuan unit untuk DataTables
	 * Mendukung searching dan ordering dengan whitelist kolom
	 * 
	 * @return array Data satuan unit, total records, dan total filtered
	 */
	public function getListData() 
	{
		$columns = $this->request->getPost('columns');
		
		// Whitelist kolom yang diizinkan untuk ORDER BY
		$allowedColumns = ['id_satuan_unit', 'nama_satuan', 'satuan'];

		// Build Query
		$builder = $this->db->table('pos_satuan_unit');
		
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
		
		// Hitung total filtered
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
				
		return [
			'data' => $data, 
			'recordsTotal' => $this->countAllData(), 
			'recordsFiltered' => $totalFiltered
		];
	}
}
?>