<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMothers extends Migration
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
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'bb' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'tb' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'umur' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'usia_bayi_bln' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'laktasi_tipe' => [
                'type'       => 'ENUM',
                'constraint' => ['eksklusif', 'parsial'],
                'default'    => 'eksklusif',
            ],
            'aktivitas' => [
                'type'       => 'ENUM',
                'constraint' => ['ringan', 'sedang', 'berat'],
                'default'    => 'ringan',
            ],
            'alergi_json' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'preferensi_json' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'riwayat_json' => [
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
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('mothers');
    }

    public function down()
    {
        $this->forge->dropTable('mothers');
    }
}
