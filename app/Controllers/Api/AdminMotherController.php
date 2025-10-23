<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\MotherFormatter;
use App\Models\MotherModel;
use App\Models\UserModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\ResponseInterface;

class AdminMotherController extends BaseController
{
    private MotherModel $mothers;
    private UserModel $users;
    private MotherFormatter $formatter;

    public function __construct()
    {
        helper('auth');

        $this->mothers   = new MotherModel();
        $this->users     = new UserModel();
        $this->formatter = new MotherFormatter();
    }

    public function index()
    {
        $records = $this->mothers
            ->withUser()
            ->orderBy('users.name', 'ASC')
            ->get()->getResultArray();

        $payload = array_map(fn(array $mother): array => $this->formatter->present($mother, false, false), $records);

        return successResponse($payload, 'Daftar ibu berhasil dimuat.');
    }

    public function show(int $id)
    {
        $mother = $this->mothers
            ->withUser()
            ->where('mothers.id', $id)
            ->get()->getRowArray();

        if (! is_array($mother)) {
            return $this->notFound();
        }

        $payload = $this->formatter->present($mother, true, true);

        return successResponse($payload, 'Data ibu berhasil dimuat.');
    }

    public function updateEmail(int $id)
    {
        $mother = $this->mothers->find($id);

        if (! is_array($mother)) {
            return $this->notFound();
        }

        $payload = get_request_data($this->request);
        $payload['email'] = isset($payload['email']) ? trim((string) $payload['email']) : null;

        $uniqueRule = 'is_unique[users.email]';
        if (! empty($mother['user_id'])) {
            $uniqueRule = 'is_unique[users.email,id,' . $mother['user_id'] . ']';
        }

        $rules = [
            'email' => 'required|valid_email|' . $uniqueRule,
        ];

        if (! $this->validateData($payload, $rules)) {
            return errorResponse(
                'Data email tidak valid.',
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->validator->getErrors()
            );
        }

        if (empty($mother['user_id'])) {
            return errorResponse(
                'Data pengguna untuk ibu ini tidak tersedia.',
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        $updated = $this->users->update($mother['user_id'], [
            'email' => $payload['email'],
        ]);

        if (! $updated) {
            return errorResponse(
                'Tidak dapat memperbarui email pengguna.',
                ResponseInterface::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return successResponse(null, 'Email berhasil diperbarui.');
    }

    public function updatePassword(int $id)
    {
        $mother = $this->mothers->find($id);

        if (! is_array($mother)) {
            return $this->notFound();
        }

        $payload = get_request_data($this->request);
        $payload['password'] = isset($payload['password']) ? trim((string) $payload['password']) : null;

        $rules = [
            'password' => 'required|min_length[8]',
        ];

        if (! $this->validateData($payload, $rules)) {
            return errorResponse(
                'Password tidak valid.',
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->validator->getErrors()
            );
        }

        if (empty($mother['user_id'])) {
            return errorResponse(
                'Data pengguna untuk ibu ini tidak tersedia.',
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        $updated = $this->users->update($mother['user_id'], [
            'password_hash' => password_hash($payload['password'], PASSWORD_DEFAULT),
        ]);

        if (! $updated) {
            return errorResponse(
                'Tidak dapat memperbarui password pengguna.',
                ResponseInterface::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return successResponse(null, 'Password berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $mother = $this->mothers->find($id);

        if (! is_array($mother)) {
            return $this->notFound();
        }

        $db = $this->mothers->db;

        if ($db instanceof BaseConnection) {
            $db->transStart();
        }

        $result = true;
        if (! empty($mother['user_id'])) {
            $result = $this->users->delete($mother['user_id']);
        } else {
            $result = $this->mothers->delete($id);
        }

        if ($db instanceof BaseConnection) {
            $db->transComplete();
            if (! $db->transStatus()) {
                $result = false;
            }
        }

        if (! $result) {
            return errorResponse('Tidak dapat menghapus data ibu.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        return successResponse(null, 'Data ibu berhasil dihapus.');
    }

    private function notFound(): ResponseInterface
    {
        return errorResponse('Data ibu tidak ditemukan.', ResponseInterface::HTTP_NOT_FOUND);
    }
}
