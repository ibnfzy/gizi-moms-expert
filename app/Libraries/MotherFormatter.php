<?php

namespace App\Libraries;

use App\Models\InferenceResultModel;
use CodeIgniter\I18n\Time;

class MotherFormatter
{
    private InferenceResultModel $inferenceResults;

    /**
     * @var array<string, array{label: string, badge: string, tone: string}>
     */
    private array $statusMap = [
        'normal' => [
            'label' => 'Normal',
            'badge' => 'bg-emerald-100 text-emerald-700',
            'tone'  => 'emerald',
        ],
        'moderate' => [
            'label' => 'Moderate',
            'badge' => 'bg-amber-100 text-amber-700',
            'tone'  => 'amber',
        ],
        'high' => [
            'label' => 'High',
            'badge' => 'bg-rose-100 text-rose-700',
            'tone'  => 'rose',
        ],
    ];

    public function __construct(?InferenceResultModel $inferenceResults = null)
    {
        $this->inferenceResults = $inferenceResults ?? new InferenceResultModel();
    }

    /**
     * @param array<string, mixed> $mother
     */
    public function present(array $mother, bool $includeProfile = false, bool $includeInferenceDetails = false): array
    {
        $formatted = [
            'id'            => isset($mother['id']) ? (int) $mother['id'] : null,
            'name'          => $mother['user_name'] ?? $mother['name'] ?? '-',
            'email'         => $mother['user_email'] ?? null,
            'umur'          => isset($mother['umur']) ? (int) $mother['umur'] : null,
            'usia_bayi_bln' => isset($mother['usia_bayi_bln']) ? (int) $mother['usia_bayi_bln'] : null,
        ];

        if ($includeProfile) {
            $formatted['profile'] = $this->buildProfile($mother);
        }

        $inference = null;
        if (! empty($mother['id'])) {
            $inference = $this->latestInference((int) $mother['id']);
        }

        $formatted['status'] = $this->buildStatus($inference);
        $formatted['latest_inference'] = $this->formatInference($inference, $includeInferenceDetails);

        return $formatted;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildProfile(array $mother): array
    {
        return [
            'bb'             => isset($mother['bb']) ? (float) $mother['bb'] : null,
            'tb'             => isset($mother['tb']) ? (float) $mother['tb'] : null,
            'umur'           => isset($mother['umur']) ? (int) $mother['umur'] : null,
            'usia_bayi_bln'  => isset($mother['usia_bayi_bln']) ? (int) $mother['usia_bayi_bln'] : null,
            'laktasi_tipe'   => $mother['laktasi_tipe'] ?? null,
            'aktivitas'      => $mother['aktivitas'] ?? null,
            'alergi'         => $this->decodeList($mother['alergi_json'] ?? null),
            'preferensi'     => $this->decodeList($mother['preferensi_json'] ?? null),
            'riwayat'        => $this->decodeList($mother['riwayat_json'] ?? null),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function latestInference(int $motherId): ?array
    {
        $record = $this->inferenceResults
            ->where('mother_id', $motherId)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->first();

        return is_array($record) ? $record : null;
    }

    /**
     * @param array<string, mixed>|null $inference
     *
     * @return array{code: string, label: string, badge: string, tone: string, source: string}
     */
    private function buildStatus(?array $inference): array
    {
        $output = $inference ? $this->decodeArray($inference['output_json'] ?? null) : null;
        $facts  = $inference ? $this->decodeArray($inference['facts_json'] ?? null) : null;

        $detected = $this->detectStatus($output, $facts);

        if ($detected === null) {
            $detected = 'normal';
            $source   = 'fallback';
        } else {
            $source = 'inference';
        }

        $status = $this->statusMap[$detected] ?? $this->statusMap['normal'];

        return [
            'code'   => $detected,
            'label'  => $status['label'],
            'badge'  => $status['badge'],
            'tone'   => $status['tone'],
            'source' => $source,
        ];
    }

    /**
     * @param array<string, mixed>|null $inference
     *
     * @return array<string, mixed>|null
     */
    private function formatInference(?array $inference, bool $includeDetails): ?array
    {
        if ($inference === null) {
            return null;
        }

        $facts       = $this->decodeArray($inference['facts_json'] ?? null);
        $firedRules  = $this->decodeList($inference['fired_rules_json'] ?? null);
        $output      = $this->decodeArray($inference['output_json'] ?? null);
        $status      = $this->buildStatus($inference);
        $timestamp   = $this->formatTimestamp($inference['created_at'] ?? null);

        $formatted = [
            'id'              => isset($inference['id']) ? (int) $inference['id'] : null,
            'version'         => $inference['version'] ?? null,
            'status'          => $status,
            'created_at'      => $timestamp['iso'],
            'recommendations' => $this->formatRecommendations($output),
        ];

        if ($includeDetails) {
            $formatted['created_at_human'] = $timestamp['human'];
            $formatted['facts']            = $facts;
            $formatted['fired_rules']      = $firedRules;
            $formatted['output']           = $output;
        }

        return $formatted;
    }

    /**
     * @param array<int|string, mixed>|null ...$sources
     */
    private function detectStatus(?array ...$sources): ?string
    {
        foreach ($sources as $source) {
            if ($source === null) {
                continue;
            }

            $status = $this->extractStatus($source);
            if ($status !== null) {
                return $status;
            }
        }

        return null;
    }

    /**
     * @param array<int|string, mixed> $data
     */
    private function extractStatus(array $data): ?string
    {
        $keys = ['status', 'risk', 'risk_level', 'severity', 'level'];

        foreach ($keys as $key) {
            if (isset($data[$key]) && is_string($data[$key])) {
                $normalized = $this->normalizeStatus($data[$key]);
                if ($normalized !== null) {
                    return $normalized;
                }
            }
        }

        foreach ($data as $value) {
            if (is_array($value)) {
                $nested = $this->extractStatus($value);
                if ($nested !== null) {
                    return $nested;
                }
            }
        }

        return null;
    }

    private function normalizeStatus(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = strtolower(trim($value));

        return match ($normalized) {
            'normal', 'rendah', 'low', 'aman' => 'normal',
            'moderate', 'sedang', 'medium'    => 'moderate',
            'high', 'tinggi', 'severe', 'bahaya' => 'high',
            default => null,
        };
    }

    /**
     * @return array<int, string>
     */
    private function formatRecommendations(?array $output): array
    {
        if ($output === null) {
            return [];
        }

        $items = $this->flattenArray($output);
        $recommendations = [];

        foreach ($items as $item) {
            if (is_string($item)) {
                $recommendations[] = $item;
            } elseif (is_array($item)) {
                if (isset($item['recommendation']) && is_string($item['recommendation'])) {
                    $recommendations[] = $item['recommendation'];
                } else {
                    $recommendations[] = json_encode($item, JSON_UNESCAPED_UNICODE);
                }
            } elseif ($item !== null) {
                $recommendations[] = (string) $item;
            }
        }

        return array_values(array_unique($recommendations));
    }

    /**
     * @param mixed $data
     *
     * @return list<mixed>
     */
    private function flattenArray($data): array
    {
        if (! is_array($data)) {
            return [$data];
        }

        $flattened = [];

        $isAssoc = array_keys($data) !== range(0, count($data) - 1);
        if (! $isAssoc) {
            foreach ($data as $value) {
                if (is_array($value)) {
                    $flattened = array_merge($flattened, $this->flattenArray($value));
                } else {
                    $flattened[] = $value;
                }
            }
        } else {
            foreach ($data as $value) {
                $flattened[] = $value;
            }
        }

        return $flattened;
    }

    /**
     * @return array<int|string, mixed>|null
     */
    private function decodeArray(?string $json): ?array
    {
        if ($json === null || $json === '') {
            return null;
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @return list<mixed>
     */
    private function decodeList(?string $json): array
    {
        $decoded = $this->decodeArray($json);

        if ($decoded === null) {
            return [];
        }

        return array_values($decoded);
    }

    /**
     * @return array{iso: string|null, human: string|null}
     */
    private function formatTimestamp(?string $timestamp): array
    {
        if (empty($timestamp)) {
            return ['iso' => null, 'human' => null];
        }

        try {
            $time = Time::parse($timestamp);
        } catch (\Throwable $exception) {
            return ['iso' => $timestamp, 'human' => null];
        }

        return [
            'iso'   => $time->toDateTimeString(),
            'human' => $time->humanize(),
        ];
    }
}
