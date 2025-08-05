<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitación de Visita</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            background-color: #f8fafc;
            color: #333;
            line-height: 1.5;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }
        .header {
            background-color: #2c3856;
            padding: 20px;
            text-align: center;
        }
        .header img {
            max-height: 50px;
        }
        .content {
            padding: 30px;
        }
        .content h1 {
            color: #2b2b2b;
            font-size: 24px;
        }
        .details-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .details-table td {
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .details-table td:first-child {
            font-weight: bold;
            color: #666666;
            width: 150px;
        }
        .qr-section {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        .footer {
            background-color: #f1f5f9;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ Storage::disk('s3')->url('LogoBlanco.png') }}" alt="Logotipo Minmer Global">
        </div>
        <div class="content">
            <h1>Invitación de Visita</h1>
            <p>Hola, <strong>{{ $visit->visitor_name }} {{ $visit->visitor_last_name }}</strong>,</p>
            <p>Has sido invitado(a) a nuestras instalaciones. A continuación, se detallan los datos de tu visita:</p>
            
            <table class="details-table">
                <tr>
                    <td>Fecha y Hora:</td>
                    <td>{{ \Carbon\Carbon::parse($visit->visit_datetime)->format('d/m/Y h:i A') }}</td>
                </tr>
                <tr>
                    <td>Motivo:</td>
                    <td>{{ $visit->reason }}</td>
                </tr>
                @if($visit->company)
                <tr>
                    <td>Empresa:</td>
                    <td>{{ $visit->company }}</td>
                </tr>
                @endif
                @if($visit->license_plate)
                <tr>
                    <td>Vehículo:</td>
                    <td>{{ $visit->vehicle_make }} {{ $visit->vehicle_model }}, Placas: {{ $visit->license_plate }}</td>
                </tr>
                @endif
                @php
                    $companions = json_decode($visit->companions);
                @endphp
                @if(!empty($companions))
                <tr>
                    <td>Acompañantes:</td>
                    <td>{{ implode(', ', $companions) }}</td>
                </tr>
                @endif
            </table>

            <div class="qr-section">
                <p style="font-weight: bold; color: #2b2b2b;">Por favor, presenta este código QR en el acceso:</p>
                <img src="{{ $message->embedData($qrCodeImage, 'codigo_qr.png') }}" alt="Código QR de Acceso">
                <p style="font-size: 12px; color: #666666;">Este código es único e intransferible.</p>
            </div>
        </div>
        <div class="footer">
            <p><strong>Dirección:</strong><br>Av. Hermenegildo Galeana, Col. El Fresno, Tultitlán, Estado de México.</p>
            <p>&copy; {{ date('Y') }} Minmer Global. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
