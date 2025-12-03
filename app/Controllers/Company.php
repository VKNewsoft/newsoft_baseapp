<?php
/**
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Controllers;
use App\Models\CompanyModel;

class Company extends \App\Controllers\BaseController
{
	protected $model;
	
	public function __construct() {
		
		parent::__construct();
		$this->model = new CompanyModel;
		$this->data['site_title'] = 'Company';
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css' );
		
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/wilayah.js');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/company.js');
	}
	
	public function index()
	{
		$this->hasPermission('read_all');
		$this->view('company-result.php', $this->data);
	}

	public function getDataSkema($id_skema =null) {
		
		if ($id_skema) {
			$data['id_skema'] = $id_skema;
		} else {
			$data['id_skema'] = '';
		}
		
		$data['skema'] =  $this->model->getSkema();
		return $data;
	}

	public function getDataBank($id_bank =null) {
		
		if ($id_bank) {
			$data['id_bank'] = $id_bank;
		} else {
			$data['id_bank'] = '';
		}
		
		$data['bank_list'] =  $this->model->getBank();
		return $data;
	}
	
	public function ajaxDeleteData() {

		$delete = $this->model->deleteData();
		if ($delete) {
			$message['status'] = 'ok';
			$message['message'] = 'Data berhasil dihapus';
		} else {
			$message['status'] = 'error';
			$message['message'] = 'Data gagal dihapus';
		}
		echo json_encode($message);
	}
	
	public function ajaxGetFormData() {
		$this->data['tenant'] = [];
		if ($this->request->getGet('id')) {
			if ($this->request->getGet('id')) {
				$this->data['tenant'] = $this->model->getCompanyById($this->request->getGet('id'));
				if (!$this->data['tenant'])
					return;
			}
		}
		$dataBank = $this->getDataBank(@$this->data['tenant']['id_bank']);
		// $dataSkema = $this->getDataSkema(@$this->data['tenant']['id_skema']);
		$this->data = array_merge($this->data, $dataBank);
		echo view('themes/modern/company-form.php', $this->data);
	}
	
	public function ajaxUpdateData() {
		$message = $this->model->saveData();
		echo json_encode($message);
	}
	
	public function ajaxSwitchDefault() {
		$result = $this->model->switchDefault();
		echo json_encode($result);
	}
		
	public function getDataDT() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllData();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListData();
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			$checked = $val['tenant_aktif'] == 'Y' ? 'checked' : '';
			$text_checked = $val['tenant_aktif'] == 'Y' ? 'Aktif' : 'Non Aktif';
			$val['ignore_search_urut'] = $no;
			$val['tenant_aktif'] = '<div class="form-switch text-center">
						 <input name="aktif" title ="' . $text_checked . '" type="checkbox" class="form-check-input switch" data-id-tenant="' . $val['id_company'] . '" ' . $checked . '>
							</div>';
			$val['ignore_search_action'] = '<div class="form-inline btn-action-group">'
										. btn_label(
												['icon' => 'fas fa-edit'
													, 'attr' => ['class' => 'btn btn-success btn-edit btn-xs me-1', 'data-id' => $val['id_company']]
													, 'label' => 'Edit'
												])
										. btn_label(
												['icon' => 'fas fa-times'
													, 'attr' => ['class' => 'btn btn-danger btn-delete btn-xs'
																	, 'data-id' => $val['id_company']
																	, 'data-delete-title' => 'Hapus nama tenant : <strong>' . $val['nama_company'] . '</strong>'
																]
													, 'label' => 'Delete'
												]) . 
										
										'</div>';
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
}