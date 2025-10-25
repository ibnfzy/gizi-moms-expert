<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\MotherFormatter;
use App\Models\ConsultationModel;
use App\Models\MotherModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;

class ConsultationController extends BaseController
{
    private ConsultationModel $consultations;
    private MotherModel $mothers;
    private UserModel $users;
    private MotherFormatter $formatter;

    public function __construct()
    {
        helper('auth');

        $this->consultations = new ConsultationModel();
        $this->mothers       = new MotherModel();
        $this->users         = new UserModel();
        $this->formatter     = new MotherFormatter();
    }

    public function index()
    {
        $user = auth_user();

        if ($user === null) {
            return errorResponse('Unauthorized.', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $role = strtolower((string) ($user['role'] ?? ''));

        if (! in_array($role, ['pakar', 'ibu'], true)) {
            return errorResponse(
                'You do not have permission to view consultations.',
                ResponseInterface::HTTP_FORBIDDEN
            );
        }

        $records = [];
        $motherRecords = [];

        if ($role === 'ibu') {
            $motherRecord = $this->mothers
                ->withUser()
                ->where('mothers.user_id', $user['id'])
                ->get()->getRowArray();

            if (! is_array($motherRecord)) {
                return errorResponse('Mother profile not found for this user.', ResponseInterface::HTTP_NOT_FOUND);
            }

            $records = $this->consultations
                ->where('mother_id', $motherRecord['id'])
                ->orderBy('updated_at', 'DESC')
                ->orderBy('created_at', 'DESC')
                ->findAll();

            $motherRecords[(int) $motherRecord['id']] = $motherRecord;
        } else {
            $records = $this->consultations
                ->where('pakar_id', $user['id'])
                ->orderBy('updated_at', 'DESC')
                ->orderBy('created_at', 'DESC')
                ->findAll();

            $motherIds = array_values(array_unique(array_map(
                static fn(array $record): int => (int) ($record['mother_id'] ?? 0),
                $records
            )));

            $motherIds = array_values(array_filter(
                $motherIds,
                static fn(int $id): bool => $id > 0
            ));

            if ($motherIds !== []) {
                $motherQuery = $this->mothers
                    ->withUser()
                    ->whereIn('mothers.id', $motherIds)
                    ->get();

                foreach ($motherQuery->getResultArray() as $motherRecord) {
                    $motherRecords[(int) $motherRecord['id']] = $motherRecord;
                }
            }
        }

        $formattedMothers = [];
        foreach ($motherRecords as $id => $motherRecord) {
            $formattedMothers[$id] = $this->formatter->present($motherRecord, true, false);
        }

        $pakarIds = array_values(array_unique(array_map(
            static fn(array $record): int => (int) ($record['pakar_id'] ?? 0),
            $records
        )));

        $pakarMap = [];
        $pakarIds = array_values(array_filter(
            $pakarIds,
            static fn(int $id): bool => $id > 0
        ));

        if ($pakarIds !== []) {
            $pakarRecords = $this->users
                ->select('id, name, email, role')
                ->whereIn('id', $pakarIds)
                ->findAll();

            foreach ($pakarRecords as $pakar) {
                $pakarMap[(int) ($pakar['id'] ?? 0)] = $pakar;
            }
        }

        $currentId = isset($user['id']) ? (int) $user['id'] : null;
        if ($currentId !== null && $role === 'pakar' && ! array_key_exists($currentId, $pakarMap)) {
            $pakarMap[$currentId] = $user;
        }

        $payload = array_map(
            fn(array $consultation): array => $this->formatConsultationRecord(
                $consultation,
                $formattedMothers,
                $pakarMap
            ),
            $records
        );

        return successResponse($payload, 'Daftar konsultasi berhasil dimuat.');
    }

    public function create()
    {
        $data = get_request_data($this->request);

        $motherId = isset($data['mother_id']) ? (int) $data['mother_id'] : null;
        $pakarId  = isset($data['pakar_id']) ? (int) $data['pakar_id'] : null;
        $status   = $data['status'] ?? 'pending';
        $notes    = $data['notes'] ?? null;

        if ($motherId === null || $motherId <= 0) {
            return errorResponse('mother_id is required.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $mother = $this->mothers->find($motherId);
        if (! is_array($mother)) {
            return errorResponse('Mother data not found.', ResponseInterface::HTTP_NOT_FOUND);
        }

        $user = auth_user();
        if ($user === null) {
            return errorResponse('User context missing.', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        if ($user['role'] === 'pakar') {
            $pakarId = $pakarId ?: (int) $user['id'];
        } else {
            if ((int) $mother['user_id'] !== (int) $user['id']) {
                return errorResponse(
                    'You cannot create a consultation for this mother.',
                    ResponseInterface::HTTP_FORBIDDEN
                );
            }

            if ($pakarId === null || $pakarId <= 0) {
                return errorResponse(
                    'pakar_id is required when created by a mother.',
                    ResponseInterface::HTTP_BAD_REQUEST
                );
            }
        }

        $pakar = $this->users
            ->where('role', 'pakar')
            ->find($pakarId);

        if (! is_array($pakar)) {
            return errorResponse('Pakar not found.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $status = strtolower((string) $status);
        $allowedStatus = ['pending', 'ongoing', 'completed'];
        if (! in_array($status, $allowedStatus, true)) {
            $status = 'pending';
        }

        $insertData = [
            'mother_id' => $motherId,
            'pakar_id'  => $pakarId,
            'status'    => $status,
            'notes'     => $notes,
        ];

        $consultationId = $this->consultations->insert($insertData, true);

        if (! is_int($consultationId) || $consultationId <= 0) {
            return errorResponse('Failed to create consultation.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        $consultation = $this->consultations->find($consultationId);
        $motherDetail = $this->mothers
            ->withUser()
            ->where('mothers.id', $motherId)
            ->get()->getRowArray();

        $payload = [
            'id'          => $consultationId,
            'mother_id'   => $motherId,
            'pakar_id'    => $pakarId,
            'status'      => $status,
            'notes'       => $consultation['notes'] ?? $notes,
            'created_at'  => $consultation['created_at'] ?? Time::now()->toDateTimeString(),
            'mother'      => is_array($motherDetail) ? $this->formatter->present($motherDetail, true, false) : null,
        ];

        return successResponse(
            $payload,
            'Konsultasi berhasil dibuat.',
            ResponseInterface::HTTP_CREATED
        );
    }

    /**
     * @param array<string, mixed> $consultation
     * @param array<int, array<string, mixed>> $motherDetails
     * @param array<int, array<string, mixed>> $pakarMap
     */
    private function formatConsultationRecord(array $consultation, array $motherDetails, array $pakarMap): array
    {
        $motherId = (int) ($consultation['mother_id'] ?? 0);
        $pakarId  = (int) ($consultation['pakar_id'] ?? 0);

        return [
            'id'         => isset($consultation['id']) ? (int) $consultation['id'] : null,
            'mother_id'  => $motherId ?: null,
            'pakar_id'   => $pakarId ?: null,
            'status'     => $consultation['status'] ?? 'pending',
            'notes'      => $consultation['notes'] ?? null,
            'created_at' => $consultation['created_at'] ?? null,
            'updated_at' => $consultation['updated_at'] ?? null,
            'mother'     => $motherDetails[$motherId] ?? null,
            'pakar'      => array_key_exists($pakarId, $pakarMap)
                ? $this->formatUserSummary($pakarMap[$pakarId])
                : null,
        ];
    }

    /**
     * @param array<string, mixed> $user
     */
    private function formatUserSummary(array $user): array
    {
        return [
            'id'    => isset($user['id']) ? (int) $user['id'] : null,
            'name'  => $user['name'] ?? null,
            'email' => $user['email'] ?? null,
            'role'  => $user['role'] ?? null,
        ];
    }
}
