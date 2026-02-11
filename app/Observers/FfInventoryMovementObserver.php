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
        if ($movement->isDirty('status') && in_array($movement->status, ['authorized', 'approved'])) {
            $this->syncService->createOutboundOrder($movement);
        }
        
        if ($movement->isDirty('approved_at') && $movement->approved_at && !$movement->getOriginal('approved_at')) {
             if (!$movement->isDirty('status')) {
                 $this->syncService->createOutboundOrder($movement);
             }
        }
    }
}
