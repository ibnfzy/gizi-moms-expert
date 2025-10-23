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
            ->findAll();

        $payload = array_map(fn (array $mother): array => $this->formatter->present($mother, false, false), $records);

        return $this->response->setJSON([
            'status' => true,
            'data'   => $payload,
        ]);
    }

    public function show(int $id)
    {
        $mother = $this->mothers
            ->withUser()
            ->where('mothers.id', $id)
            ->first();

        if (! is_array($mother)) {
            return $this->notFound();
        }

        $payload = $this->formatter->present($mother, true, true);

        return $this->response->setJSON([
            'status' => true,
            'data'   => $payload,
        ]);
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
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ]);
        }

        if (empty($mother['user_id'])) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Data pengguna untuk ibu ini tidak tersedia.',
                ]);
        }

        $updated = $this->users->update($mother['user_id'], [
            'email' => $payload['email'],
        ]);

        if (! $updated) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Tidak dapat memperbarui email pengguna.',
                ]);
        }

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Email berhasil diperbarui.',
        ]);
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
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ]);
        }

        if (empty($mother['user_id'])) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Data pengguna untuk ibu ini tidak tersedia.',
                ]);
        }

        $updated = $this->users->update($mother['user_id'], [
            'password_hash' => password_hash($payload['password'], PASSWORD_DEFAULT),
        ]);

        if (! $updated) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Tidak dapat memperbarui password pengguna.',
                ]);
        }

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Password berhasil diperbarui.',
        ]);
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
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Tidak dapat menghapus data ibu.',
                ]);
        }

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Data ibu berhasil dihapus.',
        ]);
    }

    private function notFound(): ResponseInterface
    {
        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
            ->setJSON([
                'status'  => false,
                'message' => 'Data ibu tidak ditemukan.',
            ]);
    }
}
