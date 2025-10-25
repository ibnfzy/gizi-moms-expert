<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\MotherModel;
use App\Models\NotificationModel;
use App\Models\ScheduleModel;
use CodeIgniter\HTTP\ResponseInterface;

class NotificationController extends BaseController
{
    private NotificationModel $notifications;
    private MotherModel $mothers;
    private ScheduleModel $schedules;

    public function __construct()
    {
        helper('auth');

        $this->notifications = new NotificationModel();
        $this->mothers       = new MotherModel();
        $this->schedules     = new ScheduleModel();
    }

    public function index()
    {
        $user = auth_user();

        if ($user === null) {
            return errorResponse('Unauthorized.', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $role = strtolower((string) ($user['role'] ?? ''));

        if (! in_array($role, ['ibu', 'pakar', 'admin'], true)) {
            return errorResponse('You do not have permission to view notifications.', ResponseInterface::HTTP_FORBIDDEN);
        }

        $motherIdFilter = $this->request->getGet('mother_id');
        $expertIdFilter = $this->request->getGet('expert_id');
        $unreadFilter   = $this->request->getGet('unread');

        $builder = $this->notifications->builder();
        $builder->select('notifications.*');

        if ($role === 'ibu') {
            $mother = $this->getMotherByUserId((int) $user['id']);
            if (! is_array($mother)) {
                return errorResponse('Mother profile not found.', ResponseInterface::HTTP_NOT_FOUND);
            }

            $builder->where('notifications.mother_id', (int) $mother['id']);
        } elseif ($role === 'pakar') {
            $builder->where('notifications.expert_id', (int) $user['id']);
        }

        if ($role === 'admin') {
            if ($motherIdFilter !== null && $motherIdFilter !== '') {
                $builder->where('notifications.mother_id', (int) $motherIdFilter);
            }

            if ($expertIdFilter !== null && $expertIdFilter !== '') {
                $builder->where('notifications.expert_id', (int) $expertIdFilter);
            }
        } else {
            if ($motherIdFilter !== null && $motherIdFilter !== '') {
                $builder->where('notifications.mother_id', (int) $motherIdFilter);
            }

            if ($expertIdFilter !== null && $expertIdFilter !== '') {
                $builder->where('notifications.expert_id', (int) $expertIdFilter);
            }
        }

        if ($unreadFilter !== null && $unreadFilter !== '') {
            $builder->where('notifications.is_read', $this->normalizeBoolean($unreadFilter) ? 0 : 1);
        }

        $builder->orderBy('notifications.created_at', 'DESC');
        $builder->orderBy('notifications.id', 'DESC');

        $records = $builder->get()->getResultArray();

        return successResponse($this->formatNotifications($records), 'Daftar notifikasi berhasil dimuat.');
    }

    public function create()
    {
        $user = auth_user();

        if ($user === null) {
            return errorResponse('Unauthorized.', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $role = strtolower((string) ($user['role'] ?? ''));
        if (! in_array($role, ['pakar', 'admin'], true)) {
            return errorResponse('You do not have permission to create notifications.', ResponseInterface::HTTP_FORBIDDEN);
        }

        $payload = get_request_data($this->request);

        $motherId   = isset($payload['mother_id']) ? (int) $payload['mother_id'] : null;
        $expertId   = isset($payload['expert_id']) ? (int) $payload['expert_id'] : null;
        $title      = isset($payload['title']) ? trim((string) $payload['title']) : '';
        $message    = isset($payload['message']) ? trim((string) $payload['message']) : '';
        $typeValue  = isset($payload['type']) ? trim((string) $payload['type']) : null;
        $type       = $typeValue === '' ? null : $typeValue;
        $scheduleId = isset($payload['schedule_id']) ? (int) $payload['schedule_id'] : null;
        $isRead     = $payload['is_read'] ?? false;

        if ($title === '' || $message === '') {
            return errorResponse('title and message are required.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        if (($motherId === null || $motherId <= 0) && ($expertId === null || $expertId <= 0)) {
            return errorResponse('mother_id or expert_id is required.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        if ($motherId !== null && $motherId > 0) {
            $mother = $this->mothers->find($motherId);
            if (! is_array($mother)) {
                return errorResponse('Mother not found.', ResponseInterface::HTTP_NOT_FOUND);
            }
        } else {
            $motherId = null;
        }

        if ($expertId !== null && $expertId <= 0) {
            $expertId = null;
        }

        if ($role === 'pakar') {
            $currentExpertId = (int) $user['id'];
            if ($expertId === null) {
                $expertId = $currentExpertId;
            } elseif ($expertId !== $currentExpertId) {
                return errorResponse('You may only create expert notifications for yourself.', ResponseInterface::HTTP_FORBIDDEN);
            }
        }

        if ($scheduleId !== null && $scheduleId > 0) {
            $schedule = $this->schedules->find($scheduleId);
            if (! is_array($schedule)) {
                return errorResponse('Schedule not found.', ResponseInterface::HTTP_NOT_FOUND);
            }
        } else {
            $scheduleId = null;
        }

        $notificationId = $this->notifications->insert([
            'mother_id'   => $motherId,
            'expert_id'   => $expertId,
            'title'       => $title,
            'message'     => $message,
            'type'        => $type,
            'schedule_id' => $scheduleId,
            'is_read'     => $this->normalizeBoolean($isRead) ? 1 : 0,
        ], true);

        if (! is_int($notificationId) || $notificationId <= 0) {
            return errorResponse('Failed to create notification.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        $record = $this->notifications->find($notificationId);

        return successResponse(
            $this->formatNotification($record ?: []),
            'Notifikasi berhasil dibuat.',
            ResponseInterface::HTTP_CREATED
        );
    }

    /**
     * @param array<int, array<string, mixed>> $records
     */
    private function formatNotifications(array $records): array
    {
        return array_map(fn(array $record): array => $this->formatNotification($record), $records);
    }

    /**
     * @param array<string, mixed> $record
     */
    private function formatNotification(array $record): array
    {
        if ($record === []) {
            return $record;
        }

        $record['id']         = isset($record['id']) ? (int) $record['id'] : null;
        $record['mother_id']  = isset($record['mother_id']) ? (int) $record['mother_id'] : null;
        $record['expert_id']  = isset($record['expert_id']) ? (int) $record['expert_id'] : null;
        $record['schedule_id'] = isset($record['schedule_id']) ? (int) $record['schedule_id'] : null;
        $record['is_read']    = isset($record['is_read']) ? (bool) $record['is_read'] : false;

        return $record;
    }

    private function getMotherByUserId(int $userId): ?array
    {
        return $this->mothers
            ->where('user_id', $userId)
            ->get()->getRowArray();
    }

    private function normalizeBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            $value = strtolower($value);
            return in_array($value, ['1', 'true', 'yes'], true);
        }

        return false;
    }
}
