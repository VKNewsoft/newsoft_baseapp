<?php

namespace App\Modules\Common\Controllers;

use CodeIgniter\Controller;

class Asset extends Controller
{
	public function index($moduleName, ...$assetPathSegments)
	{
		$moduleName = preg_replace('/[^A-Za-z0-9_]/', '', (string) $moduleName);
		$assetPath = implode('/', array_filter($assetPathSegments, static function($segment) {
			return $segment !== null && $segment !== '';
		}));
		$assetPath = str_replace(['..\\', '../'], '', (string) $assetPath);
		$assetPath = ltrim(str_replace('\\', '/', $assetPath), '/');

		if ($moduleName === '' || $assetPath === '') {
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}

		$basePath = realpath(APPPATH . 'Modules/' . $moduleName . '/Assets');
		if (!$basePath) {
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}

		$filePath = realpath($basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $assetPath));
		if (!$filePath || strpos($filePath, $basePath) !== 0 || !is_file($filePath)) {
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}

		$extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
		$mimeMap = [
			'css' => 'text/css',
			'js' => 'application/javascript',
			'json' => 'application/json',
			'png' => 'image/png',
			'jpg' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'gif' => 'image/gif',
			'svg' => 'image/svg+xml',
			'webp' => 'image/webp',
			'woff' => 'font/woff',
			'woff2' => 'font/woff2',
			'ttf' => 'font/ttf',
			'eot' => 'application/vnd.ms-fontobject'
		];

		$mimeType = $mimeMap[$extension] ?? null;
		if (!$mimeType && function_exists('mime_content_type')) {
			$mimeType = mime_content_type($filePath) ?: null;
		}
		if (!$mimeType) {
			$mimeType = 'application/octet-stream';
		}

		/**
		 * Asset yang sudah memiliki query versi dibuat lebih agresif untuk cache
		 * agar perpindahan halaman tidak memicu unduhan ulang font/CSS/JS.
		 */
		$assetVersion = service('request')->getGet('v') ?: service('request')->getGet('r');
		$cacheControl = $assetVersion ? 'public, max-age=31536000, immutable' : 'public, max-age=86400';

		// Header bawaan PHP/Apache yang bersifat no-cache dibersihkan lebih dulu
		// agar asset versioned benar-benar dapat memakai cache browser publik.
		header_remove('Cache-Control');
		header_remove('Pragma');
		header_remove('Expires');

		$response = service('response');
		$response->removeHeader('Cache-Control')
			->removeHeader('Pragma')
			->removeHeader('Expires');

		// Native header replace dipakai sebagai lapisan akhir agar value cache
		// tidak tergabung lagi dengan header no-cache bawaan environment.
		header('Cache-Control: ' . $cacheControl, true);

		return $response
			->setHeader('Content-Type', $mimeType)
			->setBody(file_get_contents($filePath));
	}
}
