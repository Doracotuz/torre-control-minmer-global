<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; }
        .header { background-color: #2c3856; color: white; padding: 15px; text-align: center; }
        .details { margin-top: 20px; }
        .details table { width: 100%; border-collapse: collapse; }
        .details th, .details td { padding: 8px; border-bottom: 1px solid #ddd; text-align: left; }
        .details th { background-color: #f8f8f8; }
        .items-table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        .items-table th { background-color: #2c3856; color: white; padding: 10px; text-align: left; }
        .items-table td { padding: 10px; border-bottom: 1px solid #eee; }
        .total { font-size: 18px; font-weight: bold; text-align: right; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Nueva Venta Generada</h2>
            <p>Folio: {{ $data['folio'] }}</p>
        </div>

        <div class="details">
            <h3>Datos del Cliente</h3>
            <table>
                <tr><th>Cliente:</th><td>{{ $data['client_name'] }}</td></tr>
                <tr><th>Empresa:</th><td>{{ $data['company_name'] }}</td></tr>
                <tr><th>Teléfono:</th><td>{{ $data['client_phone'] }}</td></tr>
                <tr><th>Dirección:</th><td>{{ $data['address'] }}, {{ $data['locality'] }}</td></tr>
                <tr><th>Fecha Entrega:</th><td>{{ $data['delivery_date'] }}</td></tr>
                <tr><th>Surtidor:</th><td>{{ $data['surtidor_name'] }}</td></tr>
                <tr><th>Vendedor:</th><td>{{ $data['vendedor_name'] }}</td></tr>
            </table>
        </div>

        <div class="items">
            <h3>Detalle del Pedido</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Descripción</th>
                        <th>Cant.</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['items'] as $item)
                    <tr>
                        <td>{{ $item['sku'] }}</td>
                        <td>{{ $item['description'] }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>${{ number_format($item['total_price'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="total">
            Gran Total: ${{ number_format($data['grandTotal'], 2) }}
        </div>

        <p style="margin-top: 30px; font-size: 12px; color: #777;">
            Se adjunta la remisión en formato PDF y el detalle en formato CSV.
        </p>
    </div>
</body>
</html>