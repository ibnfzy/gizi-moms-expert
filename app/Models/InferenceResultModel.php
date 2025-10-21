<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

class InferenceResultModel extends Model
{
    protected $table = 'inference_results';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'mother_id',
        'version',
        'facts_json',
        'fired_rules_json',
        'output_json',
    ];
    protected $useTimestamps = false;

    public function withMother(): BaseBuilder
    {
        return $this->builder()
            ->select('inference_results.*, mothers.user_id as mother_user_id')
            ->join('mothers', 'mothers.id = inference_results.mother_id');
    }

    public function withRuleVersion(): BaseBuilder
    {
        return $this->builder()
            ->select('inference_results.*, rules.name as rule_name')
            ->join('rules', 'rules.version = inference_results.version', 'left');
    }
}
