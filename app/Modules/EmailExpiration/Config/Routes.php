<?php
/**
 * Route module Subscription Manager
 *
 * Route dipisah per module agar struktur HMVC tetap self-contained
 * dan URL dash-case tetap mengarah ke controller module yang benar.
 */

$routes->add('email-expiration', '\App\Modules\EmailExpiration\Controllers\EmailExpiration::index');
$routes->add('email-expiration/(:segment)', '\App\Modules\EmailExpiration\Controllers\EmailExpiration::$1');
$routes->add('email-expiration/(:segment)/(:any)', '\App\Modules\EmailExpiration\Controllers\EmailExpiration::$1/$2');
