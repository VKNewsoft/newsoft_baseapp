<?php
/**
 * Model sinkronisasi schema database terhadap file installer.
 *
 * Model ini membaca schema target, membandingkan dengan schema database aktif,
 * lalu menyiapkan statement SQL untuk mode sinkronisasi aman maupun penuh.
 */

namespace App\Modules\DbSynchronisation\Models;

class DbSynchronisationModel extends \App\Modules\Common\Models\BaseModel
{
	protected $dumpPath;
	protected $cacheTtl = 120;

	public function __construct()
	{
		parent::__construct();
		$this->dumpPath = APPPATH . 'Database/newsoft_base.sql';
	}

	/**
	 * Ambil ringkasan sinkronisasi schema.
	 *
	 * Summary disimpan di cache singkat agar indicator menu tidak perlu parse
	 * dump SQL di setiap request sidebar.
	 */
	public function getSyncSummary(bool $forceRefresh = false): array
	{
		$cacheKey = $this->getCacheKey();
		$cache = cache();

		if (!$forceRefresh) {
			$cached = $cache->get($cacheKey);
			if (is_array($cached)) {
				return $cached;
			}
		}

		$summary = $this->buildSyncSummary();
		$cache->save($cacheKey, $summary, $this->cacheTtl);
		$cache->save('db_sync_summary_latest', $summary, $this->cacheTtl);
		$cache->save('db_sync_summary_current_key', $cacheKey, $this->cacheTtl);

		return $summary;
	}

	/**
	 * Hapus cache summary setelah proses sinkronisasi dijalankan.
	 */
	public function clearSyncCache(): void
	{
		$cache = cache();
		$currentKey = $cache->get('db_sync_summary_current_key');
		if ($currentKey) {
			$cache->delete($currentKey);
		}

		$cache->delete('db_sync_summary_latest');
		$cache->delete('db_sync_summary_current_key');
	}

	/**
	 * Eksekusi hanya statement aman yang sudah dipreview.
	 *
	 * Method ini dipertahankan untuk kompatibilitas flow lama agar tombol
	 * sinkronisasi aman tetap bekerja seperti sebelumnya.
	 */
	public function applySafeSync(): array
	{
		return $this->applySync(false);
	}

	/**
	 * Eksekusi seluruh diff yang sudah memiliki SQL, termasuk perubahan yang
	 * sebelumnya hanya tampil sebagai review manual.
	 */
	public function applyFullSync(): array
	{
		return $this->applySync(true);
	}

	/**
	 * Jalankan sinkronisasi schema berdasarkan mode eksekusi yang dipilih.
	 *
	 * Mode penuh dipakai saat operator memang ingin menyamakan schema aktif
	 * dengan dump installer, termasuk operasi drop/modify yang bersifat review.
	 */
	protected function applySync(bool $includeReviewItems = false): array
	{
		$summary = $this->getSyncSummary(true);
		$executed = [];
		$skipped = [];
		$errors = [];

		foreach ($summary['diff']['items'] as $item) {
			if (empty($item['sql'])) {
				continue;
			}

			if (!$includeReviewItems && empty($item['is_safe'])) {
				continue;
			}

			// Re-check sebelum eksekusi agar operasi tetap idempotent walau tombol
			// submit diklik berulang atau ada perubahan schema dari proses lain.
			if (!$this->isItemStillPending($item['item_key'])) {
				$skipped[] = $item['label'];
				continue;
			}

			try {
				foreach ($this->normalizeExecutableSql($item['sql']) as $sql) {
					$this->db->query($sql);
				}
				$executed[] = $item['label'];
			} catch (\Throwable $e) {
				$errors[] = $item['label'] . ': ' . $e->getMessage();
			}
		}

		$this->clearSyncCache();
		$updated = $this->getSyncSummary(true);

		$status = $errors ? 'warning' : 'ok';
		$message = ($includeReviewItems ? 'Sinkronisasi penuh' : 'Sinkronisasi aman') . ' selesai. Berhasil: ' . count($executed) . ', dilewati: ' . count($skipped) . ', error: ' . count($errors);

		return [
			'status' => $status,
			'message' => $message,
			'executed' => $executed,
			'skipped' => $skipped,
			'errors' => $errors,
			'summary' => $updated
		];
	}

