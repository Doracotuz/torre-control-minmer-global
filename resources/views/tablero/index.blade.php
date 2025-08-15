<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard ') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-10xl mx-auto sm:px-6 lg:px-8 space-y-12">
            
            <div class="bg-[#FFF1DC] overflow-hidden shadow-xl rounded-[40px] p-6 sm:p-10">
                <h3 class="text-2xl font-bold text-[#2c3856]" style="font-family: 'Raleway', sans-serif;">Áreas Disponibles</h3>
                <p class="mt-2 text-gray-500">Acceso rápido a las áreas de tus proyectos principales.</p>

                <div class="mt-8">
                    @if($accessibleRootFolders->isNotEmpty())
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
                            @foreach($accessibleRootFolders as $folder)
                                {{-- Verifica si el usuario actual está en la lista de IDs restringidos --}}
                                @php
                                    $restrictedUsers = ['24', '25', '26', '27', '4'];
                                    $isRestricted = in_array(Auth::id(), $restrictedUsers);
                                @endphp

                                @if($isRestricted)
                                    {{-- Si el usuario está restringido, usa un span para mostrar el modal --}}
                                    <a href="#" @click.prevent="showAccessDeniedModal()" class="group bg-white rounded-xl shadow-md p-6 flex flex-col items-center text-center transition-all duration-300 ease-in-out hover:shadow-2xl hover:-translate-y-2">
                                @else
                                    {{-- Si el usuario no está restringido, usa el enlace original --}}
                                    <a href="{{ route('folders.index', ['folder' => $folder->id]) }}" class="group bg-white rounded-xl shadow-md p-6 flex flex-col items-center text-center transition-all duration-300 ease-in-out hover:shadow-2xl hover:-translate-y-2">
                                @endif
                                    <div class="bg-[#DFE5F5] p-6 rounded-full transition-colors duration-300 group-hover:bg-[#ff9c00]">
                                        @if($folder->area?->icon_path)
                                            <img src="{{ Storage::disk('s3')->url($folder->area->icon_path) }}" alt="Icono de {{ $folder->area->name }}" class="w-24 h-24 object-contain">
                                        @else
                                            <svg class="w-12 h-12 text-[#2c3856] transition-colors duration-300 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                                            </svg>
                                        @endif
                                    </div>
                                    <h4 class="mt-4 text-lg font-semibold text-[#2c3856]">
                                        {{ $folder->area?->name ?? $folder->name }}
                                    </h4>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-gray-500 py-8">No tienes acceso a ninguna carpeta de área por el momento.</p>
                    @endif
                </div>

            </div>

            <div class="bg-[#F0F3FA] overflow-hidden shadow-xl rounded-[40px]">
                <div class="p-6 sm:px-10 bg-[#F0F3FA] border-gray-200">
                    <h3 class="text-2xl font-bold text-[#2c3856]">Indicadores Clave de Rendimiento</h3>
                </div>
                <div class="p-6 sm:p-10">
                    @if(!empty($chartData))
                        <div x-data='{ 
                            selectedYears: @json($años), 
                            selectedZones: @json($zonas),
                            selectedMonths: @json($meses),
                            allYears: @json($años),
                            allZones: @json($zonas),
                            allMonths: @json($meses),
                            chartData: @json($chartData)
                        }'>
                            <!-- <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
                                <div x-data="{ open: false }" @click.outside="open = false">
                                    <label class="block text-sm font-medium text-gray-700">Filtrar por Año</label>
                                    <div class="relative mt-1">
                                        <button @click="open = !open" type="button" class="relative w-full cursor-default rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-left shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                            <span class="block truncate" x-text="selectedYears.length === allYears.length ? 'Todos' : selectedYears.join(', ') || 'Seleccionar años'"></span>
                                            <span class="pointer-events-none absolute inset-y-0 right-0 ml-3 flex items-center pr-2">
                                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.55.25l7.5 7.5a.75.75 0 01-1.06 1.06L10 4.81l-6.94 6.94a.75.75 0 11-1.06-1.06l7.5-7.5A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                                            </span>
                                        </button>
                                        <div x-show="open" class="absolute z-10 mt-1 w-full rounded-md bg-white shadow-lg">
                                            <ul tabindex="-1" role="listbox" class="max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm overflow-auto">
                                                <li class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9">
                                                    <label class="flex items-center">
                                                        <input type="checkbox" @click="selectedYears.length === allYears.length ? selectedYears = [] : selectedYears = allYears" :checked="selectedYears.length === allYears.length" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                        <span class="ml-3 block font-normal truncate">Seleccionar Todo</span>
                                                    </label>
                                                </li>
                                                @foreach($años as $año)
                                                <li class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9">
                                                    <label class="flex items-center">
                                                        <input type="checkbox" x-model="selectedYears" value="{{ $año }}" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                        <span class="ml-3 block font-normal truncate">{{ $año }}</span>
                                                    </label>
                                                </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div x-data="{ open: false }" @click.outside="open = false">
                                    <label class="block text-sm font-medium text-gray-700">Filtrar por Zona</label>
                                    <div class="relative mt-1">
                                        <button @click="open = !open" type="button" class="relative w-full cursor-default rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-left shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                            <span class="block truncate" x-text="selectedZones.length === allZones.length ? 'Todas' : selectedZones.join(', ') || 'Seleccionar zonas'"></span>
                                            <span class="pointer-events-none absolute inset-y-0 right-0 ml-3 flex items-center pr-2">
                                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.55.25l7.5 7.5a.75.75 0 01-1.06 1.06L10 4.81l-6.94 6.94a.75.75 0 11-1.06-1.06l7.5-7.5A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                                            </span>
                                        </button>
                                        <div x-show="open" class="absolute z-10 mt-1 w-full rounded-md bg-white shadow-lg">
                                            <ul tabindex="-1" role="listbox" class="max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm overflow-auto">
                                                <li class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9">
                                                    <label class="flex items-center">
                                                        <input type="checkbox" @click="selectedZones.length === allZones.length ? selectedZones = [] : selectedZones = allZones" :checked="selectedZones.length === allZones.length" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                        <span class="ml-3 block font-normal truncate">Seleccionar Todo</span>
                                                    </label>
                                                </li>
                                                @foreach($zonas as $zona)
                                                <li class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9">
                                                    <label class="flex items-center">
                                                        <input type="checkbox" x-model="selectedZones" value="{{ $zona }}" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                        <span class="ml-3 block font-normal truncate">{{ $zona }}</span>
                                                    </label>
                                                </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div x-data="{ open: false }" @click.outside="open = false">
                                    <label class="block text-sm font-medium text-gray-700">Filtrar por Mes</label>
                                    <div class="relative mt-1">
                                        <button @click="open = !open" type="button" class="relative w-full cursor-default rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-left shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                            <span class="block truncate" x-text="selectedMonths.length === allMonths.length ? 'Todos' : selectedMonths.join(', ') || 'Seleccionar meses'"></span>
                                            <span class="pointer-events-none absolute inset-y-0 right-0 ml-3 flex items-center pr-2">
                                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.55.25l7.5 7.5a.75.75 0 01-1.06 1.06L10 4.81l-6.94 6.94a.75.75 0 11-1.06-1.06l7.5-7.5A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                                            </span>
                                        </button>
                                        <div x-show="open" class="absolute z-10 mt-1 w-full rounded-md bg-white shadow-lg">
                                            <ul tabindex="-1" role="listbox" class="max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm overflow-auto">
                                                <li class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9">
                                                    <label class="flex items-center">
                                                        <input type="checkbox" @click="selectedMonths.length === allMonths.length ? selectedMonths = [] : selectedMonths = allMonths" :checked="selectedMonths.length === allMonths.length" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                        <span class="ml-3 block font-normal truncate">Seleccionar Todo</span>
                                                    </label>
                                                </li>
                                                @foreach($meses as $mes)
                                                <li class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9">
                                                    <label class="flex items-center">
                                                        <input type="checkbox" x-model="selectedMonths" value="{{ $mes }}" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                        <span class="ml-3 block font-normal truncate">{{ $mes }}</span>
                                                    </label>
                                                </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div> -->

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8" x-init="
                                setTimeout(() => {
                                    renderCharts(chartData, selectedYears, selectedZones, selectedMonths);
                                }, 50);
                                $watch('selectedYears', (value) => renderCharts(chartData, value, selectedZones, selectedMonths));
                                $watch('selectedZones', (value) => renderCharts(chartData, selectedYears, value, selectedMonths));
                                $watch('selectedMonths', (value) => renderCharts(chartData, selectedYears, selectedZones, value));
                            ">
                                {{-- Columna Izquierda --}}
                                <div class="space-y-8">
                                    <div class="p-4 border rounded-lg shadow-md">
                                        <h4 class="text-center font-semibold text-gray-600 mb-2">Cantidad de Embarques por Zona</h4>
                                        <div id="graficoEmbarquesZona"></div>
                                    </div>
                                    <div class="p-4 border rounded-lg shadow-md">
                                        <h4 class="text-center font-semibold text-gray-600 mb-2">Cantidad de Expeditados por Zona</h4>
                                        <div id="graficoExpeditadosZona"></div>
                                    </div>
                                    <div class="p-4 border rounded-lg shadow-md">
                                        <h4 class="text-center font-semibold text-gray-600 mb-2">Entregas a Tiempo por Zona (%)</h4>
                                        <div id="graficoTiempoZona"></div>
                                    </div>
                                </div>
                                {{-- Columna Derecha --}}
                                <div class="space-y-8">
                                    <div class="p-4 border rounded-lg shadow-md">
                                        <h4 class="text-center font-semibold text-gray-600 mb-2">Cantidad de Embarques por Mes</h4>
                                        <div id="graficoEmbarquesMes"></div>
                                    </div>
                                    <div class="p-4 border rounded-lg shadow-md">
                                        <h4 class="text-center font-semibold text-gray-600 mb-2">Cantidad de Expeditados por Mes</h4>
                                        <div id="graficoExpeditadosMes"></div>
                                    </div>
                                    <div class="p-4 border rounded-lg shadow-md">
                                        <h4 class="text-center font-semibold text-gray-600 mb-2">Entregas a Tiempo por Mes (%)</h4>
                                        <div id="graficoTiempoMes"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-center text-gray-500 py-8">No hay datos de KPIs disponibles para generar los gráficos.</p>
                    @endif
                </div>
            </div>

            @if(Auth::user()->is_area_admin && Auth::user()->area?->name === 'Administración')
            <div class="bg-white overflow-hidden shadow-xl rounded-[40px]">
                <div class="p-6 sm:px-10 bg-white border-b border-gray-200">
                    <h3 class="text-2xl font-bold text-[#2c3856]" style="font-family: 'Raleway', sans-serif;">Actualizar Datos de KPIs</h3>
                </div>
                <div class="p-6 sm:p-10">
                    @if(session('success'))
                        <div class="mb-4 font-medium text-sm text-green-600 bg-green-100 p-3 rounded-lg">{{ session('success') }}</div>
                    @endif
                    <form action="{{ route('tablero.uploadKpis') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        <div>
                            <label for="kpi_generales_file" class="block text-sm font-medium text-gray-700">Archivo de KPIs Generales (.csv)</label>
                            <input type="file" name="kpi_generales_file" id="kpi_generales_file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
                        </div>
                         <div>
                            <label for="kpis_time_file" class="block text-sm font-medium text-gray-700">Archivo de KPIs de Tiempo (.csv)</label>
                            <input type="file" name="kpis_time_file" id="kpis_time_file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100"/>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-6 py-2 border border-transparent text-base font-medium rounded-full shadow-sm text-white bg-[#2c3856] hover:bg-[#4a5d8c] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#ff9c00]">
                                Cargar Archivos
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        let charts = {};

        const COLORS = ['#2c3856', '#ff9c00', '#4a5d8c', '#ffc107', '#6f42c1', '#fd7e14', '#e83e8c', '#007bff', '#17a2b8', '#dc3545', '#343a40'];

        function renderLineChart(elementId, categories, series) {
            const options = {
                series: series,
                chart: {
                    type: 'line',
                    height: 320,
                    toolbar: { show: true, tools: { download: true, customIcons: [] } },
                    zoom: { enabled: true }
                },
                colors: COLORS,
                xaxis: { categories: categories },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                tooltip: { shared: true, intersect: false },
                legend: { position: 'top' },
                responsive: [{
                    breakpoint: 1000,
                    options: {
                        legend: { position: "bottom" }
                    }
                }]
            };

            // Destruir el gráfico existente si lo hay y crearlo de nuevo
            if (charts[elementId]) {
                charts[elementId].destroy();
            }
            charts[elementId] = new ApexCharts(document.querySelector("#" + elementId), options);
            charts[elementId].render();
        }

    function renderPercentageChart(elementId, categories, series) {
        const processedSeries = JSON.parse(JSON.stringify(series));
        
        const objetivoSeries = processedSeries.find(s => s.name.includes('Objetivo'));
        if (objetivoSeries) {
            objetivoSeries.dataLabels = {
                enabled: true,
                formatter: function(val, { dataPointIndex }) {
                    return dataPointIndex === 0 ? 'Objetivo (90%)' : '';
                },
                style: {
                    colors: ['#dc3545'], // Rojo para el objetivo
                    fontSize: '12px'
                },
                offsetY: -10,
                background: {
                    enabled: false // Sin fondo para el objetivo
                }
            };
            
            objetivoSeries.stroke = {
                width: 2,
                dashArray: 5
            };
        }

        const options = {
            series: processedSeries,
            chart: {
                type: 'line',
                height: 320,
                toolbar: { show: true },
                zoom: { enabled: false }
            },
            colors: ['#2c3856', '#ff9c00', '#dc3545'],
            xaxis: { categories: categories },
            stroke: {
                curve: 'straight',
                width: [3, 2],
                dashArray: [0, 5]
            },
            dataLabels: {
                enabled: true,
                formatter: function(val) {
                    return val !== 90 ? val.toFixed(1) + '%' : '';
                },
                style: {
                    colors: ['#000000'], // Texto negro para todas las etiquetas
                    fontSize: '11px',
                    fontFamily: 'Arial, sans-serif'
                },
                offsetY: -8, 
                background: {
                    enabled: false, // Elimina el contenedor/fondo
                    opacity: 0
                },
                dropShadow: {
                    enabled: false // Elimina sombras
                }
            },
            tooltip: {
                enabled: true,
                y: {
                    formatter: function(val) {
                        return val + '%';
                    }
                }
            },
            legend: {
                position: 'top',
                markers: {
                    dashArray: [0, 5]
                }
            },
            yaxis: {
                labels: {
                    formatter: function(val) {
                        return val + '%';
                    }
                },
                min: 0,
                max: 100
            }
        };

        if (charts[elementId]) {
            charts[elementId].destroy();
        }
        charts[elementId] = new ApexCharts(document.querySelector("#" + elementId), options);
        charts[elementId].render();
    }
        
        function getAverageData(data, labels, selectedItems) {
            const seriesData = {};
            labels.forEach(label => seriesData[label] = { total: 0, count: 0 });

            data.forEach(item => {
                if (selectedItems.includes(item.label)) {
                    item.data.forEach((value, index) => {
                        const label = labels[index];
                        seriesData[label].total += value;
                        seriesData[label].count++;
                    });
                }
            });

            return Object.values(seriesData).map(data => data.count > 0 ? data.total / data.count : 0);
        }

        function renderCharts(chartData, selectedYears, selectedZones, selectedMonths) {
            const embarquesZonaSeries = chartData.embarquesPorZonaAño.datasets
                .filter(d => selectedYears.includes(d.label))
                .map(d => ({ name: d.label, data: d.data }));
            renderLineChart('graficoEmbarquesZona', chartData.embarquesPorZonaAño.labels, embarquesZonaSeries);

            const expeditadosZonaSeries = chartData.expeditadosPorZonaAño.datasets
                .filter(d => selectedYears.includes(d.label))
                .map(d => ({ name: d.label, data: d.data }));
            renderLineChart('graficoExpeditadosZona', chartData.expeditadosPorZonaAño.labels, expeditadosZonaSeries);

            const tiempoZonaSeries = chartData.tiempoPorZonaAño.datasets
                .filter(d => selectedYears.includes(d.label));
            const averageTiempoZona = getAverageData(tiempoZonaSeries, chartData.tiempoPorZonaAño.labels, selectedYears);
            
            renderPercentageChart('graficoTiempoZona', chartData.tiempoPorZonaAño.labels, [
                { name: 'Efectividad de Entregas', data: averageTiempoZona.map(num => Math.ceil(num))  },
                { name: 'Objetivo (90%)', data: chartData.tiempoPorZonaAño.datasets.find(d => d.label === 'Objetivo (90%)').data }
            ]);

            const embarquesMesSeries = chartData.embarquesPorMesZona.datasets
                .filter(d => selectedZones.includes(d.label))
                .map(d => ({ name: d.label, data: d.data }));
            renderLineChart('graficoEmbarquesMes', chartData.embarquesPorMesZona.labels, embarquesMesSeries);

            const expeditadosMesSeries = chartData.expeditadosPorMesZona.datasets
                .filter(d => selectedZones.includes(d.label))
                .map(d => ({ name: d.label, data: d.data }));
            renderLineChart('graficoExpeditadosMes', chartData.expeditadosPorMesZona.labels, expeditadosMesSeries);
                
            const tiempoMesSeries = chartData.tiempoPorMesAño.datasets
                .filter(d => selectedYears.includes(d.label));
            const averageTiempoMes = getAverageData(tiempoMesSeries, chartData.tiempoPorMesAño.labels, selectedYears);

            renderPercentageChart('graficoTiempoMes', chartData.tiempoPorMesAño.labels, [
                { name: 'Efectividad de Entregas', data: averageTiempoMes },
                { name: 'Objetivo (90%)', data: chartData.tiempoPorMesAño.datasets.find(d => d.label === 'Objetivo (90%)').data }
            ]);
        }
    </script>
</x-app-layout>