<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Financiero WMS</title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #333; line-height: 1.3; }
        .header-tbl { width: 100%; border-bottom: 2px solid #2c3856; padding-bottom: 15px; margin-bottom: 20px; }
        .logo-container { width: 150px; }
        .logo-img { max-width: 100%; height: auto; }
        .doc-title { font-size: 16px; font-weight: bold; color: #2c3856; text-transform: uppercase; text-align: right; }
        .doc-meta { font-size: 10px; color: #666; text-align: right; margin-top: 5px; }
        
        .kpi-row { width: 100%; margin-bottom: 20px; }
        .kpi-card { 
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; 
            padding: 10px; text-align: center; width: 32%; display: inline-block; vertical-align: top; margin-right: 1%;
        }
        .kpi-label { font-size: 9px; text-transform: uppercase; color: #64748b; font-weight: bold; letter-spacing: 0.5px; }
        .kpi-value { font-size: 20px; font-weight: bold; color: #2c3856; margin: 5px 0; }
        .kpi-sub { font-size: 8px; color: #94a3b8; }

        table.data { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 8px; }
        table.data th { background: #2c3856; color: white; padding: 6px; text-align: left; text-transform: uppercase; font-weight: bold; }
        table.data td { border-bottom: 1px solid #e2e8f0; padding: 6px; }
        table.data tr:nth-child(even) { background-color: #f8fafc; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        
        .bar-bg { width: 100%; background: #e2e8f0; height: 8px; border-radius: 4px; overflow: hidden; display: inline-block; vertical-align: middle; }
        .bar-val { height: 100%; background: #2c3856; }
        .bar-val.sec { background: #ff9c00; }
        
        .section-header { font-size: 12px; font-weight: bold; color: #2c3856; border-bottom: 1px solid #ff9c00; margin: 15px 0 10px; padding-bottom: 3px; }
        
        #footer { position: fixed; bottom: -1.3cm; left: 0; right: 0; font-size: 8px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 5px; }
    </style>
</head>
<body>
    @php use Carbon\Carbon; @endphp
    
    <div id="footer">
        © {{ date('Y') }} Torre de Control WMS | Reporte generado el {{ Carbon::now()->format('d/m/Y H:i') }} | Página <script type="text/php">if (isset($pdf)) { $pdf->page_text($pdf->get_width() - 40, $pdf->get_height() - 40, "{PAGE_NUM}", null, 8); }</script>
    </div>

    <table class="header-tbl">
        <tr>
            <td class="logo-container">
                @if(!empty($kpis['logo_base64']))
                    <img src="{{ $kpis['logo_base64'] }}" class="logo-img" alt="Logo">
                @else
                    <h2 style="color: #2c3856; margin: 0;">NEXUS<span style="color: #ff9c00;">WMS</span></h2>
                @endif
            </td>
            <td>
                <div class="doc-title">Reporte Ejecutivo de Facturación</div>
                <div class="doc-meta">
                    Periodo: {{ $kpis['filters']['start_date'] }} - {{ $kpis['filters']['end_date'] }}<br>
                    Cliente: {{ $kpis['filters']['client'] }} | Almacén: {{ $kpis['filters']['warehouse'] }}
                </div>
            </td>
        </tr>
    </table>

    <div style="margin-bottom: 20px;">
        <div class="kpi-card" style="border-bottom: 3px solid #10B981;">
            <div class="kpi-label">Total Facturable</div>
            <div class="kpi-value">${{ number_format($kpis['grand_total'], 2) }}</div>
            <div class="kpi-sub">Almacenaje + Servicios</div>
        </div>
    <div class="kpi-card" style="border-bottom: 3px solid #2c3856;">
            <div class="kpi-label">Servicios (VAS)</div>
            <div class="kpi-value">${{ number_format($kpis['total_vas'], 2) }}</div>
            <div class="kpi-sub">{{ number_format($kpis['metrics_count']) }} Operaciones</div>
        </div>
    <div class="kpi-card" style="border-bottom: 3px solid #ff9c00;">
            <div class="kpi-label">Almacenaje (Est.)</div>
            <div class="kpi-value">${{ number_format($kpis['total_storage'], 2) }}</div>
            <div class="kpi-sub">{{ number_format($kpis['active_pallets']) }} Pallets Activos</div>
        </div>
    </div>

    <div class="section-header">Resumen Operativo</div>
    <table class="data" style="width: 50%; float: left; margin-right: 5%;">
        <tr>
            <td>Entradas (POs)</td>
            <td class="text-right font-bold">{{ number_format($kpis['inbound_pos']) }}</td>
        </tr>
        <tr>
            <td>Salidas (SOs)</td>
            <td class="text-right font-bold">{{ number_format($kpis['outbound_sos']) }}</td>
        </tr>
    </table>
    <table class="data" style="width: 45%; float: left;">
        <tr>
            <td>Piezas Embarcadas</td>
            <td class="text-right font-bold">{{ number_format($kpis['shipped_pieces']) }}</td>
        </tr>
        <tr>
            <td>Cajas Est.</td>
            <td class="text-right font-bold">{{ number_format($kpis['shipped_cases'], 1) }}</td>
        </tr>
    </table>
    <div style="clear: both;"></div>

    <div class="section-header">Desglose de Costos por Concepto</div>
     <table class="data">
        <thead>
            <tr>
                <th>Concepto / Servicio</th>
                <th class="text-right">Cantidad</th>
                <th class="text-right">Costo Unit. (Prom)</th>
                <th style="width: 25%;">Impacto (%)</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = $kpis['grand_total'] > 0 ? $kpis['grand_total'] : 1; @endphp
            
            @foreach($kpis['vas_breakdown'] as $desc => $cost)
            <tr>
                <td>{{ $desc }}</td>
                <td class="text-right">
                    @php 
                        $idx = array_search($desc, $kpis['chart_services']['labels']);
                        $count = $idx !== false ? $kpis['chart_services']['counts'][$idx] : 0;
                    @endphp
                    {{ number_format($count) }}
                </td>
                <td class="text-right">${{ $count > 0 ? number_format($cost / $count, 2) : '0.00' }}</td>
                <td>
                    <div class="bar-bg">
                        <div class="bar-val" style="width: {{ ($cost / $grandTotal) * 100 }}%;"></div>
                    </div>
                    <span style="font-size: 7px; margin-left: 4px;">{{ number_format(($cost / $grandTotal) * 100, 1) }}%</span>
                </td>
                <td class="text-right font-bold">${{ number_format($cost, 2) }}</td>
            </tr>
            @endforeach
            
            <tr style="background-color: #fff7ed;">
                <td class="font-bold" style="color: #c2410c;">Almacenaje Estimado</td>
                <td class="text-right">{{ number_format($kpis['active_pallets']) }} Pallets</td>
                <td class="text-right">-</td>
                <td>
                    <div class="bar-bg">
                        <div class="bar-val sec" style="width: {{ ($kpis['total_storage'] / $grandTotal) * 100 }}%;"></div>
                    </div>
                    <span style="font-size: 7px; margin-left: 4px;">{{ number_format(($kpis['total_storage'] / $grandTotal) * 100, 1) }}%</span>
                </td>
                <td class="text-right font-bold" style="color: #c2410c;">${{ number_format($kpis['total_storage'], 2) }}</td>
            </tr>
            
            <tr style="border-top: 2px solid #2c3856;">
                <td colspan="4" class="text-right font-bold uppercase">Total Facturable</td>
                <td class="text-right font-bold" style="font-size: 11px;">${{ number_format($kpis['grand_total'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <p style="margin-top: 20px; font-style: italic; font-size: 8px;">
        * Nota: Este reporte es una estimación basada en los movimientos registrados en el sistema. 
        Los costos finales pueden variar según ajustes administrativos o impuestos aplicables.
    </p>

</body>
</html>