	protected function buildSyncSummary(): array
	{
		$targetTables = $this->loadDumpSchema();
		$currentTables = $this->loadCurrentSchema();
		$diff = $this->buildDiff($currentTables, $targetTables);
		$registration = $this->getRegistrationStatus();

		$summary = [
			'missing_tables' => 0,
			'missing_columns' => 0,
			'missing_indexes' => 0,
			'different_columns' => 0,
			'different_indexes' => 0,
			'extra_tables' => 0,
			'extra_columns' => 0,
			'extra_indexes' => 0,
			'missing_seed_data' => 0,
			'safe_changes' => 0,
			'manual_review' => 0,
		];

		foreach ($diff['items'] as $item) {
			if (isset($summary[$item['counter']])) {
				$summary[$item['counter']]++;
			}

			if (!empty($item['is_safe'])) {
				$summary['safe_changes']++;
			} else {
				$summary['manual_review']++;
			}
		}

		return [
			'is_synced' => count($diff['items']) === 0,
			'is_registered' => $registration['is_registered'],
			'registration' => $registration,
			'generated_at' => date('Y-m-d H:i:s'),
			'dump_path' => $this->dumpPath,
			'summary' => $summary,
			'diff' => $diff
		];
	}

	/**
	 * Cek apakah module dan menu DB Synchronisation sudah terdaftar di DB.
	 */
	public function getRegistrationStatus(): array
	{
		try {
			$moduleExists = (bool) $this->db->table('core_module')
				->select('id_module')
				->where('nama_module', 'db-synchronisation')
				->get()
				->getRowArray();

			$menuExists = (bool) $this->db->table('core_menu')
				->select('id_menu')
				->where('url', 'db-synchronisation')
				->get()
				->getRowArray();
		} catch (\Throwable $e) {
			// Bila seed menu/module belum siap atau query gagal, status tetap
			// diperlakukan belum terdaftar agar indicator first setup tetap merah.
			$moduleExists = false;
			$menuExists = false;
		}

		return [
			'module_exists' => $moduleExists,
			'menu_exists' => $menuExists,
			'is_registered' => $moduleExists && $menuExists
		];
	}

