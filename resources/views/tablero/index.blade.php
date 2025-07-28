<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tablero General') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-12">

            {{-- 1. SECCIÓN DE ÁREAS ACCESIBLES --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:px-10 bg-white border-b border-gray-200">
                    <h3 class="text-2xl font-bold text-[#2c3856]" style="font-family: 'Raleway', sans-serif;">Áreas Disponibles</h3>
                    <p class="mt-2 text-gray-500">Acceso rápido a las áreas de tus proyectos principales.</p>
                </div>

                <div class="p-6 sm:p-10">
                    {{-- Usa la variable correcta: $accessibleRootFolders --}}
                    @if($accessibleRootFolders->isNotEmpty())
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                            @foreach($accessibleRootFolders as $folder)
                                {{-- El enlace ahora apunta a la ruta de las carpetas, pasando el ID de la carpeta --}}
                                <a href="{{ route('folders.index', ['folder' => $folder->id]) }}" class="group block p-6 bg-gray-50 rounded-xl border border-gray-200 hover:bg-[#ff9c00] hover:border-[#ff9c00] transition-all duration-300 ease-in-out transform hover:-translate-y-1 hover:shadow-2xl">
                                    <div class="flex justify-center mb-4">
                                        {{-- El ícono se toma del área asociada a la carpeta --}}
                                        @if($folder->area?->icon_path)
                                            <img src="{{ Storage::disk('s3')->url($folder->area->icon_path) }}" alt="Icono de {{ $folder->area->name }}" class="h-16 w-16 object-contain transition-transform duration-300 group-hover:scale-110">
                                        @else
                                            {{-- Icono por defecto si el área no tiene uno --}}
                                            <svg class="h-16 w-16 text-gray-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                        @endif
                                    </div>
                                    <h4 class="text-center font-bold text-lg text-[#2c3856] group-hover:text-white">{{ $folder->area?->name ?? $folder->name }}</h4>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-gray-500 py-8">No tienes acceso a ninguna carpeta de área por el momento.</p>
                    @endif
                </div>
            </div>


            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:px-10 bg-white border-b border-gray-200">
                    <h3 class="text-2xl font-bold text-[#2c3856]">Indicadores Clave de Rendimiento</h3>
                </div>
                <div class="p-6 sm:p-10">
                    @if(!empty($chartData))
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div class="space-y-8">
                                <div class="p-4 border rounded-lg shadow-md"><h4 class="text-center font-semibold text-gray-600 mb-2">Cantidad por Mes</h4><canvas id="graficoLineaMes"></canvas></div>
                                <div class="p-4 border rounded-lg shadow-md"><h4 class="text-center font-semibold text-gray-600 mb-2">Cantidad por Zona</h4><canvas id="graficoBarraZona"></canvas></div>
                                <div class="p-4 border rounded-lg shadow-md"><h4 class="text-center font-semibold text-gray-600 mb-2">Distribución por Área</h4><canvas id="graficoPastelArea"></canvas></div>
                            </div>
                            <div class="space-y-8">
                                <div class="p-4 border rounded-lg shadow-md"><h4 class="text-center font-semibold text-gray-600 mb-2">Porcentaje por Concepto</h4><canvas id="graficoDonaConcepto"></canvas></div>
                                <div class="p-4 border rounded-lg shadow-md"><h4 class="text-center font-semibold text-gray-600 mb-2">Porcentaje Promedio por Mes</h4><canvas id="graficoBarraHMes"></canvas></div>
                                <div class="p-4 border rounded-lg shadow-md"><h4 class="text-center font-semibold text-gray-600 mb-2">Cantidad por Concepto</h4><canvas id="graficoPolarConcepto"></canvas></div>
                            </div>
                        </div>
                    @else
                        <p class="text-center text-gray-500 py-8">No hay datos de KPIs disponibles.</p>
                    @endif
                </div>
            </div>

            {{-- 3. SECCIÓN PARA CARGAR PLANTILLAS --}}
            @if(Auth::user()->is_area_admin && Auth::user()->area?->name === 'Administración')
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
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
            const colores = ['#2c3856', '#ff9c00', '#4a5d8c', '#ffc107', '#6c757d', '#17a2b8', '#28a745'];

            new Chart(document.getElementById('graficoLineaMes'),{type:'line',data:{labels:chartData.linea_mes_labels,datasets:[{label:'Cantidad',data:chartData.linea_mes_data,borderColor:'#2c3856',tension:0.1}]}});
            new Chart(document.getElementById('graficoBarraZona'),{type:'bar',data:{labels:chartData.barras_zona_labels,datasets:[{label:'Cantidad',data:chartData.barras_zona_data,backgroundColor:colores}]}});
            new Chart(document.getElementById('graficoPastelArea'),{type:'pie',data:{labels:chartData.pastel_area_labels,datasets:[{label:'Cantidad',data:chartData.pastel_area_data,backgroundColor:colores}]}});
            new Chart(document.getElementById('graficoDonaConcepto'),{type:'doughnut',data:{labels:chartData.dona_concepto_labels,datasets:[{label:'Porcentaje Promedio',data:chartData.dona_concepto_data,backgroundColor:colores}]}});
            new Chart(document.getElementById('graficoBarraHMes'),{type:'bar',data:{labels:chartData.barras_h_mes_labels,datasets:[{label:'Porcentaje Promedio',data:chartData.barras_h_mes_data,backgroundColor:'#ff9c00'}]},options:{indexAxis:'y'}});
            new Chart(document.getElementById('graficoPolarConcepto'),{type:'polarArea',data:{labels:chartData.polar_concepto_labels,datasets:[{label:'Cantidad',data:chartData.polar_concepto_data,backgroundColor:colores}]}});
        });
    </script>

</x-app-layout>