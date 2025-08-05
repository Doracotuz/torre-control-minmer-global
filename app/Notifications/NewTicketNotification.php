<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Ticket;

class NewTicketNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Ticket $ticket) {}
        //

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
        $subject = sprintf(
            'Nuevo Ticket de Soporte: %s (de %s)',
            $this->ticket->title,
            $this->ticket->user->name
        );

        return (new MailMessage)
                    ->subject($subject)
                    ->greeting('¡Hola!')
                    ->line('Se ha creado un nuevo ticket de soporte:')
                    ->line('**Título:** ' . $this->ticket->title)
                    ->action('Ver Ticket', route('tickets.show', $this->ticket->id))
                    ->line('Gracias por usar nuestra mesa de ayuda.');
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
