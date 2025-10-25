<?php

namespace App\Commands;

use App\Models\NotificationModel;
use App\Models\ScheduleModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\I18n\Time;

class ScheduleReminder extends BaseCommand
{
    protected $group = 'Schedule';
    protected $name = 'schedule:reminder';
    protected $description = 'Kirim pengingat untuk jadwal konsultasi yang akan berlangsung dalam 24 jam.';
    protected $usage = 'schedule:reminder';

    public function run(array $params)
    {
        $timezone = app_timezone();
        $now = Time::now($timezone);
        $windowStart = (clone $now)->addHours(23);
        $windowEnd = (clone $now)->addHours(25);

        $scheduleModel = new ScheduleModel();

        $cancelled = $this->cancelExpiredSchedules($scheduleModel, $now);

        if ($cancelled > 0) {
            CLI::write("{$cancelled} jadwal yang sudah lewat dibatalkan.", 'yellow');
        }

        $schedules = $scheduleModel
            ->where('status', 'confirmed')
            ->where('reminder_sent', 0)
            ->where('scheduled_at >=', $windowStart->toDateTimeString())
            ->where('scheduled_at <=', $windowEnd->toDateTimeString())
            ->findAll();

        if (empty($schedules)) {
            CLI::write('Tidak ada jadwal yang perlu dikirimkan pengingat.', 'yellow');

            return;
        }

        $notificationModel = new NotificationModel();
        $processed = 0;

        foreach ($schedules as $schedule) {
            $scheduledTime = Time::parse($schedule['scheduled_at'], $timezone);
            $formattedTime = $scheduledTime->format('H:i');

            $notificationModel->insert([
                'mother_id'    => $schedule['mother_id'],
                'expert_id'    => $schedule['expert_id'] ?? null,
                'schedule_id'  => $schedule['id'],
                'type'         => 'schedule-reminder',
                'title'        => 'Pengingat Konsultasi',
                'message'      => "Besok pukul {$formattedTime}, jangan lupa hadir ya!",
            ]);

            $scheduleModel->update($schedule['id'], ['reminder_sent' => 1]);

            CLI::write("Pengingat dikirim untuk jadwal ID {$schedule['id']} (ibu ID {$schedule['mother_id']}).", 'green');
            $processed++;
        }

        CLI::write("Selesai: {$processed} pengingat dikirim.", 'green');
    }

    private function cancelExpiredSchedules(ScheduleModel $scheduleModel, Time $now): int
    {
        $expiredSchedules = $scheduleModel
            ->where('scheduled_at <', $now->toDateTimeString())
            ->whereIn('status', ['pending', 'confirmed'])
            ->findAll();

        if ($expiredSchedules === []) {
            return 0;
        }

        $cancelled = 0;

        foreach ($expiredSchedules as $schedule) {
            $scheduleModel->update((int) $schedule['id'], ['status' => 'cancelled']);
            $cancelled++;
        }

        return $cancelled;
    }
}
