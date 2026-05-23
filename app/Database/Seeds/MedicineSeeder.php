<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MedicineSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name'         => 'Paracetamol',
                'generic_name' => 'Paracetamol',
                'sku'          => 'PARA-500-01',
                'facility_id'  => 1
            ],
            [
                'name'         => 'Oral Rehydration Salts',
                'generic_name' => 'ORS',
                'sku'          => 'ORS-100-01',
                'facility_id'  => 1
            ],
        ];

        $this->db->table('medicines')->insertBatch($data);
    }
}