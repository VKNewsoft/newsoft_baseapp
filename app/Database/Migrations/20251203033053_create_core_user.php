<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreUser extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_user' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
                'null' => false,
            ],
            'id_company' => [
                'type' => 'BIGINT',
                'null' => false,
            ],
            'id_module' => [
                'type' => 'SMALLINT',
                'unsigned' => true,
                'null' => true,
            ],
            'access_company' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'nama' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'verified' => [
                'type' => 'SMALLINT',
                'null' => false,
            ],
            'status' => [
                'type' => 'TINYINT',
                'unsigned' => true,
                'null' => false,
                'default' => '1',
            ],
            'created' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'avatar' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
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
                'null' => false,
                'default' => '0',
            ],
            'id_log' => [
                'type' => 'BIGINT',
                'null' => true,
            ],
        ]);

        $this->forge->addKey(['id_user'], true);
        $this->forge->createTable('core_user');
    }

    public function down()
    {
        $this->forge->dropTable('core_user');
    }
}
