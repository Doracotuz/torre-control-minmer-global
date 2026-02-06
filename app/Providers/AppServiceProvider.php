<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Models\ActivityLog;
use App\Observers\ActivityLogObserver;
use App\Models\CsOrder;
use App\Observers\CsOrderObserver;
use App\Models\CsPlanning;
use App\Observers\CsPlanningObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        ActivityLog::observe(ActivityLogObserver::class);
        CsOrder::observe(CsOrderObserver::class);
        CsPlanning::observe(CsPlanningObserver::class);

        // WMS <-> FnF Observers
        \App\Models\Product::observe(\App\Observers\ProductObserver::class);
        \App\Models\ffProduct::observe(\App\Observers\FfProductObserver::class);
        \App\Models\WMS\Quality::observe(\App\Observers\QualityObserver::class);
        \App\Models\FfQuality::observe(\App\Observers\FfQualityObserver::class);
        \App\Models\Warehouse::observe(\App\Observers\WarehouseObserver::class);
        \App\Models\FfWarehouse::observe(\App\Observers\FfWarehouseObserver::class);

        // Transaction Observers
        \App\Models\WMS\PurchaseOrder::observe(\App\Observers\PurchaseOrderObserver::class);
        \App\Models\WMS\InventoryAdjustment::observe(\App\Observers\InventoryAdjustmentObserver::class);
        \App\Models\ffInventoryMovement::observe(\App\Observers\FfInventoryMovementObserver::class);
    }

    protected $listen = [
        \Illuminate\Auth\Events\Login::class => [
            \App\Listeners\LogSuccessfulLogin::class,
        ],
    ];
    
    protected $policies = [
        
        \App\Models\Project::class => \App\Policies\ProjectPolicy::class,
    ];    

}
