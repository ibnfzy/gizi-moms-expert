<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\RuleModel;
use CodeIgniter\HTTP\ResponseInterface;

class RuleCommentController extends BaseController
{
    private RuleModel $rules;

    public function __construct()
    {
        $this->rules = new RuleModel();
        helper(['auth', 'responseformatter']);
    }

    public function store(int $id): ResponseInterface
    {
        if ($response = $this->ensurePakar()) {
            return $response;
        }

        $rule = $this->rules->find($id);

        if (! $rule) {
            return errorResponse('Rule tidak ditemukan.', ResponseInterface::HTTP_NOT_FOUND);
        }

        $payload = get_request_data($this->request);
        $comment = $payload['komentar_pakar'] ?? null;

        if ($comment !== null && ! is_string($comment)) {
            return errorResponse('Komentar pakar harus berupa teks.', ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (is_string($comment)) {
            $comment = trim($comment);
        }

        if ($comment === '') {
            $comment = null;
        }

        if (! $this->rules->update($id, ['komentar_pakar' => $comment])) {
            return errorResponse('Gagal menyimpan komentar pakar.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        $updated = $this->rules->find($id) ?? [];

        $data = [
            'id'             => $updated['id'] ?? $rule['id'] ?? $id,
            'name'           => $updated['name'] ?? $rule['name'] ?? null,
            'version'        => $updated['version'] ?? $rule['version'] ?? null,
            'komentar_pakar' => $updated['komentar_pakar'] ?? null,
        ];

        return successResponse($data, 'Komentar pakar berhasil disimpan.');
    }

    private function ensurePakar(): ?ResponseInterface
    {
        $user = auth_user();

        if ($user === null) {
            return errorResponse('Unauthorized.', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $role = strtolower((string) ($user['role'] ?? ''));

        if ($role !== 'pakar') {
            return errorResponse('You do not have permission to access this resource.', ResponseInterface::HTTP_FORBIDDEN);
        }

        return null;
    }
}
