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
        helper(['auth', 'responseformatter']);

        $authHeader = $request->getHeaderLine('Authorization');
        $token = auth_token($authHeader);

        if ($token === null) {
            return $this->unauthorizedResponse('Authorization token missing.');
        }

        try {
            $jwtService = new JWTService();
            $payload = $jwtService->validateToken($token);
        } catch (\Throwable $exception) {
            log_message('error', 'JWT validation error: ' . $exception->getMessage());
            return $this->unauthorizedResponse('Failed to validate token.');
        }

        if ($payload === null || empty($payload['id'])) {
            return $this->unauthorizedResponse('Invalid or expired token.');
        }

        $userModel = new UserModel();
        $user = $userModel->find($payload['id']);

        if (! $user) {
            return $this->unauthorizedResponse('User not found.');
        }

        unset($user['password_hash']);

        set_auth_user($user);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action required after request.
    }

    private function unauthorizedResponse(string $message): ResponseInterface
    {
        return errorResponse($message, ResponseInterface::HTTP_UNAUTHORIZED);
    }
}
