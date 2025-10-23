<?php

namespace App\Controllers;

use App\Libraries\JWTService;
use App\Models\UserModel;
use DateInterval;

class AuthController extends BaseController
{
    public function login()
    {
        $session = session();

        if ($session->get('isLoggedIn')) {
            return redirect()->to($this->redirectPathForRole($session->get('user_role')));
        }

        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'role'     => 'required|in_list[admin,pakar]',
                'email'    => 'required|valid_email',
                'password' => 'required',
            ];

            if (! $this->validate($rules)) {
                $errorMessage = implode(' ', $this->validator->getErrors());

                return redirect()->back()->withInput()->with('error', $errorMessage ?: 'Validasi gagal.');
            }

            $role = strtolower($this->request->getPost('role'));
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');

            $userModel = new UserModel();
            $user = $userModel->where('email', $email)->first();

            if (! $user || ! password_verify($password, $user['password_hash'] ?? '')) {
                return redirect()->back()->withInput()->with('error', 'Email atau kata sandi tidak valid.');
            }

            if (strtolower($user['role'] ?? '') !== $role) {
                return redirect()->back()->withInput()->with('error', 'Peran yang dipilih tidak sesuai dengan akun.');
            }

            $normalizedRole = strtolower($user['role'] ?? '');

            try {
                $jwtService = new JWTService();
                $token = $jwtService->generateToken($user, new DateInterval('P1D'));
            } catch (\Throwable $exception) {
                log_message('error', 'Failed to generate auth token: ' . $exception->getMessage());

                return redirect()->back()->withInput()->with('error', 'Gagal membuat token autentikasi. Silakan coba lagi.');
            }

            $session->set([
                'user_id'    => $user['id'],
                'user_name'  => $user['name'],
                'user_email' => $user['email'],
                'user_role'  => $normalizedRole,
                'isLoggedIn' => true,
                'auth_token' => $token,
            ]);

            return redirect()->to($this->redirectPathForRole($normalizedRole));
        }

        return view('auth/login');
    }

    public function logout()
    {
        $session = session();
        $session->destroy();

        return redirect()->to('/login');
    }

    private function redirectPathForRole(?string $role): string
    {
        return match (strtolower($role ?? '')) {
            'admin' => '/admin/dashboard',
            'pakar' => '/pakar/dashboard',
            default => '/',
        };
    }
}
