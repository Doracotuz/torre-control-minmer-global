<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Ticket;

class ClosureRequestNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Ticket $ticket) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Solicitud de Cierre para tu Ticket #' . $this->ticket->id)
                    ->greeting('¡Hola, ' . $notifiable->name . '!')
                    ->line('El equipo de TI ha marcado tu ticket "' . $this->ticket->title . '" como resuelto y está pendiente de tu aprobación para ser cerrado.')
                    ->line('Por favor, revisa la solución y la evidencia adjunta (si aplica) y confirma el cierre.')
                    ->action('Ver y Aprobar Ticket', route('tickets.show', $this->ticket->id))
                    ->line('Si no apruebas el cierre en 48 horas, el ticket se cerrará automáticamente.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
