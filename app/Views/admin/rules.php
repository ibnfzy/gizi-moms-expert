<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div
    class="space-y-6"
    data-admin-rules
    data-rules-endpoint="<?= site_url('api/rules') ?>"
    data-notification-id="admin-rules-notification">
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm shadow-slate-100 ring-1 ring-gray-100 dark:border-black/70 dark:bg-slate-950/70 dark:shadow-black/40 dark:ring-black/60">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-slate-100">Manajemen Rules</h1>
                <p class="text-sm text-gray-500 dark:text-slate-400">Kelola rule basis pengetahuan secara terpusat.</p>
            </div>
            <button
                id="addRuleButton"
                class="inline-flex items-center justify-center rounded-lg border border-blue-600 bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:border-blue-700 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-black/70 dark:focus:ring-offset-slate-950">
                Tambah Rule
            </button>
        </div>

        <div
            id="admin-rules-notification"
            class="hidden rounded-lg border border-transparent px-4 py-3 text-sm font-medium transition-all duration-200"
            role="status"
            aria-live="polite"></div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full border-collapse border border-black/40 text-left text-sm dark:border-gray-300">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-slate-950/70 dark:text-slate-200">
                    <tr>
                        <th scope="col" class="border border-black/40 px-4 py-3 dark:border-gray-300">Nama Rule</th>
                        <th scope="col" class="border border-black/40 px-4 py-3 dark:border-gray-300">Versi</th>
                        <th scope="col" class="border border-black/40 px-4 py-3 dark:border-gray-300">Status</th>
                        <th scope="col" class="border border-black/40 px-4 py-3 text-right dark:border-gray-300">Aksi</th>
                    </tr>
                </thead>
                <tbody id="rulesTableBody" class="text-gray-700 dark:text-slate-200">
                    <tr data-loader-row>
                        <td colspan="4" class="border border-black/40 px-4 py-6 text-center text-sm text-gray-500 dark:border-gray-300 dark:text-slate-400">
                            <div class="flex items-center justify-center gap-3">
                                <div class="h-6 w-6 animate-spin rounded-full border-4 border-blue-200 border-t-blue-600" aria-hidden="true"></div>
                                Memuat data rules...
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div
    id="ruleModal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 px-4 dark:bg-black/70"
    aria-hidden="true">
    <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-slate-950 dark:text-slate-100">
        <div class="flex items-start justify-between border-b border-gray-100 px-6 py-4 dark:border-black/70">
            <div>
                <h2 id="modalTitle" class="text-lg font-semibold text-gray-900 dark:text-slate-100">Tambah Rule</h2>
                <p class="text-sm text-gray-500 dark:text-slate-400">Lengkapi formulir berikut untuk menyimpan rule.</p>
            </div>
            <button id="closeModalButton" class="text-gray-400 transition hover:text-gray-600 focus:outline-none dark:text-slate-500 dark:hover:text-slate-300">
                <span class="sr-only">Tutup</span>
                &times;
            </button>
        </div>

        <form id="ruleForm" class="space-y-5 px-6 py-6">
            <input type="hidden" id="ruleId" />
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="ruleName" class="mb-1 block text-sm font-medium text-gray-700 dark:text-slate-200">Nama Rule</label>
                    <input
                        type="text"
                        id="ruleName"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-black/70 dark:bg-slate-950/40 dark:text-slate-100"
                        placeholder="Contoh: Kebutuhan Kalori"
                        required />
                </div>
                <div>
                    <label for="ruleVersion" class="mb-1 block text-sm font-medium text-gray-700 dark:text-slate-200">Versi</label>
                    <input
                        type="text"
                        id="ruleVersion"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-black/70 dark:bg-slate-950/40 dark:text-slate-100"
                        placeholder="Contoh: v1.0"
                        required />
                </div>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="ruleCondition" class="mb-1 block text-sm font-medium text-gray-700 dark:text-slate-200">Kondisi</label>
                    <textarea
                        id="ruleCondition"
                        class="block h-32 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-black/70 dark:bg-slate-950/40 dark:text-slate-100"
                        placeholder="Deskripsikan kondisi yang perlu dipenuhi"
                        required></textarea>
                </div>
                <div>
                    <label for="ruleRecommendation" class="mb-1 block text-sm font-medium text-gray-700 dark:text-slate-200">Rekomendasi</label>
                    <textarea
                        id="ruleRecommendation"
                        class="block h-32 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-black/70 dark:bg-slate-950/40 dark:text-slate-100"
                        placeholder="Tuliskan rekomendasi yang diberikan"
                        required></textarea>
                </div>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="ruleCategory" class="mb-1 block text-sm font-medium text-gray-700 dark:text-slate-200">Kategori</label>
                    <input
                        type="text"
                        id="ruleCategory"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-black/70 dark:bg-slate-950/40 dark:text-slate-100"
                        placeholder="Contoh: Nutrisi" />
                </div>
                <div>
                    <label for="ruleStatus" class="mb-1 block text-sm font-medium text-gray-700 dark:text-slate-200">Status</label>
                    <input
                        type="text"
                        id="ruleStatus"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-black/70 dark:bg-slate-950/40 dark:text-slate-100"
                        placeholder="Contoh: Aktif" />
                </div>
            </div>
            <p id="ruleDetailsMessage" class="text-xs text-red-500"></p>
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-black/70">
                <button
                    type="button"
                    id="cancelModalButton"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-black/70 dark:text-slate-300 dark:hover:bg-slate-900/50 dark:focus:ring-offset-slate-950">
                    Batal
                </button>
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg border border-blue-600 bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:border-blue-700 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-black/70 dark:focus:ring-offset-slate-950">
                    Simpan Rule
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="module" src="<?= base_url('js/admin.js') ?>"></script>
<?= $this->endSection() ?>