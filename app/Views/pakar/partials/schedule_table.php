<?php
/** @var array<int, array<string, mixed>> $schedules */
?>
<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <?php if ($schedules === []): ?>
        <div class="px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
            Belum ada jadwal konsultasi untuk ditampilkan.
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
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
    <?php endif; ?>
</div>
