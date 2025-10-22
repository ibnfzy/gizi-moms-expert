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

        $validationRules = [
            'name'      => 'required|string',
            'version'   => 'required|string',
            'json_rule' => 'required|string',
        ];

        if (! $this->validateData($payload, $validationRules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ]);
        }

        if (! $this->isValidJson($payload['json_rule'])) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Format JSON rule tidak valid.',
                ]);
        }

        $ruleId = $this->rules->insert([
            'name'           => $payload['name'],
            'version'        => $payload['version'],
            'json_rule'      => $payload['json_rule'],
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

        if ($fields === []) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Tidak ada data yang diubah.',
                ]);
        }

        if (array_key_exists('json_rule', $fields) && ! $this->isValidJson((string) $fields['json_rule'])) {
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

        return [
            'id'             => $rule['id'] ?? null,
            'name'           => $rule['name'] ?? '',
            'json_rule'      => $rule['json_rule'] ?? '',
            'version'        => $rule['version'] ?? '',
            'effective_from' => $rule['effective_from'] ?? null,
            'is_active'      => isset($rule['is_active']) ? (bool) $rule['is_active'] : false,
            'created_at'     => $rule['created_at'] ?? null,
            'updated_at'     => $rule['updated_at'] ?? null,
        ];
    }
}
