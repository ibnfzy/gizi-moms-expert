<?php

declare(strict_types=1);

use CodeIgniter\HTTP\ResponseInterface;

if (! function_exists('successResponse')) {
    /**
     * Return a standardized JSON success response.
     */
    function successResponse($data = null, string $message = 'Success', int $code = ResponseInterface::HTTP_OK): ResponseInterface
    {
        return service('response')
            ->setStatusCode($code)
            ->setJSON([
                'status'  => true,
                'message' => $message,
                'data'    => $data,
            ]);
    }
}

if (! function_exists('errorResponse')) {
    /**
     * Return a standardized JSON error response.
     */
    function errorResponse(string $message = 'Error', int $code = ResponseInterface::HTTP_BAD_REQUEST, $data = null): ResponseInterface
    {
        return service('response')
            ->setStatusCode($code)
            ->setJSON([
                'status'  => false,
                'message' => $message,
                'data'    => $data,
            ]);
    }
}
