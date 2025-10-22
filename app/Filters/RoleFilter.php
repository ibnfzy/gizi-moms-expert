<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('auth');

        $user = auth_user();

        if ($user === null) {
            return $this->forbiddenResponse('User context missing.');
        }

        $allowedRoles = array_map('strtolower', $arguments ?? []);

        if ($allowedRoles !== [] && ! in_array(strtolower((string) $user['role']), $allowedRoles, true)) {
            return $this->forbiddenResponse('You do not have permission to access this resource.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do after request.
    }

    private function forbiddenResponse(string $message): ResponseInterface
    {
        $response = Services::response();

        return $response
            ->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
            ->setJSON([
                'status'  => false,
                'message' => $message,
            ]);
    }
}
