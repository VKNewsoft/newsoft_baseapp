<?php
/**
 * Model sinkronisasi schema database terhadap file installer.
 *
 * Fokus model ini hanya membaca schema target, membandingkan dengan schema
 * database aktif, lalu menyiapkan ALTER aman yang bersifat non-destructive.
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
	 * Statement yang dijalankan hanya:
	 * - CREATE TABLE untuk tabel yang belum ada
	 * - ADD COLUMN untuk kolom yang belum ada
	 * - ADD INDEX untuk index yang belum ada
	 *
	 * Perubahan type kolom/index yang berbeda hanya ditandai review manual
	 * supaya tidak berisiko merusak data existing.
	 */
	public function applySafeSync(): array
	{
		$summary = $this->getSyncSummary(true);
		$executed = [];
		$skipped = [];
		$errors = [];

		foreach ($summary['diff']['items'] as $item) {
			if (empty($item['is_safe']) || empty($item['sql'])) {
				continue;
			}

			// Re-check sebelum eksekusi agar operasi tetap idempotent walau tombol
			// submit diklik berulang atau ada perubahan schema dari proses lain.
			if (!$this->isItemStillPending($item['item_key'])) {
				$skipped[] = $item['label'];
				continue;
			}

			try {
				$this->db->query($item['sql']);
				$executed[] = $item['label'];
			} catch (\Throwable $e) {
				$errors[] = $item['label'] . ': ' . $e->getMessage();
			}
		}

		$this->clearSyncCache();
		$updated = $this->getSyncSummary(true);

		$status = $errors ? 'warning' : 'ok';
		$message = 'Sinkronisasi aman selesai. Berhasil: ' . count($executed) . ', dilewati: ' . count($skipped) . ', error: ' . count($errors);

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

		$summary = [
			'missing_tables' => 0,
			'missing_columns' => 0,
			'missing_indexes' => 0,
			'different_columns' => 0,
			'different_indexes' => 0,
			'extra_tables' => 0,
			'extra_columns' => 0,
			'extra_indexes' => 0,
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
			'generated_at' => date('Y-m-d H:i:s'),
			'dump_path' => $this->dumpPath,
			'summary' => $summary,
			'diff' => $diff
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
						'sql' => '',
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
						'sql' => '',
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
						'sql' => '',
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
					'sql' => '',
					'is_safe' => false,
					'counter' => 'extra_tables'
				]);
			}
		}

		return [
			'items' => $items
		];
	}

	protected function createDiffItem(array $item): array
	{
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
		$result = [
			'create_statement' => trim($createStatement),
			'columns' => [],
			'indexes' => []
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
					'normalized' => $this->normalizeSqlFragment($definition)
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
}
