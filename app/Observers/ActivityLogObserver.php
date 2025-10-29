<?php

namespace App\Observers;

use App\Events\UserActivityOccurred;
use App\Models\ActivityLog;

class ActivityLogObserver
{
    public function created(ActivityLog $activityLog): void
    {
        UserActivityOccurred::dispatch($activityLog);
    }
}