<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreGudang extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_gudang' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
                'null' => false,
            ],
            'nama_gudang' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'alamat_gudang' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'id_wilayah_kelurahan' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'default_gudang' => [
                'type' => 'ENUM',
                'constraint' => ['y','n'],
                'null' => true,
            ],
        ]);

        $this->forge->addKey(['id_gudang'], true);
        $this->forge->createTable('core_gudang');
    }

    public function down()
    {
        $this->forge->dropTable('core_gudang');
    }
}
