<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\JWTService;
use App\Models\MotherModel;
use App\Models\UserModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\ResponseInterface;
use DateInterval;

class AuthController extends BaseController
{
    protected UserModel $users;
    protected MotherModel $mothers;

    public function __construct()
    {
        $this->users = new UserModel();
        $this->mothers = new MotherModel();
        helper('auth');
    }

    public function options()
    {
        return $this->response->setStatusCode(200);
    }

    public function login()
    {
        $data = get_request_data($this->request);

        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (! $this->validateData($data, $rules)) {
            return errorResponse(
                'Validation failed.',
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->validator->getErrors()
            );
        }

        $user = $this->users->where('email', $data['email'])->get()->getRowArray();

        if (! $user || ! password_verify($data['password'], $user['password_hash'])) {
            return errorResponse('Invalid email or password.', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        if (($user['role'] ?? null) === 'ibu') {
            $mother = $this->mothers->where('user_id', $user['id'])->get()->getRowArray();
            $user['mother_id'] = $mother['id'] ?? null;
        }

        try {
            $jwt = new JWTService();
            $token = $jwt->generateToken($user, new DateInterval('P1D'));
        } catch (\Throwable $exception) {
            log_message('error', 'Failed to generate token: ' . $exception->getMessage());

            return errorResponse('Unable to generate authentication token.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
        $userData = $this->formatUser($user);

        return successResponse(
            [
                'token' => $token,
                'user'  => $userData,
            ],
            'Login successful.'
        );
    }

    public function register()
    {
        $payload = get_request_data($this->request);

        $userPayload   = $payload['user'] ?? null;
        $motherPayload = $payload['ibu'] ?? null;

        if (! is_array($userPayload) || ! is_array($motherPayload)) {
            return errorResponse('Invalid registration payload.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $userInput   = $this->prepareUserInput($userPayload);
        $motherInput = $this->prepareMotherPayload($motherPayload);

        $userRules = [
            'name'     => 'required|string',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'role'     => 'required|in_list[ibu]',
        ];

        if (! $this->validateData($userInput, $userRules)) {
            return errorResponse(
                'Validation failed.',
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->validator->getErrors()
            );
        }

        $motherRules = [
            'bb'            => 'required|numeric',
            'tb'            => 'required|numeric',
            'umur'          => 'required|integer',
            'usia_bayi_bln' => 'required|integer',
            'laktasi_tipe'  => 'required|in_list[eksklusif,parsial]',
            'aktivitas'     => 'required|in_list[ringan,sedang,berat]',
        ];

        $motherValidationData = [
            'bb'            => $motherInput['bb'],
            'tb'            => $motherInput['tb'],
            'umur'          => $motherInput['umur'],
            'usia_bayi_bln' => $motherInput['usia_bayi_bln'],
            'laktasi_tipe'  => $motherInput['laktasi_tipe'],
            'aktivitas'     => $motherInput['aktivitas'],
        ];

        if (! $this->validateData($motherValidationData, $motherRules)) {
            return errorResponse(
                'Validation failed.',
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->validator->getErrors()
            );
        }

        $db = $this->users->db ?? null;
        if ($db instanceof BaseConnection) {
            $db->transBegin();
        }

        $userId = $this->users->insert([
            'name'          => $userInput['name'],
            'email'         => $userInput['email'],
            'password_hash' => password_hash($userInput['password'], PASSWORD_DEFAULT),
            'role'          => 'ibu',
        ]);

        if (! $userId) {
            if ($db instanceof BaseConnection) {
                $db->transRollback();
            }

            return errorResponse('Unable to create user.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        $motherRecord = $this->prepareMotherInsertData($motherInput);
        $motherRecord['user_id'] = $userId;

        $motherId = $this->mothers->insert($motherRecord);

        if (! $motherId) {
            if ($db instanceof BaseConnection) {
                $db->transRollback();
            } else {
                $this->users->delete($userId);
            }

            return errorResponse('Unable to create mother profile.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($db instanceof BaseConnection) {
            if ($db->transStatus() === false) {
                $db->transRollback();

                return errorResponse('Unable to create user.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
            }

            $db->transCommit();
        }

        $newUser = $this->users->find($userId);

        if (! $newUser) {
            return errorResponse('Unable to retrieve user information.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        $newUser['mother_id'] = $motherId;

        return successResponse(
            $this->formatUser($newUser),
            'User registered successfully.',
            ResponseInterface::HTTP_CREATED
        );
    }

    public function me()
    {
        $user = auth_user();

        if (! $user) {
            return errorResponse('Unauthorized.', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        return successResponse($user, 'Authenticated user retrieved.');
    }

    private function formatUser(array $user): array
    {
        unset($user['password_hash']);

        if (array_key_exists('mother_id', $user)) {
            $motherId = $user['mother_id'];
            unset($user['mother_id']);
            $user['motherId'] = $motherId !== null ? (int) $motherId : null;
        }

        return $user;
    }

    /**
     * @param array<string, mixed> $input
     *
     * @return array{name: string, email: string, password: string, role: string}
     */
    private function prepareUserInput(array $input): array
    {
        $name  = isset($input['name']) ? trim((string) $input['name']) : '';
        $email = isset($input['email']) ? strtolower(trim((string) $input['email'])) : '';
        $role  = isset($input['role']) ? strtolower(trim((string) $input['role'])) : 'ibu';

        return [
            'name'     => $name,
            'email'    => $email,
            'password' => isset($input['password']) ? (string) $input['password'] : '',
            'role'     => $role === '' ? 'ibu' : $role,
        ];
    }

    /**
     * @param array<string, mixed> $input
     *
     * @return array<string, mixed>
     */
    private function prepareMotherPayload(array $input): array
    {
        return [
            'bb'            => $this->sanitizeScalar($input['bb'] ?? null),
            'tb'            => $this->sanitizeScalar($input['tb'] ?? null),
            'umur'          => $this->sanitizeScalar($input['umur'] ?? null),
            'usia_bayi_bln' => $this->sanitizeScalar($input['usia_bayi_bln'] ?? null),
            'laktasi_tipe'  => $this->sanitizeString($input['laktasi_tipe'] ?? null),
            'aktivitas'     => $this->sanitizeString($input['aktivitas'] ?? null),
            'alergi'        => $this->normalizeList($input['alergi'] ?? $input['alergi_json'] ?? null),
            'preferensi'    => $this->normalizeList($input['preferensi'] ?? $input['preferensi_json'] ?? null),
            'riwayat'       => $this->normalizeList(
                $input['riwayat'] ?? $input['riwayat_penyakit'] ?? $input['riwayat_json'] ?? null
            ),
        ];
    }

    /**
     * @param array<string, mixed> $mother
     *
     * @return array<string, mixed>
     */
    private function prepareMotherInsertData(array $mother): array
    {
        return [
            'bb'             => $this->toNullableFloat($mother['bb']),
            'tb'             => $this->toNullableFloat($mother['tb']),
            'umur'           => $this->toNullableInt($mother['umur']),
            'usia_bayi_bln'  => $this->toNullableInt($mother['usia_bayi_bln']),
            'laktasi_tipe'   => $mother['laktasi_tipe'] ?? 'eksklusif',
            'aktivitas'      => $mother['aktivitas'] ?? 'ringan',
            'alergi_json'    => $this->encodeList($mother['alergi']),
            'preferensi_json'=> $this->encodeList($mother['preferensi']),
            'riwayat_json'   => $this->encodeList($mother['riwayat']),
        ];
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
