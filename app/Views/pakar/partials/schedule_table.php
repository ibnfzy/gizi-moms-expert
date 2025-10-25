<?php
/** @var array<int, array<string, mixed>> $schedules */

$scheduleCards = [];

if (! empty($schedules)) {
    foreach ($schedules as $schedule) {
        $scheduleId       = isset($schedule['id']) ? (int) $schedule['id'] : 0;
        $scheduledDisplay = $schedule['scheduled_at']['display'] ?? $schedule['scheduled_at']['raw'] ?? 'Jadwal tidak diketahui';
        $scheduledHuman   = $schedule['scheduled_at']['humanize'] ?? null;
        $location         = trim((string) ($schedule['location'] ?? ''));
        $notes            = trim((string) ($schedule['notes'] ?? ''));
        $mother           = $schedule['mother'] ?? [];
        $motherName       = $mother['name'] ?? 'Tanpa Nama';
        $motherEmail      = $mother['email'] ?? null;
        $status           = $schedule['status'] ?? 'pending';
        $attendance       = $schedule['attendance'] ?? 'pending';
        $evaluation       = $schedule['evaluation'] ?? null;
        $evaluationSummary = trim((string) ($evaluation['summary'] ?? ''));
        $needsFollowUp    = (bool) ($evaluation['follow_up'] ?? false);
        $hasEvaluation    = $evaluation !== null && ($evaluationSummary !== '' || $needsFollowUp);

        $statusClasses = [
            'pending'   => 'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-200',
            'confirmed' => 'bg-sky-100 text-sky-800 dark:bg-sky-500/20 dark:text-sky-200',
            'completed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-200',
            'cancelled' => 'bg-slate-200 text-slate-700 dark:bg-slate-600/40 dark:text-slate-200',
        ];

        $attendanceClasses = [
            'pending'   => 'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-200',
            'confirmed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-200',
            'declined'  => 'bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-200',
        ];

        $statusLabels = [
            'pending'   => 'Menunggu Konfirmasi',
            'confirmed' => 'Terkonfirmasi',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];

        $attendanceLabels = [
            'pending'   => 'Menunggu',
            'confirmed' => 'Hadir',
            'declined'  => 'Tidak Hadir',
        ];

        $statusBadgeClass     = $statusClasses[$status] ?? 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-100';
        $statusBadgeLabel     = $statusLabels[$status] ?? ucfirst($status);
        $attendanceBadgeClass = $attendanceClasses[$attendance] ?? 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-100';
        $attendanceBadgeLabel = $attendanceLabels[$attendance] ?? ucfirst($attendance);

        $evaluationButtonLabel = $hasEvaluation ? 'Kelola Evaluasi' : 'Tandai Selesai';
        $evaluationDatetime    = $schedule['scheduled_at']['display'] ?? $scheduledDisplay;
        $attendanceTargetId    = 'schedule-indicator-' . $scheduleId . '-attendance';

        $motherValue = esc($motherName);
        if (! empty($motherEmail)) {
            $motherValue .= '<div class="mt-1 text-xs text-slate-500 dark:text-slate-400">' . esc($motherEmail) . '</div>';
        }

        $statusHtml = '<div class="flex flex-col gap-2">'
            . '<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ' . esc($statusBadgeClass, 'attr') . '">' . esc($statusBadgeLabel) . '</span>';

        if ($hasEvaluation) {
            $statusHtml .= '<span class="inline-flex items-center rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-medium text-emerald-700 dark:text-emerald-200">Evaluasi tersedia</span>';
        }

        if ($needsFollowUp) {
            $statusHtml .= '<span class="inline-flex items-center rounded-full bg-rose-500/10 px-3 py-1 text-xs font-medium text-rose-700 dark:text-rose-200">Perlu tindak lanjut</span>';
        }

        if ($hasEvaluation && $evaluationSummary !== '') {
            $statusHtml .= '<p class="mt-2 whitespace-pre-line text-sm text-slate-500 dark:text-slate-300"><span class="font-medium text-slate-600 dark:text-slate-200">Ringkasan:</span> ' . esc($evaluationSummary) . '</p>';
        }

        $statusHtml .= '</div>';

        $attendanceHtml = '<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ' . esc($attendanceBadgeClass, 'attr') . '">' . esc($attendanceBadgeLabel) . '</span>';

        $fields = [
            [
                'label'  => 'Ibu',
                'value'  => $motherValue,
                'isHtml' => true,
            ],
            [
                'label'  => 'Status Jadwal',
                'value'  => $statusHtml,
                'isHtml' => true,
            ],
            [
                'label'  => 'Kehadiran',
                'value'  => $attendanceHtml,
                'isHtml' => true,
            ],
        ];

        if ($location !== '') {
            $fields[] = [
                'label' => 'Lokasi',
                'value' => $location,
            ];
        }

        if ($notes !== '') {
            $fields[] = [
                'label'  => 'Catatan',
                'value'  => nl2br(esc($notes)),
                'isHtml' => true,
            ];
        }

        $attendanceForm = '';

        if ($attendance === 'pending') {
            $attendanceForm = '<form class="inline-flex items-center" hx-put="' . esc(site_url('api/schedules/' . $scheduleId . '/attendance'), 'attr')
                . '" hx-target="closest [data-schedule-row]" hx-swap="none" hx-indicator="#' . esc($attendanceTargetId, 'attr')
                . '" data-schedule-refresh="true" data-schedule-id="' . esc((string) $scheduleId, 'attr') . '">
                    <input type="hidden" name="attendance" value="confirmed">
                    <button type="submit" class="inline-flex items-center rounded-lg border border-emerald-600 bg-emerald-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow-sm transition hover:border-emerald-700 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:border-emerald-500 dark:bg-emerald-500 dark:hover:border-emerald-400 dark:hover:bg-emerald-400 dark:focus:ring-offset-slate-900">Konfirmasi Kehadiran</button>
                    <span id="' . esc($attendanceTargetId, 'attr') . '" class="htmx-indicator ml-3 hidden text-xs text-slate-500 dark:text-slate-400">Memperbarui...</span>
                </form>';
        }

        $evaluationButton = '<button type="button" class="inline-flex items-center rounded-lg border border-giziblue bg-white px-4 py-2 text-xs font-semibold uppercase tracking-wide text-giziblue shadow-sm transition hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-giziblue focus:ring-offset-2 dark:border-giziblue/70 dark:bg-slate-900 dark:text-giziblue dark:hover:bg-slate-800 dark:focus:ring-offset-slate-900" data-schedule-evaluation-button data-schedule-id="' . esc((string) $scheduleId, 'attr') . '" data-schedule-name="' . esc($motherName, 'attr') . '" data-schedule-datetime="' . esc($evaluationDatetime, 'attr') . '" data-evaluation-summary="' . esc($evaluationSummary, 'attr') . '" data-evaluation-follow-up="' . ($needsFollowUp ? '1' : '0') . '" data-schedule-evaluation-url="' . esc(site_url('api/schedules/' . $scheduleId . '/evaluation'), 'attr') . '">' . esc($evaluationButtonLabel) . '</button>';

        $actions = [];

        if ($attendanceForm !== '') {
            $actions[] = [
                'content' => $attendanceForm,
                'isHtml'  => true,
            ];
        }

        $actions[] = [
            'content' => $evaluationButton,
            'isHtml'  => true,
        ];

        $scheduleCards[] = [
            'title'      => $scheduledDisplay,
            'subtitle'   => $scheduledHuman,
            'fields'     => $fields,
            'actions'    => $actions,
            'attributes' => [
                'data-schedule-row' => '1',
                'id'                => 'schedule-card-' . $scheduleId,
            ],
        ];
    }
}
?>
<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <?php if ($schedules === []): ?>
        <div class="px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
            Belum ada jadwal konsultasi untuk ditampilkan.
        </div>
    <?php else: ?>
        <div class="border-t border-slate-200 px-6 py-6 dark:border-slate-700 md:hidden">
            <?= view('components/responsive_table_cards', ['items' => $scheduleCards]) ?>
        </div>
        <div class="hidden border-t border-slate-200 dark:border-slate-700 md:block">
            <div class="overflow-x-auto">
                <table class="min-w-[64rem] divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-900 dark:text-slate-300">
                        <tr>
                            <th scope="col" class="px-6 py-3">Jadwal</th>
                            <th scope="col" class="px-6 py-3">Ibu</th>
                            <th scope="col" class="px-6 py-3">Status Jadwal</th>
                            <th scope="col" class="px-6 py-3">Kehadiran</th>
                            <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white text-sm dark:divide-slate-700 dark:bg-slate-800">
                        <?php foreach ($schedules as $schedule): ?>
                            <?= view('pakar/partials/schedule_row', ['schedule' => $schedule]) ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
