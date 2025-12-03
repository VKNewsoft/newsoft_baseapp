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
        // print_r($uri);die;
    // 🔽 Kecualikan path yang aman
    $allowedPaths = [

        'public/',
    ];

    foreach ($allowedPaths as $path) {
        if (strpos($uri, $path) === 0) {
            return null; // lewati filter
        }
    }
          helper('security'); // ini akan load app/Helpers/security_helper.php
        $security = new SecurityService();

        // 1. Cek apakah IP diblokir
        if ($security->isBlocked()) {
            // return service('response')
            //     ->setStatusCode(403)
            //     ->setBody('Access Denied: Your IP has been blocked due to suspicious activity.')
            //     ->setHeader('Content-Type', 'text/plain');
            throw new HTTPException('Access Denied', 403);
        }

        // 2. Deteksi serangan
        if ($attackType = detect_attack($request)) {
            $security->logAttack($attackType);
            // return service('response')
            //     ->setStatusCode(403)
            //     ->setBody("Security Alert: Suspicious request detected ($attackType). Your IP has been logged and blocked.")
            //     ->setHeader('Content-Type', 'text/plain');
            throw new HTTPException('Access Denied', 403);
        }

        // 3. Rate Limiting
        if (!$security->incrementRequest()) {
            // return service('response')
            //     ->setStatusCode(429)
            //     ->setBody('Too Many Requests: You have exceeded the allowed request rate.')
            //     ->setHeader('Content-Type', 'text/plain');
            throw new HTTPException('Access Denied', 403);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak perlu action setelah response
    }
}