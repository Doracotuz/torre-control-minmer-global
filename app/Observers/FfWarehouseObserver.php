<?php

namespace App\Observers;

use App\Models\FfWarehouse;
use App\Services\Sync\FnFToWmsService;

class FfWarehouseObserver
{
    protected $syncService;

    public function __construct(FnFToWmsService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function created(FfWarehouse $warehouse)
    {
        $this->syncService->syncWarehouse($warehouse);
    }

    public function updated(FfWarehouse $warehouse)
    {
        $this->syncService->syncWarehouse($warehouse);
    }
}
