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
     * Helper to return responses consistently for both API (Postman) and Web (Browser)
     */
    private function respond(int $status, string $message, array $data = [], bool $isError = false)
    {
        if ($this->request->is('json') || strpos($this->request->getHeaderLine('Content-Type'), 'application/json') !== false) {
            return $this->response->setJSON(['status' => $status, 'message' => $message, 'data' => $data])->setStatusCode($status);
        }
        
        $key = $isError ? 'error' : 'success';
        return redirect()->back()->with($key, $message)->with('data', $data)->withInput();
    }

    // ==============================================================================
    // WEB UI ROUTE METHODS (These load your Dashboard pages)
    // ==============================================================================

    public function index()
    {
        if (!RoleAccess::canOperateSupplyChain(session('user')['role'] ?? null)) {
            return redirect()->to('/unauthorized');
        }

        $batchModel = new MedicineBatchModel();

        return view('supply/index', [
            'title'      => 'Supply Operations',
            'facilities' => (new HealthcareFacilityModel())->orderBy('name', 'ASC')->findAll(),
            'medicines'  => (new MedicineModel())->orderBy('generic_name', 'ASC')->findAll(),
            'batches'    => $batchModel
                ->select('medicine_batches.*, medicines.generic_name, medicines.sku, healthcare_facilities.name AS facility_name')
                ->join('medicines', 'medicines.id = medicine_batches.medicine_id')
                ->join('healthcare_facilities', 'healthcare_facilities.id = medicines.facility_id', 'left')
                ->orderBy('expiry_date', 'ASC')
                ->findAll(25),
            'expired'    => $batchModel->expiredActiveBatches(25),
            'movements'  => $this->recentMovements(),
        ]);
    }

    public function intake()
    {
        if (!RoleAccess::canOperateSupplyChain(session('user')['role'] ?? null)) {
            return redirect()->to('/unauthorized');
        }

        return view('supply/intake', [
            'title'     => 'Supply Intake',
            'medicines' => (new MedicineModel())->orderBy('generic_name', 'ASC')->findAll(),
        ]);
    }

    public function requisition()
    {
        return view('supply/requisition', [
            'title'      => 'Clinic Requisition',
            'facilities' => (new HealthcareFacilityModel())->orderBy('name', 'ASC')->findAll(),
            'medicines'  => (new MedicineModel())->orderBy('generic_name', 'ASC')->findAll(),
        ]);
    }

    public function disposal()
    {
        if (!RoleAccess::canOperateSupplyChain(session('user')['role'] ?? null)) {
            return redirect()->to('/unauthorized');
        }

        $batchModel = new MedicineBatchModel();

        return view('supply/disposal', [
            'title'   => 'Expiry Disposal',
            'expired' => $batchModel->expiredActiveBatches(100),
        ]);
    }

    // ==============================================================================
    // HYBRID DATA PROCESSING METHODS (Handles Form Submits AND Postman API)
    // ==============================================================================

    public function storeIntake()
    {
        if (!RoleAccess::canOperateSupplyChain(session('user')['role'] ?? null)) {
            return $this->respond(403, 'Unauthorized access.', [], true);
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

        // validateData is safe for array inputs regardless of whether it's JSON or POST
        if (!$this->validateData($data, $rules)) {
