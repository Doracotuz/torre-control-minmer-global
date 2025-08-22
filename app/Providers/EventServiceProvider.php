<?php

namespace App\Providers;

// --- ASEGÚRATE DE QUE ESTOS 'USE' ESTÉN PRESENTES ---
use App\Models\ActivityLog;
use App\Observers\ActivityLogObserver;
// ---

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
    /**
     * The event to listener mappings for the application.
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \Illuminate\Auth\Events\Login::class => [
            \App\Listeners\LogSuccessfulLogin::class,
        ],
        // <-- VERIFICA QUE TU EVENTO Y LISTENER ESTÉN REGISTRADOS AQUÍ
        \App\Events\UserActivityOccurred::class => [
            \App\Listeners\SendActivityNotificationEmail::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // <-- VERIFICA QUE EL OBSERVER ESTÉ REGISTRADO AQUÍ
        ActivityLog::observe(ActivityLogObserver::class);
        CsPlanning::observe(CsPlanningObserver::class);
        CsOrder::observe(CsOrderObserver::class); 
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}