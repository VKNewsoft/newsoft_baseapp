<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreRoleModulePermission extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_role' => [
                'type' => 'SMALLINT',
                'unsigned' => true,
                'null' => false,
            ],
            'id_module_permission' => [
                'type' => 'SMALLINT',
                'unsigned' => true,
                'null' => false,
            ],
        ]);

        $this->forge->addKey(['id_role', 'id_module_permission'], true);
        $this->forge->createTable('core_role_module_permission');
    }

    public function down()
    {
        $this->forge->dropTable('core_role_module_permission');
    }
}
