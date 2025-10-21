<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Rules extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        $effectiveFrom = date('Y-m-d');

        $rules = [
            [
                'name'            => 'R1: Laktasi Eksklusif',
                'json_rule'       => json_encode([
                    'condition' => 'Laktasi eksklusif',
                    'recommendation' => '+500 kcal',
                ], JSON_UNESCAPED_UNICODE),
                'version'         => '1.0',
                'effective_from'  => $effectiveFrom,
                'is_active'       => true,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'name'            => 'R2: Anemia',
                'json_rule'       => json_encode([
                    'condition' => 'Anemia',
                    'recommendation' => 'Fokus Fe',
                ], JSON_UNESCAPED_UNICODE),
                'version'         => '1.0',
                'effective_from'  => $effectiveFrom,
                'is_active'       => true,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
        ];

        $this->db->table('rules')->insertBatch($rules);
    }
}
