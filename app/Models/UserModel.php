<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'name',
        'email',
        'password_hash',
        'role',
    ];
    protected $useTimestamps = true;

    public function withMothers(): BaseBuilder
    {
        return $this->builder()
            ->select('users.*, mothers.id as mother_id')
            ->join('mothers', 'mothers.user_id = users.id');
    }
}
