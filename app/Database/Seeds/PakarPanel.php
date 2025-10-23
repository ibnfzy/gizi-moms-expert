<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PakarPanel extends Seeder
{
    public function run()
    {
        $pakar = $this->db->table('users')
            ->select('id, email')
            ->where('email', 'pakar@example.com')
            ->get()
            ->getRowArray();

        if (! $pakar) {
            return;
        }

        $motherRecords = $this->db->table('mothers')
            ->select('mothers.id, users.email, users.name')
            ->join('users', 'users.id = mothers.user_id')
            ->get()
            ->getResultArray();

        if ($motherRecords === []) {
            return;
        }

        $mothers = [];
        foreach ($motherRecords as $record) {
            $mothers[$record['email']] = [
                'id'   => (int) $record['id'],
                'name' => $record['name'],
            ];
        }

        $this->seedInferenceResults($mothers);
        $this->seedConsultations($mothers, (int) $pakar['id']);
    }

    /**
     * @param array<string, array{id: int, name: string|null}> $mothers
     */
    private function seedInferenceResults(array $mothers): void
    {
        $now = date('Y-m-d H:i:s');

        $inferences = [
            'sari@example.com' => [
                'created_at'  => date('Y-m-d H:i:s', strtotime('-2 days')),
                'version'     => '1.2.0',
                'facts'       => [
                    'imt'        => 22.3,
                    'energi'     => 'cukup',
                    'risk_level' => 'moderate',
                ],
                'rules'       => [
                    'R-12: Kebutuhan zat besi meningkat',
                    'R-18: Asupan protein perlu ditambah',
                ],
                'output'      => [
                    'status'          => 'moderate',
                    'recommendations' => [
                        'Tambahkan sumber zat besi seperti bayam dan kacang merah minimal dua kali sehari.',
                        'Kombinasikan dengan vitamin C agar penyerapan zat besi optimal.',
                    ],
                ],
            ],
            'dewi@example.com' => [
                'created_at'  => date('Y-m-d H:i:s', strtotime('-3 days')),
                'version'     => '1.2.0',
                'facts'       => [
                    'imt'    => 24.8,
                    'tidur'  => 'cukup',
                    'status' => 'normal',
                ],
                'rules'       => [
                    'R-04: Status gizi seimbang',
                ],
                'output'      => [
                    'status'          => 'normal',
                    'recommendations' => [
                        'Pertahankan pola makan seimbang dengan menu tinggi protein dan serat.',
                        'Tetap hidrasi minimal delapan gelas air per hari.',
                    ],
                ],
            ],
            'ayu@example.com' => [
                'created_at'  => date('Y-m-d H:i:s', strtotime('-1 day')),
                'version'     => '1.2.0',
                'facts'       => [
                    'imt'        => 20.4,
                    'risk_level' => 'normal',
                    'aktivitas'  => 'ringan',
                ],
                'rules'       => [
                    'R-02: Monitoring berat badan ideal',
                ],
                'output'      => [
                    'status'          => 'normal',
                    'recommendations' => [
                        'Pastikan konsumsi kalori minimal 500 kkal tambahan per hari.',
                        'Variasikan sumber protein dari ikan laut dan telur tiga kali seminggu.',
                    ],
                ],
            ],
            'rina@example.com' => [
                'created_at'  => date('Y-m-d H:i:s', strtotime('-5 hours')),
                'version'     => '1.2.0',
                'facts'       => [
                    'imt'        => 25.5,
                    'risk_level' => 'high',
                    'riwayat'    => ['gastritis'],
                ],
                'rules'       => [
                    'R-21: Waspada risiko anemia',
                    'R-33: Prioritaskan makanan rendah asam',
                ],
                'output'      => [
                    'status'          => 'high',
                    'recommendations' => [
                        'Batasi konsumsi makanan pedas dan asam untuk menghindari iritasi lambung.',
                        'Tingkatkan asupan protein hewani rendah lemak dan suplemen zat besi sesuai anjuran.',
                        'Jadwalkan konsultasi lanjutan dalam satu minggu.',
                    ],
                ],
            ],
            'maya@example.com' => [
                'created_at'  => $now,
                'version'     => '1.2.0',
                'facts'       => [
                    'imt'        => 21.5,
                    'risk_level' => 'moderate',
                    'tiroid'     => 'aktif',
                ],
                'rules'       => [
                    'R-25: Pantau kebutuhan iodium untuk ibu dengan tiroid',
                ],
                'output'      => [
                    'status'          => 'moderate',
                    'recommendations' => [
                        'Perbanyak makanan laut rendah merkuri dua kali seminggu.',
                        'Konsultasikan suplementasi iodium dengan dokter kandungan.',
                    ],
                ],
            ],
        ];

        $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

        foreach ($inferences as $email => $data) {
            if (! isset($mothers[$email])) {
                continue;
            }

            $motherId = $mothers[$email]['id'];

            $existing = $this->db->table('inference_results')
                ->where('mother_id', $motherId)
                ->countAllResults();

            if ($existing > 0) {
                continue;
            }

            $this->db->table('inference_results')->insert([
                'mother_id'         => $motherId,
                'version'           => $data['version'],
                'facts_json'        => json_encode($data['facts'], $jsonFlags),
                'fired_rules_json'  => json_encode($data['rules'], $jsonFlags),
                'output_json'       => json_encode($data['output'], $jsonFlags),
                'created_at'        => $data['created_at'],
            ]);
        }
    }

    /**
     * @param array<string, array{id: int, name: string|null}> $mothers
     */
    private function seedConsultations(array $mothers, int $pakarId): void
    {
        $consultations = [
            [
                'mother_email' => 'sari@example.com',
                'status'       => 'ongoing',
                'notes'        => 'Pantau asupan zat besi dan jadwalkan evaluasi ulang.',
                'created_at'   => date('Y-m-d H:i:s', strtotime('-3 days')),
                'updated_at'   => date('Y-m-d H:i:s', strtotime('-1 day')),
                'messages'     => [
                    [
                        'sender_role' => 'ibu',
                        'text'        => 'Bu, saya sering merasa lelah akhir-akhir ini.',
                        'created_at'  => date('Y-m-d H:i:s', strtotime('-3 days +2 hours')),
                    ],
                    [
                        'sender_role' => 'pakar',
                        'text'        => 'Coba tambahkan konsumsi sayuran hijau setiap hari, ya bu.',
                        'created_at'  => date('Y-m-d H:i:s', strtotime('-3 days +5 hours')),
                    ],
                    [
                        'sender_role' => 'ibu',
                        'text'        => 'Baik, saya akan coba mengikuti sarannya.',
                        'created_at'  => date('Y-m-d H:i:s', strtotime('-2 days')),
                    ],
                ],
            ],
            [
                'mother_email' => 'dewi@example.com',
                'status'       => 'completed',
                'notes'        => 'Sesi konsultasi ditutup, ibu sudah memahami rencana makan.',
                'created_at'   => date('Y-m-d H:i:s', strtotime('-10 days')),
                'updated_at'   => date('Y-m-d H:i:s', strtotime('-6 days')),
                'messages'     => [
                    [
                        'sender_role' => 'pakar',
                        'text'        => 'Bagaimana perkembangan pola makan ibu dalam seminggu ini?',
                        'created_at'  => date('Y-m-d H:i:s', strtotime('-9 days 2 hours')),
                    ],
                    [
                        'sender_role' => 'ibu',
                        'text'        => 'Sudah lebih teratur dan saya merasa lebih bertenaga.',
                        'created_at'  => date('Y-m-d H:i:s', strtotime('-9 days 5 hours')),
                    ],
                    [
                        'sender_role' => 'pakar',
                        'text'        => 'Bagus, lanjutkan dan hubungi kami bila ada keluhan.',
                        'created_at'  => date('Y-m-d H:i:s', strtotime('-8 days')),
                    ],
                ],
            ],
            [
                'mother_email' => 'maya@example.com',
                'status'       => 'pending',
                'notes'        => 'Menunggu konfirmasi jadwal konsultasi pertama.',
                'created_at'   => date('Y-m-d H:i:s', strtotime('-12 hours')),
                'updated_at'   => date('Y-m-d H:i:s', strtotime('-12 hours')),
                'messages'     => [
                    [
                        'sender_role' => 'ibu',
                        'text'        => 'Saya ingin menjadwalkan konsultasi terkait kondisi tiroid.',
                        'created_at'  => date('Y-m-d H:i:s', strtotime('-11 hours')),
                    ],
                ],
            ],
        ];

        foreach ($consultations as $record) {
            $email = $record['mother_email'];

            if (! isset($mothers[$email])) {
                continue;
            }

            $motherId = $mothers[$email]['id'];

            $existing = $this->db->table('consultations')
                ->where('mother_id', $motherId)
                ->where('pakar_id', $pakarId)
                ->countAllResults();

            if ($existing > 0) {
                continue;
            }

            $this->db->table('consultations')->insert([
                'mother_id'  => $motherId,
                'pakar_id'   => $pakarId,
                'status'     => $record['status'],
                'notes'      => $record['notes'],
                'created_at' => $record['created_at'],
                'updated_at' => $record['updated_at'],
            ]);

            $consultationId = (int) $this->db->insertID();

            foreach ($record['messages'] as $message) {
                $this->db->table('messages')->insert([
                    'consultation_id' => $consultationId,
                    'sender_role'     => $message['sender_role'],
                    'text'            => $message['text'],
                    'created_at'      => $message['created_at'],
                ]);
            }
        }
    }
}

