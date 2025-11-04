<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <h2 class="font-bold text-3xl text-gray-800 leading-tight tracking-tight">
                Dashboard Inteligente de Inventario WMS
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <div class="bg-white p-4 rounded-lg shadow-md">
                <form method="GET" action="{{ route('wms.reports.inventory') }}" class="flex items-end space-x-4">
                    <div>
                        <label for="warehouse_id" class="block text-sm font-medium text-gray-700">Seleccionar Almacén</label>
                        <select name="warehouse_id" id="warehouse_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Todos los Almacenes</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ $warehouseId == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#2c3856] hover:bg-[#1f2940]">
                        Filtrar
                    </button>
                    <a href="{{ route('wms.reports.inventory') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Limpiar
                    </a>
                </form>
            </div>        

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200 col-span-1 md:col-span-2 lg:col-span-3">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Vista General del Inventario</h3>
                    <div id="chart-kpi-overview"></div> {{-- Chart 1 --}}
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200 flex flex-col justify-center items-center">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2 text-center">Precisión del Inventario</h3>
                    <div id="chart-inventory-accuracy" class="w-full h-48"></div> {{-- Chart 4 --}}
                    <p class="text-xs text-gray-500 mt-2 text-center">Basado en resultados de conteos físicos</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Perfil de Antigüedad (Aging)</h3>
                    <div id="chart-inventory-aging"></div> {{-- Chart 2 --}}
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200 lg:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Tendencia de Flujo (Unidades Mensuales)</h3>
                    <div id="chart-inbound-outbound"></div> {{-- Chart 3 --}}
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Top 10 Productos por Cantidad</h3>
                    <div id="chart-top-products-qty"></div> {{-- Chart 5 --}}
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Top 10 Productos por Frecuencia de Movimiento</h3>
                    <div id="chart-top-products-freq"></div> {{-- Chart 6 --}}
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                 <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Análisis ABC por Cantidad</h3>
                    <div id="chart-abc-analysis"></div> {{-- Chart 7 --}}
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Stock Disponible vs. Comprometido (Top 10 SKUs)</h3>
                    <div id="chart-available-committed"></div> {{-- Chart 8 --}}
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Distribución por Tipo de Ubicación</h3>
                    <div id="chart-stock-by-location-type"></div> {{-- Chart 9 --}}
                </div>
                 <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Utilización de Ubicaciones (Almacén)</h3>
                    <div id="chart-location-utilization"></div> {{-- Chart 11 --}}
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Top 10 Ubicaciones por Cantidad</h3>
                    <div id="chart-top-locations-qty"></div> {{-- Chart 10 --}}
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Tendencia de Recepciones (Unidades Diarias)</h3>
                    <div id="chart-receiving-trend"></div> {{-- Chart 12 --}}
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Tendencia de Picking (Unidades Diarias)</h3>
                    <div id="chart-picking-trend"></div> {{-- Chart 13 --}}
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Top 10 Productos por Volumen Ocupado (m³)</h3>
                    <div id="chart-top-products-volume"></div> {{-- Chart 14 - NUEVO ID --}}
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Distribución de Stock por Marca</h3>
                    <div id="chart-stock-by-brand"></div> {{-- Chart 15 - NUEVO ID --}}
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
                toolbar: { 
                    show: true, 
                    tools: { 
                        download: true, 
                        selection: false, 
                        zoom: false, 
                        zoomin: false, 
                        zoomout: false, 
                        pan: false, 
                        reset: false 
                    } 
                },
                animations: { enabled: true, easing: 'easeinout', speed: 800 },
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            grid: {
                borderColor: '#e7e7e7',
                row: { colors: ['#f3f3f3', 'transparent'], opacity: 0.5 },
            },
            tooltip: { theme: 'dark' },
            colors: ['#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#3B82F6'],
        };

        var options1 = {
                ...commonOptions,
                series: [
                    {{ $kpis['totalUnits'] ?? 0 }},
                    {{ $kpis['skusWithStock'] ?? 0 }},
                    {{ $kpis['locationsUsed'] ?? 0 }}
                ],
                chart: { type: 'radialBar', height: 250 },
                plotOptions: {
                    radialBar: {
                        offsetY: 0, startAngle: 0, endAngle: 270,
                        hollow: { margin: 5, size: '30%', background: 'transparent', image: undefined },
                        dataLabels: {
                            name: { show: false },
                            value: { show: false }
                        },
                         track: { background: '#f0f0f0' },
                    }
                },
                colors: ['#4F46E5', '#10B981', '#F59E0B'],
                labels: ['Unidades Totales', 'SKUs Únicos', 'Ubicaciones Ocupadas'],
                legend: {
                    show: true, floating: true, fontSize: '14px', position: 'left', offsetX: -20, offsetY: 15,
                    labels: { useSeriesColors: true },
                    markers: { size: 0 },
                    formatter: function(seriesName, opts) {
                         if (opts.seriesIndex === 0) return seriesName + ": " + ({{ $kpis['totalUnits'] ?? 0 }}).toLocaleString();
                         if (opts.seriesIndex === 1) return seriesName + ": " + ({{ $kpis['skusWithStock'] ?? 0 }}).toLocaleString();
                         if (opts.seriesIndex === 2) return seriesName + ": " + ({{ $kpis['locationsUsed'] ?? 0 }}).toLocaleString();
                         return seriesName;
                    },
                    itemMargin: { vertical: 3 }
                },
                responsive: [{ breakpoint: 480, options: { legend: { show: false } } }]
            };
            var chart1 = new ApexCharts(document.querySelector("#chart-kpi-overview"), options1);
            chart1.render();

        var options4 = {
            ...commonOptions,
            series: [{{ number_format($kpis['inventoryAccuracy'] ?? 0, 1) }}],
            chart: { type: 'radialBar', height: 200, sparkline: { enabled: true } },
            plotOptions: {
                radialBar: {
                    startAngle: -90, 
                    endAngle: 90, 
                    hollow: { size: '60%' },
                    dataLabels: { 
                        name: { show: false }, 
                        value: { 
                            offsetY: -2, 
                            fontSize: '22px', 
                            formatter: function(val) { return val + '%'; } 
                        } 
                    },
                    track: { background: '#e0e0e0', strokeWidth: '97%' },
                }
            },
            fill: { 
                type: 'gradient', 
                gradient: { 
                    shade: 'light', 
                    type: 'horizontal', 
                    colorStops: [
                        { offset: 0, color: "#EF4444"}, 
                        { offset: 50, color: "#F59E0B"}, 
                        { offset: 100, color: "#10B981"}
                    ] 
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
            plotOptions: { pie: { donut: { size: '65%' } } },
            legend: { position: 'bottom' },
            responsive: [{ breakpoint: 480, options: { chart: { width: 200 }, legend: { position: 'bottom' } } }]
        };
        var chart2 = new ApexCharts(document.querySelector("#chart-inventory-aging"), options2);
        chart2.render();

        var options3 = {
            ...commonOptions,
            series: [{
                name: 'Unidades Recibidas', 
                type: 'column',
                data: @json($kpis['inboundTrend']['data'] ?? [])
            }, {
                name: 'Unidades Enviadas', 
                type: 'line',
                data: @json($kpis['outboundTrend']['data'] ?? [])
            }],
            chart: { type: 'line', height: 350, stacked: false },
            stroke: { width: [0, 3], curve: 'smooth' },
            xaxis: {
                categories: @json($kpis['inboundTrend']['labels'] ?? []),
                labels: { rotate: -45, style: { fontSize: '10px' } }
            },
            yaxis: [
                { title: { text: 'Unidades Recibidas' } }, 
                { opposite: true, title: { text: 'Unidades Enviadas' } }
            ],
            tooltip: { 
                shared: true, 
                intersect: false, 
                y: { 
                    formatter: function(val) { return val.toLocaleString() + ' Un.'; } 
                } 
            },
            legend: { position: 'top', horizontalAlign: 'left', offsetY: 10 }
        };
        var chart3 = new ApexCharts(document.querySelector("#chart-inbound-outbound"), options3);
        chart3.render();

        var options5 = {
            ...commonOptions,
            series: [{ data: @json($kpis['topProductsQty']['quantities'] ?? []) }],
            chart: { type: 'bar', height: 350 },
            plotOptions: { bar: { borderRadius: 4, horizontal: true } },
            xaxis: { categories: @json($kpis['topProductsQty']['names'] ?? []) },
            tooltip: { 
                y: { 
                    formatter: function(val) { return val.toLocaleString() + ' Un.'; } 
                } 
            }
        };
        var chart5 = new ApexCharts(document.querySelector("#chart-top-products-qty"), options5);
        chart5.render();

        var options6 = {
            ...commonOptions,
            series: [{ data: @json($kpis['topProductsFreq']['frequencies'] ?? []) }],
            chart: { type: 'bar', height: 350 },
            plotOptions: { bar: { borderRadius: 4, horizontal: true } },
            colors: [commonOptions.colors[1]],
            xaxis: { categories: @json($kpis['topProductsFreq']['names'] ?? []) },
            tooltip: { 
                y: { 
                    formatter: function(val) { return val.toLocaleString() + ' Mov.'; } 
                } 
            }
        };
        var chart6 = new ApexCharts(document.querySelector("#chart-top-products-freq"), options6);
        chart6.render();

        var options7 = {
            ...commonOptions,
            series: @json($kpis['abcAnalysis']['series'] ?? []),
            chart: { type: 'bar', height: 350, stacked: true, stackType: '100%' },
            plotOptions: { bar: { horizontal: true } },
            xaxis: { categories: ['Distribución por Cantidad'] },
            tooltip: { 
                y: { 
                    formatter: function(val) { return val + "%"; } 
                } 
            },
            legend: { position: 'top', horizontalAlign: 'left' }
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
            chart: { type: 'bar', height: 350 },
            plotOptions: { 
                bar: { 
                    horizontal: false, 
                    columnWidth: '55%', 
                    endingShape: 'rounded' 
                } 
            },
            dataLabels: { enabled: false },
            stroke: { show: true, width: 2, colors: ['transparent'] },
            xaxis: { categories: @json($kpis['availableCommitted']['names'] ?? []) },
            yaxis: { title: { text: 'Unidades' } },
            fill: { opacity: 1 },
            tooltip: { 
                y: { 
                    formatter: function(val) { return val.toLocaleString() + ' Un.'; } 
                } 
            },
            legend: { position: 'top' }
        };
        var chart8 = new ApexCharts(document.querySelector("#chart-available-committed"), options8);
        chart8.render();

        var options9 = {
            ...commonOptions,
            series: @json(array_values($kpis['stockByLocationType']['data'] ?? [])),
            labels: @json(array_keys($kpis['stockByLocationType']['data'] ?? [])),
            chart: { type: 'pie', height: 350 },
            legend: { position: 'bottom' },
            responsive: [{ 
                breakpoint: 480, 
                options: { 
                    chart: { width: 200 }, 
                    legend: { position: 'bottom' } 
                } 
            }]
        };
        var chart9 = new ApexCharts(document.querySelector("#chart-stock-by-location-type"), options9);
        chart9.render();

        var options11 = {
            ...commonOptions,
            series: @json(array_values($kpis['locationUtilization'] ?? [0, 0])),
            labels: ['Ubic. Ocupadas', 'Ubic. Vacías'],
            chart: { type: 'donut', height: 350 },
            colors: [commonOptions.colors[0], '#D1D5DB'],
            plotOptions: { pie: { donut: { size: '65%' } } },
            legend: { position: 'bottom' }
        };
        var chart11 = new ApexCharts(document.querySelector("#chart-location-utilization"), options11);
        chart11.render();

        var options10 = {
            ...commonOptions,
            series: [{ data: @json($kpis['topLocationsQty']['quantities'] ?? []) }],
            chart: { type: 'bar', height: 350 },
            plotOptions: { bar: { borderRadius: 4, horizontal: true } },
            colors: [commonOptions.colors[5]],
            xaxis: { categories: @json($kpis['topLocationsQty']['codes'] ?? []) },
            tooltip: { 
                y: { 
                    formatter: function(val) { return val.toLocaleString() + ' Un.'; } 
                } 
            }
        };
        var chart10 = new ApexCharts(document.querySelector("#chart-top-locations-qty"), options10);
        chart10.render();

        var options12 = {
            ...commonOptions,
            series: [{ 
                name: 'Unidades Recibidas', 
                data: @json($kpis['receivingTrend']['data'] ?? []) 
            }],
            chart: { type: 'area', height: 350, zoom: { enabled: false } },
            dataLabels: { enabled: false }, 
            stroke: { curve: 'smooth', width: 2 },
            colors: [commonOptions.colors[0]],
            xaxis: {
                type: 'datetime', 
                categories: @json($kpis['receivingTrend']['labels'] ?? []),
                labels: { format: 'dd MMM' }
            },
            yaxis: { title: { text: 'Unidades' } },
            tooltip: { 
                x: { format: 'dd MMM yyyy' }, 
                y: { 
                    formatter: function(val) { return val.toLocaleString() + ' Un.'; } 
                } 
            }
        };
        var chart12 = new ApexCharts(document.querySelector("#chart-receiving-trend"), options12);
        chart12.render();

        var options13 = {
            ...commonOptions,
            series: [{ 
                name: 'Unidades Pickeadas', 
                data: @json($kpis['pickingTrend']['data'] ?? []) 
            }],
            chart: { type: 'area', height: 350, zoom: { enabled: false } },
            dataLabels: { enabled: false }, 
            stroke: { curve: 'smooth', width: 2 },
            colors: [commonOptions.colors[1]],
            xaxis: {
                type: 'datetime', 
                categories: @json($kpis['pickingTrend']['labels'] ?? []),
                labels: { format: 'dd MMM' }
            },
            yaxis: { title: { text: 'Unidades' } },
            tooltip: { 
                x: { format: 'dd MMM yyyy' }, 
                y: { 
                    formatter: function(val) { return val.toLocaleString() + ' Un.'; } 
                } 
            }
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
            colors: [commonOptions.colors[4]],
            xaxis: { categories: @json($kpis['topProductsVol']['names'] ?? []) },
            yaxis: { title: { text: 'Volumen Ocupado (m³)' } },
            tooltip: { 
                y: { 
                    formatter: function(val) { return val.toFixed(2) + ' m³'; } 
                } 
            }
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
            chart: { type: 'pie', height: 350 },
            legend: { position: 'bottom' },
            responsive: [{ 
                breakpoint: 480, 
                options: { 
                    chart: { width: 200 }, 
                    legend: { position: 'bottom' } 
                } 
            }]
        };
        var chart15Element = document.querySelector("#chart-stock-by-brand");
        if (chart15Element) {
            var chart15 = new ApexCharts(chart15Element, options15);
            chart15.render();
        }

    });
</script>
</x-app-layout>