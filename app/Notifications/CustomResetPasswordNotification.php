<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class CustomResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * El token para restablecer la contraseña.
     *
     * @var string
     */
    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Construye la URL para el restablecimiento de contraseña
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject(Lang::get('Restablecer Contraseña - Control Tower'))
            ->greeting(Lang::get('¡Hola!'))
            ->line(Lang::get('Has recibido este correo porque solicitaste un restablecimiento de contraseña para tu cuenta en la Torre de Control.'))
            ->line(Lang::get('Por favor, haz clic en el siguiente botón para establecer una nueva contraseña:'))
            ->action(Lang::get('Restablecer Contraseña'), $resetUrl)
            ->line(Lang::get('Este enlace de restablecimiento de contraseña expirará en :count minutos.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
            ->line(Lang::get('Si no solicitaste un restablecimiento de contraseña, no se requiere ninguna acción adicional. Puedes ignorar este correo de forma segura.'))
            ->salutation(Lang::get('Saludos,')."\n".Lang::get('El equipo de Minmer Global'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}