<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreUserLoginActivity extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
                'null' => false,
            ],
            'id_user' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => false,
            ],
            'id_activity' => [
                'type' => 'SMALLINT',
                'null' => false,
            ],
            'time' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey(['id'], true);
        $this->forge->createTable('core_user_login_activity');
    }

    public function down()
    {
        $this->forge->dropTable('core_user_login_activity');
    }
}
