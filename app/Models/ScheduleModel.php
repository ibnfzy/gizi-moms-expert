<?php

namespace App\Models;

use CodeIgniter\Model;

class ScheduleModel extends Model
{
    protected $table = 'schedules';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'mother_id',
        'expert_id',
        'scheduled_at',
        'status',
        'location',
        'notes',
        'reminder_sent',
        'attendance',
        'evaluation_json',
    ];
    protected $useTimestamps = true;
}
