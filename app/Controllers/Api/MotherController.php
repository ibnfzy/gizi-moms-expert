<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\MotherFormatter;
use App\Models\MotherModel;
use CodeIgniter\HTTP\ResponseInterface;

class MotherController extends BaseController
{
    private MotherModel $mothers;
    private MotherFormatter $formatter;

    public function __construct()
    {
        helper('auth');

        $this->mothers   = new MotherModel();
        $this->formatter = new MotherFormatter();
    }

    public function index()
    {
        $records = $this->mothers
            ->withUser()
            ->orderBy('users.name', 'ASC')
            ->findAll();

        $payload = array_map(fn (array $mother): array => $this->formatter->present($mother, false, false), $records);

        return $this->response->setJSON([
            'status' => true,
            'data'   => $payload,
        ]);
    }

    public function show(int $id)
    {
        $mother = $this->mothers
            ->withUser()
            ->where('mothers.id', $id)
            ->first();

        if (! is_array($mother)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Mother data not found.',
                ]);
        }

        $payload = $this->formatter->present($mother, true, true);

        return $this->response->setJSON([
            'status' => true,
            'data'   => $payload,
        ]);
    }
}
