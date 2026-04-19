<?php namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\SecurityService;

class Bootstrap implements FilterInterface
{
	private function resolveModuleData(RequestInterface $request, string $baseURL): array
	{
		$path = trim($request->getUri()->getPath(), '/');
		$segments = $path === '' ? [] : explode('/', $path);

		if (!$segments) {
			return [
				'nama_module' => 'login',
				'module_url' => rtrim($baseURL, '/') . '/login',
			];
		}

		$moduleName = $segments[0];
		if ($moduleName === 'builtin' && !empty($segments[1])) {
			$moduleName = 'builtin/' . $segments[1];
		}

		$aliases = [
			'security-monitor' => 'securitymonitor',
			'builtin/wilayah'  => 'wilayah',
		];

		if (isset($aliases[$moduleName])) {
			$moduleName = $aliases[$moduleName];
		}

		return [
			'nama_module' => $moduleName,
			'module_url' => rtrim($baseURL, '/') . '/' . $moduleName,
		];
	}

	public function before(RequestInterface $request, $arguments = null)
	{
		$uri = $request->getUri()->getPath();
		if (strpos($uri, 'installer') !== false || strpos($uri, 'module-assets/') !== false) {
			return;
		}

		helper(['csrf', 'app_runtime']);

		// Bootstrap cukup memakai status runtime yang sudah di-cache supaya
		// filter global tidak menambah koneksi database berulang.
		if (!app_database_runtime_status()) {
			return;
		}
		
		$config = config('App');
		
		// Custom CSRF
		if ($config->csrf['enable']) 
		{
			if ($config->csrf['auto_check']) {
				$message = csrf_validation();
				if ($message) {
					try {
						$security = new SecurityService();
						$security->logCsrfAttempt([
							'reason' => $message['message'] ?? 'CSRF validation failed',
							'source' => 'bootstrap_csrf',
						]);
					} catch (\Throwable $e) {
						log_message('error', 'Failed to log CSRF attempt: {message}', ['message' => $e->getMessage()]);
					}
					echo view('app_error.php', ['content' => $message['message']]);
					exit;
				}
			}
			
			if ($config->csrf['auto_settoken']) {
				csrf_settoken();
			}
		}
		
		$router = service('router');
		$moduleData = $this->resolveModuleData($request, $config->baseURL);
		
		session()->set('web', [
			'module_url' => $moduleData['module_url'],
			'nama_module' => $moduleData['nama_module'],
			'method_name' => $router->methodName()
		]);
	}
	
	public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
	{
		
	}
}
