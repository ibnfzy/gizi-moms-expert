<?php

namespace App\Models;

use CodeIgniter\Model;

class RuleModel extends Model
{
    protected $table = 'rules';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'name',
        'json_rule',
        'version',
        'effective_from',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = false;
}
