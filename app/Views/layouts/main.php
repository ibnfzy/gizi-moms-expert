<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Sistem Pakar Gizi Ibu Menyusui') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
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
<body class="bg-gray-50 text-gray-800 min-h-screen">
    <div class="flex flex-col min-h-screen">
        <?= $this->include('components/navbar') ?>

        <div class="flex flex-1 overflow-hidden">
            <aside class="w-64 bg-white border-r border-gray-200 hidden md:block overflow-y-auto">
                <?= $this->include('components/sidebar') ?>
            </aside>

            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <div class="max-w-6xl mx-auto">
                    <?= $this->renderSection('content') ?>
                </div>
            </main>
        </div>
    </div>
    <script>
        window.appConfig = window.appConfig || {};
        window.appConfig.authToken = <?= json_encode(session('auth_token') ?? null) ?>;
    </script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
