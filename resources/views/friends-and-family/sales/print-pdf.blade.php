<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Marketing - Lista de Productos</title>
    <style>
        @page { margin: 70px 50px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #222; line-height: 1.4; }
        header { position: fixed; top: -50px; left: 0; right: 0; height: 40px; border-bottom: 1px solid #eaeaea; padding-bottom: 10px; }
        header .logo-container { float: left; width: 50%; }
        header .logo-container img { max-height: 50px; width: auto; }
        header .event-details { float: right; text-transform: uppercase; color: #555; font-size: 13px; text-align: right; }
        header .event-details .date { font-size: 11px; display: block; margin-top: 4px; color: #777; }
        .table-wrapper { border-radius: 8px; border: 1px solid #eaeaea; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border-bottom: 1px solid #eaeaea; text-align: left; }
        thead tr { background-color: #f4f6f9; }
        th { font-size: 9px; font-weight: 600; text-transform: uppercase; color: #555; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <header>
        <div class="logo-container">
            <img src="{{ $logo_url }}" alt="Logo">
        </div>
        <div class="event-details">
            <span>Listado de precios</span>
            <span class="date">{{ $date }}</span>
        </div>
    </header>

    <main>
        <br>
        @for ($i = 0; $i < $numSets; $i++)
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th class="text-center" width="5%">#</th>
                            <th class="text-center" width="15%">SKU</th>
                            <th width="45%">Descripción</th>
                            <th class="text-right" width="15%">Precio Unit.</th>
                            <th class="text-center" width="20%">Solicitado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $index => $product)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="text-center">{{ $product['sku'] }}</td>
                                <td>{{ $product['description'] }}</td>
                                <td class="text-right">$ {{ number_format($product['unit_price'], 2) }}</td>
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