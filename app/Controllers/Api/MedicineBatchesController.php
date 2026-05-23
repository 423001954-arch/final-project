<?php

namespace App\Controllers\Api;

use App\Models\MedicineBatchModel;
use App\Models\MedicineModel;
use App\Models\StockMovementModel;

class MedicineBatchesController extends BaseApiController
{
    private MedicineBatchModel $batchModel;
    private MedicineModel $medicineModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->batchModel = new MedicineBatchModel();
        $this->medicineModel = new MedicineModel();
    }

    public function index()
    {
        if ($blocked = $this->requireSupplyChainAccess()) {
            return $blocked;
        }

        $medicineId = (int) $this->request->getGet('medicine_id');

        if ($medicineId < 1) {
            return $this->badRequest('medicine_id is required.');
        }

        $items = $this->batchModel->listForMedicine($medicineId, $this->requestedPerPage());

        return $this->paginated($items, $this->batchModel->pager, 'Medicine batches retrieved.');
    }

    public function show(int $id)
    {
        if ($blocked = $this->requireSupplyChainAccess()) {
            return $blocked;
        }

        $batch = $this->batchModel->find($id);

        return $batch ? $this->ok($batch, 'Medicine batch retrieved.') : $this->notFound('Batch not found.');
    }

    public function create()
    {
        if ($blocked = $this->requireSupplyChainAccess()) {
            return $blocked;
        }

        $payload = $this->requestPayload();
        $payload['received_at'] = $payload['received_at'] ?? date('Y-m-d H:i:s');
        $payload['available_quantity'] = $payload['available_quantity'] ?? $payload['received_quantity'] ?? null;
        $payload['warehouse_location'] = trim((string) ($payload['warehouse_location'] ?? 'Main Warehouse'));

        $medicine = $this->medicineModel->find((int) ($payload['medicine_id'] ?? 0));

        if ($medicine === null) {
            return $this->badRequest('medicine_id must reference an existing medicine.');
        }

        if ($this->batchModel->where('batch_number', $payload['batch_number'] ?? '')->first()) {
            return $this->conflict('batch_number must be unique.');
        }

        if (! $this->hasValidBatchQuantities($payload)) {
            return $this->badRequest('available_quantity cannot be greater than received_quantity.');
        }

        if (! $this->hasFutureExpiryDate((string) ($payload['expiry_date'] ?? ''))) {
            return $this->badRequest('expiry_date must be a future date.');
        }

        if (! $this->batchModel->insert($payload)) {
            return $this->validationFailed($this->batchModel->errors());
        }

        $batchId = (int) $this->batchModel->getInsertID();

        (new StockMovementModel())->insert([
            'facility_id'    => $medicine['facility_id'],
            'medicine_id'    => $medicine['id'],
            'batch_id'       => $batchId,
            'movement_type'  => 'receive',
            'quantity'       => $payload['received_quantity'],
            'reference_type' => 'batch_receipt',
            'reference_id'   => $payload['batch_number'],
            'remarks'        => 'Initial stock receipt locked to ' . $payload['warehouse_location'] . '.',
            'performed_by'   => $this->apiUser['user_id'] ?? null,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        cache()->delete('expiry_alerts_facility_' . $medicine['facility_id']);
        cache()->deleteMatching('current_stock_facility_' . $medicine['facility_id'] . '_*');

        return $this->created($this->batchModel->find($batchId), 'Medicine batch created.');
    }

    public function update(int $id)
    {
        if ($blocked = $this->requireSupplyChainAccess()) {
            return $blocked;
        }

        if ($this->batchModel->find($id) === null) {
            return $this->notFound('Batch not found.');
        }

        $payload = $this->requestPayload();

        if (! $this->hasValidBatchQuantities($payload)) {
            return $this->badRequest('available_quantity cannot be greater than received_quantity.');
        }

        if (isset($payload['expiry_date']) && ! $this->hasFutureExpiryDate((string) $payload['expiry_date'])) {
            return $this->badRequest('expiry_date must be a future date.');
        }

        if (! $this->batchModel->update($id, $payload)) {
            return $this->validationFailed($this->batchModel->errors());
        }

        $batch = $this->batchModel->find($id);
        $medicine = $this->medicineModel->find((int) $batch['medicine_id']);

        if ($medicine) {
            cache()->deleteMatching('current_stock_facility_' . $medicine['facility_id'] . '_*');
        }

        return $this->ok($batch, 'Medicine batch updated.');
    }

    private function hasValidBatchQuantities(array $payload): bool
    {
        if (! isset($payload['available_quantity'], $payload['received_quantity'])) {
            return true;
        }

        return (int) $payload['available_quantity'] <= (int) $payload['received_quantity'];
    }

    private function hasFutureExpiryDate(string $expiryDate): bool
    {
        if ($expiryDate === '') {
            return false;
        }

        return strtotime($expiryDate) > strtotime(date('Y-m-d'));
    }
}
