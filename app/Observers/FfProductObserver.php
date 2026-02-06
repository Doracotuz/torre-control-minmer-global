<?php

namespace App\Observers;

use App\Models\ffProduct;
use App\Services\Sync\FnFToWmsService;

class FfProductObserver
{
    protected $syncService;

    public function __construct(FnFToWmsService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function created(ffProduct $product)
    {
        $this->syncService->syncProduct($product);
    }

    public function updated(ffProduct $product)
    {
        $this->syncService->syncProduct($product);
    }
}
