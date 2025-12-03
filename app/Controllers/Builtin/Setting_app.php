<?php
/**
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Controllers\Builtin;
use App\Models\Builtin\SettingAppModel;

class Setting_app extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		// $this->mustLoggedIn();
		
		$this->model = new SettingAppModel;	
		$this->data['site_title'] = 'Halaman Setting Web';
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/spectrum/spectrum.min.js?r=' . time());
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/setting-logo.js?r=' . time());
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/image-upload.js?r=' . time());
		$this->addStyle ( $this->config->baseURL . 'public/vendors/spectrum/spectrum.css');
		$this->addStyle ( $this->config->baseURL . 'public/themes/modern/builtin/css/setting-app.css');
		// $this->addStyle ( $this->config->baseURL . 'public/themes/modern/builtin/css/login-header.css');
		
		helper(['cookie', 'form']);
	}
	
	public function index() 
	{
		$data = $this->data;
		if ($this->request->getPost('submit')) 
		{
			$formErrors = $this->validateForm();
			
			if ($formErrors) {
				$data['message'] = ['status' => 'error', 'message' => $formErrors];
			} else {
				
				// echo '<pre>'; print_r
				if (!$this->hasPermission('update_all'))
				{
					$data['message'] = ['status' => 'error', 'message' => 'Role anda tidak diperbolehkan melakukan perubahan'];
				} else {
					$result = $this->model->saveData();
					$data['message'] = ['status' => $result['status'], 'message' => $result['message']];
				}
			}
		}
		
		$query = $this->model->getSettingAplikasi();
		foreach($query as $val) {
			$data[$val['param']] = $val['value'];
		}

		$data['title'] = 'Edit ' . $this->currentModule['judul_module'];
		
		$this->view('builtin/setting-app-form.php', $data);
	}

	private function validateForm() 
	{
		$validation =  \Config\Services::validation();		
		$validation->setRule('footer_app', 'Footer Aplikasi', 'trim|required');
		$validation->setRule('background_logo', 'Background Logo', 'trim|required');
		$validation->setRule('judul_web', 'Judul Website', 'trim|required');
		$validation->setRule('deskripsi_web', 'Deskripsi Web', 'trim|required');
		
		$validation->withRequest($this->request)
					->run();
		$formErrors =  $validation->getErrors();
						
		// $formErrors = [];
		$logoLogin = $this->request->getFile('logo_login');
		if ($logoLogin && $logoLogin->getName()) {
			
			$fileType = $logoLogin->getMimeType();
			$allowed = ['image/png', 'image/jpeg', 'image/jpg'];
			
			if (!in_array($fileType, $allowed)) {
				$formErrors['logo_login'] = 'Tipe file harus ' . join(', ', $allowed);
			}
			
			if ($logoLogin->getSize() > 300 * 1024) {
				$formErrors['logo_login'] = 'Ukuran file maksimal 300Kb';
			}
			
			$info = getimagesize($logoLogin->getTempName());
			if ($info[0] < 20 || $info[1] < 20) { //0 Width, 1 Height
				$formErrors['logo_login'] = 'Dimensi logo login minimal: 20px x 20px, dimensi anda ' . $info[0] . 'px x ' . $info[1] . 'px';
			}
		}
		
		$logoApp = $this->request->getFile('logo_app');
		if ($logoApp && $logoApp->getName()) {
			
			$fileType = $logoApp->getMimeType();
			$allowed = ['image/png', 'image/jpeg', 'image/jpg'];
			
			if (!in_array($fileType, $allowed)) {
				$formErrors['logo_app'] = 'Tipe file harus ' . join(', ', $allowed);
			}
			
			if ($logoApp->getSize() > 300 * 1024) {
				$formErrors['logo_app'] = 'Ukuran file maksimal 300Kb';
			}
			
			$info = getimagesize($logoApp->getTempName());
			if ($info[0] < 20 || $info[1] < 20) { //0 Width, 1 Height
				$formErrors['logo_app'] = 'Dimensi logo aplikasi minimal: 20px x 20px, dimensi anda ' . $info[0] . 'px x ' . $info[1] . 'px';
			}
		}
		
		$favicon = $this->request->getFile('favicon');
		if ($favicon && $favicon->getName()) {
			
			$fileType = $favicon->getMimeType();
			$allowed = ['image/png'];
			
			if (!in_array($fileType, $allowed)) {
				$formErrors['favicon'] = 'Tipe file harus ' . join(', ', $allowed) . ' tipe file Anda: ' . $fileType;
			}
			
			if ($favicon->getSize() > 300 * 1024) {
				$formErrors['favicon'] = 'Ukuran file maksimal 300Kb';
			}
		}
		
		return $formErrors;
	}	
}