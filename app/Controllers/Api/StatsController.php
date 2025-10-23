<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\AdminStatsService;
use CodeIgniter\HTTP\ResponseInterface;

class StatsController extends BaseController
{
    private AdminStatsService $statsService;

    public function __construct()
    {
        $this->statsService = new AdminStatsService();
    }

    public function index(): ResponseInterface
    {
        $stats = $this->statsService->getOverviewStats();

        return $this->response->setJSON([
            'status' => true,
            'data'   => $stats,
        ]);
    }
}
