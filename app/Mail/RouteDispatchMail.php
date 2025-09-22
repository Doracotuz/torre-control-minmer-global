<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RouteDispatchMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $bodyContent;
    public $signature_url;
    public $senderName;
    public $senderEmail;

    /**
     * Create a new message instance.
     */
    // ✅ CAMBIO: El constructor ahora espera un 'bodyContent' de tipo string
    public function __construct(string $subject, string $bodyContent, ?string $signature_url, string $senderName, string $senderEmail)
    {
        $this->subject = $subject;
        $this->bodyContent = $bodyContent;
        $this->signature_url = $signature_url;
        $this->senderName = $senderName;
        $this->senderEmail = $senderEmail;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            replyTo: [new Address($this->senderEmail, $this->senderName)],
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Apuntamos a la vista que simplemente renderizará el HTML que le pasemos
        return new Content(
            view: 'emails.route-dispatch',
        );
    }
}