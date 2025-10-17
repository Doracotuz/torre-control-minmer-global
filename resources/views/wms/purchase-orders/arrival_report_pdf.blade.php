<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Arribo - PO {{ $purchaseOrder->po_number }}</title>
    <style>
        :root {
            --primary-color: #1f2937; /* Color principal oscuro */
            --secondary-color: #4f46e5; /* Color de acento (índigo) */
            --text-color: #333;
            --border-color: #dee2e6;
            --header-bg: #f8f9fa;
        }

        body {
            font-family: 'Helvetica', DejaVu Sans, sans-serif;
            font-size: 10px;
            color: var(--text-color);
        }
        
        @page {
            margin: 120px 40px 60px 40px;
        }

        header {
            position: fixed;
            top: -100px;
            left: 0px;
            right: 0px;
            height: 80px;
        }

        footer {
            position: fixed; 
            bottom: -40px; 
            left: 0px; 
            right: 0px;
            height: 50px; 
            text-align: center;
            font-size: 9px;
            color: #888;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        th, td {
            border: 1px solid var(--border-color);
            padding: 5px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }
        
        .table-header {
            background-color: var(--header-bg);
            font-weight: bold;
        }

        h1, h2, h3 {
            color: var(--primary-color);
            margin: 0;
            font-weight: bold;
        }

        h1 { font-size: 20px; }

        h2 {
            font-size: 16px;
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 5px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        
        h3 {
            font-size: 13px;
            margin-top: 15px;
            background-color: var(--header-bg);
            padding: 6px;
            border-left: 3px solid var(--secondary-color);
            margin-bottom: 10px;
        }

        .header-table td {
            border: none;
            vertical-align: middle;
        }

        .logo {
            width: 150px;
        }

        .info-table th {
            width: 25%;
            background-color: var(--header-bg);
        }

        .photo-grid { width: 100%; }
        .photo-grid td { width: 33.33%; text-align: center; border: none; padding: 5px; vertical-align: top; }
        
        .photo-grid img {
            max-width: 100%;
            max-height: 140px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }
        
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    @php
        function embed_image($path) {
            if (!$path || !Storage::exists($path)) { return null; }
            try {
                $file = Storage::get($path);
                $type = Storage::mimeType($path);
                return 'data:' . $type . ';base64,' . base64_encode($file);
            } catch (\Exception $e) { return null; }
        }
    @endphp

    <header>
        <table class="header-table">
            <tr>
                @if($logoBase64)
                <td><img src="{{ $logoBase64 }}" alt="Logo" class="logo"></td>
                @endif
                <td style="text-align: right;"><h1>Reporte de Arribo</h1><p><strong>Orden de Compra:</strong> {{ $purchaseOrder->po_number }}</p><p><strong>Fecha del Reporte:</strong> {{ now()->format('d/m/Y') }}</p></td>
            </tr>
        </table>
    </header>

    <footer>Generado por Control Tower - Estrategias y Soluciones Minmer GLobal.</footer>

    <main>
        <h2>Resumen General del Arribo</h2>
        <table class="info-table">
            <tr><th>Orden de Compra</th><td>{{ $purchaseOrder->po_number }}</td></tr>
            <tr><th>Estatus Final</th><td>{{ $purchaseOrder->status_in_spanish }}</td></tr>
            <tr><th>Contenedor / Factura</th><td>{{ $purchaseOrder->container_number ?? 'N/A' }} / {{ $purchaseOrder->document_invoice ?? 'N/A' }}</td></tr>
            <tr><th>Operador / Placas</th><td>{{ $purchaseOrder->operator_name ?? 'N/A' }} / {{ $purchaseOrder->latestArrival->truck_plate ?? 'N/A' }}</td></tr>
            <tr><th>Fecha de Llegada</th><td>{{ $purchaseOrder->download_start_time ? Carbon\Carbon::parse($purchaseOrder->download_start_time)->format('d/m/Y H:i A') : 'N/A' }}</td></tr>
            <tr><th>Fecha de Salida</th><td>{{ $purchaseOrder->download_end_time ? Carbon\Carbon::parse($purchaseOrder->download_end_time)->format('d/m/Y H:i A') : 'N/A' }}</td></tr>
        </table>

        <h2>Resumen de Recepción (por Calidad)</h2>
        <table>
            <tr class="table-header"><th>SKU</th><th>Producto</th><th>Calidad</th><th>Ordenado</th><th>Recibido</th><th>Cajas Rec.</th><th>Diferencia</th></tr>
            @foreach($summary as $line)
                @php
                    // Agrupar items recibidos para este producto por calidad
                    $receivedByQuality = $purchaseOrder->pallets->flatMap->items
                                         ->where('product.sku', $line->sku)
                                         ->groupBy('quality.name');
                @endphp
                @if($receivedByQuality->isNotEmpty())
                    @foreach($receivedByQuality as $qualityName => $items)
                        @php
                            $quantityReceivedForQuality = $items->sum('quantity');
                            $piecesPerCase = $items->first()->product->pieces_per_case ?? 1;
                            $casesReceivedForQuality = ceil($quantityReceivedForQuality / ($piecesPerCase > 0 ? $piecesPerCase : 1));
                        @endphp
                        <tr>
                            <td>{{ $line->sku }}</td>
                            <td>{{ $line->product_name }}</td>
                            <td><strong>{{ $qualityName }}</strong></td>
                            <td>{{ $loop->first ? number_format($line->quantity_ordered) : '' }}</td>
                            <td>{{ number_format($quantityReceivedForQuality) }}</td>
                            <td>{{ $casesReceivedForQuality }}</td>
                            <td>{{ $loop->first ? number_format($line->quantity_received - $line->quantity_ordered) : '' }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr><td>{{ $line->sku }}</td><td>{{ $line->product_name }}</td><td>N/A</td><td>{{ number_format($line->quantity_ordered) }}</td><td>0</td><td>0</td><td>{{ -$line->quantity_ordered }}</td></tr>
                @endif
            @endforeach
        </table>

        @if($purchaseOrder->pallets->isNotEmpty())
            <div class="page-break"></div>
            <h2>Detalle de Tarimas (LPNs) Recibidas ({{ $purchaseOrder->pallets->count() }} en total)</h2>
            @foreach($purchaseOrder->pallets as $pallet)
                <h3>LPN: {{ $pallet->lpn }}</h3>
                <p style="font-size: 9px;">Recibido por: <strong>{{ $pallet->user->name ?? 'N/A' }}</strong> el {{ $pallet->updated_at->format('d/m/Y h:i A') }}</p>
                <table>
                    <tr class="table-header"><th>SKU</th><th>Producto</th><th>Calidad</th><th>Cantidad</th></tr>
                    @foreach($pallet->items as $item)
                    <tr><td>{{ $item->product->sku ?? '' }}</td><td>{{ $item->product->name ?? '' }}</td><td>{{ $item->quality->name ?? '' }}</td><td>{{ $item->quantity }}</td></tr>
                    @endforeach
                </table>
            @endforeach
        @endif

        @if($purchaseOrder->evidences->isNotEmpty())
            <div class="page-break"></div>
            <h2>Evidencias Fotográficas</h2>
            @foreach($purchaseOrder->evidences->groupBy('type') as $type => $evidences)
                <h3>{{ ucfirst(str_replace('_', ' ', $type)) }}</h3>
                <table class="photo-grid">
                    <tr>
                    @foreach($evidences as $key => $evidence)
                        <td><img src="{{ embed_image($evidence->file_path) }}"><p style="font-size: 8px;">{{ $evidence->original_name }}</p></td>
                        @if(($key + 1) % 3 == 0 && !$loop->last) </tr><tr> @endif
                    @endforeach
                    </tr>
                </table>
            @endforeach
        @endif
    </main>
</body>
</html>