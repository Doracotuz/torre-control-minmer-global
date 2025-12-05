<x-app-layout>
    <x-slot name="header"></x-slot>
    <div class="min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-8 gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Centro de Control F&F</h1>
                    <p class="text-sm text-slate-500 mt-1">Monitoreo de rendimiento e inventario en tiempo real.</p>
                </div>
                
                <div class="flex flex-col xl:flex-row gap-3 items-end xl:items-center">
                    <form method="GET" action="{{ route('ff.reports.index') }}" class="relative group w-full sm:w-auto">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-slate-400 text-xs"></i>
                        </div>
                        <select name="user_id" onchange="this.form.submit()" 
                            class="pl-8 pr-8 py-2 w-full sm:w-48 bg-white border border-slate-200 rounded-lg text-xs text-slate-700 focus:ring-2 focus:ring-slate-900 focus:border-transparent shadow-sm transition-all hover:border-slate-300 appearance-none cursor-pointer">
                            <option value="">Vista Global</option>
                            @foreach ($vendedores as $vendedor)
                                <option value="{{ $vendedor->id }}" @if ($userIdFilter == $vendedor->id) selected @endif>
                                    {{ $vendedor->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-slate-400 text-xs"></i>
                        </div>
                    </form>

                    <form action="{{ route('ff.reports.generateExecutive') }}" method="POST" target="_blank" class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto items-center bg-white p-1 rounded-lg border border-slate-200 shadow-sm">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $userIdFilter }}">
                        
                        <div class="flex items-center gap-2 px-2">
                            <div class="relative">
                                <input type="date" name="start_date" required 
                                    class="border-0 p-0 text-xs text-slate-600 focus:ring-0 bg-transparent w-24 placeholder-slate-400" 
                                    placeholder="Desde" title="Fecha Inicio">
                            </div>
                            <span class="text-slate-300">/</span>
                            <div class="relative">
                                <input type="date" name="end_date" required 
                                    class="border-0 p-0 text-xs text-slate-600 focus:ring-0 bg-transparent w-24 placeholder-slate-400" 
                                    placeholder="Hasta" title="Fecha Fin">
                            </div>
                        </div>

                        <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-1.5 bg-slate-900 text-white text-xs font-medium rounded-md hover:bg-slate-800 transition-colors shadow-sm whitespace-nowrap">
                            <i class="fas fa-file-pdf mr-2"></i>
                            Generar PDF
                        </button>
                    </form>

                    <a href="{{ route('ff.dashboard.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 transition-colors shadow-sm">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="mb-6 p-4 rounded-lg bg-emerald-50 border border-emerald-100 text-emerald-800 text-sm flex items-center">
                    <i class="fas fa-check-circle mr-3 text-emerald-500"></i>
                    {{ session('success') }}
                </div>
            @endif
        
            @if (session('error'))
                <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-100 text-red-800 text-sm flex items-center">
                    <i class="fas fa-exclamation-circle mr-3 text-red-500"></i>
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <button onclick="showModal('Ingresos Totales', '{{ '$' . number_format($valorTotalVendido, 2) }}', 'Registro histórico de ventas', 'sales')" 
                    class="group relative bg-white p-6 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-all text-left">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wide">Ingresos Netos</h3>
                        <div class="p-2 bg-indigo-50 rounded-lg group-hover:bg-indigo-100 transition-colors">
                            <i class="fas fa-wallet text-indigo-600"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-slate-900">
                        {{ '$' . number_format($valorTotalVendido, 2) }}
                    </div>
                    <div class="mt-2 text-xs text-slate-400 flex items-center">
                        <span>Ver desglose de movimientos</span>
                        <i class="fas fa-arrow-right ml-1 opacity-0 group-hover:opacity-100 transition-opacity transform translate-x-0 group-hover:translate-x-1"></i>
                    </div>
                </button>

                <button onclick="showModal('Volumen de Ventas', '{{ number_format($totalUnidadesVendidas) }}', 'Unidades desplazadas totales', 'sales')" 
                    class="group relative bg-white p-6 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-all text-left">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wide">Unidades Vendidas</h3>
                        <div class="p-2 bg-emerald-50 rounded-lg group-hover:bg-emerald-100 transition-colors">
                            <i class="fas fa-box-open text-emerald-600"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-slate-900">
                        {{ number_format($totalUnidadesVendidas) }}
                    </div>
                    <div class="mt-2 text-xs text-slate-400 flex items-center">
                        <span>Ver detalle de salida</span>
                        <i class="fas fa-arrow-right ml-1 opacity-0 group-hover:opacity-100 transition-opacity transform translate-x-0 group-hover:translate-x-1"></i>
                    </div>
                </button>

                <button onclick="showModal('Inventario Crítico', '{{ number_format($stockAgotadoCount) }}', 'SKUs sin existencia física', 'exhausted')" 
                    class="group relative bg-white p-6 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-all text-left">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wide">Agotados</h3>
                        <div class="p-2 bg-rose-50 rounded-lg group-hover:bg-rose-100 transition-colors">
                            <i class="fas fa-exclamation-triangle text-rose-600"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-slate-900">
                        {{ number_format($stockAgotadoCount) }}
                        <span class="text-base font-normal text-slate-400 ml-1">SKUs</span>
                    </div>
                    <div class="mt-2 text-xs text-slate-400 flex items-center">
                        <span>Ver productos afectados</span>
                        <i class="fas fa-arrow-right ml-1 opacity-0 group-hover:opacity-100 transition-opacity transform translate-x-0 group-hover:translate-x-1"></i>
                    </div>
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm lg:col-span-2">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-base font-semibold text-slate-800">Top Productos</h3>
                        <div class="flex items-center bg-slate-100 rounded-lg p-1">
                            <button id="btn-bar" class="px-3 py-1 text-xs font-medium rounded-md shadow-sm bg-white text-slate-800 transition-all">Barras</button>
                            <button id="btn-polar" class="px-3 py-1 text-xs font-medium rounded-md text-slate-500 hover:text-slate-800 transition-all">Radial</button>
                        </div>
                    </div>
                    <div id="chart-top-productos-bar" class="chart-container w-full"></div>
                    <div id="chart-top-productos-polar" class="chart-container w-full hidden"></div>
                </div>

                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="text-base font-semibold text-slate-800 mb-6">Distribución Comercial</h3>
                    <div id="chart-ventas-vendedor" class="flex justify-center"></div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-base font-semibold text-slate-800">Herramientas de Reporte</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 divide-y lg:divide-y-0 lg:divide-x divide-slate-100">
                    
                    <a href="{{ route('ff.reports.transactions') }}" class="group p-6 hover:bg-slate-50 transition-colors">
                        <div class="flex items-center mb-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center mr-3 group-hover:bg-blue-100 transition-colors">
                                <i class="fas fa-receipt text-blue-600 text-sm"></i>
                            </div>
                            <span class="text-xs font-bold text-slate-400 uppercase">Detalle</span>
                        </div>
                        <h4 class="text-sm font-semibold text-slate-900 group-hover:text-blue-700 transition-colors">Transacciones</h4>
                        <p class="text-xs text-slate-500 mt-1">Historial completo de movimientos.</p>
                    </a>

                    <a href="{{ route('ff.reports.inventoryAnalysis') }}" class="group p-6 hover:bg-slate-50 transition-colors">
                        <div class="flex items-center mb-3">
                            <div class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center mr-3 group-hover:bg-violet-100 transition-colors">
                                <i class="fas fa-chart-line text-violet-600 text-sm"></i>
                            </div>
                            <span class="text-xs font-bold text-slate-400 uppercase">Flujo</span>
                        </div>
                        <h4 class="text-sm font-semibold text-slate-900 group-hover:text-violet-700 transition-colors">Mov. Inventario</h4>
                        <p class="text-xs text-slate-500 mt-1">Entradas, salidas y rotación.</p>
                    </a>

                    <a href="{{ route('ff.reports.stockAvailability') }}" class="group p-6 hover:bg-slate-50 transition-colors">
                        <div class="flex items-center mb-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center mr-3 group-hover:bg-amber-100 transition-colors">
                                <i class="fas fa-warehouse text-amber-600 text-sm"></i>
                            </div>
                            <span class="text-xs font-bold text-slate-400 uppercase">Stock</span>
                        </div>
                        <h4 class="text-sm font-semibold text-slate-900 group-hover:text-amber-700 transition-colors">Disponibilidad</h4>
                        <p class="text-xs text-slate-500 mt-1">Existencias vs Reservas actuales.</p>
                    </a>

                    <a href="{{ route('ff.reports.catalogAnalysis') }}" class="group p-6 hover:bg-slate-50 transition-colors">
                        <div class="flex items-center mb-3">
                            <div class="w-8 h-8 rounded-lg bg-teal-50 flex items-center justify-center mr-3 group-hover:bg-teal-100 transition-colors">
                                <i class="fas fa-tags text-teal-600 text-sm"></i>
                            </div>
                            <span class="text-xs font-bold text-slate-400 uppercase">Data</span>
                        </div>
                        <h4 class="text-sm font-semibold text-slate-900 group-hover:text-teal-700 transition-colors">Catálogo y Precios</h4>
                        <p class="text-xs text-slate-500 mt-1">Análisis de precios y marcas.</p>
                    </a>

                    <a href="{{ route('ff.reports.sellerPerformance') }}" class="group p-6 hover:bg-slate-50 transition-colors">
                        <div class="flex items-center mb-3">
                            <div class="w-8 h-8 rounded-lg bg-pink-50 flex items-center justify-center mr-3 group-hover:bg-pink-100 transition-colors">
                                <i class="fas fa-users text-pink-600 text-sm"></i>
                            </div>
                            <span class="text-xs font-bold text-slate-400 uppercase">KPIs</span>
                        </div>
                        <h4 class="text-sm font-semibold text-slate-900 group-hover:text-pink-700 transition-colors">Desempeño</h4>
                        <p class="text-xs text-slate-500 mt-1">Métricas por vendedor.</p>
                    </a>

                </div>
            </div>
        </div>
    </div>

    <div id="data-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" onclick="closeModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-semibold text-slate-900" id="modal-title"></h3>
                            <div class="mt-1">
                                <p class="text-sm text-slate-500" id="modal-subtitle"></p>
                                <p class="text-3xl font-bold text-slate-800 mt-2" id="modal-value"></p>
                            </div>
                            <div class="mt-6">
                                <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Actividad Reciente</h4>
                                <div id="modal-transaction-list" class="space-y-3 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="closeModal()" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-slate-900 text-base font-medium text-white hover:bg-slate-800 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        const dataTopProductos = @json($chartTopProductos);
        const dataVentasVendedor = @json($chartVentasVendedor);
        const stockAgotadoCount = {{ $stockAgotadoCount }};
        const currentUserId = document.getElementById('user_id') ? document.getElementById('user_id').value : '';

        function closeModal() {
            document.getElementById('data-modal').classList.add('hidden');
        }

        function showModal(title, value, subtitle, metricType) {
            const listContainer = document.getElementById('modal-transaction-list');
            
            document.getElementById('modal-title').textContent = title;
            document.getElementById('modal-value').textContent = value;
            document.getElementById('modal-subtitle').textContent = subtitle;
            document.getElementById('data-modal').classList.remove('hidden');

            listContainer.innerHTML = '<div class="flex justify-center py-8"><i class="fas fa-circle-notch fa-spin text-2xl text-slate-400"></i></div>';

            if (metricType === 'exhausted') {
                listContainer.innerHTML = `
                    <div class="text-center py-4">
                        <div class="bg-rose-50 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-clipboard-list text-rose-500"></i>
                        </div>
                        <p class="text-sm text-slate-600">Hay <strong>${stockAgotadoCount} SKUs</strong> sin inventario.</p>
                        <a href="{{ route('ff.reports.stockAvailability') }}" class="mt-3 inline-block text-sm font-medium text-indigo-600 hover:text-indigo-500">Ver reporte de disponibilidad &rarr;</a>
                    </div>`;
                return;
            }

            let url = '{{ route('ff.reports.api.recentMovements') }}';
            let params = new URLSearchParams({ 
                user_id: currentUserId,
                limit: 10
            });

            fetch(`${url}?${params.toString()}`)
                .then(response => response.ok ? response.json() : Promise.reject('Network response was not ok'))
                .then(data => {
                    if (data.length === 0) {
                        listContainer.innerHTML = '<p class="text-sm text-slate-400 text-center py-4">No hay movimientos recientes.</p>';
                        return;
                    }

                    listContainer.innerHTML = data.map(item => `
                        <div class="group flex items-center justify-between p-3 rounded-lg border border-slate-100 hover:border-slate-200 hover:bg-slate-50 transition-all">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center">
                                    <i class="fas fa-shopping-bag text-slate-500 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-800">${item.detail}</p>
                                    <p class="text-xs text-slate-500">${item.user} &bull; ${item.time}</p>
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-slate-700">${item.value}</span>
                        </div>
                    `).join('');
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    listContainer.innerHTML = '<p class="text-sm text-red-500 text-center">Error al cargar datos.</p>';
                });
        }
        
        const commonChartOptions = {
            fontFamily: 'Inter, sans-serif',
            toolbar: { show: false },
        };

        var optionsTopProductosBar = {
            ...commonChartOptions,
            series: [{
                name: 'Unidades',
                data: dataTopProductos.series[0].data.map(Number)
            }], 
            chart: {
                type: 'bar',
                height: 320,
                toolbar: { show: false }
            },
            colors: ['#334155'],
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 6,
                    barHeight: '60%',
                    dataLabels: { position: 'right' }
                }
            },
            dataLabels: {
                enabled: true,
                textAnchor: 'start',
                style: { colors: ['#334155'], fontSize: '11px', fontWeight: 600 },
                formatter: function (val, opt) {
                    return val.toLocaleString()
                },
                offsetX: 0,
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4,
            },
            xaxis: {
                categories: dataTopProductos.categories,
                labels: { style: { colors: '#64748b', fontSize: '11px' } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: { style: { colors: '#475569', fontSize: '12px', fontWeight: 500 }, maxWidth: 160 }
            },
            tooltip: {
                theme: 'light',
                y: { formatter: (val) => val.toLocaleString() + " unidades" }
            }
        };

        var optionsTopProductosPolar = {
            ...commonChartOptions,
            series: dataTopProductos.series[0].data.map(Number),
            chart: {
                type: 'polarArea',
                height: 380
            },
            labels: dataTopProductos.categories,
            colors: ['#1e293b', '#334155', '#475569', '#64748b', '#94a3b8'],
            fill: { opacity: 0.9 },
            stroke: { width: 1, colors: ['#fff'] },
            yaxis: { show: false },
            legend: { position: 'bottom', fontSize: '12px', fontFamily: 'Inter, sans-serif', markers: { radius: 12 } },
            tooltip: { theme: 'light', y: { formatter: (val) => val.toLocaleString() + " unidades" } }
        };

        var optionsVentasVendedor = {
            ...commonChartOptions,
            series: dataVentasVendedor.series.map(Number),
            chart: {
                type: 'donut',
                height: 340
            },
            labels: dataVentasVendedor.labels,
            colors: ['#0f172a', '#334155', '#475569', '#64748b', '#94a3b8', '#cbd5e1'],
            stroke: { show: true, colors: ['#fff'], width: 2 },
            dataLabels: { enabled: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '75%',
                        labels: {
                            show: true,
                            name: { show: true, fontSize: '12px', fontFamily: 'Inter, sans-serif', color: '#64748b', offsetY: -5 },
                            value: { show: true, fontSize: '24px', fontFamily: 'Inter, sans-serif', fontWeight: 700, color: '#1e293b', offsetY: 5, formatter: (val) => parseInt(val).toLocaleString() },
                            total: {
                                show: true,
                                label: 'Total Unidades',
                                color: '#64748b',
                                fontSize: '11px',
                                formatter: function (w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString();
                                }
                            }
                        }
                    }
                }
            },
            legend: { 
                position: 'bottom', 
                fontSize: '12px', 
                fontFamily: 'Inter, sans-serif',
                itemMargin: { horizontal: 10, vertical: 5 }
            },
            tooltip: { theme: 'light' }
        };
        
        document.addEventListener('DOMContentLoaded', function() {
            const barChart = new ApexCharts(document.querySelector("#chart-top-productos-bar"), optionsTopProductosBar);
            barChart.render();

            const polarChart = new ApexCharts(document.querySelector("#chart-top-productos-polar"), optionsTopProductosPolar);
            polarChart.render();
            
            const vendedorChart = new ApexCharts(document.querySelector("#chart-ventas-vendedor"), optionsVentasVendedor);
            vendedorChart.render();

            const btnBar = document.getElementById('btn-bar');
            const btnPolar = document.getElementById('btn-polar');
            const containerBar = document.getElementById('chart-top-productos-bar');
            const containerPolar = document.getElementById('chart-top-productos-polar');

            const setActive = (activeBtn, inactiveBtn) => {
                activeBtn.classList.remove('text-slate-500', 'hover:text-slate-800', 'bg-transparent');
                activeBtn.classList.add('bg-white', 'text-slate-800', 'shadow-sm');
                
                inactiveBtn.classList.add('text-slate-500', 'hover:text-slate-800', 'bg-transparent');
                inactiveBtn.classList.remove('bg-white', 'text-slate-800', 'shadow-sm');
            };

            btnBar.addEventListener('click', () => {
                containerBar.classList.remove('hidden');
                containerPolar.classList.add('hidden');
                setActive(btnBar, btnPolar);
            });

            btnPolar.addEventListener('click', () => {
                containerPolar.classList.remove('hidden');
                containerBar.classList.add('hidden');
                setActive(btnPolar, btnBar);
            });
        });
    </script>
</x-app-layout>