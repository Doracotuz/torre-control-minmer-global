<x-app-layout>
    <x-slot name="header"></x-slot>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Raleway:wght@800;900&display=swap');
        .font-raleway { font-family: 'Raleway', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        
        .nexus-card { background: white; border-radius: 1.5rem; box-shadow: 0 10px 30px -5px rgba(44, 56, 86, 0.05); border: 1px solid #f3f4f6; }
        .nexus-input { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 1rem; padding: 0.75rem 1rem; font-size: 0.875rem; transition: all 0.2s; }
        .nexus-input:focus { border-color: #2c3856; box-shadow: 0 0 0 4px rgba(44, 56, 86, 0.1); outline: none; }
        .btn-nexus { background: #2c3856; color: white; border-radius: 1rem; padding: 0.75rem 1.5rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.75rem; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(44, 56, 86, 0.2); }
        .btn-nexus:hover { background: #1a253a; transform: translateY(-1px); box-shadow: 0 10px 15px -3px rgba(44, 56, 86, 0.3); }
        .btn-ghost { background: white; color: #2c3856; border: 1px solid #e5e7eb; border-radius: 1rem; padding: 0.75rem 1.5rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.75rem; transition: all 0.2s; }
        .btn-ghost:hover { background: #f3f4f6; border-color: #d1d5db; }
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20 relative">
        <div class="max-w-[1800px] mx-auto px-4 md:px-6 pt-6 md:pt-10">
            
            <div class="flex flex-col xl:flex-row justify-between items-start xl:items-end mb-10 gap-6">
                <div>
                    <div class="flex items-center gap-4 mb-2">
                        <a href="{{ route('wms.dashboard', ['tab' => 'reportes']) }}" class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-[#2c3856] shadow-md hover:bg-gray-50 transition-colors">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h1 class="text-4xl md:text-5xl font-raleway font-black text-[#2c3856]">
                            Reporte de <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">Facturación</span>
                        </h1>
                    </div>
                    <p class="text-gray-500 font-medium ml-14">Análisis de costos de servicios y estimación de almacenaje.</p>
                </div>

                <div class="nexus-card p-4 w-full xl:w-auto">
                    <form action="{{ route('wms.reports.billing.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                        <div class="w-full md:w-48">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 block">Inicio</label>
                            <input type="date" name="start_date" value="{{ $startDate }}" class="nexus-input w-full">
                        </div>
                        <div class="w-full md:w-48">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 block">Fin</label>
                            <input type="date" name="end_date" value="{{ $endDate }}" class="nexus-input w-full">
                        </div>
                        <div class="w-full md:w-56">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 block">Almacén</label>
                            <select name="warehouse_id" class="nexus-input w-full text-sm">
                                <option value="">Todos los Almacenes</option>
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}" {{ request('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-56">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 block">Cliente</label>
                            <select name="area_id" class="nexus-input w-full text-sm">
                                <option value="">Todos los Clientes</option>
                                @foreach($clients as $c)
                                    <option value="{{ $c->id }}" {{ request('area_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn-nexus h-[46px] w-full md:w-auto flex items-center justify-center gap-2">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                    </form>
                </div>
            </div>

            <div class="flex justify-end gap-3 mb-8">
                <a href="{{ route('wms.reports.billing.pdf', request()->all()) }}" target="_blank" class="btn-ghost flex items-center gap-2 text-red-600 border-red-200 hover:bg-red-50">
                    <i class="fas fa-file-pdf"></i> Exportar PDF
                </a>
                <a href="{{ route('wms.reports.billing.csv', request()->all()) }}" class="btn-ghost flex items-center gap-2 text-green-600 border-green-200 hover:bg-green-50">
                    <i class="fas fa-file-csv"></i> Exportar CSV
                </a>
            </div>

            <div class="nexus-card overflow-hidden mb-8">
                <div class="bg-[#2c3856] px-6 py-3 border-b border-[#2c3856] flex justify-between items-center">
                    <div>
                        <h2 class="text-white font-raleway font-bold text-base">Resumen Financiero</h2>
                        <p class="text-blue-200 text-[10px]">Desglose de costos por categoría.</p>
                    </div>
                    <div class="text-right">
                         <span class="text-2xl font-black text-white">${{ number_format($kpis['grand_total'], 2) }}</span>
                         <span class="text-[10px] text-blue-200 block uppercase tracking-wider">Total Facturable</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="text-[10px] text-gray-500 uppercase bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-2 font-bold tracking-wider">Concepto</th>
                                <th class="px-4 py-2 font-bold tracking-wider text-right">Cantidad / Días</th>
                                <th class="px-4 py-2 font-bold tracking-wider text-right">Costo Unit. (Prom)</th>
                                <th class="px-4 py-2 font-bold tracking-wider text-right">Subtotal</th>
                                <th class="px-4 py-2 font-bold tracking-wider text-center">%</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                             @php $grandTotal = $kpis['grand_total'] > 0 ? $kpis['grand_total'] : 1; @endphp
                            @foreach($kpis['vas_breakdown']->take(5) as $desc => $cost)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium text-gray-900 flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div> {{ Str::limit($desc, 40) }}
                                </td>
                                <td class="px-4 py-2 text-right text-gray-500">
                                    @php 
                                        $idx = array_search($desc, $kpis['chart_services']['labels']);
                                        $count = $idx !== false ? $kpis['chart_services']['counts'][$idx] : 0;
                                    @endphp
                                    {{ number_format($count) }}
                                </td>
                                <td class="px-4 py-2 text-right text-gray-500">${{ $count > 0 ? number_format($cost / $count, 2) : '0.00' }}</td>
                                <td class="px-4 py-2 text-right font-bold text-[#2c3856]">${{ number_format($cost, 2) }}</td>
                                <td class="px-4 py-2 text-center text-gray-400">{{ number_format(($cost / $grandTotal) * 100, 1) }}%</td>
                            </tr>
                            @endforeach
                             <tr class="bg-orange-50/30">
                                <td class="px-4 py-2 font-medium text-gray-900 flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full bg-[#ff9c00]"></div> Almacenaje
                                </td>
                                <td class="px-4 py-2 text-right text-gray-500">{{ number_format($kpis['active_pallets']) }} pallets</td>
                                <td class="px-4 py-2 text-right text-gray-500">-</td>
                                <td class="px-4 py-2 text-right font-bold text-[#ff9c00]">${{ number_format($kpis['total_storage'], 2) }}</td>
                                <td class="px-4 py-2 text-center text-gray-400">{{ number_format(($kpis['total_storage'] / $grandTotal) * 100, 1) }}%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
                
                <div class="nexus-card p-4 lg:col-span-2">
                    <h3 class="text-sm font-raleway font-bold text-[#2c3856] mb-4">1. Evolución de Costos (Diario)</h3>
                    <div id="chart-cost-evolution" class="h-64"></div>
                </div>

                <div class="nexus-card p-4">
                    <h3 class="text-sm font-raleway font-bold text-[#2c3856] mb-4">2. Composición de Costos</h3>
                    <div id="chart-cost-composition" class="h-64 flex justify-center"></div>
                </div>

                <div class="nexus-card p-4 lg:col-span-2">
                    <h3 class="text-sm font-raleway font-bold text-[#2c3856] mb-4">3. Actividad Logística (Entradas vs Salidas)</h3>
                    <div id="chart-logistics-activity" class="h-64"></div>
                </div>

                <div class="nexus-card p-4">
                    <h3 class="text-sm font-raleway font-bold text-[#2c3856] mb-4">4. Top Servicios (Costo)</h3>
                    <div id="chart-top-services" class="h-64"></div>
                </div>

                <div class="nexus-card p-4">
                    <h3 class="text-sm font-raleway font-bold text-[#2c3856] mb-4">5. Volumen Embarcado (Piezas)</h3>
                    <div id="chart-shipped-volume" class="h-64"></div>
                </div>

                <div class="nexus-card p-4 lg:col-span-2">
                    <h3 class="text-sm font-raleway font-bold text-[#2c3856] mb-4">6. Mix de Servicios (Volumen vs Costo)</h3>
                     <div class="flex">
                        <div id="chart-service-polar" class="w-1/2 h-64 flex justify-center"></div>
                        <div class="w-1/2 pl-4 text-xs text-gray-500 flex flex-col justify-center">
                            <p class="mb-2">El gráfico polar muestra la relación entre la frecuencia de uso de un servicio y su impacto en la facturación.</p>
                            <ul class="list-disc pl-4 space-y-1">
                                <li><strong>Radio:</strong> Costo Total</li>
                                <li><strong>Área:</strong> Volumen de operación</li>
                            </ul>
                        </div>
                     </div>
                </div>

            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fontUrl = 'Montserrat, sans-serif';
            const colors = {
                primary: '#2c3856',
                secondary: '#ff9c00',
                success: '#10B981',
                info: '#3B82F6',
                purple: '#8B5CF6',
                gray: '#9CA3AF'
            };
            const commonOptions = {
                chart: { fontFamily: fontUrl, toolbar: { show: false }, zoom: { enabled: false } },
                dataLabels: { enabled: false },
                stroke: { width: 2 },
                grid: { borderColor: '#f3f4f6', strokeDashArray: 4 }
            };

            new ApexCharts(document.querySelector("#chart-cost-evolution"), {
                ...commonOptions,
                series: [
                    { name: 'Almacenaje', data: @json($kpis['chart_daily']['storage']) },
                    { name: 'Servicios (VAS)', data: @json($kpis['chart_daily']['vas']) }
                ],
                chart: { type: 'area', height: 260, stacked: true, fontFamily: fontUrl, toolbar: { show: false } },
                colors: [colors.secondary, colors.primary],
                fill: { type: 'gradient', gradient: { opacityFrom: 0.6, opacityTo: 0.2 } },
                xaxis: { categories: @json($kpis['chart_daily']['labels']), type: 'datetime', labels: { format: 'dd MMM' } },
                tooltip: { y: { formatter: val => "$" + val.toLocaleString() } }
            }).render();

            new ApexCharts(document.querySelector("#chart-cost-composition"), {
                ...commonOptions,
                series: @json($kpis['chart_vas_vs_storage']),
                chart: { type: 'donut', height: 260, fontFamily: fontUrl },
                labels: ['Servicios (VAS)', 'Almacenaje'],
                colors: [colors.secondary, colors.primary],
                legend: { position: 'bottom', fontSize: '11px' },
                plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Total', fontSize: '12px', color: '#2c3856', formatter: w => '$' + w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString() } } } } }
            }).render();

            new ApexCharts(document.querySelector("#chart-logistics-activity"), {
                ...commonOptions,
                series: [
                    { name: 'Entradas (POs)', data: @json($kpis['chart_daily']['pos']) },
                    { name: 'Salidas (SOs)', data: @json($kpis['chart_daily']['sos']) }
                ],
                chart: { type: 'bar', height: 260, fontFamily: fontUrl, toolbar: { show: false } },
                colors: [colors.info, colors.purple],
                plotOptions: { bar: { columnWidth: '50%', borderRadius: 2 } },
                xaxis: { categories: @json($kpis['chart_daily']['labels']), type: 'datetime', labels: { format: 'dd' } },
            }).render();

            const topServices = {
                labels: @json(array_slice($kpis['chart_services']['labels'], 0, 5)),
                costs: @json(array_slice($kpis['chart_services']['costs'], 0, 5))
            };
            new ApexCharts(document.querySelector("#chart-top-services"), {
                ...commonOptions,
                series: [{ name: 'Costo', data: topServices.costs }],
                chart: { type: 'bar', height: 260, fontFamily: fontUrl, toolbar: { show: false } },
                colors: [colors.primary],
                plotOptions: { bar: { horizontal: true, borderRadius: 2, barHeight: '60%' } },
                xaxis: { labels: { formatter: val => "$" + val.toLocaleString() } },
                tooltip: { y: { formatter: val => "$" + val.toLocaleString() } }
            }).render();

            new ApexCharts(document.querySelector("#chart-shipped-volume"), {
                ...commonOptions,
                series: [{ name: 'Piezas', data: @json($kpis['chart_daily']['pieces']) }],
                chart: { type: 'line', height: 260, fontFamily: fontUrl, toolbar: { show: false } },
                colors: [colors.success],
                stroke: { curve: 'monotoneCubic', width: 3 },
                xaxis: { categories: @json($kpis['chart_daily']['labels']), type: 'datetime', labels: { format: 'dd MMM' } },
            }).render();

             new ApexCharts(document.querySelector("#chart-service-polar"), {
                ...commonOptions,
                series: @json($kpis['chart_services']['costs']),
                chart: { type: 'polarArea', height: 280, fontFamily: fontUrl },
                labels: @json($kpis['chart_services']['labels']),
                colors: [colors.primary, colors.secondary, colors.info, colors.purple, colors.success],
                fill: { opacity: 0.8 },
                legend: { show: false },
                yaxis: { show: false }
            }).render();
        });
    </script>
</x-app-layout>
