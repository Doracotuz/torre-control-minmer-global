<?php

namespace App\Mail;

use App\Models\Guia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UnidadArriboMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * La instancia de la guía con los datos del arribo.
     *
     * @var \App\Models\Guia
     */
    public $guia;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Guia $guia)
    {
        $this->guia = $guia;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Notificación de Arribo de Unidad: Guía ' . $this->guia->guia,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.patio.unidad-arribo',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}