<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-8" data-pakar-dashboard>
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Dashboard Pakar</h1>
            <p class="text-sm text-gray-600">Pantau kondisi ibu menyusui dan tindak lanjuti kebutuhan gizi mereka.</p>
        </div>
        <div class="flex items-center gap-3 text-sm text-gray-500">
            <div class="hidden items-center gap-2 sm:flex">
                <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                <span>Status terbaru diperbarui otomatis dari hasil inferensi.</span>
            </div>
            <a
                href="<?= site_url('pakar/consultations') ?>"
                class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >Kelola Konsultasi</a>
        </div>
    </div>
    <div id="dashboard-loading" class="htmx-indicator hidden rounded-lg bg-blue-50 px-4 py-2 text-sm text-blue-700">
        Memuat data ibu menyusui...
    </div>

    <div id="dashboard-data" class="space-y-6">
        <?= view('pakar/partials/dashboard_data', [
            'mothers'       => $mothers,
            'statusSummary' => $statusSummary,
        ]) ?>
    </div>

    <div id="mother-detail-loading" class="htmx-indicator hidden rounded-lg bg-blue-50 px-4 py-2 text-sm text-blue-700">
        Memuat detail ibu menyusui...
    </div>

    <div id="mother-detail-container"></div>
</div>

<noscript>
    <div class="rounded-lg bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
        Aktifkan JavaScript atau gunakan tombol muat ulang untuk memperbarui data.
    </div>
</noscript>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script type="module" src="<?= base_url('js/pakar.js') ?>"></script>
<?= $this->endSection() ?>
