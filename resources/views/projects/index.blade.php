<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style> /* Estilos para una apariencia más pulida */
        .card { background-color: #ffffff; border: 1px solid #e5e7eb; transition: all 0.3s; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1); }
    </style>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-3xl text-[#2c3856] leading-tight tracking-tight">Dashboard de Proyectos</h2>
                <p class="text-md text-gray-500 mt-1">Resumen estratégico y estado actual.</p>
            </div>
            <a href="{{ route('projects.list') }}" class="px-5 py-2 bg-[#2c3856] text-white font-semibold rounded-lg shadow-md hover:bg-gray-800 transition-colors">
                <i class="fas fa-grip-horizontal mr-2"></i> Ir al Tablero Kanban
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="card p-6 rounded-xl flex items-center"><div class="bg-blue-100 text-blue-600 rounded-lg p-4 mr-4"><i class="fas fa-tasks fa-2x"></i></div><div><p class="text-gray-500 text-sm">Proyectos Activos</p><p class="text-3xl font-bold text-[#2c3856]">{{ $activeProjectsCount }}</p></div></div>
                <div class="card p-6 rounded-xl flex items-center"><div class="bg-red-100 text-red-600 rounded-lg p-4 mr-4"><i class="fas fa-exclamation-triangle fa-2x"></i></div><div><p class="text-gray-500 text-sm">Proyectos Atrasados</p><p class="text-3xl font-bold text-[#2c3856]">{{ $overdueProjectsCount }}</p></div></div>
                <div class="card p-6 rounded-xl flex items-center"><div class="bg-yellow-100 text-yellow-600 rounded-lg p-4 mr-4"><i class="far fa-calendar-alt fa-2x"></i></div><div><p class="text-gray-500 text-sm">Vencen en 7 días</p><p class="text-3xl font-bold text-[#2c3856]">{{ $upcomingDeadlinesCount }}</p></div></div>
                <div class="card p-6 rounded-xl flex items-center"><div class="bg-green-100 text-green-600 rounded-lg p-4 mr-4"><i class="fas fa-check-circle fa-2x"></i></div><div><p class="text-gray-500 text-sm">Completados (Mes)</p><p class="text-3xl font-bold text-[#2c3856]">{{ $completedThisMonthCount }}</p></div></div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-8">
                    <div class="card p-6 rounded-xl">
                        <h3 class="font-bold text-[#2c3856] text-xl mb-4">Carga de Trabajo del Equipo (Tareas Activas)</h3>
                        <div id="workloadChart"></div>
                    </div>
                    <div class="card p-6 rounded-xl">
                        <h3 class="font-bold text-[#2c3856] text-xl mb-4">Próximos Vencimientos (14 días)</h3>
                        <div class="space-y-4">
                            @forelse ($upcomingProjects as $project)
                                <a href="{{ route('projects.show', $project) }}" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                                    <span class="font-semibold text-gray-700">{{ $project->name }}</span>
                                    <span class="text-sm font-bold {{ $project->due_date->isBefore(now()->addDays(3)) ? 'text-red-500' : 'text-yellow-600' }}">
                                        Vence en {{ $project->due_date->diffForHumans(null, true) }}
                                    </span>
                                </a>
                            @empty
                                <p class="text-sm text-gray-500 text-center py-4">No hay proyectos con vencimientos en los próximos 14 días.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-1 space-y-8">
                    <div class="card p-6 rounded-xl">
                        <h3 class="font-bold text-[#2c3856] text-xl mb-4">Salud General de Proyectos</h3>
                        <div id="overallProgressChart"></div>
                    </div>
                    <div class="card p-6 rounded-xl">
                        <h3 class="font-bold text-[#2c3856] text-xl mb-4">Distribución por Estatus</h3>
                        <div id="statusChart" class="flex justify-center"></div>
                    </div>
                     <div class="card p-6 rounded-xl">
                        <h3 class="font-bold text-[#2c3856] text-xl mb-4">Actividad Reciente Global</h3>
                        <div class="space-y-4">
                            @forelse ($recentActivity as $comment)
                                <div class="text-sm">
                                    <p class="text-gray-800">
                                        <span class="font-semibold">{{ $comment->user->name }}</span> comentó en 
                                        <a href="{{ route('projects.show', $comment->project) }}" class="font-semibold text-indigo-600 hover:underline">{{ $comment->project->name }}</a>
                                    </p>
                                    <p class="text-gray-500 pl-2 border-l-2 ml-1 mt-1 italic">"{{ Str::limit($comment->body, 50) }}"</p>
                                    <p class="text-xs text-gray-400 mt-1 pl-3">{{ $comment->created_at->diffForHumans() }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No hay actividad reciente.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartData = @json($chartData);

            // Gráfico: Salud General (Radial)
            if (document.querySelector("#overallProgressChart")) {
                const progressOptions = {
                    series: [chartData.overallProgress],
                    chart: { type: 'radialBar', height: 250 },
                    plotOptions: { radialBar: {
                        startAngle: -135, endAngle: 135,
                        dataLabels: { name: { show: false }, value: { fontSize: '24px', fontWeight: 'bold', color: '#2c3856', formatter: (val) => val + '%' } }
                    } },
                    fill: { type: 'gradient', gradient: { shade: 'dark', type: 'horizontal', shadeIntensity: 0.5, gradientToColors: ['#0d6efd'], inverseColors: true, opacityFrom: 1, opacityTo: 1, stops: [0, 100] } },
                    stroke: { lineCap: 'round' },
                    labels: ['Progreso'],
                };
                new ApexCharts(document.querySelector("#overallProgressChart"), progressOptions).render();
            }

            // Gráfico: Carga de Trabajo (Barras Horizontales)
            if (document.querySelector("#workloadChart")) {
                const workloadOptions = {
                    series: [{ name: 'Tareas Asignadas', data: chartData.workload.series }],
                    chart: { type: 'bar', height: 350, toolbar: { show: false } },
                    plotOptions: { bar: { borderRadius: 4, horizontal: true } },
                    dataLabels: { enabled: true, style: { colors: ['#fff'], fontWeight: 'bold' }, offsetX: -20 },
                    xaxis: { categories: chartData.workload.labels, labels: { show: false } },
                    colors: ['#2c3856'],
                };
                new ApexCharts(document.querySelector("#workloadChart"), workloadOptions).render();
            }

            // Gráfico: Estatus (Donut)
            if (document.querySelector("#statusChart")) {
                const statusOptions = {
                    series: chartData.status.series,
                    chart: { type: 'donut', height: 250 },
                    labels: chartData.status.labels,
                    colors: ['#6c757d', '#0d6efd', '#ffc107', '#198754', '#dc3545'],
                    legend: { position: 'bottom' },
                };
                new ApexCharts(document.querySelector("#statusChart"), statusOptions).render();
            }
        });
    </script>

</x-app-layout>