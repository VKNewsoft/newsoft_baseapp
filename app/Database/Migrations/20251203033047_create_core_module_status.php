<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreModuleStatus extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_module_status' => [
                'type' => 'TINYINT',
                'auto_increment' => true,
                'null' => false,
            ],
            'nama_status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'keterangan' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
        ]);

        $this->forge->addKey(['id_module_status'], true);
        $this->forge->createTable('core_module_status');
    }

    public function down()
    {
        $this->forge->dropTable('core_module_status');
    }
}
