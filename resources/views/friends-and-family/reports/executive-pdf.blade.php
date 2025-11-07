<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de evento Friends & Family</title>
    <style>
        @page { margin: 1.5cm; }
        :root {
            --color-primary: #2c3856;
            --color-accent: #ff9c00;
            --color-secondary: #4a5568;
            --color-border-light: #e2e8f0;
            --color-border-dark: #cbd5e0;
            --color-bg-light: #f7fafc;
            --color-success: #28a745;
            --color-warning: #dd6b20;
            --color-danger: #e53e3e;
            --color-inprogress: #3182ce;
        }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: var(--color-secondary); line-height: 1.4; }
        #footer { position: fixed; bottom: -1.5cm; left: -1.5cm; right: -1.5cm; height: 1.2cm; padding: 5px 1.5cm; background-color: white; z-index: 100; }
        #footer .footer-line { height: 3px; background: linear-gradient(to right, var(--color-primary), var(--color-accent)); }
        #footer table { width: 100%; font-size: 8px; color: var(--color-secondary); margin-top: 5px; }
        #header { border-bottom: 2px solid var(--color-border-light); padding-bottom: 12px; }
        #header table { width: 100%; }
        #header .logo { width: 150px; }
        #header .doc-info { text-align: right; vertical-align: top; }
        .doc-title { font-size: 18px; font-weight: bold; color: var(--color-primary); margin: 0; text-transform: uppercase; }
        .doc-subtitle { font-size: 11px; margin: 4px 0; color: var(--color-secondary); }
        .page-break { page-break-before: always; }

        .section-title {
            font-size: 14px; color: var(--color-primary); font-weight: bold; text-transform: uppercase;
            letter-spacing: 0.8px; border-bottom: 2px solid var(--color-accent);
            padding-bottom: 6px; margin: 20px 0 10px 0; page-break-after: avoid;
        }
        .section-title.first { margin-top: 0; }
        .subsection-title {
             font-size: 11px; color: var(--color-primary); font-weight: bold;
             margin: 15px 0 5px 0; border-bottom: 1px solid var(--color-border-dark); padding-bottom: 3px;
             page-break-after: avoid;
        }

        .diag-box {
            padding: 12px 15px; border-radius: 5px; font-size: 10px; font-weight: bold;
            line-height: 1.5; margin: 15px 0; border: 1px solid; page-break-inside: avoid;
        }
        .diag-box strong { text-transform: uppercase; }
        .diag-box.success { background-color: #f0fff4; color: #2f855a; border-color: #9ae6b4; }
        .diag-box.warning { background-color: #fffaf0; color: #c05621; border-color: #fbd38d; }
        .diag-box.danger { background-color: #fff5f5; color: #c53030; border-color: #feb2b2; }

        .kpi-card {
            background-color: var(--color-bg-light); border: 1px solid var(--color-border-light);
            border-radius: 8px; padding: 10px; vertical-align: top; box-sizing: border-box;
            text-align: center; height: 70px;
        }
        .kpi-title { font-size: 9px; text-transform: uppercase; font-weight: bold; color: var(--color-primary); }
        .kpi-main { font-size: 20px; font-weight: bold; margin: 8px 0; }
        .kpi-main.success { color: var(--color-success); }
        .kpi-main.danger { color: var(--color-danger); }
        .kpi-main.info { color: var(--color-inprogress); }
        .kpi-main.warning { color: var(--color-warning); }
        .kpi-subtext { font-size: 9px; }
        
        .styled-table { width: 100%; border-collapse: collapse; margin-top: 10px; page-break-inside: auto; }
        .styled-table th, .styled-table td { padding: 8px; border-bottom: 1px solid var(--color-border-light); text-align: left; }
        .styled-table th { background-color: var(--color-bg-light); font-weight: bold; color: var(--color-primary); font-size: 9px; text-transform: uppercase; }
        .styled-table tr { page-break-inside: avoid; }
        .styled-table .total-row { background-color: var(--color-bg-light); border-top: 2px solid var(--color-border-dark); font-weight: bold; }
        .text-right { text-align: right !important; }
        .text-danger { color: var(--color-danger); }
        .text-warning { color: var(--color-warning); }
        .text-primary { color: var(--color-primary); }
        .text-secondary { color: var(--color-secondary); }

        .workload-chart { width: 100%; border-spacing: 0 10px; }
        .workload-label { width: 35%; font-weight: bold; padding-right: 10px; vertical-align: middle; font-size: 9px; }
        .workload-bar-cell { width: 65%; vertical-align: middle; }
        .workload-bar-bg { background-color: var(--color-border-light); border-radius: 5px; height: 16px; font-size: 0; }
        .workload-bar-fill {
            display: inline-block; height: 100%; font-size: 9px; color: white; line-height: 16px;
            text-align: right; font-weight: bold; border-radius: 5px;
            padding-right: 5px; box-sizing: border-box;
        }
        
        .task-bar-container {
            width: 100%; height: 20px;
            background-color: var(--color-border-dark);
            border-radius: 5px;
            overflow: hidden;
            display: block;
            font-size: 0;
            margin-top: 10px;
        }
        .task-bar-segment {
            display: inline-block;
            height: 100%;
            font-size: 10px;
            color: white;
            font-weight: bold;
            line-height: 20px;
            text-align: center;
        }
        .task-bar-legend { font-size: 9px; margin-top: 8px; }
        .legend-item { margin-right: 10px; display: inline-block; }
        .dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 4px; vertical-align: middle; }
    </style>
</head>
<body>
    @php use Carbon\Carbon; Carbon::setLocale('es'); @endphp

    <footer id="footer">
        <div class="footer-line"></div>
        <table><tr>
            <td>Reporte "Friends & Family" | Documento Informativo</td>
            <td style="text-align: right;" class="page-number"></td>
        </tr></table>
        <script type="text/php">
            if (isset($pdf)) {
                $text = "Página {PAGE_NUM} de {PAGE_COUNT}"; $size = 8; $font = $fontMetrics->getFont("DejaVu Sans");
                $width = $fontMetrics->get_text_width($text, $font, $size); $x = $pdf->get_width() - $width - 40;
                $y = $pdf->get_height() - 34; $pdf->page_text($x, $y, $text, $font, $size);
            }
        </script>
    </footer>

    <header id="header">
        <table><tr>
            <td style="width: 50%;">@if(isset($logo_base_64)) <img src="{{ $logo_base_64 }}" alt="Logo" class="logo"> @endif</td>
            <td style="width: 50%;" class="doc-info">
                <h1 class="doc-title">Reporte Ejecutivo</h1>
                <p class="doc-subtitle">Evento "Friends & Family"</p>
                <p class="doc-subtitle">Generado: {{ $report_date }}</p>
                <p class="doc-subtitle" style="font-weight: bold;">Filtro: {{ $user_filter_name }}</p>
            </td>
        </tr></table>
    </header>

    <main>
        
        <h2 class="section-title first">Dashboard</h2>
        
        <div class="diag-box {{ $diagnosis['level'] }}">
            <strong>Diagnóstico:</strong> {{ $diagnosis['message'] }}
        </div>

        <table style="width: 100%; border-spacing: 12px 0; margin-left: -12px;">
             <tr>
                <td style="width: 25%;"><div class="kpi-card">
                    <div class="kpi-title">Ingreso Bruto</div>
                    <div class="kpi-main info" style="font-size: 18px;">${{ number_format($kpis['valorTotalVendido'], 2) }}</div>
                    <div class="kpi-subtext">{{ $kpis['totalVentas'] }} Ventas</div>
                </div></td>
                <td style="width: 25%;"><div class="kpi-card">
                    <div class="kpi-title">Unidades Vendidas</div>
                    <div class="kpi-main success">{{ number_format($kpis['totalUnidadesVendidas']) }}</div>
                    <div class="kpi-subtext">{{ number_format($kpis['unidadesPorVenta'], 1) }} unids/venta</div>
                </div></td>
                <td style="width: 25%;"><div class="kpi-card">
                    <div class="kpi-title">Ticket Promedio</div>
                    <div class="kpi-main info" style="font-size: 18px;">${{ number_format($kpis['ticketPromedio'], 2) }}</div>
                    <div class="kpi-subtext">Promedio por venta</div>
                </div></td>
                <td style="width: 25%;"><div class="kpi-card">
                    <div class="kpi-title">Agotados / Alertas</div>
                    <div class="kpi-main"><span class="danger">{{ $kpis['stockAgotadoCount'] }}</span> / <span class="warning">{{ $kpis['lowStockAlertsCount'] }}</span></div>
                    <div class="kpi-subtext">Stock <= 0 / Stock 1-9</div>
                </div></td>
            </tr>
        </table>


        <table style="width: 100%; border-spacing: 15px 0; margin-left: -15px; margin-top: 15px;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    
                    <h3 style="font-size: 11px; color: var(--color-primary); font-weight: bold;">Top 5 Vendedores (por Valor de Venta)</h3>
                    <table class="workload-chart" style="margin-top: 10px;">
                        @php $max_valor = max(1, $ventasPorVendedor->max('valor_total')); @endphp
                        @forelse($ventasPorVendedor->take(5) as $item)
                        <tr>
                            <td class="workload-label">{{ $item->user_name }}</td>
                            <td class="workload-bar-cell">
                                <div class="workload-bar-bg">
                                    <div class="workload-bar-fill" style="width: {{ ($item->valor_total / $max_valor) * 100 }}%; background-color: var(--color-primary);">
                                        ${{ number_format($item->valor_total, 0) }}
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td style="text-align: center; padding: 10px;">Sin datos</td></tr>
                        @endforelse
                    </table>

                    <h3 style="font-size: 11px; color: var(--color-primary); font-weight: bold; margin-top: 20px;">Top 5 Productos (por pieza)</h3>
                    <table class="workload-chart" style="margin-top: 10px;">
                        @php $max_prod = max(1, $topProductos->max('total_vendido')); @endphp
                        @forelse($topProductos->take(5) as $item)
                        <tr>
                            <td class="workload-label">{{ $item->sku }}</td>
                            <td class="workload-bar-cell">
                                <div class="workload-bar-bg">
                                    <div class="workload-bar-fill" style="width: {{ ($item->total_vendido / $max_prod) * 100 }}%; background-color: var(--color-accent);">
                                        {{ number_format($item->total_vendido) }}
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td style="text-align: center; padding: 10px;">Sin datos</td></tr>
                        @endforelse
                    </table>

                </td>
                
                <td style="width: 50%; vertical-align: top;">
                    
                    <h3 style="font-size: 11px; color: var(--color-primary); font-weight: bold;">Distribución de SKU's</h3>
                    @php
                        $colors = ['#2c3856', '#ff9c00', '#666666', '#4a5568'];
                        $i = 0;
                    @endphp
                    <div class="task-bar-container">
                        @foreach($priceRanges as $range => $data)
                            @if($data['percent'] > 0)
                            <div class="task-bar-segment" style="width: {{ $data['percent'] }}%; background-color: {{ $colors[$i % count($colors)] }};">
                                {{ $data['percent'] > 15 ? number_format($data['percent'], 0).'%' : '' }}
                            </div>
                            @endif
                            @php $i++; @endphp
                        @endforeach
                    </div>
                    <div class="task-bar-legend">
                        @php $i = 0; @endphp
                        @foreach($priceRanges as $range => $data)
                            <span class="legend-item"><span class="dot" style="background-color: {{ $colors[$i % count($colors)] }};"></span> {{ $range }} ({{ $data['count'] }})</span>
                            @php $i++; @endphp
                        @endforeach
                    </div>

                    <h3 style="font-size: 11px; color: var(--color-primary); font-weight: bold; margin-top: 20px;">Rendimiento por Día</h3>
                    <table class="styled-table" style="margin-top: 10px;">
                        <thead>
                            <tr>
                                <th>Día</th>
                                <th class="text-right">Total Ventas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($trivial['ventasPorDia'] as $ventaDia)
                            <tr>
                                <td>Día {{ $loop->iteration }} ({{ Carbon::parse($ventaDia->dia)->isoFormat('dddd D MMM') }})</td>
                                <td class="text-right" style="font-weight: bold;">${{ number_format($ventaDia->total_dia, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" style="text-align: center;">No hay datos de ventas por día.</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                </td>
            </tr>
        </table>
        
        <div class="page-break"></div>
        
        <h2 class="section-title">Apéndice A: Análisis de Ventas</h2>

        <div class="subsection-title">Desglose por Vendedor (Completo)</div>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Vendedor</th>
                    <th class="text-right"># Pedidos</th>
                    <th class="text-right">Unidades</th>
                    <th class="text-right">Ticket Promedio</th>
                    <th class="text-right">Valor Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ventasPorVendedor as $item)
                <tr>
                    <td>{{ $item->user_name }}</td>
                    <td class="text-right">{{ number_format($item->total_pedidos) }}</td>
                    <td class="text-right">{{ number_format($item->total_unidades) }}</td>
                    <td class="text-right">${{ $item->total_pedidos > 0 ? number_format($item->valor_total / $item->total_pedidos, 2) : '0.00' }}</td>
                    <td class="text-right" style="font-weight: bold;">${{ number_format($item->valor_total, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align: center;">No hay ventas registradas.</td></tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="3" class="text-right">TOTAL GENERAL:</td>
                    <td class="text-right">${{ number_format($kpis['ticketPromedio'], 2) }}</td>
                    <td class="text-right">${{ number_format($kpis['valorTotalVendido'], 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="subsection-title">Desglose por Producto (Top 20)</div>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Producto</th>
                    <th class="text-right">Unidades Vendidas</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topProductos->take(20) as $item)
                <tr>
                    <td>{{ $item->sku }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="text-right" style="font-weight: bold;">{{ number_format($item->total_vendido) }}</td>
                </tr>
                @empty
                <tr><td colspan="3" style="text-align: center;">No hay productos vendidos.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="page-break"></div>
        <h2 class="section-title">Apéndice B: Análisis de Inventario</h2>
        
        <div class="subsection-title">Alertas de Stock (Disponible 1-9 unids.)</div>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Producto</th>
                    <th class="text-right">Stock Total</th>
                    <th class="text-right">Reservado</th>
                    <th class="text-right text-warning">Disponible</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lowStockAlerts as $product)
                <tr>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->description }}</td>
                    <td class="text-right">{{ (int) $product->total_stock }}</td>
                    <td class="text-right">{{ (int) $product->total_reserved }}</td>
                    <td class="text-right text-warning" style="font-weight: bold;">{{ $product->available }}</td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align: center;">No hay alertas de stock bajo.</td></tr>
                @endforelse
            </tbody>
        </table>
        
        <h2 class="section-title">Diagnóstico Final</h2>
        <div class="diag-box success">
            <strong>Resumen Inteligente:</strong> {{ $final_summary }}
        </div>
        <div class="diag-box warning">
            <strong>Datos clave del Evento:</strong>
            <ul style="margin: 5px 0 0 20px; padding: 0;">
                @if($trivial['mejorVendedor'])
                    <li><strong>Mejor Vendedor:</strong> {{ $trivial['mejorVendedor']->user_name }} (${{ number_format($trivial['mejorVendedor']->valor_total, 2) }})</li>
                @endif
                @if($trivial['productoEstrella'])
                    <li><strong>Producto Estrella:</strong> {{ $trivial['productoEstrella']->sku }} ({{ number_format($trivial['productoEstrella']->total_vendido) }} unids.)</li>
                @endif
                @if($trivial['diaPico'])
                    <li><strong>Día Pico de Ventas:</strong> {{ $trivial['diaPico']->dia_formateado }} (${{ number_format($trivial['diaPico']->total_dia, 2) }})</li>
                @endif
            </ul>
        </div>

    </main>
</body>
</html>