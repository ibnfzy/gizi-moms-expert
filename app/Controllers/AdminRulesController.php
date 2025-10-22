<?php

namespace App\Controllers;

class AdminRulesController extends BaseController
{
    public function index(): string
    {
        return view('admin/rules', [
            'title' => 'Manajemen Rules',
        ]);
    }
}
