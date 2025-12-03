<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreUserRole extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_user' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => false,
            ],
            'id_role' => [
                'type' => 'SMALLINT',
                'unsigned' => true,
                'null' => false,
            ],
        ]);

        $this->forge->addKey(['id_user', 'id_role'], true);
        $this->forge->createTable('core_user_role');
    }

    public function down()
    {
        $this->forge->dropTable('core_user_role');
    }
}
