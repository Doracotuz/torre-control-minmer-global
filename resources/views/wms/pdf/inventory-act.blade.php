<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Acta de Inventario: {{ $session->name }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; line-height: 1.4; color: #000; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 1px solid #000; padding-bottom: 10px; }
        .logo { font-size: 24px; font-weight: bold; margin-bottom: 5px; }
        .title { font-size: 16px; font-weight: bold; text-transform: uppercase; margin: 0; }
        .subtitle { font-size: 12px; margin: 5px 0; }
        
        .section { margin-bottom: 20px; }
        .section-title { font-size: 12px; font-weight: bold; border-bottom: 1px solid #ccc; padding-bottom: 3px; margin-bottom: 10px; text-transform: uppercase; }
        
        .meta-table { width: 100%; margin-bottom: 15px; }
        .meta-table td { padding: 3px 0; vertical-align: top; }
        .label { font-weight: bold; width: 120px; }
        
        .summary-box { background: #f0f0f0; border: 1px solid #ccc; padding: 10px; margin-bottom: 20px; }
        
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 9px; }
        table.data th { border: 1px solid #000; padding: 5px; background: #eee; text-align: center; font-weight: bold; }
        table.data td { border: 1px solid #000; padding: 4px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .signatures { margin-top: 50px; width: 100%; }
        .signature-box { width: 30%; float: left; margin: 0 1.5%; text-align: center; }
        .signature-line { border-top: 1px solid #000; margin-bottom: 5px; height: 1px; }
        
        .clearfix::after { content: ""; clear: both; display: table; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">MINMER GLOBAL</div>
        <div class="title">Acta de Auditoría de Inventario</div>
        <div class="subtitle">Departamento de Control de Inventarios y Calidad</div>
    </div>

    <div class="section">
        <div class="section-title">I. Datos de la Sesión</div>
        <table class="meta-table">
            <tr>
                <td class="label">Folio de Sesión:</td>
                <td>#{{ str_pad($session->id, 6, '0', STR_PAD_LEFT) }}</td>
                <td class="label">Fecha Impresión:</td>
                <td>{{ now()->format('d/m/Y H:i A') }}</td>
            </tr>
            <tr>
                <td class="label">Almacén:</td>
                <td>{{ $session->warehouse->name }}</td>
                <td class="label">Responsable:</td>
                <td>{{ $session->assignedUser->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Alcance / Tipo:</td>
                <td>{{ Str::upper($session->type) }} @if($session->area) - CLIENTE: {{ $session->area->name }} @endif</td>
                <td class="label">Estado:</td>
                <td>{{ Str::upper($session->status) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">II. Resumen de Resultados</div>
        <div class="summary-box">
            @php
                $totalParams = $session->tasks->count();
                $totalOk = $session->tasks->where('status', 'resolved')->count();
                $totalDisc = $session->tasks->where('status', 'discrepancy')->count();
                $accuracy = $totalParams > 0 ? ($totalOk / $totalParams) * 100 : 0;
            @endphp
            <table style="width: 100%; text-align: center;">
                <tr>
                    <td>
                        <strong>Total Posiciones Auditadas</strong><br>
                        {{ $totalParams }}
                    </td>
                    <td>
                        <strong>Coincidencias (OK)</strong><br>
                        {{ $totalOk }}
                    </td>
                    <td>
                        <strong>Discrepancias Finales</strong><br>
                        {{ $totalDisc }}
                    </td>
                    <td>
                        <strong>Exactitud de Inventario (IRA)</strong><br>
                        {{ number_format($accuracy, 2) }}%
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">III. Detalle de Ajustes y Discrepancias</div>
        @php
            $discrepancies = $session->tasks->filter(function($task) {
                // Include resolved tasks that had adjustments (source_id IS NOT NULL logic usually, or check expected vs last count)
                // Or just show current discrepancies clearly.
                // Let's show currently open discrepancies OR resolved ones where quantity changed.
                $lastRec = $task->records->last();
                return $task->status == 'discrepancy' || ($lastRec && $lastRec->counted_quantity != $task->expected_quantity);
            });
        @endphp
        
        @if($discrepancies->isEmpty())
            <p style="text-align: center; font-style: italic;">No se encontraron discrepancias ni ajustes en esta sesión. Inventario 100% Correcto.</p>
        @else
            <table class="data">
                <thead>
                    <tr>
                        <th width="15%">Ubicación</th>
                        <th width="15%">LPN</th>
                        <th width="10%">SKU</th>
                        <th width="25%">Producto</th>
                        <th width="10%">Sistema</th>
                        <th width="10%">Físico</th>
                        <th width="10%">Diferencia</th>
                        <th width="5%">Estatus</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($discrepancies as $task)
                    @php
                        $lastRec = $task->records->last();
                        $counted = $lastRec ? $lastRec->counted_quantity : 0;
                        $diff = $counted - $task->expected_quantity;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $task->location ? $task->location->code : 'N/A' }}</td>
                        <td class="text-center">{{ $task->pallet->lpn ?? 'N/A' }}</td>
                        <td>{{ $task->product->sku }}</td>
                        <td>{{ Str::limit($task->product->name, 30) }}</td>
                        <td class="text-center">{{ $task->expected_quantity }}</td>
                        <td class="text-center">{{ $counted }}</td>
                        <td class="text-center" style="font-weight: bold; color: {{ $diff < 0 ? 'red' : ($diff > 0 ? 'blue' : 'black') }}">
                            {{ $diff > 0 ? '+'.$diff : $diff }}
                        </td>
                        <td class="text-center">
                            @if($task->status == 'resolved') <span style="color:green">AJUSTADO</span>
                            @else <span style="color:red">PENDIENTE</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="section" style="margin-top: 50px;">
        <p style="font-size: 10px; margin-bottom: 40px; text-align: justify;">
            Por medio de la presente se certifica que el inventario físico fue realizado de acuerdo a los procedimientos establecidos. 
            Los ajustes resultantes han sido revisados y autorizados por <strong>Estrategias y Soluciones Minmer Global PC33</strong> 
            @if($session->area && $session->area->emitter_name)
                y <strong>{{ $session->area->emitter_name }}</strong>
            @endif.
        </p>
        
        <div class="signatures clearfix">
            <div class="signature-box">
                <div class="signature-line"></div>
                <strong>{{ $session->assignedUser->name ?? 'Responsable' }}</strong><br>
                <span style="font-size: 9px;">REALIZÓ CONTEO (MINMER)</span>
            </div>
            
            <div class="signature-box">
                <div class="signature-line"></div>
                <strong>{{ Auth::user()->name }}</strong><br>
                <span style="font-size: 9px;">SUPERVISOR WMS (MINMER)</span>
            </div>
            
            <div class="signature-box">
                <div class="signature-line"></div>
                @if($session->area && $session->area->emitter_name)
                    <strong>{{ $session->area->emitter_name }}</strong><br>
                @else
                    <strong>CLIENTE / AUDITORÍA</strong><br>
                @endif
                <span style="font-size: 9px;">REVISÓ Y AUTORIZÓ</span>
            </div>
        </div>
    </div>
</body>
</html>
