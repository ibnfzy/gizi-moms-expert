<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Rules extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $rules = [
            [
                'name'            => 'Peningkatan Asupan Protein',
                'json_rule'       => json_encode([
                    'condition'      => 'Ibu mengalami peningkatan kebutuhan protein',
                    'recommendation' => 'Tambahkan 25 gram protein setiap hari',
                    'category'       => 'Nutrisi',
                    'status'         => 'Aktif',
                ], JSON_UNESCAPED_UNICODE),
                'version'         => '1.0',
                'effective_from'  => '2024-05-12',
                'is_active'       => true,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'name'            => 'Monitoring Cairan Harian',
                'json_rule'       => json_encode([
                    'condition'      => 'Ibu mengalami tanda-tanda dehidrasi ringan',
                    'recommendation' => 'Catat asupan air minimal 8 gelas per hari',
                    'category'       => 'Hidrasi',
                    'status'         => 'Draft',
                ], JSON_UNESCAPED_UNICODE),
                'version'         => '1.1',
                'effective_from'  => '2024-05-10',
                'is_active'       => false,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'name'            => 'Evaluasi Berat Badan Ibu',
                'json_rule'       => json_encode([
                    'condition'      => 'Kenaikan berat badan tidak sesuai usia kehamilan',
                    'recommendation' => 'Lakukan evaluasi setiap dua minggu sekali',
                    'category'       => 'Evaluasi',
                    'status'         => 'Aktif',
                ], JSON_UNESCAPED_UNICODE),
                'version'         => '1.0',
                'effective_from'  => '2024-05-08',
                'is_active'       => true,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'name'            => 'Konsumsi Omega-3',
                'json_rule'       => json_encode([
                    'condition'      => 'Ibu dengan kadar trigliserida tinggi',
                    'recommendation' => 'Rekomendasikan konsumsi omega-3 dua kali seminggu',
                    'category'       => 'Suplementasi',
                    'status'         => 'Aktif',
                ], JSON_UNESCAPED_UNICODE),
                'version'         => '1.0',
                'effective_from'  => '2024-05-05',
                'is_active'       => true,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'name'            => 'Jadwal Konsultasi Mingguan',
                'json_rule'       => json_encode([
                    'condition'      => 'Ibu dengan risiko kehamilan tinggi',
                    'recommendation' => 'Jadwalkan konsultasi mingguan dengan tenaga kesehatan',
                    'category'       => 'Pendampingan',
                    'status'         => 'Ditinjau',
                ], JSON_UNESCAPED_UNICODE),
                'version'         => '1.2',
                'effective_from'  => '2024-05-02',
                'is_active'       => false,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
        ];

        $this->db->table('rules')->insertBatch($rules);
    }
}
