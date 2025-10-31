<?php

namespace App\Controllers;

use App\Models\RuleModel;
use CodeIgniter\I18n\Time;

class PakarRulesController extends BaseController
{
    private RuleModel $rules;

    public function __construct()
    {
        $this->rules = new RuleModel();
    }

    public function index(): string
    {
        $records = $this->rules
            ->select([
                'id',
                'name',
                'version',
                'effective_from',
                'is_active',
                'komentar_pakar',
                'updated_at',
            ])
            ->orderBy('is_active', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();

        $rules = array_map(fn (array $record): array => $this->presentRule($record), $records);

        return view('pakar/rules', [
            'rules'                   => $rules,
            'commentEndpointTemplate' => site_url('api/rules/__id__/comment'),
        ]);
    }

    /**
     * @param array<string, mixed> $record
     *
     * @return array<string, mixed>
     */
    private function presentRule(array $record): array
    {
        $effectiveFrom = $this->formatDate($record['effective_from'] ?? null);
        $updatedAt     = $this->formatDateTime($record['updated_at'] ?? null);

        return [
            'id'               => isset($record['id']) ? (int) $record['id'] : null,
            'name'             => $record['name'] ?? 'Tanpa Nama',
            'version'          => $record['version'] ?? '-',
            'isActive'         => isset($record['is_active']) ? (bool) $record['is_active'] : false,
            'effectiveFrom'    => $effectiveFrom,
            'komentarPakar'    => $record['komentar_pakar'] ?? '',
            'updatedAt'        => $updatedAt,
        ];
    }

    private function formatDate($value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Time::parse($value)->toLocalizedString('d MMMM yyyy');
        } catch (\Throwable $exception) {
            log_message('warning', 'Gagal mengurai tanggal efektif rule: ' . $exception->getMessage());
        }

        return null;
    }

    private function formatDateTime($value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Time::parse($value)->toLocalizedString('d MMMM yyyy HH.mm');
        } catch (\Throwable $exception) {
            log_message('warning', 'Gagal mengurai waktu pembaruan rule: ' . $exception->getMessage());
        }

        return null;
    }
}
