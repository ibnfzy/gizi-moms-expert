<?php

namespace App\Controllers;

class AuthController extends BaseController
{
    public function login()
    {
        return view('auth/login');
    }
}
