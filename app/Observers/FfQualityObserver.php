<?php

namespace App\Observers;

use App\Models\FfQuality;
use App\Services\Sync\FnFToWmsService;

class FfQualityObserver
{
    protected $syncService;

    public function __construct(FnFToWmsService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function created(FfQuality $quality)
    {
        $this->syncService->syncQuality($quality);
    }

    public function updated(FfQuality $quality)
    {
        $this->syncService->syncQuality($quality);
    }
}
