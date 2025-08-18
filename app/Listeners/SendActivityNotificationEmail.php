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

        // --- INICIO DE LA NUEVA LÓGICA ---
        // Si la acción es un 'login', verifica si el usuario que lo realizó es un cliente.
        // Si NO es un cliente, detenemos el envío de la notificación y terminamos.
        if ($activity->action_key === 'login' && !$activity->user->isClient()) {
            return;
        }
        // --- FIN DE LA NUEVA LÓGICA ---

        // Para todas las demás acciones (o para logins de clientes), el código continúa como antes.
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