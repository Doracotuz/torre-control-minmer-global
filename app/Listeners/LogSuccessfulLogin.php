<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LogSuccessfulLogin
{
    /**
     * La petición HTTP.
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Crea el event listener.
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {

        ActivityLog::create([
            'user_id' => $event->user->id,
            'action' => 'Inicio de Sesión',
            'action_key' => 'login',

            'details' => [
                'email' => $event->user->email,
            ],
        ]);
    }
}