	protected function buildDiff(array $currentTables, array $targetTables): array
	{
		$items = [];

		foreach ($targetTables as $tableName => $targetTable) {
			if (!isset($currentTables[$tableName])) {
				$items[] = $this->createDiffItem([
					'scope' => 'table',
					'status' => 'missing_in_current',
					'table' => $tableName,
					'name' => $tableName,
					'label' => 'Tabel ' . $tableName . ' belum ada',
					'current' => '-',
					'target' => 'CREATE TABLE tersedia di dump',
					'sql' => $targetTable['create_statement'],
					'is_safe' => true,
					'counter' => 'missing_tables'
				]);
				continue;
			}

			$currentTable = $currentTables[$tableName];

			foreach ($targetTable['columns'] as $columnName => $targetColumn) {
				if (!isset($currentTable['columns'][$columnName])) {
					$items[] = $this->createDiffItem([
						'scope' => 'column',
						'status' => 'missing_in_current',
						'table' => $tableName,
						'name' => $columnName,
						'label' => 'Kolom ' . $tableName . '.' . $columnName . ' belum ada',
						'current' => '-',
						'target' => $targetColumn['definition'],
						'sql' => 'ALTER TABLE `' . $tableName . '` ADD COLUMN ' . $targetColumn['definition'],
						'is_safe' => true,
						'counter' => 'missing_columns'
					]);
					continue;
				}

				$currentColumn = $currentTable['columns'][$columnName];
				if ($currentColumn['normalized'] !== $targetColumn['normalized']) {
					$items[] = $this->createDiffItem([
						'scope' => 'column',
						'status' => 'different',
						'table' => $tableName,
						'name' => $columnName,
						'label' => 'Definisi kolom ' . $tableName . '.' . $columnName . ' berbeda',
						'current' => $currentColumn['definition'],
						'target' => $targetColumn['definition'],
						// Statement modify tetap ditampilkan sebagai review karena
						// berpotensi memengaruhi data, tetapi sekarang bisa dieksekusi
						// melalui mode sinkronisasi penuh.
						'sql' => 'ALTER TABLE `' . $tableName . '` MODIFY COLUMN ' . $targetColumn['definition'],
						'is_safe' => false,
						'counter' => 'different_columns'
					]);
				}
			}

			foreach ($currentTable['columns'] as $columnName => $currentColumn) {
				if (!isset($targetTable['columns'][$columnName])) {
					$items[] = $this->createDiffItem([
						'scope' => 'column',
						'status' => 'extra_in_current',
						'table' => $tableName,
						'name' => $columnName,
						'label' => 'Kolom ' . $tableName . '.' . $columnName . ' hanya ada di database aktif',
						'current' => $currentColumn['definition'],
						'target' => '-',
						// Kolom ekstra diberi SQL drop agar operator bisa benar-benar
						// menyamakan schema aktif dengan target installer saat full sync.
						'sql' => 'ALTER TABLE `' . $tableName . '` DROP COLUMN `' . $columnName . '`',
						'is_safe' => false,
						'counter' => 'extra_columns'
					]);
				}
			}

			foreach ($targetTable['indexes'] as $indexName => $targetIndex) {
				if (!isset($currentTable['indexes'][$indexName])) {
					$items[] = $this->createDiffItem([
						'scope' => 'index',
						'status' => 'missing_in_current',
						'table' => $tableName,
						'name' => $indexName,
						'label' => 'Index ' . $tableName . '.' . $indexName . ' belum ada',
						'current' => '-',
						'target' => $targetIndex['definition'],
						'sql' => 'ALTER TABLE `' . $tableName . '` ADD ' . $targetIndex['definition'],
						'is_safe' => true,
						'counter' => 'missing_indexes'
					]);
					continue;
				}

				$currentIndex = $currentTable['indexes'][$indexName];
				if ($currentIndex['normalized'] !== $targetIndex['normalized']) {
					$items[] = $this->createDiffItem([
						'scope' => 'index',
						'status' => 'different',
						'table' => $tableName,
						'name' => $indexName,
						'label' => 'Definisi index ' . $tableName . '.' . $indexName . ' berbeda',
						'current' => $currentIndex['definition'],
						'target' => $targetIndex['definition'],
						// Index berbeda dieksekusi dengan pola drop lalu add agar
						// definisinya mengikuti dump tanpa perlu edit manual.
						'sql' => $this->buildReplaceIndexSql($tableName, $indexName, $targetIndex['definition']),
						'is_safe' => false,
						'counter' => 'different_indexes'
					]);
				}
			}

			foreach ($currentTable['indexes'] as $indexName => $currentIndex) {
				if (!isset($targetTable['indexes'][$indexName])) {
					$items[] = $this->createDiffItem([
						'scope' => 'index',
						'status' => 'extra_in_current',
						'table' => $tableName,
						'name' => $indexName,
						'label' => 'Index ' . $tableName . '.' . $indexName . ' hanya ada di database aktif',
						'current' => $currentIndex['definition'],
						'target' => '-',
						// Index ekstra bisa dibersihkan saat full sync agar struktur
						// index database aktif sama persis dengan dump installer.
						'sql' => $this->buildDropIndexSql($tableName, $indexName),
						'is_safe' => false,
						'counter' => 'extra_indexes'
					]);
				}
			}
		}

		foreach ($currentTables as $tableName => $currentTable) {
			if (!isset($targetTables[$tableName])) {
				$items[] = $this->createDiffItem([
					'scope' => 'table',
					'status' => 'extra_in_current',
					'table' => $tableName,
					'name' => $tableName,
					'label' => 'Tabel ' . $tableName . ' hanya ada di database aktif',
					'current' => 'Tabel tersedia di database aktif',
					'target' => '-',
					// Tabel ekstra ikut diberi SQL drop agar mode penuh benar-benar
					// mengeksekusi seluruh perbedaan schema yang terdeteksi.
					'sql' => 'DROP TABLE `' . $tableName . '`',
					'is_safe' => false,
					'counter' => 'extra_tables'
				]);
			}
		}

		// Sinkronisasi seed data penting ikut dihitung agar module tetap bisa
		// melakukan self-registration saat source baru di-pull ke database lama.
		$items = array_merge($items, $this->buildRegistrationDiff());

		return [
			'items' => $items
		];
	}

