<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreWilayahKabupaten extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_wilayah_kabupaten' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
                'null' => false,
            ],
            'id_wilayah_propinsi' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => false,
            ],
            'nama_kabupaten' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'ibukota' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'k_bsni' => [
                'type' => 'CHAR',
                'constraint' => 3,
                'null' => true,
            ],
        ]);

        $this->forge->addKey(['id_wilayah_kabupaten'], true);
        $this->forge->createTable('core_wilayah_kabupaten');
    }

    public function down()
    {
        $this->forge->dropTable('core_wilayah_kabupaten');
    }
}
