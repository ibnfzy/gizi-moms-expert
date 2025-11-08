<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\MotherModel;
use App\Models\NotificationModel;
use App\Models\ScheduleModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;
use Throwable;

class ScheduleController extends BaseController
{
    private ScheduleModel $schedules;
    private MotherModel $mothers;
    private NotificationModel $notifications;

    public function __construct()
    {
        helper('auth');

        $this->schedules     = new ScheduleModel();
        $this->mothers       = new MotherModel();
        $this->notifications = new NotificationModel();
    }

    public function index()
    {
        $user = auth_user();

        if ($user === null) {
            return errorResponse('Unauthorized.', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $role = strtolower((string) ($user['role'] ?? ''));

        $builder = $this->schedules->builder();
        $builder->select('*');

        $queryExpertId = $this->request->getGet('expert_id');
        $queryMotherId = $this->request->getGet('mother_id');

        if ($role === 'pakar') {
            $builder->where('expert_id', (int) $user['id']);
        } elseif ($role === 'ibu') {
            $mother = $this->getMotherByUserId((int) $user['id']);

            if (! is_array($mother)) {
                return errorResponse('Mother profile not found.', ResponseInterface::HTTP_NOT_FOUND);
            }

            $builder->where('mother_id', (int) $mother['id']);
        }

        if ($queryExpertId !== null && $queryExpertId !== '') {
            $builder->where('expert_id', (int) $queryExpertId);
        }

        if ($queryMotherId !== null && $queryMotherId !== '') {
            $builder->where('mother_id', (int) $queryMotherId);
        }

        $status = $this->request->getGet('status');
        if (is_string($status) && $status !== '') {
            $builder->where('status', $status);
        }

        $builder->orderBy('scheduled_at', 'ASC');
        $builder->orderBy('id', 'ASC');

        $records = $builder->get()->getResultArray();

        return successResponse($this->formatSchedules($records), 'Daftar jadwal berhasil dimuat.');
    }

    public function create()
    {
        $user = auth_user();

        if ($user === null) {
            return errorResponse('Unauthorized.', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $role = strtolower((string) ($user['role'] ?? ''));
        if ($role !== 'pakar') {
            return errorResponse('Only experts can create schedules.', ResponseInterface::HTTP_FORBIDDEN);
        }

        $payload = get_request_data($this->request);

        $motherId     = isset($payload['mother_id']) ? (int) $payload['mother_id'] : null;
        $expertId     = isset($payload['expert_id']) ? (int) $payload['expert_id'] : (int) $user['id'];
        $scheduledRaw = $payload['scheduled_at'] ?? null;
        $status       = $payload['status'] ?? 'pending';
        $location     = $payload['location'] ?? null;
        $notes        = $payload['notes'] ?? null;

        if ($motherId === null || $motherId <= 0) {
            return errorResponse('mother_id is required.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $mother = $this->mothers->find($motherId);
        if (! is_array($mother)) {
            return errorResponse('Mother data not found.', ResponseInterface::HTTP_NOT_FOUND);
        }

        if ($expertId <= 0 || $expertId !== (int) $user['id']) {
            return errorResponse('You may only create schedules for yourself.', ResponseInterface::HTTP_FORBIDDEN);
        }

        $scheduledAt = $this->parseDateTime($scheduledRaw);
        if ($scheduledAt === null) {
            return errorResponse('scheduled_at is invalid or missing.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        if (! in_array($status, ['pending', 'confirmed', 'completed', 'cancelled'], true)) {
            return errorResponse('status value is not valid for schedules.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $scheduleId = $this->schedules->insert([
            'mother_id'    => $motherId,
            'expert_id'    => $expertId,
            'scheduled_at' => $scheduledAt,
            'status'       => $status,
            'location'     => $location,
            'notes'        => $notes,
        ], true);

        if (! is_int($scheduleId) || $scheduleId <= 0) {
            return errorResponse('Failed to create schedule.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        $schedule = $this->schedules->find($scheduleId);

        return successResponse(
            $this->formatSchedule($schedule ?: []),
            'Jadwal berhasil dibuat.',
            ResponseInterface::HTTP_CREATED
        );
    }

    public function update(int $id)
    {
        $user = auth_user();

        if ($user === null) {
            return errorResponse('Unauthorized.', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $role = strtolower((string) ($user['role'] ?? ''));
        if ($role !== 'pakar') {
            return errorResponse('Only experts can update schedules.', ResponseInterface::HTTP_FORBIDDEN);
        }

        $schedule = $this->schedules->find($id);
        if (! is_array($schedule)) {
            return errorResponse('Schedule not found.', ResponseInterface::HTTP_NOT_FOUND);
        }

        if ((int) $schedule['expert_id'] !== (int) $user['id']) {
            return errorResponse('You are not allowed to modify this schedule.', ResponseInterface::HTTP_FORBIDDEN);
        }

        $payload = get_request_data($this->request);

        $allowed = [
            'scheduled_at',
            'status',
            'location',
            'notes',
            'reminder_sent',
        ];

        $fields = array_intersect_key($payload, array_flip($allowed));

        if ($fields === []) {
            return errorResponse('No valid fields provided for update.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        if (array_key_exists('scheduled_at', $fields)) {
            $parsed = $this->parseDateTime($fields['scheduled_at']);
            if ($parsed === null) {
                return errorResponse('scheduled_at is invalid.', ResponseInterface::HTTP_BAD_REQUEST);
            }
            $fields['scheduled_at'] = $parsed;
        }

        if (
            array_key_exists('status', $fields)
            && ! in_array($fields['status'], ['pending', 'confirmed', 'completed', 'cancelled'], true)
        ) {
            return errorResponse('Invalid status value.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        if (array_key_exists('reminder_sent', $fields)) {
            $fields['reminder_sent'] = $this->normalizeBoolean($fields['reminder_sent']) ? 1 : 0;
        }

        $updated = $this->schedules->update($id, $fields);

        if (! $updated) {
            return errorResponse('Failed to update schedule.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        $fresh = $this->schedules->find($id);

        return successResponse($this->formatSchedule($fresh ?: []), 'Jadwal berhasil diperbarui.');
    }

    public function updateAttendance(int $id)
    {
        $user = auth_user();

        if ($user === null) {
            return errorResponse('Unauthorized.', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $role = strtolower((string) ($user['role'] ?? ''));
        if (! in_array($role, ['ibu', 'pakar'], true)) {
            return errorResponse('Only mothers and experts can update attendance.', ResponseInterface::HTTP_FORBIDDEN);
        }

        $schedule = $this->schedules->find($id);
        if (! is_array($schedule)) {
            return errorResponse('Schedule not found.', ResponseInterface::HTTP_NOT_FOUND);
        }

        if ($role === 'ibu') {
            $mother = $this->mothers->find($schedule['mother_id']);
            if (! is_array($mother) || (int) $mother['user_id'] !== (int) $user['id']) {
                return errorResponse('You are not allowed to update this attendance.', ResponseInterface::HTTP_FORBIDDEN);
            }
        } elseif ((int) $schedule['expert_id'] !== (int) $user['id']) {
            return errorResponse('You are not allowed to update this attendance.', ResponseInterface::HTTP_FORBIDDEN);
        }

        $payload = get_request_data($this->request);
        $attendance = isset($payload['attendance']) ? (string) $payload['attendance'] : '';

        if (! in_array($attendance, ['pending', 'confirmed', 'declined'], true)) {
            return errorResponse('Invalid attendance value.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $fields = ['attendance' => $attendance];

        $currentStatus = (string) ($schedule['status'] ?? 'pending');

        if ($currentStatus !== 'completed') {
            if ($attendance === 'confirmed') {
                $fields['status'] = 'confirmed';
            } elseif ($attendance === 'declined') {
                $fields['status'] = 'cancelled';
            } elseif ($attendance === 'pending' && $currentStatus !== 'pending') {
                $fields['status'] = 'pending';
            }
        }

        $updated = $this->schedules->update($id, $fields);

        if (! $updated) {
            return errorResponse('Failed to update attendance.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($attendance === 'declined') {
            $this->notifyExpert(
                (int) $schedule['expert_id'],
                'Jadwal ditolak',
                'Jadwal konsultasi telah ditolak oleh ibu.',
                'schedule_attendance_declined',
                (int) $schedule['id']
            );
        }

        $fresh = $this->schedules->find($id);

        return successResponse($this->formatSchedule($fresh ?: []), 'Kehadiran berhasil diperbarui.');
    }

    public function updateEvaluation(int $id)
    {
        $user = auth_user();

        if ($user === null) {
            return errorResponse('Unauthorized.', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $role = strtolower((string) ($user['role'] ?? ''));
        if ($role !== 'pakar') {
            return errorResponse('Only experts can submit evaluations.', ResponseInterface::HTTP_FORBIDDEN);
        }

        $schedule = $this->schedules->find($id);
        if (! is_array($schedule)) {
            return errorResponse('Schedule not found.', ResponseInterface::HTTP_NOT_FOUND);
        }

        if ((int) $schedule['expert_id'] !== (int) $user['id']) {
            return errorResponse('You are not allowed to evaluate this schedule.', ResponseInterface::HTTP_FORBIDDEN);
        }

        $payload = get_request_data($this->request);
        $evaluationPayload = $payload['evaluation'] ?? null;
        $motherPayload     = $payload['mother'] ?? null;

        $evaluationJson = null;
        $jsonError = JSON_ERROR_NONE;
        if (is_array($evaluationPayload)) {
            $evaluationJson = json_encode($evaluationPayload, JSON_UNESCAPED_UNICODE);
            $jsonError = json_last_error();
        } elseif (is_string($evaluationPayload)) {
            $evaluationJson = $evaluationPayload;
        }

        if ($evaluationJson === null || $evaluationJson === '') {
            return errorResponse('evaluation is required.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        if ($jsonError !== JSON_ERROR_NONE) {
            return errorResponse('evaluation data is not valid JSON.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $updated = $this->schedules->update($id, [
            'evaluation_json' => $evaluationJson,
            'status'          => 'completed',
        ]);

        if (! $updated) {
            return errorResponse('Failed to update evaluation.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        if (is_array($motherPayload) && isset($schedule['mother_id']) && (int) $schedule['mother_id'] > 0) {
            $motherUpdate = $this->prepareMotherUpdateData($motherPayload);

            if ($motherUpdate !== []) {
                try {
                    $motherUpdated = $this->mothers->update((int) $schedule['mother_id'], $motherUpdate);
                } catch (Throwable $exception) {
                    log_message('error', 'Failed to update mother profile during evaluation: ' . $exception->getMessage());
                    $motherUpdated = false;
                }

                if ($motherUpdated === false) {
                    return errorResponse('Failed to update mother profile.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        }

        $this->notifyMother(
            (int) $schedule['mother_id'],
            'Evaluasi Konsultasi',
            'Pakar telah mengunggah evaluasi untuk jadwal konsultasi Anda.',
            'schedule_evaluation_completed',
            (int) $schedule['id']
        );

        $fresh = $this->schedules->find($id);

        return successResponse($this->formatSchedule($fresh ?: []), 'Evaluasi jadwal berhasil diperbarui.');
    }

    public function reminderDue()
    {
        $user = auth_user();

        if ($user === null) {
            return errorResponse('Unauthorized.', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $role = strtolower((string) ($user['role'] ?? ''));
        if (! in_array($role, ['pakar', 'admin'], true)) {
            return errorResponse('Only experts or admin can access reminders.', ResponseInterface::HTTP_FORBIDDEN);
        }

        $beforeParam = $this->request->getGet('before');
        $windowParam = $this->request->getGet('within_minutes');

        $now = Time::now();
        $before = null;

        if (is_string($beforeParam) && $beforeParam !== '') {
            $before = $this->parseTimeOrNull($beforeParam);
        }

        if ($before === null) {
            $minutes = is_numeric($windowParam) ? max(1, (int) $windowParam) : 1440;
            $before  = $now->addMinutes($minutes);
        }

        $builder = $this->schedules->builder();
        $builder->select('*');
        $builder->where('reminder_sent', 0);
        $builder->where('scheduled_at >=', $now->toDateTimeString());
        $builder->where('scheduled_at <=', $before->toDateTimeString());

        if ($role === 'pakar') {
            $builder->where('expert_id', (int) $user['id']);
        }

        $builder->orderBy('scheduled_at', 'ASC');
        $builder->orderBy('id', 'ASC');

        $records = $builder->get()->getResultArray();

        return successResponse($this->formatSchedules($records), 'Daftar jadwal yang perlu diingatkan.');
    }

    /**
     * @param array<int, array<string, mixed>> $records
     */
    private function formatSchedules(array $records): array
    {
        return array_map(fn(array $record): array => $this->formatSchedule($record), $records);
    }

    /**
     * @param array<string, mixed> $record
     */
    private function formatSchedule(array $record): array
    {
        if ($record === []) {
            return $record;
        }

        $record['id']            = isset($record['id']) ? (int) $record['id'] : null;
        $record['mother_id']     = isset($record['mother_id']) ? (int) $record['mother_id'] : null;
        $record['expert_id']     = isset($record['expert_id']) ? (int) $record['expert_id'] : null;
        $record['reminder_sent'] = isset($record['reminder_sent']) ? (bool) $record['reminder_sent'] : false;

        return $record;
    }

    /**
     * @param array<string, mixed> $input
     *
     * @return array<string, mixed>
     */
    private function prepareMotherUpdateData(array $input): array
    {
        $bbRaw       = $this->sanitizeScalar($input['bb'] ?? null);
        $tbRaw       = $this->sanitizeScalar($input['tb'] ?? null);
        $umurRaw     = $this->sanitizeScalar($input['umur'] ?? null);
        $usiaBayiRaw = $this->sanitizeScalar($input['usia_bayi_bln'] ?? null);

        $data = [
            'bb'            => $this->toNullableFloat($bbRaw),
            'tb'            => $this->toNullableFloat($tbRaw),
            'umur'          => $this->toNullableInt($umurRaw),
            'usia_bayi_bln' => $this->toNullableInt($usiaBayiRaw),
        ];

        $laktasi = $this->sanitizeString($input['laktasi_tipe'] ?? null);
        if ($laktasi !== null && in_array($laktasi, ['eksklusif', 'parsial'], true)) {
            $data['laktasi_tipe'] = $laktasi;
        }

        $aktivitas = $this->sanitizeString($input['aktivitas'] ?? null);
        if ($aktivitas !== null && in_array($aktivitas, ['ringan', 'sedang', 'berat'], true)) {
            $data['aktivitas'] = $aktivitas;
        }

        $alergi = $this->normalizeList($input['alergi'] ?? $input['alergi_json'] ?? null);
        $data['alergi_json'] = $this->encodeList($alergi);

        return $data;
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

    private function parseDateTime($value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            $time = Time::parse($value);
        } catch (Throwable $exception) {
            log_message('error', 'Failed to parse schedule datetime: ' . $exception->getMessage());
            return null;
        }

        return $time->toDateTimeString();
    }

    private function parseTimeOrNull(string $value): ?Time
    {
        try {
            return Time::parse($value);
        } catch (Throwable $exception) {
            log_message('error', 'Failed to parse reminder datetime: ' . $exception->getMessage());
            return null;
        }
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

    private function getMotherByUserId(int $userId): ?array
    {
        return $this->mothers
            ->where('user_id', $userId)
            ->get()->getRowArray();
    }

    private function notifyMother(int $motherId, string $title, string $message, string $type, ?int $scheduleId = null): void
    {
        if ($motherId <= 0) {
            return;
        }

        try {
            $this->notifications->insert([
                'mother_id'   => $motherId,
                'title'       => $title,
                'message'     => $message,
                'type'        => $type,
                'schedule_id' => $scheduleId,
                'is_read'     => 0,
            ]);
        } catch (Throwable $exception) {
            log_message('error', 'Failed to create mother notification: ' . $exception->getMessage());
        }
    }

    private function notifyExpert(int $expertId, string $title, string $message, string $type, ?int $scheduleId = null): void
    {
        if ($expertId <= 0) {
            return;
        }

        try {
            $this->notifications->insert([
                'expert_id'   => $expertId,
                'title'       => $title,
                'message'     => $message,
                'type'        => $type,
                'schedule_id' => $scheduleId,
                'is_read'     => 0,
            ]);
        } catch (Throwable $exception) {
            log_message('error', 'Failed to create expert notification: ' . $exception->getMessage());
        }
    }
}
