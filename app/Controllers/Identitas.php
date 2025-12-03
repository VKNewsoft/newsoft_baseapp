<?php
/**
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Controllers;
use App\Models\IdentitasModel;

class Identitas extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new IdentitasModel;	
		$this->data['title'] = 'Identitas';
		$this->addJs ( $this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css' );
		
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/wilayah.js');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/identitas.js');
		
		helper(['cookie', 'form']);
	}
	
	public function index() {
		
		if ($this->request->getPost('submit')) 
		{
			$error = $this->validateFormSetting();
			if ($error) {
				$this->data['message'] = ['status' => 'error', 'message' => $error];
			} else {
				$message = $this->model->saveData();
				$this->data['message'] = $message;
			}
		}
		
		$identitas = $this->model->getIdentitas();
		
		$wilayah = new \App\Controllers\Wilayah();
		$data_wilayah = $wilayah->getDataWilayah((($identitas)?$identitas['id_wilayah_kelurahan']:null));
		$this->data = array_merge($this->data, $data_wilayah);
		
		$this->data['identitas'] = $identitas;
		$this->view('identitas-form.php', $this->data);
	}
	
	private function validateFormSetting() {
	
		$validation =  \Config\Services::validation();
		$validation->setRule('nama', 'Nama', 'trim|required|min_length[5]');
		$validation->setRule('alamat', 'Nama Setting', 'trim|required|min_length[5]');
		$validation->withRequest($this->request)->run();
		$formErrors = $validation->getErrors();
		
		return $formErrors;
	}
}
