<?php

namespace App\Controllers;

use App\Models\RuleModel;
use CodeIgniter\I18n\Time;

class AdminDashboardController extends BaseController
{
    private RuleModel $ruleModel;

    public function __construct()
    {
        $this->ruleModel = new RuleModel();
    }

    public function index(): string
    {
        $stats = $this->buildStats();
        $rules = $this->getLatestRules();

        $statusClasses = [
            'Aktif' => 'bg-blue-50 text-blue-700',
            'Draft' => 'bg-yellow-50 text-yellow-700',
            'Ditinjau' => 'bg-purple-50 text-purple-700',
        ];

        return view('admin/dashboard', [
            'stats' => $stats,
            'rules' => $rules,
            'statusClasses' => $statusClasses,
        ]);
    }

    private function buildStats(): array
    {
        $db = db_connect();

        $userCount = $db->table('users')->countAllResults();
        $ruleCount = $db->table('rules')->countAllResults();
        $inferenceCount = $db->table('inference_results')->countAllResults();

        return [
            [
                'badgeText' => 'Jumlah User',
                'title' => number_format($userCount, 0, ',', '.'),
                'description' => 'Total pengguna yang telah terdaftar.',
            ],
            [
                'badgeText' => 'Jumlah Rule',
                'title' => number_format($ruleCount, 0, ',', '.'),
                'description' => 'Rule aktif dalam basis pengetahuan.',
            ],
            [
                'badgeText' => 'Total Inferensi',
                'title' => number_format($inferenceCount, 0, ',', '.'),
                'description' => 'Sesi inferensi yang telah dijalankan.',
            ],
        ];
    }

    private function getLatestRules(): array
    {
        Time::setLocale('id_ID');

        $latestRules = $this->ruleModel
            ->orderBy('effective_from', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->findAll(5);

        return array_map(static function (array $rule): array {
            $details = json_decode($rule['json_rule'] ?? '{}', true) ?? [];
            $status = $details['status'] ?? ($rule['is_active'] ? 'Aktif' : 'Tidak Aktif');
            $category = $details['category'] ?? 'Tidak diketahui';

            $effectiveDate = '-';
            if (! empty($rule['effective_from'])) {
                $date = Time::createFromFormat('Y-m-d', $rule['effective_from']);
                if ($date !== false) {
                    $effectiveDate = $date->toLocalizedString('d MMMM yyyy');
                }
            }

            return [
                'id' => sprintf('R-%03d', (int) ($rule['id'] ?? 0)),
                'name' => $rule['name'] ?? '-',
                'category' => $category,
                'date' => $effectiveDate,
                'status' => $status,
            ];
        }, $latestRules);
    }
}
