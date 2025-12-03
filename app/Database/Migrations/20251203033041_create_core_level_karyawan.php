<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreLevelKaryawan extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_lvl_karyawan' => [
                'type' => 'BIGINT',
                'auto_increment' => true,
                'null' => false,
            ],
            'id_company' => [
                'type' => 'BIGINT',
                'null' => true,
            ],
            'nama_level' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'gaji_terlapor' => [
                'type' => 'BIGINT',
                'null' => true,
            ],
            'tgl_input' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'id_user_input' => [
                'type' => 'BIGINT',
                'null' => true,
            ],
            'tgl_edit' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'id_user_edit' => [
                'type' => 'BIGINT',
                'null' => true,
            ],
            'isDeleted' => [
                'type' => 'TINYINT',
                'null' => true,
                'default' => '0',
            ],
        ]);

        $this->forge->addKey(['id_lvl_karyawan'], true);
        $this->forge->createTable('core_level_karyawan');
    }

    public function down()
    {
        $this->forge->dropTable('core_level_karyawan');
    }
}
