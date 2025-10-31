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
        if ($response = $this->ensureAdmin()) {
            return $response;
        }

        $rules = $this->rules
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();

        $data = array_map([$this, 'formatRule'], $rules);

        return successResponse($data, 'Daftar rule berhasil dimuat.');
    }

    public function create()
    {
        if ($response = $this->ensureAdmin()) {
            return $response;
        }

        $payload = get_request_data($this->request);

        $usingJsonRule = array_key_exists('json_rule', $payload);

        $validationRules = [
            'name'           => 'required|string',
            'version'        => 'required|string',
            'komentar_pakar' => 'permit_empty|string',
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
            return errorResponse(
                'Data rule tidak valid.',
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->validator->getErrors()
            );
        }

        $resolution = $this->resolveJsonRule($payload, null, ! $usingJsonRule);

        if (($resolution['error'] ?? null) !== null) {
            return errorResponse(
                $resolution['error'],
                ResponseInterface::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $jsonRule = $resolution['json'] ?? null;

        if ($jsonRule === null) {
            return errorResponse('Detail rule tidak valid.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $ruleId = $this->rules->insert([
            'name'           => $payload['name'],
            'version'        => $payload['version'],
            'json_rule'      => $jsonRule,
            'effective_from' => $payload['effective_from'] ?? null,
            'is_active'      => array_key_exists('is_active', $payload)
                ? (bool) $payload['is_active']
                : true,
            'komentar_pakar' => array_key_exists('komentar_pakar', $payload)
                ? ($payload['komentar_pakar'] !== '' ? $payload['komentar_pakar'] : null)
                : null,
        ], true);

        if (! $ruleId) {
            return errorResponse('Gagal menyimpan rule baru.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        $rule = $this->rules->find($ruleId);

        return successResponse(
            $this->formatRule($rule ?? []),
            'Rule berhasil ditambahkan.',
            ResponseInterface::HTTP_CREATED
        );
    }

    public function update(int $id)
    {
        if ($response = $this->ensureAdmin()) {
            return $response;
        }

        $existing = $this->rules->find($id);

        if (! $existing) {
            return errorResponse('Rule tidak ditemukan.', ResponseInterface::HTTP_NOT_FOUND);
        }

        $payload = get_request_data($this->request);

        $fields = array_intersect_key($payload, array_flip([
            'name',
            'version',
            'json_rule',
            'effective_from',
            'is_active',
            'komentar_pakar',
        ]));

        $detailPayload = array_intersect_key($payload, array_flip([
            'condition',
            'recommendation',
            'category',
            'status',
        ]));

        if ($fields === [] && $detailPayload === []) {
            return errorResponse('Tidak ada data yang diubah.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $existingDetails = $this->decodeRuleDetails($existing['json_rule'] ?? '') ?: [];
        $resolution = $this->resolveJsonRule($payload, $existingDetails, false);

        if (($resolution['error'] ?? null) !== null) {
            return errorResponse(
                $resolution['error'],
                ResponseInterface::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if (($resolution['json'] ?? null) !== null) {
            $fields['json_rule'] = $resolution['json'];
        } elseif (array_key_exists('json_rule', $fields) && ! $this->isValidJson((string) $fields['json_rule'])) {
            return errorResponse('Format JSON rule tidak valid.', ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (array_key_exists('is_active', $fields)) {
            $fields['is_active'] = (bool) $fields['is_active'];
        }

        $fields['version'] = $this->incrementVersion((string) ($existing['version'] ?? ''));
        $fields['komentar_pakar'] = null;

        if (! $this->rules->update($id, $fields)) {
            return errorResponse('Gagal memperbarui rule.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        $updated = $this->rules->find($id);

        return successResponse(
            $this->formatRule($updated ?? []),
            'Rule berhasil diperbarui.'
        );
    }

    public function delete(int $id)
    {
        if ($response = $this->ensureAdmin()) {
            return $response;
        }

        $existing = $this->rules->find($id);

        if (! $existing) {
            return errorResponse('Rule tidak ditemukan.', ResponseInterface::HTTP_NOT_FOUND);
        }

        if (! $this->rules->delete($id)) {
            return errorResponse('Gagal menghapus rule.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        return successResponse(null, 'Rule berhasil dihapus.');
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
            'komentar_pakar' => $rule['komentar_pakar'] ?? null,
        ];
    }

    private function incrementVersion(string $current): string
    {
        $trimmed = trim($current);

        if ($trimmed === '') {
            return '1.0.0';
        }

        if (preg_match('/^(?<prefix>[^0-9]*)(?<numbers>\d+(?:\.\d+)*)(?<suffix>.*)$/', $trimmed, $matches) === 1) {
            $numbers = array_map('intval', explode('.', $matches['numbers']));

            if ($numbers !== []) {
                $lastIndex = count($numbers) - 1;
                $numbers[$lastIndex]++;

                return $matches['prefix'] . implode('.', $numbers) . $matches['suffix'];
            }
        }

        if (is_numeric($trimmed)) {
            return (string) ((int) $trimmed + 1);
        }

        return $trimmed . '.1';
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

    private function ensureAdmin(): ?ResponseInterface
    {
        $user = auth_user();

        if ($user === null) {
            return errorResponse('Unauthorized.', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $role = strtolower((string) ($user['role'] ?? ''));

        if ($role !== 'admin') {
            return errorResponse('You do not have permission to manage rules.', ResponseInterface::HTTP_FORBIDDEN);
        }

        return null;
    }
}
