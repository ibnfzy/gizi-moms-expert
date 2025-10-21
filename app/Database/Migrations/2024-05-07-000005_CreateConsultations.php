<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConsultations extends Migration
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
            'pakar_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'ongoing', 'completed'],
                'default'    => 'pending',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('mother_id');
        $this->forge->addKey('pakar_id');
        $this->forge->addForeignKey('mother_id', 'mothers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('pakar_id', 'users', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('consultations');
    }

    public function down()
    {
        $this->forge->dropTable('consultations');
    }
}
