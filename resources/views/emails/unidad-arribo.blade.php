<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arribo de Unidad</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .header { font-size: 24px; font-weight: bold; color: #2c3856; text-align: center; margin-bottom: 20px; }
        .content-table { width: 100%; border-collapse: collapse; }
        .content-table td { padding: 10px; border-bottom: 1px solid #eee; }
        .content-table td:first-child { font-weight: bold; color: #555; width: 150px; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #999; }
        .button { display: inline-block; padding: 12px 25px; margin-top: 20px; background-color: #purple-600; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">Notificación de Arribo de Unidad</div>
        <p>Se ha completado la auditoría de patio para la siguiente unidad. Ya se encuentra en cortina, lista para el proceso de carga.</p>
        <table class="content-table">
            <tr>
                <td>Guía:</td>
                <td><strong>{{ $guia->guia }}</strong></td>
            </tr>
            <tr>
                <td>Operador:</td>
                <td>{{ $guia->operador }}</td>
            </tr>
            <tr>
                <td>Placas:</td>
                <td>{{ $guia->placas }}</td>
            </tr>
            <tr>
                <td>Fecha de Arribo:</td>
                <td>{{ \Carbon\Carbon::parse($guia->audit_patio_arribo)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td>Hora de Arribo:</td>
                <td>{{ \Carbon\Carbon::parse($guia->audit_patio_arribo)->format('H:i') }} hrs</td>
            </tr>
            <tr>
                <td>Estatus de la Guía:</td>
                <td><span style="color: green; font-weight: bold;">{{ $guia->estatus }}</span></td>
            </tr>
        </table>
        <div style="text-align: center;">
             <a href="{{ route('audit.index') }}" class="button">Ir al Dashboard de Auditoría</a>
        </div>
    </div>
    <div class="footer">
        Este es un correo generado automáticamente por el sistema de logística.
    </div>
</body>
</html>