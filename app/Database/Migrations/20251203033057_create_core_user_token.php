<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreUserToken extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'selector' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'token' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'action' => [
                'type' => 'ENUM',
                'constraint' => ['register','remember','recovery','activation'],
                'null' => false,
            ],
            'id_user' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => false,
            ],
            'created' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'expires' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey(['selector'], true);
        $this->forge->createTable('core_user_token');
    }

    public function down()
    {
        $this->forge->dropTable('core_user_token');
    }
}
