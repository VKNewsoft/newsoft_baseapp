<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHrmLogActivityBlock extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'auto_increment' => true,
                'null' => false,
            ],
            'device_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'request_url' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'user_agent' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'is_deleted' => [
                'type' => 'TINYINT',
                'null' => false,
                'default' => '0',
            ],
        ]);

        $this->forge->addKey(['id'], true);
        $this->forge->createTable('hrm_log_activity_block');
    }

    public function down()
    {
        $this->forge->dropTable('hrm_log_activity_block');
    }
}