	/**
	 * Bangun diff untuk seed data wajib module DB Synchronisation.
	 *
	 * Diff ini menangani kondisi database lama yang schema tabelnya sudah sama,
	 * tetapi data core_module/core_menu/core_permission untuk module ini belum ada.
	 */
	protected function buildRegistrationDiff(): array
	{
		$items = [];
		foreach ($this->getRequiredSeedRows() as $seed) {
			$exists = (bool) $this->db->table($seed['table'])
				->select($seed['key_column'])
				->where($seed['where'])
				->get()
				->getRowArray();

			if ($exists) {
				continue;
			}

			$items[] = $this->createDiffItem([
				'scope' => 'seed',
				'status' => 'missing_in_current',
				'table' => $seed['table'],
				'name' => $seed['label'],
				'label' => $seed['description'],
				'current' => '-',
				'target' => json_encode($seed['data'], JSON_UNESCAPED_UNICODE),
				'sql' => $seed['sql'],
				'is_safe' => true,
				'counter' => 'missing_seed_data'
			]);
		}

		return $items;
	}

	/**
	 * Seed data minimum yang wajib ada agar module bisa beralih dari fallback ke
	 * mode database penuh setelah proses sinkronisasi dijalankan.
	 */
	protected function getRequiredSeedRows(): array
	{
		return [
			[
				'table' => 'core_module',
				'key_column' => 'id_module',
				'where' => ['id_module' => 124],
				'label' => 'core_module.db-synchronisation',
				'description' => 'Registrasi module DB Synchronisation belum ada',
				'data' => [
					'id_module' => 124,
					'nama_module' => 'db-synchronisation',
					'judul_module' => 'CORE - DB Synchronisation',
					'id_module_status' => 1,
					'login' => 'Y',
					'deskripsi' => 'Module untuk membandingkan schema database aktif dengan dump installer'
				],
				'sql' => "INSERT INTO `core_module` (`id_module`,`nama_module`,`judul_module`,`id_module_status`,`login`,`deskripsi`) SELECT 124,'db-synchronisation','CORE - DB Synchronisation',1,'Y','Module untuk membandingkan schema database aktif dengan dump installer' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `core_module` WHERE `id_module` = 124 OR `nama_module` = 'db-synchronisation')"
			],
			[
				'table' => 'core_menu',
				'key_column' => 'id_menu',
				'where' => ['id_menu' => 172],
				'label' => 'core_menu.db-synchronisation',
				'description' => 'Registrasi menu DB Synchronisation belum ada',
				'data' => [
					'id_menu' => 172,
					'nama_menu' => 'DB Synchronisation',
					'id_menu_kategori' => 1,
					'class' => null,
					'url' => 'db-synchronisation',
					'id_module' => 124,
					'id_parent' => 13,
					'aktif' => 1,
					'new' => 0,
					'urut' => 5
				],
				'sql' => "INSERT INTO `core_menu` (`id_menu`,`nama_menu`,`id_menu_kategori`,`class`,`url`,`id_module`,`id_parent`,`aktif`,`new`,`urut`) SELECT 172,'DB Synchronisation',1,NULL,'db-synchronisation',124,13,1,0,5 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `core_menu` WHERE `id_menu` = 172 OR `url` = 'db-synchronisation')"
			],
			[
				'table' => 'core_menu_role',
				'key_column' => 'id_menu',
				'where' => ['id_menu' => 172, 'id_role' => 1],
				'label' => 'core_menu_role.db-synchronisation.admin',
				'description' => 'Akses menu Administrator ke DB Synchronisation belum ada',
				'data' => ['id_menu' => 172, 'id_role' => 1],
				'sql' => "INSERT INTO `core_menu_role` (`id_menu`,`id_role`) SELECT 172,1 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `core_menu_role` WHERE `id_menu` = 172 AND `id_role` = 1)"
			],
			[
				'table' => 'core_module_permission',
				'key_column' => 'id_module_permission',
				'where' => ['id_module_permission' => 450],
				'label' => 'core_module_permission.db-sync.create',
				'description' => 'Permission create DB Synchronisation belum ada',
				'data' => ['id_module_permission' => 450, 'id_module' => 124, 'nama_permission' => 'create'],
				'sql' => "INSERT INTO `core_module_permission` (`id_module_permission`,`id_module`,`nama_permission`,`judul_permission`,`keterangan`) SELECT 450,124,'create','Create Data','Hak akses untuk menambah data sinkronisasi schema' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `core_module_permission` WHERE `id_module_permission` = 450)"
			],
			[
				'table' => 'core_module_permission',
				'key_column' => 'id_module_permission',
				'where' => ['id_module_permission' => 451],
				'label' => 'core_module_permission.db-sync.read',
				'description' => 'Permission read_all DB Synchronisation belum ada',
				'data' => ['id_module_permission' => 451, 'id_module' => 124, 'nama_permission' => 'read_all'],
				'sql' => "INSERT INTO `core_module_permission` (`id_module_permission`,`id_module`,`nama_permission`,`judul_permission`,`keterangan`) SELECT 451,124,'read_all','Read All Data','Hak akses untuk melihat diff sinkronisasi schema' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `core_module_permission` WHERE `id_module_permission` = 451)"
			],
			[
				'table' => 'core_module_permission',
				'key_column' => 'id_module_permission',
				'where' => ['id_module_permission' => 452],
				'label' => 'core_module_permission.db-sync.update',
				'description' => 'Permission update_all DB Synchronisation belum ada',
				'data' => ['id_module_permission' => 452, 'id_module' => 124, 'nama_permission' => 'update_all'],
				'sql' => "INSERT INTO `core_module_permission` (`id_module_permission`,`id_module`,`nama_permission`,`judul_permission`,`keterangan`) SELECT 452,124,'update_all','Update All Data','Hak akses untuk mengeksekusi sinkronisasi schema aman' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `core_module_permission` WHERE `id_module_permission` = 452)"
			],
			[
				'table' => 'core_module_permission',
				'key_column' => 'id_module_permission',
				'where' => ['id_module_permission' => 453],
				'label' => 'core_module_permission.db-sync.delete',
				'description' => 'Permission delete_all DB Synchronisation belum ada',
				'data' => ['id_module_permission' => 453, 'id_module' => 124, 'nama_permission' => 'delete_all'],
				'sql' => "INSERT INTO `core_module_permission` (`id_module_permission`,`id_module`,`nama_permission`,`judul_permission`,`keterangan`) SELECT 453,124,'delete_all','Delete All Data','Hak akses placeholder agar pattern permission module tetap konsisten' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `core_module_permission` WHERE `id_module_permission` = 453)"
			],
			[
				'table' => 'core_role_module_permission',
				'key_column' => 'id_module_permission',
				'where' => ['id_role' => 1, 'id_module_permission' => 450],
				'label' => 'core_role_module_permission.db-sync.450',
				'description' => 'Assignment permission create untuk role Administrator belum ada',
				'data' => ['id_role' => 1, 'id_module_permission' => 450],
				'sql' => "INSERT INTO `core_role_module_permission` (`id_role`,`id_module_permission`) SELECT 1,450 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `core_role_module_permission` WHERE `id_role` = 1 AND `id_module_permission` = 450)"
			],
			[
				'table' => 'core_role_module_permission',
				'key_column' => 'id_module_permission',
				'where' => ['id_role' => 1, 'id_module_permission' => 451],
				'label' => 'core_role_module_permission.db-sync.451',
				'description' => 'Assignment permission read_all untuk role Administrator belum ada',
				'data' => ['id_role' => 1, 'id_module_permission' => 451],
				'sql' => "INSERT INTO `core_role_module_permission` (`id_role`,`id_module_permission`) SELECT 1,451 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `core_role_module_permission` WHERE `id_role` = 1 AND `id_module_permission` = 451)"
			],
			[
				'table' => 'core_role_module_permission',
				'key_column' => 'id_module_permission',
				'where' => ['id_role' => 1, 'id_module_permission' => 452],
				'label' => 'core_role_module_permission.db-sync.452',
				'description' => 'Assignment permission update_all untuk role Administrator belum ada',
				'data' => ['id_role' => 1, 'id_module_permission' => 452],
				'sql' => "INSERT INTO `core_role_module_permission` (`id_role`,`id_module_permission`) SELECT 1,452 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `core_role_module_permission` WHERE `id_role` = 1 AND `id_module_permission` = 452)"
			],
			[
				'table' => 'core_role_module_permission',
				'key_column' => 'id_module_permission',
				'where' => ['id_role' => 1, 'id_module_permission' => 453],
				'label' => 'core_role_module_permission.db-sync.453',
				'description' => 'Assignment permission delete_all untuk role Administrator belum ada',
				'data' => ['id_role' => 1, 'id_module_permission' => 453],
				'sql' => "INSERT INTO `core_role_module_permission` (`id_role`,`id_module_permission`) SELECT 1,453 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `core_role_module_permission` WHERE `id_role` = 1 AND `id_module_permission` = 453)"
			]
		];
	}

