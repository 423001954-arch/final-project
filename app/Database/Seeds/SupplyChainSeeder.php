<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SupplyChainSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $facilityId = $this->ensureFacility([
            'code'           => 'WH-MAIN',
            'name'           => 'Main Medical Warehouse',
            'address'        => 'Central supply storage',
            'contact_person' => 'Supply Administrator',
            'contact_phone'  => '0917-000-0000',
            'is_active'      => 1,
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);

        $clinicId = $this->ensureFacility([
            'code'           => 'CLINIC-01',
            'name'           => 'Community Clinic 01',
            'address'        => 'Clinic receiving point',
            'contact_person' => 'Clinic Nurse',
            'contact_phone'  => '0917-111-0000',
            'is_active'      => 1,
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);

        $paracetamolId = $this->ensureMedicine([
            'facility_id'   => $facilityId,
            'sku'           => 'MED-PARA-500',
            'generic_name'  => 'Paracetamol',
            'brand_name'    => 'Clinic Relief',
            'dosage_form'   => 'Tablet',
            'strength'      => '500 mg',
            'unit'          => 'tablet',
            'reorder_level' => 200,
            'status'        => 'active',
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        $orsId = $this->ensureMedicine([
            'facility_id'   => $clinicId,
            'sku'           => 'MED-ORS-SACHET',
            'generic_name'  => 'Oral Rehydration Salts',
            'brand_name'    => 'HydraCare',
            'dosage_form'   => 'Sachet',
            'strength'      => '20.5 g',
            'unit'          => 'sachet',
            'reorder_level' => 100,
            'status'        => 'active',
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        $adminId = (int) ($this->db->table('users')->where('email', 'superadmin@healthchain.local')->get()->getRowArray()['id'] ?? 0);

        $this->ensureBatch($facilityId, $paracetamolId, [
            'medicine_id'          => $paracetamolId,
            'batch_number'         => 'BATCH-PARA-2026-001',
            'supplier'             => 'DOH Regional Supplier',
            'warehouse_location'  => 'Main Warehouse - Rack A1',
            'received_quantity'    => 1000,
            'available_quantity'   => 1000,
            'unit_cost'            => 1.25,
            'manufactured_date'    => date('Y-m-d', strtotime('-2 months')),
            'expiry_date'          => date('Y-m-d', strtotime('+20 days')),
            'received_at'          => $now,
            'status'               => 'available',
            'created_at'           => $now,
            'updated_at'           => $now,
        ], $adminId);

        $this->ensureBatch($facilityId, $paracetamolId, [
            'medicine_id'          => $paracetamolId,
            'batch_number'         => 'BATCH-PARA-2026-002',
            'supplier'             => 'DOH Regional Supplier',
            'warehouse_location'  => 'Main Warehouse - Rack A2',
            'received_quantity'    => 1500,
            'available_quantity'   => 1500,
            'unit_cost'            => 1.20,
            'manufactured_date'    => date('Y-m-d', strtotime('-1 month')),
            'expiry_date'          => date('Y-m-d', strtotime('+180 days')),
            'received_at'          => $now,
            'status'               => 'available',
            'created_at'           => $now,
            'updated_at'           => $now,
        ], $adminId);

        $this->ensureBatch($clinicId, $orsId, [
            'medicine_id'          => $orsId,
            'batch_number'         => 'BATCH-ORS-2026-001',
            'supplier'             => 'Health Emergency Supplier',
            'warehouse_location'  => 'Clinic Stock Room - Shelf B',
            'received_quantity'    => 400,
            'available_quantity'   => 400,
            'unit_cost'            => 8.50,
            'manufactured_date'    => date('Y-m-d', strtotime('-3 months')),
            'expiry_date'          => date('Y-m-d', strtotime('+45 days')),
            'received_at'          => $now,
            'status'               => 'available',
            'created_at'           => $now,
            'updated_at'           => $now,
        ], $adminId);
    }

    private function ensureFacility(array $data): int
    {
        $existing = $this->db->table('healthcare_facilities')->where('code', $data['code'])->get()->getRowArray();

        if ($existing) {
            return (int) $existing['id'];
        }

        $this->db->table('healthcare_facilities')->insert($data);

        return (int) $this->db->insertID();
    }

    private function ensureMedicine(array $data): int
    {
        $existing = $this->db->table('medicines')
            ->where('facility_id', $data['facility_id'])
            ->where('sku', $data['sku'])
            ->get()
            ->getRowArray();

        if ($existing) {
            return (int) $existing['id'];
        }

        $this->db->table('medicines')->insert($data);

        return (int) $this->db->insertID();
    }

    private function ensureBatch(int $facilityId, int $medicineId, array $data, int $adminId): void
    {
        $existing = $this->db->table('medicine_batches')
            ->where('batch_number', $data['batch_number'])
            ->get()
            ->getRowArray();

        if ($existing) {
            return;
        }

        $this->db->table('medicine_batches')->insert($data);
        $batchId = (int) $this->db->insertID();

        $this->db->table('stock_movements')->insert([
            'facility_id'    => $facilityId,
            'medicine_id'    => $medicineId,
            'batch_id'       => $batchId,
            'movement_type'  => 'receive',
            'quantity'       => $data['received_quantity'],
            'reference_type' => 'seed_receipt',
            'reference_id'   => $data['batch_number'],
            'remarks'        => 'Seeded sample stock receipt.',
            'performed_by'   => $adminId > 0 ? $adminId : null,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
    }
}
