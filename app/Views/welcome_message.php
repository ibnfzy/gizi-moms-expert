<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <?= view('components/card', [
            'badgeText' => 'Status Gizi',
            'title' => 'Kebutuhan Kalori Harian',
            'description' => 'Perkirakan kebutuhan kalori berdasarkan usia bayi, berat badan ibu, dan tingkat aktivitas.',
            'actions' => [
                [
                    'label' => 'Hitung Sekarang',
                    'href' => '#',
                    'type' => 'primary',
                ],
            ],
        ]) ?>

        <?= view('components/card', [
            'badgeText' => 'Rencana Menu',
            'title' => 'Rekomendasi Menu Seimbang',
            'description' => 'Temukan kombinasi makanan tinggi protein, kaya serat, dan seimbang untuk produksi ASI optimal.',
            'actions' => [
                [
                    'label' => 'Lihat Menu',
                    'href' => '#',
                ],
            ],
        ]) ?>

        <?= view('components/card', [
            'badgeText' => 'Monitoring',
            'title' => 'Pantau Asupan Harian',
            'description' => 'Catat asupan nutrisi dan dapatkan peringatan ketika target harian belum tercapai.',
            'actions' => [
                [
                    'label' => 'Mulai Pantau',
                    'href' => '#',
                ],
            ],
        ]) ?>
    </div>

    <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <?= view('components/card', [
            'title' => 'Tips Cepat',
            'description' => 'Penuhi cairan minimal 3 liter per hari dan konsumsi makanan kaya omega-3 seperti ikan laut untuk mendukung kualitas ASI.',
        ]) ?>

        <?= view('components/card', [
            'title' => 'Agenda Konsultasi',
            'description' => 'Atur jadwal konsultasi dengan ahli gizi untuk evaluasi perkembangan ibu menyusui setiap minggu.',
            'actions' => [
                [
                    'label' => 'Jadwalkan',
                    'href' => '#',
                    'type' => 'primary',
                ],
            ],
        ]) ?>
    </section>
</div>
<?= $this->endSection() ?>
