<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Auditoría - Guía {{ $guia->guia }}</title>
    <style>
        :root {
            --primary-color: #2c3856;
            --secondary-color: #ff9c00;
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

        h1, h2, h3, h4 {
            color: var(--primary-color);
            margin-top: 0;
            margin-bottom: 10px;
            font-weight: bold;
        }

        h2 {
            font-size: 16px;
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 5px;
            margin-top: 20px;
        }
        
        h3 {
            font-size: 13px;
            margin-top: 15px;
            background-color: var(--header-bg);
            padding: 6px;
            border-left: 3px solid var(--secondary-color);
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
            width: auto;
            height: auto;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }

        .check-item { font-size: 10px; }
        .check-item .icon { font-size: 12px; }
        .check-item .checked { color: #28a745; }
        .check-item .unchecked { color: #dc3545; }
        
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    @php
        function embed_image($path) {
            if (!$path || !Storage::disk('s3')->exists($path)) { return null; }
            try {
                $file = Storage::disk('s3')->get($path);
                $type = Storage::disk('s3')->mimeType($path);
                return 'data:' . $type . ';base64,' . base64_encode($file);
            } catch (\Exception $e) { return null; }
        }

        $firstPlanning = $guia->plannings->first();
        $firstOrder = $firstPlanning?->order;
        $mainAudit = $firstOrder?->audits->where('location', $firstPlanning?->origen)->first();
        
        $patioData = $mainAudit?->patio_audit_data ?? [];
        $loadingData = $mainAudit?->loading_audit_data ?? [];
    @endphp

    <header>
        <table class="header-table">
            <tr>
                <td><img src="{{ embed_image(parse_url($logoUrl, PHP_URL_PATH)) }}" alt="Logo" class="logo"></td>
                <td style="text-align: right;"><h1>Reporte de Auditoría</h1><p><strong>Guía:</strong> {{ $guia->guia }}</p><p><strong>Fecha del Reporte:</strong> {{ now()->format('d/m/Y') }}</p></td>
            </tr>
        </table>
    </header>

    <footer>Generado por Torre de Control Minmer Global.</footer>

    <main>
        <h2>Resumen General del Envío</h2>
        <table class="info-table">
            <tr><th>Guía</th><td>{{ $guia->guia }}</td></tr>
            <tr><th>Estatus Final</th><td>{{ $guia->estatus }}</td></tr>
            <tr><th>Operador</th><td>{{ $guia->operador }}</td></tr>
            <tr><th>Placas</th><td>{{ $guia->placas }}</td></tr>
            <tr><th>Órdenes (SOs)</th><td>{{ $guia->plannings->pluck('order.so_number')->filter()->unique()->join(', ') }}</td></tr>
        </table>

        <h2>Fase 1: Auditoría de Almacén</h2>
        @foreach($guia->plannings as $planning)
            @if($order = $planning->order)
            @php
                $audit = $order->audits->where('location', $planning->origen)->first();
                $warehouseData = $audit?->warehouse_audit_data ?? [];
                $warehouseEvent = $order->events->first(fn($e) => str_contains($e->description, 'Auditoría de almacén completada'));
            @endphp
            <h3>Resultados para SO: {{ $order->so_number }} (Ubicación: {{ $audit->location ?? 'N/A' }})</h3>
            <p class="text-xs text-gray-500">Auditado por: <strong>{{ $warehouseEvent->user->name ?? 'N/A' }}</strong></p>
            <p><strong>Observaciones:</strong> {{ $warehouseData['observaciones'] ?? 'Ninguna.' }}</p>
            @if(!empty($warehouseData['items']))
                <table class="info-table">
                    <tr class="table-header"><th>SKU</th><th>Calidad</th><th>SKU OK?</th><th>Pzs OK?</th><th>UPC OK?</th></tr>
                    @foreach($warehouseData['items'] as $detailId => $itemData)
                    @php $detail = \App\Models\CsOrderDetail::find($detailId); @endphp
                    @if($detail)
                    <tr class="check-item">
                        <td>{{ $detail->sku }}</td>
                        <td>{{ $itemData['calidad'] ?? 'N/A' }}</td>
                        <td class="{{ isset($itemData['sku_validado']) ? 'checked' : 'unchecked' }}"><span class="icon">✔</span></td>
                        <td class="{{ isset($itemData['piezas_validadas']) ? 'checked' : 'unchecked' }}"><span class="icon">✔</span></td>
                        <td class="{{ isset($itemData['upc_validado']) ? 'checked' : 'unchecked' }}"><span class="icon">✔</span></td>
                    </tr>
                    @endif
                    @endforeach
                </table>
            @endif
            @endif
        @endforeach
        
        <h2>Fase 2: Auditoría de Patio</h2>
        @php
            $patioEvent = $guia->plannings->pluck('order')->flatten()->pluck('events')->flatten()->first(fn($e) => str_contains($e->description, 'Auditoría de patio completada'));
        @endphp
        <p class="text-xs text-gray-500">Auditado por: <strong>{{ $patioEvent->user->name ?? 'N/A' }}</strong></p>                
        <table class="info-table">
            <tr><th>Fecha y Hora de Arribo</th><td>{{ $patioData['arribo_completo'] ?? ($patioData['arribo_fecha'] ?? 'N/A') }}</td></tr>
            <tr><th>Estado de la Caja</th><td>{{ $patioData['caja_estado'] ?? 'N/A' }}</td></tr>
            <tr><th>Estado de Llantas</th><td>{{ $patioData['llantas_estado'] ?? 'N/A' }}</td></tr>
            <tr><th>Nivel de Combustible</th><td>{{ $patioData['combustible_nivel'] ?? 'N/A' }}</td></tr>
            <tr><th>Equipo de Sujeción</th><td>{{ $patioData['equipo_sujecion'] ?? 'N/A' }}</td></tr>
            <tr><th>Presenta Maniobra</th><td>{{ isset($patioData['presenta_maniobra']) && $patioData['presenta_maniobra'] ? 'Sí (' . ($patioData['maniobra_personas'] ?? 'N/A') . 'p)' : 'No' }}</td></tr>
        </table>

        <h3>Fotos de Patio</h3>

        @php
            $fotoUnidadUrl = $patioData['foto_unidad_path'] ?? null;
            $fotoLlantasUrl = $patioData['foto_llantas_path'] ?? null;

            if (!$fotoUnidadUrl && isset($patioData['audit_patio_fotos'])) {
                $oldFotos = is_string($patioData['audit_patio_fotos']) ? json_decode($patioData['audit_patio_fotos'], true) : $patioData['audit_patio_fotos'];
                $fotoUnidadUrl = $oldFotos['unidad'] ?? null;
                $fotoLlantasUrl = $oldFotos['llantas'] ?? null;
            }
        @endphp

        <table class="photo-grid">
            <tr>
                @if($fotoUnidadUrl)
                    <td>
                        <p>Unidad</p>
                        <img src="{{ embed_image($fotoUnidadUrl) }}">
                    </td>
                @endif
                @if($fotoLlantasUrl)
                    <td>
                        <p>Llantas</p>
                        <img src="{{ embed_image($fotoLlantasUrl) }}">
                    </td>
                @endif
            </tr>
        </table>

        <div class="page-break"></div>

        <h2>Fase 3: Auditoría de Carga</h2>
        @php
            $loadingEvent = $guia->plannings->pluck('order')->flatten()->pluck('events')->flatten()->first(fn($e) => str_contains($e->description, 'Auditoría de carga finalizada'));
        @endphp
        <p class="text-xs text-gray-500">Auditado por: <strong>{{ $loadingEvent->user->name ?? 'N/A' }}</strong></p>       
        <table class="info-table">
            <tr><th>Marchamo</th><td>{{ $loadingData['marchamo_numero'] ?? 'N/A' }}</td></tr>
            <tr><th>Custodia</th><td>{{ isset($loadingData['lleva_custodia']) && $loadingData['lleva_custodia'] ? 'Sí' : 'No' }}</td></tr>
            @if(isset($loadingData['incluye_tarimas']) && $loadingData['incluye_tarimas'])
            <tr><th>Tarimas Chep</th><td>{{ $loadingData['tarimas_cantidad_chep'] ?? 0 }}</td></tr>
            <tr><th>Tarimas Estándar</th><td>{{ $loadingData['tarimas_cantidad_estandar'] ?? 0 }}</td></tr>
            @else
            <tr><th>Incluye Tarimas</th><td>No</td></tr>
            @endif
        </table>

        @php $validatedSpecs = $loadingData['validated_specs'] ?? []; @endphp
        @if(!empty($validatedSpecs))
            <h3>Checklist de Especificaciones</h3>
            @foreach($validatedSpecs as $customerName => $specs)
                <h4>Cliente: {{ $customerName }}</h4>
                <table class="info-table">
                    @foreach($specs as $spec => $was_checked)
                    <tr class="check-item">
                        <th style="width: 75%;">{{ str_replace([' - Entrega', ' - Documentación'], '', $spec) }}</th>
                        <td style="width: 25%;" class="{{ $was_checked ? 'checked' : 'unchecked' }}"><span class="icon">{{ $was_checked ? '✔ Cumplido' : '✘ No Cumplido' }}</span></td>
                    </tr>
                    @endforeach
                </table>
            @endforeach
        @endif

        <h3>Incidencias Registradas</h3>
        @if($guia->incidencias->isNotEmpty())
            <ul>@foreach($guia->incidencias as $incidencia)<li>{{ $incidencia->tipo_incidencia }}</li>@endforeach</ul>
        @else
            <p>No se registraron incidencias.</p>
        @endif

        <h3>Fotos de Carga</h3>
        @php $cargaFotos = $loadingData['audit_carga_fotos'] ?? []; @endphp
        <table class="photo-grid">
            <tr>
                @if(!empty($cargaFotos['caja_vacia']))
                    <td>
                        <p>Caja Vacía</p>
                        <img src="{{ embed_image($cargaFotos['caja_vacia']) }}">
                    </td>
                @endif
                @if(!empty($cargaFotos['marchamo']))
                    <td>
                        <p>Marchamo</p>
                        <img src="{{ embed_image($cargaFotos['marchamo']) }}">
                    </td>
                @endif
            </tr>
        </table>

        @if(!empty($cargaFotos['proceso_carga']))
            <h4 style="margin-top: 15px; margin-bottom: 5px; font-weight: bold;">Fotos del Proceso de Carga</h4>
            <table class="photo-grid">
                <tr>
                @foreach($cargaFotos['proceso_carga'] as $key => $fotoPath)
                    <td>
                        <p>Proceso de carga {{ $key + 1 }}</p>
                        <img src="{{ embed_image($fotoPath) }}">
                    </td>

                    @if(($key + 1) % 3 == 0 && !$loop->last)
                        </tr><tr>
                    @endif
                @endforeach
                </tr>
            </table>
        @endif
    </main>
</body>
</html>