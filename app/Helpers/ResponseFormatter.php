<?php

declare(strict_types=1);

use CodeIgniter\HTTP\ResponseInterface;

if (! function_exists('successResponse')) {
    /**
     * Return a standardized JSON success response.
     */
    function successResponse($data, string $message = 'Success'): ResponseInterface
    {
        return service('response')->setJSON([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ]);
    }
}

if (! function_exists('errorResponse')) {
    /**
     * Return a standardized JSON error response.
     */
    function errorResponse(string $message = 'Error', int $code = 400, $data = null): ResponseInterface
    {
        return service('response')
            ->setStatusCode($code)
            ->setJSON([
                'status'  => 'error',
                'message' => $message,
                'data'    => $data,
            ]);
    }
}
