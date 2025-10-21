<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$rules = [
    ['id' => 'R-120', 'name' => 'Peningkatan Asupan Protein', 'category' => 'Nutrisi', 'date' => '12 Mei 2024', 'status' => 'Aktif'],
    ['id' => 'R-119', 'name' => 'Monitoring Cairan Harian', 'category' => 'Hidrasi', 'date' => '10 Mei 2024', 'status' => 'Draft'],
    ['id' => 'R-118', 'name' => 'Evaluasi Berat Badan Ibu', 'category' => 'Evaluasi', 'date' => '8 Mei 2024', 'status' => 'Aktif'],
    ['id' => 'R-117', 'name' => 'Konsumsi Omega-3', 'category' => 'Suplementasi', 'date' => '5 Mei 2024', 'status' => 'Aktif'],
    ['id' => 'R-116', 'name' => 'Jadwal Konsultasi Mingguan', 'category' => 'Pendampingan', 'date' => '2 Mei 2024', 'status' => 'Ditinjau'],
];

$statusClasses = [
    'Aktif' => 'bg-blue-50 text-blue-700',
    'Draft' => 'bg-yellow-50 text-yellow-700',
    'Ditinjau' => 'bg-purple-50 text-purple-700',
];

$ruleRows = array_map(static function ($rule) use ($statusClasses) {
    $statusClass = $statusClasses[$rule['status']] ?? 'bg-gray-100 text-gray-700';

    return [
        'cells' => [
            [
                'content' => $rule['id'],
                'class' => 'font-medium text-gray-900',
            ],
            [
                'content' => $rule['name'],
                'class' => 'text-gray-700',
            ],
            [
                'content' => $rule['category'],
                'class' => 'text-gray-500',
            ],
            [
                'content' => $rule['date'],
                'class' => 'text-gray-500',
            ],
            [
                'raw' => true,
                'content' => '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ' . $statusClass . '">' . esc($rule['status']) . '</span>',
            ],
        ],
    ];
}, $rules);
?>

<div class="space-y-8">
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?= view('components/card', [
            'badgeText' => 'Jumlah User',
            'title' => '1.250',
            'description' => 'Total pengguna yang telah terdaftar.',
        ]) ?>

        <?= view('components/card', [
            'badgeText' => 'Jumlah Rule',
            'title' => '87',
            'description' => 'Rule aktif dalam basis pengetahuan.',
        ]) ?>

        <?= view('components/card', [
            'badgeText' => 'Total Inferensi',
            'title' => '342',
            'description' => 'Sesi inferensi yang telah dijalankan.',
        ]) ?>
    </section>

    <section class="space-y-4">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Manajemen Rule</h2>
                <p class="text-sm text-gray-500">Kelola daftar rule terbaru pada basis pengetahuan.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <?= view('components/modal', [
                    'id' => 'create-rule-modal',
                    'title' => 'Tambah Rule Baru',
                    'trigger' => [
                        'label' => 'Tambah Rule',
                        'variant' => 'primary',
                    ],
                    'content' => [
                        'Form penambahan rule akan tersedia pada pembaruan berikutnya.',
                        'Sementara ini, Anda dapat menyiapkan data rule yang ingin ditambahkan.',
                    ],
                    'actions' => [
                        [
                            'label' => 'Pelajari Dokumentasi',
                            'variant' => 'primary',
                            'href' => '#',
                        ],
                        [
                            'label' => 'Tutup',
                            'variant' => 'danger',
                            'closesModal' => true,
                        ],
                    ],
                ]) ?>
                <?= view('components/button', [
                    'label' => 'Hapus Rule Kadaluarsa',
                    'variant' => 'danger',
                    'attributes' => [
                        'onclick' => "alert('Fitur pembersihan akan segera hadir.')",
                    ],
                ]) ?>
            </div>
        </div>

        <?= view('components/table', [
            'title' => 'Rule Terbaru',
            'description' => 'Daftar lima rule terakhir yang ditambahkan.',
            'headers' => [
                ['label' => 'ID Rule'],
                ['label' => 'Nama'],
                ['label' => 'Kategori'],
                ['label' => 'Tanggal'],
                ['label' => 'Status'],
            ],
            'rows' => $ruleRows,
        ]) ?>
    </section>
</div>
<?= $this->endSection() ?>
