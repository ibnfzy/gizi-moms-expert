<?php

namespace App\Controllers;

class AdminDashboardController extends BaseController
{
    public function index(): string
    {
        $stats = [
            [
                'badgeText' => 'Jumlah User',
                'title' => '1.250',
                'description' => 'Total pengguna yang telah terdaftar.',
            ],
            [
                'badgeText' => 'Jumlah Rule',
                'title' => '87',
                'description' => 'Rule aktif dalam basis pengetahuan.',
            ],
            [
                'badgeText' => 'Total Inferensi',
                'title' => '342',
                'description' => 'Sesi inferensi yang telah dijalankan.',
            ],
        ];

        $rules = [
            [
                'id' => 'R-120',
                'name' => 'Peningkatan Asupan Protein',
                'category' => 'Nutrisi',
                'date' => '12 Mei 2024',
                'status' => 'Aktif',
            ],
            [
                'id' => 'R-119',
                'name' => 'Monitoring Cairan Harian',
                'category' => 'Hidrasi',
                'date' => '10 Mei 2024',
                'status' => 'Draft',
            ],
            [
                'id' => 'R-118',
                'name' => 'Evaluasi Berat Badan Ibu',
                'category' => 'Evaluasi',
                'date' => '8 Mei 2024',
                'status' => 'Aktif',
            ],
            [
                'id' => 'R-117',
                'name' => 'Konsumsi Omega-3',
                'category' => 'Suplementasi',
                'date' => '5 Mei 2024',
                'status' => 'Aktif',
            ],
            [
                'id' => 'R-116',
                'name' => 'Jadwal Konsultasi Mingguan',
                'category' => 'Pendampingan',
                'date' => '2 Mei 2024',
                'status' => 'Ditinjau',
            ],
        ];

        $statusClasses = [
            'Aktif' => 'bg-blue-50 text-blue-700',
            'Draft' => 'bg-yellow-50 text-yellow-700',
            'Ditinjau' => 'bg-purple-50 text-purple-700',
        ];

        return view('admin/dashboard', [
            'stats' => $stats,
            'rules' => $rules,
            'statusClasses' => $statusClasses,
        ]);
    }
}
