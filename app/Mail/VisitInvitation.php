<?php

namespace App\Mail;

use App\Models\Tms\Visit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VisitInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $visit;
    public $qrCodeImage;

    /**
     * Create a new message instance.
     *
     * @param \App\Models\Tms\Visit $visit
     * @param mixed $qrCodeImage (Raw PNG data of the QR code)
     * @return void
     */
    public function __construct(Visit $visit, $qrCodeImage)
    {
        $this->visit = $visit;
        $this->qrCodeImage = $qrCodeImage;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Invitación de Visita a Minmer Global')
                    ->view('emails.visit_invitation') // La plantilla Blade para el correo
                    ->attachData($this->qrCodeImage, 'codigo_qr.png', [
                        'mime' => 'image/png',
                    ]);
    }
}