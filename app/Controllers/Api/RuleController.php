<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\RuleModel;
use CodeIgniter\HTTP\ResponseInterface;

class RuleController extends BaseController
{
    protected RuleModel $rules;

    public function __construct()
    {
        $this->rules = new RuleModel();
        helper('auth');
    }

    public function index()
    {
        $rules = $this->rules
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $data = array_map([$this, 'formatRule'], $rules);

        return $this->response->setJSON([
            'status' => true,
            'data'   => $data,
        ]);
    }

    public function create()
    {
        $payload = get_request_data($this->request);

        $usingJsonRule = array_key_exists('json_rule', $payload);

        $validationRules = [
            'name'    => 'required|string',
            'version' => 'required|string',
        ];

        if ($usingJsonRule) {
            $validationRules['json_rule'] = 'required|string';
        } else {
            $validationRules['condition'] = 'required|string';
            $validationRules['recommendation'] = 'required|string';
            $validationRules['category'] = 'permit_empty|string';
            $validationRules['status'] = 'permit_empty|string';
        }

        if (! $this->validateData($payload, $validationRules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ]);
        }

        $resolution = $this->resolveJsonRule($payload, null, ! $usingJsonRule);

        if (($resolution['error'] ?? null) !== null) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON([
                    'status'  => false,
                    'message' => $resolution['error'],
                ]);
        }

        $jsonRule = $resolution['json'] ?? null;

        if ($jsonRule === null) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Detail rule tidak valid.',
                ]);
        }

        $ruleId = $this->rules->insert([
            'name'           => $payload['name'],
            'version'        => $payload['version'],
            'json_rule'      => $jsonRule,
            'effective_from' => $payload['effective_from'] ?? null,
            'is_active'      => array_key_exists('is_active', $payload)
                ? (bool) $payload['is_active']
                : true,
        ], true);

        if (! $ruleId) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Gagal menyimpan rule baru.',
                ]);
        }

        $rule = $this->rules->find($ruleId);

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON([
                'status'  => true,
                'message' => 'Rule berhasil ditambahkan.',
                'data'    => $this->formatRule($rule ?? []),
            ]);
    }

    public function update(int $id)
    {
        $existing = $this->rules->find($id);

        if (! $existing) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Rule tidak ditemukan.',
                ]);
        }

        $payload = get_request_data($this->request);

        $fields = array_intersect_key($payload, array_flip([
            'name',
            'version',
            'json_rule',
            'effective_from',
            'is_active',
        ]));

        $detailPayload = array_intersect_key($payload, array_flip([
            'condition',
            'recommendation',
            'category',
            'status',
        ]));

        if ($fields === [] && $detailPayload === []) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Tidak ada data yang diubah.',
                ]);
        }

        $existingDetails = $this->decodeRuleDetails($existing['json_rule'] ?? '') ?: [];
        $resolution = $this->resolveJsonRule($payload, $existingDetails, false);

        if (($resolution['error'] ?? null) !== null) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON([
                    'status'  => false,
                    'message' => $resolution['error'],
                ]);
        }

        if (($resolution['json'] ?? null) !== null) {
            $fields['json_rule'] = $resolution['json'];
        } elseif (array_key_exists('json_rule', $fields) && ! $this->isValidJson((string) $fields['json_rule'])) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Format JSON rule tidak valid.',
                ]);
        }

        if (array_key_exists('is_active', $fields)) {
            $fields['is_active'] = (bool) $fields['is_active'];
        }

        if (! $this->rules->update($id, $fields)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Gagal memperbarui rule.',
                ]);
        }

        $updated = $this->rules->find($id);

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Rule berhasil diperbarui.',
            'data'    => $this->formatRule($updated ?? []),
        ]);
    }

    public function delete(int $id)
    {
        $existing = $this->rules->find($id);

        if (! $existing) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Rule tidak ditemukan.',
                ]);
        }

        if (! $this->rules->delete($id)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Gagal menghapus rule.',
                ]);
        }

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Rule berhasil dihapus.',
        ]);
    }

    private function isValidJson(string $json): bool
    {
        json_decode($json);

        return json_last_error() === JSON_ERROR_NONE;
    }

    private function formatRule(array $rule): array
    {
        if ($rule === []) {
            return [];
        }

        $details = $this->decodeRuleDetails($rule['json_rule'] ?? '') ?? [];

        return [
            'id'             => $rule['id'] ?? null,
            'name'           => $rule['name'] ?? '',
            'json_rule'      => $rule['json_rule'] ?? '',
            'version'        => $rule['version'] ?? '',
            'effective_from' => $rule['effective_from'] ?? null,
            'is_active'      => isset($rule['is_active']) ? (bool) $rule['is_active'] : false,
            'created_at'     => $rule['created_at'] ?? null,
            'updated_at'     => $rule['updated_at'] ?? null,
            'details'        => $details,
        ];
    }

    private function decodeRuleDetails(?string $json): array
    {
        $defaults = [
            'condition'      => '',
            'recommendation' => '',
            'category'       => '',
            'status'         => '',
        ];

        if (! $json) {
            return $defaults;
        }

        $decoded = json_decode($json, true);

        if (! is_array($decoded)) {
            return $defaults;
        }

        return array_merge($defaults, array_intersect_key($decoded, $defaults));
    }

    /**
     * @return array{json: string|null, error?: string|null}
     */
    private function resolveJsonRule(array $payload, ?array $currentDetails = null, bool $requireDetails = false): array
    {
        if (array_key_exists('json_rule', $payload)) {
            $json = (string) $payload['json_rule'];

            if (! $this->isValidJson($json)) {
                return [
                    'json'  => null,
                    'error' => 'Format JSON rule tidak valid.',
                ];
            }

            return ['json' => $json];
        }

        $detailKeys = ['condition', 'recommendation', 'category', 'status'];
        $details = $currentDetails ?? [];
        $hasDetailInput = false;

        foreach ($detailKeys as $key) {
            if (! array_key_exists($key, $payload)) {
                continue;
            }

            $hasDetailInput = true;
            $value = $payload[$key];

            if (is_string($value)) {
                $value = trim($value);
            }

            if ($value === '' && in_array($key, ['category', 'status'], true)) {
                unset($details[$key]);
                continue;
            }

            $details[$key] = $value;
        }

        if (! $hasDetailInput) {
            if ($requireDetails) {
                return [
                    'json'  => null,
                    'error' => 'Detail rule tidak ditemukan.',
                ];
            }

            return ['json' => null];
        }

        if (empty($details['condition']) || empty($details['recommendation'])) {
            return [
                'json'  => null,
                'error' => 'Kondisi dan rekomendasi rule wajib diisi.',
            ];
        }

        return [
            'json' => json_encode($details, JSON_UNESCAPED_UNICODE),
        ];
    }
}
