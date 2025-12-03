<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreKategori extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_kategori' => [
                'type' => 'SMALLINT',
                'unsigned' => true,
                'auto_increment' => true,
                'null' => false,
            ],
            'judul_kategori' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
        ]);

        $this->forge->addKey(['id_kategori'], true);
        $this->forge->createTable('core_kategori');
    }

    public function down()
    {
        $this->forge->dropTable('core_kategori');
    }
}