	protected function createDiffItem(array $item): array
	{
		// Preview SQL disiapkan terpisah agar view bisa menampilkan array statement
		// multi-langkah dengan format yang tetap mudah dibaca operator.
		$item['sql_preview'] = $this->buildSqlPreview($item['sql'] ?? '');
		$item['item_key'] = sha1(
			$item['scope'] . '|' .
			$item['status'] . '|' .
			$item['table'] . '|' .
			$item['name'] . '|' .
			$item['target']
		);

		return $item;
	}

	protected function isItemStillPending(string $itemKey): bool
	{
		$summary = $this->getSyncSummary(true);
		foreach ($summary['diff']['items'] as $item) {
			if ($item['item_key'] === $itemKey) {
				return true;
			}
		}

		return false;
	}

	protected function getCacheKey(): string
	{
		$dbName = $this->db->database ?? 'default';
		$version = @filemtime($this->dumpPath) ?: 0;
		return 'db_sync_summary_' . md5($dbName . '|' . $version);
	}

	protected function loadDumpSchema(): array
	{
		if (!is_file($this->dumpPath)) {
			return [];
		}

		$sql = file_get_contents($this->dumpPath);
		$tables = [];
		preg_match_all('/CREATE TABLE `([^`]+)` \\((.*?)\\) ENGINE=.*?;/si', $sql, $matches, PREG_SET_ORDER);

		foreach ($matches as $match) {
			$tableName = $match[1];
			$createStatement = trim($match[0]);
			$tables[$tableName] = $this->parseCreateStatement($createStatement);
		}

		return $tables;
	}

