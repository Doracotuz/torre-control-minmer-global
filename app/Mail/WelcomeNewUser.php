<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeNewUser extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;
    public $isReWelcome;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $password, bool $isReWelcome = false)
    {
        $this->user = $user;
        $this->password = $password;
        $this->isReWelcome = $isReWelcome; 
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $firstName = explode(' ', $this->user->name)[0];
        $subject = $this->isReWelcome
            ? "¡Bienvenido(a), {$firstName}! Tu cuenta en Control Tower ha sido creada"
            : "¡Bienvenido(a), {$firstName}! Tu cuenta en Control Tower ha sido creada";

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.users.welcome', // Esta es la vista que crearemos a continuación
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}