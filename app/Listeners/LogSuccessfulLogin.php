<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\ActivityLog;

class LogSuccessfulLogin
{
    public function __construct()
    {
        //
    }

    public function handle(Login $event)
    {
        ActivityLog::create([
            'user_id' => $event->user->id,
            'action' => 'Inició sesión',
            'details' => json_encode(['email' => $event->user->email]),
        ]);
    }
}