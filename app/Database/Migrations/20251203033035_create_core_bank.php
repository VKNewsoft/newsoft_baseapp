<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreBank extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_bank' => [
                'type' => 'BIGINT',
                'auto_increment' => true,
                'null' => false,
            ],
            'nama_bank' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'code_bank' => [
                'type' => 'INT',
                'null' => true,
            ],
        ]);

        $this->forge->addKey(['id_bank'], true);
        $this->forge->createTable('core_bank');
    }

    public function down()
    {
        $this->forge->dropTable('core_bank');
    }
}
