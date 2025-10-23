<?php

namespace App\Filters;

use App\Libraries\JWTService;
use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('auth');

        $authHeader = $request->getHeaderLine('Authorization');
        $token = auth_token($authHeader);

        if ($token === null) {
            return errorResponse('Authorization token missing.', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        try {
            $jwtService = new JWTService();
            $payload = $jwtService->validateToken($token);
        } catch (\Throwable $exception) {
            log_message('error', 'JWT validation error: ' . $exception->getMessage());

            return errorResponse('Token expired or invalid', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        if ($payload === null || empty($payload['id'])) {
            return errorResponse('Token expired or invalid', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $userModel = new UserModel();
        $user = $userModel->find($payload['id']);

        if (! $user) {
            return errorResponse('User not found.', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        unset($user['password_hash']);

        set_auth_user($user);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action required after request.
    }

}
