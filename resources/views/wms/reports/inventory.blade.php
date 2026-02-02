<x-app-layout>
    <x-slot name="header"></x-slot>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Raleway:wght@800;900&display=swap');
        
        .font-raleway { font-family: 'Raleway', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        
        .stagger-enter { opacity: 0; transform: translateY(20px); animation: enterUp 0.5s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; }
        @keyframes enterUp { to { opacity: 1; transform: translateY(0); } }
        
        .input-arch {
            background: transparent; border: none; border-bottom: 2px solid #e5e7eb; border-radius: 0;
            padding: 0.5rem 0; font-family: 'Montserrat', sans-serif; font-weight: 600; color: #2c3856;
            transition: all 0.3s ease; width: 100%; font-size: 0.9rem;
        }
        .input-arch:focus { border-bottom-color: #ff9c00; box-shadow: none; outline: none; }
        .input-arch-select { background-image: none; cursor: pointer; padding-right: 1.5rem; }

        .btn-nexus { background: #2c3856; color: white; border-radius: 0.8rem; font-weight: 700; transition: all 0.2s; }
        .btn-nexus:hover { background: #1a253a; transform: translateY(-1px); }
        
        .btn-ghost { background: transparent; color: #2c3856; border: 2px solid #e5e7eb; border-radius: 1rem; font-weight: 700; }
        .btn-ghost:hover { border-color: #2c3856; background: #2c3856; color: white; }

        [x-cloak] { display: none !important; }
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20 relative overflow-hidden">
        
        <div class="fixed inset-0 -z-10 pointer-events-none">
            <div class="absolute top-0 left-0 w-full h-full bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-10"></div>
            <div class="absolute top-[-20%] right-[-10%] w-[50rem] h-[50rem] bg-gradient-to-b from-[#2c3856]/5 to-transparent rounded-full blur-[120px]"></div>
        </div>

        <div class="max-w-[1920px] mx-auto px-6 pt-10 relative z-10">
            
            <div class="flex flex-col xl:flex-row justify-between items-end mb-10 stagger-enter" style="animation-delay: 0.1s;">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-12 h-1 bg-[#ff9c00]"></span>
                        <span class="text-sm font-bold text-[#2c3856] tracking-[0.3em] uppercase">Business Intelligence</span>
                    </div>
                    <h1 class="text-4xl md:text-6xl font-raleway font-black text-[#2c3856] leading-none">
                        DASHBOARD <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-600">WMS</span>
                    </h1>
                </div>

                <div class="flex flex-wrap gap-3 mt-6 xl:mt-0 items-center">
                    <a href="{{ route('wms.reports.index') }}" class="flex items-center gap-2 px-5 py-3 bg-white border border-gray-200 text-[#666666] font-bold rounded-xl shadow-sm hover:shadow-md hover:border-[#2c3856] hover:text-[#2c3856] transition-all text-sm uppercase tracking-wider">
                        <i class="fas fa-arrow-left"></i> <span>Volver a Reportes</span>
                    </a>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] p-8 border border-gray-100 shadow-xl mb-10 stagger-enter" style="animation-delay: 0.2s;">
                <form method="GET" action="{{ route('wms.reports.inventory') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 items-end">
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Almacén</label>
                        <select name="warehouse_id" id="warehouse_id" class="input-arch input-arch-select text-lg">
                            <option value="">Todos los Almacenes</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ $warehouseId == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-[#ff9c00] uppercase tracking-widest block mb-1">Área / Cliente</label>
                        <select name="area_id" id="area_id" class="input-arch input-arch-select text-lg text-[#ff9c00]">
                            <option value="">Todas las Áreas</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}" {{ $areaId == $area->id ? 'selected' : '' }}>
                                    {{ $area->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn-nexus px-6 py-3 w-full shadow-lg uppercase tracking-wider text-xs">
                            <i class="fas fa-filter mr-2"></i> Actualizar Gráficos
                        </button>
                    </div>
                    <div>
                        <a href="{{ route('wms.reports.inventory') }}" class="btn-ghost px-6 py-3 w-full flex items-center justify-center uppercase tracking-wider text-xs">
                            <i class="fas fa-undo mr-2"></i> Limpiar Filtros
                        </a>
                    </div>
                </form>
            </div>        

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 stagger-enter" style="animation-delay: 0.3s;">
                <div class="bg-white rounded-[2rem] p-8 shadow-lg border border-gray-100 col-span-1 md:col-span-2 lg:col-span-3">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6">Vista General del Inventario</h3>
                    <div id="chart-kpi-overview"></div>
                </div>
                <div class="bg-white rounded-[2rem] p-8 shadow-lg border border-gray-100 flex flex-col justify-center items-center">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-2 text-center">Precisión de Inventario</h3>
                    <div id="chart-inventory-accuracy" class="w-full h-48"></div>
                    <p class="text-[10px] text-gray-400 font-bold uppercase mt-2 text-center">Basado en Conteos Físicos</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8 stagger-enter" style="animation-delay: 0.4s;">
                <div class="bg-white rounded-[2rem] p-8 shadow-lg border border-gray-100">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6">Perfil de Antigüedad (Aging)</h3>
                    <div id="chart-inventory-aging"></div>
                </div>
                <div class="bg-white rounded-[2rem] p-8 shadow-lg border border-gray-100 lg:col-span-2">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6">Tendencia de Flujo (Unidades Mensuales)</h3>
                    <div id="chart-inbound-outbound"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8 stagger-enter" style="animation-delay: 0.5s;">
                <div class="bg-white rounded-[2rem] p-8 shadow-lg border border-gray-100">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6">Top 10 Productos por Cantidad</h3>
                    <div id="chart-top-products-qty"></div>
                </div>
                <div class="bg-white rounded-[2rem] p-8 shadow-lg border border-gray-100">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6">Top 10 Productos por Frecuencia</h3>
                    <div id="chart-top-products-freq"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8 stagger-enter" style="animation-delay: 0.6s;">
                 <div class="bg-white rounded-[2rem] p-8 shadow-lg border border-gray-100">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6">Análisis ABC por Cantidad</h3>
                    <div id="chart-abc-analysis"></div>
                </div>
                <div class="bg-white rounded-[2rem] p-8 shadow-lg border border-gray-100">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6">Stock Disponible vs. Comprometido</h3>
                    <div id="chart-available-committed"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8 stagger-enter" style="animation-delay: 0.7s;">
                <div class="bg-white rounded-[2rem] p-8 shadow-lg border border-gray-100">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6">Stock por Tipo de Ubicación</h3>
                    <div id="chart-stock-by-location-type"></div>
                </div>
                 <div class="bg-white rounded-[2rem] p-8 shadow-lg border border-gray-100">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6">Utilización de Ubicaciones</h3>
                    <div id="chart-location-utilization"></div>
                </div>
                <div class="bg-white rounded-[2rem] p-8 shadow-lg border border-gray-100">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6">Top 10 Ubicaciones (Qty)</h3>
                    <div id="chart-top-locations-qty"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8 stagger-enter" style="animation-delay: 0.8s;">
                <div class="bg-white rounded-[2rem] p-8 shadow-lg border border-gray-100">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6">Tendencia Recepciones (Diario)</h3>
                    <div id="chart-receiving-trend"></div>
                </div>
                <div class="bg-white rounded-[2rem] p-8 shadow-lg border border-gray-100">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6">Tendencia Picking (Diario)</h3>
                    <div id="chart-picking-trend"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8 mb-20 stagger-enter" style="animation-delay: 0.9s;">
                <div class="bg-white rounded-[2rem] p-8 shadow-lg border border-gray-100">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6">Top 10 Productos por Volumen (m³)</h3>
                    <div id="chart-top-products-volume"></div>
                </div>
                <div class="bg-white rounded-[2rem] p-8 shadow-lg border border-gray-100">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6">Distribución por Marca</h3>
                    <div id="chart-stock-by-brand"></div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const commonOptions = {
                chart: {
                    fontFamily: 'Montserrat, sans-serif',
                    toolbar: { show: false },
                    animations: { enabled: true, easing: 'easeinout', speed: 800 },
                    background: 'transparent'
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                grid: {
                    borderColor: '#f3f4f6',
                    strokeDashArray: 4,
                    xaxis: { lines: { show: false } }
                },
                tooltip: { 
                    theme: 'light',
                    style: { fontSize: '12px', fontFamily: 'Montserrat, sans-serif' },
                    x: { show: true },
                    marker: { show: true },
                },
                colors: ['#2c3856', '#ff9c00', '#10B981', '#EF4444', '#8B5CF6', '#3B82F6'],
                legend: { 
                    fontFamily: 'Montserrat, sans-serif',
                    fontWeight: 600,
                    markers: { radius: 12 },
                    itemMargin: { horizontal: 10, vertical: 5 }
                }
            };

            var options1 = {
                ...commonOptions,
                series: [
                    {{ $kpis['totalUnits'] ?? 0 }},
                    {{ $kpis['skusWithStock'] ?? 0 }},
                    {{ $kpis['locationsUsed'] ?? 0 }}
                ],
                chart: { type: 'radialBar', height: 280 },
                plotOptions: {
                    radialBar: {
                        offsetY: 0, startAngle: 0, endAngle: 270,
                        hollow: { margin: 5, size: '35%', background: 'transparent' },
                        dataLabels: {
                            name: { show: false },
                            value: { show: false }
                        },
                        track: { background: '#f3f4f6', strokeWidth: '100%' },
                    }
                },
                colors: ['#2c3856', '#ff9c00', '#10B981'],
                labels: ['Unidades Totales', 'SKUs Únicos', 'Ubicaciones Ocupadas'],
                legend: {
                    show: true, floating: true, fontSize: '12px', position: 'left', offsetX: 0, offsetY: 10,
                    labels: { useSeriesColors: true },
                    formatter: function(seriesName, opts) {
                         if (opts.seriesIndex === 0) return seriesName + ": " + ({{ $kpis['totalUnits'] ?? 0 }}).toLocaleString();
                         if (opts.seriesIndex === 1) return seriesName + ": " + ({{ $kpis['skusWithStock'] ?? 0 }}).toLocaleString();
                         if (opts.seriesIndex === 2) return seriesName + ": " + ({{ $kpis['locationsUsed'] ?? 0 }}).toLocaleString();
                         return seriesName;
                    }
                }
            };
            var chart1 = new ApexCharts(document.querySelector("#chart-kpi-overview"), options1);
            chart1.render();

            var options4 = {
                ...commonOptions,
                series: [{{ number_format($kpis['inventoryAccuracy'] ?? 0, 1) }}],
                chart: { type: 'radialBar', height: 220, sparkline: { enabled: true } },
                plotOptions: {
                    radialBar: {
                        startAngle: -90, endAngle: 90, hollow: { size: '65%' },
                        dataLabels: { 
                            name: { show: false }, 
                            value: { offsetY: -5, fontSize: '28px', fontWeight: 900, color: '#2c3856', formatter: function(val) { return val + '%'; } } 
                        },
                        track: { background: '#f3f4f6', strokeWidth: '97%' },
                    }
                },
                fill: { 
                    type: 'gradient', 
                    gradient: { 
                        shade: 'light', type: 'horizontal', 
                        colorStops: [ { offset: 0, color: "#EF4444"}, { offset: 50, color: "#ff9c00"}, { offset: 100, color: "#10B981"} ] 
                    } 
                },
                labels: ['Precisión'],
            };
            var chart4 = new ApexCharts(document.querySelector("#chart-inventory-accuracy"), options4);
            chart4.render();

            var options2 = {
                ...commonOptions,
                series: @json(array_values($kpis['agingData'])),
                labels: @json(array_keys($kpis['agingData'])),
                chart: { type: 'donut', height: 350 },
                plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, label: 'Total', fontSize: '14px', fontWeight: 700, color: '#9ca3af' } } } } },
                legend: { position: 'bottom' }
            };
            var chart2 = new ApexCharts(document.querySelector("#chart-inventory-aging"), options2);
            chart2.render();

            var options3 = {
                ...commonOptions,
                series: [{
                    name: 'Recibidas', 
                    type: 'column',
                    data: @json($kpis['inboundTrend']['data'] ?? [])
                }, {
                    name: 'Enviadas', 
                    type: 'line',
                    data: @json($kpis['outboundTrend']['data'] ?? [])
                }],
                chart: { type: 'line', height: 350, stacked: false },
                stroke: { width: [0, 4], curve: 'smooth' },
                xaxis: {
                    categories: @json($kpis['inboundTrend']['labels'] ?? []),
                    labels: { rotate: 0, style: { fontSize: '10px', fontFamily: 'Montserrat, sans-serif' } }
                },
                yaxis: [
                    { title: { text: 'Recibidas', style: { fontSize: '10px', fontFamily: 'Montserrat, sans-serif' } } }, 
                    { opposite: true, title: { text: 'Enviadas', style: { fontSize: '10px', fontFamily: 'Montserrat, sans-serif' } } }
                ],
                colors: ['#2c3856', '#ff9c00'],
                legend: { position: 'top' }
            };
            var chart3 = new ApexCharts(document.querySelector("#chart-inbound-outbound"), options3);
            chart3.render();

            var options5 = {
                ...commonOptions,
                series: [{ data: @json($kpis['topProductsQty']['quantities'] ?? []) }],
                chart: { type: 'bar', height: 350 },
                plotOptions: { bar: { borderRadius: 6, horizontal: true, barHeight: '60%' } },
                xaxis: { categories: @json($kpis['topProductsQty']['names'] ?? []) },
                colors: ['#2c3856']
            };
            var chart5 = new ApexCharts(document.querySelector("#chart-top-products-qty"), options5);
            chart5.render();

            var options6 = {
                ...commonOptions,
                series: [{ data: @json($kpis['topProductsFreq']['frequencies'] ?? []) }],
                chart: { type: 'bar', height: 350 },
                plotOptions: { bar: { borderRadius: 6, horizontal: true, barHeight: '60%' } },
                colors: ['#ff9c00'],
                xaxis: { categories: @json($kpis['topProductsFreq']['names'] ?? []) }
            };
            var chart6 = new ApexCharts(document.querySelector("#chart-top-products-freq"), options6);
            chart6.render();

            var options7 = {
                ...commonOptions,
                series: @json($kpis['abcAnalysis']['series'] ?? []),
                chart: { type: 'bar', height: 350, stacked: true, stackType: '100%' },
                plotOptions: { bar: { horizontal: true, borderRadius: 6 } },
                xaxis: { categories: ['Distribución por Cantidad'] },
                legend: { position: 'top' },
                colors: ['#2c3856', '#ff9c00', '#9ca3af']
            };
            var chart7 = new ApexCharts(document.querySelector("#chart-abc-analysis"), options7);
            chart7.render();

            var options8 = {
                ...commonOptions,
                series: [{
                    name: 'Disponible', 
                    data: @json($kpis['availableCommitted']['available'] ?? [])
                }, {
                    name: 'Comprometido', 
                    data: @json($kpis['availableCommitted']['committed'] ?? [])
                }],
                chart: { type: 'bar', height: 350, stacked: true },
                plotOptions: { bar: { horizontal: false, borderRadius: 4, columnWidth: '40%' } },
                xaxis: { categories: @json($kpis['availableCommitted']['names'] ?? []) },
                legend: { position: 'top' },
                colors: ['#10B981', '#EF4444']
            };
            var chart8 = new ApexCharts(document.querySelector("#chart-available-committed"), options8);
            chart8.render();

            var options9 = {
                ...commonOptions,
                series: @json(array_values($kpis['stockByLocationType']['data'] ?? [])),
                labels: @json(array_keys($kpis['stockByLocationType']['data'] ?? [])),
                chart: { type: 'donut', height: 350 },
                plotOptions: { pie: { donut: { size: '70%' } } },
                legend: { position: 'bottom' }
            };
            var chart9 = new ApexCharts(document.querySelector("#chart-stock-by-location-type"), options9);
            chart9.render();

            var options11 = {
                ...commonOptions,
                series: @json(array_values($kpis['locationUtilization'] ?? [0, 0])),
                labels: ['Ocupadas', 'Vacías'],
                chart: { type: 'donut', height: 350 },
                colors: ['#ff9c00', '#f3f4f6'],
                plotOptions: { pie: { donut: { size: '70%' } } },
                legend: { position: 'bottom' }
            };
            var chart11 = new ApexCharts(document.querySelector("#chart-location-utilization"), options11);
            chart11.render();

            var options10 = {
                ...commonOptions,
                series: [{ data: @json($kpis['topLocationsQty']['quantities'] ?? []) }],
                chart: { type: 'bar', height: 350 },
                plotOptions: { bar: { borderRadius: 4, horizontal: true } },
                colors: ['#8B5CF6'],
                xaxis: { categories: @json($kpis['topLocationsQty']['codes'] ?? []) }
            };
            var chart10 = new ApexCharts(document.querySelector("#chart-top-locations-qty"), options10);
            chart10.render();

            var options12 = {
                ...commonOptions,
                series: [{ 
                    name: 'Unidades', 
                    data: @json($kpis['receivingTrend']['data'] ?? []) 
                }],
                chart: { type: 'area', height: 350 },
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.2, stops: [0, 90, 100] } },
                colors: ['#2c3856'],
                xaxis: { type: 'datetime', categories: @json($kpis['receivingTrend']['labels'] ?? []) }
            };
            var chart12 = new ApexCharts(document.querySelector("#chart-receiving-trend"), options12);
            chart12.render();

            var options13 = {
                ...commonOptions,
                series: [{ 
                    name: 'Unidades', 
                    data: @json($kpis['pickingTrend']['data'] ?? []) 
                }],
                chart: { type: 'area', height: 350 },
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.2, stops: [0, 90, 100] } },
                colors: ['#ff9c00'],
                xaxis: { type: 'datetime', categories: @json($kpis['pickingTrend']['labels'] ?? []) }
            };
            var chart13 = new ApexCharts(document.querySelector("#chart-picking-trend"), options13);
            chart13.render();

            var options14 = {
                ...commonOptions,
                series: [{ 
                    name: 'Volumen (m³)', 
                    data: @json($kpis['topProductsVol']['volumes'] ?? []) 
                }],
                chart: { type: 'bar', height: 350 },
                plotOptions: { bar: { borderRadius: 4, horizontal: true } },
                colors: ['#10B981'],
                xaxis: { categories: @json($kpis['topProductsVol']['names'] ?? []) }
            };
            var chart14Element = document.querySelector("#chart-top-products-volume");
            if (chart14Element) {
                var chart14 = new ApexCharts(chart14Element, options14);
                chart14.render();
            }

            var options15 = {
                ...commonOptions,
                series: @json($kpis['stockByBrandSeries'] ?? []),
                labels: @json($kpis['stockByBrandLabels'] ?? []),
                chart: { type: 'donut', height: 350 },
                legend: { position: 'bottom' }
            };
            var chart15Element = document.querySelector("#chart-stock-by-brand");
            if (chart15Element) {
                var chart15 = new ApexCharts(chart15Element, options15);
                chart15.render();
            }
        });
    </script>
</x-app-layout>