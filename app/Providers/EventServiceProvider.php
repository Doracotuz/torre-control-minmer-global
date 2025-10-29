<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Observers\ActivityLogObserver;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Models\CsPlanning;
use App\Observers\CsPlanningObserver;
use App\Models\CsOrder;
use App\Observers\CsOrderObserver;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \Illuminate\Auth\Events\Login::class => [
            \App\Listeners\LogSuccessfulLogin::class,
        ],
        \App\Events\UserActivityOccurred::class => [
            \App\Listeners\SendActivityNotificationEmail::class,
        ],
    ];

    public function boot(): void
    {
        ActivityLog::observe(ActivityLogObserver::class);
        CsPlanning::observe(CsPlanningObserver::class);
        CsOrder::observe(CsOrderObserver::class); 
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}