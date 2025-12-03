<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreSetting extends Migration
{
    public function up()
    {
        $this->forge->addField([
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
            'value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);

        $this->forge->addKey(['type', 'param'], true);
        $this->forge->createTable('core_setting');
    }

    public function down()
    {
        $this->forge->dropTable('core_setting');
    }
}
