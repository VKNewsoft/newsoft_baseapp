<?php
/**
 * Route module Forex Monitor dipisahkan agar monitoring realtime, fetch, dan
 * pengelolaan alert manual tetap terisolasi dari prediction module.
 */

$routes->add('forex-monitor', '\App\Modules\ForexMonitor\Controllers\ForexMonitor::index');
$routes->add('forex-monitor/snapshot', '\App\Modules\ForexMonitor\Controllers\ForexMonitor::snapshot');
$routes->add('forex-monitor/fetch', '\App\Modules\ForexMonitor\Controllers\ForexMonitor::fetch');
$routes->add('forex-monitor/save-alert', '\App\Modules\ForexMonitor\Controllers\ForexMonitor::saveAlert');
$routes->add('forex-monitor/toggle-alert', '\App\Modules\ForexMonitor\Controllers\ForexMonitor::toggleAlert');
$routes->add('forex-monitor/delete-alert', '\App\Modules\ForexMonitor\Controllers\ForexMonitor::deleteAlert');
$routes->add('forex-monitor/(:segment)', '\App\Modules\ForexMonitor\Controllers\ForexMonitor::$1');
$routes->add('forex-monitor/(:segment)/(:any)', '\App\Modules\ForexMonitor\Controllers\ForexMonitor::$1/$2');
