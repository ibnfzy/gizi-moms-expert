<?php
    $session = session();
    $userName = $session->get('user_name') ?? 'Pakar Gizi';
    $userRole = $session->get('user_role') ?? 'pakar';
?>

<header class="border-b border-gray-200 bg-white">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500 text-sm font-semibold text-white">GM</div>
            <div>
                <h1 class="text-lg font-semibold text-gray-900">Sistem Pakar Gizi Ibu Menyusui</h1>
                <p class="text-sm text-gray-500">Monitoring kondisi gizi dan rekomendasi nutrisi harian.</p>
            </div>
        </div>
        <div class="flex items-center gap-3 text-sm text-gray-500">
            <div class="hidden text-right sm:block">
                <p class="font-medium text-gray-700">Hai, <?= esc($userName) ?>!</p>
                <p class="text-xs uppercase tracking-wide text-gray-400"><?= esc(strtoupper($userRole)) ?></p>
            </div>
            <a
                href="<?= site_url('logout') ?>"
                class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >Keluar</a>
        </div>
    </div>
</header>
