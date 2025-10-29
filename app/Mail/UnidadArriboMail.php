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
     * @var \App\Models\Guia
     */
    public $guia;

    /**
     * @return void
     */
    public function __construct(Guia $guia)
    {
        $this->guia = $guia;
    }

    /**
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Notificación de Arribo de Unidad: Guía ' . $this->guia->guia,
        );
    }

    /**
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.patio.unidad-arribo',
        );
    }

    /**
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}