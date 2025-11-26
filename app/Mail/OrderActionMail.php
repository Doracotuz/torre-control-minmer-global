<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class OrderActionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $type;
    protected $pdfContent;
    protected $csvContent;
    protected $externalDocs;

    public function __construct($data, $type, $pdfContent = null, $csvContent = null, $externalDocs = [])
    {
        $this->data = $data;
        $this->type = $type;
        $this->pdfContent = $pdfContent;
        $this->csvContent = $csvContent;
        $this->externalDocs = $externalDocs;
    }

    public function envelope(): Envelope
    {
        $prefix = match($this->type) {
            'new' => 'Nueva Pedido',
            'update' => 'Actualización de Pedido',
            'cancel' => 'CANCELACIÓN de Pedido',
        };

        return new Envelope(
            subject: "$prefix #" . $this->data['folio'] . ' - ' . $this->data['client_name'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sales.action-notification',
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        if ($this->pdfContent) {
            $attachments[] = Attachment::fromData(fn () => $this->pdfContent, 'Remision_' . $this->data['folio'] . '.pdf')
                ->withMime('application/pdf');
        }

        if ($this->csvContent) {
            $attachments[] = Attachment::fromData(fn () => $this->csvContent, 'Detalle_' . $this->data['folio'] . '.csv')
                ->withMime('text/csv');
        }

        foreach ($this->externalDocs as $doc) {
            $attachments[] = Attachment::fromStorageDisk('s3', $doc['path'])
                ->as($doc['name'])
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}