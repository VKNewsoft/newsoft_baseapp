<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreConfig extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_config' => [
                'type' => 'BIGINT',
                'auto_increment' => true,
                'null' => false,
            ],
            'id_sequence' => [
                'type' => 'BIGINT',
                'null' => true,
            ],
            'param_config' => [
                'type' => 'VARCHAR',
                'constraint' => 765,
                'null' => true,
            ],
            'intval_config' => [
                'type' => 'BIGINT',
                'null' => true,
            ],
            'strval_config' => [
                'type' => 'VARCHAR',
                'constraint' => 300,
                'null' => true,
            ],
            'nama_config' => [
                'type' => 'VARCHAR',
                'constraint' => 765,
                'null' => true,
            ],
            'tipe_config' => [
                'type' => 'VARCHAR',
                'constraint' => 765,
                'null' => true,
            ],
            'permanent' => [
                'type' => 'TINYINT',
                'null' => true,
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'isDeleted' => [
                'type' => 'TINYINT',
                'null' => false,
                'default' => '0',
            ],
        ]);

        $this->forge->addKey(['id_config'], true);
        $this->forge->createTable('core_config');
    }

    public function down()
    {
        $this->forge->dropTable('core_config');
    }
}
