<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLogFirebaseNotifications extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'auto_increment' => true,
                'null' => false,
            ],
            'response' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'title' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'payload' => [
                'type' => 'TEXT',
                'null' => false,
            ],
        ]);

        $this->forge->addKey(['id'], true);
        $this->forge->createTable('log_firebase_notifications');
    }

    public function down()
    {
        $this->forge->dropTable('log_firebase_notifications');
    }
}
