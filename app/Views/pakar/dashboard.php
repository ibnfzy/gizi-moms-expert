<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$badgeClasses = [
    'green' => 'bg-green-100 text-green-800',
    'yellow' => 'bg-yellow-100 text-yellow-800',
    'red' => 'bg-red-100 text-red-800',
];

$patientRows = array_map(static function ($ibu) use ($badgeClasses) {
    $warna = $ibu['warna'] ?? 'green';
    $statusClass = $badgeClasses[$warna] ?? $badgeClasses['green'];

    return [
        'cells' => [
            [
                'content' => $ibu['nama'],
                'class' => 'font-medium text-gray-900',
            ],
            [
                'content' => $ibu['umur'] . ' tahun',
                'class' => 'text-gray-500',
            ],
            [
                'raw' => true,
                'content' => '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ' . $statusClass . '">' . esc($ibu['status']) . '</span>',
            ],
            [
                'raw' => true,
                'content' => view('components/button', [
                    'label' => 'Lihat Detail',
                    'variant' => 'primary',
                    'href' => '#',
                ]),
                'align' => 'right',
            ],
        ],
    ];
}, $ibuMenyusui ?? []);
?>

<div class="space-y-8">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div class="space-y-1">
            <h1 class="text-2xl font-semibold text-gray-900">Dashboard Pakar</h1>
            <p class="text-sm text-gray-600">Pantau perkembangan ibu menyusui di bawah pengawasan Anda.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <?= view('components/button', [
                'label' => 'Tambah Ibu',
                'variant' => 'primary',
                'attributes' => [
                    'onclick' => "alert('Form penambahan ibu akan tersedia segera.')",
                ],
            ]) ?>
            <?= view('components/modal', [
                'id' => 'status-guide-modal',
                'title' => 'Panduan Status Monitoring',
                'trigger' => [
                    'label' => 'Panduan Status',
                    'variant' => 'danger',
                ],
                'content' => [
                    'Status hijau menandakan kondisi ibu stabil dan memenuhi kebutuhan gizi.',
                    'Status kuning menunjukkan perlunya evaluasi tambahan terhadap pola makan ibu.',
                    'Status merah mengindikasikan perhatian khusus dan tindak lanjut segera oleh pakar.',
                ],
                'actions' => [
                    [
                        'label' => 'Hubungi Tim Medis',
                        'variant' => 'primary',
                        'href' => '#',
                    ],
                    [
                        'label' => 'Tandai Dipahami',
                        'variant' => 'danger',
                        'closesModal' => true,
                    ],
                ],
            ]) ?>
        </div>
    </div>

    <?= view('components/table', [
        'title' => 'Ibu Menyusui',
        'description' => 'Daftar ibu menyusui yang berada dalam pemantauan pakar gizi.',
        'headers' => [
            ['label' => 'Nama'],
            ['label' => 'Umur'],
            ['label' => 'Status'],
            ['label' => 'Aksi', 'align' => 'right'],
        ],
        'rows' => $patientRows,
        'emptyMessage' => 'Belum ada data ibu menyusui terdaftar.',
    ]) ?>
</div>
<?= $this->endSection() ?>
