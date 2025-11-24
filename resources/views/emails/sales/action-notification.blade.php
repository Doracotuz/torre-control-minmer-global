<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; color: #333; line-height: 1.6; background-color: #f4f4f4; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header { text-align: center; padding-bottom: 20px; border-bottom: 2px solid #eee; }
        .header h2 { margin: 0; color: #2c3856; }
        .status-badge { display: inline-block; padding: 5px 15px; border-radius: 50px; color: white; font-size: 12px; font-weight: bold; margin-top: 10px; }
        .status-new { background-color: #28a745; }
        .status-update { background-color: #ff9c00; }
        .status-cancel { background-color: #dc3545; }
        
        .details { margin-top: 20px; }
        .details table { width: 100%; border-collapse: collapse; }
        .details th, .details td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; font-size: 14px; }
        .details th { color: #777; width: 35%; }
        
        .items-table { width: 100%; margin-top: 25px; border-collapse: collapse; font-size: 13px; }
        .items-table th { background-color: #2c3856; color: white; padding: 10px; text-align: left; }
        .items-table td { padding: 10px; border-bottom: 1px solid #eee; }
        
        .total-row { font-size: 18px; font-weight: bold; text-align: right; padding-top: 15px; color: #2c3856; }
        
        .footer { margin-top: 30px; text-align: center; font-size: 11px; color: #999; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Notificación de Pedido</h2>
            @if($type == 'new')
                <span class="status-badge status-new">NUEVA VENTA</span>
            @elseif($type == 'update')
                <span class="status-badge status-update">ACTUALIZACIÓN</span>
            @elseif($type == 'cancel')
                <span class="status-badge status-cancel">CANCELADO</span>
            @endif
            <p style="font-size: 18px; font-weight: bold; margin: 10px 0;">Folio #{{ $data['folio'] }}</p>
        </div>

        <div class="details">
            <h3>Datos Generales</h3>
            <table>
                <tr><th>Cliente:</th><td>{{ $data['client_name'] }}</td></tr>
                <tr><th>Empresa:</th><td>{{ $data['company_name'] }}</td></tr>
                <tr><th>Entrega:</th><td>{{ $data['delivery_date'] }}</td></tr>
                <tr><th>Surtidor:</th><td>{{ $data['surtidor_name'] }}</td></tr>
                @if($type == 'cancel')
                    <tr><th style="color:red;">Motivo Cancelación:</th><td style="color:red; font-weight:bold;">{{ $data['cancel_reason'] ?? 'Solicitud de usuario' }}</td></tr>
                @endif
            </table>
        </div>

        @if($type != 'cancel')
        <div class="items">
            <h3>Detalle</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Desc.</th>
                        <th style="text-align:center;">Cant.</th>
                        <th style="text-align:right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['items'] as $item)
                    <tr>
                        <td>{{ $item['sku'] }}</td>
                        <td>{{ $item['description'] }}</td>
                        <td style="text-align:center;">{{ $item['quantity'] }}</td>
                        <td style="text-align:right;">${{ number_format($item['total_price'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="total-row">
                Gran Total: ${{ number_format($data['grandTotal'], 2) }}
            </div>
        </div>
        @endif

        <div class="footer">
            Generado automáticamente por el sistema Control Tower de Minmer Global.<br>
            Fecha de movimiento: {{ date('d/m/Y H:i') }}
        </div>
    </div>
</body>
</html>