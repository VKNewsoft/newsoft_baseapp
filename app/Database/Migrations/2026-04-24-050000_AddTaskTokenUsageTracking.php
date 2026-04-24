<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTaskTokenUsageTracking extends Migration
{
	public function up()
	{
		// Tabel log token dipisahkan dari task agar riwayat penggunaan AI bisa diaudit tanpa mengubah data task utama.
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `project_task_token_usage` (
				`id_task_token_usage` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
				`task_id` INT(10) UNSIGNED NOT NULL,
				`project_id` INT(10) UNSIGNED NOT NULL,
				`user_id` INT(10) UNSIGNED NOT NULL,
				`token_used` DECIMAL(18,2) NOT NULL DEFAULT 0,
				`usage_type` VARCHAR(30) NOT NULL,
				`notes` TEXT NULL DEFAULT NULL,
				`created_at` DATETIME NOT NULL,
				`updated_at` DATETIME NULL DEFAULT NULL,
				PRIMARY KEY (`id_task_token_usage`),
				KEY `project_task_token_usage_task_idx` (`task_id`),
				KEY `project_task_token_usage_project_idx` (`project_id`),
				KEY `project_task_token_usage_user_idx` (`user_id`),
				KEY `project_task_token_usage_created_idx` (`created_at`),
				KEY `project_task_token_usage_project_user_date_idx` (`project_id`, `user_id`, `created_at`),
				CONSTRAINT `project_task_token_usage_task_foreign` FOREIGN KEY (`task_id`) REFERENCES `project_task` (`id_project_task`) ON DELETE CASCADE ON UPDATE CASCADE,
				CONSTRAINT `project_task_token_usage_project_foreign` FOREIGN KEY (`project_id`) REFERENCES `project` (`id_project`) ON DELETE CASCADE ON UPDATE CASCADE,
				CONSTRAINT `project_task_token_usage_user_foreign` FOREIGN KEY (`user_id`) REFERENCES `core_user` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
		");
	}

	public function down()
	{
		// Rollback cukup melepas tabel log token karena seluruh relasinya bersifat turunan dari task/project.
		$this->forge->dropTable('project_task_token_usage', true);
	}
}
