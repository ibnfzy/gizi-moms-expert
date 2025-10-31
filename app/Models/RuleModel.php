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
        'komentar_pakar',
    ];
    protected $useTimestamps = true;

    protected $beforeInsert = ['ensureKomentarPakar'];

    protected function ensureKomentarPakar(array $data): array
    {
        if (! isset($data['data']) || ! is_array($data['data'])) {
            $data['data'] = ['komentar_pakar' => null];

            return $data;
        }

        if (! array_key_exists('komentar_pakar', $data['data'])) {
            $data['data']['komentar_pakar'] = null;
        }

        return $data;
    }

    public function withInferenceResults(): BaseBuilder
    {
        return $this->builder()
            ->select('rules.*, inference_results.id as inference_result_id')
            ->join('inference_results', 'inference_results.version = rules.version', 'left');
    }
}
