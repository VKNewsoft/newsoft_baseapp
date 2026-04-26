<?php
error_reporting(-1);
ini_set('display_errors', '1');
define('FCPATH', __DIR__ . '/../public/');
chdir(FCPATH);
require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once SYSTEMPATH . 'Config/DotEnv.php';
(new CodeIgniter\Config\DotEnv(ROOTPATH))->load();
$app = Config\Services::codeigniter();
$app->initialize();

$service = new App\Modules\ForexDashboard\Libraries\ForexMarketDataService();
$payload = $service->getDashboardPayload('1D', false, 1);
$result = [
	'timeframe' => $payload['chart']['timeframe'] ?? null,
	'axis' => $payload['chart']['meta']['axis'] ?? [],
	'toggle_count' => count($payload['chart']['indicators']['toggles'] ?? []),
	'series' => array_keys($payload['chart']['series'] ?? []),
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
