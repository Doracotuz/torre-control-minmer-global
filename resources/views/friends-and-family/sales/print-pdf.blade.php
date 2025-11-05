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
            text-align: center;
            color: #999999;
        }
        
        footer .page-number:after {
            content: "Página " counter(page);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 0;
            padding: 9px 8px; 
            text-align: left;
            vertical-align: middle;
            border-bottom: 1px solid #eaeaea; 
        }

        thead tr {
            background-color: #ffffff;
        }

        th {
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #000000;
            padding-bottom: 9px;
        }
        
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        tbody tr {
            border-bottom: 1px solid #eaeaea; 
        }

        .solicitado-cell {
            height: 20px;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .page-break { page-break-after: always; }
        
    </style>
</head>
<body>
    <header>
        <span class="company-name">Moët Hennessy de México</span>
        <div class="event-details">
            <span>Friends & Family</span>
            <span class="date">{{ $date }}</span>
        </div>
    </header>

    <footer>
        <span class="page-number"></span>
    </footer>

    <main>
        @for ($i = 0; $i < $numSets; $i++)
            <div>
                <table>
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 5%;">#</th>
                            <th style="width: 15%;">SKU</th>
                            <th style="width: 45%;">Descripción</th>
                            <th class="text-right" style="width: 15%;">Precio Unit.</th>
                            <th class="text-center" style="width: 20%;">Solicitado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $index => $product)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $product['sku'] }}</td>
                                <td>{{ $product['description'] }}</td>
                                <td class="text-right">$ {{ number_format($product['price'], 2) }}</td>
                                <td class="solicitado-cell">&nbsp;</td>
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