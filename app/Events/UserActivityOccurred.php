<?php
namespace App\Events;
use App\Models\ActivityLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class UserActivityOccurred {
    use Dispatchable, SerializesModels;
    public ActivityLog $activity;
    public function __construct(ActivityLog $activity) {
        $this->activity = $activity;
    }
}