<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRules extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'json_rule' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'version' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'effective_from' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'is_active' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'komentar_pakar' => [
                'type' => 'TEXT',
                'null' => true
            ]
        ]);

        $this->forge->addKey('id', true);

        $this->forge->createTable('rules');
    }

    public function down()
    {
        $this->forge->dropTable('rules');
    }
}
