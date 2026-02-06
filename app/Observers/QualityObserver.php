<?php

namespace App\Observers;

use App\Models\WMS\Quality;
use App\Services\Sync\WmsToFnFService;

class QualityObserver
{
    protected $syncService;

    public function __construct(WmsToFnFService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function created(Quality $quality)
    {
        $this->syncService->syncQuality($quality);
    }

    public function updated(Quality $quality)
    {
        $this->syncService->syncQuality($quality);
    }
}
