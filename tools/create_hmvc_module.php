<?php

/**
 * HMVC Module Generator
 *
 * Usage:
 *   php tools/create_hmvc_module.php NamaModul [route-segment]
 *
 * Example:
 *   php tools/create_hmvc_module.php Produk produk
 */

if (PHP_SAPI !== 'cli') {
	exit("Script ini hanya bisa dijalankan dari CLI.\n");
}

$moduleName = $argv[1] ?? '';
$routeSegment = $argv[2] ?? '';

if ($moduleName === '') {
	exit("Usage: php tools/create_hmvc_module.php NamaModul [route-segment]\n");
}

if (!preg_match('/^[A-Z][A-Za-z0-9_]*$/', $moduleName)) {
	exit("Nama modul harus diawali huruf besar dan hanya boleh berisi huruf, angka, atau underscore.\n");
}

$routeSegment = $routeSegment !== ''
	? strtolower(trim($routeSegment))
	: strtolower(trim(preg_replace('/([a-z])([A-Z])/', '$1-$2', $moduleName)));

if (!preg_match('/^[a-z0-9\-]+$/', $routeSegment)) {
	exit("Route segment hanya boleh berisi huruf kecil, angka, atau dash.\n");
}

$rootPath = dirname(__DIR__);
$modulePath = $rootPath . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . $moduleName;

if (is_dir($modulePath)) {
	exit("Modul {$moduleName} sudah ada.\n");
}

$paths = [
	$modulePath . DIRECTORY_SEPARATOR . 'Config',
	$modulePath . DIRECTORY_SEPARATOR . 'Controllers',
	$modulePath . DIRECTORY_SEPARATOR . 'Models',
	$modulePath . DIRECTORY_SEPARATOR . 'Views',
	$modulePath . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'themes',
	$modulePath . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'modern',
];

foreach ($paths as $path) {
	if (!is_dir($path) && !mkdir($path, 0777, true) && !is_dir($path)) {
		exit("Gagal membuat folder: {$path}\n");
	}
}

$controllerPath = $modulePath . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . $moduleName . '.php';
$modelPath = $modulePath . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . $moduleName . 'Model.php';
$viewPath = $modulePath . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'modern' . DIRECTORY_SEPARATOR . $routeSegment . '.php';
$routesPath = $modulePath . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Routes.php';

$controllerContent = <<<PHP
<?php
/**
 * @author VKNewsoft - Newsoft Developer
 * @year 2026
 */

namespace App\\Modules\\{$moduleName}\\Controllers;

use App\\Modules\\{$moduleName}\\Models\\{$moduleName}Model;

class {$moduleName} extends \\App\\Modules\\Common\\Controllers\\BaseController
{
	protected \$model;

	public function __construct()
	{
		parent::__construct();
		\$this->model = new {$moduleName}Model;
		\$this->data['site_title'] = '{$moduleName}';
	}

	public function index()
	{
		\$this->data['title'] = '{$moduleName}';
		\$this->data['content'] = \$this->model->getPageData();
		\$this->view('{$routeSegment}.php', \$this->data);
	}
}
PHP;

$modelContent = <<<PHP
<?php
/**
 * @author VKNewsoft - Newsoft Developer
 * @year 2026
 */

namespace App\\Modules\\{$moduleName}\\Models;

class {$moduleName}Model extends \\App\\Modules\\Common\\Models\\BaseModel
{
	public function getPageData()
	{
		return [
			'message' => 'Modul {$moduleName} siap digunakan'
		];
	}
}
PHP;

$viewContent = <<<PHP
<div class="card">
	<div class="card-header">
		<h5 class="mb-0"><?= \$title ?></h5>
	</div>
	<div class="card-body">
		<p class="mb-0"><?= esc(\$content['message'] ?? '') ?></p>
	</div>
</div>
PHP;

$routesContent = <<<PHP
<?php

/**
 * Route modul {$moduleName}
 */

\$routes->group('{$routeSegment}', static function(\$routes) {
	\$routes->add('/', '\\App\\Modules\\{$moduleName}\\Controllers\\{$moduleName}::index');
	\$routes->add('(:segment)', '\\App\\Modules\\{$moduleName}\\Controllers\\{$moduleName}::\$1');
	\$routes->add('(:segment)/(:any)', '\\App\\Modules\\{$moduleName}\\Controllers\\{$moduleName}::\$1/\$2');
	\$routes->add('(:segment)/(:any)/(:any)', '\\App\\Modules\\{$moduleName}\\Controllers\\{$moduleName}::\$1/\$2/\$3');
});
PHP;

file_put_contents($controllerPath, $controllerContent);
file_put_contents($modelPath, $modelContent);
file_put_contents($viewPath, $viewContent);
file_put_contents($routesPath, $routesContent);

echo "Modul {$moduleName} berhasil dibuat di {$modulePath}\n";
echo "Route default: /{$routeSegment}\n";
