<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreMenu extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_menu' => [
                'type' => 'SMALLINT',
                'unsigned' => true,
                'auto_increment' => true,
                'null' => false,
            ],
            'nama_menu' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
            ],
            'id_menu_kategori' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => false,
                'default' => '0',
            ],
            'class' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'url' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'id_module' => [
                'type' => 'SMALLINT',
                'unsigned' => true,
                'null' => true,
            ],
            'id_parent' => [
                'type' => 'SMALLINT',
                'unsigned' => true,
                'null' => true,
            ],
            'aktif' => [
                'type' => 'TINYINT',
                'null' => false,
                'default' => '1',
            ],
            'new' => [
                'type' => 'TINYINT',
                'null' => false,
                'default' => '0',
            ],
            'urut' => [
                'type' => 'TINYINT',
                'null' => false,
                'default' => '0',
            ],
        ]);

        $this->forge->addKey(['id_menu'], true);
        $this->forge->createTable('core_menu');
    }

    public function down()
    {
        $this->forge->dropTable('core_menu');
    }
}
