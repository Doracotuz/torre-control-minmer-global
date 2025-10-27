<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Notificación de Actividad</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; 
            -webkit-text-size-adjust: none;
            word-wrap: break-word;
        }

        @media screen and (max-width: 600px) {
            .container {
                width: 100% !important;
                max-width: 100% !important;
            }
            .content {
                padding: 20px !important;
            }
            .details-table-cell {
                display: block !important;
                width: 100% !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
            .details-table-cell:first-child {
                padding-bottom: 5px !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc;">
    
    <table class="container" width="600" border="0" cellpadding="0" cellspacing="0" align="center" style="width: 600px; max-width: 600px; margin: 20px auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
        
        <tr>
            <td class="header" style="background-color: #2c3856; padding: 20px; text-align: center;">
                <img src="{{ config('filesystems.disks.s3.url') . '/LogoBlanco.png' }}" 
                     alt="Logotipo Minmer Global" 
                     width="180" 
                     style="width: 180px; max-width: 180px; height: auto; display: block; border: 0; margin: 0 auto;">
            </td>
        </tr>

        <tr>
            <td class="content" style="padding: 30px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #333; line-height: 1.5;">
                
                <h1 style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #2b2b2b; font-size: 24px; margin: 0 0 15px 0;">
                    Notificación de Actividad del Sistema
                </h1>
                <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; margin: 0 0 10px 0;">Hola,</p>
                <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; margin: 0 0 20px 0;">Se ha registrado una nueva actividad en el sistema que coincide con tus suscripciones de notificación:</p>

                <table class="details-table" width="100%" border="0" cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td class="details-table-cell" style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold; color: #666666; width: 150px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                            Fecha y Hora:
                        </td>
                        <td class="details-table-cell" style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                            {{ $activity->created_at->format('d/m/Y h:i A') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="details-table-cell" style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold; color: #666666; width: 150px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                            Usuario:
                        </td>
                        <td class="details-table-cell" style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                            {{ $activity->user->name ?? 'Usuario no disponible' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="details-table-cell" style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold; color: #666666; width: 150px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                            Acción:
                        </td>
                        <td class="details-table-cell" style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                            <strong>{{ $activity->action }}</strong>
                        </td>
                    </tr>
                    @if(!empty($activity->details))
                        <tr>
                            <td class-="details-table-cell" style="padding: 8px 0; vertical-align: top; font-weight: bold; color: #666666; width: 150px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                                Detalles:
                            </td>
                            <td class="details-table-cell" style="padding: 8px 0; vertical-align: top; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6;">
                                @foreach($activity->details as $key => $value)
                                    <strong>{{ Str::title(str_replace('_', ' ', $key)) }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}<br>
                                @endforeach
                            </td>
                        </tr>
                    @endif
                </table>
                
            </td>
        </tr>

        <tr>
            <td class="footer" style="background-color: #f1f5f9; padding: 20px; text-align: center; font-size: 12px; color: #666666; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                <p style="margin: 0 0 5px 0;">Este es un correo generado automáticamente. Por favor, no respondas a este mensaje.</p>
                <p style="margin: 0;">&copy; {{ date('Y') }} Minmer Global. Todos los derechos reservados.</p>
            </td>
        </tr>

    </table>
    
</body>
</html>