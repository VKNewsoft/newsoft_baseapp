<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreUserDevice extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_user_device' => [
                'type' => 'BIGINT',
                'auto_increment' => true,
                'null' => false,
            ],
            'token_fcm' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'device_info' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'tgl_input' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'id_user' => [
                'type' => 'BIGINT',
                'null' => false,
            ],
            'id_user_input' => [
                'type' => 'BIGINT',
                'null' => true,
            ],
            'tgl_edit' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'id_user_edit' => [
                'type' => 'BIGINT',
                'null' => true,
            ],
            'isDeleted' => [
                'type' => 'TINYINT',
                'null' => true,
                'default' => '0',
            ],
        ]);

        $this->forge->addKey(['id_user_device'], true);
        $this->forge->createTable('core_user_device');
    }

    public function down()
    {
        $this->forge->dropTable('core_user_device');
    }
}