	protected function loadCurrentSchema(): array
	{
		$tables = [];
		$query = $this->db->query('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"')->getResultArray();

		foreach ($query as $row) {
			$tableName = array_values($row)[0] ?? '';
			if ($tableName === '') {
				continue;
			}

			$createRow = $this->db->query('SHOW CREATE TABLE `' . $tableName . '`')->getRowArray();
			$createStatement = $createRow['Create Table'] ?? '';
			if ($createStatement === '') {
				continue;
			}

			$tables[$tableName] = $this->parseCreateStatement($createStatement);
		}

		return $tables;
	}

	protected function parseCreateStatement(string $createStatement): array
	{
		$tableDefaults = $this->extractTableDefaults($createStatement);
		$result = [
			'create_statement' => trim($createStatement),
			'columns' => [],
			'indexes' => [],
			'defaults' => $tableDefaults
		];

		$lines = preg_split('/\r\n|\r|\n/', $createStatement);
		foreach ($lines as $line) {
			$line = trim($line);
			$line = rtrim($line, ',');

			if ($line === '' || strpos($line, 'CREATE TABLE') === 0 || $line === ')') {
				continue;
			}

			if (preg_match('/^`([^`]+)`\s+(.*)$/', $line, $matches)) {
				$columnName = $matches[1];
				$definition = '`' . $columnName . '` ' . trim($matches[2]);
				$result['columns'][$columnName] = [
					'definition' => $definition,
					// Normalisasi kolom memperhitungkan default charset/collation
					// tabel agar beda representasi yang semantik sama tidak dianggap diff.
					'normalized' => $this->normalizeColumnFragment($definition, $tableDefaults)
				];
				continue;
			}

			if (stripos($line, 'PRIMARY KEY') === 0) {
				$result['indexes']['PRIMARY'] = [
					'definition' => $line,
					'normalized' => $this->normalizeSqlFragment($line)
				];
				continue;
			}

			if (preg_match('/^(UNIQUE KEY|KEY)\s+`([^`]+)`/i', $line, $matches)) {
				$indexName = $matches[2];
				$result['indexes'][$indexName] = [
					'definition' => $line,
					'normalized' => $this->normalizeSqlFragment($line)
				];
			}
		}

		return $result;
	}

