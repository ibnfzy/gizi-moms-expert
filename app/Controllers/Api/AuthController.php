<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\JWTService;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class AuthController extends BaseController
{
    protected UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
        helper('auth');
    }

    public function login()
    {
        $data = get_request_data($this->request);

        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (! $this->validateData($data, $rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ]);
        }

        $user = $this->users->where('email', $data['email'])->first();

        if (! $user || ! password_verify($data['password'], $user['password_hash'])) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Invalid email or password.',
                ]);
        }

        try {
            $jwt = new JWTService();
            $token = $jwt->generateToken($user);
        } catch (\Throwable $exception) {
            log_message('error', 'Failed to generate token: ' . $exception->getMessage());

            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Unable to generate authentication token.',
                ]);
        }
        $userData = $this->formatUser($user);

        return $this->response->setJSON([
            'status' => true,
            'token'  => $token,
            'user'   => $userData,
        ]);
    }

    public function register()
    {
        $data = get_request_data($this->request);

        $rules = [
            'name'     => 'required|string',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'role'     => 'required|in_list[admin,pakar,ibu]',
        ];

        if (! $this->validateData($data, $rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ]);
        }

        $userId = $this->users->insert([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role'          => $data['role'],
        ]);

        if (! $userId) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Unable to create user.',
                ]);
        }

        $newUser = $this->users->find($userId);

        if (! $newUser) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Unable to retrieve user information.',
                ]);
        }

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON([
                'status' => true,
                'user'   => $this->formatUser($newUser),
            ]);
    }

    public function me()
    {
        $user = auth_user();

        if (! $user) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Unauthorized.',
                ]);
        }

        return $this->response->setJSON([
            'status' => true,
            'user'   => $user,
        ]);
    }

    private function formatUser(array $user): array
    {
        unset($user['password_hash']);

        return $user;
    }
}
