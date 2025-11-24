<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class NewSaleMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    protected $pdfContent;
    protected $csvContent;

    public function __construct($data, $pdfContent, $csvContent)
    {
        $this->data = $data;
        $this->pdfContent = $pdfContent;
        $this->csvContent = $csvContent;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Remisión #' . $this->data['folio'] . ' - ' . $this->data['client_name'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sales.new-sale',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, 'Remision_' . $this->data['folio'] . '.pdf')
                ->withMime('application/pdf'),
                
            Attachment::fromData(fn () => $this->csvContent, 'Pedido_' . $this->data['folio'] . '.csv')
                ->withMime('text/csv'),
        ];
    }
}