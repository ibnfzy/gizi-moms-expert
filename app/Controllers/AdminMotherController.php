<?php

namespace App\Controllers;

class AdminMotherController extends BaseController
{
    public function index(): string
    {
        return view('admin/mothers', [
            'title' => 'Manajemen Data Ibu',
        ]);
    }
}
