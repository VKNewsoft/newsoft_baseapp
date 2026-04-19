<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;

/*
 * --------------------------------------------------------------------
 * Application Events
 * --------------------------------------------------------------------
 * Events allow you to tap into the execution of the program without
 * modifying or extending core files. This file provides a central
 * location to define your events, though they can always be added
 * at run-time, also, if needed.
 *
 * You create code that can execute by subscribing to events with
 * the 'on()' method. This accepts any form of callable, including
 * Closures, that will be executed when the event is triggered.
 *
 * Example:
 *      Events::on('create', [$myInstance, 'myMethod']);
 */

Events::on('pre_system', static function () {
	if (ENVIRONMENT !== 'testing') {
		if (ini_get('zlib.output_compression')) {
			throw FrameworkException::forEnabledZlibOutputCompression();
		}

		// Cache limiter bawaan session PHP dimatikan agar asset berversi dapat
		// memakai header cache publik tanpa ditimpa `no-store`/`no-cache`.
		ini_set('session.cache_limiter', '');
		session_cache_limiter('');

		while (ob_get_level() > 0) {
			ob_end_flush();
		}

		$acceptEncoding = strtolower((string) ($_SERVER['HTTP_ACCEPT_ENCODING'] ?? ''));

		// Kompresi gzip diaktifkan hanya saat browser mendukungnya agar ukuran
		// HTML, JSON, CSS, dan JS turun tanpa mengubah logic response aplikasi.
		if (!is_cli() && strpos($acceptEncoding, 'gzip') !== false && function_exists('ob_gzhandler')) {
			header('Vary: Accept-Encoding');
			ob_start('ob_gzhandler');
		} else {
			ob_start(static fn ($buffer) => $buffer);
		}
	}

    /*
     * --------------------------------------------------------------------
     * Debug Toolbar Listeners.
     * --------------------------------------------------------------------
     * If you delete, they will no longer be collected.
     */
    if (CI_DEBUG && ! is_cli()) {
        Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
        Services::toolbar()->respond();
    }
});
