<?php
/**
 * Model Setting Invoice
 * Mengelola pengaturan invoice, nota retur, dan nota transfer
 * Termasuk upload logo dan pengaturan nomor otomatis
 * 
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Models;

class SettingInvoiceModel extends \App\Models\BaseModel
{
	/**
	 * Ambil pengaturan invoice
	 * 
	 * @return array Pengaturan invoice
	 */
	public function getSettingInvoice() 
	{
		$builder = $this->db->table('core_setting');
		$builder->where('type', 'invoice');
		$result = $builder->get()->getResultArray();
		return $result;
	}
	
	/**
	 * Ambil pengaturan nota retur
	 * 
	 * @return array Pengaturan nota retur
	 */
	public function getSettingNotaRetur() 
	{
		$builder = $this->db->table('core_setting');
		$builder->where('type', 'nota_retur');
		$result = $builder->get()->getResultArray();
		return $result;
	}
	
	/**
	 * Ambil pengaturan nota transfer
	 * 
	 * @return array Pengaturan nota transfer
	 */
	public function getSettingNotaTransfer() 
	{
		$builder = $this->db->table('core_setting');
		$builder->where('type', 'nota_transfer');
		$result = $builder->get()->getResultArray();
		return $result;
	}
	
	/**
	 * Simpan pengaturan invoice, nota retur, dan nota transfer
	 * Menangani upload logo invoice
	 * 
	 * @return array Status dan pesan hasil penyimpanan
	 */
	public function saveSetting() 
	{
		// Siapkan data pengaturan
		$dataDb = [
			['type' => 'invoice', 'param' => 'no_invoice', 'value' => $this->request->getPost('no_invoice')],
			['type' => 'invoice', 'param' => 'jml_digit', 'value' => $this->request->getPost('jml_digit_invoice')],
			['type' => 'invoice', 'param' => 'footer_text', 'value' => $this->request->getPost('footer_text')],
			['type' => 'nota_retur', 'param' => 'no_nota_retur', 'value' => $this->request->getPost('no_nota_retur')],
			['type' => 'nota_retur', 'param' => 'jml_digit', 'value' => $this->request->getPost('jml_digit_nota_retur')],
			['type' => 'nota_transfer', 'param' => 'no_nota_transfer', 'value' => $this->request->getPost('no_nota_transfer')],
			['type' => 'nota_transfer', 'param' => 'jml_digit', 'value' => $this->request->getPost('jml_digit_nota_transfer')]
		];
		
		helper('upload_file');
		
		// Ambil logo lama
		$builder = $this->db->table('core_setting');
		$builder->where('type', 'invoice');
		$builder->where('param', 'logo');
		$setting = $builder->get()->getRowArray();
		
		$logoInvoiceLama = $setting['value'] ?? '';
		$path = ROOTPATH . 'public/images/';
		
		// Handle upload logo baru
		$file = $this->request->getFile('logo');
		if ($file && $file->getName()) 
		{
			// Hapus logo lama jika ada
			if ($logoInvoiceLama && file_exists($path . $logoInvoiceLama)) {
				$unlink = delete_file($path . $logoInvoiceLama);
				if (!$unlink) {
					return [
						'status' => 'error',
						'message' => 'Gagal menghapus gambar lama'
					];
				}
			}
			
			// Upload file baru
			$filename = upload_file($path, $file);
			$dataDb[] = ['type' => 'invoice', 'param' => 'logo', 'value' => $filename];
		} else {
			// Pertahankan logo lama
			$dataDb[] = ['type' => 'invoice', 'param' => 'logo', 'value' => $logoInvoiceLama];
		}
		
		// Simpan ke database dengan transaksi
		$this->db->transStart();
		$this->db->table('core_setting')->delete(['type' => 'invoice']);
		$this->db->table('core_setting')->delete(['type' => 'nota_retur']);
		$this->db->table('core_setting')->delete(['type' => 'nota_transfer']);
		$this->db->table('core_setting')->insertBatch($dataDb);
		$this->db->transComplete();
		
		if ($this->db->transStatus()) {
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
}
?>