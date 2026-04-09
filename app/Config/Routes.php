<?php namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php'))
{
	require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('');

// Installer routes (bypass filter)
$routes->group('installer', ['filter' => null], function($routes) {
    $routes->get('/', '\App\Modules\Installer\Controllers\Installer::index');
    $routes->post('install', '\App\Modules\Installer\Controllers\Installer::install');
    $routes->get('success', '\App\Modules\Installer\Controllers\Installer::success');
});

$moduleControllers = [
	'login' => '\App\Modules\Login\Controllers\Login',
	'welcome' => '\App\Modules\Welcome\Controllers\Welcome',
	'dashboard' => '\App\Modules\Dashboard\Controllers\Dashboard',
	'company' => '\App\Modules\Company\Controllers\Company',
	'filepicker' => '\App\Modules\Filepicker\Controllers\Filepicker',
	'identitas' => '\App\Modules\Identitas\Controllers\Identitas',
	'midtrans' => '\App\Modules\Midtrans\Controllers\Midtrans',
	'recovery' => '\App\Modules\Recovery\Controllers\Recovery',
	'register' => '\App\Modules\Register\Controllers\Register',
	'securitymonitor' => '\App\Modules\SecurityMonitor\Controllers\SecurityMonitor',
	'builtin/menu' => '\App\Modules\Builtin\Controllers\Menu',
	'builtin/menu-role' => '\App\Modules\Builtin\Controllers\Menu_role',
	'builtin/module' => '\App\Modules\Builtin\Controllers\Module',
	'builtin/module-role' => '\App\Modules\Builtin\Controllers\Module_role',
	'builtin/permission' => '\App\Modules\Builtin\Controllers\Permission',
	'builtin/qrscan' => '\App\Modules\Builtin\Controllers\Qrscan',
	'builtin/role' => '\App\Modules\Builtin\Controllers\Role',
	'builtin/role-permission' => '\App\Modules\Builtin\Controllers\Role_permission',
	'builtin/setting-app' => '\App\Modules\Builtin\Controllers\Setting_app',
	'builtin/setting-layout' => '\App\Modules\Builtin\Controllers\Setting_layout',
	'builtin/setting-registrasi' => '\App\Modules\Builtin\Controllers\Setting_registrasi',
	'builtin/user' => '\App\Modules\Builtin\Controllers\User',
	'builtin/user-role' => '\App\Modules\Builtin\Controllers\User_role',
	'wilayah' => '\App\Modules\Builtin\Controllers\Wilayah',
];

$routes->get('/', '\App\Modules\Login\Controllers\Login::index');

foreach ($moduleControllers as $uri => $controller) {
	$routes->add($uri, $controller . '::index');
	$routes->add($uri . '/(:segment)', $controller . '::$1');
	$routes->add($uri . '/(:segment)/(:any)', $controller . '::$1/$2');
	$routes->add($uri . '/(:segment)/(:any)/(:any)', $controller . '::$1/$2/$3');
	$routes->add($uri . '/(:segment)/(:any)/(:any)/(:any)', $controller . '::$1/$2/$3/$4');
	$routes->add($uri . '/(:segment)/(:any)/(:any)/(:any)/(:any)', $controller . '::$1/$2/$3/$4/$5');
}

$routes->add('security-monitor', '\App\Modules\SecurityMonitor\Controllers\SecurityMonitor::index');
$routes->add('security-monitor/(:segment)', '\App\Modules\SecurityMonitor\Controllers\SecurityMonitor::$1');
$routes->add('security-monitor/(:segment)/(:any)', '\App\Modules\SecurityMonitor\Controllers\SecurityMonitor::$1/$2');
$routes->add('security-monitor/(:segment)/(:any)/(:any)', '\App\Modules\SecurityMonitor\Controllers\SecurityMonitor::$1/$2/$3');

$routes->add('builtin/wilayah', '\App\Modules\Builtin\Controllers\Wilayah::index');
$routes->add('builtin/wilayah/(:segment)', '\App\Modules\Builtin\Controllers\Wilayah::$1');
$routes->add('builtin/wilayah/(:segment)/(:any)', '\App\Modules\Builtin\Controllers\Wilayah::$1/$2');
$routes->add('builtin/wilayah/(:segment)/(:any)/(:any)', '\App\Modules\Builtin\Controllers\Wilayah::$1/$2/$3');

$routes->setDefaultController('\App\Modules\Login\Controllers\Login');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(true);
$routes->set404Override();
$routes->setAutoRoute(false);

/**
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
/* $routes->get('/', 'Home::index');
$routes->setTranslateURIDashes(true);
 */
 
/**
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
foreach (glob(APPPATH . 'Modules/*/Config/Routes.php') ?: [] as $moduleRoutes) {
	require $moduleRoutes;
}

if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php'))
{
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
