<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('auth');

        $user = auth_user();

        if ($user === null) {
            return errorResponse('User context missing.', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $allowedRoles = array_map('strtolower', $arguments ?? []);

        if ($allowedRoles !== [] && ! in_array(strtolower((string) $user['role']), $allowedRoles, true)) {
            return errorResponse('Unauthorized access', ResponseInterface::HTTP_FORBIDDEN);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do after request.
    }

}
