<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
$root = dirname(__DIR__);
define('FCPATH', $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
chdir(FCPATH);
require $root . DIRECTORY_SEPARATOR . 'app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once SYSTEMPATH . 'CodeIgniter.php';
$app = Config\Services::codeigniter();
$app->initialize();
$app->setContext('php-cli');
$service = new App\Modules\ForexDashboard\Libraries\ForexMarketDataService();
$payload = $service->getDashboardPayload('1D', false, 1);
echo json_encode([
	'current_signal' => $payload['trading_signal']['current_signal'] ?? '-',
	'label' => $payload['trading_signal']['signal_label'] ?? '-',
	'confidence' => $payload['trading_signal']['confidence'] ?? '-',
	'rsi' => $payload['trading_signal']['indicators']['rsi'] ?? 0,
	'bb_lower' => $payload['trading_signal']['indicators']['bollinger']['lower'] ?? 0,
	'bb_upper' => $payload['trading_signal']['indicators']['bollinger']['upper'] ?? 0,
	'fib_0382' => $payload['trading_signal']['indicators']['fibonacci']['0.382'] ?? 0,
	'auto_monitor' => $payload['trading_signal']['auto_monitor']['enabled'] ?? false,
	'notes' => $payload['trading_signal']['notes'] ?? [],
	'triggered_alerts' => isset($payload['triggered_alerts']) ? count($payload['triggered_alerts']) : 0
], JSON_UNESCAPED_SLASHES);
