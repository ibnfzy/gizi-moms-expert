<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class User extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $users = [
            [
                'name'          => 'Administrator',
                'email'         => 'admin@example.com',
                'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
                'role'          => 'admin',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'name'          => 'Pakar',
                'email'         => 'pakar@example.com',
                'password_hash' => password_hash('pakar123', PASSWORD_DEFAULT),
                'role'          => 'pakar',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'name'          => 'Ibu',
                'email'         => 'ibu@example.com',
                'password_hash' => password_hash('ibu123', PASSWORD_DEFAULT),
                'role'          => 'ibu',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
        ];

        $this->db->table('users')->insertBatch($users);
    }
}
