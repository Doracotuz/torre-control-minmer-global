<?php

namespace App\Mail;

use App\Models\Guia;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class AuditReabiertaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $guia;
    public $reopenedBy;

    /**
     * @param Guia $guia La guía cuya auditoría fue reabierta.
     * @param User $reopenedBy El usuario que realizó la acción.
     * @return void
     */
    public function __construct(Guia $guia, User $reopenedBy)
    {
        $this->guia = $guia;
        $this->reopenedBy = $reopenedBy;
    }

    /**
     * @return $this
     */
    public function build()
    {
        return $this->subject('Auditoría Reabierta: Guía ' . $this->guia->guia)
                    ->view('emails.audit-reabierta');
    }
}