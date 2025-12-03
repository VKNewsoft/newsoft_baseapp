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
$routes->setDefaultNamespace('App\Controllers');

// Installer routes (bypass filter)
$routes->group('installer', ['filter' => null], function($routes) {
    $routes->get('/', 'Installer::index');
    $routes->post('install', 'Installer::install');
    $routes->get('success', 'Installer::success');
});

$routes->get('/', 'Login::index');
$routes->setDefaultController('Login');
$routes->get('hrm/crontarik-absen/([0-9]{8})', 'Hrm\Crontarik_absen::index/$1');
$routes->get('hrm/crontarik-absen', 'Hrm\Crontarik_absen::index'); // tanpa parameter
$routes->get('hrm/crontarik-fid/([0-9]{8})/(:any)', 'Hrm\Crontarik_fid::index/$1/$2'); // dengan parameter tambahan
$routes->get('hrm/crontarik-fid/([0-9]{8})', 'Hrm\Crontarik_fid::index/$1');
$routes->get('hrm/crontarik-fid', 'Hrm\Crontarik_fid::index'); // tanpa parameter
$routes->get('hrm/compress-images', 'Hrm\Compress_image::compressLeavePictures');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(true);
$routes->set404Override();
$routes->setAutoRoute(true);

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
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php'))
{
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
