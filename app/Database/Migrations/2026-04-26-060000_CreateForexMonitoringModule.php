<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateForexMonitoringModule extends Migration
{
	/**
	 * Registry module forex disimpan terpusat agar proses seed menu dan
	 * permission tetap konsisten saat migration dijalankan ulang.
	 */
	private array $modules = [
		'forex-monitor' => 'Forex Monitor',
		'forex-prediction' => 'Forex Prediction',
	];

	public function up()
	{
		// Tabel harga forex menyimpan satu candle harian per pair dan tanggal
		// agar histori GBP/JPY dapat dicari ulang tanpa memanggil API lagi.
		$this->forge->addField([
			'id_forex_price' => [
				'type' => 'INT',
				'constraint' => 10,
				'unsigned' => true,
				'auto_increment' => true,
			],
			'pair' => [
				'type' => 'VARCHAR',
				'constraint' => 20,
			],
			'date' => [
				'type' => 'DATE',
			],
			'open_price' => [
				'type' => 'DECIMAL',
				'constraint' => '18,6',
			],
			'high_price' => [
				'type' => 'DECIMAL',
				'constraint' => '18,6',
			],
			'low_price' => [
				'type' => 'DECIMAL',
				'constraint' => '18,6',
			],
			'close_price' => [
				'type' => 'DECIMAL',
				'constraint' => '18,6',
			],
			'source_api' => [
				'type' => 'VARCHAR',
				'constraint' => 100,
			],
			'created_at' => [
				'type' => 'DATETIME',
				'null' => true,
			],
		]);
		$this->forge->addKey('id_forex_price', true);
		$this->forge->addUniqueKey(['pair', 'date']);
		$this->forge->addKey('pair');
		$this->forge->addKey('date');
		$this->forge->createTable('forex_price', true);

		// Tabel analisis menjaga hasil range, trend, dan narasi harian tetap
		// terdokumentasi untuk kebutuhan report tanpa menghitung ulang terus-menerus.
		$this->forge->addField([
			'id_forex_analysis' => [
				'type' => 'INT',
				'constraint' => 10,
				'unsigned' => true,
				'auto_increment' => true,
			],
			'date' => [
				'type' => 'DATE',
			],
			'pair' => [
				'type' => 'VARCHAR',
				'constraint' => 20,
			],
			'high_low_range' => [
				'type' => 'DECIMAL',
				'constraint' => '18,6',
			],
			'trend' => [
				'type' => 'VARCHAR',
				'constraint' => 20,
			],
			'summary' => [
				'type' => 'TEXT',
				'null' => true,
			],
			'created_at' => [
				'type' => 'DATETIME',
				'null' => true,
			],
		]);
		$this->forge->addKey('id_forex_analysis', true);
		$this->forge->addUniqueKey(['pair', 'date']);
		$this->forge->addKey('pair');
		$this->forge->addKey('date');
		$this->forge->createTable('forex_analysis', true);

		$moduleIds = [];
		foreach ($this->modules as $moduleName => $moduleTitle) {
			$moduleIds[$moduleName] = $this->upsertModule($moduleName, $moduleTitle);
			$this->registerDefaultPermission($moduleIds[$moduleName], $moduleTitle);
		}

		// Permission default dihubungkan ke Administrator agar module langsung
		// dapat diuji begitu migration selesai dijalankan.
		$this->assignPermissionToRole(1, array_values($moduleIds));

		$parentMenuId = $this->upsertParentMenu();
		$this->upsertChildMenu('Forex Monitor', 'forex-monitor', $moduleIds['forex-monitor'], $parentMenuId, 1);
		$this->upsertChildMenu('Forex Prediction', 'forex-prediction', $moduleIds['forex-prediction'], $parentMenuId, 2);
	}

	public function down()
	{
		$moduleIds = [];
		foreach (array_keys($this->modules) as $moduleName) {
			$module = $this->db->table('core_module')->select('id_module')->where('nama_module', $moduleName)->get()->getRowArray();
			if ($module) {
				$moduleIds[] = (int) $module['id_module'];
			}
		}

		// Menu child dihapus lebih dulu agar parent Forex dapat dibersihkan aman.
		$this->db->table('core_menu')->whereIn('url', ['forex-monitor', 'forex-prediction'])->delete();
		$this->db->table('core_menu')->where('nama_menu', 'Forex')->where('url', '#')->delete();

		if ($moduleIds) {
			$permissionRows = $this->db->table('core_module_permission')
				->select('id_module_permission')
				->whereIn('id_module', $moduleIds)
				->get()
				->getResultArray();

			$permissionIds = array_map(static fn ($row) => (int) $row['id_module_permission'], $permissionRows);
			if ($permissionIds) {
				$this->db->table('core_role_module_permission')->whereIn('id_module_permission', $permissionIds)->delete();
				$this->db->table('core_module_permission')->whereIn('id_module_permission', $permissionIds)->delete();
			}

			$this->db->table('core_module')->whereIn('id_module', $moduleIds)->delete();
		}

		$this->forge->dropTable('forex_analysis', true);
		$this->forge->dropTable('forex_price', true);
	}

	/**
	 * Module dibuat idempotent agar migration aman pada database yang sudah
	 * pernah dipasang sebagian atau dijalankan ulang di environment lain.
	 */
	private function upsertModule(string $moduleName, string $moduleTitle): int
	{
		$existing = $this->db->table('core_module')->where('nama_module', $moduleName)->get()->getRowArray();
		$data = [
			'nama_module' => $moduleName,
			'judul_module' => $moduleTitle,
			'id_module_status' => 1,
			'login' => 'Y',
			'deskripsi' => $moduleName === 'forex-monitor'
				? 'Module Forex Monitor untuk monitoring realtime, chart, histori, dan alert GBP/JPY'
				: 'Module Forex Prediction untuk signal, market context, dan prediksi multi-metode GBP/JPY',
		];

		if ($existing) {
			$this->db->table('core_module')->where('id_module', $existing['id_module'])->update($data);
			return (int) $existing['id_module'];
		}

		$this->db->table('core_module')->insert($data);
		return (int) $this->db->insertID();
	}

	/**
	 * Permission standar dijaga seragam dengan module baseapp lain agar pola
	 * otorisasi create, read, update, dan delete tetap mudah dipahami admin.
	 */
	private function registerDefaultPermission(int $moduleId, string $moduleTitle): void
	{
		$permissions = [
			'create' => 'Hak akses untuk menjalankan fetch manual pada ' . $moduleTitle,
			'read_all' => 'Hak akses untuk melihat data ' . $moduleTitle,
			'update_all' => 'Hak akses untuk memperbarui data ' . $moduleTitle,
			'delete_all' => 'Hak akses placeholder agar pola permission ' . $moduleTitle . ' tetap konsisten',
		];

		foreach ($permissions as $name => $description) {
			$existing = $this->db->table('core_module_permission')
				->where('id_module', $moduleId)
				->where('nama_permission', $name)
				->get()
				->getRowArray();

			$data = [
				'id_module' => $moduleId,
				'nama_permission' => $name,
				'judul_permission' => ucwords(str_replace('_', ' ', $name)),
				'keterangan' => $description,
			];

			if ($existing) {
				$this->db->table('core_module_permission')->where('id_module_permission', $existing['id_module_permission'])->update($data);
				continue;
			}

			$this->db->table('core_module_permission')->insert($data);
		}
	}

	/**
	 * Relasi role-permission dibuat terpisah agar migration tetap aman bila
	 * dijalankan ulang pada environment yang sudah memiliki sebagian record.
	 */
	private function assignPermissionToRole(int $roleId, array $moduleIds): void
	{
		$permissions = $this->db->table('core_module_permission')
			->select('id_module_permission')
			->whereIn('id_module', $moduleIds)
			->get()
			->getResultArray();

		foreach ($permissions as $permission) {
			$exists = $this->db->table('core_role_module_permission')
				->where('id_role', $roleId)
				->where('id_module_permission', $permission['id_module_permission'])
				->countAllResults();

			if (!$exists) {
				$this->db->table('core_role_module_permission')->insert([
					'id_role' => $roleId,
					'id_module_permission' => $permission['id_module_permission'],
				]);
			}
		}
	}

	/**
	 * Parent menu forex dipasang sebagai top-level baru setelah urutan menu
	 * teratas terakhir yang sudah ada agar struktur sidebar lama tidak terganggu.
	 */
	private function upsertParentMenu(): int
	{
		$existing = $this->db->table('core_menu')->where('nama_menu', 'Forex')->where('url', '#')->get()->getRowArray();
		$maxOrderRow = $this->db->table('core_menu')
			->select('MAX(urut) AS max_urut', false)
			->where('id_parent IS NULL', null, false)
			->get()
			->getRowArray();
		$targetOrder = ((int) ($maxOrderRow['max_urut'] ?? 0)) + 1;

		$data = [
			'nama_menu' => 'Forex',
			'id_menu_kategori' => 1,
			'class' => 'fas fa-chart-line',
			'url' => '#',
			'id_module' => null,
			'id_parent' => null,
			'aktif' => 1,
			'new' => 0,
			'urut' => $targetOrder,
		];

		if ($existing) {
			$this->db->table('core_menu')->where('id_menu', $existing['id_menu'])->update($data);
			$menuId = (int) $existing['id_menu'];
		} else {
			$this->db->table('core_menu')->insert($data);
			$menuId = (int) $this->db->insertID();
		}

		$this->ensureMenuRole($menuId, 1);
		return $menuId;
	}

	/**
	 * Child menu dipasang di bawah parent Forex agar alur navigasi monitor dan
	 * report tetap rapi sekaligus mudah ditemukan dari sidebar utama.
	 */
	private function upsertChildMenu(string $name, string $url, int $moduleId, int $parentId, int $order): void
	{
		$existing = $this->db->table('core_menu')->where('url', $url)->get()->getRowArray();
		$data = [
			'nama_menu' => $name,
			'id_menu_kategori' => 1,
			'class' => null,
			'url' => $url,
			'id_module' => $moduleId,
			'id_parent' => $parentId,
			'aktif' => 1,
			'new' => 0,
			'urut' => $order,
		];

		if ($existing) {
			$this->db->table('core_menu')->where('id_menu', $existing['id_menu'])->update($data);
			$menuId = (int) $existing['id_menu'];
		} else {
			$this->db->table('core_menu')->insert($data);
			$menuId = (int) $this->db->insertID();
		}

		$this->ensureMenuRole($menuId, 1);
	}

	/**
	 * Relasi menu-role dijaga idempotent agar sidebar Administrator langsung
	 * memunculkan menu forex tanpa duplikasi record saat migrate ulang.
	 */
	private function ensureMenuRole(int $menuId, int $roleId): void
	{
		$exists = $this->db->table('core_menu_role')
			->where('id_menu', $menuId)
			->where('id_role', $roleId)
			->countAllResults();

		if (!$exists) {
			$this->db->table('core_menu_role')->insert([
				'id_menu' => $menuId,
				'id_role' => $roleId,
			]);
		}
	}
}
