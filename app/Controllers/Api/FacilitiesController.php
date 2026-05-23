<?php

namespace App\Controllers\Api;

use App\Models\HealthcareFacilityModel;

class FacilitiesController extends BaseApiController
{
    private HealthcareFacilityModel $facilityModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->facilityModel = new HealthcareFacilityModel();
    }

    public function index()
    {
        if ($blocked = $this->requireSupplyChainAccess()) {
            return $blocked;
        }

        $facilities = $this->facilityModel
            ->orderBy('name', 'ASC')
            ->paginate($this->requestedPerPage());

        return $this->paginated($facilities, $this->facilityModel->pager, 'Facilities retrieved.');
    }

    public function show(int $id)
    {
        if ($blocked = $this->requireSupplyChainAccess()) {
            return $blocked;
        }

        $facility = $this->facilityModel->find($id);

        return $facility ? $this->ok($facility, 'Facility retrieved.') : $this->notFound('Facility not found.');
    }

    public function create()
    {
        if ($blocked = $this->requireSupplyChainAccess()) {
            return $blocked;
        }

        $payload = $this->requestPayload();

        if (! $this->facilityModel->insert($payload)) {
            return $this->validationFailed($this->facilityModel->errors());
        }

        return $this->created(
            $this->facilityModel->find((int) $this->facilityModel->getInsertID()),
            'Facility created.'
        );
    }

    public function update(int $id)
    {
        if ($blocked = $this->requireSupplyChainAccess()) {
            return $blocked;
        }

        if ($this->facilityModel->find($id) === null) {
            return $this->notFound('Facility not found.');
        }

        $payload = $this->requestPayload();
        $payload['id'] = $id;

        if (! $this->facilityModel->update($id, $payload)) {
            return $this->validationFailed($this->facilityModel->errors());
        }

        return $this->ok($this->facilityModel->find($id), 'Facility updated.');
    }
}
