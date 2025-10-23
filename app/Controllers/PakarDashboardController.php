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
        $data = $this->loadDashboardData();

        return view('pakar/dashboard', $data);
    }

    public function data(): string
    {
        $data = $this->loadDashboardData();

        return view('pakar/partials/dashboard_data', $data);
    }

    public function motherDetail(int $motherId): string
    {
        $record = $this->mothers
            ->withUser()
            ->where('mothers.id', $motherId)
            ->get()->getRowArray();

        if (! is_array($record)) {
            return '<div class="sr-only">Data ibu tidak ditemukan.</div>';
        }

        $mother = $this->formatter->present($record, true, true);

        return view('pakar/partials/mother_detail', [
            'mother' => $mother,
        ]);
    }

    public function clearDetail(): string
    {
        return '';
    }

    /**
     * @return array{mothers: array<int, array<string, mixed>>, statusSummary: array<string, int>}
     */
    private function loadDashboardData(): array
    {
        $records = $this->mothers
            ->withUser()
            ->orderBy('users.name', 'ASC')
            ->get()->getResultArray();

        $mothers = array_map(
            fn (array $mother): array => $this->formatter->present($mother, true, true),
            $records
        );

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

        return [
            'mothers'       => $mothers,
            'statusSummary' => $statusSummary,
        ];
    }
}
