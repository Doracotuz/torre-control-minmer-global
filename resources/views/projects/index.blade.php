<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-3xl text-[#2c3856] leading-tight tracking-tight">
                    Centro de Mando de Proyectos
                </h2>
                <p class="text-md text-gray-500 mt-1">
                    Análisis interactivo del portafolio.
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
        
        <script type="application/json" id="dashboard-data">
            @json($dashboardData)
        </script>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white p-6 rounded-xl shadow-lg mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-[#2c3856] text-xl" 
                        x-show="filterStatus === 'all' && filterHealth === 'all'"
                        x-transition>Resumen del Portafolio</h3>
                    <h3 class="font-bold text-[#2c3856] text-xl" 
                        x-show="filterStatus !== 'all' || filterHealth !== 'all'"
                        x-transition>
                        Resumen Filtrado
                    </h3>
                    <button @click="resetFilters()" x-show="filterStatus !== 'all' || filterHealth !== 'all'" x-transition
                            class="text-sm text-indigo-600 hover:text-indigo-800 font-semibold">
                        Limpiar filtros
                    </button>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="card p-4 rounded-xl flex-row items-center bg-blue-50">
                        <p class="text-gray-500 text-sm">Proyectos Activos</p>
                        <p class="text-3xl font-bold text-[#2c3856]" x-text="kpis.activeCount"></p>
                    </div>
                    <div class="card p-4 rounded-xl flex-row items-center bg-red-50">
                        <p class="text-gray-500 text-sm">Proyectos Vencidos</p>
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">

                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h3 class="font-bold text-[#2c3856] text-xl mb-4">Distribución por Estatus</h3>
                    <div id="statusChart" x-ref="statusChart" class="flex justify-center -mt-4 -mb-4"></div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h3 class="font-bold text-[#2c3856] text-xl mb-4">Salud del Portafolio</h3>
                    <div id="healthChart" x-ref="healthChart" class="flex justify-center -mt-4 -mb-4"></div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h3 class="font-bold text-[#2c3856] text-xl mb-4">Carga de Trabajo (Tareas Activas)</h3>
                    <div id="workloadChart" x-ref="workloadChart" class="flex justify-center -mt-4 -mb-4"></div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h3 class="font-bold text-[#2c3856] text-xl mb-4">Resumen Global de Tareas</h3>
                    <div id="taskSummaryChart" x-ref="taskSummaryChart" class="flex justify-center -mt-4 -mb-4"></div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h3 class="font-bold text-[#2c3856] text-xl mb-4">Top 5 Proyectos por Gasto</h3>
                    <div id="financialChart" x-ref="financialChart" class="flex justify-center -mt-4 -mb-4"></div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h3 class="font-bold text-[#2c3856] text-xl mb-4">Próximos a Vencer (30 Días)</h3>
                    <div id="timelineChart" x-ref="timelineChart" class="flex justify-center -mt-4 -mb-4"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h3 class="font-bold text-[#2c3856] text-xl mb-4">Mi Enfoque</h3>
                    <div class="space-y-4 max-h-96 overflow-y-auto">
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
                    <h3 class="font-bold text-[#2c3856] text-xl mb-4">Actividad Reciente</h3>
                    <div class="space-y-6 max-h-96 overflow-y-auto">
                        <template x-for="activity in recentActivity" :key="activity.id">
                            <div class="relative flex timeline-item">
                                <div class="h-10 w-10 flex-shrink-0 flex items-center justify-center rounded-full bg-indigo-100 text-indigo-600 font-bold z-10 ring-8 ring-white">
                                    <template x-if="activity.user.profile_photo_path">
                                        <img class="h-10 w-10 rounded-full object-cover" :src="'/storage/' + activity.user.profile_photo_path.replace('public/', '')" :alt="activity.user.name">
                                    </template>
                                    <template x-if="!activity.user.profile_photo_path">
                                        <span x-text="activity.user.name.match(/\b(\w)/g).join('').substring(0, 2)"></span>
                                    </template>
                                </div>
                                <div class="ml-4">
                                    <p class="text-gray-800 text-sm">
                                        <span class="font-semibold" x-text="activity.user.name"></span>
                                        <span x-show="activity.action_type == 'comment'"> comentó en</span>
                                        <span x-show="activity.action_type == 'status_change'"> actualizó el estatus de</span>
                                        <span x-show="activity.action_type == 'file_upload'"> subió un archivo a</span>
                                        <span x-show="activity.action_type == 'expense_added'"> registró un gasto en</span>
                                        <a :href="'/projects/' + activity.project.id" class="font-semibold text-indigo-600 hover:underline" x-text="activity.project.name"></a>
                                    </p>
                                    <template x-if="activity.action_type == 'status_change'">
                                        <p class="text-gray-600 text-sm mt-1">
                                            <span class="font-semibold px-2 py-0.5 rounded-full text-xs bg-gray-200 text-gray-700" x-text="activity.old_status"></span>
                                            <i class="fas fa-long-arrow-alt-right mx-1 text-gray-500"></i>
                                            <span class="font-semibold px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-800" x-text="activity.new_status"></span>
                                        </p>
                                    </template>
                                    <template x-if="activity.action_type != 'status_change'">
                                        <p class="text-gray-600 text-sm mt-1 pl-4 border-l-2 border-gray-200 italic" x-text="activity.comment_body.substring(0, 90) + (activity.comment_body.length > 90 ? '...' : '')"></p>
                                    </template>
                                    <p class="text-xs text-gray-400 mt-1" x-text="new Date(activity.created_at).toLocaleString('es-MX', { dateStyle: 'medium', timeStyle: 'short' })"></p>
                                </div>
                            </div>
                        </template>
                        <template x-if="recentActivity.length === 0">
                            <p class="text-sm text-gray-500">No hay actividad reciente.</p>
                        </template>
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
                filterHealth: 'all', 
                charts: {
                    status: null,
                    health: null,
                    workload: null,
                    taskSummary: null,
                    financial: null,
                    timeline: null
                },

                init() {
                    const data = JSON.parse(document.getElementById('dashboard-data').textContent);
                    this.allProjects = data.projects;
                    this.myTasks = data.myTasks;
                    this.recentActivity = data.recentActivity;
                    
                    this.initAllCharts();

                    this.$watch('filterStatus', () => this.updateAllCharts());
                    this.$watch('filterHealth', () => this.updateAllCharts());
                },

                resetFilters() {
                    this.filterStatus = 'all';
                    this.filterHealth = 'all';
                    this.charts.status?.clearSelection();
                    this.charts.health?.clearSelection();
                },
                setStatusFilter(status) { this.filterStatus = (this.filterStatus === status) ? 'all' : status; },
                setHealthFilter(health) { this.filterHealth = (this.filterHealth === health) ? 'all' : health; },

                get filteredProjects() {
                    return this.allProjects.filter(p => {
                        const statusMatch = this.filterStatus === 'all' || p.status === this.filterStatus;
                        const healthMatch = this.filterHealth === 'all' || p.health_status === this.filterHealth;
                        return statusMatch && healthMatch;
                    });
                },

                get kpis() {
                    const projects = this.filteredProjects;
                    return {
                        activeCount: projects.filter(p => ['Planeación', 'En Progreso', 'En Pausa'].includes(p.status)).length,
                        overdueCount: projects.filter(p => p.health_status === 'Vencido').length,
                        totalBudget: '$' + (projects.reduce((sum, p) => sum + parseFloat(p.budget || 0), 0) / 1000).toFixed(1) + 'k',
                        totalSpent: '$' + (projects.reduce((sum, p) => sum + parseFloat(p.spent || 0), 0) / 1000).toFixed(1) + 'k',
                    }
                },
                
                getChartData() {
                    const projects = this.filteredProjects;
                    const allTasks = projects.pluck('tasks').flatten();

                    const statusMap = projects.groupBy('status');
                    const healthMap = projects.groupBy('health_status');

                    const workloadMap = allTasks.filter(t => t.status !== 'Completada' && t.assignee)
                                              .groupBy('assignee.name');
                    
                    const taskMap = allTasks.groupBy('status');

                    const topFinancial = projects.filter(p => p.spent > 0)
                                                 .sortByDesc('spent')
                                                 .take(5);
                    
                    const upcoming = projects.filter(p => p.due_date && new Date(p.due_date) > new Date() && new Date(p.due_date) < new Date(new Date().setDate(new Date().getDate() + 30)))
                                             .sortBy('due_date');

                    return {
                        status: {
                            series: ['Planeación', 'En Progreso', 'En Pausa', 'Completado', 'Cancelado'].map(s => statusMap[s]?.length || 0),
                            labels: ['Planeación', 'En Progreso', 'En Pausa', 'Completado', 'Cancelado']
                        },
                        health: {
                            series: ['A Tiempo', 'En Riesgo', 'Vencido', 'Archivado'].map(s => healthMap[s]?.length || 0),
                            labels: ['A Tiempo', 'En Riesgo', 'Vencido', 'Archivado']
                        },
                        workload: {
                            series: Object.values(workloadMap).map(tasks => tasks.length),
                            labels: Object.keys(workloadMap)
                        },
                        taskSummary: {
                            series: [taskMap['Pendiente']?.length || 0, taskMap['En Progreso']?.length || 0, taskMap['Completada']?.length || 0],
                            labels: ['Pendiente', 'En Progreso', 'Completada']
                        },
                        financial: {
                            series: [{ name: 'Gasto', data: topFinancial.map(p => p.spent) }],
                            labels: topFinancial.map(p => p.name)
                        },
                        timeline: {
                            series: [{ data: upcoming.map(p => ({
                                x: p.name,
                                y: [new Date(p.start_date).getTime(), new Date(p.due_date).getTime()]
                            }))}],
                        }
                    };
                },

                initAllCharts() {
                    const data = this.getChartData();
                    
                    if (this.$refs.statusChart) {
                        const options = {
                            series: data.status.series,
                            labels: data.status.labels,
                            chart: { 
                                type: 'donut', 
                                height: 300,
                                events: { 
                                    dataPointSelection: (e, chart, config) => {
                                        this.setStatusFilter(data.status.labels[config.dataPointIndex]);
                                    }
                                }
                            },
                            colors: ['#6c757d', '#0d6efd', '#ffc107', '#198754', '#dc3545'],
                            legend: { position: 'bottom' },
                            dataLabels: { 
                                formatter: (val, opts) => {
                                    return opts.w.config.series[opts.seriesIndex];
                                }
                            },
                            plotOptions: { 
                                pie: { 
                                    donut: { 
                                        labels: { 
                                            show: true, 
                                            total: { show: true, label: 'Total' }
                                        }
                                    }
                                }
                            }
                        };
                        this.charts.status = new ApexCharts(this.$refs.statusChart, options);
                        this.charts.status.render();
                    }
                    
                    if (this.$refs.healthChart) {
                        const options = {
                            series: data.health.series,
                            labels: data.health.labels,
                            chart: { 
                                type: 'donut', 
                                height: 300,
                                events: { 
                                    dataPointSelection: (e, chart, config) => {
                                        this.setHealthFilter(data.health.labels[config.dataPointIndex]);
                                    }
                                }
                            },
                            colors: ['#28a745', '#dd6b20', '#e53e3e', '#6c757d'],
                            legend: { position: 'bottom' },
                            dataLabels: { 
                                formatter: (val, opts) => {
                                    return opts.w.config.series[opts.seriesIndex];
                                }
                            },
                            plotOptions: { 
                                pie: { 
                                    donut: { 
                                        labels: { 
                                            show: true, 
                                            total: { show: true, label: 'Total' }
                                        }
                                    }
                                }
                            }
                        };
                        this.charts.health = new ApexCharts(this.$refs.healthChart, options);
                        this.charts.health.render();
                    }

                    if (this.$refs.workloadChart) {
                        const options = {
                            series: [{ 
                                name: 'Tareas Activas', 
                                data: data.workload.series 
                            }],
                            chart: { 
                                type: 'bar', 
                                height: 250 
                            },
                            xaxis: { 
                                categories: data.workload.labels 
                            },
                            plotOptions: { 
                                bar: { 
                                    horizontal: true, 
                                    barHeight: '70%' 
                                } 
                            },
                            colors: ['#4338ca'],
                            dataLabels: { 
                                enabled: true, 
                                style: { 
                                    colors: ['#fff'], 
                                    fontWeight: 'bold' 
                                }, 
                                offsetX: -25 
                            },
                        };
                        this.charts.workload = new ApexCharts(this.$refs.workloadChart, options);
                        this.charts.workload.render();
                    }

                    if (this.$refs.taskSummaryChart) {
                        const options = {
                            series: [{ 
                                name: 'Tareas', 
                                data: data.taskSummary.series 
                            }],
                            chart: { 
                                type: 'bar', 
                                height: 250 
                            },
                            colors: ['#ecc94b', '#3182ce', '#28a745'],
                            plotOptions: { 
                                bar: { 
                                    distributed: true 
                                } 
                            },
                            dataLabels: { 
                                enabled: true 
                            }, 
                            legend: { 
                                show: false 
                            },
                            xaxis: { 
                                categories: data.taskSummary.labels 
                            }
                        };
                        this.charts.taskSummary = new ApexCharts(this.$refs.taskSummaryChart, options);
                        this.charts.taskSummary.render();
                    }

                    if (this.$refs.financialChart) {
                        const options = {
                            series: [{ 
                                name: 'Gasto', 
                                data: data.financial.series[0].data 
                            }],
                            chart: { 
                                type: 'bar', 
                                height: 250 
                            },
                            xaxis: { 
                                categories: data.financial.labels 
                            },
                            plotOptions: { 
                                bar: { 
                                    horizontal: true 
                                } 
                            },
                            colors: ['#ff9c00'],
                            dataLabels: { 
                                enabled: true, 
                                formatter: (val) => {
                                    return '$' + (val / 1000).toFixed(1) + 'k';
                                }
                            },
                        };
                        this.charts.financial = new ApexCharts(this.$refs.financialChart, options);
                        this.charts.financial.render();
                    }

                    if (this.$refs.timelineChart) {
                        const options = {
                            series: data.timeline.series,
                            chart: { 
                                type: 'rangeBar', 
                                height: 250 
                            },
                            plotOptions: { 
                                bar: { 
                                    horizontal: true 
                                } 
                            },
                            xaxis: { 
                                type: 'datetime' 
                            },
                            dataLabels: { 
                                enabled: true, 
                                formatter: (val, opts) => {
                                    return opts.w.config.series[0].data[opts.dataPointIndex].x;
                                }
                            }
                        };
                        this.charts.timeline = new ApexCharts(this.$refs.timelineChart, options);
                        this.charts.timeline.render();
                    }
                },

                updateAllCharts() {
                    const data = this.getChartData();
                    
                    if (this.charts.status) {
                        this.charts.status.updateSeries(data.status.series);
                    }
                    if (this.charts.health) {
                        this.charts.health.updateSeries(data.health.series);
                    }
                    if (this.charts.workload) {
                        this.charts.workload.updateSeries([{ 
                            name: 'Tareas Activas', 
                            data: data.workload.series 
                        }]);
                        this.charts.workload.updateOptions({
                            xaxis: { categories: data.workload.labels }
                        });
                    }
                    if (this.charts.taskSummary) {
                        this.charts.taskSummary.updateSeries([{ 
                            name: 'Tareas', 
                            data: data.taskSummary.series 
                        }]);
                        this.charts.taskSummary.updateOptions({
                            xaxis: { categories: data.taskSummary.labels }
                        });
                    }
                    if (this.charts.financial) {
                        this.charts.financial.updateSeries([{ 
                            name: 'Gasto', 
                            data: data.financial.series[0].data 
                        }]);
                        this.charts.financial.updateOptions({
                            xaxis: { categories: data.financial.labels }
                        });
                    }
                    if (this.charts.timeline) {
                        this.charts.timeline.updateSeries(data.timeline.series);
                    }
                }
            }));

            if (!Array.prototype.pluck) {
                Array.prototype.pluck = function(key) { return this.map(item => item[key]); }
            }
            if (!Array.prototype.flatten) {
                Array.prototype.flatten = function() { return [].concat.apply([], this); }
            }
            if (!Array.prototype.groupBy) {
                Array.prototype.groupBy = function(key) {
                    return this.reduce((acc, item) => {
                        const group = key.split('.').reduce((o, i) => o?.[i], item);
                        (acc[group] = acc[group] || []).push(item);
                        return acc;
                    }, {});
                }
            }
            if (!Array.prototype.sortByDesc) {
                Array.prototype.sortByDesc = function(key) {
                    return this.sort((a, b) => (a[key] < b[key]) ? 1 : ((a[key] > b[key]) ? -1 : 0));
                }
            }
            if (!Array.prototype.sortBy) {
                Array.prototype.sortBy = function(key) {
                    return this.sort((a, b) => (a[key] > b[key]) ? 1 : ((a[key] < b[key]) ? -1 : 0));
                }
            }
            if (!Array.prototype.take) {
                Array.prototype.take = function(count) { return this.slice(0, count); }
            }
        });
    </script>
</x-app-layout>