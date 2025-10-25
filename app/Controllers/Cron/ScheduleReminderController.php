<?php

namespace App\Controllers\Cron;

use App\Controllers\BaseController;
use App\Services\ScheduleReminderService;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class ScheduleReminderController extends BaseController
{
    private ScheduleReminderService $service;

    public function __construct()
    {
        $this->service = new ScheduleReminderService();
    }

    public function trigger(): ResponseInterface
    {
        $secret = env('CRON_SECRET');

        if (is_string($secret) && $secret !== '') {
            $provided = (string) $this->request->getGet('secret');

            if ($provided === '' || ! hash_equals($secret, $provided)) {
                return errorResponse('Unauthorized cron access.', ResponseInterface::HTTP_FORBIDDEN);
            }
        }

        try {
            $result = $this->service->run();
        } catch (Throwable $exception) {
            log_message('error', 'Schedule reminder cron failed: {message}', ['message' => $exception->getMessage()]);

            return errorResponse('Failed to trigger schedule reminders.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        return successResponse($result, 'Schedule reminder cron executed successfully.');
    }
}
