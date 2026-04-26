<?php
/**
 * Controller Forex Prediction menampilkan signal aktif, market context,
 * prediksi multi-metode, dan histori analisis GBP/JPY yang terpisah.
 */

namespace App\Modules\ForexPrediction\Controllers;

use App\Modules\ForexMonitor\Libraries\ForexMarketDataService;
use App\Modules\ForexPrediction\Libraries\ForexPredictionService;
use App\Modules\ForexPrediction\Libraries\ForexSignalService;
use App\Modules\ForexPrediction\Models\ForexPredictionModel;

class ForexPrediction extends \App\Modules\Common\Controllers\BaseController
{
	protected $model;
	protected $predictionService;
	protected $signalService;
	protected $marketDataService;

	public function __construct()
	{
		parent::__construct();
		helper(['html', 'form']);

		$this->model = new ForexPredictionModel();
		$this->predictionService = new ForexPredictionService($this->model);
		$this->signalService = new ForexSignalService($this->model);
		$this->marketDataService = new ForexMarketDataService($this->model);
		$this->data['site_title'] = 'Forex Prediction';

		$projectSuiteCssVersion = '?v=' . @filemtime(APPPATH . 'Modules/Common/Assets/css/project-suite.css');
		$projectSuiteJsVersion = '?v=' . @filemtime(APPPATH . 'Modules/Common/Assets/js/project-suite.js');
		$forexSuiteCssVersion = '?v=' . @filemtime(APPPATH . 'Modules/ForexMonitor/Assets/css/forex-suite.css');
		$monitorCssVersion = '?v=' . @filemtime(APPPATH . 'Modules/ForexMonitor/Assets/css/forex-monitor.css');

		/**
		 * Asset monitor dipakai ulang agar kartu signal, context, dan histori
		 * prediction tetap satu bahasa desain dengan monitor realtime.
		 */
		$this->addStyle($this->commonAsset('css/project-suite.css') . $projectSuiteCssVersion);
		$this->addStyle($this->moduleAsset('css/forex-suite.css', 'ForexMonitor') . $forexSuiteCssVersion);
		$this->addStyle($this->moduleAsset('css/forex-monitor.css', 'ForexMonitor') . $monitorCssVersion);
		$this->addJs($this->commonAsset('js/project-suite.js') . $projectSuiteJsVersion);
	}

	public function index()
	{
		$this->hasPermission('read_all', true);

		$filters = $this->model->getDateRangeFilters();
		$userId = (int) ($this->session->get('user')['id_user'] ?? 0);
		$livePrice = $this->marketDataService->syncLivePrice(false);
		$chartPayload = $this->marketDataService->getChartPayload('1D', false);
		$signalPayload = $this->signalService->getSignalPayload($livePrice, $chartPayload, $userId);

		$data = $this->data;
		$data['title'] = 'Forex Prediction';
		$data['pair'] = $this->model->getPair();
		$data['filters'] = $filters;
		$data['latest_snapshot'] = $this->model->getLatestSnapshot($filters['date_to']);
		$data['prediction_result'] = $this->predictionService->getLatestPrediction($filters['date_to']);
		$data['signal_payload'] = $signalPayload;
		$data['live_price'] = $livePrice;
		$data['report_metrics'] = $this->model->getReportMetrics($filters);
		$data['report_rows'] = $this->model->getAnalysisHistory($filters);

		$this->view('forex_prediction/index.php', $data);
	}

	/**
	 * Mode auto-monitor dipisah di prediction karena setting ini mengikuti
	 * rule signal dan context, bukan target threshold manual milik monitor.
	 */
	public function saveAutoMonitor()
	{
		$this->hasPermission('update_all', true);

		$userId = (int) ($this->session->get('user')['id_user'] ?? 0);
		$enabled = $this->request->getPost('auto_monitor') === '1';
		session()->setFlashdata('message', $this->signalService->saveAutoMonitorSetting($userId, $enabled));

		return redirect()->to($this->moduleURL);
	}
}
