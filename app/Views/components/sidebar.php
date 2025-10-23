<?php
$request     = service('request');
$session     = session();
$currentPath = trim($request->getUri()->getPath(), '/');
$userRole    = $session->get('user_role') ?? 'pakar';

if ($userRole === 'admin') {
    $navigation = [
        [
            'label' => 'Dashboard',
            'href'  => site_url('admin/dashboard'),
            'match' => 'admin/dashboard',
        ],
        [
            'label' => 'Manajemen Data Ibu',
            'href'  => site_url('admin/mothers'),
            'match' => 'admin/mothers',
        ],
        [
            'label' => 'Manajemen Rules',
            'href'  => site_url('admin/rules'),
            'match' => 'admin/rules',
        ],
    ];

    $tipsText = 'Gunakan halaman manajemen untuk memperbarui akses pengguna dan menjaga data ibu tetap akurat.';
} else {
    $navigation = [
        [
            'label' => 'Dashboard',
            'href'  => site_url('pakar/dashboard'),
            'match' => 'pakar/dashboard',
        ],
        [
            'label' => 'Konsultasi',
            'href'  => site_url('pakar/consultations'),
            'match' => 'pakar/consultations',
        ],
        [
            'label' => 'Panduan Status',
            'href'  => '#panduan-status',
            'match' => '',
        ],
    ];

    $tipsText = 'Gunakan panel konsultasi untuk memantau percakapan aktif dengan ibu menyusui dan tindak lanjuti rekomendasi dari hasil inferensi.';
}

$isPathMatched = static function (string $path, string $match): bool {
    if ($match === '') {
        return false;
    }

    return strpos($path, $match) === 0;
};
?>

<nav class="flex h-full flex-col gap-6 px-4 py-6">
    <div>
        <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Navigasi</h2>
        <ul class="mt-3 space-y-2 text-sm">
            <?php foreach ($navigation as $item):
                $isActive = $isPathMatched($currentPath, $item['match']);
                $classes = $isActive
                    ? 'bg-blue-50 text-blue-600 border-blue-200'
                    : 'text-gray-600 hover:bg-gray-50';
            ?>
                <li>
                    <a href="<?= esc($item['href']) ?>"
                        class="flex items-center justify-between gap-3 rounded-lg border border-transparent px-3 py-2 transition <?= $classes ?>">
                        <span><?= esc($item['label']) ?></span>
                        <?php if ($isActive): ?>
                            <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="rounded-xl bg-blue-50 p-4 text-xs text-blue-600">
        <p class="font-semibold">Tips</p>
        <p class="mt-1 leading-relaxed">
            <?= esc($tipsText) ?>
        </p>
    </div>
</nav>
