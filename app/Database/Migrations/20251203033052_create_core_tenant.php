<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreTenant extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_company' => [
                'type' => 'BIGINT',
                'auto_increment' => true,
                'null' => false,
            ],
            'sistem' => [
                'type' => 'ENUM',
                'constraint' => ['pos','hrms','core'],
                'null' => false,
            ],
            'nama_company' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'kode_lokasi' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'slip_dashboard' => [
                'type' => 'TINYINT',
                'null' => false,
                'default' => '1',
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'tarif_per_jkk' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'null' => false,
            ],
            'tarif_per_jkm' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'null' => false,
            ],
            'tarif_per_jht' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'null' => false,
            ],
            'tarif_per_jp' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'null' => false,
            ],
            'tarif_per_kes' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'null' => false,
            ],
            'tarif_kar_jht' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'null' => false,
            ],
            'tarif_kar_jp' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'null' => false,
            ],
            'tarif_kar_kes' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'null' => false,
            ],
            'rev_share' => [
                'type' => 'BIGINT',
                'null' => false,
            ],
            'id_bank' => [
                'type' => 'BIGINT',
                'null' => false,
            ],
            'no_rekening' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
            ],
            'tenant_aktif' => [
                'type' => 'ENUM',
                'constraint' => ['y','n'],
                'null' => false,
                'default' => 'N',
            ],
            'bypass_jhk' => [
                'type' => 'ENUM',
                'constraint' => ['y','n'],
                'null' => false,
                'default' => 'N',
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
                'null' => false,
                'default' => '0',
            ],
            'kode_perusahaan' => [
                'type' => 'VARCHAR',
                'constraint' => 3,
                'null' => true,
            ],
        ]);

        $this->forge->addKey(['id_company'], true);
        $this->forge->createTable('core_company');
    }

    public function down()
    {
        $this->forge->dropTable('core_company');
    }
}
