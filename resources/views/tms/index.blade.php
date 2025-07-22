@extends('layouts.app')

@section('content')
{{-- Incluimos la librería Chart.js y un adaptador para fechas --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

<style>
    .kpi-card { background-color: #fff; border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -2px rgba(0,0,0,.1); transition: all .3s ease; }
    .kpi-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0,0,0,.1), 0 4px 6px -2px rgba(0,0,0,.1); }
    .nav-card { background-color: #2c3856; color: #ffffff; border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -2px rgba(0,0,0,.1); transition: all .3s ease; }
    .nav-card:hover { transform: translateY(-5px); background-color: #ff9c00; }
</style>

<div class="container mx-auto px-4">
    <!-- Navegación: Botones en una fila -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <a href="{{ route('tms.createRoute') }}" class="nav-card p-4 flex items-center"><svg class="w-8 h-8 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg><div><p class="font-bold">Crear Ruta</p><p class="text-sm opacity-80">Planifica un trayecto.</p></div></a>
        <a href="{{ route('tms.assignRoutes') }}" class="nav-card p-4 flex items-center"><svg class="w-8 h-8 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg><div><p class="font-bold">Asignar Rutas</p><p class="text-sm opacity-80">Gestiona embarques.</p></div></a>
        <a href="{{ route('tms.viewRoutes') }}" class="nav-card p-4 flex items-center"><svg class="w-8 h-8 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l5.447 2.724A1 1 0 0021 16.382V5.618a1 1 0 00-1.447-.894L15 7m-6 13v-6.5m6 10V7"></path></svg><div><p class="font-bold">Ver Rutas</p><p class="text-sm opacity-80">Monitoreo en mapa.</p></div></a>
    </div>

    <!-- Cabecera y Filtros -->
    <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-800">Dashboard de Métricas</h1>
        <form action="{{ route('tms.index') }}" method="GET" class="flex items-center gap-2 text-sm">
            <label for="start_date">Desde:</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="form-input rounded-md shadow-sm border-gray-300 text-sm">
            <label for="end_date">Hasta:</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="form-input rounded-md shadow-sm border-gray-300 text-sm">
            <button type="submit" class="bg-[#2c3856] text-white font-semibold py-2 px-4 rounded-lg shadow-sm hover:bg-[#ff9c00] transition-colors">Filtrar</button>
        </form>
    </div>

    <!-- Tarjetas de KPIs -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-8">
        <div class="kpi-card p-4"><p class="text-sm text-gray-500">Rutas en Tránsito</p><p class="text-3xl font-bold">{{ $stats['routes_in_transit'] }}</p></div>
        <div class="kpi-card p-4"><p class="text-sm text-gray-500">Por Asignar</p><p class="text-3xl font-bold">{{ $stats['shipments_to_assign'] }}</p></div>
        <div class="kpi-card p-4"><p class="text-sm text-gray-500">Rutas Completadas</p><p class="text-3xl font-bold">{{ $stats['routes_completed'] }}</p></div>
        <div class="kpi-card p-4"><p class="text-sm text-gray-500">Embarques Totales</p><p class="text-3xl font-bold">{{ $stats['total_shipments'] }}</p></div>
        <div class="kpi-card p-4"><p class="text-sm text-gray-500">Incidentes</p><p class="text-3xl font-bold text-red-500">{{ $stats['incidents'] }}</p></div>
    </div>

    <!-- Contenido Principal -->
    <div class="grid grid-cols-1 gap-8">
        <!-- Gráficos -->
        <div class="space-y-8">
            <!-- Primera fila: 3 gráficos en pantallas grandes, 2 en tablets, 1 en móviles -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-md"><h3 class="font-bold text-gray-800 mb-4">Rutas por Estatus</h3><canvas id="routesByStatusChart"></canvas></div>
                <div class="bg-white p-6 rounded-lg shadow-md"><h3 class="font-bold text-gray-800 mb-4">Rendimiento por Operador (Top 7)</h3><canvas id="operatorPerformanceChart"></canvas></div>
                <div class="bg-white p-6 rounded-lg shadow-md"><h3 class="font-bold text-gray-800 mb-4">Tipos de Incidencias</h3><canvas id="incidentTypesChart"></canvas></div>
            </div>
            <!-- Segunda fila: 3 gráficos en pantallas grandes, 2 en tablets, 1 en móviles -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-md"><h3 class="font-bold text-gray-800 mb-4">Entregas por Origen</h3><canvas id="deliveriesByOriginChart"></canvas></div>
                <div class="bg-white p-6 rounded-lg shadow-md"><h3 class="font-bold text-gray-800 mb-4">Entregas por Destino</h3><canvas id="deliveriesByDestinationChart"></canvas></div>
                <div class="bg-white p-6 rounded-lg shadow-md"><h3 class="font-bold text-gray-800 mb-4">Volumen por Tipo</h3><canvas id="shipmentTypesChart"></canvas></div>
            </div>
            <!-- Tercera fila: Actividad de Rutas (1 gráfico, ocupa todo el ancho) -->
            <div class="bg-white p-6 rounded-lg shadow-md"><h3 class="font-bold text-gray-800 mb-4">Actividad de Rutas (Últimos 30 días)</h3><canvas id="activityChart"></canvas></div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const corporateColors = { blue: '#2c3856', orange: '#ff9c00', gray: '#6c757d', green: '#198754', red: '#dc3545', yellow: '#f59e0b', purple: '#6f42c1' };

        // Gráfico 1: Rutas por Estatus (Pastel)
        new Chart(document.getElementById('routesByStatusChart'), {
            type: 'pie', data: { labels: @json(array_keys($routesByStatusChart->toArray())), datasets: [{ data: @json(array_values($routesByStatusChart->toArray())), backgroundColor: Object.values(corporateColors) }] },
            options: { responsive: true, plugins: { legend: { position: 'top' } } }
        });

        // Gráfico 2: Rendimiento por Operador (Barras Horizontales)
        new Chart(document.getElementById('operatorPerformanceChart'), {
            type: 'bar', data: { labels: @json(array_keys($operatorPerformanceChart->toArray())), datasets: [{ label: 'Embarques Entregados', data: @json(array_values($operatorPerformanceChart->toArray())), backgroundColor: corporateColors.blue }] },
            options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } } }
        });
        
        // Gráfico 3: Entregas por Origen (Barras Verticales)
        new Chart(document.getElementById('deliveriesByOriginChart'), {
            type: 'bar', data: { labels: @json(array_keys($deliveriesByOriginChart->toArray())), datasets: [{ label: 'Total Entregas', data: @json(array_values($deliveriesByOriginChart->toArray())), backgroundColor: corporateColors.orange }] },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });

        // Gráfico 4: Actividad de Rutas (Líneas)
        new Chart(document.getElementById('activityChart'), {
            type: 'line',
            data: { datasets: [
                { label: 'Rutas Creadas', data: @json($createdRoutesActivity->map(fn($item) => ['x' => $item->date, 'y' => $item->count])), borderColor: corporateColors.blue, tension: 0.1 },
                { label: 'Rutas Completadas', data: @json($completedRoutesActivity->map(fn($item) => ['x' => $item->date, 'y' => $item->count])), borderColor: corporateColors.green, tension: 0.1 }
            ]},
            options: { responsive: true, scales: { x: { type: 'time', time: { unit: 'day' } } } }
        });

        // Gráfico 5: Tipos de Incidencias (Radar)
        new Chart(document.getElementById('incidentTypesChart'), {
            type: 'radar',
            data: { labels: @json(array_keys($incidentTypesChart->toArray())), datasets: [{ label: 'Total de Eventos', data: @json(array_values($incidentTypesChart->toArray())), backgroundColor: 'rgba(255, 156, 0, 0.2)', borderColor: corporateColors.orange, pointBackgroundColor: corporateColors.orange }] },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        // Gráfico 6: Entregas por Destino (Polar Area)
        new Chart(document.getElementById('deliveriesByDestinationChart'), {
            type: 'polarArea',
            data: { labels: @json(array_keys($deliveriesByDestinationChart->toArray())), datasets: [{ label: 'Total Entregas', data: @json(array_values($deliveriesByDestinationChart->toArray())), backgroundColor: [corporateColors.purple, corporateColors.yellow, corporateColors.gray, corporateColors.red, corporateColors.blue] }] },
            options: { responsive: true }
        });

        // Gráfico 7: Volumen por Tipo (Doughnut)
        const shipmentTypesData = @json($shipmentTypesChart);
        new Chart(document.getElementById('shipmentTypesChart'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(shipmentTypesData),
                datasets: [{
                    label: 'Total Embarques',
                    data: Object.values(shipmentTypesData),
                    backgroundColor: [corporateColors.blue, corporateColors.purple]
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    });
</script>
@endsection