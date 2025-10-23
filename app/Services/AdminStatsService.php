<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class AdminStatsService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    /**
     * Build overview statistics used on the admin dashboard.
     */
    public function getOverviewStats(): array
    {
        $userCount = $this->countFromTable('users');
        $ruleCount = $this->countFromTable('rules');
        $inferenceCount = $this->countFromTable('inference_results');

        return [
            [
                'badgeText'   => 'Jumlah User',
                'title'       => $this->formatNumber($userCount),
                'value'       => $this->formatNumber($userCount),
                'subtitle'    => 'Pengguna terdaftar',
                'description' => 'Total pengguna yang telah terdaftar.',
                'accent'      => 'bg-blue-500',
            ],
            [
                'badgeText'   => 'Jumlah Rule',
                'title'       => $this->formatNumber($ruleCount),
                'value'       => $this->formatNumber($ruleCount),
                'subtitle'    => 'Rule aktif',
                'description' => 'Rule aktif dalam basis pengetahuan.',
                'accent'      => 'bg-emerald-500',
            ],
            [
                'badgeText'   => 'Total Inferensi',
                'title'       => $this->formatNumber($inferenceCount),
                'value'       => $this->formatNumber($inferenceCount),
                'subtitle'    => 'Sesi inferensi',
                'description' => 'Sesi inferensi yang telah dijalankan.',
                'accent'      => 'bg-purple-500',
            ],
        ];
    }

    private function countFromTable(string $table): int
    {
        return (int) $this->db->table($table)->countAllResults();
    }

    private function formatNumber(int $value): string
    {
        return number_format($value, 0, ',', '.');
    }
}
