<?php

namespace App\Controllers;

use App\Libraries\MotherFormatter;
use App\Models\MotherModel;

class PakarDashboardController extends BaseController
{
    private MotherModel $mothers;
    private MotherFormatter $formatter;

    public function __construct()
    {
        $this->mothers   = new MotherModel();
        $this->formatter = new MotherFormatter();
    }

    public function index(): string
    {
        $records = $this->mothers
            ->withUser()
            ->orderBy('users.name', 'ASC')
            ->get()->getResultArray();

        $mothers = array_map(fn(array $mother): array => $this->formatter->present($mother, true, true), $records);

        $statusSummary = [
            'normal'   => 0,
            'moderate' => 0,
            'high'     => 0,
        ];

        foreach ($mothers as $mother) {
            $code = $mother['status']['code'] ?? 'normal';

            if (array_key_exists($code, $statusSummary)) {
                $statusSummary[$code]++;
            }
        }

        return view('pakar/dashboard', [
            'mothers'       => $mothers,
            'statusSummary' => $statusSummary,
        ]);
    }
}
