<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-8 text-slate-700 dark:text-slate-300" data-pakar-dashboard>
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Dashboard Pakar</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400">Pantau kondisi ibu menyusui dan tindak lanjuti kebutuhan gizi mereka.</p>
        </div>
        <div class="flex items-center gap-3 text-sm text-slate-500 dark:text-slate-400">
            <div class="hidden items-center gap-2 sm:flex">
                <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                <span>Status terbaru diperbarui otomatis dari hasil inferensi.</span>
            </div>
            <a
                href="<?= site_url('pakar/schedules') ?>"
                class="inline-flex items-center rounded-md border border-blue-200 px-4 py-2 text-sm font-medium text-giziblue transition hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-giziblue focus:ring-offset-2 dark:border-blue-500/60 dark:text-blue-200 dark:hover:bg-slate-900/50 dark:focus:ring-offset-slate-900"
            >Kelola Jadwal</a>
            <a
                href="<?= site_url('pakar/consultations') ?>"
                class="inline-flex items-center rounded-md border border-giziblue bg-giziblue px-4 py-2 text-sm font-medium text-white shadow hover:border-blue-600 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-giziblue focus:ring-offset-2 dark:border-giziblue/60 dark:focus:ring-offset-slate-900"
            >Kelola Konsultasi</a>
        </div>
    </div>
    <div id="dashboard-loading" class="htmx-indicator hidden rounded-lg bg-blue-50 px-4 py-2 text-sm text-blue-700 dark:bg-slate-800/70 dark:text-blue-200">
        Memuat data ibu menyusui...
    </div>

    <div id="dashboard-data" class="space-y-6">
        <?= view('pakar/partials/dashboard_data', [
            'mothers'       => $mothers,
            'statusSummary' => $statusSummary,
        ]) ?>
    </div>

    <div id="mother-detail-loading" class="htmx-indicator hidden rounded-lg bg-blue-50 px-4 py-2 text-sm text-blue-700 dark:bg-slate-800/70 dark:text-blue-200">
        Memuat detail ibu menyusui...
    </div>

    <div id="mother-detail-container"></div>
</div>

<noscript>
    <div class="rounded-lg bg-yellow-50 px-4 py-3 text-sm text-yellow-800 dark:bg-amber-400/20 dark:text-amber-200">
        Aktifkan JavaScript atau gunakan tombol muat ulang untuk memperbarui data.
    </div>
</noscript>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script type="module" src="<?= base_url('js/pakar.js') ?>"></script>
<?= $this->endSection() ?>
