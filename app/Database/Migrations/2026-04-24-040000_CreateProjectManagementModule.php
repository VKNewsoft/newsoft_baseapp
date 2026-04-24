<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProjectManagementModule extends Migration
{
	/**
	 * Menyimpan nama module project agar proses registrasi menu dan permission
	 * tetap konsisten serta mudah dirawat pada saat migration dijalankan ulang.
	 */
	private array $modules = [
		'project' => 'Project',
		'project-category' => 'Project Category',
		'project-member' => 'Project Member',
		'task-management' => 'Task Management',
	];

	public function up()
	{
		// Membuat tabel master kategori project untuk kebutuhan dropdown dan relasi project.
		$this->forge->addField([
			'id_project_category' => [
				'type' => 'INT',
				'constraint' => 10,
				'unsigned' => true,
				'auto_increment' => true,
			],
			'name' => [
				'type' => 'VARCHAR',
				'constraint' => 150,
			],
			'is_deleted' => [
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0,
			],
			'created_at' => [
				'type' => 'DATETIME',
				'null' => true,
			],
			'updated_at' => [
				'type' => 'DATETIME',
				'null' => true,
			],
		]);
		$this->forge->addKey('id_project_category', true);
		$this->forge->addKey('name');
		$this->forge->createTable('project_category', true);

		// Menyimpan data project utama beserta timeline dan kategorinya.
		$this->forge->addField([
			'id_project' => [
				'type' => 'INT',
				'constraint' => 10,
				'unsigned' => true,
				'auto_increment' => true,
			],
			'name' => [
				'type' => 'VARCHAR',
				'constraint' => 150,
			],
			'description' => [
				'type' => 'TEXT',
				'null' => true,
			],
			'start_date' => [
				'type' => 'DATE',
				'null' => true,
			],
			'end_date' => [
				'type' => 'DATE',
				'null' => true,
			],
			'category_id' => [
				'type' => 'INT',
				'constraint' => 10,
				'unsigned' => true,
			],
			'is_deleted' => [
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0,
			],
			'created_at' => [
				'type' => 'DATETIME',
				'null' => true,
			],
			'updated_at' => [
				'type' => 'DATETIME',
				'null' => true,
			],
		]);
		$this->forge->addKey('id_project', true);
		$this->forge->addKey('category_id');
		$this->forge->addKey('start_date');
		$this->forge->addKey('end_date');
		// Urutan parameter foreign key mengikuti format onUpdate lalu onDelete milik CI4.
		$this->forge->addForeignKey('category_id', 'project_category', 'id_project_category', 'CASCADE', 'RESTRICT');
		$this->forge->createTable('project', true);

		// Tabel anggota menjaga agar user yang dapat ditugaskan benar-benar anggota project terkait.
		$this->forge->addField([
			'id_project_member' => [
				'type' => 'INT',
				'constraint' => 10,
				'unsigned' => true,
				'auto_increment' => true,
			],
			'project_id' => [
				'type' => 'INT',
				'constraint' => 10,
				'unsigned' => true,
			],
			'user_id' => [
				'type' => 'INT',
				'constraint' => 10,
				'unsigned' => true,
			],
			'created_at' => [
				'type' => 'DATETIME',
				'null' => true,
			],
			'updated_at' => [
				'type' => 'DATETIME',
				'null' => true,
			],
		]);
		$this->forge->addKey('id_project_member', true);
		$this->forge->addKey(['project_id', 'user_id']);
		$this->forge->addUniqueKey(['project_id', 'user_id']);
		$this->forge->addForeignKey('project_id', 'project', 'id_project', 'CASCADE', 'CASCADE');
		$this->forge->addForeignKey('user_id', 'core_user', 'id_user', 'CASCADE', 'RESTRICT');
		$this->forge->createTable('project_member', true);

		// Tabel task memakai relasi ke anggota project agar assignment selalu tervalidasi di level database.
		$this->forge->addField([
			'id_project_task' => [
				'type' => 'INT',
				'constraint' => 10,
				'unsigned' => true,
				'auto_increment' => true,
			],
			'project_id' => [
				'type' => 'INT',
				'constraint' => 10,
				'unsigned' => true,
			],
			'title' => [
				'type' => 'VARCHAR',
				'constraint' => 150,
			],
			'description' => [
				'type' => 'TEXT',
				'null' => true,
			],
			'assigned_to' => [
				'type' => 'INT',
				'constraint' => 10,
				'unsigned' => true,
			],
			'status' => [
				'type' => 'VARCHAR',
				'constraint' => 30,
				'default' => 'todo',
			],
			'priority' => [
				'type' => 'VARCHAR',
				'constraint' => 30,
				'default' => 'medium',
			],
			'start_date' => [
				'type' => 'DATE',
				'null' => true,
			],
			'end_date' => [
				'type' => 'DATE',
				'null' => true,
			],
			'created_at' => [
				'type' => 'DATETIME',
				'null' => true,
			],
			'updated_at' => [
				'type' => 'DATETIME',
				'null' => true,
			],
		]);
		$this->forge->addKey('id_project_task', true);
		$this->forge->addKey('project_id');
		$this->forge->addKey('assigned_to');
		$this->forge->addKey('status');
		$this->forge->addKey('priority');
		$this->forge->addForeignKey('project_id', 'project', 'id_project', 'CASCADE', 'CASCADE');
		$this->forge->addForeignKey('assigned_to', 'project_member', 'id_project_member', 'CASCADE', 'RESTRICT');
		$this->forge->createTable('project_task', true);

		// Seed kategori awal disiapkan agar form project langsung usable setelah module aktif.
		if (!$this->db->table('project_category')->countAllResults()) {
			$now = date('Y-m-d H:i:s');
			$this->db->table('project_category')->insertBatch([
				['name' => 'Software Development', 'is_deleted' => 0, 'created_at' => $now, 'updated_at' => $now],
				['name' => 'Recruitment', 'is_deleted' => 0, 'created_at' => $now, 'updated_at' => $now],
				['name' => 'Accounting', 'is_deleted' => 0, 'created_at' => $now, 'updated_at' => $now],
				['name' => 'Financial', 'is_deleted' => 0, 'created_at' => $now, 'updated_at' => $now],
			]);
		}

		$moduleIds = [];
		foreach ($this->modules as $moduleName => $moduleTitle) {
			$moduleIds[$moduleName] = $this->upsertModule($moduleName, $moduleTitle);
			$this->registerDefaultPermission($moduleIds[$moduleName]);
		}

		// Permission default diberikan ke Administrator agar modul baru langsung bisa diuji.
		$this->assignPermissionToRole(1, array_values($moduleIds));

		// Menyisipkan menu parent Project tepat di bawah Security Monitor tanpa mengganggu struktur kategori menu lain.
		$securityMenu = $this->db->table('core_menu')->where('url', 'securitymonitor')->get()->getRowArray();
		$securityOrder = (int) ($securityMenu['urut'] ?? 2);
		$projectParentId = $this->upsertParentMenu($securityOrder + 1);

		$this->upsertChildMenu('Project List', 'project', $moduleIds['project'], $projectParentId, 1);
		$this->upsertChildMenu('Category List', 'project-category', $moduleIds['project-category'], $projectParentId, 2);
		$this->upsertChildMenu('Task List', 'task-management', $moduleIds['task-management'], $projectParentId, 3);
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

		// Menghapus menu child lebih dulu agar parent Project bisa dihapus aman.
		$this->db->table('core_menu')->whereIn('url', ['project', 'project-category', 'task-management'])->delete();
		$this->db->table('core_menu')->where('nama_menu', 'Project')->where('url', '#')->delete();

		if ($moduleIds) {
			$permissionRows = $this->db->table('core_module_permission')
				->select('id_module_permission')
				->whereIn('id_module', $moduleIds)
				->get()
				->getResultArray();

			$permissionIds = array_map(static fn ($row) => (int) $row['id_module_permission'], $permissionRows);
			if ($permissionIds) {
				$this->db->table('core_role_module_permission')->whereIn('id_module_permission', $permissionIds)->delete();
			}

			$this->db->table('core_module')->whereIn('id_module', $moduleIds)->delete();
		}

		$this->forge->dropTable('project_task', true);
		$this->forge->dropTable('project_member', true);
		$this->forge->dropTable('project', true);
		$this->forge->dropTable('project_category', true);
	}

	/**
	 * Menjaga data module tetap idempotent agar migration aman dijalankan di environment yang sudah terpasang.
	 */
	private function upsertModule(string $moduleName, string $moduleTitle): int
	{
		$existing = $this->db->table('core_module')->where('nama_module', $moduleName)->get()->getRowArray();
		$data = [
			'nama_module' => $moduleName,
			'judul_module' => $moduleTitle,
			'id_module_status' => 1,
			'login' => 'Y',
			'deskripsi' => 'Module ' . $moduleTitle . ' untuk manajemen project dan task',
		];

		if ($existing) {
			$this->db->table('core_module')->where('id_module', $existing['id_module'])->update($data);
			return (int) $existing['id_module'];
		}

		$this->db->table('core_module')->insert($data);
		return (int) $this->db->insertID();
	}

	/**
	 * Permission standar dipasang supaya pola otorisasi mengikuti module lain di baseapp.
	 */
	private function registerDefaultPermission(int $moduleId): void
	{
		$permissions = [
			'create' => 'Hak akses untuk menambah data project',
			'read_all' => 'Hak akses untuk membaca seluruh data project',
			'update_all' => 'Hak akses untuk mengubah seluruh data project',
			'delete_all' => 'Hak akses untuk menghapus seluruh data project',
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
	 * Role Administrator dihubungkan ke permission module baru agar menu dapat langsung diakses.
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
	 * Menu parent Project diposisikan setelah Security Monitor dengan cara menggeser urut menu top-level yang ada.
	 */
	private function upsertParentMenu(int $targetOrder): int
	{
		$existing = $this->db->table('core_menu')->where('nama_menu', 'Project')->where('url', '#')->get()->getRowArray();

		if (!$existing) {
			$this->db->table('core_menu')
				->set('urut', 'urut + 1', false)
				->where('id_parent IS NULL', null, false)
				->where('urut >=', $targetOrder)
				->update();
		}

		$data = [
			'nama_menu' => 'Project',
			'id_menu_kategori' => 1,
			'class' => 'fas fa-diagram-project',
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
	 * Menu child dihubungkan ke parent Project supaya highlight sidebar mengikuti struktur menu utama.
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
	 * Relasi menu-role dijaga terpisah agar migration tetap aman saat dijalankan ulang.
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
