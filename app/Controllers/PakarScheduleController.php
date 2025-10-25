<?php

namespace App\Controllers;

use App\Models\ScheduleModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;

class PakarScheduleController extends BaseController
{
    private const STATUS_OPTIONS = [
        ''          => 'Semua Status',
        'pending'   => 'Menunggu Konfirmasi',
        'confirmed' => 'Terkonfirmasi',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    private ScheduleModel $schedules;

    public function __construct()
    {
        $this->schedules = new ScheduleModel();
    }

    public function index(): string
    {
        $status = $this->normalizeStatus((string) ($this->request->getGet('status') ?? ''));

        $schedules = $this->loadSchedules($status);

        $createStatusOptions = array_filter(
            self::STATUS_OPTIONS,
            static fn (string $label, string $value): bool => $value !== '',
            ARRAY_FILTER_USE_BOTH
        );

        return view('pakar/schedules/index', [
            'schedules'            => $schedules,
            'statusOptions'        => self::STATUS_OPTIONS,
            'createStatusOptions'  => $createStatusOptions,
            'filterStatus'         => $status ?? '',
            'tableUrl'             => site_url('pakar/schedules/table'),
            'rowUrlTemplate'       => site_url('pakar/schedules/rows/__id__'),
            'motherListEndpoint'   => site_url('api/mothers'),
            'scheduleCreateEndpoint' => site_url('api/schedules'),
        ]);
    }

    public function table(): string
    {
        $status = $this->normalizeStatus((string) ($this->request->getGet('status') ?? ''));

        return view('pakar/partials/schedule_table', [
            'schedules'    => $this->loadSchedules($status),
            'filterStatus' => $status ?? '',
        ]);
    }

    public function row(int $id)
    {
        $schedule = $this->findSchedule($id);

        if ($schedule === null) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setBody('<tr><td colspan="5" class="px-4 py-3 text-sm text-red-600">Jadwal tidak ditemukan.</td></tr>');
        }

        return view('pakar/partials/schedule_row', [
            'schedule' => $schedule,
        ]);
    }

    private function loadSchedules(?string $status = null): array
    {
        $session  = session();
        $expertId = (int) ($session->get('user_id') ?? 0);

        if ($expertId <= 0) {
            return [];
        }

        $builder = $this->schedules->builder();
        $builder
            ->select([
                'schedules.*',
                'mothers.user_id as mother_user_id',
                'users.name as mother_name',
                'users.email as mother_email',
            ])
            ->join('mothers', 'mothers.id = schedules.mother_id')
            ->join('users', 'users.id = mothers.user_id')
            ->where('schedules.expert_id', $expertId)
            ->orderBy('schedules.scheduled_at', 'ASC')
            ->orderBy('schedules.id', 'ASC');

        if ($status !== null && $status !== '') {
            $builder->where('schedules.status', $status);
        }

        $records = $builder->get()->getResultArray();

        return array_map(fn (array $record): array => $this->presentSchedule($record), $records);
    }

    private function findSchedule(int $id): ?array
    {
        $session  = session();
        $expertId = (int) ($session->get('user_id') ?? 0);

        if ($expertId <= 0) {
            return null;
        }

        $builder = $this->schedules->builder();
        $builder
            ->select([
                'schedules.*',
                'mothers.user_id as mother_user_id',
                'users.name as mother_name',
                'users.email as mother_email',
            ])
            ->join('mothers', 'mothers.id = schedules.mother_id')
            ->join('users', 'users.id = mothers.user_id')
            ->where('schedules.expert_id', $expertId)
            ->where('schedules.id', $id)
            ->orderBy('schedules.id', 'ASC');

        $record = $builder->get()->getRowArray();

        if (! is_array($record)) {
            return null;
        }

        return $this->presentSchedule($record);
    }

    private function presentSchedule(array $record): array
    {
        $scheduledAt = $record['scheduled_at'] ?? null;
        $time        = $this->parseDateTime($scheduledAt);

        $evaluation = $this->decodeEvaluation($record['evaluation_json'] ?? null);

        return [
            'id'         => isset($record['id']) ? (int) $record['id'] : null,
            'mother'     => [
                'id'     => isset($record['mother_id']) ? (int) $record['mother_id'] : null,
                'userId' => isset($record['mother_user_id']) ? (int) $record['mother_user_id'] : null,
                'name'   => $record['mother_name'] ?? 'Tanpa Nama',
                'email'  => $record['mother_email'] ?? null,
            ],
            'status'     => $record['status'] ?? 'pending',
            'attendance' => $record['attendance'] ?? 'pending',
            'scheduled_at' => [
                'raw'      => $scheduledAt,
                'display'  => $time?->toLocalizedString('EEEE, d MMMM yyyy HH.mm'),
                'date'     => $time?->toDateString(),
                'time'     => $time?->toTimeString(),
                'humanize' => $time?->humanize(),
            ],
            'location'   => $record['location'] ?? null,
            'notes'      => $record['notes'] ?? null,
            'evaluation' => $evaluation,
        ];
    }

    private function parseDateTime($value): ?Time
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Time::parse($value);
        } catch (\Throwable $exception) {
            log_message('warning', 'Gagal mengurai tanggal jadwal: ' . $exception->getMessage());
        }

        return null;
    }

    private function decodeEvaluation($value): ?array
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $decoded = json_decode($value, true);
        if (! is_array($decoded)) {
            return null;
        }

        $summary  = trim((string) ($decoded['summary'] ?? ''));
        $followUp = $decoded['follow_up'] ?? false;

        if (! is_bool($followUp)) {
            $followUp = in_array($followUp, ['1', 1, 'true', true, 'on'], true);
        }

        return [
            'summary'   => $summary,
            'follow_up' => (bool) $followUp,
        ];
    }

    private function normalizeStatus(string $status): ?string
    {
        $normalized = strtolower(trim($status));

        if ($normalized === '') {
            return null;
        }

        return array_key_exists($normalized, self::STATUS_OPTIONS) ? $normalized : null;
    }
}
