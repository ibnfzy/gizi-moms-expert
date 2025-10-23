<?php

namespace App\Controllers;

class AdminUserController extends BaseController
{
    public function index(): string
    {
        return view('admin/users', [
            'title' => 'Manajemen Pengguna',
        ]);
    }
}
