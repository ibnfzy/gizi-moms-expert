<?php
    $profile          = $mother['profile'] ?? [];
    $latestInference  = $mother['latest_inference'] ?? [];
    $recommendations  = is_array($latestInference['recommendations'] ?? null)
        ? $latestInference['recommendations']
        : [];
    $facts            = is_array($latestInference['facts'] ?? null)
        ? $latestInference['facts']
        : [];

    $formatValue = static function ($value, string $suffix = ''): string {
        if ($value === null || $value === '' || $value === []) {
            return '-';
        }

        return esc($value) . $suffix;
    };

    $listValue = static function ($values): string {
        if (! is_array($values) || $values === []) {
            return 'Tidak ada data';
        }

        $escaped = array_map(static fn ($item): string => esc((string) $item), $values);

        return implode(', ', $escaped);
    };

    $formatKey = static function (?string $key): string {
        if ($key === null || $key === '') {
            return '-';
        }

        $label = ucwords(str_replace('_', ' ', $key));

        return esc($label);
    };

    $formatDisplay = static function ($value): string {
        if ($value === null || $value === '' || $value === []) {
            return '-';
        }

        if (is_array($value)) {
            $values = array_map(static fn ($item): string => esc((string) $item), array_values($value));

            return implode(', ', $values);
        }

        return esc($value);
    };
?>
<div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 px-4" data-modal="mother-detail">
    <div class="relative w-full max-w-3xl rounded-2xl bg-white shadow-xl">
        <div class="flex items-start justify-between border-b border-gray-100 px-6 py-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900"><?= esc($mother['name'] ?? '-') ?></h3>
                <p class="text-sm text-gray-500"><?= esc($mother['email'] ?? 'Email belum tersedia') ?></p>
            </div>
            <button
                type="button"
                class="rounded-full p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                hx-get="<?= site_url('pakar/dashboard/mothers/close') ?>"
                hx-target="#mother-detail-container"
                hx-swap="innerHTML"
            >
                <span class="sr-only">Tutup</span>
                &times;
            </button>
        </div>
        <div class="grid gap-6 px-6 py-6 md:grid-cols-2">
            <div class="space-y-4">
                <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-700">Profil Ibu</h4>
                <dl class="space-y-2 text-sm text-gray-600">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Berat Badan</dt>
                        <dd><?= $formatValue($profile['bb'] ?? null, ' kg') ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Tinggi Badan</dt>
                        <dd><?= $formatValue($profile['tb'] ?? null, ' cm') ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Umur</dt>
                        <dd><?= $formatValue($profile['umur'] ?? null, ' tahun') ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Usia Bayi</dt>
                        <dd><?= $formatValue($profile['usia_bayi_bln'] ?? null, ' bulan') ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Tipe Laktasi</dt>
                        <dd><?= esc($profile['laktasi_tipe'] ?? '-') ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Aktivitas</dt>
                        <dd><?= esc($profile['aktivitas'] ?? '-') ?></dd>
                    </div>
                </dl>
                <div class="space-y-3 text-sm text-gray-600">
                    <div>
                        <h5 class="font-medium text-gray-700">Alergi</h5>
                        <p class="mt-1 rounded-lg bg-gray-50 px-3 py-2">
                            <?= $listValue($profile['alergi'] ?? null) ?>
                        </p>
                    </div>
                    <div>
                        <h5 class="font-medium text-gray-700">Preferensi Makanan</h5>
                        <p class="mt-1 rounded-lg bg-gray-50 px-3 py-2">
                            <?= $listValue($profile['preferensi'] ?? null) ?>
                        </p>
                    </div>
                    <div>
                        <h5 class="font-medium text-gray-700">Riwayat Kesehatan</h5>
                        <p class="mt-1 rounded-lg bg-gray-50 px-3 py-2">
                            <?= $listValue($profile['riwayat'] ?? null) ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="space-y-4">
                <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-700">Hasil Inferensi Terbaru</h4>
                <div class="rounded-2xl border border-gray-100 bg-gradient-to-br from-blue-50 to-white p-4">
                    <?php
                        $statusBadge = $mother['status']['badge'] ?? 'bg-gray-100 text-gray-600';
                        $statusLabel = $mother['status']['label'] ?? 'Normal';
                    ?>
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?= esc($statusBadge) ?>">
                            <?= esc($statusLabel) ?>
                        </span>
                        <span class="text-xs text-gray-400"><?= esc($latestInference['created_at_human'] ?? '-') ?></span>
                    </div>
                    <ul class="mt-4 space-y-2 text-sm text-gray-700" role="list">
                        <?php if ($recommendations === []): ?>
                            <li class="rounded-lg bg-white/80 px-3 py-2 text-gray-500">Belum ada rekomendasi khusus.</li>
                        <?php else: ?>
                            <?php foreach ($recommendations as $item): ?>
                                <li class="rounded-lg bg-white px-3 py-2 shadow-sm ring-1 ring-gray-100"><?= esc($item) ?></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-white p-4">
                    <h5 class="text-sm font-medium text-gray-700">Fakta Dasar</h5>
                    <ul class="mt-3 space-y-2 text-sm text-gray-600">
                        <?php if ($facts === []): ?>
                            <li class="text-gray-400">Fakta pendukung belum tersedia.</li>
                        <?php else: ?>
                            <?php foreach ($facts as $key => $value): ?>
                                <li class="flex justify-between gap-4">
                                    <span class="font-medium text-gray-500"><?= $formatKey(is_string($key) ? $key : (string) $key) ?></span>
                                    <span class="text-right"><?= $formatDisplay($value) ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="flex justify-end border-t border-gray-100 px-6 py-4">
            <button
                type="button"
                class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                hx-get="<?= site_url('pakar/dashboard/mothers/close') ?>"
                hx-target="#mother-detail-container"
                hx-swap="innerHTML"
            >Tutup</button>
        </div>
    </div>
</div>
