<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

class MessageModel extends Model
{
    protected $table = 'messages';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'consultation_id',
        'sender_role',
        'text',
        'created_at',
    ];
    protected $useTimestamps = false;

    public function withConsultation(): BaseBuilder
    {
        return $this->builder()
            ->select('messages.*, consultations.status, consultations.pakar_id, consultations.mother_id')
            ->join('consultations', 'consultations.id = messages.consultation_id');
    }

    public function withParticipants(): BaseBuilder
    {
        return $this->builder()
            ->select(
                'messages.*, consultations.status, mother_users.name as mother_name, pakar_users.name as pakar_name'
            )
            ->join('consultations', 'consultations.id = messages.consultation_id')
            ->join('mothers', 'mothers.id = consultations.mother_id')
            ->join('users as mother_users', 'mother_users.id = mothers.user_id')
            ->join('users as pakar_users', 'pakar_users.id = consultations.pakar_id', 'left');
    }
}
