<?php

if (!function_exists('app_runtime_cache_remember')) {
	/**
	 * Cache helper sederhana agar pengecekan runtime berat tidak dieksekusi
	 * berulang pada request yang berbeda dalam waktu berdekatan.
	 */
	function app_runtime_cache_remember(string $key, callable $resolver, int $ttl = 120)
	{
		static $requestCache = [];

		if (array_key_exists($key, $requestCache)) {
			return $requestCache[$key];
		}

		$cache = cache();
		$cached = $cache ? $cache->get($key) : null;
		if (is_array($cached) && array_key_exists('data', $cached)) {
			$requestCache[$key] = $cached['data'];
			return $requestCache[$key];
		}

		$value = $resolver();
		if ($cache) {
			$cache->save($key, ['data' => $value], $ttl);
		}

		$requestCache[$key] = $value;
		return $value;
	}
}

if (!function_exists('app_database_runtime_status')) {
	/**
	 * Status database di-cache singkat agar filter global tidak membuka koneksi
	 * MySQL berulang pada setiap request hanya untuk validasi bootstrap.
	 */
	function app_database_runtime_status(bool $refresh = false): bool
	{
		static $requestCache = null;
		if (!$refresh && $requestCache !== null) {
			return $requestCache;
		}

		$configFile = APPPATH . 'Config/Database.php';
		if (!is_file($configFile)) {
			$requestCache = false;
			return false;
		}

		$signature = md5((string) @filemtime($configFile) . '|' . (string) @filesize($configFile));
		$cacheKey = 'runtime_db_status_' . $signature;

		$resolver = static function() {
			try {
				$dbConfig = config('Database');
				$default = (array) ($dbConfig->default ?? []);
				$database = trim((string) ($default['database'] ?? ''));

				if ($database === '') {
					return false;
				}

				$conn = @new \mysqli(
					(string) ($default['hostname'] ?? 'localhost'),
					(string) ($default['username'] ?? 'root'),
					(string) ($default['password'] ?? ''),
					$database,
					(int) ($default['port'] ?? 3306)
				);

				if ($conn->connect_error) {
					return false;
				}

				$result = @$conn->query("SHOW TABLES LIKE 'core_user'");
				$isReady = $result && $result->num_rows > 0;
				$conn->close();

				return (bool) $isReady;
			} catch (\Throwable $e) {
				return false;
			}
		};

		$ttl = $refresh ? 1 : 120;
		$requestCache = (bool) app_runtime_cache_remember($cacheKey, $resolver, $ttl);
		return $requestCache;
	}
}
