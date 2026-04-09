<?php namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

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
       // Skip jika sedang di installer mode
       $uri = $request->getUri()->getPath();
       if (strpos($uri, 'installer') !== false) {
           return;
       }
       
       // Check if database is configured
       if (!$this->isDatabaseConfigured()) {
           return; // Bypass bootstrap if no database
       }
       
	   $config = config('App');
	   
	   helper('csrf');
	   
		// Custom CSRF
		if ($config->csrf['enable']) 
		{
			if ($config->csrf['auto_check']) {
				$message = csrf_validation();
				if ($message) {
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
   
   /**
    * Check if database is configured
    */
   private function isDatabaseConfigured(): bool
   {
       try {
           $configFile = APPPATH . 'Config/Database.php';
           if (!file_exists($configFile)) {
               return false;
           }
           
           $content = file_get_contents($configFile);
           preg_match("/'database'\s*=>\s*'([^']+)'/", $content, $db);
           $database = $db[1] ?? '';
           
           if (empty($database)) {
               return false;
           }
           
           // Quick check if database exists
           preg_match("/'hostname'\s*=>\s*'([^']+)'/", $content, $host);
           preg_match("/'username'\s*=>\s*'([^']+)'/", $content, $user);
           preg_match("/'password'\s*=>\s*'([^']+)'/", $content, $pass);
           preg_match("/'port'\s*=>\s*(\d+)/", $content, $port);
           
           $conn = @new \mysqli(
               $host[1] ?? 'localhost',
               $user[1] ?? 'root',
               $pass[1] ?? '',
               $database,
               (int)($port[1] ?? 3306)
           );
           
           if ($conn->connect_error) {
               return false;
           }
           
           $conn->close();
           return true;
           
       } catch (\Exception $e) {
           return false;
       }
   }
   
}
