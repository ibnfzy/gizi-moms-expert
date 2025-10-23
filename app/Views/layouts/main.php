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
  <?= $this->renderSection('scripts') ?>
</body>

</html>