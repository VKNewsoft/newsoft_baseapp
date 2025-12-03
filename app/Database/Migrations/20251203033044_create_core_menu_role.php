<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreMenuRole extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_menu' => [
                'type' => 'SMALLINT',
                'unsigned' => true,
                'null' => false,
            ],
            'id_role' => [
                'type' => 'SMALLINT',
                'unsigned' => true,
                'null' => false,
            ],
        ]);

        $this->forge->createTable('core_menu_role');
    }

    public function down()
    {
        $this->forge->dropTable('core_menu_role');
    }
}
