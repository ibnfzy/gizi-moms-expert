<?php

namespace App\Commands;

use App\Services\ScheduleReminderService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ScheduleReminder extends BaseCommand
{
    protected $group = 'Schedule';
    protected $name = 'schedule:reminder';
    protected $description = 'Kirim pengingat untuk jadwal konsultasi yang akan berlangsung dalam 24 jam.';
    protected $usage = 'schedule:reminder';

    public function run(array $params)
    {
        $service = new ScheduleReminderService();
        $result  = $service->run();

        if ($result['cancelled_count'] > 0) {
            CLI::write("{$result['cancelled_count']} jadwal yang sudah lewat dibatalkan.", 'yellow');
        }

        if ($result['reminders_sent'] === 0) {
            CLI::write('Tidak ada jadwal yang perlu dikirimkan pengingat.', 'yellow');

            return;
        }

        foreach ($result['reminders'] as $reminder) {
            $scheduleId = $reminder['schedule_id'];
            $motherId   = $reminder['mother_id'];

            CLI::write("Pengingat dikirim untuk jadwal ID {$scheduleId} (ibu ID {$motherId}).", 'green');
        }

        CLI::write("Selesai: {$result['reminders_sent']} pengingat dikirim.", 'green');
    }
}
