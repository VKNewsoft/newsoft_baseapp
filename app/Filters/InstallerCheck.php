<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Installer Check Filter
 * Redirect ke installer jika database belum terkonfigurasi
 * 
 * @author VKNewsoft - Newsoft Developer, 2025
 */
class InstallerCheck implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Skip jika sedang akses installer atau asset files
        $uri = $request->getUri()->getPath();
        
        if (strpos($uri, 'installer') !== false || 
            strpos($uri, 'public/') !== false ||
            strpos($uri, '.css') !== false ||
            strpos($uri, '.js') !== false ||
            strpos($uri, '.png') !== false ||
            strpos($uri, '.jpg') !== false) {
            return;
        }

        // Cek apakah database sudah terkonfigurasi
        if (!$this->isDatabaseConfigured()) {
            // Set flag untuk bypass filter lain
            $request->installerMode = true;
            
            return redirect()->to(base_url('installer'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do here
    }

    /**
     * Cek apakah database sudah terkonfigurasi dan bisa diakses
     */
    private function isDatabaseConfigured(): bool
    {
        try {
            // Read config file directly to avoid autoload issues
            $configFile = APPPATH . 'Config/Database.php';
            if (!file_exists($configFile)) {
                return false;
            }
            
            $content = file_get_contents($configFile);
            
            // Extract database settings using regex
            preg_match("/'hostname'\s*=>\s*'([^']+)'/", $content, $host);
            preg_match("/'username'\s*=>\s*'([^']+)'/", $content, $user);
            preg_match("/'password'\s*=>\s*'([^']+)'/", $content, $pass);
            preg_match("/'database'\s*=>\s*'([^']+)'/", $content, $db);
            preg_match("/'port'\s*=>\s*(\d+)/", $content, $port);
            
            $hostname = $host[1] ?? 'localhost';
            $username = $user[1] ?? 'root';
            $password = $pass[1] ?? '';
            $database = $db[1] ?? '';
            $dbport = (int)($port[1] ?? 3306);
            
            if (empty($database)) {
                return false;
            }
            
            // Try to connect using mysqli directly (bypass CI4 error handling)
            $conn = @new \mysqli($hostname, $username, $password, $database, $dbport);
            
            // Check connection
            if ($conn->connect_error) {
                return false;
            }
            
            // Check if core_user table exists
            $result = @$conn->query("SHOW TABLES LIKE 'core_user'");
            
            if ($result && $result->num_rows > 0) {
                $conn->close();
                return true;
            }
            
            $conn->close();
            return false;
            
        } catch (\Exception $e) {
            return false;
        }
    }
}
