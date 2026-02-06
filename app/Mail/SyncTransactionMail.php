<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SyncTransactionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $type;
    public $message_body;
    public $payload;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($type, $message, $payload = [])
    {
        $this->type = $type;
        $this->message_body = $message;
        $this->payload = $payload;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Sincronización WMS-FnF: ' . $this->type)
                    ->view('emails.sync_notification');
    }
}
