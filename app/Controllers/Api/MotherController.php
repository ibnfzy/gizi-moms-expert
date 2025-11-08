<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\MotherFormatter;
use App\Models\MotherModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class MotherController extends BaseController
{
    private MotherModel $mothers;
    private UserModel $users;
    private MotherFormatter $formatter;

    public function __construct()
    {
        helper('auth');

        $this->mothers   = new MotherModel();
        $this->users     = new UserModel();
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
        $mother = $this->mothers->find($id);

        if (! is_array($mother)) {
            return errorResponse('Mother data not found.', ResponseInterface::HTTP_NOT_FOUND);
        }

        $currentUser = auth_user();
        if (is_array($currentUser) && strtolower((string) ($currentUser['role'] ?? '')) === 'ibu') {
            $motherId = isset($currentUser['motherId']) ? (int) $currentUser['motherId'] : null;
            if ($motherId === null || $motherId !== (int) $id) {
                return errorResponse(
                    'You do not have permission to access this resource.',
                    ResponseInterface::HTTP_FORBIDDEN
                );
            }
        }

        $payload = get_request_data($this->request);
        if (! is_array($payload) || $payload === []) {
            return errorResponse('Tidak ada data yang diperbarui.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $hasUserField = array_key_exists('name', $payload)
            || array_key_exists('email', $payload)
            || array_key_exists('password', $payload);

        $userId = isset($mother['user_id']) ? (int) $mother['user_id'] : 0;
        if ($hasUserField && $userId <= 0) {
            return errorResponse(
                'Data pengguna untuk ibu ini tidak tersedia.',
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        $validationData = [];
        $validationRules = [];

        $sanitizedName = null;
        if (array_key_exists('name', $payload)) {
            $sanitizedName = isset($payload['name']) ? trim((string) $payload['name']) : '';
            $validationData['name'] = $sanitizedName;
            $validationRules['name'] = 'required|string|min_length[3]|max_length[100]';
        }

        $sanitizedEmail = null;
        if (array_key_exists('email', $payload)) {
            $sanitizedEmail = isset($payload['email']) ? strtolower(trim((string) $payload['email'])) : '';
            $validationData['email'] = $sanitizedEmail;

            $uniqueRule = 'is_unique[users.email]';
            if ($userId > 0) {
                $uniqueRule = 'is_unique[users.email,id,' . $userId . ']';
            }

            $validationRules['email'] = 'required|valid_email|' . $uniqueRule;
        }

        $passwordForHash = null;
        if (array_key_exists('password', $payload)) {
            $passwordForHash = isset($payload['password']) ? (string) $payload['password'] : '';
            $validationData['password'] = $passwordForHash;
            $validationRules['password'] = 'required|min_length[8]';
        }

        if ($validationRules !== [] && ! $this->validateData($validationData, $validationRules)) {
            return errorResponse(
                'Data ibu tidak valid.',
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->validator->getErrors()
            );
        }

        [$motherUpdates, $motherErrors] = $this->prepareMotherUpdates($payload);
        if ($motherErrors !== []) {
            return errorResponse(
                'Data ibu tidak valid.',
                ResponseInterface::HTTP_BAD_REQUEST,
                $motherErrors
            );
        }

        $userUpdates = [];
        if ($sanitizedName !== null) {
            $userUpdates['name'] = $sanitizedName;
        }

        if ($sanitizedEmail !== null) {
            $userUpdates['email'] = $sanitizedEmail;
        }

        if ($passwordForHash !== null) {
            $userUpdates['password_hash'] = password_hash($passwordForHash, PASSWORD_DEFAULT);
        }

        if ($motherUpdates === [] && $userUpdates === []) {
            return errorResponse('Tidak ada data yang diperbarui.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $db = $this->mothers->db;
        $result = true;

        if ($db instanceof \CodeIgniter\Database\BaseConnection) {
            $db->transStart();
        }

        if ($motherUpdates !== []) {
            $result = $this->mothers->update($id, $motherUpdates);
        }

        if ($result && $userUpdates !== []) {
            $result = $this->users->update($userId, $userUpdates);
        }

        if ($db instanceof \CodeIgniter\Database\BaseConnection) {
            $db->transComplete();
            if (! $db->transStatus()) {
                $result = false;
            }
        }

        if (! $result) {
            return errorResponse('Tidak dapat memperbarui data ibu.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        $fresh = $this->mothers
            ->withUser()
            ->where('mothers.id', $id)
            ->get()->getRowArray();

        if (! is_array($fresh)) {
            $fresh = $this->mothers->find($id) ?: [];
        }

        $responsePayload = $this->formatter->present($fresh, true, true);

        return successResponse($responsePayload, 'Data ibu berhasil diperbarui.');
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    private function prepareMotherUpdates(array $payload): array
    {
        $data = [];
        $errors = [];

        if (array_key_exists('bb', $payload)) {
            $data['bb'] = $this->toNullableFloat($this->sanitizeScalar($payload['bb']));
        }

        if (array_key_exists('tb', $payload)) {
            $data['tb'] = $this->toNullableFloat($this->sanitizeScalar($payload['tb']));
        }

        if (array_key_exists('umur', $payload)) {
            $data['umur'] = $this->toNullableInt($this->sanitizeScalar($payload['umur']));
        }

        if (array_key_exists('usia_bayi_bln', $payload)) {
            $data['usia_bayi_bln'] = $this->toNullableInt($this->sanitizeScalar($payload['usia_bayi_bln']));
        }

        if (array_key_exists('laktasi_tipe', $payload)) {
            $laktasi = $this->sanitizeString($payload['laktasi_tipe']);
            if ($laktasi === null) {
                $data['laktasi_tipe'] = null;
            } elseif (in_array($laktasi, ['eksklusif', 'parsial'], true)) {
                $data['laktasi_tipe'] = $laktasi;
            } else {
                $errors['laktasi_tipe'] = 'Nilai laktasi_tipe tidak valid.';
            }
        }

        if (array_key_exists('aktivitas', $payload)) {
            $aktivitas = $this->sanitizeString($payload['aktivitas']);
            if ($aktivitas === null) {
                $data['aktivitas'] = null;
            } elseif (in_array($aktivitas, ['ringan', 'sedang', 'berat'], true)) {
                $data['aktivitas'] = $aktivitas;
            } else {
                $errors['aktivitas'] = 'Nilai aktivitas tidak valid.';
            }
        }

        if (array_key_exists('alergi', $payload) || array_key_exists('alergi_json', $payload)) {
            $alergi = $this->normalizeList($payload['alergi'] ?? $payload['alergi_json'] ?? null);
            $data['alergi_json'] = $this->encodeList($alergi);
        }

        if (array_key_exists('preferensi', $payload) || array_key_exists('preferensi_json', $payload)) {
            $preferensi = $this->normalizeList($payload['preferensi'] ?? $payload['preferensi_json'] ?? null);
            $data['preferensi_json'] = $this->encodeList($preferensi);
        }

        if (
            array_key_exists('riwayat', $payload)
            || array_key_exists('riwayat_json', $payload)
            || array_key_exists('riwayat_penyakit', $payload)
        ) {
            $riwayat = $this->normalizeList(
                $payload['riwayat']
                    ?? $payload['riwayat_json']
                    ?? $payload['riwayat_penyakit']
                    ?? null
            );
            $data['riwayat_json'] = $this->encodeList($riwayat);
        }

        return [$data, $errors];
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
     * @param string|null $value
     */
    private function toNullableFloat(?string $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * @param string|null $value
     */
    private function toNullableInt(?string $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
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
