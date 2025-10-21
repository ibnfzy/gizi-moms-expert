<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
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
    ];
    protected $useTimestamps = true;

    public function withInferenceResults(): BaseBuilder
    {
        return $this->builder()
            ->select('rules.*, inference_results.id as inference_result_id')
            ->join('inference_results', 'inference_results.version = rules.version', 'left');
    }
}
