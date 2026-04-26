<?php
/**
 * Controller Forex Monitor menampilkan live price, chart interaktif, histori
 * OHLC, fetch manual, serta pengelolaan alert GBP/JPY dalam satu module.
 */

namespace App\Modules\ForexMonitor\Controllers;

use App\Modules\ForexMonitor\Libraries\ForexDataService;
use App\Modules\ForexMonitor\Libraries\ForexMarketDataService;
use App\Modules\ForexMonitor\Models\ForexMonitorModel;

class ForexMonitor extends \App\Modules\Common\Controllers\BaseController
{
	protected $model;
	protected $forexService;
	protected $marketDataService;

	public function __construct()
	{
		parent::__construct();
		helper(['html', 'form']);

		$this->model = new ForexMonitorModel();
		$this->forexService = new ForexDataService($this->model);
		$this->marketDataService = new ForexMarketDataService($this->model);
		$this->data['site_title'] = 'Forex Monitor';

		$projectSuiteCssVersion = '?v=' . @filemtime(APPPATH . 'Modules/Common/Assets/css/project-suite.css');
		$projectSuiteJsVersion = '?v=' . @filemtime(APPPATH . 'Modules/Common/Assets/js/project-suite.js');
		$forexSuiteCssVersion = '?v=' . @filemtime(APPPATH . 'Modules/ForexMonitor/Assets/css/forex-suite.css');
		$monitorCssVersion = '?v=' . @filemtime(APPPATH . 'Modules/ForexMonitor/Assets/css/forex-monitor.css');
		$monitorJsVersion = '?v=' . @filemtime(APPPATH . 'Modules/ForexMonitor/Assets/js/forex-monitor.js');

		/**
		 * Asset monitor dirangkai lokal module agar refaktor dashboard lama tidak
		 * mengubah bundle global dan tetap aman bagi halaman lain di aplikasi.
		 */
		$this->addStyle($this->commonAsset('css/project-suite.css') . $projectSuiteCssVersion);
		$this->addStyle($this->moduleAsset('css/forex-suite.css') . $forexSuiteCssVersion);
		$this->addStyle($this->moduleAsset('css/forex-monitor.css') . $monitorCssVersion);
		$this->addJs($this->commonAsset('js/project-suite.js') . $projectSuiteJsVersion);
		$this->addJs($this->config->baseURL . 'public/vendors/apexcharts/dist/apexcharts.min.js');
		$this->addJs($this->moduleAsset('js/forex-monitor.js') . $monitorJsVersion);
	}

	public function index()
	{
		$this->hasPermission('read_all', true);

		$filters = $this->model->getDateRangeFilters();
		$timeframe = strtoupper(trim((string) ($this->request->getGet('timeframe') ?: '1D')));
		$userId = (int) ($this->session->get('user')['id_user'] ?? 0);
		$granularity = trim((string) ($this->request->getGet('granularity') ?: ''));
		$dashboardPayload = $this->marketDataService->getDashboardPayload($timeframe, false, $userId, $granularity);
		$editId = (int) ($this->request->getGet('edit') ?? 0);

		$data = $this->data;
		$data['title'] = 'Forex Monitor';
		$data['pair'] = $this->model->getPair();
		$data['filters'] = $filters;
		$data['default_timeframe'] = $dashboardPayload['timeframe'] ?? '1D';
		$data['dashboard_payload'] = $dashboardPayload;
		$data['latest_snapshot'] = $this->model->getLatestSnapshot();
		$data['monitor_metrics'] = $this->model->getMonitorMetrics($filters);
		$data['price_rows'] = $this->model->getPriceHistory($filters);
		$data['active_alerts'] = $this->model->getAlertList($userId);
		$data['alert_history'] = $this->model->getAlertHistory($userId, 120);
		$data['edit_alert'] = $editId > 0 ? $this->model->getAlertById($editId, $userId) : [];
		$data['form_data'] = session()->getFlashdata('form_data') ?: $data['edit_alert'];

		$this->view('forex_monitor/index.php', $data);
	}

