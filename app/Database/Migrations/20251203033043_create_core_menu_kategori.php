<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreMenuKategori extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_menu_kategori' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
                'null' => false,
            ],
            'nama_kategori' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'deskripsi' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'aktif' => [
                'type' => 'ENUM',
                'constraint' => ['y','n'],
                'null' => true,
            ],
            'show_title' => [
                'type' => 'ENUM',
                'constraint' => ['y','n'],
                'null' => true,
            ],
            'urut' => [
                'type' => 'TINYINT',
                'unsigned' => true,
                'null' => true,
            ],
        ]);

        $this->forge->addKey(['id_menu_kategori'], true);
        $this->forge->createTable('core_menu_kategori');
    }

    public function down()
    {
        $this->forge->dropTable('core_menu_kategori');
    }
}
