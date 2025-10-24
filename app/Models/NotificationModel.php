<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'mother_id',
        'expert_id',
        'title',
        'message',
        'type',
        'schedule_id',
        'is_read',
    ];
    protected $useTimestamps = true;
}
