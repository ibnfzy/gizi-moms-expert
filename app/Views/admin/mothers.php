<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div
    class="space-y-8"
    data-admin-mothers
    data-base-endpoint="<?= site_url('api/admin/mothers') ?>"
    data-notification-id="admin-mothers-notification">
    <div
        id="admin-mothers-notification"
        class="hidden rounded-lg border border-transparent px-4 py-3 text-sm font-medium transition-all duration-200"
        role="status"
        aria-live="polite"></div>

    <section class="space-y-4">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-slate-100">Manajemen Data Ibu</h1>
                <p class="text-sm text-gray-600 dark:text-slate-400">Pantau data ibu menyusui dan kelola akses akunnya.</p>
            </div>
            <div class="flex items-center gap-3 text-sm text-gray-500 dark:text-slate-400">
                <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 font-medium text-blue-600 dark:bg-slate-900/60 dark:text-blue-200">
                    <span class="h-2 w-2 rounded-full bg-blue-500 dark:bg-blue-300"></span>
                    Akses Admin
                </span>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm shadow-slate-100 ring-1 ring-gray-100 dark:border-black/70 dark:bg-slate-950/70 dark:shadow-black/40 dark:ring-black/60">
            <div class="border-b border-gray-100 px-6 py-6 dark:border-black/70 md:hidden" data-card-wrapper>
                <div data-card-container class="space-y-4">
                    <div class="rounded-2xl border border-slate-200/80 bg-white/80 p-5 text-sm text-gray-500 shadow-sm shadow-slate-100/60 ring-1 ring-slate-200/70 dark:border-black/70 dark:bg-slate-950/70 dark:text-slate-400 dark:shadow-black/30 dark:ring-black/60">
                        <div class="flex items-center justify-center gap-3">
                            <div class="h-6 w-6 animate-spin rounded-full border-4 border-blue-200 border-t-blue-600" aria-hidden="true"></div>
                            Memuat data ibu...
                        </div>
                    </div>
                </div>
            </div>
            <div class="hidden border-t border-gray-100 dark:border-black/70 md:block">
                <div class="overflow-x-auto">
                    <table class="min-w-[64rem] border-collapse border border-black/40 text-left text-sm dark:border-gray-300">
                        <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-slate-950/70 dark:text-slate-200">
                            <tr>
                                <th scope="col" class="border border-black/40 px-6 py-3 dark:border-gray-300">Nama</th>
                                <th scope="col" class="border border-black/40 px-6 py-3 dark:border-gray-300">Email</th>
                                <th scope="col" class="border border-black/40 px-6 py-3 dark:border-gray-300">Umur</th>
                                <th scope="col" class="border border-black/40 px-6 py-3 dark:border-gray-300">Usia Bayi (bln)</th>
                                <th scope="col" class="border border-black/40 px-6 py-3 dark:border-gray-300">Status</th>
                                <th scope="col" class="border border-black/40 px-6 py-3 text-right dark:border-gray-300">Aksi</th>
                            </tr>
                        </thead>
                        <tbody data-table-body class="text-gray-700 dark:text-slate-200">
                            <tr>
                                <td colspan="6" class="border border-black/40 px-6 py-8 dark:border-gray-300">
                                    <div class="flex items-center justify-center gap-3 text-sm text-gray-500 dark:text-slate-400">
                                        <div class="h-6 w-6 animate-spin rounded-full border-4 border-blue-200 border-t-blue-600" aria-hidden="true"></div>
                                        Memuat data ibu...
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Detail Modal -->
<div id="motherDetailModal" class="fixed inset-0 z-50 hidden items-start justify-center bg-gray-900/50 px-4 py-6 dark:bg-black/70 md:items-center">
    <div class="w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-2xl border border-slate-200/70 bg-white shadow-xl dark:border-slate-200/40 dark:bg-slate-950 dark:text-slate-100">
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-slate-200/30">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-slate-100">Detail Data Ibu</h2>
                <p class="text-sm text-gray-500 dark:text-slate-400">Informasi lengkap mengenai profil dan riwayat ibu.</p>
            </div>
            <button type="button" class="text-gray-400 hover:text-gray-600 dark:text-slate-300 dark:hover:text-slate-100" data-close-detail>
                <span class="sr-only">Tutup</span>
                &times;
            </button>
        </div>
        <div class="space-y-6 px-6 py-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-slate-100" data-detail-name>-</h3>
                    <p class="text-sm text-gray-500 dark:text-slate-400" data-detail-email>-</p>
                </div>
                <div class="text-sm">
                    <span class="inline-flex items-center rounded-full px-3 py-1 font-medium" data-detail-status>-</span>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-4">
                    <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">Profil</h4>
                    <dl class="grid grid-cols-2 gap-3 text-sm text-gray-700 dark:text-slate-200">
                        <div>
                            <dt class="font-medium text-gray-500 dark:text-slate-400">Berat Badan</dt>
                            <dd data-detail-bb>-</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500 dark:text-slate-400">Tinggi Badan</dt>
                            <dd data-detail-tb>-</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500 dark:text-slate-400">Umur</dt>
                            <dd data-detail-umur>-</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500 dark:text-slate-400">Usia Bayi</dt>
                            <dd data-detail-usia-bayi>-</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500 dark:text-slate-400">Tipe Laktasi</dt>
                            <dd data-detail-laktasi>-</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500 dark:text-slate-400">Aktivitas</dt>
                            <dd data-detail-aktivitas>-</dd>
                        </div>
                    </dl>
                </div>
                <div class="space-y-4">
                    <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">Catatan Kesehatan</h4>
                    <div>
                        <h5 class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-slate-500">Alergi</h5>
                        <ul data-detail-alergi class="mt-1 list-disc space-y-1 pl-5 text-sm text-gray-700 dark:text-slate-200">
                            <li class="text-gray-400 dark:text-slate-500">Tidak ada data</li>
                        </ul>
                    </div>
                    <div>
                        <h5 class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-slate-500">Preferensi</h5>
                        <ul data-detail-preferensi class="mt-1 list-disc space-y-1 pl-5 text-sm text-gray-700 dark:text-slate-200">
                            <li class="text-gray-400 dark:text-slate-500">Tidak ada data</li>
                        </ul>
                    </div>
                    <div>
                        <h5 class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-slate-500">Riwayat</h5>
                        <ul data-detail-riwayat class="mt-1 list-disc space-y-1 pl-5 text-sm text-gray-700 dark:text-slate-200">
                            <li class="text-gray-400 dark:text-slate-500">Tidak ada data</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-4 text-sm dark:border-slate-200/30 dark:bg-slate-900/50 dark:text-slate-200">
                <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">Inferensi Terakhir</h4>
                <p class="mt-2 text-gray-600 dark:text-slate-300" data-detail-inference>-</p>
            </div>
        </div>
        <div class="flex justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-slate-200/30">
            <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-slate-200/30 dark:text-slate-300 dark:hover:bg-slate-900/50" data-close-detail>
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- Email Modal -->
<div id="motherEmailModal" class="fixed inset-0 z-50 hidden items-start justify-center bg-gray-900/50 px-4 py-6 dark:bg-black/70 md:items-center">
    <div class="w-full max-w-md max-h-[90vh] overflow-y-auto rounded-2xl border border-slate-200/70 bg-white shadow-xl dark:border-slate-200/40 dark:bg-slate-950 dark:text-slate-100">
        <form id="motherEmailForm" class="space-y-6">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-slate-200/30">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-slate-100">Perbarui Email</h2>
                <p class="text-sm text-gray-500 dark:text-slate-400">Atur ulang email yang digunakan ibu untuk mengakses sistem.</p>
            </div>
            <div class="space-y-4 px-6 py-6">
                <div class="space-y-2">
                    <label for="motherEmailInput" class="text-sm font-medium text-gray-700 dark:text-slate-200">Email</label>
                    <input
                        type="email"
                        id="motherEmailInput"
                        name="email"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-slate-200/50 dark:bg-slate-950/40 dark:text-slate-100"
                        placeholder="nama@email.com"
                        required>
                </div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Pastikan email aktif dan dapat dihubungi.</p>
            </div>
            <div class="flex justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-slate-200/30">
                <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-slate-200/30 dark:text-slate-300 dark:hover:bg-slate-900/50" data-close-email>
                    Batal
                </button>
                <button type="submit" class="inline-flex items-center rounded-lg border border-blue-600 bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:border-blue-700 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-slate-200/30 dark:focus:ring-offset-slate-950">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Password Modal -->
