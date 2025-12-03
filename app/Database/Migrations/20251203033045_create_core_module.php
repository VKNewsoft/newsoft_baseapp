<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreModule extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_module' => [
                'type' => 'SMALLINT',
                'unsigned' => true,
                'auto_increment' => true,
                'null' => false,
            ],
            'nama_module' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'judul_module' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'id_module_status' => [
                'type' => 'TINYINT',
                'null' => true,
            ],
            'login' => [
                'type' => 'ENUM',
                'constraint' => ['y','n','r'],
                'null' => false,
                'default' => 'Y',
            ],
            'deskripsi' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
        ]);

        $this->forge->addKey(['id_module'], true);
        $this->forge->addUniqueKey('nama_module');
        $this->forge->createTable('core_module');
    }

    public function down()
    {
        $this->forge->dropTable('core_module');
    }
}
