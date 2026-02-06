<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\Sync\WmsToFnFService;

class ProductObserver
{
    protected $syncService;

    public function __construct(WmsToFnFService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function created(Product $product)
    {
        $this->syncService->syncProduct($product);
    }

    public function updated(Product $product)
    {
        $this->syncService->syncProduct($product);
    }
}
