<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateForexDashboardAlertModule extends Migration
{
	public function up()
	{
		// Snapshot live price disimpan terpisah agar polling monitor cukup
		// membaca satu baris terbaru tanpa menghitung ulang dari histori harian.
		$this->forge->addField([
			'id_forex_live_price' => [
				'type' => 'INT',
				'constraint' => 10,
				'unsigned' => true,
				'auto_increment' => true,
			],
			'pair' => [
				'type' => 'VARCHAR',
				'constraint' => 20,
			],
			'current_price' => [
				'type' => 'DECIMAL',
				'constraint' => '18,6',
				'default' => 0,
			],
			'previous_price' => [
				'type' => 'DECIMAL',
				'constraint' => '18,6',
				'null' => true,
			],
			'change_amount' => [
				'type' => 'DECIMAL',
				'constraint' => '18,6',
				'default' => 0,
			],
			'change_percent' => [
				'type' => 'DECIMAL',
				'constraint' => '18,6',
				'default' => 0,
			],
			'day_open' => [
				'type' => 'DECIMAL',
				'constraint' => '18,6',
				'default' => 0,
			],
			'day_high' => [
				'type' => 'DECIMAL',
				'constraint' => '18,6',
				'default' => 0,
			],
			'day_low' => [
				'type' => 'DECIMAL',
				'constraint' => '18,6',
				'default' => 0,
			],
			'provider' => [
				'type' => 'VARCHAR',
				'constraint' => 100,
			],
			'source_type' => [
				'type' => 'VARCHAR',
				'constraint' => 40,
			],
			'quote_time' => [
				'type' => 'DATETIME',
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
		$this->forge->addKey('id_forex_live_price', true);
		$this->forge->addUniqueKey('pair');
		$this->forge->addKey('quote_time');
		$this->forge->createTable('forex_live_price', true);

		// Master alert menyimpan target per user supaya monitor dapat mengecek
		// crossing threshold pada setiap refresh tanpa membaca konfigurasi lain.
		$this->forge->addField([
			'id_forex_alert' => [
				'type' => 'INT',
				'constraint' => 10,
				'unsigned' => true,
				'auto_increment' => true,
			],
			'pair' => [
				'type' => 'VARCHAR',
				'constraint' => 20,
			],
			'user_id' => [
				'type' => 'INT',
				'constraint' => 10,
				'unsigned' => true,
			],
			'condition_type' => [
				'type' => 'VARCHAR',
				'constraint' => 20,
			],
			'target_price' => [
				'type' => 'DECIMAL',
				'constraint' => '18,6',
			],
			'with_sound' => [
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0,
			],
			'is_active' => [
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 1,
			],
			'last_checked_price' => [
				'type' => 'DECIMAL',
				'constraint' => '18,6',
				'null' => true,
			],
			'triggered_at' => [
				'type' => 'DATETIME',
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
		$this->forge->addKey('id_forex_alert', true);
		$this->forge->addKey(['pair', 'user_id']);
		$this->forge->addKey('is_active');
		$this->forge->addForeignKey('user_id', 'core_user', 'id_user', 'RESTRICT', 'CASCADE');
		$this->forge->createTable('forex_alert', true);

		// Histori trigger dipisahkan agar penghapusan alert master tidak menghilang-
		// kan jejak notifikasi yang sebelumnya sudah pernah terjadi.
		$this->forge->addField([
			'id_forex_alert_history' => [
				'type' => 'INT',
				'constraint' => 10,
				'unsigned' => true,
				'auto_increment' => true,
			],
			'alert_id' => [
				'type' => 'INT',
				'constraint' => 10,
				'unsigned' => true,
				'null' => true,
			],
			'pair' => [
				'type' => 'VARCHAR',
				'constraint' => 20,
			],
			'user_id' => [
				'type' => 'INT',
				'constraint' => 10,
				'unsigned' => true,
			],
			'condition_type' => [
				'type' => 'VARCHAR',
				'constraint' => 20,
			],
			'target_price' => [
				'type' => 'DECIMAL',
				'constraint' => '18,6',
			],
			'triggered_price' => [
				'type' => 'DECIMAL',
				'constraint' => '18,6',
			],
			'with_sound' => [
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0,
			],
			'message' => [
				'type' => 'TEXT',
				'null' => true,
			],
			'created_at' => [
				'type' => 'DATETIME',
				'null' => true,
			],
		]);
		$this->forge->addKey('id_forex_alert_history', true);
		$this->forge->addKey(['pair', 'user_id']);
		$this->forge->addKey('created_at');
		$this->forge->addForeignKey('alert_id', 'forex_alert', 'id_forex_alert', 'SET NULL', 'CASCADE');
		$this->forge->addForeignKey('user_id', 'core_user', 'id_user', 'RESTRICT', 'CASCADE');
		$this->forge->createTable('forex_alert_history', true);
	}

	public function down()
	{
		$this->forge->dropTable('forex_alert_history', true);
		$this->forge->dropTable('forex_alert', true);
		$this->forge->dropTable('forex_live_price', true);
	}
}
