<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreIdentitas extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_identitas' => [
                'type' => 'BIGINT',
                'auto_increment' => true,
                'null' => false,
            ],
            'id_company' => [
                'type' => 'BIGINT',
                'null' => false,
            ],
            'nama' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'alamat' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'id_wilayah_kelurahan' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'no_telp' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'url_website' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
        ]);

        $this->forge->addKey(['id_identitas'], true);
        $this->forge->createTable('core_identitas');
    }

    public function down()
    {
        $this->forge->dropTable('core_identitas');
    }
}
