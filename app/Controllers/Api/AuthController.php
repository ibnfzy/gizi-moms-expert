<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\JWTService;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use DateInterval;

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
            return errorResponse(
                'Validation failed.',
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->validator->getErrors()
            );
        }

        $user = $this->users->where('email', $data['email'])->first();

        if (! $user || ! password_verify($data['password'], $user['password_hash'])) {
            return errorResponse('Invalid email or password.', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        try {
            $jwt = new JWTService();
            $token = $jwt->generateToken($user, new DateInterval('P1D'));
        } catch (\Throwable $exception) {
            log_message('error', 'Failed to generate token: ' . $exception->getMessage());

            return errorResponse('Unable to generate authentication token.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
        $userData = $this->formatUser($user);

        return successResponse(
            [
                'token' => $token,
                'user'  => $userData,
            ],
            'Login successful.'
        );
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
            return errorResponse(
                'Validation failed.',
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->validator->getErrors()
            );
        }

        $userId = $this->users->insert([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role'          => $data['role'],
        ]);

        if (! $userId) {
            return errorResponse('Unable to create user.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        $newUser = $this->users->find($userId);

        if (! $newUser) {
            return errorResponse('Unable to retrieve user information.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        return successResponse(
            $this->formatUser($newUser),
            'User registered successfully.',
            ResponseInterface::HTTP_CREATED
        );
    }

    public function me()
    {
        $user = auth_user();

        if (! $user) {
            return errorResponse('Unauthorized.', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        return successResponse($user, 'Authenticated user retrieved.');
    }

    private function formatUser(array $user): array
    {
        unset($user['password_hash']);

        return $user;
    }
}
