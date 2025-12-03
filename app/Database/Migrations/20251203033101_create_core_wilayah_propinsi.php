<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreWilayahPropinsi extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_wilayah_propinsi' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
                'null' => false,
            ],
            'nama_propinsi' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'ibukota' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'p_bsni' => [
                'type' => 'CHAR',
                'constraint' => 5,
                'null' => true,
            ],
        ]);

        $this->forge->addKey(['id_wilayah_propinsi'], true);
        $this->forge->createTable('core_wilayah_propinsi');
    }

    public function down()
    {
        $this->forge->dropTable('core_wilayah_propinsi');
    }
}
