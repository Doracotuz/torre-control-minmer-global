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
