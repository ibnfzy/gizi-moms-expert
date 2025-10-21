<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <section class="grid grid-cols-3 gap-6">
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

    <section class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Rule Terbaru</h2>
            <p class="text-sm text-gray-500">Daftar lima rule terakhir yang ditambahkan.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            ID Rule
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nama
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Kategori
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tanggal
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ([
                        ['id' => 'R-120', 'name' => 'Peningkatan Asupan Protein', 'category' => 'Nutrisi', 'date' => '12 Mei 2024', 'status' => 'Aktif'],
                        ['id' => 'R-119', 'name' => 'Monitoring Cairan Harian', 'category' => 'Hidrasi', 'date' => '10 Mei 2024', 'status' => 'Draft'],
                        ['id' => 'R-118', 'name' => 'Evaluasi Berat Badan Ibu', 'category' => 'Evaluasi', 'date' => '8 Mei 2024', 'status' => 'Aktif'],
                        ['id' => 'R-117', 'name' => 'Konsumsi Omega-3', 'category' => 'Suplementasi', 'date' => '5 Mei 2024', 'status' => 'Aktif'],
                        ['id' => 'R-116', 'name' => 'Jadwal Konsultasi Mingguan', 'category' => 'Pendampingan', 'date' => '2 Mei 2024', 'status' => 'Ditinjau'],
                    ] as $rule) : ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= esc($rule['id']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= esc($rule['name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= esc($rule['category']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= esc($rule['date']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                    <?= esc($rule['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?= $this->endSection() ?>
