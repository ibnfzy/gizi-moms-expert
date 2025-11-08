<?php

use App\Libraries\JWTService;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\IncomingRequest;

if (! function_exists('get_request_data')) {
    function get_request_data(IncomingRequest $request): array
    {
        try {
            $data = $request->getJSON(true) ?? [];
        } catch (HTTPException $exception) {
            $data = [];
        }

        if (! is_array($data) || $data === []) {
            $data = $request->getPost();
        }

        return is_array($data) ? $data : [];
    }
}

if (! function_exists('set_auth_user')) {
    function set_auth_user(array $user): void
    {
        $GLOBALS['auth_user'] = $user;
    }
}

if (! function_exists('auth_user')) {
    function auth_user(): ?array
    {
        return $GLOBALS['auth_user'] ?? null;
    }
}

if (! function_exists('auth_token')) {
    function auth_token(?string $header): ?string
    {
        if (empty($header) || stripos($header, 'bearer ') !== 0) {
            return null;
        }

        return trim(substr($header, 7));
    }
}

if (! function_exists('jwt_service')) {
    function jwt_service(): JWTService
    {
        return new JWTService();
    }
}
