<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard de Rutas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">


            
            <div class="mb-8 grid grid-cols-1 sm:grid-cols-3 gap-6">
                <a href="{{ route('rutas.plantillas.index') }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow-md hover:shadow-xl hover:bg-gray-50 transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-[#ff9c00] text-white mr-4">
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m0 0v2.25m0-2.25h1.5m-1.5 0H5.25m11.25-8.25v2.25m0-2.25h-1.5m1.5 0H12m0 0v2.25m0-2.25V6.75m0 0H9m12 6.75h-1.5m1.5 0v-2.25m0 2.25H12m0 0H9m12 6.75h-1.5m1.5 0v-2.25m0 2.25H12m0 0H9m-3.75 0H5.25m0 0V9.75M5.25 12h1.5m0 0V9.75m0 0H5.25m3.75 0H9m-3.75 0H5.25m0 0h1.5m3 0h1.5m-1.5 0H9m-3.75 0H9" /></svg>
                        </div>
                        <div>
                            <h5 class="mb-1 text-xl font-bold tracking-tight text-[#2c3856]">Gestión de Rutas</h5>
                            <p class="font-normal text-gray-600 text-sm">Crear, editar y eliminar plantillas de rutas.</p>
                        </div>
                    </div>
                </a>
                
                <a href="{{ route('rutas.asignaciones.index') }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow-md hover:shadow-xl hover:bg-gray-50 transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-[#2c3856] text-white mr-4">
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                        </div>
                        <div>
                            <h5 class="mb-1 text-xl font-bold tracking-tight text-[#2c3856]">Asignaciones</h5>
                            <p class="font-normal text-gray-600 text-sm">Asignar guías y facturas a las rutas planeadas.</p>
                        </div>
                    </div>
                </a>
                
                <a href="{{ route('rutas.monitoreo.index') }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow-md hover:shadow-xl hover:bg-gray-50 transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-[#666666] text-white mr-4">
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </div>
                        <div>
                            <h5 class="mb-1 text-xl font-bold tracking-tight text-[#2c3856]">Monitoreo</h5>
                            <p class="font-normal text-gray-600 text-sm">Visualizar rutas activas y eventos en tiempo real.</p>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="bg-white p-4 rounded-lg shadow-md mb-6">
                <form action="{{ route('rutas.dashboard') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Fecha de inicio</label>
                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date', $startDate) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">Fecha de fin</label>
                        <input type="date" name="end_date" id="end_date" value="{{ request('end_date', $endDate) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div class="self-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-[#2c3856] hover:bg-[#1a2b41] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Filtrar
                        </button>
                    </div>
                    <div class="self-end border-l pl-4 ml-4">
                        <a href="{{ route('rutas.dashboard.export', ['start_date' => request('start_date', $startDate), 'end_date' => request('end_date', $endDate)]) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Exportar a CSV
                        </a>
                        <a href="{{ route('rutas.dashboard.exportTiempos', ['start_date' => request('start_date', $startDate), 'end_date' => request('end_date', $endDate)]) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Reporte de Tiempos
                        </a>
                    </div>
                </form>
            </div>       
                 
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-md col-span-1 min-h-[350px]"><canvas id="chart1"></canvas></div>
                <div class="bg-white p-6 rounded-lg shadow-md col-span-1"><canvas id="chart2"></canvas></div>
                <div class="bg-white p-6 rounded-lg shadow-md col-span-1"><canvas id="chart3"></canvas></div>

                <div class="bg-white p-6 rounded-lg shadow-md col-span-1 min-h-[350px]"><canvas id="chart4"></canvas></div>
                <div class="bg-white p-6 rounded-lg shadow-md col-span-1"><canvas id="chart5"></canvas></div>
                <div class="bg-white p-6 rounded-lg shadow-md col-span-1"><canvas id="chart6"></canvas></div>

                <div class="bg-white p-6 rounded-lg shadow-md lg:col-span-3min-h-[600px]"><canvas id="chart7"></canvas></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const colorNaranja = 'rgba(255, 156, 0, 0.6)';
        const colorAzul = 'rgba(44, 60, 86, 0.6)';
        const colorGris = 'rgba(102, 102, 102, 0.6)';
        const colorGrisOscuro = 'rgba(43, 43, 43, 0.6)';
        const colorBlanco = 'rgba(255, 255, 255, 0.6)';
        
        const borderNaranja = 'rgba(255, 156, 0, 1)';
        const borderAzul = 'rgba(44, 60, 86, 1)';
        const borderGris = 'rgba(102, 102, 102, 1)';
        const borderGrisOscuro = 'rgba(43, 43, 43, 1)';

        const chart1Data = @json($chart1Data);
        const ctx1 = document.getElementById('chart1').getContext('2d');
        new Chart(ctx1, {
            type: 'bar', data: { labels: chart1Data.labels, datasets: [{ label: 'Rutas por Tipo', data: chart1Data.data, backgroundColor: [colorNaranja, colorAzul, colorGris], borderColor: [borderNaranja, borderAzul, borderGris], borderWidth: 1 }] },
            options: { responsive: true, plugins: { title: { display: true, text: 'Rutas por Tipo' } }, scales: { y: { beginAtZero: true } } }
        });

        const chart2Data = @json($chart2Data);
        const ctx2 = document.getElementById('chart2').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut', data: { labels: chart2Data.labels, datasets: [{ label: 'Guías por Estatus', data: chart2Data.data, backgroundColor: [colorGris, colorAzul, borderNaranja, 'rgba(75, 192, 192, 0.6)'], hoverOffset: 4 }] },
            options: { responsive: true, plugins: { title: { display: true, text: 'Guías por Estatus' } } }
        });

        const chart3Data = @json($chart3Data);
        const ctx3 = document.getElementById('chart3').getContext('2d');
        new Chart(ctx3, {
            type: 'bar', data: { labels: chart3Data.labels, datasets: [{ label: 'No. de Rutas', data: chart3Data.data, backgroundColor: colorAzul, borderColor: borderAzul, borderWidth: 1 }] },
            options: { responsive: true, plugins: { title: { display: true, text: 'Plantillas por Región' } }, scales: { y: { beginAtZero: true } } }
        });

        const chart4Data = @json($chart4Data);
        const ctx4 = document.getElementById('chart4').getContext('2d');
        new Chart(ctx4, {
            type: 'pie', data: { labels: chart4Data.labels, datasets: [{ label: 'Eventos por Tipo', data: chart4Data.data, backgroundColor: [colorNaranja, colorGrisOscuro, colorGris], hoverOffset: 4 }] },
            options: { responsive: true, plugins: { title: { display: true, text: 'Eventos Registrados por Tipo' } } }
        });

        const chart5Data = @json($chart5Data);
        const ctx5 = document.getElementById('chart5').getContext('2d');
        new Chart(ctx5, {
            type: 'bar', data: { labels: chart5Data.labels, datasets: [{ label: 'Guías Asignadas', data: chart5Data.data, backgroundColor: colorNaranja, borderColor: borderNaranja, borderWidth: 1 }] },
            options: { indexAxis: 'y', responsive: true, plugins: { title: { display: true, text: 'Top 5 Operadores' } }, scales: { x: { beginAtZero: true } } }
        });

        const chart6Data = @json($chart6Data);
        const ctx6 = document.getElementById('chart6').getContext('2d');
        new Chart(ctx6, {
            type: 'doughnut', data: { labels: chart6Data.labels, datasets: [{ label: 'Estatus de Facturas', data: chart6Data.data, backgroundColor: ['rgba(75, 192, 192, 0.6)', 'rgba(255, 99, 132, 0.6)'], hoverOffset: 4 }] },
            options: { responsive: true, plugins: { title: { display: true, text: 'Estatus de Entregas' } } }
        });

        const chart7Data = @json($chart7Data);
        const ctx7 = document.getElementById('chart7').getContext('2d');
        new Chart(ctx7, {
            type: 'line', data: { labels: chart7Data.labels, datasets: [{
                label: 'Guías Completadas por Día', data: chart7Data.data,
                fill: true, backgroundColor: colorAzul, borderColor: borderAzul, tension: 0.1
            }] },
            options: { responsive: true, plugins: { title: { display: true, text: 'Actividad de los Últimos 7 Días' } }, scales: { y: { beginAtZero: true } } }
        });
    });
    </script>
</x-app-layout>