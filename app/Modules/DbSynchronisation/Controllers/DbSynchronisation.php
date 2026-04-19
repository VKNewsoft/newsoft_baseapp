<?php
/**
 * @author VKNewsoft - Newsoft Developer
 * @year 2026
 */

namespace App\Modules\DbSynchronisation\Controllers;

use App\Modules\DbSynchronisation\Models\DbSynchronisationModel;

class DbSynchronisation extends \App\Modules\Common\Controllers\BaseController
{
	protected $model;

	public function __construct()
	{
		parent::__construct();

		// Asset modul dipisah lokal agar tampilan sinkronisasi tetap self-contained
		// dan tidak menambah ketergantungan CSS/JS khusus di modul lain.
		$this->model = new DbSynchronisationModel();
		$this->data['site_title'] = 'DB Synchronisation';
		$this->addStyle($this->moduleAsset('css/db-synchronisation.css') . '?v=' . @filemtime(APPPATH . 'Modules/DbSynchronisation/Assets/css/db-synchronisation.css'));
	}

	public function index()
	{
		$this->hasPermission('read_all');

		$data = $this->data;
		$data['msg'] = session()->getFlashdata('msg') ?: [];
		$data['can_apply_sync'] = $this->hasPermission('update_all');
		$data['sync_summary'] = $this->model->getSyncSummary((int) $this->request->getGet('refresh') === 1);
		$data['title'] = 'DB Synchronisation';

		$this->view('db-synchronisation-result.php', $data);
	}

	public function apply()
	{
		$this->hasPermission('update_all', true);

		// Token form diverifikasi agar eksekusi ALTER aman dari submit liar.
		if (!$this->request->getPost('submit') || !$this->auth->validateFormToken('form_db_synchronisation')) {
			session()->setFlashdata('msg', [
				'status' => 'error',
				'message' => 'Token sinkronisasi tidak valid, silakan muat ulang halaman lalu coba lagi'
			]);
			return redirect()->to($this->moduleURL);
		}

		// Mode sinkronisasi dipilih dari tombol submit agar flow lama tetap aman,
		// sekaligus membuka opsi full sync untuk seluruh diff yang punya SQL.
		$syncMode = $this->request->getPost('sync_mode') === 'full' ? 'full' : 'safe';
		$result = $syncMode === 'full' ? $this->model->applyFullSync() : $this->model->applySafeSync();
		$message = $result['message'];
		if (!empty($result['errors'])) {
			$message .= '<br>' . implode('<br>', $result['errors']);
		}

		session()->setFlashdata('msg', [
			'status' => $result['status'],
			'message' => $message
		]);

		return redirect()->to($this->moduleURL);
	}
}
