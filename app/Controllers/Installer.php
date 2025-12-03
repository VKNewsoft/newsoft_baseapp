<?php

namespace App\Controllers;

use CodeIgniter\Controller;

/**
 * Database Installer Controller
 * 
 * @author VKNewsoft - Newsoft Developer, 2025
 */
class Installer extends Controller
{
    protected $helpers = ['form'];

    public function index()
    {
        // Cek apakah database sudah terkonfigurasi dan bisa diakses
        if ($this->isDatabaseConfigured()) {
            return redirect()->to('/');
        }

        // Load view installer
        return view('installer/index');
    }

    public function install()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/installer');
        }

        $validation = \Config\Services::validation();
        
        $rules = [
            'db_host' => 'required',
            'db_username' => 'required',
            'db_password' => 'permit_empty',
            'db_name' => 'required|alpha_numeric_punct',
            'db_port' => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $data = [
            'hostname' => $this->request->getPost('db_host'),
            'username' => $this->request->getPost('db_username'),
            'password' => $this->request->getPost('db_password'),
            'database' => $this->request->getPost('db_name'),
            'port' => $this->request->getPost('db_port'),
            'driver' => 'MySQLi',
        ];

        // Test koneksi
        try {
            $db = new \mysqli(
                $data['hostname'], 
                $data['username'], 
                $data['password'],
                '',
                $data['port']
            );

            if ($db->connect_error) {
                return redirect()->back()->withInput()->with('error', 'Koneksi database gagal: ' . $db->connect_error);
            }

            // Create database jika belum ada
            $dbName = $db->real_escape_string($data['database']);
            $db->query("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $db->select_db($dbName);

            // Import SQL file
            $sqlFile = APPPATH . 'Database/newsoft_base.sql';
            if (!file_exists($sqlFile)) {
                return redirect()->back()->withInput()->with('error', 'File newsoft_base.sql tidak ditemukan!');
            }

            $sql = file_get_contents($sqlFile);
            
            // Execute multi-query
            $db->query('SET FOREIGN_KEY_CHECKS=0');
            $db->query('SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO"');
            $db->query('SET AUTOCOMMIT=0');
            $db->query('START TRANSACTION');

            if ($db->multi_query($sql)) {
                do {
                    if ($result = $db->store_result()) {
                        $result->free();
                    }
                } while ($db->more_results() && $db->next_result());
            }

            $db->query('COMMIT');
            $db->query('SET FOREIGN_KEY_CHECKS=1');
            $db->close();

            // Update file Database.php
            if (!$this->updateDatabaseConfig($data)) {
                return redirect()->back()->withInput()->with('error', 'Gagal menulis konfigurasi database!');
            }

            // Redirect ke halaman sukses
            return redirect()->to('/installer/success');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function success()
    {
        if ($this->isDatabaseConfigured()) {
            return view('installer/success');
        }
        return redirect()->to('/installer');
    }

    /**
     * Cek apakah database sudah terkonfigurasi dan bisa diakses
     */
    private function isDatabaseConfigured(): bool
    {
        try {
            // Get database config
            $dbConfig = new \Config\Database();
            $config = $dbConfig->default;
            
            // Try to connect using mysqli directly (bypass CI4 error handling)
            $db = @new \mysqli(
                $config['hostname'],
                $config['username'],
                $config['password'],
                $config['database'],
                $config['port']
            );
            
            // Check connection
            if ($db->connect_error) {
                return false;
            }
            
            // Check if core_user table exists
            $result = @$db->query("SHOW TABLES LIKE 'core_user'");
            
            if ($result && $result->num_rows > 0) {
                $db->close();
                return true;
            }
            
            $db->close();
            return false;
            
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update file app/Config/Database.php
     */
    private function updateDatabaseConfig(array $data): bool
    {
        $configFile = APPPATH . 'Config/Database.php';
        
        if (!file_exists($configFile)) {
            return false;
        }

        $content = file_get_contents($configFile);

        // Update konfigurasi default group
        $patterns = [
            "/'hostname'\s*=>\s*'[^']*'/" => "'hostname' => '{$data['hostname']}'",
            "/'username'\s*=>\s*'[^']*'/" => "'username' => '{$data['username']}'",
            "/'password'\s*=>\s*'[^']*'/" => "'password' => '{$data['password']}'",
            "/'database'\s*=>\s*'[^']*'/" => "'database' => '{$data['database']}'",
            "/'port'\s*=>\s*\d+/" => "'port' => {$data['port']}",
        ];

        foreach ($patterns as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content, 1);
        }

        // Tulis kembali file
        if (file_put_contents($configFile, $content)) {
            // Clear opcode cache jika ada
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
            return true;
        }

        return false;
    }
}
