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

    <?php
    ['items' => $mobileNavigation, 'currentPath' => $mobileCurrentPath] = require APPPATH . 'Views/components/navigation.php';
    $isMobilePathMatched = static function (string $path, string $match): bool {
      if ($match === '') {
        return false;
      }

      return strpos($path, $match) === 0;
    };
    ?>

    <nav
      class="md:hidden fixed inset-x-0 bottom-0 z-40 border-t border-slate-200/80 bg-white/80 px-2 backdrop-blur-xl shadow-[0_-8px_24px_rgba(15,23,42,0.08)] transition-colors duration-300 dark:border-slate-800/60 dark:bg-slate-950/70">
      <ul class="flex items-center justify-around">
        <?php foreach ($mobileNavigation as $item):
          $isActive = $isMobilePathMatched($mobileCurrentPath, $item['match']);
          $isModalTrigger = ($item['type'] ?? 'link') === 'modal';
          $activeClasses = $isActive
            ? 'text-blue-600 dark:text-blue-300'
            : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-100';
          $label = esc($item['label']);
          $iconName = $item['icon'] ?? null;
        ?>
          <li class="flex-1">
            <?php if ($isModalTrigger): ?>
              <button type="button"
                class="flex w-full flex-col items-center justify-center gap-1 rounded-xl px-2 py-3 text-xs text-center font-medium transition-colors duration-200 <?= $activeClasses ?>"
                data-status-guidance-open aria-haspopup="dialog" aria-controls="status-guidance-modal">
                <?php if ($iconName !== null): ?>
                  <?= view('components/icon', ['name' => $iconName, 'class' => 'h-6 w-6']) ?>
                <?php endif; ?>
                <span><?= $label ?></span>
              </button>
            <?php else: ?>
              <a href="<?= esc($item['href']) ?>"
                class="flex w-full flex-col items-center justify-center gap-1 rounded-xl px-2 py-3 text-xs font-medium transition-colors duration-200 <?= $activeClasses ?>"
                <?= $isActive ? 'aria-current="page"' : '' ?>>
                <?php if ($iconName !== null): ?>
                  <?= view('components/icon', ['name' => $iconName, 'class' => 'h-6 w-6']) ?>
                <?php endif; ?>
                <span><?= $label ?></span>
                <?php if ($isActive): ?>
                  <span class="mt-1 h-1.5 w-12 rounded-full bg-blue-500/80 shadow-sm dark:bg-blue-400/80"></span>
                <?php endif; ?>
              </a>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </nav>

    <div class="flex flex-1 overflow-hidden">
      <aside
        class="hidden w-64 overflow-y-auto border-r border-slate-200 bg-white/80 backdrop-blur-sm dark:border-black/70 dark:bg-slate-950/70 md:block">
        <?= $this->include('components/sidebar') ?>
      </aside>

      <main
        class="flex-1 overflow-y-auto bg-gradient-to-br from-slate-50 via-white to-slate-200 px-6 pb-24 pt-6 transition-colors duration-300 dark:bg-gradient-to-br dark:from-slate-950/90 dark:via-slate-900/80 dark:to-black/70 md:pb-6">
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