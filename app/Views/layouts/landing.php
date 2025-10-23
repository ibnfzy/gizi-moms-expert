<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'GiziChain | Sistem Pakar Gizi Ibu Menyusui') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        (function () {
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
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                    },
                    colors: {
                        giziblue: '#3b82f6',
                        giziblueLight: '#60a5fa',
                        gizigreen: '#22c55e',
                    },
                }
            }
        }
    </script>
</head>
<body class="antialiased bg-slate-50 text-slate-900 transition-colors duration-150 dark:bg-slate-950 dark:text-slate-100">
    <?= $this->renderSection('content') ?>

    <script src="<?= base_url('js/theme.js') ?>" defer></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
