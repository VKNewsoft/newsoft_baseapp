<?php
/**
 * Route Project Category dipisahkan agar integrasi module tidak perlu mengubah route global.
 */

$routes->add('project-category', '\App\Modules\ProjectCategory\Controllers\ProjectCategory::index');
$routes->add('project-category/(:segment)', '\App\Modules\ProjectCategory\Controllers\ProjectCategory::$1');
$routes->add('project-category/(:segment)/(:any)', '\App\Modules\ProjectCategory\Controllers\ProjectCategory::$1/$2');