	/**
	 * Endpoint polling mengembalikan snapshot lengkap agar frontend cukup
	 * melakukan satu request untuk update quote, chart, dan trigger alert.
	 */
	public function snapshot()
	{
		$this->hasPermission('read_all', true);

		$timeframe = (string) ($this->request->getGet('timeframe') ?: '1D');
		$forceRefresh = $this->request->getGet('force') === '1';
		$granularity = trim((string) ($this->request->getGet('granularity') ?: ''));
		$userId = (int) ($this->session->get('user')['id_user'] ?? 0);

		return $this->response->setJSON($this->marketDataService->getDashboardPayload($timeframe, $forceRefresh, $userId, $granularity));
	}

	/**
	 * Fetch manual dipisah dari index agar submit form tidak men-trigger ulang
	 * render list sebelum proses sinkronisasi data GBP/JPY selesai dijalankan.
	 */
	public function fetch()
	{
		$this->hasPermission('create', true);

		if (!$this->request->getPost('submit')) {
			return redirect()->to($this->moduleURL);
		}

		$requestedDate = (string) $this->request->getPost('date');
		$forceRefresh = $this->request->getPost('force_refresh') === '1';
		$result = $this->forexService->syncDailyData($requestedDate, $forceRefresh);

		session()->setFlashdata('message', [
			'status' => $result['status'],
			'message' => $result['message'],
		]);

		$query = http_build_query([
			'date_from' => $this->request->getPost('date_from') ?: null,
			'date_to' => $this->request->getPost('date_to') ?: null,
			'timeframe' => $this->request->getPost('timeframe') ?: null,
		]);

		return redirect()->to($this->moduleURL . ($query !== '' ? '?' . $query : ''));
	}

	/**
	 * Simpan alert memakai flashdata agar validasi tetap sederhana dan user
	 * dapat langsung kembali ke halaman monitor tanpa endpoint modal tambahan.
	 */
	public function saveAlert()
	{
		$id = (int) ($this->request->getPost('id') ?? 0);
		if ($id > 0) {
			$this->hasPermission('update_all', true);
		} else {
			$this->hasPermission('create', true);
		}

		$userId = (int) ($this->session->get('user')['id_user'] ?? 0);
		$result = $this->model->saveAlertData($userId);

		if (($result['status'] ?? '') !== 'ok') {
			session()->setFlashdata('form_data', $this->request->getPost());
		}

		session()->setFlashdata('message', $result);
		$redirectUrl = $this->moduleURL;
		if (($result['status'] ?? '') !== 'ok' && $id > 0) {
			$redirectUrl .= '?edit=' . $id;
		}

		return redirect()->to($redirectUrl);
	}

	/**
	 * Toggle dipisahkan dari save agar operator dapat mengaktifkan ulang alert
	 * dengan satu klik tanpa mengubah target harga yang sudah tersimpan.
	 */
	public function toggleAlert()
	{
		$this->hasPermission('update_all', true);

		$userId = (int) ($this->session->get('user')['id_user'] ?? 0);
		$alertId = (int) ($this->request->getPost('id_forex_alert') ?? 0);
		session()->setFlashdata('message', $this->model->toggleAlertStatus($alertId, $userId));

		return redirect()->to($this->moduleURL);
	}

	/**
	 * Hapus alert hanya memengaruhi master alert aktif karena histori trigger
	 * sudah dipisahkan pada tabel sendiri untuk kebutuhan audit sederhana.
	 */
	public function deleteAlert()
	{
		$this->hasPermission('delete_all', true);

		$userId = (int) ($this->session->get('user')['id_user'] ?? 0);
		$alertId = (int) ($this->request->getPost('id_forex_alert') ?? 0);
		session()->setFlashdata('message', $this->model->deleteAlert($alertId, $userId));

		return redirect()->to($this->moduleURL);
	}
}
