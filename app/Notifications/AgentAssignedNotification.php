<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Ticket;

class AgentAssignedNotification extends Notification
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
                    ->subject('Se te ha asignado un nuevo ticket: #' . $this->ticket->id)
                    ->greeting('¡Hola, ' . $notifiable->name . '!')
                    ->line('Se te ha asignado el siguiente ticket para su resolución:')
                    ->line('**Título:** ' . $this->ticket->title)
                    ->line('**Prioridad:** ' . $this->ticket->priority)
                    ->action('Ver Ticket Asignado', route('tickets.show', $this->ticket->id));
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
