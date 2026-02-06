<?php

namespace App\Observers;

use App\Models\ffInventoryMovement;
use App\Services\Sync\FnFToWmsService;

class FfInventoryMovementObserver
{
    protected $syncService;

    public function __construct(FnFToWmsService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function updated(ffInventoryMovement $movement)
    {
        // Check if just authorized/approved
        if ($movement->isDirty('status') && in_array($movement->status, ['authorized', 'approved'])) {
            $this->syncService->createOutboundOrder($movement);
        }
        
        // Also check approved_by/approved_at just in case status logic differs
        if ($movement->isDirty('approved_at') && $movement->approved_at && !$movement->getOriginal('approved_at')) {
             // Avoid double call if status also changed, usually status syncs with approval
             if (!$movement->isDirty('status')) {
                 $this->syncService->createOutboundOrder($movement);
             }
        }
    }
}
