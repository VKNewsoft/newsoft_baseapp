<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreFilePicker extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_file_picker' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
                'null' => false,
            ],
            'id_company' => [
                'type' => 'BIGINT',
                'null' => false,
                'default' => '0',
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'caption' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'alt_text' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'nama_file' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'mime_type' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'size' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => false,
            ],
            'tgl_upload' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'id_user_upload' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => false,
            ],
            'meta_file' => [
                'type' => 'TEXT',
                'null' => false,
            ],
        ]);

        $this->forge->addKey(['id_file_picker'], true);
        $this->forge->createTable('core_file_picker');
    }

    public function down()
    {
        $this->forge->dropTable('core_file_picker');
    }
}
