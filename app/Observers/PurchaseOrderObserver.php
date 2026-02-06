<?php

namespace App\Observers;

use App\Models\WMS\PurchaseOrder;
use App\Services\Sync\WmsToFnFService;

class PurchaseOrderObserver
{
    protected $syncService;

    public function __construct(WmsToFnFService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function updated(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->isDirty('status') && $purchaseOrder->status === 'Completed') {
            $this->syncService->createInboundMovement($purchaseOrder);
        }
    }
}
