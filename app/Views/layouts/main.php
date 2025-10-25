<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title ?? 'Sistem Pakar Gizi Ibu Menyusui') ?></title>
  <script>
  (function() {
    const storageKey = 'gizi-theme';
    const root = document.documentElement;
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    let storedTheme = null;

    try {
      storedTheme = localStorage.getItem(storageKey);
    } catch (error) {
      storedTheme = null;
    }

    const theme = storedTheme || (prefersDark ? 'dark' : 'light');

    if (theme === 'dark') {
      root.classList.add('dark');
    } else {
      root.classList.remove('dark');
    }

    root.setAttribute('data-theme', theme);
  })();
  </script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
  window.tailwind = window.tailwind || {};
  tailwind.config = {
    darkMode: 'class',
    theme: {
      extend: {
        colors: {
          giziblue: '#3b82f6',
          gizigreen: '#22c55e',
        },
      },
    },
  };
  </script>
  <script src="https://unpkg.com/htmx.org@1.9.12"></script>
  <style>
  :root {
    --primary: #3b82f6;
    --background: #f9fafb;
    --text-dark: #1f2937;
  }

  [x-cloak] {
    display: none !important;
  }
  </style>
</head>

<body
  class="min-h-screen bg-slate-50 text-slate-900 transition-colors duration-150 dark:bg-gradient-to-br dark:from-slate-950 dark:via-slate-900 dark:to-black dark:text-slate-100">
  <div class="flex flex-col min-h-screen">
    <?= $this->include('components/navbar') ?>

    <div class="md:hidden" data-mobile-nav-drawer>
      <div class="fixed inset-0 z-40 hidden bg-slate-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-200"
        data-mobile-nav-backdrop></div>
      <div id="mobile-nav-panel"
        class="fixed inset-y-0 left-0 z-50 flex w-64 max-w-full flex-col gap-4 overflow-y-auto bg-white/95 p-4 shadow-lg transition-transform duration-200 ease-in-out dark:bg-slate-950/95 hidden translate-x-full"
        data-mobile-nav-panel aria-hidden="true">
        <div class="flex items-center justify-between">
          <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Navigasi</span>
          <button type="button"
            class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-300 text-slate-600 transition hover:border-slate-400 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-giziblue focus:ring-offset-2 dark:border-slate-700 dark:text-slate-300 dark:hover:border-slate-500 dark:hover:text-slate-100 dark:focus:ring-offset-slate-950"
            data-mobile-nav-close aria-label="Tutup navigasi">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
              class="h-4 w-4">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6m0 12L6 6" />
            </svg>
          </button>
        </div>
        <div class="-mx-4 flex-1 overflow-y-auto px-4">
          <?= $this->include('components/sidebar') ?>
        </div>
      </div>
    </div>

    <div class="flex flex-1 overflow-hidden">
      <aside
        class="hidden w-64 overflow-y-auto border-r border-slate-200 bg-white/80 backdrop-blur-sm dark:border-black/70 dark:bg-slate-950/70 md:block">
        <?= $this->include('components/sidebar') ?>
      </aside>

      <main class="flex-1 overflow-y-auto bg-gradient-to-br from-slate-50 via-white to-slate-200 p-6 dark:bg-gradient-to-br dark:from-slate-950/90 dark:via-slate-900/80 dark:to-black/70">
        <div class="mx-auto max-w-6xl">
          <?= $this->renderSection('content') ?>
        </div>
      </main>
    </div>
  </div>
  <script>
  window.appConfig = window.appConfig || {};
  window.appConfig.authToken = <?= json_encode(session('auth_token') ?? null) ?>;
  </script>
  <script src="<?= base_url('js/theme.js') ?>" defer></script>
  <?php if ((session('user_role') ?? 'pakar') === 'pakar'): ?>
    <?= view('pakar/partials/status_guidance_modal') ?>
  <?php endif; ?>
  <?= $this->renderSection('scripts') ?>
</body>

</html>