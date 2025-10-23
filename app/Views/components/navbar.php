<?php
$session = session();
$userName = $session->get('user_name') ?? 'Pakar Gizi';
$userRole = $session->get('user_role') ?? 'pakar';
?>

<header class="border-b border-slate-200 bg-slate-200/90 backdrop-blur-sm dark:border-black/70 dark:bg-slate-950/80">
  <div
    class="mx-auto flex max-w-6xl flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
    <div class="flex items-center gap-3">
      <div class="flex h-10 w-10 items-center justify-center rounded-full bg-giziblue text-sm font-semibold text-white">
        GM</div>
      <div>
        <h1 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Sistem Pakar Gizi Ibu Menyusui</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">Monitoring kondisi gizi dan rekomendasi nutrisi harian.
        </p>
      </div>
    </div>
    <div class="flex items-center justify-end gap-3 text-sm text-slate-500 dark:text-slate-400">
      <button type="button"
        class="inline-flex items-center gap-2 rounded-full border border-slate-600/70 bg-white/70 px-3 py-1.5 text-xs font-medium text-slate-600 shadow-sm transition hover:border-giziblue/60 hover:text-giziblue focus:outline-none focus:ring-2 focus:ring-giziblue focus:ring-offset-2 dark:border-slate-50 dark:bg-slate-950/60 dark:text-slate-200 dark:hover:border-giziblue/80 dark:hover:text-giziblue"
        data-theme-toggle aria-pressed="false" aria-label="Aktifkan mode gelap">
        <span class="sr-only" data-theme-toggle-text>Aktifkan mode gelap</span>
        <span aria-hidden="true" data-theme-icon="sun" class="h-4 w-4 text-amber-400">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
            <path
              d="M12 5.25a.75.75 0 0 1 .75-.75h.01a.75.75 0 0 1 .75.75V7a.75.75 0 0 1-.75.75h-.01A.75.75 0 0 1 12 7V5.25Zm0 11.25a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm6.75-3a.75.75 0 0 1 .75-.75H21a.75.75 0 0 1 0 1.5h-1.5a.75.75 0 0 1-.75-.75Zm-13.5 0a.75.75 0 0 1-.75-.75H3a.75.75 0 0 1 0 1.5h1.5a.75.75 0 0 1 .75-.75ZM6.22 7.53a.75.75 0 1 1 1.06-1.06l1.06 1.06a.75.75 0 0 1-1.06 1.06L6.22 7.53Zm10.49 10.49a.75.75 0 1 1 1.06-1.06l1.06 1.06a.75.75 0 1 1-1.06 1.06l-1.06-1.06Zm1.06-10.49a.75.75 0 1 1 1.06 1.06l-1.06 1.06a.75.75 0 0 1-1.06-1.06l1.06-1.06ZM7.28 17.28a.75.75 0 1 1 1.06 1.06L7.28 19.4a.75.75 0 0 1-1.06-1.06l1.06-1.06ZM12 17a.75.75 0 0 1 .75.75V19a.75.75 0 0 1-.75.75h-.01A.75.75 0 0 1 11.25 19v-1.25A.75.75 0 0 1 12 17Z" />
          </svg>
        </span>
        <span aria-hidden="true" data-theme-icon="moon" class="hidden h-4 w-4 text-slate-200">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
            <path
              d="M21 12.75a8.25 8.25 0 0 1-8.25 8.25 8.25 8.25 0 0 1-7.882-5.535.75.75 0 0 1 1.096-.867 5.999 5.999 0 0 0 7.362-9.088.75.75 0 0 1 .816-1.216A8.25 8.25 0 0 1 21 12.75Z" />
          </svg>
        </span>
      </button>
      <div class="hidden text-right sm:block">
        <p class="font-medium text-slate-700 dark:text-slate-200">Hai, <?= esc($userName) ?>!</p>
        <p class="text-xs uppercase tracking-wide text-slate-400 dark:text-slate-500"><?= esc(strtoupper($userRole)) ?>
        </p>
      </div>
      <a href="<?= site_url('logout') ?>"
        class="inline-flex items-center rounded-md border border-giziblue bg-giziblue px-3 py-2 text-sm font-semibold text-white shadow hover:border-blue-600 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-giziblue focus:ring-offset-2 dark:border-black/70 dark:focus:ring-offset-slate-950">Keluar</a>
    </div>
  </div>
</header>