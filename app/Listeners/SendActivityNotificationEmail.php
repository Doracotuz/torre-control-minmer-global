<?php

namespace App\Listeners;

use App\Events\UserActivityOccurred;
use App\Mail\ActivityNotificationMail;
use App\Models\NotificationSetting;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendActivityNotificationEmail
{
    public function handle(UserActivityOccurred $event): void
    {
        $activity = $event->activity;

        if ($activity->action_key === 'login' && !$activity->user->isClient()) {
            return;
        }
        $subscribedUserIds = NotificationSetting::where('event_name', $activity->action_key)
                                                ->pluck('user_id');

        if ($subscribedUserIds->isNotEmpty()) {
            $recipients = User::whereIn('id', $subscribedUserIds)->get();
            foreach ($recipients as $recipient) {
                Mail::to($recipient->email)->queue(new ActivityNotificationMail($activity));
            }
        }
    }
}