<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte Ejecutivo Estratégico</title>
    <style>
        @page { margin: 0cm 0cm; }
        body { margin-top: 3.5cm; margin-bottom: 1cm; margin-left: 1cm; margin-right: 1cm; font-family: 'Helvetica', 'Arial', sans-serif; color: #334155; line-height: 1.4; }
        table { width: 100%; border-collapse: collapse; border-spacing: 0; }
        td, th { vertical-align: top; padding: 0; }
        
        .header-bg { position: fixed; top: 0; left: 0; width: 100%; height: 3cm; background-color: #1e293b; border-bottom: 3px solid #f59e0b; z-index: -1; }
        .header-content { position: fixed; top: 0cm; left: 1cm; right: 1cm; color: #ffffff; height: 3cm; }
        .client-title { font-size: 22px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin: 0; }
        .report-meta { font-size: 9px; text-transform: uppercase; opacity: 0.8; letter-spacing: 1px; margin-bottom: 5px; }
        
        .logo-container { 
            width: 180px;
            text-align: right; 
            vertical-align: middle; 
            height: 3cm; 
            padding-right: 70px;
        }
        .logo-img { height: 1.8cm; width: auto; max-width: 140px; background: #fff; padding: 5px; border-radius: 4px; object-fit: contain; }
        
        .section-container { margin-bottom: 25px; break-inside: avoid; }
        .section-title { font-size: 12px; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; }
        .content-box { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05); }

        .kpi-row { margin-bottom: 20px; }
        .kpi-card { background: #fff; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .kpi-title { font-size: 9px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .kpi-value { font-size: 20px; font-weight: 800; color: #1e293b; margin: 5px 0; }
        .kpi-sub { font-size: 9px; color: #94a3b8; }
        .trend-up { color: #10b981; } .trend-down { color: #ef4444; }

        .chart-container { width: 100%; height: 200px; text-align: center; overflow: hidden; }
        .chart-img-trend { width: 100%; height: auto; max-height: 200px; object-fit: contain; }
        .chart-img-pie { width: 160px; height: 160px; }

        .clean-table th { text-align: left; font-size: 9px; font-weight: 600; color: #64748b; text-transform: uppercase; padding: 8px 5px; border-bottom: 1px solid #e2e8f0; }
        .clean-table td { font-size: 10px; padding: 10px 5px; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
        .clean-table tr:last-child td { border-bottom: none; }
        .prog-bar-bg { height: 4px; background: #f1f5f9; border-radius: 2px; margin-top: 5px; overflow: hidden; }
        .prog-bar-fill { height: 100%; background: #f59e0b; border-radius: 2px; }

        .heatmap-cell { text-align: center; padding: 5px; }
        .heatmap-box { width: 100%; height: 35px; border-radius: 4px; margin-bottom: 5px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 10px; border: 1px solid rgba(0,0,0,0.05); }
        
        .insight-panel { background-color: #f8fafc; border-left: 4px solid #f59e0b; padding: 15px; border-radius: 0 6px 6px 0; font-size: 10px; color: #475569; display: flex; align-items: center; }
        .footer { position: fixed; bottom: 0cm; left: 0cm; right: 0cm; height: 0.8cm; background: #f8fafc; color: #94a3b8; font-size: 8px; text-align: center; line-height: 0.8cm; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="header-bg"></div>
    <table class="header-content">
        <tr>
            <td style="vertical-align: middle;">
                <div class="report-meta">Reporte Ejecutivo Estratégico | {{ $report_date }}</div>
                <h1 class="client-title">{{ Str::limit($clientName, 50) }}</h1>
                <div style="font-size: 10px; margin-top: 5px; opacity: 0.9;">
                    Antigüedad: <strong>{{ $antiguedad }}</strong> | Estado: 
                    <span style="color: {{ $diasInactivo > 45 ? '#fca5a5' : '#86efac' }}; font-weight: bold;">
                        {{ $diasInactivo > 45 ? 'Inactivo (' . $diasInactivo . ' días)' : 'Activo' }}
                    </span>
                </div>
            </td>
            <td class="logo-container">
                @if($logoBase64)
                <img src="{{ $logoBase64 }}" class="logo-img">
                @endif
            </td>
        </tr>
    </table>

    <table class="kpi-row" cellspacing="10">
        <tr>
            <td width="25%">
                <div class="kpi-card">
                    <div class="kpi-title">Valor Total</div>
                    <div class="kpi-value">${{ number_format($baseMetrics->total_valor, 0) }}</div>
                    <div class="kpi-sub trend-up">Ingresos Brutos</div>
                </div>
            </td>
            <td width="25%">
                <div class="kpi-card">
                    <div class="kpi-title">Ticket Promedio</div>
                    <div class="kpi-value">${{ number_format($baseMetrics->ticket_promedio, 0) }}</div>
                    <div class="kpi-sub">Por Transacción</div>
                </div>
            </td>
            <td width="25%">
                <div class="kpi-card">
                    <div class="kpi-title">Volumen Total</div>
                    <div class="kpi-value">{{ number_format($baseMetrics->total_unidades) }}</div>
                    <div class="kpi-sub">Unidades Movidas</div>
                </div>
            </td>
            <td width="25%">
                <div class="kpi-card">
                    <div class="kpi-title">Transacciones</div>
                    <div class="kpi-value">{{ number_format($baseMetrics->total_transacciones) }}</div>
                    <div class="kpi-sub">Pedidos Totales</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-container">
        <table cellspacing="10">
            <tr>
                <td width="65%">
                    <div class="content-box" style="height: 260px;">
                        <div class="section-title">Tendencia Financiera (Últimos 12 Meses)</div>
                        <div class="chart-container" style="padding-top: 20px;">
                            @if($svgTrend)
                                <img src="{{ $svgTrend }}" class="chart-img-trend">
                            @else
                                <div style="color: #94a3b8; margin-top: 80px;">Datos históricos insuficientes para graficar.</div>
                            @endif
                        </div>
                    </div>
                </td>
                <td width="35%">
                    <div class="content-box" style="height: 260px;">
                        <div class="section-title">Distribución por Sede</div>
                        <table style="margin-top: 15px;">
                            <tr>
                                <td align="center" style="padding-bottom: 15px;">
                                    @if($svgPie)
                                        <img src="{{ $svgPie }}" class="chart-img-pie">
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <table class="clean-table" style="font-size: 9px;">
                                    @php $colors = ['#1e293b', '#f59e0b', '#334155', '#d97706', '#94a3b8']; $i=0; @endphp
                                    @forelse($locData as $name => $val)
                                        <tr>
                                            <td width="15"><div style="width:8px;height:8px;border-radius:2px;background:{{$colors[$i%5]}}"></div></td>
                                            <td>{{ Str::limit($name, 18) }}</td>
                                            <td align="right" style="font-weight:bold;">{{ number_format(($val/$totalLoc)*100, 1) }}%</td>
                                        </tr>
                                        @php $i++; @endphp
                                    @empty
                                        <tr><td colspan="3" align="center" style="color:#94a3b8">Sin datos de ubicación</td></tr>
                                    @endforelse
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-container">
        <table cellspacing="10">
            <tr>
                <td width="60%">
                    <div class="content-box">
                        <div class="section-title">Top 5 Productos por Valor</div>
                        <table class="clean-table">
                            <thead>
                                <tr>
                                    <th width="55%">SKU / Producto</th>
                                    <th width="20%" style="text-align: right;">Volumen</th>
                                    <th width="25%" style="text-align: right;">Valor Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topProducts as $p)
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; font-size: 11px;">{{ $p->sku }}</div>
                                        <div style="font-size: 9px; color: #64748b;">{{ Str::limit($p->description, 40) }}</div>
                                    </td>
                                    <td align="right">{{ number_format($p->cantidad) }}</td>
                                    <td align="right">
                                        <div style="font-weight: 700;">${{ number_format($p->monto, 0) }}</div>
                                        <div class="prog-bar-bg"><div class="prog-bar-fill" style="width: {{ $p->percent }}%"></div></div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="3" align="center" style="color:#94a3b8; padding: 20px;">No hay datos de productos.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </td>
                <td width="40%">
                    <div class="content-box">
                        <div class="section-title">Patrón de Pedidos Semanal</div>
                        <table style="margin-top: 20px; border-spacing: 2px;">
                            <tr>
                                @foreach($chartWeek as $d)
                                <td class="heatmap-cell">
                                    <div class="heatmap-box" style="background-color: {{ $d['color_bg'] }}; color: {{ $d['color_txt'] }};">
                                        {{ $d['val'] > 0 ? $d['val'] : '' }}
                                    </div>
                                    <div style="font-size: 8px; color: #64748b; font-weight: 600;">{{ $d['label'] }}</div>
                                </td>
                                @endforeach
                            </tr>
                        </table>
                        <div style="margin-top: 20px; padding: 10px; background: #f8fafc; border-radius: 4px; font-size: 9px; color: #64748b;">
                            <strong style="color: #1e293b;">Análisis de Frecuencia:</strong> El mapa de calor muestra la concentración de actividad transaccional por día de la semana. Los tonos más oscuros y dorados indican los días de mayor operación.
                        </div>
                    </div>
                    <div class="insight-panel" style="margin-top: 10px;">
                        <div>
                            <strong style="color: #1e293b; text-transform: uppercase;">Resumen Ejecutivo:</strong>
                            El cliente muestra una actividad {{ $diasInactivo > 30 ? 'decreciente, requiere atención comercial' : 'constante y saludable' }}. 
                            Su ticket promedio de <strong>${{ number_format($baseMetrics->ticket_promedio, 0) }}</strong> sugiere un perfil de compra {{ $baseMetrics->ticket_promedio > 5000 ? 'alto' : 'estándar' }}.
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Confidence Level: High | System Generated Report | {{ date('Y') }} © Control Tower Analytics. All rights reserved.
    </div>
</body>
</html>