<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><title>Pick List - {{ $pickList->salesOrder->so_number }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; color: #333; }
        .header-table, .details-table { width: 100%; border-collapse: collapse; }
        .header-table td { padding: 5px; vertical-align: middle; }
        .doc-title { font-size: 24px; font-weight: bold; }
        /* CORRECCIÓN: Se define un estilo para el logo */
        .logo { max-height: 60px; width: auto; }
        h2 { font-size: 14px; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-top: 20px; }
        .details-table { margin-top: 15px; }
        .details-table th, .details-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .details-table th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td>
                {{-- CORRECCIÓN: Se aplica la clase .logo a la imagen --}}
                @if($logoBase64) <img src="{{ $logoBase64 }}" alt="Logo" class="logo"> @endif
            </td>
            <td style="text-align: right;">
                <h1 class="doc-title">Pick List</h1>
                <p><strong>Nº de Orden:</strong> {{ $pickList->salesOrder->so_number }}</p>
                <p><strong>Fecha de Emisión:</strong> {{ now()->format('d/m/Y') }}</p>
            </td>
        </tr>
    </table>
    
    <h2>Información del Pedido</h2>
    <p><strong>Cliente:</strong> {{ $pickList->salesOrder->customer_name }}</p>
    <p><strong>Fecha de Orden:</strong> {{ $pickList->salesOrder->order_date->format('d/m/Y') }}</p>

    <h2>Productos a Surtir</h2> 
    <table class="details-table">
        <thead>
            <tr>
                <th>SKU</th><th>Producto</th><th class="font-mono">Ubicación</th>
                <th class="text-right">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pickList->items as $item)
            <tr>
                <td>{{ $item->product->sku ?? 'N/A' }}</td>
                <td>{{ $item->product->name ?? 'N/A' }}</td>
                <td class="font-mono font-bold">
                    @if($item->location)
                        {{ $item->location->aisle }}-{{ $item->location->rack }}-{{ $item->location->shelf }}-{{ $item->location->bin }}
                    @else
                        SIN UBICACIÓN
                    @endif
                </td>
                <td class="text-right font-bold" style="font-size: 14px;">{{ $item->quantity_to_pick }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 60px; text-align: center;">
        <p>Surtidor: _________________________________________</p>
        <p style="font-size: 9px;">(Nombre y Firma)</p>
    </div>
</body>
</html>