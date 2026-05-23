<?php

namespace App\Controllers\Api;

use App\Models\MedicineBatchModel;

class ExpiryAlertsController extends BaseApiController
{
    public function index()
    {
        if ($blocked = $this->requireSupplyChainAccess()) {
            return $blocked;
        }

        $facilityId = (int) $this->request->getGet('facility_id');

        if ($facilityId < 1) {
            return $this->badRequest('facility_id is required.');
        }

        $days = max(1, min((int) ($this->request->getGet('days') ?? 30), 365));
        $cacheKey = 'expiry_alerts_facility_' . $facilityId . '_days_' . $days;
        $alerts = cache()->remember($cacheKey, 300, static function () use ($facilityId, $days): array {
            return (new MedicineBatchModel())->expiringSoon($facilityId, $days);
        });

        return $this->ok([
            'facility_id' => $facilityId,
            'days_ahead'  => $days,
            'items'       => $alerts,
        ], 'Expiry alerts retrieved.');
    }
}
