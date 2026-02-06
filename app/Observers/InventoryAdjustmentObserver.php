<?php

namespace App\Observers;

use App\Models\WMS\InventoryAdjustment;
use App\Services\Sync\WmsToFnFService;

class InventoryAdjustmentObserver
{
    protected $syncService;

    public function __construct(WmsToFnFService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function created(InventoryAdjustment $adjustment)
    {
        $this->syncService->createAdjustmentMovement($adjustment);
    }
}
