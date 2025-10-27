<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div>
                <h2 class="font-bold text-3xl text-[#2c3856] leading-tight tracking-tight">
                    Centro de Mando: Proyectos
                </h2>
                <p class="text-md text-gray-500 mt-1">Dashboard de BI interactivo para revisión y toma de decisiones.</p>
            </div>
            <a href="{{ route('projects.list') }}" class="mt-4 md:mt-0 px-5 py-2 bg-[#2c3856] text-white font-semibold rounded-lg shadow-md hover:bg-gray-800 transition-colors">
                <i class="fas fa-grip-horizontal mr-2"></i> Ir al Kanban
            </a>
        </div>
    </x-slot>

    <script type="application/json" id="projects-data">
        @json($projects)
    </script>
    <script type="application/json" id="statuses-data">
        @json($statuses)
    </script>
    <script type="application/json" id="leaders-data">
        @json($leaders)
    </script>
    <script type="application/json" id="areas-data">
        @json($areas)
    </script>


    <div class="py-12" x-data="projectReviewManager" x-init="initData()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Notificaciones de éxito --}}
            @if (session('success_comment'))
                <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert"><p>{{ session('success_comment') }}</p></div>
            @endif
            @if (session('success_status'))
                <div class="mb-6 bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4" role="alert"><p>{{ session('success_status') }}</p></div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div class="card p-6 rounded-xl shadow-lg" x-data="chartDonut(biHealthData, 'Salud de Proyectos', ['#28a745', '#ffc107', '#dc3545'], (status) => setHealthFilter(status))">
                    <h3 class="font-bold text-[#2c3856] text-xl mb-4">Salud del Portafolio</h3>
                    <div x-ref="chart" class="h-64"></div>
                </div>

                <div class="card p-6 rounded-xl shadow-lg" x-data="chartBar(biTaskData, 'Estatus de Tareas', ['#ffc107', '#0d6efd', '#28a745'])">
                    <h3 class="font-bold text-[#2c3856] text-xl mb-4">Estatus de Tareas</h3>
                    <div x-ref="chart" class="h-68"></div>
                </div>

                <div class="card p-6 rounded-xl shadow-lg">
                    <h3 class="font-bold text-[#2c3856] text-xl mb-4">Carga de Trabajo (Tareas Activas)</h3>
                    <ul class="space-y-3 h-64 overflow-y-auto">
                        <template x-for="user in biWorkloadData" :key="user.id">
                            <li class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-100 cursor-pointer" 
                                @click="setWorkloadFilter(user.id)"
                                :class="{ 'bg-indigo-100': filterWorkloadUser == user.id }">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-600 text-white font-bold text-xs mr-3"
                                          x-text="user.initials"></span>
                                    <span class="text-sm font-semibold text-gray-700" x-text="user.name"></span>
                                </div>
                                <span class="text-lg font-bold text-[#2c3856]" x-text="user.tasks_count"></span>
                            </li>
                        </template>
                        <template x-if="biWorkloadData.length === 0">
                            <li class="text-center text-sm text-gray-500 pt-16">No hay tareas activas.</li>
                        </template>
                    </ul>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl shadow-lg mb-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-1">
                        <label for="search" class="block text-sm font-medium text-gray-700">Buscar Proyecto</label>
                        <input type="text" id="search" x-model.debounce.300ms="search" placeholder="Escribe..."
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="filterLeader" class="block text-sm font-medium text-gray-700">Líder</label>
                        <select id="filterLeader" x-model="filterLeader" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="all">Todos los Líderes</option>
                            <template x-for="leader in leaders" :key="leader.id">
                                <option :value="leader.id" x-text="leader.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label for="filterArea" class="block text-sm font-medium text-gray-700">Área</label>
                        <select id="filterArea" x-model="filterArea" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="all">Todas las Áreas</option>
                            <template x-for="area in areas" :key="area.id">
                                <option :value="area.id" x-text="area.name"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button @click="clearFilters()" class="w-full h-10 px-4 py-2 bg-gray-600 text-white text-sm font-semibold rounded-lg shadow-md hover:bg-gray-800 transition-colors">
                            <i class="fas fa-times-circle mr-2"></i> Limpiar Filtros
                        </button>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 mt-4" x-show="filterHealth !== 'all' || filterWorkloadUser !== 'all'">
                    <span x-show="filterHealth !== 'all'" class="flex items-center text-sm font-semibold bg-red-100 text-red-700 px-3 py-1 rounded-full">
                        Salud: <span class="capitalize ml-1" x-text="filterHealth"></span>
                        <button @click="filterHealth = 'all'" class="ml-2 text-red-500 hover:text-red-700">&times;</button>
                    </span>
                    <span x-show="filterWorkloadUser !== 'all'" class="flex items-center text-sm font-semibold bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full">
                        Usuario: <span class="ml-1" x-text="leaders.find(l => l.id == filterWorkloadUser)?.name || '...'"></span>
                        <button @click="filterWorkloadUser = 'all'" class="ml-2 text-indigo-500 hover:text-indigo-700">&times;</button>
                    </span>
                </div>
            </div>

            <div class="space-y-10">
                <template x-for="project in filteredProjects" :key="project.id">
                    <div class="bg-white rounded-xl shadow-2xl overflow-hidden transition-all duration-300"
                         :class="{ 'opacity-100': true, 'opacity-30 hover:opacity-100': (filterHealth !== 'all' && getProjectHealth(project).status !== filterHealth) || (filterWorkloadUser !== 'all' && !project.tasks.some(t => t.assignee_id == filterWorkloadUser)) }">
                        
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex flex-col md:flex-row justify-between md:items-start">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-circle fa-sm mr-3" :class="getProjectHealth(project).color" :title="'Salud: ' + getProjectHealth(project).text"></i>
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full mr-3"
                                              :class="{
                                                  'bg-gray-200 text-gray-800': project.status === 'Planeación',
                                                  'bg-blue-200 text-blue-800': project.status === 'En Progreso',
                                                  'bg-yellow-200 text-yellow-800': project.status === 'En Pausa'
                                              }"
                                              x-text="project.status"></span>
                                        <h3 class="text-2xl font-bold text-gray-900">
                                            <a :href="'/projects/' + project.id" class="hover:text-indigo-600" title="Ver detalles" x-text="project.name"></a>
                                        </h3>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-3">
                                        <span class="font-semibold">Líder:</span> <span x-text="project.leader ? project.leader.name : 'N/A'"></span> | 
                                        <span class="font-semibold">Áreas:</span> <span x-text="project.areas.map(a => a.name).join(', ') || 'N/A'"></span>
                                    </p>
                                    <p class="text-sm text-gray-500" x-text="project.description ? project.description.substring(0, 250) + (project.description.length > 250 ? '...' : '') : ''"></p>
                                </div>
                                
                                <div class="w-full md:w-56 mt-4 md:mt-0 flex-shrink-0">
                                    <form :action="'/projects/' + project.id + '/status'" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <label :for="'status-' + project.id" class="block text-xs font-medium text-gray-500 mb-1">Cambiar Estatus (En vivo)</label>
                                        <select name="status" :id="'status-' + project.id" onchange="this.form.submit()"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-semibold text-sm">
                                            <template x-for="status in statuses" :key="status">
                                                <option :value="status" :selected="project.status === status" x-text="status"></option>
                                            </template>
                                        </select>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-gray-50 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between items-center text-sm mb-1">
                                        <span class="font-semibold text-gray-700">Progreso de Tareas</span>
                                        <span class="font-bold text-gray-900" x-text="project.tasks.filter(t => t.status === 'Completada').length + ' / ' + project.tasks.length"></span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-blue-600 h-2.5 rounded-full" :style="'width: ' + (project.tasks.length > 0 ? (project.tasks.filter(t => t.status === 'Completada').length / project.tasks.length) * 100 : 0) + '%'"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-700">Salud del Proyecto (Tiempo)</div>
                                    <div x-show="!project.due_date" class="text-sm font-bold text-gray-500">Fecha no definida</div>
                                    <template x-if="project.due_date">
                                        <div x-data="{ daysLeft: Math.round((new Date(project.due_date) - new Date()) / (1000 * 60 * 60 * 24)) }">
                                            <div x-show="daysLeft >= 0" class="text-lg font-bold" :class="daysLeft <= 7 ? 'text-yellow-600' : 'text-green-600'" x-text="daysLeft + ' días restantes'"></div>
                                            <div x-show="daysLeft < 0" class="text-lg font-bold text-red-600" x-text="'Vencido hace ' + Math.abs(daysLeft) + ' días'"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div>
                                <span class="text-sm font-semibold text-gray-700">Equipo Involucrado</span>
                                <div class="flex flex-wrap items-center mt-2">
                                    <template x-for="user in [...new Map(project.tasks.map(task => task.assignee).filter(Boolean).map(user => [user.id, user])).values()]" :key="user.id">
                                        <span :title="user.name" class="mr-2 mb-2 inline-flex items-center justify-center h-10 w-10 rounded-full bg-gray-600 text-white font-bold text-xs ring-2 ring-white"
                                              x-text="user.name.split(' ').slice(0, 2).map(n => n[0]).join('')">
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-200" x-data="{ openTab: '' }">
                            <div class="flex bg-gray-50">
                                <button @click="openTab = (openTab === 'tasks' ? '' : 'tasks')" :class="openTab === 'tasks' ? 'bg-white border-b-2 border-indigo-500' : 'text-gray-500 hover:text-gray-700'" class="flex-1 py-3 px-4 font-semibold text-sm focus:outline-none">
                                    <i class="fas fa-tasks mr-2"></i> Ver Tareas (<span x-text="project.tasks.length"></span>)
                                </button>
                                <button @click="openTab = (openTab === 'comments' ? '' : 'comments')" :class="openTab === 'comments' ? 'bg-white border-b-2 border-indigo-500' : 'text-gray-500 hover:text-gray-700'" class="flex-1 py-3 px-4 font-semibold text-sm focus:outline-none border-l">
                                    <i class="fas fa-comments mr-2"></i> Comentarios (<span x-text="project.comments.length"></span>)
                                </button>
                            </div>
                            <div x-show="openTab === 'tasks'" x-transition class="p-6 max-h-96 overflow-y-auto">
                                <h4 class="text-lg font-semibold text-gray-800 mb-4">Lista de Tareas</h4>
                                <ul class="space-y-3">
                                    <template x-if="project.tasks.length === 0">
                                        <li class="text-sm text-gray-500 text-center py-4">No hay tareas creadas.</li>
                                    </template>
                                    <template x-for="task in project.tasks" :key="task.id">
                                        <li class="p-3 bg-white border rounded-md">
                                            <div class="flex items-center justify-between text-sm">
                                                <div class="flex items-center">
                                                    <i class="fa-lg mr-3" :class="{
                                                        'fas fa-check-circle text-green-500': task.status === 'Completada',
                                                        'far fa-circle text-gray-400': task.status !== 'Completada'
                                                    }"></i>
                                                    <div>
                                                        <span class="font-semibold" :class="task.status === 'Completada' ? 'line-through text-gray-500' : 'text-gray-900'" x-text="task.name"></span>
                                                        <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-medium"
                                                              :class="{
                                                                  'bg-yellow-100 text-yellow-800': task.status === 'Pendiente',
                                                                  'bg-blue-100 text-blue-800': task.status === 'En Progreso',
                                                                  'bg-green-100 text-green-800': task.status === 'Completada'
                                                              }"
                                                              x-text="task.status"></span>
                                                    </div>
                                                </div>
                                                <div class="flex items-center space-x-4">
                                                    <span class="text-gray-500 hidden md:block" title="Asignado a">
                                                        <i class="fas fa-user mr-1"></i> <span x-text="task.assignee ? task.assignee.name : 'Sin asignar'"></span>
                                                    </span>
                                                </div>
                                            </div>
                                            <p x-show="task.description" class="text-sm text-gray-600 mt-2 pt-2 pl-4 border-l-2 border-gray-200 ml-4" x-html="task.description.replace(/\n/g, '<br>')"></p>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                            <div x-show="openTab === 'comments'" x-transition class="p-6">
                                <h4 class="text-lg font-semibold text-gray-800 mb-4">Comentarios</h4>
                                <form :action="'/projects/' + project.id + '/comments'" method="POST" class="mb-6">
                                    @csrf
                                    <textarea name="body" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Escribe una actualización o comentario..."></textarea>
                                    <div class="flex justify-end mt-2">
                                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg shadow-md hover:bg-indigo-700">Comentar</button>
                                    </div>
                                </form>
                                <div class="space-y-4 max-h-60 overflow-y-auto">
                                    <template x-if="project.comments.length === 0">
                                        <p class="text-sm text-gray-500 text-center py-4">No hay comentarios todavía.</p>
                                    </template>
                                    <template x-for="comment in [...project.comments].reverse()" :key="comment.id">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 mr-3">
                                                <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-500 text-white font-bold text-xs"
                                                      x-text="comment.user.name.split(' ').slice(0, 2).map(n => n[0]).join('')"></span>
                                            </div>
                                            <div class="bg-gray-100 rounded-lg p-3 flex-1 border">
                                                <p class="text-sm font-semibold text-gray-900" x-text="comment.user.name"></p>
                                                <p class="text-sm text-gray-700 mt-1" x-html="comment.body.replace(/\n/g, '<br>')"></p>
                                                <p class="text-xs text-gray-400 mt-2 text-right" x-text="new Date(comment.created_at).toLocaleString('es-MX', { dateStyle: 'medium', timeStyle: 'short' })"></p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="filteredProjects.length === 0">
                    <div class="text-center text-gray-500 border-2 border-dashed rounded-lg p-12">
                        <i class="fas fa-search fa-3x mb-4 text-gray-400"></i>
                        <h3 class="text-xl font-semibold">No se encontraron proyectos</h3>
                        <p>Intenta ajustar tus filtros de búsqueda.</p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
        // --- COMPONENTE DE GRÁFICO DONUT (REUTILIZABLE) ---
        function chartDonut(data, title, colors, onClickCallback) {
            return {
                chart: null,
                init() {
                    const options = {
                        series: data.series,
                        labels: data.labels,
                        chart: {
                            type: 'donut',
                            // --- CAMBIO CLAVE ---
                            height: '85%', // Deja 15% de espacio para la leyenda
                            events: {
                                dataPointSelection: (event, chartContext, config) => {
                                    const statusMap = {'En Tiempo': 'on-track', 'En Riesgo': 'at-risk', 'Vencido': 'overdue'};
                                    const selectedLabel = config.w.config.labels[config.dataPointIndex];
                                    onClickCallback(statusMap[selectedLabel]);
                                }
                            }
                        },
                        colors: colors,
                        // --- CAMBIO CLAVE (Leyenda más compacta) ---
                        legend: {
                            position: 'bottom',
                            horizontalAlign: 'center',
                            fontSize: '12px', // Letra más pequeña
                            itemMargin: {
                                horizontal: 5, // Menos espacio
                                vertical: 0
                            },
                            markers: {
                                width: 10, // Marcadores más pequeños
                                height: 10
                            }
                        },
                        dataLabels: { formatter: (val, opts) => opts.w.config.series[opts.seriesIndex] }
                    };
                    this.chart = new ApexCharts(this.$refs.chart, options);
                    this.chart.render();

                    // Observar cambios en los datos y actualizar el gráfico
                    this.$watch('biHealthData', (newData) => {
                        this.chart.updateSeries(newData.series);
                    });
                }
            }
        }

        // --- COMPONENTE DE GRÁFICO DE BARRAS (REUTILIZABLE) ---
        // (Sin cambios, ya que no tiene leyenda que corregir)
        function chartBar(data, title, colors) {
            return {
                chart: null,
                init() {
                    const options = {
                        series: [{ name: 'Tareas', data: data.series }],
                        chart: { type: 'bar', height: '100%', toolbar: { show: false } },
                        xaxis: { categories: data.labels },
                        colors: colors,
                        plotOptions: { bar: { horizontal: false, columnWidth: '55%', distributed: true } },
                        dataLabels: { enabled: true },
                        legend: { show: false }
                    };
                    this.chart = new ApexCharts(this.$refs.chart, options);
                    this.chart.render();

                    // Observar cambios en los datos y actualizar el gráfico
                    this.$watch('biTaskData', (newData) => {
                        this.chart.updateSeries([{ data: newData.series }]);
                    });
                }
            }
        }

        // --- COMPONENTE PRINCIPAL (EL CEREBRO) ---
        function projectReviewManager() {
            return {
                // --- Filtros ---
                search: '',
                filterLeader: 'all',
                filterArea: 'all',
                filterHealth: 'all',       // Filtro de BI
                filterWorkloadUser: 'all', // Filtro de BI

                // --- Datos Crudos ---
                allProjects: [],
                statuses: [],
                leaders: [],
                areas: [],
                
                /**
                 * Carga los datos de las "islas" JSON.
                 */
                initData() {
                    this.allProjects = JSON.parse(document.getElementById('projects-data').textContent || '[]');
                    this.statuses = JSON.parse(document.getElementById('statuses-data').textContent || '[]');
                    this.leaders = JSON.parse(document.getElementById('leaders-data').textContent || '[]');
                    this.areas = JSON.parse(document.getElementById('areas-data').textContent || '[]');
                },

                /**
                 * Limpia todos los filtros a su estado inicial.
                 */
                clearFilters() {
                    this.search = '';
                    this.filterLeader = 'all';
                    this.filterArea = 'all';
                    this.filterHealth = 'all';
                    this.filterWorkloadUser = 'all';
                },
                
                /**
                 * Funciones de callback para los filtros de BI.
                 */
                setHealthFilter(status) {
                    this.filterHealth = (this.filterHealth === status) ? 'all' : status;
                },
                setWorkloadFilter(userId) {
                    this.filterWorkloadUser = (this.filterWorkloadUser === userId) ? 'all' : userId;
                },

                /**
                 * Helper para calcular la salud de un proyecto.
                 */
                getProjectHealth(project) {
                    if (!project.due_date) {
                        return { status: 'on-track', text: 'En Tiempo', color: 'text-green-500' };
                    }
                    const daysLeft = Math.round((new Date(project.due_date) - new Date()) / (1000 * 60 * 60 * 24));
                    if (daysLeft < 0) {
                        return { status: 'overdue', text: 'Vencido', color: 'text-red-500' };
                    }
                    if (daysLeft <= 7) {
                        return { status: 'at-risk', text: 'En Riesgo', color: 'text-yellow-500' };
                    }
                    return { status: 'on-track', text: 'En Tiempo', color: 'text-green-500' };
                },

                // --- GETTERS COMPUTADOS (EL MOTOR DE BI) ---

                /**
                 * [COMPUTADO] La lista de proyectos que se muestra, basada en todos los filtros.
                 */
                get filteredProjects() {
                    return this.allProjects.filter(project => {
                        // Filtros estándar
                        const searchMatch = project.name.toLowerCase().includes(this.search.toLowerCase()) ||
                                            (project.leader && project.leader.name.toLowerCase().includes(this.search.toLowerCase()));
                        const leaderMatch = this.filterLeader === 'all' || project.leader_id == this.filterLeader;
                        const areaMatch = this.filterArea === 'all' || project.areas.some(area => area.id == this.filterArea);
                        
                        // Filtros de BI
                        const healthMatch = this.filterHealth === 'all' || this.getProjectHealth(project).status === this.filterHealth;
                        const workloadMatch = this.filterWorkloadUser === 'all' || project.tasks.some(t => t.assignee_id == this.filterWorkloadUser);

                        return searchMatch && leaderMatch && areaMatch && healthMatch && workloadMatch;
                    });
                },

                /**
                 * [BI COMPUTADO] Datos para el gráfico de Salud.
                 */
                get biHealthData() {
                    let onTrack = 0;
                    let atRisk = 0;
                    let overdue = 0;
                    this.filteredProjects.forEach(p => {
                        const health = this.getProjectHealth(p).status;
                        if (health === 'overdue') overdue++;
                        else if (health === 'at-risk') atRisk++;
                        else onTrack++;
                    });
                    return {
                        series: [onTrack, atRisk, overdue],
                        labels: ['En Tiempo', 'En Riesgo', 'Vencido']
                    };
                },

                /**
                 * [BI COMPUTADO] Datos para el gráfico de Tareas.
                 */
                get biTaskData() {
                    let pending = 0;
                    let inProgress = 0;
                    let completed = 0;
                    this.filteredProjects.forEach(p => {
                        p.tasks.forEach(t => {
                            if (t.status === 'Completada') completed++;
                            else if (t.status === 'En Progreso') inProgress++;
                            else pending++;
                        });
                    });
                    return {
                        series: [pending, inProgress, completed],
                        labels: ['Pendiente', 'En Progreso', 'Completada']
                    };
                },

                /**
                 * [BI COMPUTADO] Datos para la lista de Carga de Trabajo.
                 */
                get biWorkloadData() {
                    const workload = new Map();
                    this.filteredProjects.forEach(p => {
                        p.tasks.forEach(t => {
                            if (t.assignee && t.status !== 'Completada') {
                                if (!workload.has(t.assignee_id)) {
                                    workload.set(t.assignee_id, {
                                        id: t.assignee.id,
                                        name: t.assignee.name,
                                        initials: t.assignee.name.split(' ').slice(0, 2).map(n => n[0]).join(''),
                                        tasks_count: 0
                                    });
                                }
                                workload.get(t.assignee_id).tasks_count++;
                            }
                        });
                    });
                    // Convertir el Map a un Array, ordenar por conteo y devolver
                    return Array.from(workload.values()).sort((a, b) => b.tasks_count - a.tasks_count);
                }
            }
        }
        
        // Registra los componentes con Alpine
        document.addEventListener('alpine:init', () => {
            Alpine.data('projectReviewManager', projectReviewManager);
            Alpine.data('chartDonut', chartDonut);
            Alpine.data('chartBar', chartBar);
        });
    </script>

    <style>
        .card { background-color: #ffffff; }
    </style>
</x-app-layout>