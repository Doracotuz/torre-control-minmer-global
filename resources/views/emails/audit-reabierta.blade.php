<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación de Auditoría Reabierta</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; margin: 0; padding: 0; }
        .email-container { max-width: 600px; margin: 30px auto; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; border: 1px solid #e0e0e0; }
        .header { background-color: #c0392b; padding: 24px; text-align: center; }
        .header img { max-width: 150px; }
        .content-body { padding: 30px; }
        .title { font-size: 26px; font-weight: 700; color: #c0392b; text-align: center; margin-bottom: 10px; }
        .subtitle { font-size: 16px; color: #666666; text-align: center; margin-bottom: 25px; line-height: 1.6; }
        .info-card { background-color: #f9f9f9; border-radius: 8px; padding: 20px; border: 1px solid #e0e0e0; margin-bottom: 25px; }
        .info-item { display: flex; align-items: center; margin-bottom: 15px; }
        .info-item:last-child { margin-bottom: 0; }
        .info-item .info-item-text { margin-left: 10px; color: #2c3856; font-size: 15px; }
        .info-label { font-weight: 600; margin-right: 5px; }
        .button-link { display: block; width: fit-content; margin: 30px auto 0; padding: 12px 28px; background-color: #3498db; color: #ffffff; text-decoration: none; font-weight: 600; border-radius: 8px; }
        .footer-text { text-align: center; font-size: 12px; color: #999999; margin-top: 30px; padding: 20px; border-top: 1px solid #e0e0e0; }
    </style>
</head>
<body>
    <div class="email-container">
        
        <div class="header">
            <img src="{{ Storage::disk('s3')->url('LogoBlanco.png') }}" alt="Logotipo de la Empresa">
        </div>
        
        <div class="content-body">
            <h1 class="title">Auditoría Reabierta</h1>
            <p class="subtitle">
                La auditoría para la siguiente guía ha sido reabierta. Todo el progreso y las evidencias fotográficas anteriores han sido eliminados.
            </p>
            
            <div class="info-card">
                <div class="info-item">
                    <span class="info-item-text">
                        <span class="info-label">Guía:</span>
                        <span>{{ $guia->guia }}</span>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-item-text">
                        <span class="info-label">Órdenes (SOs):</span>
                        <span>{{ $guia->plannings->pluck('order.so_number')->unique()->implode(', ') }}</span>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-item-text">
                        <span class="info-label">Acción realizada por:</span>
                        <span>{{ $reopenedBy->name }}</span>
                    </span>
                </div>
                 <div class="info-item">
                    <span class="info-item-text">
                        <span class="info-label">Fecha y Hora:</span>
                        <span>{{ now()->format('d/m/Y H:i') }} hrs</span>
                    </span>
                </div>
            </div>

            <p class="subtitle">
                El proceso debe comenzar nuevamente desde la <strong>Auditoría de Almacén</strong>.
            </p>

            <a href="{{ route('audit.index') }}" class="button-link">Ir al Dashboard de Auditoría</a>

        </div>

        <div class="footer-text">
            Este es un correo generado automáticamente por el sistema de logística.
        </div>
        
    </div>
</body>
</html>