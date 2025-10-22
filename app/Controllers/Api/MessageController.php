<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ConsultationModel;
use App\Models\MessageModel;
use App\Models\MotherModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;

class MessageController extends BaseController
{
    private MessageModel $messages;
    private ConsultationModel $consultations;
    private MotherModel $mothers;

    public function __construct()
    {
        helper('auth');

        $this->messages       = new MessageModel();
        $this->consultations  = new ConsultationModel();
        $this->mothers        = new MotherModel();
    }

    public function index(int $consultationId)
    {
        $consultation = $this->consultations->find($consultationId);

        if (! is_array($consultation)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Consultation not found.',
                ]);
        }

        if (! $this->isParticipant($consultation)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                ->setJSON([
                    'status'  => false,
                    'message' => 'You are not allowed to access this consultation.',
                ]);
        }

        $messages = $this->messages
            ->where('consultation_id', $consultationId)
            ->orderBy('created_at', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $payload = array_map(fn (array $message): array => $this->formatMessage($message), $messages);

        return $this->response->setJSON([
            'status' => true,
            'data'   => $payload,
        ]);
    }

    public function create()
    {
        $data = get_request_data($this->request);

        $consultationId = isset($data['consultation_id']) ? (int) $data['consultation_id'] : null;
        $text           = trim((string) ($data['text'] ?? ''));

        if ($consultationId === null || $consultationId <= 0) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON([
                    'status'  => false,
                    'message' => 'consultation_id is required.',
                ]);
        }

        if ($text === '') {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Message text cannot be empty.',
                ]);
        }

        $consultation = $this->consultations->find($consultationId);
        if (! is_array($consultation)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Consultation not found.',
                ]);
        }

        if (! $this->isParticipant($consultation)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                ->setJSON([
                    'status'  => false,
                    'message' => 'You are not allowed to send a message to this consultation.',
                ]);
        }

        $user  = auth_user();
        $role  = $user['role'] ?? null;
        $validRoles = ['pakar', 'ibu'];

        if (! in_array($role, $validRoles, true)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Only pakar or ibu can send messages.',
                ]);
        }

        $messageId = $this->messages->insert([
            'consultation_id' => $consultationId,
            'sender_role'     => $role,
            'text'            => $text,
            'created_at'      => Time::now()->toDateTimeString(),
        ], true);

        if (! is_int($messageId) || $messageId <= 0) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Failed to send message.',
                ]);
        }

        $message = $this->messages->find($messageId);

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON([
                'status' => true,
                'data'   => $this->formatMessage($message ?: []),
            ]);
    }

    /**
     * @param array<string, mixed> $consultation
     */
    private function isParticipant(array $consultation): bool
    {
        $user = auth_user();

        if ($user === null) {
            return false;
        }

        if ($user['role'] === 'pakar') {
            return (int) $consultation['pakar_id'] === (int) $user['id'];
        }

        $mother = $this->mothers->find($consultation['mother_id']);

        return is_array($mother) && (int) $mother['user_id'] === (int) $user['id'];
    }

    /**
     * @param array<string, mixed> $message
     */
    private function formatMessage(array $message): array
    {
        $timestamp = $message['created_at'] ?? null;

        try {
            $time = $timestamp ? Time::parse($timestamp) : null;
        } catch (\Throwable $exception) {
            $time = null;
        }

        return [
            'id'         => isset($message['id']) ? (int) $message['id'] : null,
            'sender'     => $message['sender_role'] ?? null,
            'text'       => $message['text'] ?? null,
            'created_at' => $time?->toDateTimeString() ?? $timestamp,
            'humanize'   => $time?->humanize(),
        ];
    }
}
