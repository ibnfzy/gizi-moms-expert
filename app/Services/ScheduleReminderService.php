<?php

namespace App\Services;

use App\Models\NotificationModel;
use App\Models\ScheduleModel;
use CodeIgniter\I18n\Time;

/**
 * Handle reminder generation for schedules approaching their start time.
 */
class ScheduleReminderService
{
    private ScheduleModel $schedules;
    private NotificationModel $notifications;

    public function __construct(?ScheduleModel $schedules = null, ?NotificationModel $notifications = null)
    {
        $this->schedules     = $schedules ?? new ScheduleModel();
        $this->notifications = $notifications ?? new NotificationModel();
    }

    /**
     * Execute the reminder flow and return a summary of the work performed.
     *
     * @return array{
     *     cancelled_count: int,
     *     cancelled_schedule_ids: list<int>,
     *     reminders_sent: int,
     *     reminders: list<array{
     *         schedule_id: int,
     *         mother_id: int,
     *         expert_id: (int|null),
     *         scheduled_at: string,
     *         message: string
     *     }>
     * }
     */
    public function run(?Time $now = null): array
    {
        $timezone = app_timezone();
        $now ??= Time::now($timezone);
        $windowStart = (clone $now)->addHours(23);
        $windowEnd   = (clone $now)->addHours(25);

        $cancelled = $this->cancelExpiredSchedules($now);

        $schedules = $this->schedules
            ->where('status', 'confirmed')
            ->where('reminder_sent', 0)
            ->where('scheduled_at >=', $windowStart->toDateTimeString())
            ->where('scheduled_at <=', $windowEnd->toDateTimeString())
            ->findAll();

        $reminders = [];

        foreach ($schedules as $schedule) {
            $scheduledTime = Time::parse($schedule['scheduled_at'], $timezone);
            $message       = sprintf('Besok pukul %s, jangan lupa hadir ya!', $scheduledTime->format('H:i'));

            $this->notifications->insert([
                'mother_id'    => $schedule['mother_id'],
                'expert_id'    => $schedule['expert_id'] ?? null,
                'schedule_id'  => $schedule['id'],
                'type'         => 'schedule-reminder',
                'title'        => 'Pengingat Konsultasi',
                'message'      => $message,
            ]);

            $this->schedules->update((int) $schedule['id'], ['reminder_sent' => 1]);

            $reminders[] = [
                'schedule_id'  => (int) $schedule['id'],
                'mother_id'    => (int) $schedule['mother_id'],
                'expert_id'    => isset($schedule['expert_id']) ? (int) $schedule['expert_id'] : null,
                'scheduled_at' => $scheduledTime->toDateTimeString(),
                'message'      => $message,
            ];
        }

        return [
            'cancelled_count'       => $cancelled['count'],
            'cancelled_schedule_ids' => $cancelled['ids'],
            'reminders_sent'        => count($reminders),
            'reminders'             => $reminders,
        ];
    }

    /**
     * @return array{count: int, ids: list<int>}
     */
    private function cancelExpiredSchedules(Time $now): array
    {
        $expiredSchedules = $this->schedules
            ->where('scheduled_at <', $now->toDateTimeString())
            ->whereIn('status', ['pending', 'confirmed'])
            ->findAll();

        if ($expiredSchedules === []) {
            return ['count' => 0, 'ids' => []];
        }

        $ids = [];

        foreach ($expiredSchedules as $schedule) {
            $this->schedules->update((int) $schedule['id'], ['status' => 'cancelled']);
            $ids[] = (int) $schedule['id'];
        }

        return [
            'count' => count($ids),
            'ids'   => $ids,
        ];
    }
}
