<?php
    $cards = [
        [
            'key'         => 'normal',
            'label'       => 'Status Normal',
            'description' => 'Ibu dengan kondisi stabil dan kebutuhan gizi terpenuhi.',
            'color'       => 'bg-emerald-500',
        ],
        [
            'key'         => 'moderate',
            'label'       => 'Perlu Pemantauan',
            'description' => 'Perlu pemantauan berkala untuk menyesuaikan pola makan.',
            'color'       => 'bg-amber-500',
        ],
        [
            'key'         => 'high',
            'label'       => 'Prioritas Tinggi',
            'description' => 'Membutuhkan tindak lanjut segera dari pakar gizi.',
            'color'       => 'bg-rose-500',
        ],
    ];

    $formatValue = static function ($value, string $suffix = ''): string {
        if ($value === null || $value === '' || $value === []) {
            return '-';
        }

        return esc($value) . $suffix;
    };
?>
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <?php foreach ($cards as $card): ?>
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white/80 shadow-sm shadow-slate-100/60 ring-1 ring-slate-200/70 dark:border-slate-800/80 dark:bg-slate-900/70 dark:ring-slate-800/80">
            <div class="absolute inset-x-0 top-0 h-1 <?= esc($card['color']) ?> dark:opacity-80"></div>
            <div class="p-6">
                <p class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"><?= esc($card['label']) ?></p>
                <div class="mt-3 flex items-end justify-between">
                    <h2 class="text-3xl font-bold text-slate-900 dark:text-slate-100">
                        <?= esc($statusSummary[$card['key']] ?? 0) ?>
                    </h2>
                    <span class="text-xs text-slate-400 dark:text-slate-500">Ibu terpantau</span>
                </div>
                <p class="mt-4 text-sm leading-relaxed text-slate-600 dark:text-slate-400"><?= esc($card['description']) ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/80 shadow-sm shadow-slate-100/60 ring-1 ring-slate-200/70 dark:border-slate-800/80 dark:bg-slate-900/70 dark:ring-slate-800/80">
    <div class="border-b border-slate-100 px-6 py-4 dark:border-slate-800">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Daftar Ibu Menyusui</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Status dihitung dari hasil inferensi terbaru.</p>
            </div>
            <button
                type="button"
                class="inline-flex items-center rounded-lg border border-blue-200 px-4 py-2 text-sm font-medium text-blue-600 transition hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-giziblue focus:ring-offset-2 dark:border-blue-500/40 dark:text-blue-300 dark:hover:bg-slate-800/60 dark:focus:ring-offset-slate-900"
                data-dashboard-refresh="<?= site_url('pakar/dashboard/data') ?>"
            >Muat Ulang</button>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 border border-slate-200/80 dark:divide-slate-800 dark:border-slate-800/80">
            <thead class="bg-slate-50 text-left text-sm font-semibold text-slate-600 dark:bg-slate-900/60 dark:text-slate-300">
                <tr>
                    <th scope="col" class="px-6 py-3">Nama</th>
                    <th scope="col" class="px-6 py-3">Umur</th>
                    <th scope="col" class="px-6 py-3">Usia Bayi</th>
                    <th scope="col" class="px-6 py-3">Status</th>
                    <th scope="col" class="px-6 py-3">Terakhir Diperbarui</th>
                    <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm text-slate-700 dark:divide-slate-800 dark:text-slate-300">
                <?php if ($mothers === []): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-6 text-center text-sm text-slate-500 dark:text-slate-400">
                            Belum ada data ibu menyusui yang dapat ditampilkan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($mothers as $mother): ?>
                        <?php
                            $statusCode  = $mother['status']['code'] ?? null;
                            $baseBadge   = $mother['status']['badge'] ?? 'bg-slate-100 text-slate-600';
                            $badgeDark   = match ($statusCode) {
                                'high'     => 'dark:bg-rose-500/20 dark:text-rose-200',
                                'moderate' => 'dark:bg-amber-500/20 dark:text-amber-200',
                                'normal'   => 'dark:bg-emerald-500/20 dark:text-emerald-200',
                                default    => 'dark:bg-slate-700 dark:text-slate-200',
                            };
                            $statusBadge = trim($baseBadge . ' ' . $badgeDark);
                            $statusLabel = $mother['status']['label'] ?? 'Normal';
                        ?>
                        <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/70">
                            <td class="px-6 py-4 font-medium text-slate-900 dark:text-slate-100"><?= esc($mother['name'] ?? '-') ?></td>
                            <td class="px-6 py-4"><?= $formatValue($mother['profile']['umur'] ?? null, ' tahun') ?></td>
                            <td class="px-6 py-4"><?= $formatValue($mother['profile']['usia_bayi_bln'] ?? null, ' bln') ?></td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?= esc($statusBadge) ?>">
                                    <?= esc($statusLabel) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">
                                <?= esc($mother['latest_inference']['created_at_human'] ?? '-') ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md border border-giziblue px-3 py-2 text-xs font-semibold text-giziblue transition hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-giziblue focus:ring-offset-2 dark:border-blue-500/60 dark:text-blue-300 dark:hover:bg-slate-800/60 dark:focus:ring-offset-slate-900"
                                    data-mother-detail="<?= site_url('pakar/dashboard/mothers') ?>/<?= esc($mother['id'] ?? 0) ?>"
                                >Lihat Detail</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
