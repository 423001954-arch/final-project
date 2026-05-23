<?php

namespace App\Models;

use CodeIgniter\Model;

class MedicineModel extends Model
{
    protected $table            = 'medicines';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $useTimestamps    = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'facility_id',
        'sku',
        'generic_name',
        'brand_name',
        'dosage_form',
        'strength',
        'unit',
        'reorder_level',
        'status',
    ];

    protected $validationRules = [
        'facility_id'   => 'required|is_natural_no_zero',
        'sku'           => 'required|alpha_dash|min_length[2]|max_length[60]',
        'generic_name'  => 'required|min_length[2]|max_length[160]',
        'brand_name'    => 'permit_empty|max_length[160]',
        'dosage_form'   => 'required|max_length[80]',
        'strength'      => 'required|max_length[80]',
        'unit'          => 'required|max_length[40]',
        'reorder_level' => 'permit_empty|is_natural',
        'status'        => 'permit_empty|in_list[active,inactive]',
    ];

    public function listForFacility(int $facilityId, ?string $search, int $perPage): array
    {
        $builder = $this->where('facility_id', $facilityId)
            ->orderBy('generic_name', 'ASC');

        if ($search !== null && $search !== '') {
            $builder->groupStart()
                ->like('sku', $search)
                ->orLike('generic_name', $search)
                ->orLike('brand_name', $search)
                ->groupEnd();
        }

        return $builder->paginate($perPage);
    }

    public function findForFacility(int $id, int $facilityId): ?array
    {
        return $this->where('id', $id)
            ->where('facility_id', $facilityId)
            ->first();
    }

    public function currentStockForFacility(int $facilityId, ?string $search, int $perPage): array
    {
        $builder = $this->select('id, facility_id, sku, generic_name, brand_name, dosage_form, strength, unit, reorder_level')
            ->where('facility_id', $facilityId)
            ->where('status', 'active');

        if ($search !== null && $search !== '') {
            $builder->groupStart()
                ->like('sku', $search)
                ->orLike('generic_name', $search)
                ->orLike('brand_name', $search)
                ->groupEnd();
        }

        $rows = $builder->orderBy('generic_name', 'ASC')->paginate($perPage);
        $batchModel = new MedicineBatchModel();

        foreach ($rows as &$row) {
            $stock = $batchModel->selectSum('available_quantity')
                ->where('medicine_id', (int) $row['id'])
                ->where('status', 'available')
                ->where('expiry_date >', date('Y-m-d'))
                ->first();

            $row['current_stock'] = (int) ($stock['available_quantity'] ?? 0);
        }

        return $rows;
    }
}
