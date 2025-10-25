<?php
/** @var array<string, mixed> $schedule */

$scheduleId        = isset($schedule['id']) ? (int) $schedule['id'] : 0;
$scheduledDisplay  = $schedule['scheduled_at']['display'] ?? $schedule['scheduled_at']['raw'] ?? 'Jadwal tidak diketahui';
$scheduledHuman    = $schedule['scheduled_at']['humanize'] ?? null;
$location          = trim((string) ($schedule['location'] ?? ''));
$notes             = trim((string) ($schedule['notes'] ?? ''));
$mother            = $schedule['mother'] ?? [];
$motherName        = $mother['name'] ?? 'Tanpa Nama';
$motherEmail       = $mother['email'] ?? null;
$status            = $schedule['status'] ?? 'pending';
$attendance        = $schedule['attendance'] ?? 'pending';
$evaluation        = $schedule['evaluation'] ?? null;
$evaluationSummary = trim((string) ($evaluation['summary'] ?? ''));
$needsFollowUp     = (bool) ($evaluation['follow_up'] ?? false);
$hasEvaluation     = $evaluation !== null && ($evaluationSummary !== '' || $needsFollowUp);

$statusLabels = [
    'pending'   => 'Menunggu Konfirmasi',
    'confirmed' => 'Terkonfirmasi',
    'completed' => 'Selesai',
    'cancelled' => 'Dibatalkan',
];

$statusClasses = [
    'pending'   => 'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-200',
    'confirmed' => 'bg-sky-100 text-sky-800 dark:bg-sky-500/20 dark:text-sky-200',
    'completed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-200',
    'cancelled' => 'bg-slate-200 text-slate-700 dark:bg-slate-600/40 dark:text-slate-200',
];

$attendanceLabels = [
    'pending'   => 'Menunggu',
    'confirmed' => 'Hadir',
    'declined'  => 'Tidak Hadir',
];

$attendanceClasses = [
    'pending'   => 'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-200',
    'confirmed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-200',
    'declined'  => 'bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-200',
];

$statusBadgeClass     = $statusClasses[$status] ?? 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-100';
$statusBadgeLabel     = $statusLabels[$status] ?? ucfirst($status);
$attendanceBadgeClass = $attendanceClasses[$attendance] ?? 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-100';
$attendanceBadgeLabel = $attendanceLabels[$attendance] ?? ucfirst($attendance);

$evaluationButtonLabel = $hasEvaluation ? 'Kelola Evaluasi' : 'Tandai Selesai';
$evaluationDatetime    = $schedule['scheduled_at']['display'] ?? $scheduledDisplay;
?>
<tr id="schedule-row-<?= esc($scheduleId) ?>" data-schedule-row>
    <td class="px-6 py-4 align-top">
        <div class="font-semibold text-slate-900 dark:text-slate-100"><?= esc($scheduledDisplay) ?></div>
        <?php if (! empty($scheduledHuman)): ?>
            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400"><?= esc($scheduledHuman) ?></div>
        <?php endif; ?>
        <?php if ($location !== ''): ?>
            <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                <span class="font-medium text-slate-700 dark:text-slate-200">Lokasi:</span>
                <?= esc($location) ?>
            </div>
        <?php endif; ?>
        <?php if ($notes !== ''): ?>
            <p class="mt-2 whitespace-pre-line text-sm text-slate-500 dark:text-slate-400">
                <span class="font-medium text-slate-600 dark:text-slate-300">Catatan:</span>
                <?= esc($notes) ?>
            </p>
        <?php endif; ?>
    </td>
    <td class="px-6 py-4 align-top">
        <div class="font-medium text-slate-900 dark:text-slate-100"><?= esc($motherName) ?></div>
        <?php if (! empty($motherEmail)): ?>
            <div class="text-sm text-slate-500 dark:text-slate-400"><?= esc($motherEmail) ?></div>
        <?php endif; ?>
    </td>
    <td class="px-6 py-4 align-top">
        <div class="flex flex-col gap-2">
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?= esc($statusBadgeClass) ?>">
                <?= esc($statusBadgeLabel) ?>
            </span>
            <?php if ($hasEvaluation): ?>
                <span class="inline-flex items-center rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-medium text-emerald-700 dark:text-emerald-200">
                    Evaluasi tersedia
                </span>
            <?php endif; ?>
            <?php if ($needsFollowUp): ?>
                <span class="inline-flex items-center rounded-full bg-rose-500/10 px-3 py-1 text-xs font-medium text-rose-700 dark:text-rose-200">
                    Perlu tindak lanjut
                </span>
            <?php endif; ?>
        </div>
        <?php if ($hasEvaluation && $evaluationSummary !== ''): ?>
            <p class="mt-3 whitespace-pre-line text-sm text-slate-500 dark:text-slate-300">
                <span class="font-medium text-slate-600 dark:text-slate-200">Ringkasan:</span>
                <?= esc($evaluationSummary) ?>
            </p>
        <?php endif; ?>
    </td>
    <td class="px-6 py-4 align-top">
        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?= esc($attendanceBadgeClass) ?>">
            <?= esc($attendanceBadgeLabel) ?>
        </span>
    </td>
    <td class="px-6 py-4 align-top">
        <div class="flex flex-col items-end gap-2 text-right">
            <?php if ($attendance === 'pending'): ?>
                <form
                    class="inline-flex items-center"
                    hx-put="<?= esc(site_url('api/schedules/' . $scheduleId . '/attendance')) ?>"
                    hx-target="closest [data-schedule-row]"
                    hx-swap="none"
                    hx-indicator="#schedule-indicator-<?= esc($scheduleId) ?>-attendance"
                    data-schedule-refresh="true"
                    data-schedule-id="<?= esc($scheduleId) ?>"
                >
                    <input type="hidden" name="attendance" value="confirmed">
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-lg border border-emerald-600 bg-emerald-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow-sm transition hover:border-emerald-700 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:border-emerald-500 dark:bg-emerald-500 dark:hover:border-emerald-400 dark:hover:bg-emerald-400 dark:focus:ring-offset-slate-900"
                    >Konfirmasi Kehadiran</button>
                    <span
                        id="schedule-indicator-<?= esc($scheduleId) ?>-attendance"
                        class="htmx-indicator ml-3 hidden text-xs text-slate-500 dark:text-slate-400"
                    >Memperbarui...</span>
                </form>
            <?php endif; ?>
            <button
                type="button"
                class="inline-flex items-center rounded-lg border border-giziblue bg-white px-4 py-2 text-xs font-semibold uppercase tracking-wide text-giziblue shadow-sm transition hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-giziblue focus:ring-offset-2 dark:border-giziblue/70 dark:bg-slate-900 dark:text-giziblue dark:hover:bg-slate-800 dark:focus:ring-offset-slate-900"
                data-schedule-evaluation-button
                data-schedule-id="<?= esc($scheduleId) ?>"
                data-schedule-name="<?= esc($motherName, 'attr') ?>"
                data-schedule-datetime="<?= esc($evaluationDatetime, 'attr') ?>"
                data-evaluation-summary="<?= esc($evaluationSummary, 'attr') ?>"
                data-evaluation-follow-up="<?= $needsFollowUp ? '1' : '0' ?>"
                data-schedule-evaluation-url="<?= esc(site_url('api/schedules/' . $scheduleId . '/evaluation'), 'attr') ?>"
            ><?= esc($evaluationButtonLabel) ?></button>
        </div>
    </td>
</tr>
