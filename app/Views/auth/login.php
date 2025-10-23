<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk | GiziChain</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                    },
                    colors: {
                        primary: '#3b82f6',
                        accent: '#6366f1',
                    },
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-[#3b82f6] via-[#4c6ef5] to-[#6366f1] flex items-center justify-center px-6 py-16 font-sans">
    <div class="max-w-md w-full">
        <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-2xl p-8 sm:p-10 transition-all duration-200">
            <div class="text-center mb-8">
                <div class="text-3xl font-bold text-blue-600">GiziChain</div>
                <p class="text-sm text-gray-600 mt-2">Sistem Pakar Kebutuhan Gizi Ibu Menyusui</p>
            </div>

            <?php if (session()->has('error')): ?>
                <div class="bg-red-100 text-red-700 rounded-md p-3 text-sm mb-6">
                    <?= esc(session('error')) ?>
                </div>
            <?php endif; ?>

            <form action="<?= site_url('login') ?>" method="post" class="space-y-6">
                <?= csrf_field() ?>
                <div class="space-y-2">
                    <label for="role" class="text-sm font-medium text-gray-700">Login sebagai</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 0 1 .894.553l5 10A1 1 0 0 1 15 15H5a1 1 0 0 1-.894-1.447l5-10A1 1 0 0 1 10 3zm0 3.618L6.618 13h6.764L10 6.618z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        <select id="role" name="role" required class="w-full appearance-none rounded-lg border border-gray-300 bg-white py-3 pl-11 pr-10 text-left text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="" disabled <?= old('role') ? '' : 'selected' ?>>Pilih peran</option>
                            <option value="admin" <?= old('role') === 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="pakar" <?= old('role') === 'pakar' ? 'selected' : '' ?>>Pakar</option>
                        </select>
                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.939l3.71-3.71a.75.75 0 0 1 1.06 1.061l-4.24 4.24a.75.75 0 0 1-1.06 0l-4.24-4.24a.75.75 0 0 1 .02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium text-gray-700">Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2.94 6.34A2 2 0 0 1 4.89 5h10.22a2 2 0 0 1 1.95 1.34L10 10.882 2.94 6.34z" />
                                <path d="M18 8.118v5.382A2.5 2.5 0 0 1 15.5 16h-11A2.5 2.5 0 0 1 2 13.5V8.118l7.553 4.53a1 1 0 0 0 .894 0L18 8.118z" />
                            </svg>
                        </span>
                        <input type="email" id="email" name="email" required placeholder="nama@email.com" class="w-full border border-gray-300 rounded-lg p-3 pl-11 focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all duration-200" />
                    </div>
                </div>
                <div class="space-y-2">
                    <label for="password" class="text-sm font-medium text-gray-700">Kata Sandi</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 8a5 5 0 0 1 10 0v1h.5A1.5 1.5 0 0 1 17 10.5v6A1.5 1.5 0 0 1 15.5 18h-11A1.5 1.5 0 0 1 3 16.5v-6A1.5 1.5 0 0 1 4.5 9H5V8zm2 1h6V8a3 3 0 0 0-6 0v1zm3 3a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        <input type="password" id="password" name="password" required placeholder="Kata sandi" class="w-full border border-gray-300 rounded-lg p-3 pl-11 focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all duration-200" />
                    </div>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <label class="inline-flex items-center space-x-2 text-gray-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                        <span>Ingat saya</span>
                    </label>
                    <a href="#" class="text-blue-600 hover:text-blue-700 transition-all duration-200">Lupa kata sandi?</a>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition-all duration-200 shadow-md hover:shadow-lg">
                    Masuk Sekarang
                </button>
            </form>

            <p class="text-center text-gray-500 text-sm mt-6">© 2025 GiziChain. Semua hak dilindungi.</p>
        </div>
    </div>
</body>
</html>
