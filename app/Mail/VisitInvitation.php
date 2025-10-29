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
     * @return $this
     */
    public function build()
    {
        return $this->subject('Invitación de Visita a Minmer Global')
                    ->view('emails.visit_invitation')
                    ->attachData($this->qrCodeImage, 'codigo_qr.png', [
                        'mime' => 'image/png',
                    ]);
    }
}