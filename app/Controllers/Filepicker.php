<?php
/**
 * Filepicker Controller
 * Mengelola file manager dengan upload, delete, dan preview
 * 
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Controllers;
use App\Models\FilepickerModel;

class Filepicker extends BaseController
{
	private $configFilepicker;
	
	public function __construct() 
	{
		parent::__construct();
		$this->model = new FilepickerModel;
		$this->configFilepicker = new \Config\Filepicker();
		
		$ajax = false;
		if( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ) {
			$ajax = true;
		}
		
		if (!$ajax) {
			$this->addJs('
				var filepicker_server_url = "' . $this->configFilepicker->serverURL . '";
				var filepicker_icon_url = "' . $this->configFilepicker->iconURL . '";', true
			);
		}
	
		$this->addJs($this->config->baseURL . 'public/vendors/nsdfilepicker/nsdfilepicker.js');
		$this->addJs($this->config->baseURL . 'public/themes/modern/js/nsdfilepicker-defaults.js');
		$this->addJs($this->config->baseURL . 'public/themes/modern/js/filepicker.js');
		$this->addJs($this->config->baseURL . 'public/vendors/dropzone/dropzone.min.js');

		$this->addStyle($this->config->baseURL . 'public/vendors/nsdfilepicker/nsdfilepicker.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/nsdfilepicker/nsdfilepicker-loader.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/nsdfilepicker/nsdfilepicker-modal.css');
		$this->addStyle($this->config->baseURL . 'public/themes/modern/css/filepicker.css');

	}

	/**
	 * Halaman utama file picker dengan pagination dan filter
	 */
    public function index()
	{
        $message = [];
		$itemPerPage = $this->request->getGet('item_per_page') ?? $this->configFilepicker->itemPerPage;
		$idCompany = $this->request->getGet('id_company') ?? 0;
		
		$loadItem = $this->model->getData($itemPerPage, $idCompany);
		
		// Response AJAX
		if ($this->request->getGet('ajax')) {
			echo json_encode($loadItem['result']);
			exit();
		}
				
		$this->data['title'] = 'File Picker Manager';
		$this->data['filter_file'] = ['' => 'All Files', 'image' => 'Image', 'video' => 'Video', 'document' => 'Dokumen', 'archive' => 'Archive'];
		$this->data['filter_tgl'] = $loadItem['result']['filter_tgl'] ?? [];
		$this->data['total_item'] = $loadItem['total_item'];
		$this->data['loaded_item'] = $loadItem['loaded_item'];
		$this->data['item_per_page'] = $itemPerPage;
        $this->data['result'] = $loadItem['result'];
        $this->data['message'] = $message;

        if (!$this->data['result']) {
            $this->errorDataNotfound();
			return;
		}

		$this->view('filepicker-result.php', $this->data);
	}
	
	/**
	 * Update metadata file via AJAX
	 */
	public function ajaxUpdateFile() 
	{
		$update = $this->model->updateMetaFile();
		if ($update)
			echo json_encode(['status' => 'ok']);
		else
			echo json_encode(['status' => 'error']);
		
		exit;
	}
	
	/**
	 * Upload file baru via AJAX
	 */
	public function ajaxUploadFile() 
	{
		$allowedFile = $this->request->getGet('allowed_type') ?? 'file_type_images';
		$idCompany = $this->request->getGet('id_company') ?? 0;
		
		$result = $this->model->uploadFile($allowedFile, $idCompany);
		
		echo json_encode($result);
		exit;
	}
	
	/**
	 * Hapus file via AJAX
	 */
	public function ajaxDeleteFile() 
	{
		$result = ['status' => 'error', 'message' => 'Bad request'];
		$error = [];
		
		// Validasi AJAX request
		if (!$this->request->getPost('submit') 
			|| !$this->request->isAJAX()
		) {
			$error[] = 'Bad request';
		}

		if (!$this->request->getPost('id')) {
			$error[] = 'ID file tidak valid';
		}
		
		if (!$error) {
			$result = $this->model->deleteFile();
		}
		
		echo json_encode($result);
		exit();
	}
	
	/**
	 * Tampilan file picker untuk TinyMCE
	 */
	public function tinymce() 
	{
		echo view('themes/modern/filepicker-tinymce.php', $this->data);
		exit;
	}
	
	/**
	 * Get icon file berdasarkan MIME type
	 */
	public function ajaxFileIcon()
	{
		helper('filepicker');
		$listFileType = file_type();
		
		$result = ['status' => 'error', 'icon' => ''];
		
		$fileIcon = 'file';
		$mime = $this->request->getGet('mime');
		$ext = $this->request->getGet('ext');
		
		if (key_exists($mime, $listFileType)) {
			$fileIcon = $listFileType[$mime]['extension'];
		} else {
			foreach ($listFileType as $val) {
				if (($val['extension'] ?? '') == $ext) {
					$fileIcon = strtolower($ext);
				}
			}
		}
		
		$iconPath = $this->configFilepicker->filepickerIconPath . $fileIcon . '.png';			
			
		if (file_exists($iconPath)) 
		{
			$result['status'] = 'ok';
			$result['icon'] = 'data:image/png;base64,' . base64_encode(file_get_contents($iconPath));
		}
		
		echo json_encode($result);
		exit;
	}
	
	/**
	 * Hapus semua file (HATI-HATI!)
	 */
	public function ajaxDeleteAll() 
	{
		$result = ['status' => 'error', 'message' => 'Bad request'];
		
		if ($this->request->getPost('submit') && $this->request->isAJAX()) 
		{
			$result = $this->model->deleteAllFiles();
		}
		
		echo json_encode($result);
		exit;
	}
}