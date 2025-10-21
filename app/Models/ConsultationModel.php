<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

class ConsultationModel extends Model
{
    protected $table = 'consultations';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'mother_id',
        'pakar_id',
        'status',
        'notes',
    ];
    protected $useTimestamps = true;

    public function withMother(): BaseBuilder
    {
        return $this->builder()
            ->select('consultations.*, mothers.user_id as mother_user_id')
            ->join('mothers', 'mothers.id = consultations.mother_id');
    }

    public function withPakar(): BaseBuilder
    {
        return $this->builder()
            ->select('consultations.*, pakar.name as pakar_name')
            ->join('users as pakar', 'pakar.id = consultations.pakar_id');
    }

    public function withMessages(): BaseBuilder
    {
        return $this->builder()
            ->select('consultations.*, messages.id as message_id')
            ->join('messages', 'messages.consultation_id = consultations.id');
    }
}
