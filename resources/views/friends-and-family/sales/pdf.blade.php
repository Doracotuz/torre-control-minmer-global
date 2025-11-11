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
            height: 55px;
            font-size: 14px;
            color: #222;
            border-bottom: 1px solid #eaeaea;
            padding-bottom: 10px;
        }
        
        header .logo-container {
            float: left;
            width: 50%;
            height: 50px;
            text-align: left;
        }
        
        header .logo-container img {
            max-height: 50px; 
            width: auto;
        }
        
        header .event-details {
            float: right;
            text-align: right;
            width: 50%;
            font-weight: 300;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #555;
            font-size: 13px;
            line-height: 1.2;
        }
        
        header .company-name {
            font-weight: 600;
            font-size: 14px;
            display: block; 
            color: #222;
            margin-top: 5px; 
        }

        header .event-details .date {
            font-size: 11px;
            font-weight: 300;
            color: #777;
            text-transform: none;
            letter-spacing: 0;
            display: block;
            margin-top: 4px;
        }           

        footer {
            position: fixed; 
            bottom: -40px; 
            left: 0px; 
            right: 0px;
            height: 30px; 
            font-size: 10px;
            text-align: right;
            color: #888;
            border-top: 1px solid #eaeaea;
            padding-top: 5px;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 25px;
        }

        .products-table th, .products-table td {
            border: 1px solid #ccc;
            padding: 5px;
            vertical-align: top;
        }

        .products-table th {
            background-color: #f7f7f7;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .products-table tfoot td {
            font-weight: 700;
            font-size: 11px;
            background-color: #f7f7f7;
        }

        .text-right {
            text-align: right;
        }
        
        .page-break {
            page-break-after: always;
        }

        .signatures {
            width: 100%;
            border-collapse: collapse;
            margin-top: 50px;
        }
        
        .signatures td {
            width: 45%;
            padding: 0;
            text-align: center;
        }

        .signature-box {
            border-bottom: 1px solid #000;
            height: 50px;
            vertical-align: bottom;
        }

        .signature-line {
            font-size: 9px;
            margin-top: 5px;
            padding-bottom: 2px;
            font-weight: 600;
        }

    </style>
</head>
<body>
    <header>
        <div class="logo-container">
            <img src="{{ $logo_url }}" alt="Logo Moët Hennessy">
            <!-- <span class="company-name">Moët Hennessy de México, S.A. de C.V.</span> -->
        </div>
        <div class="event-details">
            <span>Friends & Family</span>
            <span class="date">{{ $date }}</span>
            <div style="font-size: 11px; text-transform: none; color: #777; margin-top: 3px;">Recibo de Venta</div>
        </div>
    </header>

    <footer>
        <p>Este documento no tiene validez fiscal. Recibo generado el {{ $date }}.</p>
    </footer>

    <main>
        @foreach ($copies as $copyName)
        <div>
            <div class="page-copy" style="margin-top: 50px;">
                <div class="copy-header" style="text-align: right; margin-bottom: 5px;">
                    <span class="copy-name" style="font-weight: 700; color: #333;">COPIA PARA: {{ $copyName }}</span>
                </div>
                
                <div style="font-size: 11px; margin-bottom: 15px; border: 1px solid #ccc; padding: 5px;">   
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 50%; padding: 2px 0;">
                                <span style="font-weight: 600;">Cliente:</span> {{ $client_name ?? 'N/A' }}
                            </td>
                            <td style="width: 50%; padding: 2px 0;">
                                <span style="font-weight: 600;">Folio de Venta:</span> {{ $folio ?? 'N/A' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%; padding: 2px 0;">
                                <span style="font-weight: 600;">Vendedor (Caja):</span> {{ $vendedor_name ?? 'N/A' }}
                            </td>
                            <td style="width: 50%; padding: 2px 0;">
                                <span style="font-weight: 600;">Surtidor :</span> {{ $surtidor_name ?? 'N/A' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%; padding: 2px 0;">
                                <span style="font-weight: 600;">Fecha de Emisión:</span> {{ $date }}
                            </td>
                            <td style="width: 50%; padding: 2px 0;">
                                &nbsp;
                            </td>
                        </tr>
                    </table>
                </div>
                
                <table class="products-table">
                    <thead>
                        <tr>
                            <th style="width: 15%;">SKU</th>
                            <th style="width: 45%;">Descripción</th>
                            <th class="text-right" style="width: 15%;">Cant.</th>
                            <th class="text-right" style="width: 20%;">Precio Unit.</th>
                            <th class="text-right" style="width: 20%;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                        <tr>
                            <td>{{ $item['sku'] }}</td>
                            <td>{{ $item['description'] }}</td>
                            <td class="text-right">{{ $item['quantity'] }}</td>
                            <td class="text-right">${{ number_format($item['unit_price'], 2) }}</td>
                            <td class="text-right">${{ number_format($item['total_price'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-right">GRAN TOTAL:</td>
                            <td class="text-right">${{ number_format($grandTotal, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>

                <table class="signatures">
                    <tr>
                        <td class="signature-box">
                            <div class="signature-line">
                                Firma de Entregado (Surtidor/Vendedor)
                            </div>
                        </td>
                        <td style="width: 10%;"></td> 
                        <td class="signature-box">
                            <div class="signature-line">
                                Firma de Recibido (Cliente)
                            </div>
                        </td>
                    </tr>
                </table>

            </div>
        </div>
        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
        @endforeach
    </main>
</body>
</html>