<?php
/**
 * Route Task Management diletakkan di module sendiri agar mudah dirawat dan tidak mengganggu route global.
 */

$routes->add('task-management', '\App\Modules\TaskManagement\Controllers\TaskManagement::index');
$routes->add('task-management/(:segment)', '\App\Modules\TaskManagement\Controllers\TaskManagement::$1');
$routes->add('task-management/(:segment)/(:any)', '\App\Modules\TaskManagement\Controllers\TaskManagement::$1/$2');
