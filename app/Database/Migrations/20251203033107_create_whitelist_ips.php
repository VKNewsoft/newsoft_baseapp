<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWhitelistIps extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'auto_increment' => true,
                'null' => false,
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => false,
            ],
            'blocked_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey(['id'], true);
        $this->forge->addUniqueKey('ip_address');
        $this->forge->createTable('whitelist_ips');
    }

    public function down()
    {
        $this->forge->dropTable('whitelist_ips');
    }
}
