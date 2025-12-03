<?php
/**
 * security_helper.php
 * Security Attack Detection Helper
 * 
 * @author  VKNewsoft - Newsoft Developer, 2025
 */

if (!function_exists('detect_attack')) {
    function detect_attack($request)
    {
        $body = $request->getBody() ?? '';
        $get = $request->getGet() ? http_build_query($request->getGet()) : '';
        $post = $request->getPost() ? http_build_query($request->getPost()) : '';
        $userAgent = $request->getUserAgent() ?? '';

        // Gabungkan semua input
        $combined = "$body | $get | $post";

        // Hapus atau abaikan string panjang (misal konten JS/CSS) agar tidak kena regex salah
        // Tapi tetap cek input user

        // Pola serangan — diperketat agar tidak false positive
        $patterns = [
            'sql_injection' => [
                '/\bunion\s+all\s+select\b/i',
                '/\bselect\s+\*\s+from\s+\w+\s+where\s+\w+\s*[<>=]/i',
                '/\b(insert|update|delete|drop|alter)\s+\w+/i',
                '/\b(exec|execute)\s+[\w\(\)]+/i',
                '/(or|and)\s+[\'"]?\d+[\'"]?\s*=\s*[\'"]?\d+[\'"]?/i', // or '1'='1'
                '/sleep\s*$$\s*\d+/i',
                '/benchmark\s*$$/i',
            ],
            'xss' => [
                '/<script[^>]*>[\s\S]*?<\/script>/i',
                '/<img[^>]*onerror[\s\S]*?>/i',
                '/javascript:/i',
                '/<iframe[^>]*src[\s\S]*?>/i',
                '/expression\s*$$/i',
                '/<svg[^>]*onload[\s\S]*?>/i',
            ],
            'sqlmap' => [
                '/sqlmap/i',
                '/sqlmap\/\d+/i',
                '/User-Agent:.*sqlmap/i',
            ],
            'brute_force' => [
                    // Hanya deteksi SQLi umum di field password
                    '/(password|pass|pwd)[^a-zA-Z0-9]*[\'"][\s]*or[\s]*[\'"]\d+[\'"][\s]*=[\s]*[\'"]\d+[\'"]/i',
                    '/(password|pass)[^a-zA-Z0-9]*[=\s]+[\'"]?\s*[\d\w]*1\s*or\s*1\s*[=\'\s]+/i',
                ],
        ];

        foreach ($patterns as $type => $regexes) {
            foreach ($regexes as $regex) {
                // Cek di body, GET, POST
                if (preg_match($regex, $combined)) {
                    return $type;
                }
                // Cek khusus User-Agent hanya untuk sqlmap dan beberapa XSS
                if (in_array($type, ['sqlmap', 'xss']) && preg_match($regex, $userAgent)) {
                    return $type;
                }
            }
        }

        return false;
    }
}

if (!function_exists('get_client_ip')) {
    function get_client_ip()
    {
        $request = service('request');
        return $request->getIPAddress();
    }
}