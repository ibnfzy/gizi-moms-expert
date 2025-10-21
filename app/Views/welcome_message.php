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

    <div class="flex flex-wrap items-center justify-between gap-4 bg-white border border-gray-200 rounded-xl shadow-sm px-6 py-5">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Panduan Gizi Ibu Menyusui</h2>
            <p class="text-sm text-gray-500">Pelajari langkah penting untuk menjaga kebutuhan gizi harian Anda.</p>
        </div>
        <?= view('components/modal', [
            'id' => 'guideline-modal',
            'title' => 'Panduan Harian',
            'trigger' => [
                'label' => 'Lihat Panduan',
                'variant' => 'primary',
            ],
            'content' => [
                'Pastikan asupan kalori terpenuhi dari sumber karbohidrat kompleks dan protein berkualitas.',
                'Perbanyak konsumsi sayur dan buah sebagai sumber vitamin, mineral, serta antioksidan.',
                'Cukupi kebutuhan cairan minimal 3 liter per hari untuk menjaga produksi ASI tetap optimal.',
            ],
            'actions' => [
                [
                    'label' => 'Hubungi Pakar',
                    'variant' => 'primary',
                    'href' => '#',
                ],
                [
                    'label' => 'Selesai Membaca',
                    'variant' => 'danger',
                    'closesModal' => true,
                ],
            ],
        ]) ?>
    </div>
</div>
<?= $this->endSection() ?>
