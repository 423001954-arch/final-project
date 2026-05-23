<?php

namespace App\Controllers\Api;

use App\Services\FefoStockAllocator;
use RuntimeException;

class StockAllocationsController extends BaseApiController
{
    public function create()
    {
        if ($blocked = $this->requireSupplyChainAccess()) {
            return $blocked;
        }

        $payload = $this->requestPayload();
        $rules = [
            'facility_id' => 'required|is_natural_no_zero',
            'medicine_id' => 'required|is_natural_no_zero',
            'quantity'    => 'required|is_natural_no_zero',
            'mode'        => 'permit_empty|in_list[preview,commit]',
            'reference_type' => 'permit_empty|max_length[60]',
            'reference_id'   => 'permit_empty|max_length[80]',
        ];

        if (! $this->validateData($payload, $rules)) {
            return $this->validationFailed($this->validator->getErrors());
        }

        $allocator = new FefoStockAllocator();
        $mode = $payload['mode'] ?? 'preview';

        try {
            if ($mode === 'commit') {
                $result = $allocator->commitConsumption(
                    (int) $payload['facility_id'],
                    (int) $payload['medicine_id'],
                    (int) $payload['quantity'],
                    (int) ($this->apiUser['user_id'] ?? 0),
                    $payload['reference_type'] ?? null,
                    $payload['reference_id'] ?? null,
                    $payload['remarks'] ?? null
                );
            } else {
                $result = $allocator->preview((int) $payload['medicine_id'], (int) $payload['quantity']);
            }
        } catch (RuntimeException $exception) {
            return $this->conflict($exception->getMessage());
        }

        cache()->delete('expiry_alerts_facility_' . $payload['facility_id']);

        return $this->ok($result, 'FEFO allocation computed.');
    }
}
