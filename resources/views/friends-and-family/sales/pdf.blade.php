<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Venta - Friends & Family</title>
    <style>
        @page {
            margin: 70px 50px;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #222222;
            line-height: 1.4;
        }

        header {
            position: fixed;
            top: -50px;
            left: 0px;
            right: 0px;
            height: 40px;
            font-size: 14px;
            color: #222;
            border-bottom: 1px solid #eaeaea;
            padding-bottom: 10px;
        }
        
        header .company-name {
            font-weight: 600;
            float: left;
        }
        
        header .event-details {
            float: right;
            font-weight: 300;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #555;
            font-size: 13px;
        }

        footer {
            position: fixed; 
            bottom: -40px; 
            left: 0px; 
            right: 0px;
            height: 30px; 
            font-size: 10px;
            text-align: center;
            color: #999999;
        }
        
        footer .page-number:after {
            content: "Página " counter(page);
        }

        .page {
            page-break-after: always;
        }
        .page:last-child {
            page-break-after: never;
        }
        
        .copy-identifier {
            font-size: 14px;
            font-weight: 600;
            color: #888;
            text-transform: uppercase;
        }

        .sale-info {
            margin-bottom: 25px;
            font-size: 11px;
            line-height: 1.5;
        }

        table.receipt-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.receipt-table th, 
        table.receipt-table td {
            border: 0;
            padding: 9px 8px;
            text-align: left;
            vertical-align: middle;
            border-bottom: 1px solid #eaeaea; 
        }

        table.receipt-table th {
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #000000;
            padding-bottom: 9px;
        }
        
        table.receipt-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table.receipt-table tfoot td {
            border-top: 2px solid #000;
            font-weight: bold;
            font-size: 13px;
            padding-top: 10px;
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .signatures {
            margin-top: 70px;
            width: 100%;
        }
        
        .signature-box {
            width: 45%;
            text-align: center;
            font-size: 11px;
            color: #333;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            padding-top: 8px;
            margin-top: 50px;
        }
        
    </style>
</head>
<body>
    <header>
        <span class="company-name">Moët Hennessy de México</span>
        <span class="event-details">Friends & Family</span>
    </header>

    <footer>
        <span class="page-number"></span>
    </footer>

    <main>
        
        @foreach (['CLIENTE', 'VENDEDOR', 'AUDITOR'] as $copy)
        
        <div class="page">
            <div class="container">
                
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    <tbody>
                        <tr>
                            <td style="width: 50%; text-align: left; border: 0; padding: 0; vertical-align: bottom;">
                                <h2 style="font-size: 20px; font-weight: 300; margin: 0;">Recibo de Venta</h2>
                            </td>
                            <td style="width: 50%; text-align: right; border: 0; padding: 0; vertical-align: bottom;">
                                <span class="copy-identifier">{{ $copy }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="sale-info">
                    <strong>Fecha:</strong> {{ $date }}<br>
                    <strong>Vendedor:</strong> {{ $user }}
                </div>

                <table class="receipt-table">
                    <thead>
                        <tr>
                            <th style="width: 50%;">Descripción</th>
                            <th class="text-right" style="width: 15%;">Cantidad</th>
                            <th class="text-right" style="width: 15%;">Precio Unit.</th>
                            <th class="text-right" style="width: 20%;">Precio Total</th>
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
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right">GRAN TOTAL:</td>
                            <td class="text-right">${{ number_format($grandTotal, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>

                <table class="signatures">
                    <tr>
                        <td class="signature-box">
                            <div class="signature-line">
                                Firma de Entregado
                            </div>
                        </td>
                        <td style="width: 10%;"></td> <td class="signature-box">
                            <div class="signature-line">
                                Firma de Recibido
                            </div>
                        </td>
                    </tr>
                </table>

            </div>
        </div>
        @endforeach
    </main>
</body>
</html>