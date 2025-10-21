<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

class MotherModel extends Model
{
    protected $table = 'mothers';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'user_id',
        'bb',
        'tb',
        'umur',
        'usia_bayi_bln',
        'laktasi_tipe',
        'aktivitas',
        'alergi_json',
        'preferensi_json',
        'riwayat_json',
    ];
    protected $useTimestamps = true;

    public function withUser(): BaseBuilder
    {
        return $this->builder()
            ->select('mothers.*, users.name as user_name, users.email as user_email')
            ->join('users', 'users.id = mothers.user_id');
    }

    public function withConsultations(): BaseBuilder
    {
        return $this->builder()
            ->select('mothers.*, consultations.id as consultation_id')
            ->join('consultations', 'consultations.mother_id = mothers.id');
    }
}
