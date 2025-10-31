<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\NotificationModel;
use App\Models\RuleModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class RuleCommentController extends BaseController
{
    private RuleModel $rules;
    private NotificationModel $notifications;

    public function __construct()
    {
        $this->rules = new RuleModel();
        $this->notifications = new NotificationModel();
        helper(['auth', 'responseformatter']);
    }

    public function store(int $id): ResponseInterface
    {
        if ($response = $this->ensurePakar()) {
            return $response;
        }

        $currentExpert = auth_user();

        $rule = $this->rules->find($id);

        if (! $rule) {
            return errorResponse('Rule tidak ditemukan.', ResponseInterface::HTTP_NOT_FOUND);
        }

        $payload = get_request_data($this->request);
        $comment = $payload['komentar_pakar'] ?? null;

        $previousComment = null;
        if (isset($rule['komentar_pakar']) && is_string($rule['komentar_pakar'])) {
            $previousComment = trim($rule['komentar_pakar']);
        }

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

        if ($comment !== null && $comment !== $previousComment) {
            $this->notifyAdminAboutComment($updated + $rule, $comment, is_array($currentExpert) ? $currentExpert : null);
        }

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

    private function notifyAdminAboutComment(array $rule, string $comment, ?array $expert): void
    {
        if ($expert === null) {
            return;
        }

        $expertId = isset($expert['id']) ? (int) $expert['id'] : null;
        $ruleName = isset($rule['name']) ? trim((string) $rule['name']) : '';
        if ($ruleName === '') {
            $ruleName = 'Rule';
        }

        $excerpt = trim($comment);
        if ($excerpt !== '') {
            if (function_exists('mb_strlen') && function_exists('mb_substr')) {
                if (mb_strlen($excerpt) > 120) {
                    $excerpt = mb_substr($excerpt, 0, 117) . '...';
                }
            } elseif (strlen($excerpt) > 120) {
                $excerpt = substr($excerpt, 0, 117) . '...';
            }
        }

        $message = sprintf(
            'Komentar pakar baru pada rule "%s".',
            $ruleName
        );

        if ($excerpt !== '') {
            $message .= ' Catatan: "' . $excerpt . '"';
        }

        try {
            $this->notifications->insert([
                'mother_id'   => null,
                'expert_id'   => $expertId,
                'title'       => 'Komentar pakar baru',
                'message'     => $message,
                'type'        => 'rule_comment',
                'schedule_id' => null,
                'is_read'     => 0,
            ]);
        } catch (Throwable $exception) {
            log_message('error', 'Gagal membuat notifikasi komentar pakar: {message}', [
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
