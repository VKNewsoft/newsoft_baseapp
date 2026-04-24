<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnsureProjectRelationTables extends Migration
{
	public function up()
	{
		// Foreign key kategori pada tabel project diperbaiki agar delete kategori ditahan oleh database.
		$this->db->query('ALTER TABLE `project` DROP FOREIGN KEY `project_category_id_foreign`');
		$this->db->query('ALTER TABLE `project` ADD CONSTRAINT `project_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `project_category` (`id_project_category`) ON DELETE RESTRICT ON UPDATE CASCADE');

		// Tabel relasi anggota dibuat ulang dengan tipe user_id mengikuti core_user.id_user yang bertipe INT UNSIGNED.
		$this->db->query('
			CREATE TABLE IF NOT EXISTS `project_member` (
				`id_project_member` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
				`project_id` INT(10) UNSIGNED NOT NULL,
				`user_id` INT(10) UNSIGNED NOT NULL,
				`created_at` DATETIME NULL DEFAULT NULL,
				`updated_at` DATETIME NULL DEFAULT NULL,
				PRIMARY KEY (`id_project_member`),
				UNIQUE KEY `project_member_project_user_unique` (`project_id`,`user_id`),
				KEY `project_member_project_id_idx` (`project_id`),
				KEY `project_member_user_id_idx` (`user_id`),
				CONSTRAINT `project_member_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `project` (`id_project`) ON DELETE CASCADE ON UPDATE CASCADE,
				CONSTRAINT `project_member_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `core_user` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
		');

		// Tabel task dibuat sesudah project_member agar foreign key assigned_to selalu valid.
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `project_task` (
				`id_project_task` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
				`project_id` INT(10) UNSIGNED NOT NULL,
				`title` VARCHAR(150) NOT NULL,
				`description` TEXT NULL DEFAULT NULL,
				`assigned_to` INT(10) UNSIGNED NOT NULL,
				`status` VARCHAR(30) NOT NULL DEFAULT 'todo',
				`priority` VARCHAR(30) NOT NULL DEFAULT 'medium',
				`start_date` DATE NULL DEFAULT NULL,
				`end_date` DATE NULL DEFAULT NULL,
				`created_at` DATETIME NULL DEFAULT NULL,
				`updated_at` DATETIME NULL DEFAULT NULL,
				PRIMARY KEY (`id_project_task`),
				KEY `project_task_project_id_idx` (`project_id`),
				KEY `project_task_assigned_to_idx` (`assigned_to`),
				KEY `project_task_status_idx` (`status`),
				KEY `project_task_priority_idx` (`priority`),
				CONSTRAINT `project_task_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `project` (`id_project`) ON DELETE CASCADE ON UPDATE CASCADE,
				CONSTRAINT `project_task_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `project_member` (`id_project_member`) ON DELETE RESTRICT ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
		");
	}

	public function down()
	{
		// Rollback hanya melepas tabel relasi tambahan agar migration tetap fokus pada perbaikan schema.
		$this->forge->dropTable('project_task', true);
		$this->forge->dropTable('project_member', true);
		$this->db->query('ALTER TABLE `project` DROP FOREIGN KEY `project_category_id_foreign`');
		$this->db->query('ALTER TABLE `project` ADD CONSTRAINT `project_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `project_category` (`id_project_category`) ON DELETE CASCADE ON UPDATE CASCADE');
	}
}
