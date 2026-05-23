<?php

namespace App\Services;

use App\Models\MedicineBatchModel;
use App\Models\MedicineModel;
use App\Models\StockMovementModel;
use CodeIgniter\Database\BaseConnection;
use Config\Database;
use RuntimeException;

class FefoStockAllocator
{
    private BaseConnection $db;
    private MedicineBatchModel $batchModel;
    private MedicineModel $medicineModel;
    private StockMovementModel $movementModel;

    public function __construct(
        ?BaseConnection $db = null,
        ?MedicineBatchModel $batchModel = null,
        ?MedicineModel $medicineModel = null,
        ?StockMovementModel $movementModel = null
    ) {
        $this->db = $db ?? Database::connect();
        $this->batchModel = $batchModel ?? new MedicineBatchModel();
        $this->medicineModel = $medicineModel ?? new MedicineModel();
        $this->movementModel = $movementModel ?? new StockMovementModel();
    }

    /**
     * Computes FEFO picking without mutating data.
     *
     * This pure allocation shape is intentionally easy to assert in PHPUnit:
     * expired, quarantined, recalled, and depleted batches are excluded; the
     * remaining candidates are sorted by expiry_date ASC as required by FEFO.
     */
    public function preview(int $medicineId, int $quantity): array
    {
        if ($quantity < 1) {
            throw new RuntimeException('Requested quantity must be greater than zero.');
        }

        $remaining = $quantity;
        $allocations = [];

        foreach ($this->batchModel->availableFefoBatches($medicineId) as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $picked = min((int) $batch['available_quantity'], $remaining);
            $remaining -= $picked;

            $allocations[] = [
                'batch_id'       => (int) $batch['id'],
                'batch_number'   => $batch['batch_number'],
                'expiry_date'    => $batch['expiry_date'],
                'picked_quantity' => $picked,
                'remaining_after_batch' => $remaining,
            ];
        }

        return [
            'medicine_id'        => $medicineId,
            'requested_quantity' => $quantity,
            'allocated_quantity' => $quantity - $remaining,
            'unfulfilled_quantity' => $remaining,
            'is_fulfilled'       => $remaining === 0,
            'allocations'        => $allocations,
        ];
    }

    public function commitConsumption(
        int $facilityId,
        int $medicineId,
        int $quantity,
        int $performedBy,
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?string $remarks = null
    ): array {
        $medicine = $this->medicineModel->findForFacility($medicineId, $facilityId);

        if ($medicine === null) {
            throw new RuntimeException('Medicine was not found for this facility.');
        }

        $plan = $this->preview($medicineId, $quantity);

        if (! $plan['is_fulfilled']) {
            throw new RuntimeException('Insufficient non-expired stock for FEFO allocation.');
        }

        $this->db->transBegin();

        foreach ($plan['allocations'] as $allocation) {
            $batch = $this->batchModel->find($allocation['batch_id']);

            if ($batch === null) {
                $this->db->transRollback();
                throw new RuntimeException('A selected batch no longer exists.');
            }

            $newQuantity = (int) $batch['available_quantity'] - (int) $allocation['picked_quantity'];

            if ($newQuantity < 0) {
                $this->db->transRollback();
                throw new RuntimeException('A selected batch no longer has enough stock.');
            }

            $this->batchModel->update($allocation['batch_id'], [
                'available_quantity' => $newQuantity,
                'status'             => $newQuantity === 0 ? 'depleted' : $batch['status'],
            ]);

            $this->movementModel->insert([
                'facility_id'    => $facilityId,
                'medicine_id'    => $medicineId,
                'batch_id'       => $allocation['batch_id'],
                'movement_type'  => 'consume',
                'quantity'       => $allocation['picked_quantity'],
                'reference_type' => $referenceType,
                'reference_id'   => $referenceId,
                'remarks'        => $remarks,
                'performed_by'   => $performedBy,
                'created_at'     => date('Y-m-d H:i:s'),
            ]);
        }

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            throw new RuntimeException('Unable to persist FEFO allocation.');
        }

        $this->db->transCommit();

        return $plan;
    }
}
