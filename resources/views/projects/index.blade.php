<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        .card { background-color: #ffffff; border: 1px solid #eef0f5; transition: all 0.3s ease-in-out; display: flex; flex-direction: column; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px -5px rgb(0 0 0 / 0.07); }
        .timeline-item::before { content: ''; position: absolute; left: 20px; top: 48px; bottom: -8px; width: 2px; background-color: #eef0f5; }
        .timeline-item:last-child::before { display: none; }
    </style>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-3xl text-[#2c3856] leading-tight tracking-tight">Dashboard de Proyectos</h2>
                <p class="text-md text-gray-500 mt-1">Resumen estratégico y estado actual. Hoy es {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}.</p>
            </div>
            <div class="flex justify-between items-center">
                <a href="{{ route('projects.list') }}" class="px-5 py-2 bg-[#2c3856] text-white font-semibold rounded-lg shadow-md hover:bg-gray-800 transition-colors">
                    <i class="fas fa-grip-horizontal mr-2"></i> Ir al Tablero Kanban
                </a>
                <a href="{{ route('projects.review') }}" class="px-5 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-users mr-2"></i> Vista de Reunión
                </a>
            </div>               
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="card p-6 rounded-xl flex-row items-center"><div class="bg-blue-100 text-blue-600 rounded-lg p-4 mr-4"><i class="fas fa-tasks fa-2x"></i></div><div><p class="text-gray-500 text-sm">Proyectos Activos</p><p class="text-3xl font-bold text-[#2c3856]">{{ $activeProjectsCount }}</p></div></div>
                <div class="card p-6 rounded-xl flex-row items-center"><div class="bg-red-100 text-red-600 rounded-lg p-4 mr-4"><i class="fas fa-exclamation-triangle fa-2x"></i></div><div><p class="text-gray-500 text-sm">Proyectos Atrasados</p><p class="text-3xl font-bold text-[#2c3856]">{{ $overdueProjectsCount }}</p></div></div>
                <div class="card p-6 rounded-xl flex-row items-center"><div class="bg-green-100 text-green-600 rounded-lg p-4 mr-4"><i class="fas fa-dollar-sign fa-2x"></i></div><div><p class="text-gray-500 text-sm">Presupuesto Total</p><p class="text-3xl font-bold text-[#2c3856]">${{ number_format($totalBudget, 0) }}</p></div></div>
                <div class="card p-6 rounded-xl flex-row items-center"><div class="bg-orange-100 text-orange-600 rounded-lg p-4 mr-4"><i class="fas fa-wallet fa-2x"></i></div><div><p class="text-gray-500 text-sm">Gasto Total</p><p class="text-3xl font-bold text-[#2c3856]">${{ number_format($totalSpent, 0) }}</p></div></div>
            </div>

            <div class="card rounded-xl p-6 mb-8">
                <h3 class="font-bold text-[#2c3856] text-xl mb-4">Proyectos Críticos</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold text-red-600 mb-2">Atrasados</h4>
                        <div class="space-y-3">
                            @forelse ($overdueProjects as $project)
                                <a href="{{ route('projects.show', $project) }}" class="block p-3 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                                    <div class="flex justify-between items-center"><span class="font-semibold text-gray-800">{{ $project->name }}</span><span class="text-xs font-bold text-red-700">Atrasado {{ $project->due_date->diffForHumans(null, true) }}</span></div>
                                </a>
                            @empty
                                <div class="flex items-center text-sm text-gray-500 pt-4"><i class="fas fa-glass-cheers mr-2 text-green-500"></i> ¡Felicidades! No hay proyectos atrasados.</div>
                            @endforelse
                        </div>
                    </div>
                    <div>
                        <h4 class="font-semibold text-yellow-600 mb-2">Próximos a Vencer</h4>
                        <div class="space-y-3">
                             @forelse ($upcomingProjects as $project)
                                <a href="{{ route('projects.show', $project) }}" class="block p-3 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors">
                                    <div class="flex justify-between items-center"><span class="font-semibold text-gray-800">{{ $project->name }}</span><span class="text-xs font-bold text-yellow-700">Vence en {{ $project->due_date->diffForHumans(null, true) }}</span></div>
                                </a>
                            @empty
                                <p class="text-sm text-gray-500 pt-4">No hay proyectos con vencimientos cercanos.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>


            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 card p-6 rounded-xl">
                    <h3 class="font-bold text-[#2c3856] text-xl mb-4">Carga de Trabajo del Equipo (Tareas Activas)</h3>
                    <div id="workloadChart"></div>
                </div>
                <div class="card p-6 rounded-xl">
                    <h3 class="font-bold text-[#2c3856] text-xl mb-4">Distribución por Estatus</h3>
                    <div id="statusChart" class="flex justify-center"></div>
                </div>
                <div class="lg:col-span-3 card p-6 rounded-xl">
                    <h3 class="font-bold text-[#2c3856] text-xl mb-4">Actividad Reciente Global</h3>
                    <div class="space-y-6">
                        @forelse ($recentActivity as $comment)
                            <div class="relative flex timeline-item">
                                <div class="h-10 w-10 flex-shrink-0 flex items-center justify-center rounded-full bg-indigo-100 text-indigo-600 font-bold z-10 ring-8 ring-white">
                                    @php $words = explode(" ", $comment->user->name); $initials = ""; foreach (array_slice($words, 0, 2) as $w) { $initials .= mb_substr($w, 0, 1); } @endphp
                                    {{ $initials }}
                                </div>
                                <div class="ml-4">
                                    <p class="text-gray-800 text-sm"><span class="font-semibold">{{ $comment->user->name }}</span> comentó en <a href="{{ route('projects.show', $comment->project) }}" class="font-semibold text-indigo-600 hover:underline">{{ $comment->project->name }}</a></p>
                                    <p class="text-gray-600 text-sm mt-1 pl-4 border-l-2 border-gray-200 italic">"{{ Str::limit($comment->body, 90) }}"</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $comment->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No hay actividad reciente.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <br>
            <br>
            <div class="card rounded-xl p-6 mb-8">
                <h3 class="font-bold text-[#2c3856] text-xl mb-4">Análisis Financiero por Proyecto</h3>
                <div id="financialChart"></div>
            </div>            
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartData = @json($chartData);

            if (document.querySelector("#financialChart") && chartData.financials.length > 0) {
                const financialOptions = {
                    series: [{
                        name: 'Presupuesto',
                        data: chartData.financials.map(p => p.budget)
                    }, {
                        name: 'Gastado',
                        data: chartData.financials.map(p => p.spent)
                    }],
                    chart: { type: 'bar', height: 350, stacked: false, toolbar: { show: false } },
                    plotOptions: { bar: { horizontal: true, dataLabels: { position: 'top' }, borderRadius: 4 } },
                    dataLabels: {
                        enabled: true,
                        offsetX: -10,
                        style: { fontSize: '11px', colors: ['#fff'] },
                        formatter: val => '$' + (val / 1000).toFixed(1) + 'k'
                    },
                    stroke: { show: true, width: 1, colors: ['#fff'] },
                    xaxis: { categories: chartData.financials.map(p => p.name) },
                    colors: ['#6EE7B7', '#10B981'], // Tonos de verde (claro y oscuro)
                    legend: { position: 'top' },
                    tooltip: {
                        y: { formatter: val => '$' + val.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) }
                    },
                    grid: { borderColor: '#f1f1f1' }
                };
                new ApexCharts(document.querySelector("#financialChart"), financialOptions).render();
            }

            if (document.querySelector("#workloadChart") && chartData.workload.series.length > 0) {
                const workloadOptions = {
                    series: [{ name: 'Tareas Asignadas', data: chartData.workload.series }],
                    chart: { type: 'bar', height: 350, toolbar: { show: false } },
                    plotOptions: { bar: { borderRadius: 4, horizontal: true, barHeight: '70%' } },
                    dataLabels: { enabled: true, style: { colors: ['#fff'], fontWeight: 'bold' }, offsetX: -25 },
                    xaxis: { categories: chartData.workload.labels, labels: { show: false }, axisBorder: { show: false }, axisTicks: { show: false } },
                    yaxis: { labels: { style: { fontWeight: 'bold' } } },
                    grid: { show: false },
                    colors: ['#4338ca'],
                };
                new ApexCharts(document.querySelector("#workloadChart"), workloadOptions).render();
            }

            if (document.querySelector("#statusChart") && chartData.status.series.length > 0) {
                const statusOptions = {
                    series: chartData.status.series,
                    chart: { type: 'donut', height: 300 },
                    labels: chartData.status.labels,
                    colors: ['#6c757d', '#0d6efd', '#ffc107', '#198754', '#dc3545'],
                    legend: { position: 'bottom' },
                    dataLabels: { formatter: (val, opts) => opts.w.config.series[opts.seriesIndex] }
                };
                new ApexCharts(document.querySelector("#statusChart"), statusOptions).render();
            }
        });
    </script>

</x-app-layout>