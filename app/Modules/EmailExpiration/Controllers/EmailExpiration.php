<?php
/**
 * Controller Email Expiration.
 *
 * Controller ini mengikuti pola CRUD popup/DataTable existing agar
 * pengalaman pakai dan maintenance tetap konsisten antar module.
 */

namespace App\Modules\EmailExpiration\Controllers;

use App\Modules\EmailExpiration\Models\EmailExpirationModel;

class EmailExpiration extends \App\Modules\Common\Controllers\BaseController
{
	protected $model;

	public function __construct()
	{
		parent::__construct();

		$resultPageVersion = '?v=' . @filemtime(APPPATH . 'Modules/Common/Assets/css/result-page.css');
		$resultTableVersion = '?v=' . @filemtime(APPPATH . 'Modules/Common/Assets/js/result-table.js');
		$emailExpirationJsVersion = '?v=' . @filemtime(APPPATH . 'Modules/EmailExpiration/Assets/js/email-expiration.js');

		$this->model = new EmailExpirationModel();
		$this->data['site_title'] = 'Email Expiration';

		// Asset result page dipakai ulang dari Common, sedangkan interaksi CRUD
		// spesifik module tetap diload dari asset lokal module ini.
		$this->addJs($this->commonAsset('js/result-table.js') . $resultTableVersion);
		$this->addJs($this->moduleAsset('js/email-expiration.js') . $emailExpirationJsVersion);
		$this->addStyle($this->commonAsset('css/result-page.css') . $resultPageVersion);
	}

	public function index()
	{
		$this->hasPermission('read_all');
		$this->view('email-expiration-result.php', $this->data);
	}

	public function ajaxGetFormData()
	{
		$this->hasPermissionPrefix('read');
		$this->data['email_expiration'] = [];

		$id = (int) ($this->request->getGet('id') ?? 0);
		if ($id > 0) {
			$this->data['email_expiration'] = $this->model->getById($id);
			if (!$this->data['email_expiration']) {
				return;
			}
		}

		echo $this->fetchView('email-expiration-form.php', $this->data);
	}

	public function ajaxUpdateData()
	{
		/**
		 * Aksi simpan dipisah berdasarkan konteks tambah/edit agar permission
		 * create dan update existing tetap dihormati sesuai pattern project.
		 */
		$id = (int) ($this->request->getPost('id') ?? 0);
		if ($id > 0) {
			$this->hasPermissionPrefix('update');
		} else {
			$this->hasPermission('create');
		}

		echo json_encode($this->model->saveData());
	}

	public function ajaxDeleteData()
	{
		$this->hasPermissionPrefix('delete');
		$id = (int) ($this->request->getPost('id') ?? 0);
		$delete = $id > 0 ? $this->model->deleteData($id) : false;

		echo json_encode([
			'status' => $delete ? 'ok' : 'error',
			'message' => $delete ? 'Data email berhasil dihapus' : 'Data email gagal dihapus'
		]);
	}

	public function ajaxRenew()
	{
		$this->hasPermissionPrefix('update');
		$id = (int) ($this->request->getPost('id') ?? 0);
		echo json_encode($this->model->renewData($id));
	}

	public function getDataDT()
	{
		$this->hasPermissionPrefix('read');
		helper('html');

		$numData = $this->model->countAllData();
		$result['draw'] = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $numData;

		$query = $this->model->getListData();
		$result['recordsFiltered'] = $query['total_filtered'];

		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as &$val) {
			/**
			 * Nilai email mentah disimpan lebih dulu agar tombol renew/delete
			 * tidak ikut membawa markup kolom yang sudah diformat.
			 */
			$emailAkunRaw = $val['email_akun'];
			$statusMeta = $this->model->buildStatusMeta($val['tgl_end']);

			if ($statusMeta['days_remaining'] <= 0) {
				$remainingText = 'Sudah bisa di renew ulang';
				$statusTextClass = 'text-success fw-semibold';
			} else {
				$remainingText = $statusMeta['days_remaining'] . ' hari lagi';
				$statusTextClass = 'text-warning fw-semibold';
			}

			$val['ignore_search_urut'] = $no;
			$val['subscription'] = '<div class="d-flex flex-column gap-1">'
				. '<span class="' . $statusTextClass . '">' . $val['subscription']  . '</span>'
				. '</div>';
			$val['email_akun'] = '<div class="d-flex flex-column gap-1">'
				. '<strong>' . esc($val['email_akun']) . '</strong>'
				. '<span class="' . $statusTextClass . '">' . $remainingText . '</span>'
				. '</div>';
			$val['expiration_hari'] = '<span class="badge bg-light text-dark border">' . (int) $val['expiration_hari'] . ' Hari</span>';
			$val['tgl_start'] = format_tanggal($val['tgl_start']);
			$val['tgl_end'] = '<div class="d-flex flex-column gap-1">'
				. '<span>' . format_tanggal($val['tgl_end']) . '</span>'
				. '</div>';

			$val['ignore_search_action'] = '<div class="dropdown">'
				. '<button class="btn btn-secondary btn-xs dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">'
				. '<i class="fas fa-ellipsis-v"></i>'
				. '</button>'
				. '<ul class="dropdown-menu dropdown-menu-end">'
				. '<li>' . btn_label([
					'icon' => 'fas fa-rotate-right',
					'attr' => ['class' => 'dropdown-item btn-renew', 'data-id' => $val['id_email_expiration'], 'data-email' => esc($emailAkunRaw)],
					'label' => 'Renew'
				]) . '</li>'
				. '<li>' . btn_label([
					'icon' => 'fas fa-edit',
					'attr' => ['class' => 'dropdown-item btn-edit', 'data-id' => $val['id_email_expiration']],
					'label' => 'Edit'
				]) . '</li>'
				. '<li><hr class="dropdown-divider"></li>'
				. '<li>' . btn_label([
					'icon' => 'fas fa-times',
					'attr' => ['class' => 'dropdown-item btn-delete text-danger', 'data-id' => $val['id_email_expiration'], 'data-delete-title' => 'Hapus akun email : <strong>' . esc($emailAkunRaw) . '</strong>'],
					'label' => 'Delete'
				]) . '</li>'
				. '</ul>'
				. '</div>';
			$no++;
		}

		$result['data'] = $query['data'];
		echo json_encode($result);
		exit();
	}
}
