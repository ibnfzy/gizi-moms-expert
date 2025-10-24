<?php
/** @var array<string, string> $statusOptions */
/** @var string $filterStatus */
/** @var string $tableUrl */
?>
<form
    id="schedule-filter-form"
    method="get"
    action="<?= esc($tableUrl) ?>"
    class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800 md:flex-row md:items-end md:justify-between"
    hx-get="<?= esc($tableUrl) ?>"
    hx-target="#schedule-table"
    hx-swap="innerHTML"
    hx-trigger="change from:select, submit"
    hx-include="#schedule-filter-form"
    hx-push-url="true"
    hx-indicator="#schedule-table-indicator"
    data-schedule-filter-form
>
    <div class="flex flex-1 flex-col gap-2">
        <label for="schedule-filter-status" class="text-sm font-medium text-slate-600 dark:text-slate-300">Status Jadwal</label>
        <select
            id="schedule-filter-status"
            name="status"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-giziblue focus:outline-none focus:ring-2 focus:ring-giziblue/40 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
        >
            <?php foreach ($statusOptions as $value => $label): ?>
                <option value="<?= esc($value) ?>" <?= $filterStatus === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="flex items-center gap-3">
        <button
            type="submit"
            class="inline-flex items-center rounded-lg border border-giziblue bg-giziblue px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:border-blue-600 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-giziblue focus:ring-offset-2 dark:border-giziblue/70 dark:hover:border-blue-500 dark:hover:bg-blue-500 dark:focus:ring-offset-slate-900"
        >Terapkan</button>
        <button
            type="button"
            class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:border-slate-400 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-slate-500 dark:hover:text-white dark:focus:ring-offset-slate-900"
            data-schedule-filter-reset
        >Reset</button>
    </div>
</form>
