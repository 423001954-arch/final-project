<?php

namespace App\Controllers;

use App\Models\HealthcareFacilityModel;
use App\Models\MedicineBatchModel;
use App\Models\MedicineModel;
use App\Models\StockMovementModel;
use App\Services\FefoStockAllocator;
use App\Services\RoleAccess;
use RuntimeException;

class SupplyChainController extends BaseController
{
    /**
     * Helper to return responses consistently for both API and Web
     */
    private function respond(int $status, string $message, array $data = [], bool $isError = false)
    {
        if ($this->request->is('json')) {
            return $this->response->setJSON(['status' => $status, 'message' => $message, 'data' => $data])->setStatusCode($status);
        }
        
        $key = $isError ? 'error' : 'success';
        return redirect()->back()->with($key, $message)->with('data', $data);
    }

    public function storeIntake()
    {
        if (!RoleAccess::canOperateSupplyChain(session('user')['role'] ?? null)) {
            return $this->respond(403, 'Unauthorized access.');
        }

        // Use getVar() to support both JSON and Form Data
        $data = [
            'medicine_id'        => $this->request->getVar('medicine_id'),
            'batch_number'       => $this->request->getVar('batch_number'),
            'warehouse_location' => $this->request->getVar('warehouse_location'),
            'received_quantity'  => $this->request->getVar('received_quantity'),
            'expiry_date'        => $this->request->getVar('expiry_date'),
            'supplier'           => $this->request->getVar('supplier'),
            'unit_cost'          => $this->request->getVar('unit_cost'),
        ];

        $rules = [
            'medicine_id'        => 'required|is_natural_no_zero',
            'batch_number'       => 'required|min_length[2]|max_length[80]',
            'warehouse_location' => 'required|min_length[2]|max_length[120]',
            'received_quantity'  => 'required|is_natural_no_zero',
            'expiry_date'        => 'required|valid_date[Y-m-d]',
        ];

        if (!$this->validate($rules)) {
            return $this->respond(400, 'Validation failed.', $this->validator->getErrors(), true);
        }

        $db = db_connect();
        $db->transStart();

        $batchModel = new MedicineBatchModel();
        $quantity = (int) $data['received_quantity'];
        
        $batchId = $batchModel->insert([
            'medicine_id'        => (int) $data['medicine_id'],
            'batch_number'       => strtoupper(trim($data['batch_number'])),
            'warehouse_location' => trim($data['warehouse_location']),
            'received_quantity'  => $quantity,
            'available_quantity' => $quantity,
            'expiry_date'        => $data['expiry_date'],
            'status'             => 'available',
        ]);

        (new StockMovementModel())->insert([
            'batch_id'      => (int) $batchId,
            'movement_type' => 'receive',
            'quantity'      => $quantity,
            'performed_by'  => session('user')['id'] ?? 0,
        ]);

        $db->transComplete();

        return $this->respond(201, 'Stock intake successfully recorded.');
    }

    public function fulfillRequisition()
    {
        $rules = ['facility_id' => 'required', 'medicine_id' => 'required', 'quantity' => 'required'];
        if (!$this->validate($rules)) {
            return $this->respond(400, 'Invalid request.', $this->validator->getErrors(), true);
        }

        try {
            $result = (new FefoStockAllocator())->commitConsumption(
                (int)$this->request->getVar('facility_id'),
                (int)$this->request->getVar('medicine_id'),
                (int)$this->request->getVar('quantity'),
                (int)(session('user')['id'] ?? 0),
                'clinic_requisition',
                'REQ-' . date('Ymd-His')
            );
            return $this->respond(200, 'Requisition fulfilled.', $result);
        } catch (RuntimeException $e) {
            return $this->respond(422, $e->getMessage(), [], true);
        }
    }
}