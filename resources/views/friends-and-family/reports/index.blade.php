<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ __('Centro de Mando BI Friends & Family') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="space-y-10">

                <div class="bg-white p-6 rounded-2xl shadow-xl border-t-4 border-gray-300">
                    <form method="GET" action="{{ route('ff.reports.index') }}" class="flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-4">
                        <label for="user_id" class="text-lg font-semibold text-gray-700 whitespace-nowrap">Análisis por Vendedor:</label>
                        
                        <select name="user_id" id="user_id" onchange="this.form.submit()" class="form-select block w-full md:w-64 rounded-xl shadow-inner border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition duration-150 ease-in-out">
                            <option value="">-- Todos los Vendedores --</option>
                            @foreach ($vendedores as $vendedor)
                                <option value="{{ $vendedor->id }}" @if ($userIdFilter == $vendedor->id) selected @endif>
                                    {{ $vendedor->name }}
                                </option>
                            @endforeach
                        </select>

                        @if ($userIdFilter)
                            <a href="{{ route('ff.reports.index') }}" class="text-sm text-red-600 hover:text-red-800 font-medium transition duration-150 ease-in-out">
                                <i class="fas fa-times-circle mr-1"></i> Limpiar Filtro
                            </a>
                        @endif
                    </form>
                </div>
                
                @php
                    $rendimientoClase = ($valorTotalVendido >= 5000) ? 'bg-green-100 border-green-500 text-green-700' : 
                                        (($valorTotalVendido > 0) ? 'bg-blue-100 border-blue-500 text-blue-700' : 'bg-gray-100 border-gray-400 text-gray-700');
                    $mensajeRendimiento = ($valorTotalVendido >= 1000000) ? '¡Excelente progreso! Se ha superado la marca de los $5,000 en ventas.' :
                                          (($valorTotalVendido > 0) ? 'El evento está activo. Continúa monitoreando las métricas clave.' : 'Comienza tu análisis.');
                @endphp
                
                <div class="p-4 rounded-xl shadow-md border-l-4 {{ $rendimientoClase }}">
                    <p class="font-bold text-lg">{{ $mensajeRendimiento }}</p>
                    @if ($userIdFilter)
                        <p class="text-sm mt-1">
                            Desempeño individual para: **{{ $vendedores->find($userIdFilter)->name ?? 'N/A' }}**
                        </p>
                    @endif
                </div>

                <h3 class="text-xl font-extrabold text-gray-700 pt-4 border-b pb-2">Métricas Clave del Evento</h3>
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">

                    <button onclick="showModal('Valor Total Vendido', '{{ '$' . number_format($valorTotalVendido, 2) }}', 'Últimas 10 Ventas registradas en el sistema.', 'sales')" class="kpi-card group p-6 rounded-2xl shadow-2xl bg-gradient-to-br from-indigo-700 to-indigo-900 text-white overflow-hidden relative cursor-pointer hover:shadow-4xl transform hover:-translate-y-0.5 transition duration-300">
                        <i class="fas fa-dollar-sign absolute right-4 top-4 text-6xl opacity-10 group-hover:rotate-12 transition duration-500"></i>
                        <p class="text-sm font-light uppercase tracking-widest opacity-90">Ingreso Bruto</p>
                        <p class="mt-1 text-5xl font-black tracking-tight transition-all duration-300 ease-in-out">
                            {{ '$' . number_format($valorTotalVendido, 2) }}
                        </p>
                        <p class="text-xs opacity-70 mt-2">Click para ver detalle.</p>
                    </button>

                    <button onclick="showModal('Total Unidades Vendidas', '{{ number_format($totalUnidadesVendidas) }}', 'Detalle de los últimos movimientos de venta.', 'sales')" class="kpi-card group p-6 rounded-2xl shadow-2xl bg-gradient-to-br from-green-700 to-green-900 text-white overflow-hidden relative cursor-pointer hover:shadow-4xl transform hover:-translate-y-0.5 transition duration-300">
                        <i class="fas fa-cubes absolute right-4 top-4 text-6xl opacity-10 group-hover:scale-110 transition duration-500"></i>
                        <p class="text-sm font-light uppercase tracking-widest opacity-90">Unidades Vendidas</p>
                        <p class="mt-1 text-5xl font-black tracking-tight transition-all duration-300 ease-in-out">
                            {{ number_format($totalUnidadesVendidas) }}
                        </p>
                        <p class="text-xs opacity-70 mt-2">Click para ver detalle.</p>
                    </button>

                    <button onclick="showModal('SKUs Agotados (Global)', '{{ number_format($stockAgotadoCount) }}', 'Lista de productos que necesitan reabastecimiento o ajuste.', 'exhausted')" class="kpi-card group p-6 rounded-2xl shadow-2xl bg-gradient-to-br from-red-700 to-red-900 text-white overflow-hidden relative cursor-pointer hover:shadow-4xl transform hover:-translate-y-0.5 transition duration-300">
                        <i class="fas fa-exclamation-triangle absolute right-4 top-4 text-6xl opacity-10 group-hover:animate-pulse transition duration-500"></i>
                        <p class="text-sm font-light uppercase tracking-widest opacity-90">Productos Agotados</p>
                        <p class="mt-1 text-5xl font-black tracking-tight transition-all duration-300 ease-in-out">
                            {{ number_format($stockAgotadoCount) }}
                        </p>
                        <p class="text-xs opacity-70 mt-2">Click para ver lista global.</p>
                    </button>

                    <div class="kpi-card group p-6 rounded-2xl shadow-2xl bg-gradient-to-br from-yellow-700 to-yellow-900 text-white overflow-hidden relative">
                        <i class="fas fa-clock absolute right-4 top-4 text-6xl opacity-10 group-hover:rotate-[-12deg] transition duration-500"></i>
                        <p class="text-sm font-light uppercase tracking-widest opacity-90">Tiempo Restante</p>
                        <p class="mt-1 text-3xl font-black tracking-tight transition-all duration-300 ease-in-out">
                            {{ $daysRemainingFormatted }}
                        </p>
                        <p class="text-xs opacity-70 mt-2">Días, horas y minutos para el cierre del evento.</p>
                    </div>

                </div>

                <h3 class="text-xl font-extrabold text-gray-700 pt-4 border-b pb-2">Visualización de Rendimiento</h3>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-100 lg:col-span-2 space-y-4">
                        <div class="flex justify-between items-center border-b pb-3">
                            <h4 class="text-lg font-semibold text-gray-900">Top 5 Productos más Vendidos</h4>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-600">Barra</span>
                                <label class="switch">
                                    <input type="checkbox" id="toggle-chart-view">
                                    <span class="slider round"></span>
                                </label>
                                <span class="text-sm text-gray-600">Polar</span>
                            </div>
                        </div>
                        <div id="chart-top-productos-bar" class="chart-container"></div>
                        <div id="chart-top-productos-polar" class="chart-container hidden"></div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-100 lg:col-span-1">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-3">
                            Distribución por Vendedor
                        </h4>
                        <div id="chart-ventas-vendedor" style="min-height: 400px;"></div>
                    </div>
                </div>
                
                <h3 class="text-xl font-extrabold text-gray-700 border-b pb-2">Navegación Rápida a Reportes</h3>
                <div class="grid grid-cols-2 gap-6 md:grid-cols-5">

                    <a href="{{ route('ff.reports.transactions') }}" class="report-card group p-5 rounded-2xl shadow-lg border-b-4 border-blue-500 bg-gradient-to-br from-white to-blue-50 hover:shadow-2xl hover:border-blue-700 transition duration-300 transform hover:scale-[1.02]">
                        <i class="fas fa-receipt text-3xl text-blue-600 group-hover:text-blue-800 transition"></i>
                        <p class="text-xs font-bold text-blue-700 uppercase tracking-wider mt-3">R2</p>
                        <p class="text-lg font-extrabold text-gray-900 leading-tight">Transacciones</p>
                    </a>
                    
                    <a href="{{ route('ff.reports.inventoryAnalysis') }}" class="report-card group p-5 rounded-2xl shadow-lg border-b-4 border-purple-500 bg-gradient-to-br from-white to-purple-50 hover:shadow-2xl hover:border-purple-700 transition duration-300 transform hover:scale-[1.02]">
                        <i class="fas fa-boxes text-3xl text-purple-600 group-hover:text-purple-800 transition"></i>
                        <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mt-3">R3</p>
                        <p class="text-lg font-extrabold text-gray-900 leading-tight">Mov. Inventario</p>
                    </a>

                    <a href="{{ route('ff.reports.stockAvailability') }}" class="report-card group p-5 rounded-2xl shadow-lg border-b-4 border-orange-500 bg-gradient-to-br from-white to-orange-50 hover:shadow-2xl hover:border-orange-700 transition duration-300 transform hover:scale-[1.02]">
                        <i class="fas fa-warehouse text-3xl text-orange-600 group-hover:text-orange-800 transition"></i>
                        <p class="text-xs font-bold text-orange-700 uppercase tracking-wider mt-3">R4</p>
                        <p class="text-lg font-extrabold text-gray-900 leading-tight">Disponibilidad</p>
                    </a>
                    
                    <a href="{{ route('ff.reports.catalogAnalysis') }}" class="report-card group p-5 rounded-2xl shadow-lg border-b-4 border-teal-500 bg-gradient-to-br from-white to-teal-50 hover:shadow-2xl hover:border-teal-700 transition duration-300 transform hover:scale-[1.02]">
                        <i class="fas fa-tags text-3xl text-teal-600 group-hover:text-teal-800 transition"></i>
                        <p class="text-xs font-bold text-teal-700 uppercase tracking-wider mt-3">R5</p>
                        <p class="text-lg font-extrabold text-gray-900 leading-tight">Catálogo / Precios</p>
                    </a>
                    
                    <a href="{{ route('ff.reports.sellerPerformance') }}" class="report-card group p-5 rounded-2xl shadow-lg border-b-4 border-pink-500 bg-gradient-to-br from-white to-pink-50 hover:shadow-2xl hover:border-pink-700 transition duration-300 transform hover:scale-[1.02]">
                        <i class="fas fa-trophy text-3xl text-pink-600 group-hover:text-pink-800 transition"></i>
                        <p class="text-xs font-bold text-pink-700 uppercase tracking-wider mt-3">R6</p>
                        <p class="text-lg font-extrabold text-gray-900 leading-tight">Desempeño</p>
                    </a>
                </div>

            </div>
        </div>
    </div>
    
    <div id="data-modal" class="hidden fixed inset-0 z-50 modal-mask flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl p-6 transform transition-all duration-300 ease-in-out">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="text-2xl font-bold text-gray-800" id="modal-title">Detalle de Métrica</h3>
                <button onclick="document.getElementById('data-modal').classList.add('hidden')" class="text-gray-500 hover:text-gray-900">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div id="modal-content">
                <p class="text-lg font-extrabold text-indigo-600 mb-3" id="modal-value"></p>
                <p class="text-gray-600 mb-4" id="modal-subtitle"></p>

                <h4 class="text-md font-bold text-gray-700 mb-2">Últimos 10 Eventos Relevantes:</h4>
                <div id="modal-transaction-list" class="space-y-2 max-h-60 overflow-y-auto">
                    <p class="text-gray-500">Cargando...</p>
                </div>
            </div>
            
            <div class="mt-6 text-right">
                <button onclick="document.getElementById('data-modal').classList.add('hidden')" class="px-4 py-2 bg-indigo-500 text-white font-semibold rounded-lg hover:bg-indigo-600 transition duration-150">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        const dataTopProductos = @json($chartTopProductos);
        const dataVentasVendedor = @json($chartVentasVendedor);
        const stockAgotadoCount = {{ $stockAgotadoCount }};

        let chartBar, chartPolar, chartVendedor;

        function showModal(title, value, subtitle, metricType) {
            const currentUserId = document.getElementById('user_id').value;
            const listContainer = document.getElementById('modal-transaction-list');
            
            document.getElementById('modal-title').textContent = title;
            document.getElementById('modal-value').textContent = value;
            document.getElementById('modal-subtitle').textContent = subtitle;
            document.getElementById('data-modal').classList.remove('hidden');

            listContainer.innerHTML = '<div class="flex justify-center py-5"><i class="fas fa-spinner fa-spin text-4xl text-indigo-500"></i></div>';

            if (metricType === 'exhausted') {
                listContainer.innerHTML = `<p class="text-gray-500 italic">Para ${stockAgotadoCount} SKU agotados, por favor use el Reporte 4: Disponibilidad para obtener la lista completa y detallada de estos productos.</p><p class="mt-2 text-sm text-red-500 font-medium">Esta vista solo carga transacciones.</p>`;
                return;
            }

            let url = '{{ route('ff.reports.api.recentMovements') }}';
            let params = new URLSearchParams({ 
                user_id: currentUserId,
                limit: 10
            });

            fetch(`${url}?${params.toString()}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.length === 0) {
                        listContainer.innerHTML = '<p class="text-gray-500 italic">No se encontraron movimientos recientes que coincidan con este filtro.</p>';
                        return;
                    }

                    listContainer.innerHTML = data.map(item => `
                        <div class="flex items-center space-x-3 p-2 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-150">
                            <i class="fas fa-${item.icon} text-lg text-indigo-500"></i>
                            <div class="flex-grow">
                                <p class="text-sm font-medium text-gray-900">${item.detail}</p> 
                                <p class="text-xs text-gray-500">Vendedor: ${item.user} - ${item.time}</p>
                            </div>
                            <span class="text-sm font-semibold text-right text-gray-700">${item.value}</span>
                        </div>
                    `).join('');
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    listContainer.innerHTML = '<p class="text-red-500 font-medium">Error al cargar datos: Intente nuevamente.</p>';
                });
        }
        
        var optionsTopProductosBar = {
            series: [{
                name: 'Unidades Vendidas',
                data: dataTopProductos.series[0].data.map(Number)
            }], 
            chart: {
                type: 'bar',
                height: 350,
                toolbar: { show: false }
            },
            colors: ['#3b82f6'],
            plotOptions: {
                bar: {
                    horizontal: true,
                    dataLabels: { position: 'top' },
                    borderRadius: 4,
                }
            },
            dataLabels: {
                enabled: true,
                offsetX: 10,
                style: {
                    fontSize: '12px',
                    colors: ['#fff']
                }
            },
            xaxis: {
                categories: dataTopProductos.categories,
                title: {
                    text: 'Unidades Vendidas'
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val.toLocaleString() + " unidades"
                    }
                }
            }
        };

        var optionsTopProductosPolar = {
            series: dataTopProductos.series[0].data.map(Number),
            chart: {
                type: 'polarArea',
                height: 400,
                toolbar: { show: false }
            },
            labels: dataTopProductos.categories,
            fill: { opacity: 0.9 },
            stroke: { width: 1 },
            yaxis: { show: false },
            legend: { position: 'bottom' },
            tooltip: {
                y: {
                    formatter: (val) => val.toLocaleString() + " unidades"
                }
            }
        };

        var optionsVentasVendedor = {
            series: dataVentasVendedor.series.map(Number),
            chart: {
                type: 'donut',
                height: 400
            },
            labels: dataVentasVendedor.labels,
            plotOptions: {
                pie: {
                    donut: {
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total Unidades',
                                formatter: function (w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString();
                                }
                            }
                        }
                    }
                }
            },
            legend: { 
                position: 'right'
            }
        };
        
        document.addEventListener('DOMContentLoaded', function() {
            chartBar = new ApexCharts(document.querySelector("#chart-top-productos-bar"), optionsTopProductosBar);
            chartBar.render();

            chartPolar = new ApexCharts(document.querySelector("#chart-top-productos-polar"), optionsTopProductosPolar);
            chartPolar.render();
            
            chartVendedor = new ApexCharts(document.querySelector("#chart-ventas-vendedor"), optionsVentasVendedor);
            chartVendedor.render();

            document.getElementById('toggle-chart-view').addEventListener('change', function() {
                const barContainer = document.getElementById('chart-top-productos-bar');
                const polarContainer = document.getElementById('chart-top-productos-polar');
                
                if (this.checked) {
                    barContainer.classList.add('hidden');
                    polarContainer.classList.remove('hidden');
                } else {
                    polarContainer.classList.add('hidden');
                    barContainer.classList.remove('hidden');
                }
            });
        });
    </script>
</x-app-layout>