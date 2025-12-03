<?php
/**
 * Wilayah Controller - Manajemen Data Wilayah Indonesia
 * 
 * Controller ini menangani CRUD untuk data wilayah hierarkis:
 * - Provinsi (Level 1)
 * - Kabupaten/Kota (Level 2)
 * - Kecamatan (Level 3)
 * - Kelurahan/Desa (Level 4)
 * 
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 * @package App\Controllers\Builtin
 */

namespace App\Controllers\Builtin;
use App\Models\Builtin\WilayahModel;

class Wilayah extends \App\Controllers\BaseController
{
	protected $model;
	protected $moduleURL;
	
	/**
	 * Constructor - Inisialisasi model dan asset
	 */
	public function __construct() 
	{
		parent::__construct();
		
		$this->model = new WilayahModel;	
		$this->formValidation = \Config\Services::validation();
		$this->data['site_title'] = 'Manajemen Data Wilayah';
		
		// Load JavaScript dan CSS dependencies
		$this->addJs($this->config->baseURL . 'public/themes/modern/builtin/js/wilayah.js');
		$this->addJs($this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css');
		
		helper(['cookie', 'form']);
	}
	
	// ========================================================================
	// PROVINSI MANAGEMENT
	// ========================================================================
	
	/**
	 * Index Provinsi - Halaman daftar provinsi
	 * Menampilkan tabel data provinsi dengan DataTables
	 */
	public function provinsi()
	{
		$this->hasPermissionPrefix('read');

		$data['message'] = [];
		
		// Handle delete request
		if ($this->request->getPost('delete')) 
		{
			$this->hasPermissionPrefix('delete');
			
			$result = $this->model->deleteProvinsi();
			if ($result) {
				$data['message'] = ['status' => 'ok', 'message' => 'Data provinsi berhasil dihapus'];
			} else {
				$data['message'] = ['status' => 'warning', 'message' => 'Tidak ada data yang dihapus'];
			}
		}
		
		$data['title'] = 'Data Provinsi';
		$data['wilayah_type'] = 'provinsi';
		$this->view('builtin/wilayah/provinsi-result.php', array_merge($data, $this->data));
	}
	
	/**
	 * Get Data Provinsi untuk DataTables
	 * Return JSON data untuk populate DataTables
	 */
	public function getDataProvinsiDT() 
	{
		$this->hasPermission('read_all');
		
		$numProvinsi = $this->model->countAllProvinsi();
		$provinsi = $this->model->getListProvinsi('');
		
		$result['draw'] = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $numProvinsi;
		$result['recordsFiltered'] = $provinsi['total_filtered'];		
		
		helper('html');
		
		// Tambahkan action buttons untuk setiap row
		foreach ($provinsi['data'] as $key => &$val) {
			$actions = [
				['type' => 'link', 'href' => $this->moduleURL . '/provinsi/edit?id=' . $val['id_wilayah_propinsi'], 'icon' => 'fas fa-edit text-success', 'label' => 'Edit', 'attrs' => ['class' => 'btn-edit']],
			];
			
			if ($this->hasPermission('delete_own') || $this->hasPermission('delete_all')) {
				$actions[] = ['type' => 'form', 'action' => $this->moduleURL . '/provinsi', 'icon' => 'fas fa-times text-danger', 'label' => 'Delete', 'attrs' => ['data-action' => 'delete-data', 'id' => $val['id_wilayah_propinsi'], 'data-delete-title' => 'Hapus provinsi: <strong>' . $val['nama_propinsi'] . '</strong> ?']];
			}
			
			$val['ignore_btn_action'] = btn_dropdown_actions($actions);
		}
					
		$result['data'] = $provinsi['data'];
		echo json_encode($result); 
		exit();
	}
	
	/**
	 * Add Provinsi - Halaman form tambah provinsi baru
	 * Menampilkan form dan proses submit data provinsi baru
	 */
	public function addProvinsi() 
	{
		$this->hasPermission('create');
		
		$data = $this->data;
		$data['title'] = 'Tambah Provinsi';
		$data['wilayah_type'] = 'provinsi';
		$data['provinsi_edit'] = [];
		
		// Proses submit form
		if ($this->request->getPost('submit')) {
			$data['message'] = $this->saveProvinsi();
			
			// Jika berhasil, redirect ke halaman edit
			if ($data['message']['status'] == 'ok') {
				return redirect()->to($this->moduleURL . '/provinsi/edit?id=' . $data['message']['id']);
			}
		}
		
		$this->view('builtin/wilayah/provinsi-form.php', $data);
	}
	
	/**
	 * Edit Provinsi - Halaman form edit provinsi
	 * Menampilkan form edit dan proses update data provinsi
	 */
	public function editProvinsi()
	{
		$this->hasPermissionPrefix('update');
		
		$data = $this->data;
		$data['title'] = 'Edit Provinsi';
		$data['wilayah_type'] = 'provinsi';
		
		// Submit
		$data['message'] = [];
		if ($this->request->getPost('submit')) {
			$data['message'] = $this->saveProvinsi();
		}
		
		// Ambil data provinsi berdasarkan ID
		$result = $this->model->getProvinsiById($this->request->getGet('id'));
		
		if (!$result) {
			$this->errorDataNotFound();
			return;
		}
		
		$data['provinsi_edit'] = $result;
		
		$this->view('builtin/wilayah/provinsi-form.php', $data);
	}
	
	/**
	 * Save Provinsi - Proses simpan data provinsi (create/update)
	 * 
	 * @return array Status dan pesan hasil operasi
	 */
	private function saveProvinsi() 
	{		
		$formErrors = $this->validateFormProvinsi();
		$error = false;		
		
		if ($formErrors) {
			$data['status'] = 'error';
			$data['form_errors'] = $formErrors;
			$data['message'] = $formErrors;
			$error = true;
		}
		
		if (!$error) {				
			$data = $this->model->saveProvinsi();
		}
		
		return $data;
	}
	
	/**
	 * Validasi form provinsi
	 * 
	 * @return mixed Array error jika gagal, false jika sukses
	 */
	private function validateFormProvinsi() 
	{
		$validation = \Config\Services::validation();
		$validation->setRule('nama_propinsi', 'Nama Provinsi', 'trim|required');
		
		if ($validation->withRequest($this->request)->run() === false) {
			return $validation->getErrors();
		}
		
		return false;
	}
	
	// ========================================================================
	// KABUPATEN MANAGEMENT
	// ========================================================================
	
	/**
	 * Index Kabupaten - Halaman daftar kabupaten
	 * Menampilkan tabel data kabupaten dengan DataTables
	 */
	public function kabupaten()
	{
		$this->hasPermissionPrefix('read');

		$data['message'] = [];
		
		// Handle delete request
		if ($this->request->getPost('delete')) 
		{
			$this->hasPermissionPrefix('delete');
			
			$result = $this->model->deleteKabupaten();
			if ($result) {
				$data['message'] = ['status' => 'ok', 'message' => 'Data kabupaten berhasil dihapus'];
			} else {
				$data['message'] = ['status' => 'warning', 'message' => 'Tidak ada data yang dihapus'];
			}
		}
		
		$data['title'] = 'Data Kabupaten/Kota';
		$data['wilayah_type'] = 'kabupaten';
		$this->view('builtin/wilayah/kabupaten-result.php', array_merge($data, $this->data));
	}
	
	/**
	 * Get Data Kabupaten untuk DataTables
	 * Return JSON data untuk populate DataTables
	 */
	public function getDataKabupatenDT() 
	{
		$this->hasPermission('read_all');
		
		$numKabupaten = $this->model->countAllKabupaten();
		$kabupaten = $this->model->getListKabupaten('');
		
		$result['draw'] = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $numKabupaten;
		$result['recordsFiltered'] = $kabupaten['total_filtered'];		
		
		helper('html');
		
		// Tambahkan action buttons untuk setiap row
		foreach ($kabupaten['data'] as $key => &$val) {
			$actions = [
				['type' => 'link', 'href' => $this->moduleURL . '/kabupaten/edit?id=' . $val['id_wilayah_kabupaten'], 'icon' => 'fas fa-edit text-success', 'label' => 'Edit', 'attrs' => ['class' => 'btn-edit']],
			];
			
			if ($this->hasPermission('delete_own') || $this->hasPermission('delete_all')) {
				$actions[] = ['type' => 'form', 'action' => $this->moduleURL . '/kabupaten', 'icon' => 'fas fa-times text-danger', 'label' => 'Delete', 'attrs' => ['data-action' => 'delete-data', 'id' => $val['id_wilayah_kabupaten'], 'data-delete-title' => 'Hapus kabupaten: <strong>' . $val['nama_kabupaten'] . '</strong> ?']];
			}
			
			$val['ignore_btn_action'] = btn_dropdown_actions($actions);
		}
					
		$result['data'] = $kabupaten['data'];
		echo json_encode($result); 
		exit();
	}
	
	/**
	 * Add Kabupaten - Halaman form tambah kabupaten baru
	 * Menampilkan form dan proses submit data kabupaten baru
	 */
	public function addKabupaten() 
	{
		$this->hasPermission('create');
		
		$data = $this->data;
		$data['title'] = 'Tambah Kabupaten/Kota';
		$data['wilayah_type'] = 'kabupaten';
		$data['kabupaten_edit'] = [];
		
		// Ambil daftar provinsi untuk dropdown
		$data['list_provinsi'] = $this->model->getProvinsiList();
		
		// Proses submit form
		if ($this->request->getPost('submit')) {
			$data['message'] = $this->saveKabupaten();
			
			// Jika berhasil, redirect ke halaman edit
			if ($data['message']['status'] == 'ok') {
				return redirect()->to($this->moduleURL . '/kabupaten/edit?id=' . $data['message']['id']);
			}
		}
		
		$this->view('builtin/wilayah/kabupaten-form.php', $data);
	}
	
	/**
	 * Edit Kabupaten - Halaman form edit kabupaten
	 * Menampilkan form edit dan proses update data kabupaten
	 */
	public function editKabupaten()
	{
		$this->hasPermissionPrefix('update');
		
		$data = $this->data;
		$data['title'] = 'Edit Kabupaten/Kota';
		$data['wilayah_type'] = 'kabupaten';
		
		// Submit
		$data['message'] = [];
		if ($this->request->getPost('submit')) {
			$data['message'] = $this->saveKabupaten();
		}
		
		// Ambil data kabupaten berdasarkan ID
		$result = $this->model->getKabupatenById($this->request->getGet('id'));
		
		if (!$result) {
			$this->errorDataNotFound();
			return;
		}
		
		$data['kabupaten_edit'] = $result;
		
		// Ambil daftar provinsi untuk dropdown
		$data['list_provinsi'] = $this->model->getProvinsiList();
		
		$this->view('builtin/wilayah/kabupaten-form.php', $data);
	}
	
	/**
	 * Save Kabupaten - Proses simpan data kabupaten (create/update)
	 * 
	 * @return array Status dan pesan hasil operasi
	 */
	private function saveKabupaten() 
	{		
		$formErrors = $this->validateFormKabupaten();
		$error = false;		
		
		if ($formErrors) {
			$data['status'] = 'error';
			$data['form_errors'] = $formErrors;
			$data['message'] = $formErrors;
			$error = true;
		}
		
		if (!$error) {				
			$data = $this->model->saveKabupaten();
		}
		
		return $data;
	}
	
	/**
	 * Validasi form kabupaten
	 * 
	 * @return mixed Array error jika gagal, false jika sukses
	 */
	private function validateFormKabupaten() 
	{
		$validation = \Config\Services::validation();
		$validation->setRule('id_wilayah_propinsi', 'Provinsi', 'trim|required|numeric');
		$validation->setRule('nama_kabupaten', 'Nama Kabupaten/Kota', 'trim|required');
		
		if ($validation->withRequest($this->request)->run() === false) {
			return $validation->getErrors();
		}
		
		return false;
	}
	
	// ========================================================================
	// KECAMATAN MANAGEMENT
	// ========================================================================
	
	/**
	 * Index Kecamatan - Halaman daftar kecamatan
	 * Menampilkan tabel data kecamatan dengan DataTables
	 */
	public function kecamatan()
	{
		$this->hasPermissionPrefix('read');

		$data['message'] = [];
		
		// Handle delete request
		if ($this->request->getPost('delete')) 
		{
			$this->hasPermissionPrefix('delete');
			
			$result = $this->model->deleteKecamatan();
			if ($result) {
				$data['message'] = ['status' => 'ok', 'message' => 'Data kecamatan berhasil dihapus'];
			} else {
				$data['message'] = ['status' => 'warning', 'message' => 'Tidak ada data yang dihapus'];
			}
		}
		
		$data['title'] = 'Data Kecamatan';
		$data['wilayah_type'] = 'kecamatan';
		$this->view('builtin/wilayah/kecamatan-result.php', array_merge($data, $this->data));
	}
	
	/**
	 * Get Data Kecamatan untuk DataTables
	 * Return JSON data untuk populate DataTables
	 */
	public function getDataKecamatanDT() 
	{
		$this->hasPermission('read_all');
		
		$numKecamatan = $this->model->countAllKecamatan();
		$kecamatan = $this->model->getListKecamatan('');
		
		$result['draw'] = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $numKecamatan;
		$result['recordsFiltered'] = $kecamatan['total_filtered'];		
		
		helper('html');
		
		// Tambahkan action buttons untuk setiap row
		foreach ($kecamatan['data'] as $key => &$val) {
			$actions = [
				['type' => 'link', 'href' => $this->moduleURL . '/kecamatan/edit?id=' . $val['id_wilayah_kecamatan'], 'icon' => 'fas fa-edit text-success', 'label' => 'Edit', 'attrs' => ['class' => 'btn-edit']],
			];
			
			if ($this->hasPermission('delete_own') || $this->hasPermission('delete_all')) {
				$actions[] = ['type' => 'form', 'action' => $this->moduleURL . '/kecamatan', 'icon' => 'fas fa-times text-danger', 'label' => 'Delete', 'attrs' => ['data-action' => 'delete-data', 'id' => $val['id_wilayah_kecamatan'], 'data-delete-title' => 'Hapus kecamatan: <strong>' . $val['nama_kecamatan'] . '</strong> ?']];
			}
			
			$val['ignore_btn_action'] = btn_dropdown_actions($actions);
		}
					
		$result['data'] = $kecamatan['data'];
		echo json_encode($result); 
		exit();
	}
	
	/**
	 * Add Kecamatan - Halaman form tambah kecamatan baru
	 * Menampilkan form dan proses submit data kecamatan baru
	 */
	public function addKecamatan() 
	{
		$this->hasPermission('create');
		
		$data = $this->data;
		$data['title'] = 'Tambah Kecamatan';
		$data['wilayah_type'] = 'kecamatan';
		$data['kecamatan_edit'] = [];
		
		// Ambil daftar provinsi dan kabupaten untuk dropdown
		$data['list_provinsi'] = $this->model->getProvinsiList();
		$data['list_kabupaten'] = [];
		
		// Proses submit form
		if ($this->request->getPost('submit')) {
			$data['message'] = $this->saveKecamatan();
			
			// Jika berhasil, redirect ke halaman edit
			if ($data['message']['status'] == 'ok') {
				return redirect()->to($this->moduleURL . '/kecamatan/edit?id=' . $data['message']['id']);
			}
		}
		
		$this->view('builtin/wilayah/kecamatan-form.php', $data);
	}
	
	/**
	 * Edit Kecamatan - Halaman form edit kecamatan
	 * Menampilkan form edit dan proses update data kecamatan
	 */
	public function editKecamatan()
	{
		$this->hasPermissionPrefix('update');
		
		$data = $this->data;
		$data['title'] = 'Edit Kecamatan';
		$data['wilayah_type'] = 'kecamatan';
		
		// Submit
		$data['message'] = [];
		if ($this->request->getPost('submit')) {
			$data['message'] = $this->saveKecamatan();
		}
		
		// Ambil data kecamatan berdasarkan ID
		$result = $this->model->getKecamatanById($this->request->getGet('id'));
		
		if (!$result) {
			$this->errorDataNotFound();
			return;
		}
		
		$data['kecamatan_edit'] = $result;
		
		// Ambil daftar provinsi untuk dropdown
		$data['list_provinsi'] = $this->model->getProvinsiList();
		
		// Ambil daftar kabupaten berdasarkan provinsi
		$data['list_kabupaten'] = $this->model->getKabupatenListByProvinsi($result['id_wilayah_propinsi']);
		
		$this->view('builtin/wilayah/kecamatan-form.php', $data);
	}
	
	/**
	 * Save Kecamatan - Proses simpan data kecamatan (create/update)
	 * 
	 * @return array Status dan pesan hasil operasi
	 */
	private function saveKecamatan() 
	{		
		$formErrors = $this->validateFormKecamatan();
		$error = false;		
		
		if ($formErrors) {
			$data['status'] = 'error';
			$data['form_errors'] = $formErrors;
			$data['message'] = $formErrors;
			$error = true;
		}
		
		if (!$error) {				
			$data = $this->model->saveKecamatan();
		}
		
		return $data;
	}
	
	/**
	 * Validasi form kecamatan
	 * 
	 * @return mixed Array error jika gagal, false jika sukses
	 */
	private function validateFormKecamatan() 
	{
		$validation = \Config\Services::validation();
		$validation->setRule('id_wilayah_kabupaten', 'Kabupaten/Kota', 'trim|required|numeric');
		$validation->setRule('nama_kecamatan', 'Nama Kecamatan', 'trim|required');
		
		if ($validation->withRequest($this->request)->run() === false) {
			return $validation->getErrors();
		}
		
		return false;
	}
	
	// ========================================================================
	// KELURAHAN MANAGEMENT
	// ========================================================================
	
	/**
	 * Index Kelurahan - Halaman daftar kelurahan
	 * Menampilkan tabel data kelurahan dengan DataTables
	 */
	public function kelurahan()
	{
		$this->hasPermissionPrefix('read');

		$data['message'] = [];
		
		// Handle delete request
		if ($this->request->getPost('delete')) 
		{
			$this->hasPermissionPrefix('delete');
			
			$result = $this->model->deleteKelurahan();
			if ($result) {
				$data['message'] = ['status' => 'ok', 'message' => 'Data kelurahan berhasil dihapus'];
			} else {
				$data['message'] = ['status' => 'warning', 'message' => 'Tidak ada data yang dihapus'];
			}
		}
		
		$data['title'] = 'Data Kelurahan/Desa';
		$data['wilayah_type'] = 'kelurahan';
		$this->view('builtin/wilayah/kelurahan-result.php', array_merge($data, $this->data));
	}
	
	/**
	 * Get Data Kelurahan untuk DataTables
	 * Return JSON data untuk populate DataTables
	 */
	public function getDataKelurahanDT() 
	{
		$this->hasPermission('read_all');
		
		$numKelurahan = $this->model->countAllKelurahan();
		$kelurahan = $this->model->getListKelurahan('');
		
		$result['draw'] = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $numKelurahan;
		$result['recordsFiltered'] = $kelurahan['total_filtered'];		
		
		helper('html');
		
		// Tambahkan action buttons untuk setiap row
		foreach ($kelurahan['data'] as $key => &$val) {
			$actions = [
				['type' => 'link', 'href' => $this->moduleURL . '/kelurahan/edit?id=' . $val['id_wilayah_kelurahan'], 'icon' => 'fas fa-edit text-success', 'label' => 'Edit', 'attrs' => ['class' => 'btn-edit']],
			];
			
			if ($this->hasPermission('delete_own') || $this->hasPermission('delete_all')) {
				$actions[] = ['type' => 'form', 'action' => $this->moduleURL . '/kelurahan', 'icon' => 'fas fa-times text-danger', 'label' => 'Delete', 'attrs' => ['data-action' => 'delete-data', 'id' => $val['id_wilayah_kelurahan'], 'data-delete-title' => 'Hapus kelurahan: <strong>' . $val['nama_kelurahan'] . '</strong> ?']];
			}
			
			$val['ignore_btn_action'] = btn_dropdown_actions($actions);
		}
					
		$result['data'] = $kelurahan['data'];
		echo json_encode($result); 
		exit();
	}
	
	/**
	 * Add Kelurahan - Halaman form tambah kelurahan baru
	 * Menampilkan form dan proses submit data kelurahan baru
	 */
	public function addKelurahan() 
	{
		$this->hasPermission('create');
		
		$data = $this->data;
		$data['title'] = 'Tambah Kelurahan/Desa';
		$data['wilayah_type'] = 'kelurahan';
		$data['kelurahan_edit'] = [];
		
		// Ambil daftar provinsi untuk dropdown
		$data['list_provinsi'] = $this->model->getProvinsiList();
		$data['list_kabupaten'] = [];
		$data['list_kecamatan'] = [];
		
		// Proses submit form
		if ($this->request->getPost('submit')) {
			$data['message'] = $this->saveKelurahan();
			
			// Jika berhasil, redirect ke halaman edit
			if ($data['message']['status'] == 'ok') {
				return redirect()->to($this->moduleURL . '/kelurahan/edit?id=' . $data['message']['id']);
			}
		}
		
		$this->view('builtin/wilayah/kelurahan-form.php', $data);
	}
	
	/**
	 * Edit Kelurahan - Halaman form edit kelurahan
	 * Menampilkan form edit dan proses update data kelurahan
	 */
	public function editKelurahan()
	{
		$this->hasPermissionPrefix('update');
		
		$data = $this->data;
		$data['title'] = 'Edit Kelurahan/Desa';
		$data['wilayah_type'] = 'kelurahan';
		
		// Submit
		$data['message'] = [];
		if ($this->request->getPost('submit')) {
			$data['message'] = $this->saveKelurahan();
		}
		
		// Ambil data kelurahan berdasarkan ID
		$result = $this->model->getKelurahanById($this->request->getGet('id'));
		
		if (!$result) {
			$this->errorDataNotFound();
			return;
		}
		
		$data['kelurahan_edit'] = $result;
		
		// Ambil daftar provinsi untuk dropdown
		$data['list_provinsi'] = $this->model->getProvinsiList();
		
		// Ambil daftar kabupaten berdasarkan provinsi
		$data['list_kabupaten'] = $this->model->getKabupatenListByProvinsi($result['id_wilayah_propinsi']);
		
		// Ambil daftar kecamatan berdasarkan kabupaten
		$data['list_kecamatan'] = $this->model->getKecamatanListByKabupaten($result['id_wilayah_kabupaten']);
		
		$this->view('builtin/wilayah/kelurahan-form.php', $data);
	}
	
	/**
	 * Save Kelurahan - Proses simpan data kelurahan (create/update)
	 * 
	 * @return array Status dan pesan hasil operasi
	 */
	private function saveKelurahan() 
	{		
		$formErrors = $this->validateFormKelurahan();
		$error = false;		
		
		if ($formErrors) {
			$data['status'] = 'error';
			$data['form_errors'] = $formErrors;
			$data['message'] = $formErrors;
			$error = true;
		}
		
		if (!$error) {				
			$data = $this->model->saveKelurahan();
		}
		
		return $data;
	}
	
	/**
	 * Validasi form kelurahan
	 * 
	 * @return mixed Array error jika gagal, false jika sukses
	 */
	private function validateFormKelurahan() 
	{
		$validation = \Config\Services::validation();
		$validation->setRule('id_wilayah_kecamatan', 'Kecamatan', 'trim|required|numeric');
		$validation->setRule('nama_kelurahan', 'Nama Kelurahan/Desa', 'trim|required');
		
		if ($validation->withRequest($this->request)->run() === false) {
			return $validation->getErrors();
		}
		
		return false;
	}
	
	// ========================================================================
	// AJAX HELPERS - Untuk cascading dropdown
	// ========================================================================
	
	/**
	 * AJAX Get Kabupaten by Provinsi
	 * Return JSON data kabupaten berdasarkan ID provinsi
	 */
	public function ajaxGetKabupatenByProvinsi()
	{
		$result = [];
		$idProvinsi = $this->request->getGet('id');
		
		if ($idProvinsi && is_numeric($idProvinsi)) {
			$result = $this->model->getKabupatenListByProvinsi($idProvinsi);
		}
		
		echo json_encode($result);
		exit;
	}
	
	/**
	 * AJAX Get Kecamatan by Kabupaten
	 * Return JSON data kecamatan berdasarkan ID kabupaten
	 */
	public function ajaxGetKecamatanByKabupaten()
	{
		$result = [];
		$idKabupaten = $this->request->getGet('id');
		
		if ($idKabupaten && is_numeric($idKabupaten)) {
			$result = $this->model->getKecamatanListByKabupaten($idKabupaten);
		}
		
		echo json_encode($result);
		exit;
	}
	
	/**
	 * AJAX Get Kelurahan by Kecamatan
	 * Return JSON data kelurahan berdasarkan ID kecamatan
	 */
	public function ajaxGetKelurahanByKecamatan()
	{
		$result = [];
		$idKecamatan = $this->request->getGet('id');
		
		if ($idKecamatan && is_numeric($idKecamatan)) {
			$result = $this->model->getKelurahanListByKecamatan($idKecamatan);
		}
		
		echo json_encode($result);
		exit;
	}
}
