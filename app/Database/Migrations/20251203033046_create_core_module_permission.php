<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreModulePermission extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_module_permission' => [
                'type' => 'SMALLINT',
                'unsigned' => true,
                'auto_increment' => true,
                'null' => false,
            ],
            'id_module' => [
                'type' => 'SMALLINT',
                'unsigned' => true,
                'null' => false,
                'default' => '0',
            ],
            'nama_permission' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
            ],
            'judul_permission' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'keterangan' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
        ]);

        $this->forge->addKey(['id_module_permission'], true);
        $this->forge->createTable('core_module_permission');
    }

    public function down()
    {
        $this->forge->dropTable('core_module_permission');
    }
}
