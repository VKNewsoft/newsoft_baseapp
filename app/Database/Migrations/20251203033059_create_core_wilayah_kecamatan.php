<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreWilayahKecamatan extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_wilayah_kecamatan' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
                'null' => false,
            ],
            'id_wilayah_kabupaten' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => false,
            ],
            'nama_kecamatan' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
        ]);

        $this->forge->addKey(['id_wilayah_kecamatan'], true);
        $this->forge->createTable('core_wilayah_kecamatan');
    }

    public function down()
    {
        $this->forge->dropTable('core_wilayah_kecamatan');
    }
}
