<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Venta</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; line-height: 1.4; color: #333; }
        .page { page-break-after: always; }
        .page:last-child { page-break-after: never; }
        .container { width: 90%; margin: 0 auto; }
        .header { text-align: right; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #000; font-size: 24px; }
        .header p { margin: 0; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f9f9f9; font-weight: bold; }
        td.text-right, th.text-right { text-align: right; }
        .total-row td { font-weight: bold; font-size: 14px; border-top: 2px solid #000; }
        .signatures { margin-top: 80px; }
        .signature-box { display: inline-block; width: 45%; margin: 0 2%; }
        .signature-line { border-top: 1px solid #000; padding-top: 5px; margin-top: 50px; }
        .footer { text-align: center; margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px; font-style: italic; }
    </style>
</head>
<body>

    @foreach (['CLIENTE', 'VENDEDOR'] as $copy)
    <div class="page">
        <div class="container">
            <div class="header">
                <h1>Recibo de Venta</h1>
                <p>Fecha: {{ $date }}</p>
                <p>Vendedor: {{ $user }}</p>
                <p style="font-size: 10px; font-weight: bold; margin-top: 5px;">COPIA: {{ $copy }}</p>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Descripción</th>
                        <th class="text-right">Cantidad</th>
                        <th class="text-right">Precio Unit.</th>
                        <th class="text-right">Precio Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                    <tr>
                        <td>{{ $item['description'] }}</td>
                        <td class="text-right">{{ $item['quantity'] }}</td>
                        <td class="text-right">${{ number_format($item['unit_price'], 2) }}</td>
                        <td class="text-right">${{ number_format($item['total_price'], 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="3" class="text-right">GRAN TOTAL:</td>
                        <td class="text-right">${{ number_format($grandTotal, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="signatures">
                <div class="signature-box" style="float: left;">
                    <div class="signature-line">
                        Firma de Entregado
                    </div>
                </div>
                <div class="signature-box" style="float: right;">
                    <div class="signature-line">
                        Firma de Recibido
                    </div>
                </div>
            </div>

            <div class="footer" style="clear: both;">
                <p>¡Gracias por tu compra!</p>
            </div>
        </div>
    </div>
    @endforeach

</body>
</html>