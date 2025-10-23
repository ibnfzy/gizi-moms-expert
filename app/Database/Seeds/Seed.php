<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Seed extends Seeder
{
    public function run()
    {
        $this->call('User');
        $this->call('Mother');
        $this->call('Rules');
        $this->call('PakarPanel');
    }
}
