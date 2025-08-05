<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(public Ticket $ticket)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Actualización de Estado en tu Ticket #' . $this->ticket->id)
                    ->greeting('¡Hola, ' . $notifiable->name . '!')
                    ->line('El estado de tu ticket "' . $this->ticket->title . '" ha sido actualizado.')
                    ->line('**Nuevo Estado:** ' . $this->ticket->status)
                    ->action('Ver Ticket', route('tickets.show', $this->ticket->id))
                    ->line('Puedes seguir el progreso desde el enlace.');
    }
}