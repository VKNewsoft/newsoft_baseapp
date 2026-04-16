<?php
/**
 * Route module DB Synchronisation.
 *
 * Route dipisah di level module supaya modul ini tetap self-contained
 * mengikuti pola HMVC project.
 */

$routes->add('db-synchronisation', '\App\Modules\DbSynchronisation\Controllers\DbSynchronisation::index');
$routes->add('db-synchronisation/(:segment)', '\App\Modules\DbSynchronisation\Controllers\DbSynchronisation::$1');
