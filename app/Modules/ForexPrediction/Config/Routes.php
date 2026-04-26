<?php
/**
 * Route module Forex Prediction dipisahkan agar analisis signal dan metode
 * prediksi tidak lagi bercampur dengan route monitor realtime.
 */

$routes->add('forex-prediction', '\App\Modules\ForexPrediction\Controllers\ForexPrediction::index');
$routes->add('forex-prediction/save-auto-monitor', '\App\Modules\ForexPrediction\Controllers\ForexPrediction::saveAutoMonitor');
$routes->add('forex-prediction/(:segment)', '\App\Modules\ForexPrediction\Controllers\ForexPrediction::$1');
$routes->add('forex-prediction/(:segment)/(:any)', '\App\Modules\ForexPrediction\Controllers\ForexPrediction::$1/$2');
