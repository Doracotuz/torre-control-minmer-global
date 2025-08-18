<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notificación de Actividad</title>
    <style>
        /* Estilos copiados de tu ejemplo 'visit_invitation.blade.php' */
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #333; line-height: 1.5; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
        .header { background-color: #2c3856; padding: 20px; text-align: center; }
        .header img { max-height: 50px; }
        .content { padding: 30px; }
        .content h1 { color: #2b2b2b; font-size: 24px; }
        .details-table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        .details-table td { padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
        .details-table td:first-child { font-weight: bold; color: #666666; width: 150px; }
        .footer { background-color: #f1f5f9; padding: 20px; text-align: center; font-size: 12px; color: #666666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            {{-- Asumiendo que tu logo está en S3, si no, usa asset() --}}
            <img src="{{ config('filesystems.disks.s3.url') . '/LogoBlanco.png' }}" alt="Logotipo Minmer Global" width="180" style="display: block; width: 180px; height: auto; border: 0;">
        </div>
        <div class="content">
            <h1>Notificación de Actividad del Sistema</h1>
            <p>Hola,</p>
            <p>Se ha registrado una nueva actividad en el sistema que coincide con tus suscripciones de notificación:</p>

            <table class="details-table">
                <tr>
                    <td>Fecha y Hora:</td>
                    <td>{{ $activity->created_at->format('d/m/Y h:i A') }}</td>
                </tr>
                <tr>
                    <td>Usuario:</td>
                    <td>{{ $activity->user->name ?? 'Usuario no disponible' }}</td>
                </tr>
                <tr>
                    <td>Acción:</td>
                    <td><strong>{{ $activity->action }}</strong></td>
                </tr>
                <!-- @php
                    $details = is_string($activity->details) ? json_decode($activity->details, true) : $activity->details;
                @endphp -->
                @if(is_array($details) && count($details) > 0)
                    <tr>
                        <td style="vertical-align: top;">Detalles:</td>
                        <td>
                            @foreach($details as $key => $value)
                                <strong>{{ Str::title(str_replace('_', ' ', $key)) }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}<br>
                            @endforeach
                        </td>
                    </tr>
                @endif
            </table>
        </div>
        <div class="footer">
            <p>Este es un correo generado automáticamente. Por favor, no respondas a este mensaje.</p>
            <p>&copy; {{ date('Y') }} Minmer Global. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>