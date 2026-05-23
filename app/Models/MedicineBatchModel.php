<?php

namespace App\Models;

use CodeIgniter\Model;

class MedicineBatchModel extends Model
{
    protected $table            = 'medicine_batches';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'medicine_id',
        'batch_number',
        'supplier',
        'warehouse_location',
        'received_quantity',
        'available_quantity',
        'unit_cost',
        'manufactured_date',
        'expiry_date',
        'received_at',
        'status',
    ];

    protected $validationRules = [
        'medicine_id'          => 'required|is_natural_no_zero',
        'batch_number'        => 'required|alpha_numeric_punct|min_length[2]|max_length[80]',
        'supplier'            => 'permit_empty|max_length[160]',
        'warehouse_location'  => 'required|min_length[2]|max_length[120]',
        'received_quantity'   => 'required|is_natural_no_zero',
        'available_quantity'  => 'required|is_natural',
        'unit_cost'           => 'permit_empty|decimal',
        'manufactured_date'   => 'permit_empty|valid_date[Y-m-d]',
        'expiry_date'         => 'required|valid_date[Y-m-d]',
        'received_at'         => 'required|valid_date[Y-m-d H:i:s]',
        'status'              => 'permit_empty|in_list[available,depleted,quarantined,recalled,expired]',
    ];

    public function availableFefoBatches(int $medicineId): array
    {
        return $this->where('medicine_id', $medicineId)
            ->where('available_quantity >', 0)
            ->where('expiry_date >', date('Y-m-d'))
            ->where('status', 'available')
            ->orderBy('expiry_date', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function expiringSoon(int $facilityId, int $daysAhead, int $limit = 100): array
    {
        $until = date('Y-m-d', strtotime('+' . $daysAhead . ' days'));

        return $this->db->table($this->table . ' b')
            ->select('b.*, m.sku, m.generic_name, m.brand_name, m.facility_id')
            ->join('medicines m', 'm.id = b.medicine_id')
            ->where('m.facility_id', $facilityId)
            ->where('b.available_quantity >', 0)
            ->where('b.expiry_date >', date('Y-m-d'))
            ->where('b.expiry_date <=', $until)
            ->where('b.status', 'available')
            ->orderBy('b.expiry_date', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function expiredActiveBatches(int $limit = 100): array
    {
        return $this->db->table($this->table . ' b')
            ->select('b.*, m.sku, m.generic_name, m.facility_id, f.name AS facility_name')
            ->join('medicines m', 'm.id = b.medicine_id')
            ->join('healthcare_facilities f', 'f.id = m.facility_id', 'left')
            ->where('b.available_quantity >', 0)
            ->where('b.expiry_date <=', date('Y-m-d'))
            ->where('b.status', 'available')
            ->orderBy('b.expiry_date', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function flagExpiredActiveBatches(): int
    {
        $builder = $this->builder();
        $builder->where('available_quantity >', 0)
            ->where('expiry_date <=', date('Y-m-d'))
            ->where('status', 'available')
            ->set(['status' => 'expired'])
            ->update();

        return $this->db->affectedRows();
    }

    public function listForMedicine(int $medicineId, int $perPage): array
    {
        return $this->where('medicine_id', $medicineId)
            ->orderBy('expiry_date', 'ASC')
            ->paginate($perPage);
    }
}
