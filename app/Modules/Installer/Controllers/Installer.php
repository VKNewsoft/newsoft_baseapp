<?php

namespace App\Modules\Installer\Controllers;

use CodeIgniter\Controller;

/**
 * Database Installer Controller
 * 
 * @author VKNewsoft - Newsoft Developer, 2025
 */
class Installer extends Controller
{
    protected $helpers = ['form'];

    private function escapeConfigValue(string $value): string
    {
        return var_export($value, true);
    }

    private function getDatabaseConfigTemplate(): string
    {
        return <<<'PHP'
<?php namespace Config;

/**
 * Database Configuration
 *
 * @package Config
 */

class Database extends \CodeIgniter\Database\Config
{
	public $filesPath = APPPATH . 'Database/';

	public $migrationsNamespace = [
		'App',
	];

	public $defaultGroup = 'default';

	public $default = [
		'DSN'      => '',
		'hostname' => 'localhost',
		'username' => 'root',
		'password' => '',
		'database' => '',
		'DBDriver' => 'MySQLi',
		'DBPrefix' => '',
		'pConnect' => false,
		'DBDebug'  => (ENVIRONMENT !== 'production'),
		'cacheOn'  => false,
		'cacheDir' => '',
		'charset'  => 'utf8',
		'DBCollat' => 'utf8_general_ci',
		'swapPre'  => '',
		'encrypt'  => false,
		'compress' => false,
		'strictOn' => false,
		'failover' => [],
		'port' => 3306,
	];

	public $tests = [
		'DSN'      => '',
		'hostname' => '127.0.0.1',
		'username' => '',
		'password' => '',
		'database' => ':memory:',
		'DBDriver' => 'SQLite3',
		'DBPrefix' => 'db_',
		'pConnect' => false,
		'DBDebug'  => (ENVIRONMENT !== 'production'),
		'cacheOn'  => false,
		'cacheDir' => '',
		'charset'  => 'utf8',
		'DBCollat' => 'utf8_general_ci',
		'swapPre'  => '',
		'encrypt'  => false,
		'compress' => false,
		'strictOn' => false,
		'failover' => [],
		'port'     => 3306,
	];

	public function __construct()
	{
		parent::__construct();

		if (ENVIRONMENT === 'testing')
		{
			$this->defaultGroup = 'tests';

			if ($group = getenv('DB'))
			{
				if (is_file(TESTPATH . 'travis/Database.php'))
				{
					require TESTPATH . 'travis/Database.php';

					if (! empty($dbconfig) && array_key_exists($group, $dbconfig))
					{
						$this->tests = $dbconfig[$group];
					}
				}
			}
		}
	}
}
PHP;
    }

    private function ensureWritableDirectories(): bool
    {
        $directories = [
            WRITEPATH,
            WRITEPATH . 'session',
            WRITEPATH . 'cache',
            WRITEPATH . 'debugbar',
            WRITEPATH . 'logs',
            WRITEPATH . 'uploads',
        ];

        foreach ($directories as $directory) {
            if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
                return false;
            }

            $indexFile = rtrim($directory, '\\/') . DIRECTORY_SEPARATOR . 'index.html';
            if (!file_exists($indexFile) && file_put_contents($indexFile, '') === false) {
                return false;
            }
        }

        return true;
    }

    private function ensureDatabaseConfigFile(): bool
    {
        $configFile = APPPATH . 'Config/Database.php';
        if (file_exists($configFile)) {
            return true;
        }

        return file_put_contents($configFile, $this->getDatabaseConfigTemplate()) !== false;
    }

    private function ensureInstallerPrerequisites(): bool
    {
        return $this->ensureWritableDirectories() && $this->ensureDatabaseConfigFile();
    }

    public function index()
    {
        if (!$this->ensureInstallerPrerequisites()) {
            return redirect()->back()->with('error', 'Gagal menyiapkan file instalasi awal.');
        }

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

        if (!$this->ensureInstallerPrerequisites()) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyiapkan file/folder instalasi.');
        }

        $validation = \Config\Services::validation();
        
        $rules = [
            'db_host' => 'required',
            'db_username' => 'required',
            'db_password' => 'permit_empty',
            'db_name' => 'required|regex_match[/^[A-Za-z0-9_]+$/]',
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
            $dbName = $data['database'];
            if (!preg_match('/^[A-Za-z0-9_]+$/', $dbName)) {
                $db->close();
                return redirect()->back()->withInput()->with('error', 'Nama database tidak valid.');
            }

            $dbName = $db->real_escape_string($dbName);
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
            } else {
                $error = $db->error;
                $db->query('ROLLBACK');
                $db->query('SET FOREIGN_KEY_CHECKS=1');
                $db->close();
                return redirect()->back()->withInput()->with('error', 'Import database gagal: ' . $error);
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
        if (!$this->ensureInstallerPrerequisites()) {
            return redirect()->to('/installer')->with('error', 'Gagal menyiapkan file/folder instalasi.');
        }

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
            if (!$this->ensureDatabaseConfigFile() || !class_exists('\Config\Database')) {
                return false;
            }

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

        if (!$this->ensureDatabaseConfigFile() || !file_exists($configFile)) {
            return false;
        }

        $content = file_get_contents($configFile);

        // Update konfigurasi default group
        $patterns = [
            "/'hostname'\s*=>\s*'[^']*'/" => "'hostname' => " . $this->escapeConfigValue((string) $data['hostname']),
            "/'username'\s*=>\s*'[^']*'/" => "'username' => " . $this->escapeConfigValue((string) $data['username']),
            "/'password'\s*=>\s*'[^']*'/" => "'password' => " . $this->escapeConfigValue((string) $data['password']),
            "/'database'\s*=>\s*'[^']*'/" => "'database' => " . $this->escapeConfigValue((string) $data['database']),
            "/'port'\s*=>\s*\d+/" => "'port' => " . (int) $data['port'],
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