	protected function normalizeSqlFragment(string $fragment): string
	{
		$fragment = strtolower(trim($fragment));
		$fragment = preg_replace('/\s+/', ' ', $fragment);
		return trim((string) $fragment);
	}

	/**
	 * Ambil default charset dan collation tabel dari CREATE TABLE agar
	 * komparasi kolom bisa mengabaikan deklarasi yang setara secara semantik.
	 */
	protected function extractTableDefaults(string $createStatement): array
	{
		$defaults = [
			'charset' => '',
			'collation' => ''
		];

		if (preg_match('/default\s+charset\s*=\s*([a-zA-Z0-9_]+)/i', $createStatement, $matches)) {
			$defaults['charset'] = strtolower($matches[1]);
		}

		if (preg_match('/collate\s*=\s*([a-zA-Z0-9_]+)/i', $createStatement, $matches)) {
			$defaults['collation'] = strtolower($matches[1]);
		}

		return $defaults;
	}

	/**
	 * Normalisasi definisi kolom dengan menghapus charset/collation eksplisit
	 * yang nilainya sama persis dengan default tabel.
	 */
	protected function normalizeColumnFragment(string $fragment, array $tableDefaults = []): string
	{
		$normalized = $this->normalizeSqlFragment($fragment);
		$tableCharset = strtolower((string) ($tableDefaults['charset'] ?? ''));
		$tableCollation = strtolower((string) ($tableDefaults['collation'] ?? ''));

		if ($tableCharset !== '') {
			$normalized = preg_replace('/\s+character set\s+' . preg_quote($tableCharset, '/') . '\b/i', '', $normalized);
		}

		if ($tableCollation !== '') {
			$normalized = preg_replace('/\s+collate\s+' . preg_quote($tableCollation, '/') . '\b/i', '', $normalized);
		}

		$normalized = preg_replace('/\s+/', ' ', (string) $normalized);
		return trim((string) $normalized);
	}

	/**
	 * Ubah definisi SQL item menjadi daftar statement yang siap dieksekusi.
	 */
	protected function normalizeExecutableSql($sql): array
	{
		if (is_array($sql)) {
			return array_values(array_filter(array_map('trim', $sql)));
		}

		$sql = trim((string) $sql);
		return $sql === '' ? [] : [$sql];
	}

	/**
	 * Bangun preview SQL yang konsisten untuk kebutuhan tampilan diff.
	 */
	protected function buildSqlPreview($sql): string
	{
		$statements = $this->normalizeExecutableSql($sql);
		return implode(";\n", $statements);
	}

	/**
	 * Susun statement penggantian index agar definisi index berbeda bisa
	 * disinkronkan penuh tanpa perlu intervensi SQL manual.
	 */
	protected function buildReplaceIndexSql(string $tableName, string $indexName, string $targetDefinition): string
	{
		$dropClause = strtoupper($indexName) === 'PRIMARY' ? 'DROP PRIMARY KEY' : 'DROP INDEX `' . $indexName . '`';
		return 'ALTER TABLE `' . $tableName . '` ' . $dropClause . ', ADD ' . $targetDefinition;
	}

	/**
	 * Bentuk statement drop index sesuai tipe index agar PRIMARY KEY juga
	 * dapat ditangani dengan syntax MySQL yang benar.
	 */
	protected function buildDropIndexSql(string $tableName, string $indexName): string
	{
		if (strtoupper($indexName) === 'PRIMARY') {
			return 'ALTER TABLE `' . $tableName . '` DROP PRIMARY KEY';
		}

		return 'ALTER TABLE `' . $tableName . '` DROP INDEX `' . $indexName . '`';
	}
}
