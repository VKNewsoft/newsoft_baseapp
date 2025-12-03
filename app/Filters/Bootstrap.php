<?php namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Bootstrap implements FilterInterface
{
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
		$controller  = $router->controllerName();

		$exp  = explode('\\', $controller);

		$nama_module =  'welcome';		
		foreach ($exp as $key => $val) {
			if (!$val || strtolower($val) == 'app' || strtolower($val) == 'controllers')
				unset($exp[$key]);
		}
		
		// Dash tidak valid untuk nama class, sehingga jika ada dash di url maka otomatis akan diubah menjadi underscore, hal tersebut berpengaruh ke nama controller
		$nama_module = str_replace('_', '-', strtolower(join('/', $exp)));
		$module_url = $config->baseURL . $nama_module;
		
		session()->set('web', ['module_url' => $module_url, 'nama_module' => $nama_module, 'method_name' => $router->methodName()]);
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