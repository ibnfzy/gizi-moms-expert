<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInferenceResults extends Migration
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
            'mother_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'version' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'facts_json' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'fired_rules_json' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'output_json' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('mother_id');
        $this->forge->addForeignKey('mother_id', 'mothers', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('inference_results');
    }

    public function down()
    {
        $this->forge->dropTable('inference_results');
    }
}
