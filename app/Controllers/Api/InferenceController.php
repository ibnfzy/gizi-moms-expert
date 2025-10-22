<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\ForwardChaining;
use App\Models\InferenceResultModel;
use App\Models\MotherModel;
use App\Models\RuleModel;
use CodeIgniter\HTTP\ResponseInterface;

class InferenceController extends BaseController
{
    protected MotherModel $mothers;
    protected RuleModel $rules;
    protected InferenceResultModel $inferenceResults;
    protected ForwardChaining $engine;

    public function __construct()
    {
        helper('auth');

        $this->mothers = new MotherModel();
        $this->rules = new RuleModel();
        $this->inferenceResults = new InferenceResultModel();
        $this->engine = new ForwardChaining();
    }

    public function run()
    {
        $requestData = get_request_data($this->request);
        $motherId = $requestData['mother_id'] ?? null;

        if (! is_numeric($motherId)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON([
                    'status'  => false,
                    'message' => 'mother_id is required and must be a valid number.',
                ]);
        }

        $mother = $this->mothers->find((int) $motherId);

        if (! $mother) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Mother data not found.',
                ]);
        }

        $facts = $this->buildFactsFromMother($mother);

        $ruleRecords = $this->rules->where('is_active', true)->findAll();
        $parsedRules = $this->parseRules($ruleRecords);

        $inferenceResult = $this->engine->run($facts, $parsedRules);

        $version = $this->resolveRuleVersion($ruleRecords);

        $saved = $this->inferenceResults->insert([
            'mother_id'        => (int) $motherId,
            'version'          => $version,
            'facts_json'       => json_encode($inferenceResult['facts'], JSON_UNESCAPED_UNICODE),
            'fired_rules_json' => json_encode($inferenceResult['fired_rules'], JSON_UNESCAPED_UNICODE),
            'output_json'      => json_encode($inferenceResult['recommendations'], JSON_UNESCAPED_UNICODE),
        ]);

        if ($saved === false) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Failed to save inference result.',
                ]);
        }

        return $this->response->setJSON([
            'status'          => true,
            'facts'           => $inferenceResult['facts'],
            'fired_rules'     => $inferenceResult['fired_rules'],
            'recommendations' => $inferenceResult['recommendations'],
        ]);
    }

    /**
     * @param array<string, mixed> $mother
     *
     * @return array<string, mixed>
     */
    private function buildFactsFromMother(array $mother): array
    {
        $facts = [];
        $jsonFields = ['alergi_json', 'preferensi_json', 'riwayat_json'];

        foreach ($mother as $field => $value) {
            if (in_array($field, ['created_at', 'updated_at'], true)) {
                continue;
            }

            if (in_array($field, $jsonFields, true)) {
                $decoded = json_decode((string) $value, true);

                if (is_array($decoded)) {
                    $facts[$field] = $decoded;
                }

                continue;
            }

            if ($value !== null && $value !== '') {
                $facts[$field] = $value;
            }
        }

        return $facts;
    }

    /**
     * @param list<array<string, mixed>> $ruleRecords
     *
     * @return list<array<string, mixed>>
     */
    private function parseRules(array $ruleRecords): array
    {
        $parsed = [];

        foreach ($ruleRecords as $rule) {
            $decoded = json_decode($rule['json_rule'] ?? '', true);

            if (! is_array($decoded)) {
                continue;
            }

            if (! isset($decoded['when'], $decoded['then']) || ! is_array($decoded['when']) || ! is_array($decoded['then'])) {
                continue;
            }

            $decoded['id'] = $decoded['id'] ?? $rule['id'];
            $decoded['rule_id'] = $rule['id'];
            $decoded['version'] = $rule['version'];

            $parsed[] = $decoded;
        }

        return $parsed;
    }

    /**
     * @param list<array<string, mixed>> $ruleRecords
     */
    private function resolveRuleVersion(array $ruleRecords): ?string
    {
        $versions = [];

        foreach ($ruleRecords as $rule) {
            if (! empty($rule['version'])) {
                $versions[] = $rule['version'];
            }
        }

        if ($versions === []) {
            return null;
        }

        $versions = array_values(array_unique($versions));
        rsort($versions, SORT_NATURAL);

        return $versions[0] ?? null;
    }
}
