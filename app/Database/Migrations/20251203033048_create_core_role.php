<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreRole extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_role' => [
                'type' => 'SMALLINT',
                'unsigned' => true,
                'auto_increment' => true,
                'null' => false,
            ],
            'id_module' => [
                'type' => 'SMALLINT',
                'unsigned' => true,
                'null' => true,
            ],
            'sistem' => [
                'type' => 'ENUM',
                'constraint' => ['core','pos','hrms'],
                'null' => false,
            ],
            'nama_role' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
            ],
            'judul_role' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
            ],
            'keterangan' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
            ],
        ]);

        $this->forge->addKey(['id_role'], true);
        $this->forge->addUniqueKey('nama_role');
        $this->forge->createTable('core_role');
    }

    public function down()
    {
        $this->forge->dropTable('core_role');
    }
}
