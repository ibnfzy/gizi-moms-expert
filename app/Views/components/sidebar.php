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
            'label' => 'Manajemen Pengguna',
            'href'  => site_url('admin/users'),
            'match' => 'admin/users',
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
            'label' => 'Jadwal Konsultasi',
            'href'  => site_url('pakar/schedules'),
            'match' => 'pakar/schedules',
        ],
        [
            'label' => 'Panduan Status',
            'href'  => '#panduan-status',
            'match' => '',
            'type'  => 'modal',
        ],
    ];

    $tipsText = 'Gunakan panel konsultasi dan jadwal untuk memantau percakapan aktif serta menindaklanjuti rekomendasi dari hasil inferensi.';
}

$isPathMatched = static function (string $path, string $match): bool {
    if ($match === '') {
        return false;
    }

    return strpos($path, $match) === 0;
};
?>

<nav class="flex h-full flex-col gap-6 px-4 py-6 text-slate-600 dark:text-slate-300">
  <div>
    <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Navigasi</h2>
    <ul class="mt-3 space-y-2 text-sm">
      <?php foreach ($navigation as $item):
                $isActive = $isPathMatched($currentPath, $item['match']);
                $isModalTrigger = ($item['type'] ?? 'link') === 'modal';
                $classes = $isActive
                    ? 'border-blue-200 bg-blue-50 text-blue-600 dark:border-blue-500/40 dark:bg-slate-800/80 dark:text-blue-300'
                    : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800/60';
                $baseClasses = 'flex w-full items-center justify-between gap-3 rounded-lg border border-transparent px-3 py-2 transition';
            ?>
      <li>
        <?php if ($isModalTrigger): ?>
        <button type="button"
          class="<?= $baseClasses ?> <?= $classes ?>"
          data-status-guidance-open
          aria-haspopup="dialog"
          aria-controls="status-guidance-modal">
          <span><?= esc($item['label']) ?></span>
        </button>
        <?php else: ?>
        <a href="<?= esc($item['href']) ?>"
          class="<?= $baseClasses ?> <?= $classes ?>">
          <span><?= esc($item['label']) ?></span>
          <?php if ($isActive): ?>
          <span class="h-2 w-2 rounded-full bg-blue-500 shadow-sm dark:bg-blue-400"></span>
          <?php endif; ?>
        </a>
        <?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <div class="rounded-xl bg-blue-50 p-4 text-xs text-blue-600 dark:bg-slate-800/70 dark:text-blue-200">
    <p class="font-semibold">Tips</p>
    <p class="mt-1 leading-relaxed text-slate-600 dark:text-slate-300">
      <?= esc($tipsText) ?>
    </p>
  </div>
</nav>