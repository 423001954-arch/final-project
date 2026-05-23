<?php

namespace App\Controllers\Api;

use App\Models\HealthcareFacilityModel;
use App\Models\MedicineModel;

class MedicinesController extends BaseApiController
{
    private MedicineModel $medicineModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->medicineModel = new MedicineModel();
    }

    public function index()
    {
        if ($blocked = $this->requireSupplyChainAccess()) {
            return $blocked;
        }

        $facilityId = (int) $this->request->getGet('facility_id');

        if ($facilityId < 1) {
            return $this->badRequest('facility_id is required.');
        }

        $items = $this->medicineModel->listForFacility(
            $facilityId,
            $this->request->getGet('search'),
            $this->requestedPerPage()
        );

        return $this->paginated($items, $this->medicineModel->pager, 'Medicines retrieved.');
    }

    public function currentStock()
    {
        if ($blocked = $this->requireSupplyChainAccess()) {
            return $blocked;
        }

        $facilityId = (int) $this->request->getGet('facility_id');

        if ($facilityId < 1) {
            return $this->badRequest('facility_id is required.');
        }

        $items = $this->medicineModel->currentStockForFacility(
            $facilityId,
            $this->request->getGet('search'),
            $this->requestedPerPage()
        );

        return $this->paginated($items, $this->medicineModel->pager, 'Current medicine stocks retrieved.');
    }

    public function show(int $id)
    {
        if ($blocked = $this->requireSupplyChainAccess()) {
            return $blocked;
        }

        $facilityId = (int) $this->request->getGet('facility_id');
        $medicine = $facilityId > 0
            ? $this->medicineModel->findForFacility($id, $facilityId)
            : $this->medicineModel->find($id);

        return $medicine ? $this->ok($medicine, 'Medicine retrieved.') : $this->notFound('Medicine not found.');
    }

    public function create()
    {
        if ($blocked = $this->requireSupplyChainAccess()) {
            return $blocked;
        }

        $payload = $this->requestPayload();
        $payload['sku'] = strtoupper(trim((string) ($payload['sku'] ?? '')));

        if (! (new HealthcareFacilityModel())->find((int) ($payload['facility_id'] ?? 0))) {
            return $this->badRequest('facility_id must reference an existing facility.');
        }

        if (! $this->medicineModel->insert($payload)) {
            return $this->validationFailed($this->medicineModel->errors());
        }

        cache()->delete('expiry_alerts_facility_' . $payload['facility_id']);

        return $this->created(
            $this->medicineModel->find((int) $this->medicineModel->getInsertID()),
            'Medicine created.'
        );
    }

    public function update(int $id)
    {
        if ($blocked = $this->requireSupplyChainAccess()) {
            return $blocked;
        }

        if ($this->medicineModel->find($id) === null) {
            return $this->notFound('Medicine not found.');
        }

        $payload = $this->requestPayload();

        if (isset($payload['sku'])) {
            $payload['sku'] = strtoupper(trim((string) $payload['sku']));
        }

        if (isset($payload['facility_id']) && ! (new HealthcareFacilityModel())->find((int) $payload['facility_id'])) {
            return $this->badRequest('facility_id must reference an existing facility.');
        }

        if (! $this->medicineModel->update($id, $payload)) {
            return $this->validationFailed($this->medicineModel->errors());
        }

        cache()->delete('expiry_alerts_facility_' . ($payload['facility_id'] ?? 'all'));

        return $this->ok($this->medicineModel->find($id), 'Medicine updated.');
    }

    public function delete(int $id)
    {
        if ($blocked = $this->requireSupplyChainAccess()) {
            return $blocked;
        }

        if ($this->medicineModel->find($id) === null) {
            return $this->notFound('Medicine not found.');
        }

        $this->medicineModel->delete($id);

        return $this->ok(null, 'Medicine archived.');
    }
}
