<?php

namespace App\Observers;

use App\Models\Warehouse;
use App\Services\Sync\WmsToFnFService;

class WarehouseObserver
{
    protected $syncService;

    public function __construct(WmsToFnFService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function created(Warehouse $warehouse)
    {
        $this->syncService->syncWarehouse($warehouse);
    }

    public function updated(Warehouse $warehouse)
    {
        $this->syncService->syncWarehouse($warehouse);
    }
}
