<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SupplyChainUserSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(UserSeeder::class);
    }
}
