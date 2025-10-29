<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\TicketReply;

class NewReplyNotification extends Notification
{
    use Queueable;

    public function __construct(public TicketReply $reply) {}

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
                    ->subject('Nueva Respuesta en el Ticket #' . $this->reply->ticket->id)
                    ->greeting('¡Hola!')
                    ->line('**' . $this->reply->user->name . '** ha respondido en el ticket "' . $this->reply->ticket->title . '".')
                    ->action('Ver Respuesta', route('tickets.show', $this->reply->ticket->id))
                    ->line('Puedes revisar la conversación y continuar desde el enlace.');
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
