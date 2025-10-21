<?php

namespace App\Controllers;

class PakarDashboardController extends BaseController
{
    public function index(): string
    {
        $data = [
            'ibuMenyusui' => [
                [
                    'nama' => 'Siti Rahma',
                    'umur' => 29,
                    'status' => 'Sehat',
                    'warna' => 'green',
                ],
                [
                    'nama' => 'Dewi Lestari',
                    'umur' => 32,
                    'status' => 'Perlu Pemantauan',
                    'warna' => 'yellow',
                ],
                [
                    'nama' => 'Nina Kartika',
                    'umur' => 24,
                    'status' => 'Butuh Tindakan',
                    'warna' => 'red',
                ],
            ],
        ];

        return view('pakar/dashboard', $data);
    }
}
