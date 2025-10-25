<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;

class AdminUserController extends BaseController
{
    private UserModel $users;

    public function __construct()
    {
        helper('auth');

        $this->users = new UserModel();
    }

    public function index()
    {
        $records = $this->users
            ->whereIn('role', $this->managedRoles())
            ->orderBy('name', 'ASC')
            ->get()->getResultArray();

        $payload = array_map(fn(array $user): array => $this->present($user), $records);

        return successResponse($payload, 'Daftar pengguna berhasil dimuat.');
    }

    public function show(int $id)
    {
        $user = $this->findManagedUser($id);

        if ($user === null) {
            return $this->notFound();
        }

        return successResponse($this->present($user), 'Data pengguna berhasil dimuat.');
    }

    public function create()
    {
        $payload = get_request_data($this->request);

        $payload['name'] = isset($payload['name']) ? trim((string) $payload['name']) : null;
        $payload['email'] = isset($payload['email']) ? trim((string) $payload['email']) : null;
        $payload['role'] = isset($payload['role']) ? strtolower(trim((string) $payload['role'])) : null;
        $payload['password'] = isset($payload['password']) ? (string) $payload['password'] : null;

        $rules = [
            'name'     => 'required|string|min_length[3]|max_length[100]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'role'     => 'required|in_list[' . implode(',', $this->managedRoles()) . ']',
            'password' => 'required|min_length[8]',
        ];

        if (! $this->validateData($payload, $rules)) {
            return $this->validationFailedResponse(
                $this->validator->getErrors(),
                'Data pengguna tidak valid.'
            );
        }

        $userId = $this->users->insert([
            'name'          => $payload['name'],
            'email'         => $payload['email'],
            'role'          => $payload['role'],
            'password_hash' => password_hash($payload['password'], PASSWORD_DEFAULT),
        ]);

        if (! $userId) {
            return errorResponse('Tidak dapat membuat pengguna baru.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        $user = $this->users->find((int) $userId);

        if (! is_array($user)) {
            return errorResponse(
                'Pengguna berhasil dibuat namun tidak dapat dimuat.',
                ResponseInterface::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return successResponse(
            $this->present($user),
            'Pengguna berhasil dibuat.',
            ResponseInterface::HTTP_CREATED
        );
    }

    public function update(int $id)
    {
        $user = $this->findManagedUser($id);

        if ($user === null) {
            return $this->notFound();
        }

        $payload = get_request_data($this->request);

        $payload['name'] = isset($payload['name']) ? trim((string) $payload['name']) : null;
        $payload['email'] = isset($payload['email']) ? trim((string) $payload['email']) : null;
        $payload['role'] = isset($payload['role']) ? strtolower(trim((string) $payload['role'])) : null;

        $uniqueEmailRule = 'is_unique[users.email,id,' . $user['id'] . ']';

        $rules = [
            'name'  => 'required|string|min_length[3]|max_length[100]',
            'email' => 'required|valid_email|' . $uniqueEmailRule,
            'role'  => 'required|in_list[' . implode(',', $this->managedRoles()) . ']',
        ];

        if (! $this->validateData($payload, $rules)) {
            return $this->validationFailedResponse(
                $this->validator->getErrors(),
                'Data pengguna tidak valid.'
            );
        }

        $updated = $this->users->update($user['id'], [
            'name'  => $payload['name'],
            'email' => $payload['email'],
            'role'  => $payload['role'],
        ]);

        if (! $updated) {
            return errorResponse('Tidak dapat memperbarui data pengguna.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        $fresh = $this->users->find($user['id']);

        return successResponse(
            $this->present($fresh ?? $user),
            'Pengguna berhasil diperbarui.'
        );
    }

    public function updatePassword(int $id)
    {
        $user = $this->findManagedUser($id);

        if ($user === null) {
            return $this->notFound();
        }

        $payload = get_request_data($this->request);
        $payload['password'] = isset($payload['password']) ? (string) $payload['password'] : null;

        $rules = [
            'password' => 'required|min_length[8]',
        ];

        if (! $this->validateData($payload, $rules)) {
            return $this->validationFailedResponse(
                $this->validator->getErrors(),
                'Password tidak valid.'
            );
        }

        $updated = $this->users->update($user['id'], [
            'password_hash' => password_hash($payload['password'], PASSWORD_DEFAULT),
        ]);

        if (! $updated) {
            return errorResponse(
                'Tidak dapat memperbarui password pengguna.',
                ResponseInterface::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return successResponse(null, 'Password pengguna berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $user = $this->findManagedUser($id);

        if ($user === null) {
            return $this->notFound();
        }

        $currentUser = auth_user();
        if (is_array($currentUser) && (int) ($currentUser['id'] ?? 0) === (int) $user['id']) {
            return errorResponse(
                'Anda tidak dapat menghapus akun yang sedang digunakan.',
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        if (! $this->users->delete($user['id'])) {
            return errorResponse('Tidak dapat menghapus pengguna.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        return successResponse(null, 'Pengguna berhasil dihapus.');
    }

    private function findManagedUser(int $id): ?array
    {
        $user = $this->users->find($id);

        if (! is_array($user)) {
            return null;
        }

        if (! in_array(strtolower((string) ($user['role'] ?? '')), $this->managedRoles(), true)) {
            return null;
        }

        return $user;
    }

    private function present(array $user): array
    {
        unset($user['password_hash']);

        $createdAtHuman = null;
        if (! empty($user['created_at'])) {
            try {
                $time = Time::parse($user['created_at']);
                $createdAtHuman = $time->humanize();
            } catch (\Throwable $exception) {
                $createdAtHuman = $user['created_at'];
            }
        }

        $role = strtolower((string) ($user['role'] ?? ''));

        return [
            'id'               => (int) $user['id'],
            'name'             => $user['name'] ?? '',
            'email'            => $user['email'] ?? '',
            'role'             => $role,
            'role_label'       => $this->roleLabel($role),
            'role_badge'       => $this->roleBadge($role),
            'created_at'       => $user['created_at'] ?? null,
            'created_at_human' => $createdAtHuman,
            'updated_at'       => $user['updated_at'] ?? null,
        ];
    }

    private function managedRoles(): array
    {
        return ['admin', 'pakar'];
    }

    private function roleLabel(string $role): string
    {
        return match ($role) {
            'admin' => 'Admin',
            'pakar' => 'Pakar',
            default => ucfirst($role),
        };
    }

    private function validationFailedResponse(array $errors, string $fallback): ResponseInterface
    {
        $message = trim(implode(' ', array_filter($errors)));
        if ($message === '') {
            $message = $fallback;
        }

        return errorResponse(
            $message,
            ResponseInterface::HTTP_BAD_REQUEST,
            ['errors' => $errors]
        );
    }

    private function roleBadge(string $role): string
    {
        return match ($role) {
            'admin' => 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-200',
            'pakar' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-200',
            default => 'bg-gray-100 text-gray-700 dark:bg-slate-800/70 dark:text-slate-200',
        };
    }

    private function notFound(): ResponseInterface
    {
        return errorResponse('Pengguna tidak ditemukan.', ResponseInterface::HTTP_NOT_FOUND);
    }
}
