<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreSettingUser extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_user' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => false,
            ],
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
            ],
            'param' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
        ]);

        $this->forge->addKey(['id_user', 'type'], true);
        $this->forge->createTable('core_setting_user');
    }

    public function down()
    {
        $this->forge->dropTable('core_setting_user');
    }
}
