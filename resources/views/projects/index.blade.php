<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-3xl text-[#2c3856] leading-tight tracking-tight">
                    Dashboard de Proyectos
                </h2>
                <p class="text-md text-gray-500 mt-1">
                    Resumen interactivo de tu portafolio.
                </p>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('projects.list') }}" class="px-5 py-2 bg-[#2c3856] text-white font-semibold rounded-lg shadow-md hover:bg-gray-800 transition-colors">
                    <i class="fas fa-grip-horizontal mr-2"></i> Ir al Kanban
                </a>
                <a href="{{ route('projects.review') }}" class="px-5 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-users mr-2"></i> Vista de Reunión
                </a>
            </div>               
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50" x-data="projectDashboard">
        
        <script typePlease="application/json" id="dashboard-data">
            {
                "projects": @json($visibleProjects),
                "myTasks": @json($myPendingTasks),
                "recentActivity": @json($recentActivity)
            }
        </script>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="lg:col-span-2 space-y-8">

                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-[#2c3856] text-xl" 
                                x-show="filterStatus === 'all'"
                                x-transition>Resumen del Portafolio</h3>
                            <h3 class="font-bold text-[#2c3856] text-xl" 
                                x-show="filterStatus !== 'all'"
                                x-transition>
                                Resumen de: <span class="capitalize" x-text="filterStatus"></span>
                            </h3>
                            <button @click="resetFilters()" x-show="filterStatus !== 'all'" x-transition
                                    class="text-sm text-indigo-600 hover:text-indigo-800 font-semibold">
                                Limpiar filtro
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="card p-4 rounded-xl flex-row items-center bg-blue-50">
                                <p class="text-gray-500 text-sm">Proyectos Activos</p>
                                <p class="text-3xl font-bold text-[#2c3856]" x-text="kpis.activeCount"></p>
                            </div>
                            <div class="card p-4 rounded-xl flex-row items-center bg-red-50">
                                <p class="text-gray-500 text-sm">Proyectos Atrasados</p>
                                <p class="text-3xl font-bold text-[#2c3856]" x-text="kpis.overdueCount"></p>
                            </div>
                            <div class="card p-4 rounded-xl flex-row items-center bg-green-50">
                                <p class="text-gray-500 text-sm">Presupuesto</p>
                                <p class="text-2xl font-bold text-[#2c3856]" x-text="kpis.totalBudget"></p>
                            </div>
                            <div class="card p-4 rounded-xl flex-row items-center bg-orange-50">
                                <p class="text-gray-500 text-sm">Gasto Total</p>
                                <p class="text-2xl font-bold text-[#2c3856]" x-text="kpis.totalSpent"></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <h3 class="font-bold text-[#2c3856] text-xl mb-4">Proyectos Críticos</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="font-semibold text-red-600 mb-2">Atrasados</h4>
                                <div class="space-y-3 max-h-48 overflow-y-auto">
                                    <template x-for="project in criticalProjects.overdue" :key="project.id">
                                        <a :href="'/projects/' + project.id" class="block p-3 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                                            <div class="flex justify-between items-center">
                                                <span class="font-semibold text-gray-800" x-text="project.name"></span>
                                                <span class="text-xs font-bold text-red-700" x-text="project.due_date ? (new Date(project.due_date)).toLocaleDateString() : ''"></span>
                                            </div>
                                        </a>
                                    </template>
                                    <template x-if="criticalProjects.overdue.length === 0">
                                        <div class="flex items-center text-sm text-gray-500 pt-4"><i class="fas fa-glass-cheers mr-2 text-green-500"></i> ¡Felicidades! No hay proyectos atrasados.</div>
                                    </template>
                                </div>
                            </div>
                            <div>
                                <h4 class="font-semibold text-yellow-600 mb-2">Próximos a Vencer (7 días)</h4>
                                <div class="space-y-3 max-h-48 overflow-y-auto">
                                     <template x-for="project in criticalProjects.atRisk" :key="project.id">
                                        <a :href="'/projects/' + project.id" class="block p-3 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors">
                                            <div class="flex justify-between items-center">
                                                <span class="font-semibold text-gray-800" x-text="project.name"></span>
                                                <span class="text-xs font-bold text-yellow-700" x-text="project.due_date ? (new Date(project.due_date)).toLocaleDateString() : ''"></span>
                                            </div>
                                        </a>
                                    </template>
                                    <template x-if="criticalProjects.atRisk.length === 0">
                                        <p class="text-sm text-gray-500 pt-4">No hay proyectos con vencimientos cercanos.</p>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1 space-y-8">

                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <h3 class="font-bold text-[#2c3856] text-xl mb-4">Mi Enfoque</h3>
                        <div class="space-y-4 max-h-72 overflow-y-auto">
                            <template x-for="task in myTasks" :key="task.id">
                                <a :href="'/projects/' + task.project.id" class="block p-3 rounded-lg hover:bg-gray-100 transition-colors">
                                    <p class="font-semibold text-gray-800" x-text="task.name"></p>
                                    <p class="text-sm text-indigo-600" x-text="task.project.name"></p>
                                    <p class="text-xs text-gray-500 mt-1" x-text="task.due_date ? 'Vence: ' + (new Date(task.due_date)).toLocaleDateString() : 'Sin fecha'"></p>
                                </a>
                            </template>
                            <template x-if="myTasks.length === 0">
                                <div class="text-sm text-gray-500 text-center py-8">¡Sin tareas pendientes!</div>
                            </template>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <h3 class="font-bold text-[#2c3856] text-xl mb-4">Distribución (Filtro)</h3>
                        <div id="statusChart" x-ref="statusChart" class="flex justify-center -mt-4 -mb-4"></div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <h3 class="font-bold text-[#2c3856] text-xl mb-4">Actividad Reciente</h3>
                        <div class="space-y-6 max-h-96 overflow-y-auto">
                            @forelse ($recentActivity as $activity)
                                <div class="relative flex timeline-item">
                                    <div class="h-10 w-10 flex-shrink-0 flex items-center justify-center rounded-full bg-indigo-100 text-indigo-600 font-bold z-10 ring-8 ring-white">
                                        @if ($activity->user->profile_photo_path)
                                            <img class="h-10 w-10 rounded-full object-cover" src="{{ Storage::disk('s3')->url($activity->user->profile_photo_path) }}" alt="{{ $activity->user->name }}">
                                        @else
                                            @php $words = explode(" ", $activity->user->name); $initials = ""; foreach (array_slice($words, 0, 2) as $w) { $initials .= mb_substr($w, 0, 1); } @endphp
                                            {{ $initials }}
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-gray-800 text-sm">
                                            <span class="font-semibold">{{ $activity->user->name }}</span>
                                            @if ($activity->action_type == 'comment') comentó en
                                            @elseif ($activity->action_type == 'status_change') actualizó el estatus de
                                            @elseif ($activity->action_type == 'file_upload') subió un archivo a
                                            @elseif ($activity->action_type == 'expense_added') registró un gasto en
                                            @else realizó una acción en @endif
                                            <a href="{{ route('projects.show', $activity->project) }}" class="font-semibold text-indigo-600 hover:underline">{{ $activity->project->name }}</a>
                                        </p>
                                        @if ($activity->action_type == 'status_change')
                                            <p class="text-gray-600 text-sm mt-1">
                                                <span class="font-semibold px-2 py-0.5 rounded-full text-xs bg-gray-200 text-gray-700">{{ $activity->old_status }}</span>
                                                <i class="fas fa-long-arrow-alt-right mx-1 text-gray-500"></i>
                                                <span class="font-semibold px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-800">{{ $activity->new_status }}</span>
                                            </p>
                                        @else
                                            <p class="text-gray-600 text-sm mt-1 pl-4 border-l-2 border-gray-200 italic">"{{ Str::limit($activity->comment_body, 90) }}"</p>
                                        @endif
                                        <p class="text-xs text-gray-400 mt-1">{{ $activity->created_at->diffForHumans() }}</p>
                                    </div>
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
        document.addEventListener('alpine:init', () => {
            Alpine.data('projectDashboard', () => ({
                allProjects: [],
                myTasks: [],
                recentActivity: [],
                filterStatus: 'all',
                charts: {
                    status: null,
                },

                init() {
                    const data = JSON.parse(document.getElementById('dashboard-data').textContent);
                    this.allProjects = data.projects;
                    this.myTasks = data.myTasks;
                    this.recentActivity = data.recentActivity;
                    
                    this.initStatusChart();

                    this.$watch('filterStatus', () => {
                        this.updateCharts();
                    });
                },

                resetFilters() {
                    this.filterStatus = 'all';
                    this.charts.status.clearSelection();
                },
                setStatusFilter(status) {
                    this.filterStatus = status;
                },

                get filteredProjects() {
                    return this.allProjects.filter(p => {
                        const statusMatch = this.filterStatus === 'all' || p.status === this.filterStatus;
                        return statusMatch;
                    });
                },

                get kpis() {
                    const projects = this.filteredProjects;
                    return {
                        activeCount: projects.filter(p => ['Planeación', 'En Progreso', 'En Pausa'].includes(p.status)).length,
                        overdueCount: projects.filter(p => p.health_status === 'overdue').length,
                        totalBudget: '$' + (projects.reduce((sum, p) => sum + parseFloat(p.budget || 0), 0) / 1000).toFixed(1) + 'k',
                        totalSpent: '$' + (projects.reduce((sum, p) => sum + parseFloat(p.spent || 0), 0) / 1000).toFixed(1) + 'k',
                    }
                },
                
                get criticalProjects() {
                    const projects = this.filteredProjects;
                    return {
                        overdue: projects.filter(p => p.health_status === 'overdue').sort((a,b) => new Date(a.due_date) - new Date(b.due_date)),
                        atRisk: projects.filter(p => p.health_status === 'at-risk').sort((a,b) => new Date(a.due_date) - new Date(b.due_date)),
                    }
                },

                getChartData() {
                    const statusMap = this.filteredProjects.reduce((map, p) => {
                        map[p.status] = (map[p.status] || 0) + 1;
                        return map;
                    }, {});
                    
                    const labels = ['Planeación', 'En Progreso', 'En Pausa', 'Completado', 'Cancelado'];
                    const series = labels.map(label => statusMap[label] || 0);

                    return {
                        status: { series, labels }
                    };
                },

                initStatusChart() {
                    const data = this.getChartData().status;
                    const options = {
                        series: data.series,
                        chart: { 
                            type: 'donut', 
                            height: 300,
                            events: {
                                dataPointSelection: (event, chartContext, config) => {
                                    const selectedStatus = data.labels[config.dataPointIndex];
                                    this.setStatusFilter(selectedStatus);
                                }
                            }
                        },
                        labels: data.labels,
                        colors: ['#6c757d', '#0d6efd', '#ffc107', '#198754', '#dc3545'],
                        legend: { position: 'bottom' },
                        dataLabels: { formatter: (val, opts) => opts.w.config.series[opts.seriesIndex] }
                    };
                    this.charts.status = new ApexCharts(this.$refs.statusChart, options);
                    this.charts.status.render();
                },

                updateCharts() {
                    const data = this.getChartData();
                    this.charts.status.updateSeries(data.status.series);
                }
            }));
        });
    </script>
</x-app-layout>