<div id="motherPasswordModal" class="fixed inset-0 z-50 hidden items-start justify-center bg-gray-900/50 px-4 py-6 dark:bg-black/70 md:items-center">
    <div class="w-full max-w-md max-h-[90vh] overflow-y-auto rounded-2xl border border-slate-200/70 bg-white shadow-xl dark:border-slate-200/40 dark:bg-slate-950 dark:text-slate-100">
        <form id="motherPasswordForm" class="space-y-6">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-slate-200/30">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-slate-100">Atur Ulang Password</h2>
                <p class="text-sm text-gray-500 dark:text-slate-400">Buat password baru minimal 8 karakter.</p>
            </div>
            <div class="space-y-4 px-6 py-6">
                <div class="space-y-2">
                    <label for="motherPasswordInput" class="text-sm font-medium text-gray-700 dark:text-slate-200">Password Baru</label>
                    <input
                        type="password"
                        id="motherPasswordInput"
                        name="password"
                        minlength="8"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-slate-200/50 dark:bg-slate-950/40 dark:text-slate-100"
                        placeholder="Minimal 8 karakter"
                        required>
                </div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Bagikan password baru kepada ibu setelah disimpan.</p>
            </div>
            <div class="flex justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-slate-200/30">
                <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-slate-200/30 dark:text-slate-300 dark:hover:bg-slate-900/50" data-close-password>
                    Batal
                </button>
                <button type="submit" class="inline-flex items-center rounded-lg border border-blue-600 bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:border-blue-700 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-slate-200/30 dark:focus:ring-offset-slate-950">
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