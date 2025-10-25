<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <title>Notificación de Actividad</title>
    
    <style>
        :root {
            --brand-primary: #283856;
            --brand-accent: #F59E0B;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --bg-light: #f9fafb;
            --bg-white: #ffffff;
            --border-color: #e5e7eb;
            --footer-bg: #f8fafc;
        }

        html, body {
            margin: 0 auto !important;
            padding: 0 !important;
            height: 100% !important;
            width: 100% !important;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: #f1f5f9;
        }

        table, td {
            mso-table-lspace: 0pt !important;
            mso-table-rspace: 0pt !important;
        }

        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }

        .container {
            width: 95%;
            max-width: 600px;
            margin: 20px auto;
            background-color: var(--bg-white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            border: 1px solid var(--border-color);
        }

        .header {
            background-color: var(--brand-primary);
            padding: 24px;
            text-align: center;
        }

        .content {
            padding: 32px;
        }
        .content h1 {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-dark);
            margin-top: 0;
            margin-bottom: 12px;
        }
        .content p {
            margin-bottom: 24px;
            color: var(--text-light);
        }

        /* --- INNOVACIÓN: Ficha de Acción --- */
        .action-card {
            background-color: var(--bg-light);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .action-card h2 {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 16px 0;
            color: var(--brand-accent); /* Color de acento para la acción */
        }
        .action-card p {
            margin: 4px 0;
            font-size: 15px;
            color: var(--text-light);
        }
        .action-card p strong {
            color: var(--text-dark);
            font-weight: 600;
        }
        
        /* --- INNOVACIÓN: Snippets de Detalles --- */
        .details-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 12px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 8px;
        }
        .detail-snippet {
            background-color: var(--bg-white);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 8px;
        }
        .detail-snippet span {
            display: block;
            font-size: 13px;
            color: var(--text-light);
            font-weight: 500;
            margin-bottom: 2px;
            text-transform: capitalize;
        }
        .detail-snippet strong {
            display: block;
            font-size: 15px;
            color: var(--text-dark);
            font-weight: 600;
        }


        /* --- CTA (Call to Action) --- */
        .btn {
            display: block;
            width: fit-content;
            margin: 32px auto 0;
            padding: 14px 28px;
            background-color: var(--brand-accent);
            color: #ffffff;
            font-size: 16px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: #d97706;
        }

        /* --- FOOTER --- */
        .footer {
            padding: 24px 32px;
            text-align: center;
            font-size: 13px;
            color: #94a3b8;
            background-color: var(--footer-bg);
            border-top: 1px solid var(--border-color);
        }
        .footer p {
            margin: 8px 0;
            color: #94a3b8;
        }

    </style>
</head>
<body style="background-color: #f1f5f9; margin: 0; padding: 0;">
    <div class="container" style="max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid #e5e7eb;">
        
        <div class="header" style="background-color: #283856; padding: 24px; text-align: center;">
            <img src="{{ config('filesystems.disks.s3.url') . '/LogoBlanco.png' }}" alt="Logotipo Minmer Global" 
                 style="display: block; width: 180px; height: auto; border: 0; margin: 0 auto;">
        </div>

        <div class="content" style="padding: 32px;">
            
            <h1 style="font-size: 24px; font-weight: 600; color: #1f2937; margin-top: 0; margin-bottom: 12px;">Hola,</h1>
            
            <p style="margin-bottom: 24px; color: #6b7280;">Se ha registrado una nueva actividad en el sistema que coincide con tus suscripciones.</p>

            <div class="action-card" style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 24px; margin-bottom: 24px;">
                <h2 style="font-size: 22px; font-weight: 700; margin: 0 0 16px 0; color: #F59E0B;">{{ $activity->action }}</h2>
                <p style="margin: 4px 0; font-size: 15px; color: #6b7280;">
                    Realizado por: <strong style="color: #1f2937; font-weight: 600;">{{ $activity->user->name ?? 'Usuario no disponible' }}</strong>
                </p>
                <p style="margin: 4px 0; font-size: 15px; color: #6b7280;">
                    Fecha y Hora: <strong style="color: #1f2937; font-weight: 600;">{{ $activity->created_at->format('d/m/Y h:i A') }}</strong>
                </p>
            </div>

            @if(!empty($activity->details))
                <h3 class="details-title" style="font-size: 16px; font-weight: 600; color: #1f2937; margin-bottom: 12px; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px;">
                    Detalles Adicionales
                </h3>

                @foreach($activity->details as $key => $value)
                    @if(!is_array($value) && $value)
                        <div class="detail-snippet" style="background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px 16px; margin-bottom: 8px;">
                            <span style="display: block; font-size: 13px; color: #6b7280; font-weight: 500; margin-bottom: 2px; text-transform: capitalize;">
                                {{ Str::title(str_replace('_', ' ', $key)) }}
                            </span>
                            <strong style="display: block; font-size: 15px; color: #1f2937; font-weight: 600;">
                                {{ $value }}
                            </strong>
                        </div>
                    @endif
                @endforeach
                        <div class="detail-snippet" style="background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px 16px; margin-bottom: 8px;">
                            <span style="display: block; font-size: 13px; color: #6b7280; font-weight: 500; margin-bottom: 2px; text-transform: capitalize;">
                                Nombre de Usuario:
                            </span>
                            <strong style="display: block; font-size: 15px; color: #1f2937; font-weight: 600;">
                                {{ $activity->user->name ?? 'Usuario' }}
                            </strong>
                        </div>                
            @endif

            <a href="{{ config('app.url') }}" class="btn" style="display: block; width: -moz-fit-content; width: fit-content; margin: 32px auto 0; padding: 14px 28px; background-color: #F59E0B; color: #ffffff; font-size: 16px; font-weight: 600; text-align: center; text-decoration: none; border-radius: 8px;">
                Ir a la Aplicación
            </a>

        </div>

        <div class="footer" style="padding: 24px 32px; text-align: center; font-size: 13px; color: #94a3b8; background-color: #f8fafc; border-top: 1px solid #e5e7eb;">
            <p style="margin: 8px 0; color: #94a3b8;">&copy; {{ date('Y') }} Minmer Global. Todos los derechos reservados.</p>
            <p style="margin: 8px 0; color: #94a3b8;">Este es un correo generado automáticamente. Por favor, no respondas a este mensaje.</p>
        </div>
    </div>

    </body>
</html>