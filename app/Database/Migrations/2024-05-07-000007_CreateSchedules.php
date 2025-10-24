<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSchedules extends Migration
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
            'expert_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'scheduled_at' => [
                'type' => 'DATETIME',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'confirmed', 'completed', 'cancelled'],
                'default'    => 'pending',
            ],
            'location' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'reminder_sent' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'attendance' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'confirmed', 'declined'],
                'default'    => 'pending',
            ],
            'evaluation_json' => [
                'type' => 'LONGTEXT',
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
        $this->forge->addKey('expert_id');
        $this->forge->addForeignKey('mother_id', 'mothers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('expert_id', 'users', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('schedules');
    }

    public function down()
    {
        $this->forge->dropTable('schedules');
    }
}
