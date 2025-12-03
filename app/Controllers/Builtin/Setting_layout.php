<?php
/**
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */


namespace App\Controllers\Builtin;
use App\Models\Builtin\SettingLayoutModel;

class Setting_layout extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		// $this->mustLoggedIn();
		
		$this->model = new SettingLayoutModel;	
		$this->data['site_title'] = 'Halaman Setting';
		
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/builtin/js/setting-layout.js?r=' . time());
		$this->addStyle ( $this->config->baseURL . 'public/themes/modern/builtin/css/setting-layout.css');
		
		
		helper(['cookie', 'form']);
	}
	
	public function index() 
	{
		$data = $this->data;
		if ($this->request->getPost('submit')) 
		{
			
			if ($this->hasPermission('update_all')
				|| $this->hasPermission('update_own')
			) {
				$save = $this->model->saveData();
				
				if ($save) {
					$data['status'] = 'ok';
					$data['message'] = 'Data berhasil disimpan';
				} else {
					$data['status'] = 'error';
					$data['message'] = 'Data gagal disimpan';
				}
			} else {
				$data['status'] = 'error';
				$data['message'] = 'Role anda tidak diperbolehkan melakukan perubahan';
			}
			
			if ($this->request->getPost('ajax')) {
				echo json_encode($data); die;
			}
		}
		
		$userSetting = $this->model->getUserSetting();
					
		if ($userSetting) {
			$userParam = json_decode($userSetting['param'], true);
			$data = array_merge($data, $userParam);
		} else {
			$query = $this->model->getDefaultSetting();
			
			foreach($query as $val) {
				$data[$val['param']] = $val['value'];
			}
		}
		
		$data['list_bootswatch_theme'] = scandir(ROOTPATH . 'public/vendors/bootswatch');
	
		$data['title'] = 'Edit ' . $this->currentModule['judul_module'];
		$this->view('builtin/setting-layout-form.php', $data);
	}
	
}