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

    public function __construct(User $user, string $password, bool $isReWelcome = false)
    {
        $this->user = $user;
        $this->password = $password;
        $this->isReWelcome = $isReWelcome; 
    }

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

    public function content(): Content
    {
        return new Content(
            view: 'emails.users.welcome',
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}