<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreWilayahKelurahan extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_wilayah_kelurahan' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
                'null' => false,
            ],
            'id_wilayah_kecamatan' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => false,
            ],
            'nama_kelurahan' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'kode_pos' => [
                'type' => 'CHAR',
                'constraint' => 5,
                'null' => true,
            ],
        ]);

        $this->forge->addKey(['id_wilayah_kelurahan'], true);
        $this->forge->createTable('core_wilayah_kelurahan');
    }

    public function down()
    {
        $this->forge->dropTable('core_wilayah_kelurahan');
    }
}
