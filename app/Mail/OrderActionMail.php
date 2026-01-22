<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class OrderActionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $type;
    protected $pdfContent;
    protected $csvContent;
    protected $externalDocs;
    protected $conditionsPdfData;
    protected $targetAdminId;

    public function __construct($data, $type, $pdfContent = null, $csvContent = null, $externalDocs = [], $conditionsPdfData = null, $targetAdminId = null)
    {
        $this->data = $data;
        $this->type = $type;
        $this->pdfContent = $pdfContent;
        $this->csvContent = $csvContent;
        $this->externalDocs = $externalDocs;
        $this->conditionsPdfData = $conditionsPdfData;
        $this->targetAdminId = $targetAdminId;
    }

    public function envelope(): Envelope
    {
        $prefix = 'Notificación de Pedido';
        $isEdit = $this->data['is_edit'] ?? false;

        if ($this->type === 'admin_alert') {
            $prefix = $isEdit ? 'ACTUALIZACIÓN de Pedido' : 'ALERTA: Nuevo Pedido por Aprobar';
        } else {
            $prefix = match($this->type) {
                'new' => 'Nuevo Pedido',
                'update' => 'Actualización de Pedido',
                'cancel' => 'CANCELACIÓN de Pedido',
                'backorder_filled' => '✅ STOCK DISPONIBLE: Backorder Surtido',
                default => 'Notificación de Pedido'
            };
        }

        return new Envelope(
            subject: "$prefix #" . $this->data['folio'] . ' - ' . $this->data['client_name'],
        );
    }

    public function content(): Content
    {
        $approveUrl = null;
        $rejectUrl = null;

        if ($this->type === 'admin_alert' && $this->targetAdminId) {
            $folio = $this->data['folio'];
            
            $approveUrl = URL::temporarySignedRoute(
                'ff.email.approve', 
                now()->addHours(48), 
                ['folio' => $folio, 'adminId' => $this->targetAdminId]
            );
            
            $rejectUrl = URL::temporarySignedRoute(
                'ff.email.reject.form', 
                now()->addHours(48), 
                ['folio' => $folio, 'adminId' => $this->targetAdminId]
            );
        }

        return new Content(
            view: 'emails.sales.action-notification',
            with: [
                'approveUrl' => $approveUrl,
                'rejectUrl' => $rejectUrl,
            ]
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        if ($this->pdfContent) {
            $attachments[] = Attachment::fromData(fn () => $this->pdfContent, 'Remision_' . $this->data['folio'] . '.pdf')->withMime('application/pdf');
        }
        if ($this->csvContent) {
            $attachments[] = Attachment::fromData(fn () => $this->csvContent, 'Detalle_' . $this->data['folio'] . '.csv')->withMime('text/csv');
        }
        if ($this->conditionsPdfData) {
            $attachments[] = Attachment::fromData(fn () => $this->conditionsPdfData, 'Condiciones_Entrega.pdf')->withMime('application/pdf');
        }
        foreach ($this->externalDocs as $doc) {
            $path = is_array($doc) ? $doc['path'] : $doc->path;
            $name = is_array($doc) ? $doc['name'] : $doc->filename;
            $attachments[] = Attachment::fromStorageDisk('s3', $path)->as($name)->withMime('application/pdf');
        }

        return $attachments;
    }
}