<?php
/**
 * Route Project dipisahkan di level module agar modul tetap mandiri mengikuti pola HMVC.
 */

$routes->add('project', '\App\Modules\Project\Controllers\Project::index');
$routes->add('project/(:segment)', '\App\Modules\Project\Controllers\Project::$1');
$routes->add('project/(:segment)/(:any)', '\App\Modules\Project\Controllers\Project::$1/$2');
