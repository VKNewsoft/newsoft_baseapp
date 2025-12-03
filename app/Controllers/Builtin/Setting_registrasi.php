<?php
/**
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Controllers\Builtin;
use App\Models\Builtin\SettingRegistrasiModel;

class Setting_registrasi extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();

		$this->model = new SettingRegistrasiModel;	
		$this->data['site_title'] = 'Halaman Setting Registrasi';
		
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/setting-registrasi.js');
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
				
				if (!$this->hasPermission('update_all'))
				{
					$data['message'] = ['status' => 'error', 'message' => 'Role anda tidak diperbolehkan melakukan perubahan'];
				} else {
					$result = $this->model->saveData();
					$data['message'] = ['status' => $result['status'], 'message' => $result['message']];
				}
			}
		}
		
		$query = $this->model->getSettingRegistrasi();
		foreach($query as $val) {
			$data['setting'][$val['param']] = $val['value'];
		}
		
		$data['title'] = 'Edit ' . $this->currentModule['judul_module'];
		$data['role'] = $this->model->getRole();
		$data['list_module'] = $this->model->getListModules();
		
		$this->view('builtin/setting-registrasi-form.php', $data);
	}

	private function validateForm() 
	{
		$validation =  \Config\Services::validation();		
		$validation->setRule('enable', 'Enable', 'trim|required');
		$validation->setRule('metode_aktivasi', 'Metode Aktivasi', 'trim|required');
		$validation->setRule('id_role', 'Role', 'trim|required');
		
		$validation->withRequest($this->request)
					->run();
		$formErrors =  $validation->getErrors();

		return $formErrors;
	}	
}