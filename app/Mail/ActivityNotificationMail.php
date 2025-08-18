<?php
namespace App\Mail;
use App\Models\ActivityLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class ActivityNotificationMail extends Mailable {
    use Queueable, SerializesModels;
    public $activity;
    public function __construct(ActivityLog $activity) {
        $this->activity = $activity;
    }
    public function build() {
        return $this->subject('Notificación de Actividad: ' . $this->activity->action)
                    ->view('emails.activity_notification');
    }
}