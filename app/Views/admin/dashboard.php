<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div
    class="space-y-8"
    data-admin-dashboard
    data-stats-endpoint="<?= site_url('api/stats') ?>"
    data-rules-endpoint="<?= site_url('api/rules') ?>"
    data-notification-id="admin-dashboard-notification"
>
    <div
        id="admin-dashboard-notification"
        class="hidden rounded-lg border border-transparent px-4 py-3 text-sm font-medium transition-all duration-200"
        role="status"
        aria-live="polite"
    ></div>

    <section class="space-y-4">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Dashboard Admin</h1>
                <p class="text-sm text-gray-600">Pantau statistik sistem pakar dan ringkasan rule terbaru.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a
                    href="<?= site_url('admin/rules') ?>"
                    class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >Kelola Rules</a>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4" data-stats-grid>
            <div class="col-span-full flex justify-center py-12" data-stats-loader>
                <div class="h-10 w-10 rounded-full border-4 border-blue-200 border-t-blue-600 animate-spin" aria-hidden="true"></div>
                <span class="sr-only">Memuat statistik...</span>
            </div>
        </div>
    </section>

    <section class="space-y-4">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Rule Terbaru</h2>
                <p class="text-sm text-gray-500">Daftar rule yang terakhir diperbarui pada basis pengetahuan.</p>
            </div>
            <button
                type="button"
                data-refresh-rules
                class="inline-flex items-center rounded-lg border border-blue-200 px-4 py-2 text-sm font-medium text-blue-600 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >Muat Ulang</button>
        </div>

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th scope="col" class="px-6 py-3">ID Rule</th>
                            <th scope="col" class="px-6 py-3">Nama</th>
                            <th scope="col" class="px-6 py-3">Kategori</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3 text-right">Terakhir Diperbarui</th>
                        </tr>
                    </thead>
                    <tbody data-rules-body class="divide-y divide-gray-100 text-gray-700">
                        <tr data-rules-loader-row>
                            <td colspan="5" class="px-6 py-8">
                                <div class="flex items-center justify-center gap-3 text-sm text-gray-500">
                                    <div class="h-6 w-6 rounded-full border-4 border-blue-200 border-t-blue-600 animate-spin" aria-hidden="true"></div>
                                    Memuat data rule...
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="module" src="<?= base_url('js/admin.js') ?>"></script>
<?= $this->endSection() ?>
