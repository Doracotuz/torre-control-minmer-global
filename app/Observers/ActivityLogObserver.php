<?php

namespace App\Observers;

use App\Events\UserActivityOccurred;
use App\Models\ActivityLog;

class ActivityLogObserver
{
    /**
     * Handle the ActivityLog "created" event.
     */
    public function created(ActivityLog $activityLog): void
    {
        // <-- VERIFICA QUE ESTA LÍNEA ESTÉ PRESENTE
        UserActivityOccurred::dispatch($activityLog);
    }
}