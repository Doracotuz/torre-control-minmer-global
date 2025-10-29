<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Ticket;

class TicketClosedNotification extends Notification
{
    use Queueable;

    public function __construct(public Ticket $ticket) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Ticket Cerrado: #' . $this->ticket->id)
                    ->greeting('¡Hola, ' . $notifiable->name . '!')
                    ->line('El ticket "' . $this->ticket->title . '" ha sido cerrado exitosamente por el usuario.')
                    ->line('**Cerrado por:** ' . $this->ticket->user->name)
                    ->action('Ver Ticket Finalizado', route('tickets.show', $this->ticket->id));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
        ];
    }
}
