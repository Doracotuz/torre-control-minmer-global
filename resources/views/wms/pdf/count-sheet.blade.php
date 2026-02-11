<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Hoja de Conteo: {{ $session->name }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; }
        .header { width: 100%; border-bottom: 2px solid #2c3856; padding-bottom: 10px; margin-bottom: 20px; }
        .header-table { width: 100%; }
        .header-logo { text-align: left; width: 20%; }
        .header-title { text-align: center; width: 60%; }
        .header-meta { text-align: right; width: 20%; }
        h1 { font-size: 16px; margin: 0; color: #2c3856; text-transform: uppercase; }
        h2 { font-size: 12px; margin: 2px 0; color: #666; }
        .info-box { background: #f3f4f6; padding: 10px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #e5e7eb; }
        .info-table { width: 100%; }
        .info-label { font-weight: bold; color: #666; text-transform: uppercase; font-size: 8px; }
        .info-value { font-weight: bold; color: #2c3856; font-size: 11px; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.data-table th { background: #2c3856; color: white; padding: 8px 5px; text-transform: uppercase; font-size: 8px; text-align: left; }
        table.data-table td { border-bottom: 1px solid #e5e7eb; padding: 8px 5px; vertical-align: middle; }
        table.data-table tr:nth-child(even) { background: #f9fafb; }
        
        .box-input { border: 1px solid #999; height: 20px; width: 60px; display: inline-block; background: white; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 20px 0; border-top: 1px solid #ddd; font-size: 8px; text-align: center; color: #999; }
        
        .check-box { width: 10px; height: 10px; border: 1px solid #333; display: inline-block; margin-right: 5px; }
        .priority-high { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-logo">
                     <h2 style="font-weight:900; font-size: 20px; color:#2c3856;">MINMER</h2>
                </td>
                <td class="header-title">
                    <h1>Hoja de Conteo de Inventario</h1>
                    <h2>{{ $session->warehouse->name }}</h2>
                </td>
                <td class="header-meta">
                    <strong>Fecha:</strong> {{ now()->format('d/m/Y') }}<br>
                    <strong>Hora:</strong> {{ now()->format('H:i') }}
                </td>
            </tr>
        </table>
    </div>

    <div class="info-box">
        <table class="info-table">
            <tr>
                <td>
                    <div class="info-label">Sesión</div>
                    <div class="info-value">{{ $session->name }}</div>
                </td>
                <td>
                    <div class="info-label">Tipo</div>
                    <div class="info-value">{{ Str::upper($session->type) }}</div>
                </td>
                <td>
                    <div class="info-label">Área / Cliente</div>
                    <div class="info-value">{{ $session->area ? $session->area->name : 'GENERAL' }}</div>
                </td>
                <td>
                    <div class="info-label">Responsable</div>
                    <div class="info-value">{{ $session->assignedUser->name ?? 'N/A' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="15%">Ubicación</th>
                <th width="15%">LPN / Tarima</th>
                <th width="10%">SKU</th>
                <th width="25%">Producto</th>
                <th width="10%">Pedimento</th>
                <th width="10%" style="text-align: center;">Cantidad (Físico)</th>
                <th width="15%">Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tasks as $task)
            <tr>
                <td style="font-weight: bold; font-family: monospace; font-size: 11px;">
                    {{ $task->location ? "{$task->location->aisle}-{$task->location->rack}-{$task->location->shelf}-{$task->location->bin}" : 'SIN UBIC.' }}
                </td>
                <td style="font-family: monospace;">{{ $task->pallet->lpn ?? 'N/A' }}</td>
                <td>{{ $task->product->sku }}</td>
                <td>{{ Str::limit($task->product->name, 40) }}</td>
                <td>{{ $task->pallet->purchaseOrder->pedimento_a4 ?? 'N/A' }}</td>
                <td style="text-align: center;">
                    <div class="box-input"></div>
                </td>
                <td>
                    <div style="border-bottom: 1px solid #ccc; height: 15px;"></div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Página <script type="text/php">if (isset($pdf)) { echo $pdf->get_page_number(); }</script>
        | Generado por Sistema WMS Minmer | Documento de uso interno
    </div>
</body>
</html>
