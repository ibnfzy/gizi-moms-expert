<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\MotherFormatter;
use App\Models\MotherModel;
use CodeIgniter\HTTP\ResponseInterface;

class MotherController extends BaseController
{
    private MotherModel $mothers;
    private MotherFormatter $formatter;

    public function __construct()
    {
        helper('auth');

        $this->mothers   = new MotherModel();
        $this->formatter = new MotherFormatter();
    }

    public function index()
    {
        $records = $this->mothers
            ->withUser()
            ->orderBy('users.name', 'ASC')
            ->get()->getResultArray();

        $payload = array_map(fn(array $mother): array => $this->formatter->present($mother, false, false), $records);

        return successResponse($payload, 'Daftar ibu berhasil dimuat.');
    }

    public function show(int $id)
    {
        $mother = $this->mothers
            ->withUser()
            ->where('mothers.id', $id)
            ->get()->getRowArray();

        if (! is_array($mother)) {
            return errorResponse('Mother data not found.', ResponseInterface::HTTP_NOT_FOUND);
        }

        $payload = $this->formatter->present($mother, true, true);

        return successResponse($payload, 'Data ibu berhasil dimuat.');
    }

    public function update(int $id)
    {
        $user = auth_user();

        if ($user === null) {
            return errorResponse('Unauthorized.', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $mother = $this->mothers->find($id);

        if (! is_array($mother)) {
            return errorResponse('Mother data not found.', ResponseInterface::HTTP_NOT_FOUND);
        }

        $role = strtolower((string) ($user['role'] ?? ''));
        if ($role !== 'ibu' || (int) ($mother['user_id'] ?? 0) !== (int) $user['id']) {
            return errorResponse('You are not allowed to update this mother.', ResponseInterface::HTTP_FORBIDDEN);
        }

        $payload = get_request_data($this->request);

        if ($payload === []) {
            return errorResponse('No data provided for update.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $fields = [];

        if (array_key_exists('bb', $payload)) {
            $value = $this->sanitizeScalar($payload['bb']);
            if ($value === null) {
                $fields['bb'] = null;
            } elseif (! is_numeric($value)) {
                return errorResponse('bb must be a numeric value.', ResponseInterface::HTTP_BAD_REQUEST);
            } else {
                $fields['bb'] = (float) $value;
            }
        }

        if (array_key_exists('tb', $payload)) {
            $value = $this->sanitizeScalar($payload['tb']);
            if ($value === null) {
                $fields['tb'] = null;
            } elseif (! is_numeric($value)) {
                return errorResponse('tb must be a numeric value.', ResponseInterface::HTTP_BAD_REQUEST);
            } else {
                $fields['tb'] = (float) $value;
            }
        }

        if (array_key_exists('umur', $payload)) {
            $value = $this->sanitizeScalar($payload['umur']);
            if ($value === null) {
                $fields['umur'] = null;
            } elseif (filter_var($value, FILTER_VALIDATE_INT) === false) {
                return errorResponse('umur must be an integer.', ResponseInterface::HTTP_BAD_REQUEST);
            } else {
                $fields['umur'] = (int) $value;
            }
        }

        if (array_key_exists('usia_bayi_bln', $payload)) {
            $value = $this->sanitizeScalar($payload['usia_bayi_bln']);
            if ($value === null) {
                $fields['usia_bayi_bln'] = null;
            } elseif (filter_var($value, FILTER_VALIDATE_INT) === false) {
                return errorResponse('usia_bayi_bln must be an integer.', ResponseInterface::HTTP_BAD_REQUEST);
            } else {
                $fields['usia_bayi_bln'] = (int) $value;
            }
        }

        if (array_key_exists('laktasi_tipe', $payload)) {
            $value = $this->sanitizeString($payload['laktasi_tipe']);
            if ($value === null || ! in_array($value, ['eksklusif', 'parsial'], true)) {
                return errorResponse('laktasi_tipe must be either eksklusif or parsial.', ResponseInterface::HTTP_BAD_REQUEST);
            }
            $fields['laktasi_tipe'] = $value;
        }

        if (array_key_exists('aktivitas', $payload)) {
            $value = $this->sanitizeString($payload['aktivitas']);
            if ($value === null || ! in_array($value, ['ringan', 'sedang', 'berat'], true)) {
                return errorResponse('aktivitas must be one of ringan, sedang, or berat.', ResponseInterface::HTTP_BAD_REQUEST);
            }
            $fields['aktivitas'] = $value;
        }

        if (array_key_exists('alergi', $payload) || array_key_exists('alergi_json', $payload)) {
            $fields['alergi_json'] = $this->encodeList(
                $this->normalizeList($payload['alergi'] ?? $payload['alergi_json'] ?? null)
            );
        }

        if (array_key_exists('preferensi', $payload) || array_key_exists('preferensi_json', $payload)) {
            $fields['preferensi_json'] = $this->encodeList(
                $this->normalizeList($payload['preferensi'] ?? $payload['preferensi_json'] ?? null)
            );
        }

        if (array_key_exists('riwayat', $payload) || array_key_exists('riwayat_json', $payload)) {
            $fields['riwayat_json'] = $this->encodeList(
                $this->normalizeList($payload['riwayat'] ?? $payload['riwayat_json'] ?? null)
            );
        }

        if ($fields === []) {
            return errorResponse('No valid fields provided for update.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        if (! $this->mothers->update($id, $fields)) {
            return errorResponse('Failed to update mother profile.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        $updated = $this->mothers
            ->withUser()
            ->where('mothers.id', $id)
            ->get()->getRowArray();

        if (! is_array($updated)) {
            return errorResponse('Mother data not found.', ResponseInterface::HTTP_NOT_FOUND);
        }

        return successResponse(
            $this->formatter->present($updated, true, true),
            'Profil ibu berhasil diperbarui.'
        );
    }

    /**
     * @param mixed $value
     */
    private function sanitizeScalar($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $value = trim($value);

            return $value === '' ? null : $value;
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        return null;
    }

    /**
     * @param mixed $value
     */
    private function sanitizeString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = strtolower(trim((string) $value));

        return $value === '' ? null : $value;
    }

    /**
     * @param mixed $value
     *
     * @return array<int, string>|null
     */
    private function normalizeList($value): ?array
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $value = trim($value);

            if ($value === '') {
                return null;
            }

            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $value = $decoded;
            } else {
                $value = array_map('trim', explode(',', $value));
            }
        }

        if (! is_array($value)) {
            $value = [$value];
        }

        $output = [];
        foreach ($value as $item) {
            if (is_string($item)) {
                $item = trim($item);
            }

            if ($item === null || $item === '') {
                continue;
            }

            $output[] = is_string($item) ? $item : (string) $item;
        }

        if ($output === []) {
            return null;
        }

        return array_values(array_unique($output));
    }

    /**
     * @param array<int, string>|null $list
     */
    private function encodeList(?array $list): ?string
    {
        if ($list === null) {
            return null;
        }

        return json_encode(array_values($list), JSON_UNESCAPED_UNICODE);
    }
}
