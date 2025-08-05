<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard ') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-10xl mx-auto sm:px-6 lg:px-8 space-y-12">

            {{-- 1. SECCIÓN DE ÁREAS ACCESIBLES --}}
            <div class="bg-[#FFF1DC] overflow-hidden shadow-xl rounded-[40px] p-6 sm:p-10">
                <h3 class="text-2xl font-bold text-[#2c3856]" style="font-family: 'Raleway', sans-serif;">Áreas Disponibles</h3>
                <p class="mt-2 text-gray-500">Acceso rápido a las áreas de tus proyectos principales.</p>

                <div class="mt-8">
                    @if($accessibleRootFolders->isNotEmpty())
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
                            @foreach($accessibleRootFolders as $folder)
                                <a href="{{ route('folders.index', ['folder' => $folder->id]) }}" class="group bg-white rounded-xl shadow-md p-6 flex flex-col items-center text-center transition-all duration-300 ease-in-out hover:shadow-2xl hover:-translate-y-2">
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
                                    <!-- <p class="mt-2 text-sm text-gray-600">
                                        Accede a los archivos y documentos de esta área.
                                    </p> -->
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
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            {{-- Columna Izquierda --}}
                            <div class="space-y-8">
                                <div class="p-4 border rounded-lg shadow-md">
                                    <h4 class="text-center font-semibold text-gray-600 mb-2">Cantidad de Embarques por Zona (por Año)</h4>
                                    <div class="relative h-80"><canvas id="graficoEmbarquesZona"></canvas></div>
                                </div>
                                <div class="p-4 border rounded-lg shadow-md">
                                    <h4 class="text-center font-semibold text-gray-600 mb-2">Cantidad de Documentos por Zona</h4>
                                    <div class="relative h-80"><canvas id="graficoDocumentosZona"></canvas></div>
                                </div>
                                <div class="p-4 border rounded-lg shadow-md">
                                    <h4 class="text-center font-semibold text-gray-600 mb-2">Cantidad de Expeditados por Zona (por Año)</h4>
                                    <div class="relative h-80"><canvas id="graficoExpeditadosZona"></canvas></div>
                                </div>
                                <div class="p-4 border rounded-lg shadow-md">
                                    <h4 class="text-center font-semibold text-gray-600 mb-2">Entregas a Tiempo por Zona (por Año)</h4>
                                    <div class="relative h-80"><canvas id="graficoTiempoZona"></canvas></div>
                                </div>
                            </div>
                            {{-- Columna Derecha --}}
                            <div class="space-y-8">
                                <div class="p-4 border rounded-lg shadow-md">
                                    <h4 class="text-center font-semibold text-gray-600 mb-2">Cantidad de Embarques por Mes (por Zona)</h4>
                                    <div class="relative h-80"><canvas id="graficoEmbarquesMes"></canvas></div>
                                </div>
                                <div class="p-4 border rounded-lg shadow-md">
                                    <h4 class="text-center font-semibold text-gray-600 mb-2">Cantidad de Expeditados por Mes (por Zona)</h4>
                                    <div class="relative h-80"><canvas id="graficoExpeditadosMes"></canvas></div>
                                </div>
                                <div class="p-4 border rounded-lg shadow-md">
                                    <h4 class="text-center font-semibold text-gray-600 mb-2">Entregas a Tiempo por Mes (por Año)</h4>
                                    <div class="relative h-80"><canvas id="graficoTiempoMes"></canvas></div>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-center text-gray-500 py-8">No hay datos de KPIs disponibles para generar los gráficos.</p>
                    @endif
                </div>
            </div>

            {{-- 3. SECCIÓN PARA CARGAR PLANTILLAS --}}
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartData = @json($chartData ?? []);
            if (Object.keys(chartData).length === 0) return;

            const chartOptions = { responsive: true, maintainAspectRatio: false };

            new Chart(document.getElementById('graficoEmbarquesZona'),{type:'bar', data: chartData.embarquesPorZonaAño, options: chartOptions });
            new Chart(document.getElementById('graficoExpeditadosZona'),{type:'bar', data: chartData.expeditadosPorZonaAño, options: chartOptions });
            new Chart(document.getElementById('graficoDocumentosZona'),{type:'doughnut', data: chartData.documentosPorZonaAño, options: chartOptions });
            new Chart(document.getElementById('graficoTiempoZona'),{type:'bar', data: chartData.tiempoPorZonaAño, options: chartOptions });
            
            new Chart(document.getElementById('graficoEmbarquesMes'),{type:'line', data: chartData.embarquesPorMesZona, options: chartOptions });
            new Chart(document.getElementById('graficoExpeditadosMes'),{type:'line', data: chartData.expeditadosPorMesZona, options: chartOptions });
            new Chart(document.getElementById('graficoTiempoMes'),{type:'line', data: chartData.tiempoPorMesAño, options: chartOptions });
        });
    </script>
</x-app-layout>