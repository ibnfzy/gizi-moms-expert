<?php

namespace App\Controllers;

use App\Libraries\MotherFormatter;
use App\Models\ConsultationModel;
use App\Models\MessageModel;
use App\Models\MotherModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;

class PakarConsultationController extends BaseController
{
    private ConsultationModel $consultations;
    private MessageModel $messages;
    private MotherModel $mothers;
    private MotherFormatter $formatter;

    public function __construct()
    {
        $this->consultations = new ConsultationModel();
        $this->messages      = new MessageModel();
        $this->mothers       = new MotherModel();
        $this->formatter     = new MotherFormatter();
    }

    public function index(): string
    {
        $session  = session();
        $pakarId  = (int) ($session->get('user_id') ?? 0);
        $userRole = (string) ($session->get('user_role') ?? 'pakar');

        $consultations = $this->loadConsultations($pakarId, $userRole);

        return view('pakar/consultation', [
            'consultations'        => $consultations,
            'selectedConsultation' => $consultations[0] ?? null,
            'userRole'             => $userRole,
        ]);
    }

    public function conversation(int $consultationId): ResponseInterface
    {
        $session  = session();
        $pakarId  = (int) ($session->get('user_id') ?? 0);
        $userRole = (string) ($session->get('user_role') ?? 'pakar');

        $consultations = $this->loadConsultations($pakarId, $userRole);
        $selected      = $this->findConsultation($consultations, $consultationId);

        if ($selected === null) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setBody('<div class="p-4 text-sm text-red-700">Sesi konsultasi tidak ditemukan.</div>');
        }

        return $this->response->setBody(view('pakar/partials/consultation_page', [
            'consultations'        => $consultations,
            'selectedConsultation' => $selected,
            'userRole'             => $userRole,
        ]));
    }

    public function sendMessage(int $consultationId): ResponseInterface
    {
        $session  = session();
        $pakarId  = (int) ($session->get('user_id') ?? 0);
        $userRole = (string) ($session->get('user_role') ?? 'pakar');

        $consultation = $this->consultations
            ->where('pakar_id', $pakarId)
            ->find($consultationId);

        if (! is_array($consultation)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setBody('<div class="p-4 text-sm text-red-700">Sesi konsultasi tidak ditemukan.</div>');
        }

        $text = trim((string) ($this->request->getPost('text') ?? ''));

        if ($text === '') {
            return $this->renderConversationResponse(
                $pakarId,
                $userRole,
                $consultationId,
                [
                    'type'    => 'error',
                    'message' => 'Isi pesan sebelum mengirim.',
                ],
                ''
            )->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($userRole !== 'pakar') {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                ->setBody('<div class="p-4 text-sm text-red-700">Anda tidak diizinkan mengirim pesan.</div>');
        }

        $messageId = $this->messages->insert([
            'consultation_id' => $consultationId,
            'sender_role'     => $userRole,
            'text'            => $text,
            'created_at'      => Time::now()->toDateTimeString(),
        ], true);

        if (! is_int($messageId) || $messageId <= 0) {
            return $this->renderConversationResponse(
                $pakarId,
                $userRole,
                $consultationId,
                [
                    'type'    => 'error',
                    'message' => 'Pesan gagal dikirim.',
                ],
                $text
            )->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->renderConversationResponse(
            $pakarId,
            $userRole,
            $consultationId,
            [
                'type'    => 'success',
                'message' => 'Pesan berhasil dikirim.',
            ],
            ''
        );
    }

    /**
     * @param array<string, mixed> $message
     */
    private function formatMessage(array $message, string $currentRole): array
    {
        $timestamp = $message['created_at'] ?? null;

        try {
            $time = $timestamp ? Time::parse($timestamp) : null;
        } catch (\Throwable $exception) {
            $time = null;
        }

        $senderRole = $message['sender_role'] ?? null;

        return [
            'id'         => isset($message['id']) ? (int) $message['id'] : null,
            'sender'     => $senderRole,
            'text'       => $message['text'] ?? '',
            'created_at' => $time?->toDateTimeString() ?? $timestamp,
            'humanize'   => $time?->humanize(),
            'is_self'    => $senderRole !== null && $senderRole === $currentRole,
        ];
    }

    /**
     * @return array{iso: string|null, human: string|null}
     */
    private function formatTimestamp(?string $timestamp): array
    {
        if (empty($timestamp)) {
            return ['iso' => null, 'human' => null];
        }

        try {
            $time = Time::parse($timestamp);
        } catch (\Throwable $exception) {
            return ['iso' => $timestamp, 'human' => null];
        }

        return [
            'iso'   => $time->toDateTimeString(),
            'human' => $time->humanize(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loadConsultations(int $pakarId, string $userRole): array
    {
        $records = $this->consultations
            ->where('pakar_id', $pakarId)
            ->orderBy('updated_at', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();

        $mothersCache   = [];
        $consultations  = [];

        foreach ($records as $consultation) {
            $motherId = (int) ($consultation['mother_id'] ?? 0);

            if (! array_key_exists($motherId, $mothersCache)) {
                $mothersCache[$motherId] = $this->mothers
                    ->withUser()
                    ->where('mothers.id', $motherId)
                    ->get()->getRowArray() ?: null;
            }

            $motherRecord = $mothersCache[$motherId];
            $motherData   = is_array($motherRecord)
                ? $this->formatter->present($motherRecord, true, false)
                : null;

            $messages = $this->messages
                ->where('consultation_id', $consultation['id'])
                ->orderBy('created_at', 'ASC')
                ->orderBy('id', 'ASC')
                ->get()->getResultArray();

            $formattedMessages = array_map(
                fn(array $message): array => $this->formatMessage($message, $userRole),
                $messages
            );

            $lastMessage = $formattedMessages !== [] ? end($formattedMessages) : null;
            if ($formattedMessages !== []) {
                reset($formattedMessages);
            }

            $timestamp = $consultation['updated_at'] ?? $consultation['created_at'] ?? null;
            $timeData  = $this->formatTimestamp($timestamp);

            $consultations[] = [
                'id'            => (int) $consultation['id'],
                'status'        => $consultation['status'] ?? 'pending',
                'notes'         => $consultation['notes'] ?? null,
                'mother'        => $motherData,
                'messages'      => $formattedMessages,
                'last_message'  => $lastMessage,
                'created_at'    => $timeData['iso'],
                'updated_human' => $timeData['human'],
            ];
        }

        return $consultations;
    }

    /**
     * @param array<int, array<string, mixed>> $consultations
     */
    private function findConsultation(array $consultations, int $consultationId): ?array
    {
        foreach ($consultations as $consultation) {
            if ((int) ($consultation['id'] ?? 0) === $consultationId) {
                return $consultation;
            }
        }

        return null;
    }

    private function renderConversationResponse(
        int $pakarId,
        string $userRole,
        int $consultationId,
        ?array $feedback,
        string $messageText
    ): ResponseInterface {
        $consultations = $this->loadConsultations($pakarId, $userRole);
        $selected      = $this->findConsultation($consultations, $consultationId);

        if ($selected === null) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setBody('<div class="p-4 text-sm text-red-700">Sesi konsultasi tidak ditemukan.</div>');
        }

        return $this->response->setBody(view('pakar/partials/consultation_page', [
            'consultations'        => $consultations,
            'selectedConsultation' => $selected,
            'userRole'             => $userRole,
            'feedback'             => $feedback,
            'messageText'          => $messageText,
        ]));
    }
}
