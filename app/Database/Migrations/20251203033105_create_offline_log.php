<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOfflineLog extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_offlog' => [
                'type' => 'BIGINT',
                'auto_increment' => true,
                'null' => false,
            ],
            'tgl_down' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'tgl_up' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey(['id_offlog'], true);
        $this->forge->createTable('offline_log');
    }

    public function down()
    {
        $this->forge->dropTable('offline_log');
    }
}
