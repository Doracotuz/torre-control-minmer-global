<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Friends & Family - Lista de Productos</title>
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
            font-weight: 300;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #555;
            font-size: 13px;
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

        .info-card {
            background-color: #f9f9f9;
            border: 1px solid #eaeaea;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 20px;
            font-size: 11px;
        }
        .info-card table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-card td {
            padding: 5px;
            width: 50%;
            border: none;
            vertical-align: bottom;
        }
        .info-label {
            font-weight: 600;
            color: #333;
            padding-right: 10px;
            display: block;
            margin-bottom: 4px;
        }
        .info-field {
            display: block;
            border-bottom: 1px solid #bbb;
            height: 20px;
        }
        .table-wrapper {
            border-radius: 8px;
            border: 1px solid #eaeaea;
            overflow: hidden;
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }

        th, td {
            border: 0;
            padding: 8px;
            text-align: left;
            vertical-align: middle;
            border-bottom: 1px solid #eaeaea; 
        }

        thead tr {
            background-color: #f4f6f9;
        }

        th {
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #ddd;
            padding: 10px 8px;
            color: #555;
        }
        
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        tbody tr {
            border-bottom: 1px solid #eaeaea; 
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .page-break { page-break-after: always; }
        
    </style>
</head>
<body>
    <header>
        <div class="logo-container">
            <img src="{{ $logo_url }}" alt="Logo Moët Hennessy">
        </div>
        <div class="event-details">
            <span>Friends & Family</span>
            <span class="date">{{ $date }}</span>
        </div>
    </header>

    <main>
        <br>
        <div class="info-card">
            <table>
                <tr>
                    <td>
                        <span class="info-label">Cliente:</span>
                        <span class="info-field"></span>
                    </td>
                    <td>
                        <span class="info-label">Surtidor:</span>
                        <span class="info-field"></span>
                    </td>
                </tr>
            </table>
        </div>
        @for ($i = 0; $i < $numSets; $i++)
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 5%;">#</th>
                            <th class="text-center" style="width: 15%;">SKU</th>
                            <th style="width: 45%;">Descripción</th>
                            <th class="text-right" style="width: 15%;">Precio Unit.</th>
                            <th class="text-center" style="width: 20%;">Solicitado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $index => $product)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="text-center">{{ $product['sku'] }}</td>
                                <td>{{ $product['description'] }}</td>
                                <td class="text-right">$ {{ number_format($product['price'], 2) }}</td>
                                <td class="text-center">&nbsp;</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if ($i < $numSets - 1)
                <div class="page-break"></div>
            @endif
        @endfor
    </main>
</body>
</html>