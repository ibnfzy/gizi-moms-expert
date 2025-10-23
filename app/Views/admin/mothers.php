<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div
    class="space-y-8"
    data-admin-mothers
    data-base-endpoint="<?= site_url('api/admin/mothers') ?>"
    data-notification-id="admin-mothers-notification"
>
    <div
        id="admin-mothers-notification"
        class="hidden rounded-lg border border-transparent px-4 py-3 text-sm font-medium transition-all duration-200"
        role="status"
        aria-live="polite"
    ></div>

    <section class="space-y-4">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Manajemen Data Ibu</h1>
                <p class="text-sm text-gray-600">Pantau data ibu menyusui dan kelola akses akunnya.</p>
            </div>
            <div class="flex items-center gap-3 text-sm text-gray-500">
                <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 font-medium text-blue-600">
                    <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                    Akses Admin
                </span>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th scope="col" class="px-6 py-3">Nama</th>
                            <th scope="col" class="px-6 py-3">Email</th>
                            <th scope="col" class="px-6 py-3">Umur</th>
                            <th scope="col" class="px-6 py-3">Usia Bayi (bln)</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody data-table-body class="divide-y divide-gray-100 text-gray-700">
                        <tr>
                            <td colspan="6" class="px-6 py-8">
                                <div class="flex items-center justify-center gap-3 text-sm text-gray-500">
                                    <div class="h-6 w-6 animate-spin rounded-full border-4 border-blue-200 border-t-blue-600" aria-hidden="true"></div>
                                    Memuat data ibu...
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<!-- Detail Modal -->
<div id="motherDetailModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 px-4 py-6">
    <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Detail Data Ibu</h2>
                <p class="text-sm text-gray-500">Informasi lengkap mengenai profil dan riwayat ibu.</p>
            </div>
            <button type="button" class="text-gray-400 hover:text-gray-600" data-close-detail>
                <span class="sr-only">Tutup</span>
                &times;
            </button>
        </div>
        <div class="space-y-6 px-6 py-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900" data-detail-name>-</h3>
                    <p class="text-sm text-gray-500" data-detail-email>-</p>
                </div>
                <div class="text-sm">
                    <span class="inline-flex items-center rounded-full px-3 py-1 font-medium" data-detail-status>-</span>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-4">
                    <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Profil</h4>
                    <dl class="grid grid-cols-2 gap-3 text-sm text-gray-700">
                        <div>
                            <dt class="font-medium text-gray-500">Berat Badan</dt>
                            <dd data-detail-bb>-</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Tinggi Badan</dt>
                            <dd data-detail-tb>-</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Umur</dt>
                            <dd data-detail-umur>-</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Usia Bayi</dt>
                            <dd data-detail-usia-bayi>-</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Tipe Laktasi</dt>
                            <dd data-detail-laktasi>-</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Aktivitas</dt>
                            <dd data-detail-aktivitas>-</dd>
                        </div>
                    </dl>
                </div>
                <div class="space-y-4">
                    <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Catatan Kesehatan</h4>
                    <div>
                        <h5 class="text-xs font-semibold uppercase tracking-wide text-gray-400">Alergi</h5>
                        <ul data-detail-alergi class="mt-1 list-disc pl-5 text-sm text-gray-700 space-y-1">
                            <li class="text-gray-400">Tidak ada data</li>
                        </ul>
                    </div>
                    <div>
                        <h5 class="text-xs font-semibold uppercase tracking-wide text-gray-400">Preferensi</h5>
                        <ul data-detail-preferensi class="mt-1 list-disc pl-5 text-sm text-gray-700 space-y-1">
                            <li class="text-gray-400">Tidak ada data</li>
                        </ul>
                    </div>
                    <div>
                        <h5 class="text-xs font-semibold uppercase tracking-wide text-gray-400">Riwayat</h5>
                        <ul data-detail-riwayat class="mt-1 list-disc pl-5 text-sm text-gray-700 space-y-1">
                            <li class="text-gray-400">Tidak ada data</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-4 text-sm">
                <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Inferensi Terakhir</h4>
                <p class="mt-2 text-gray-600" data-detail-inference>-</p>
            </div>
        </div>
        <div class="flex justify-end gap-3 border-t border-gray-200 px-6 py-4">
            <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50" data-close-detail>
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- Email Modal -->
<div id="motherEmailModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 px-4 py-6">
    <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
        <form id="motherEmailForm" class="space-y-6">
            <div class="border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Perbarui Email</h2>
                <p class="text-sm text-gray-500">Atur ulang email yang digunakan ibu untuk mengakses sistem.</p>
            </div>
            <div class="space-y-4 px-6 py-6">
                <div class="space-y-2">
                    <label for="motherEmailInput" class="text-sm font-medium text-gray-700">Email</label>
                    <input
                        type="email"
                        id="motherEmailInput"
                        name="email"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        placeholder="nama@email.com"
                        required
                    >
                </div>
                <p class="text-xs text-gray-500">Pastikan email aktif dan dapat dihubungi.</p>
            </div>
            <div class="flex justify-end gap-3 border-t border-gray-200 px-6 py-4">
                <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50" data-close-email>
                    Batal
                </button>
                <button type="submit" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Password Modal -->
<div id="motherPasswordModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 px-4 py-6">
    <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
        <form id="motherPasswordForm" class="space-y-6">
            <div class="border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Atur Ulang Password</h2>
                <p class="text-sm text-gray-500">Buat password baru minimal 8 karakter.</p>
            </div>
            <div class="space-y-4 px-6 py-6">
                <div class="space-y-2">
                    <label for="motherPasswordInput" class="text-sm font-medium text-gray-700">Password Baru</label>
                    <input
                        type="password"
                        id="motherPasswordInput"
                        name="password"
                        minlength="8"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        placeholder="Minimal 8 karakter"
                        required
                    >
                </div>
                <p class="text-xs text-gray-500">Bagikan password baru kepada ibu setelah disimpan.</p>
            </div>
            <div class="flex justify-end gap-3 border-t border-gray-200 px-6 py-4">
                <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50" data-close-password>
                    Batal
                </button>
                <button type="submit" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Simpan Password
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="module" src="<?= base_url('js/admin.js') ?>"></script>
<?= $this->endSection() ?>
