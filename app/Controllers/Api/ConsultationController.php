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

    public function create()
    {
        $data = get_request_data($this->request);

        $motherId = isset($data['mother_id']) ? (int) $data['mother_id'] : null;
        $pakarId  = isset($data['pakar_id']) ? (int) $data['pakar_id'] : null;
        $status   = $data['status'] ?? 'pending';
        $notes    = $data['notes'] ?? null;

        if ($motherId === null || $motherId <= 0) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON([
                    'status'  => false,
                    'message' => 'mother_id is required.',
                ]);
        }

        $mother = $this->mothers->find($motherId);
        if (! is_array($mother)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Mother data not found.',
                ]);
        }

        $user = auth_user();
        if ($user === null) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON([
                    'status'  => false,
                    'message' => 'User context missing.',
                ]);
        }

        if ($user['role'] === 'pakar') {
            $pakarId = $pakarId ?: (int) $user['id'];
        } else {
            if ((int) $mother['user_id'] !== (int) $user['id']) {
                return $this->response
                    ->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                    ->setJSON([
                        'status'  => false,
                        'message' => 'You cannot create a consultation for this mother.',
                    ]);
            }

            if ($pakarId === null || $pakarId <= 0) {
                return $this->response
                    ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                    ->setJSON([
                        'status'  => false,
                        'message' => 'pakar_id is required when created by a mother.',
                    ]);
            }
        }

        $pakar = $this->users
            ->where('role', 'pakar')
            ->find($pakarId);

        if (! is_array($pakar)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Pakar not found.',
                ]);
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
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Failed to create consultation.',
                ]);
        }

        $consultation = $this->consultations->find($consultationId);
        $motherDetail = $this->mothers
            ->withUser()
            ->where('mothers.id', $motherId)
            ->first();

        $payload = [
            'id'          => $consultationId,
            'mother_id'   => $motherId,
            'pakar_id'    => $pakarId,
            'status'      => $status,
            'notes'       => $consultation['notes'] ?? $notes,
            'created_at'  => $consultation['created_at'] ?? Time::now()->toDateTimeString(),
            'mother'      => is_array($motherDetail) ? $this->formatter->present($motherDetail, true, false) : null,
        ];

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON([
                'status' => true,
                'data'   => $payload,
            ]);
    }
}
