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

    /**
     * Create a new notification instance.
     */
    public function __construct(public TicketReply $reply) {}

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
                    ->subject('Nueva Respuesta en el Ticket #' . $this->reply->ticket->id)
                    ->greeting('¡Hola!')
                    ->line('**' . $this->reply->user->name . '** ha respondido en el ticket "' . $this->reply->ticket->title . '".')
                    ->action('Ver Respuesta', route('tickets.show', $this->reply->ticket->id))
                    ->line('Puedes revisar la conversación y continuar desde el enlace.');
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
