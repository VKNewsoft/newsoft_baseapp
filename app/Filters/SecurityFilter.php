<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\SecurityService;
use CodeIgniter\HTTP\Exceptions\HTTPException;
// use function App\Helpers\detect_attack;

class SecurityFilter implements FilterInterface
{
	public function before(RequestInterface $request, $arguments = null)
	{
		$uri = $request->uri->getPath();
		
		if (strpos($uri, 'installer') !== false) {
			return null;
		}

		helper(['security', 'app_runtime']);

		// Filter keamanan tidak lagi membuka koneksi validasi database sendiri.
		// Status runtime dipakai dari cache agar overhead keamanan tetap ringan.
		if (!app_database_runtime_status()) {
			return null;
		}
		
		$allowedPaths = [
			'public/',
			'module-assets/',
		];

		foreach ($allowedPaths as $path) {
			if (strpos($uri, $path) === 0) {
				return null;
			}
		}

		$security = new SecurityService();

		if ($security->isBlocked()) {
			throw new HTTPException('Access Denied', 403);
		}

		if ($attack = detect_attack($request)) {
			$security->logAttack($attack);
			throw new HTTPException('Access Denied', 403);
		}

		if (!$security->incrementRequest()) {
			throw new HTTPException('Access Denied', 403);
		}

		return null;
	}

	public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
	{
		// Tidak perlu action setelah response
	}
}
