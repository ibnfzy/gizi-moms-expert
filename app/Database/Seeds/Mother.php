<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Mother extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $mothers = [
            [
                'email'           => 'sari@example.com',
                'bb'              => 58.5,
                'tb'              => 162.0,
                'umur'            => 29,
                'usia_bayi_bln'   => 6,
                'laktasi_tipe'    => 'eksklusif',
                'aktivitas'       => 'sedang',
                'alergi'          => ['kacang'],
                'preferensi'      => ['sayur hijau', 'susu rendah lemak'],
                'riwayat_penyakit'=> ['anemia'],
            ],
            [
                'email'           => 'dewi@example.com',
                'bb'              => 62.0,
                'tb'              => 158.0,
                'umur'            => 32,
                'usia_bayi_bln'   => 4,
                'laktasi_tipe'    => 'parsial',
                'aktivitas'       => 'ringan',
                'alergi'          => ['seafood'],
                'preferensi'      => ['buah-buahan', 'yoghurt'],
                'riwayat_penyakit'=> ['hipertensi ringan'],
            ],
            [
                'email'           => 'ayu@example.com',
                'bb'              => 55.2,
                'tb'              => 165.0,
                'umur'            => 27,
                'usia_bayi_bln'   => 9,
                'laktasi_tipe'    => 'eksklusif',
                'aktivitas'       => 'ringan',
                'alergi'          => [],
                'preferensi'      => ['protein tinggi', 'buah tropis'],
                'riwayat_penyakit'=> [],
            ],
            [
                'email'           => 'rina@example.com',
                'bb'              => 60.8,
                'tb'              => 160.0,
                'umur'            => 31,
                'usia_bayi_bln'   => 2,
                'laktasi_tipe'    => 'parsial',
                'aktivitas'       => 'sedang',
                'alergi'          => ['telur'],
                'preferensi'      => ['makanan rumahan', 'sup'],
                'riwayat_penyakit'=> ['gastritis'],
            ],
            [
                'email'           => 'maya@example.com',
                'bb'              => 57.0,
                'tb'              => 163.0,
                'umur'            => 30,
                'usia_bayi_bln'   => 7,
                'laktasi_tipe'    => 'eksklusif',
                'aktivitas'       => 'ringan',
                'alergi'          => ['udang'],
                'preferensi'      => ['sayuran kukus', 'ikan laut'],
                'riwayat_penyakit'=> ['tiroid'],
            ],
        ];

        foreach ($mothers as $mother) {
            $user = $this->db->table('users')->where('email', $mother['email'])->get()->getRowArray();

            if (! $user) {
                continue;
            }

            $this->db->table('mothers')->insert([
                'user_id'         => $user['id'],
                'bb'              => $mother['bb'],
                'tb'              => $mother['tb'],
                'umur'            => $mother['umur'],
                'usia_bayi_bln'   => $mother['usia_bayi_bln'],
                'laktasi_tipe'    => $mother['laktasi_tipe'],
                'aktivitas'       => $mother['aktivitas'],
                'alergi_json'     => json_encode($mother['alergi'], JSON_UNESCAPED_UNICODE),
                'preferensi_json' => json_encode($mother['preferensi'], JSON_UNESCAPED_UNICODE),
                'riwayat_json'    => json_encode($mother['riwayat_penyakit'], JSON_UNESCAPED_UNICODE),
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }
    }
}
