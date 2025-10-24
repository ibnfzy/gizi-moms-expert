<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
/** @var array<int, array<string, mixed>> $schedules */
/** @var array<string, string> $statusOptions */
/** @var string $filterStatus */
/** @var string $tableUrl */
/** @var string $rowUrlTemplate */
?>
<div
    class="space-y-6 text-slate-700 dark:text-slate-300"
    data-pakar-schedules
    data-table-url="<?= esc($tableUrl) ?>"
    data-row-url-template="<?= esc($rowUrlTemplate) ?>"
    data-current-status="<?= esc($filterStatus) ?>"
>
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Jadwal Konsultasi</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Kelola jadwal konsultasi dan catat evaluasi tanpa perlu memuat ulang halaman.
            </p>
        </div>
        <a
            href="<?= site_url('pakar/dashboard') ?>"
            class="inline-flex items-center rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:border-slate-300 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-giziblue focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-slate-600 dark:hover:text-white dark:focus:ring-offset-slate-900"
        >Kembali ke Dashboard</a>
    </div>

    <div
        id="schedule-feedback"
        data-schedule-feedback
        class="hidden rounded-xl border px-4 py-3 text-sm"
        role="status"
        aria-live="polite"
    ></div>

    <?= view('pakar/partials/schedule_filters', [
        'statusOptions' => $statusOptions,
        'filterStatus'  => $filterStatus,
        'tableUrl'      => $tableUrl,
    ]) ?>

    <div class="relative">
        <div
            id="schedule-table-indicator"
            class="htmx-indicator hidden rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm text-blue-700 dark:border-blue-400/30 dark:bg-blue-500/10 dark:text-blue-200"
        >Memuat jadwal konsultasi...</div>
        <div id="schedule-table" class="space-y-4">
            <?= view('pakar/partials/schedule_table', [
                'schedules'    => $schedules,
                'filterStatus' => $filterStatus,
            ]) ?>
        </div>
    </div>
</div>

<?= view('pakar/partials/schedule_evaluation_modal') ?>

<noscript>
    <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-400/40 dark:bg-amber-400/10 dark:text-amber-100">
        Aktifkan JavaScript untuk menggunakan filter dan pembaruan jadwal secara langsung.
    </div>
</noscript>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script type="module" src="<?= base_url('js/pakar.js') ?>"></script>
<?= $this->endSection() ?>
