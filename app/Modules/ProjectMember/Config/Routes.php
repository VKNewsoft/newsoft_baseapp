<?php
/**
 * Route Project Member dipisahkan agar halaman anggota project bisa diakses dari action module lain.
 */

$routes->add('project-member', '\App\Modules\ProjectMember\Controllers\ProjectMember::index');
$routes->add('project-member/(:segment)', '\App\Modules\ProjectMember\Controllers\ProjectMember::$1');
$routes->add('project-member/(:segment)/(:any)', '\App\Modules\ProjectMember\Controllers\ProjectMember::$1/$2');
