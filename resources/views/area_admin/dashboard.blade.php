<x-app-layout>
    <style>
        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up {
            animation: fade-in-up 0.6s ease-out forwards;
            opacity: 0;
        }
        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }
        .skeleton-bar {
            animation: shimmer 2s infinite linear;
            background: linear-gradient(to right, #e0e0e0 4%, #f0f0f0 25%, #e0e0e0 36%);
            background-size: 1000px 100%;
            border-radius: 0.375rem;
        }
    </style>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Panel de Control de Área
            </h2>

            @php
                $user = Auth::user();
                $manageableAreas = $user->accessibleAreas;
                if (!$manageableAreas->contains('id', $user->area_id)) {
                    $manageableAreas->prepend($user->area);
                }
                $currentAreaId = session('current_admin_area_id', $user->area_id);
            @endphp
            
            @if($user->is_area_admin && $manageableAreas->count() > 1) {{-- --}}
                <form method="POST" action="{{ route('area_admin.switch_area') }}" class="mt-4 md:mt-0">
                    @csrf
                    <label for="area_id" class="sr-only">Gestionar Área:</label>
                    <select name="area_id" id="area_id" onchange="this.form.submit()"
                            class="block w-full md:w-auto pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-[#ff9c00] focus:border-[#ff9c00] sm:text-sm rounded-md shadow-sm">
                        @foreach($manageableAreas as $area)
                            <option value="{{ $area->id }}" @selected($area->id == $currentAreaId)>
                                Gestionar: {{ $area->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-12 bg-[#E8ECF7] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-8 px-4 sm:px-0">
                <h3 class="text-3xl font-bold text-[#2c3856]" style="font-family: 'Raleway', sans-serif;">
                    <span id="welcome-title">Panel de</span> <span id="area-name-title" class="text-[#ff9c00]">...</span>
                </h3>
                <p class="mt-1 text-lg text-gray-600">
                    Bienvenido, {{ Auth::user()->name }}.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="lg:col-span-2 space-y-6">
                    
                    @if(Auth::user()->is_area_admin) {{-- --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <a href="{{ route('area_admin.users.index') }}" 
                           class="group bg-white rounded-xl shadow-lg p-6 flex items-center space-x-5 transition-all duration-300 ease-in-out hover:shadow-2xl hover:-translate-y-1 animate-fade-in-up" style="animation-delay: 0.1s;">
                            <div class="p-4 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 text-blue-700 transition-all duration-300 group-hover:from-blue-700 group-hover:to-blue-500 group-hover:text-white">
                                <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m-7.5-2.962a3.75 3.75 0 1 0-7.5 0 3.75 3.75 0 0 0 7.5 0ZM10.5 1.5a9 9 0 1 0 0 18 9 9 0 0 0 0-18Z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-[#2c3856] group-hover:text-blue-700">
                                    Gestionar Usuarios
                                </h4>
                                <p class="text-sm text-gray-600">
                                    Añade, edita y gestiona tu equipo.
                                </p>
                            </div>
                        </a>
                        
                        <a href="{{ route('area_admin.folder_permissions.index') }}" 
                           class="group bg-white rounded-xl shadow-lg p-6 flex items-center space-x-5 transition-all duration-300 ease-in-out hover:shadow-2xl hover:-translate-y-1 animate-fade-in-up" style="animation-delay: 0.2s;">
                            <div class="p-4 rounded-full bg-gradient-to-br from-orange-100 to-orange-200 text-[#ff9c00] transition-all duration-300 group-hover:from-[#ff9c00] group-hover:to-[#f2b04c] group-hover:text-white">
                                <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.286Z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-[#2c3856] group-hover:text-orange-600">
                                    Gestionar Permisos
                                </h4>
                                <p class="text-sm text-gray-600">
                                    Define el acceso a las carpetas.
                                </p>
                            </div>
                        </a>
                    </div>
                    @endif

                    <div classs="bg-white rounded-xl shadow-lg p-6 animate-fade-in-up" style="animation-delay: 0.3s;">
                        <h4 class="text-xl font-semibold text-[#2c3856] mb-4" id="activity-breakdown-title">Desglose de Actividad (Últ. 30 días)</h4>
                        <div id="activity-breakdown-chart" class="flex justify-center items-center" style="min-height: 300px;">
                            <div class="skeleton-bar h-64 w-64 rounded-full"></div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-lg p-6 animate-fade-in-up" style="animation-delay: 0.4s;">
                        <h4 class="text-xl font-semibold text-[#2c3856] mb-4" id="activity-feed-title">Actividad Reciente</h4>
                        <ul id="recent-activity-list" class="space-y-4">
                            <li id="activity-loader"> <div class="flex items-center space-x-3"><div class="skeleton-bar rounded-full w-10 h-10"></div><div class="flex-1 space-y-2"><div class="skeleton-bar h-4 w-3/4"></div><div class="skeleton-bar h-3 w-1/2"></div></div></div>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="lg:col-span-1 space-y-6">
                    
                    <div class="bg-white rounded-xl shadow-lg p-6 animate-fade-in-up" style="animation-delay: 0.1s;">
                        <h4 class="text-xl font-semibold text-[#2c3856] mb-4" id="kpi-title">Métricas</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center" id="userCount-wrapper"> <div id="userCount-container"><div class="skeleton-bar h-8 w-16 mx-auto mb-1"></div></div>
                                <p class="text-sm font-medium text-gray-500">Usuarios</p>
                            </div>
                            <div class="text-center">
                                <div id="folderCount-container"><div class="skeleton-bar h-8 w-16 mx-auto mb-1"></div></div>
                                <p class="text-sm font-medium text-gray-500">Carpetas</p>
                            </div>
                            <div class="text-center">
                                <div id="fileCount-container"><div class="skeleton-bar h-8 w-16 mx-auto mb-1"></div></div>
                                <p class="text-sm font-medium text-gray-500">Archivos</p>
                            </div>
                            <div class="text-center">
                                <div id="linkCount-container"><div class="skeleton-bar h-8 w-16 mx-auto mb-1"></div></div>
                                <p class="text-sm font-medium text-gray-500">Enlaces</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 animate-fade-in-up" style="animation-delay: 0.2s;">
                        <h4 class="text-xl font-semibold text-[#2c3856] mb-4" id="file-types-title">Tipos de Archivo</h4>
                        <div id="file-types-chart" class="flex justify-center items-center" style="min-height: 250px;">
                            <div class="skeleton-bar h-48 w-48 rounded-full"></div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-lg p-6 animate-fade-in-up" style="animation-delay: 0.3s;">
                        <h4 class="text-xl font-semibold text-[#2c3856] mb-4">Miembros del Equipo</h4>
                        <ul id="team-members-list" class="space-y-3 max-h-96 overflow-y-auto">
                            <li id="team-loader"> <div class="flex items-center space-x-3"><div class="skeleton-bar rounded-full w-10 h-10"></div><div class="flex-1 space-y-2"><div class="skeleton-bar h-4 w-3/4"></div><div class="skeleton-bar h-3 w-1/2"></div></div></div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            const colorPrimary = '#2c3856';
            const colorSecondary = '#ff9c00';
            const colorPalette = [colorPrimary, colorSecondary, '#3e4e7a', '#f2b04c', '#5a6f9a'];
            const fontFamily = 'Raleway, sans-serif';
            const areaTitle = document.getElementById('area-name-title');
            const welcomeTitle = document.getElementById('welcome-title');
            const kpiTitle = document.getElementById('kpi-title');
            const userCountEl = document.getElementById('userCount-container');
            const userCountWrapper = document.getElementById('userCount-wrapper');
            const folderCountEl = document.getElementById('folderCount-container');
            const fileCountEl = document.getElementById('fileCount-container');
            const linkCountEl = document.getElementById('linkCount-container');
            const activityList = document.getElementById('recent-activity-list');
            const teamList = document.getElementById('team-members-list');
            const activityChartEl = document.getElementById('activity-breakdown-chart');
            const fileTypesChartEl = document.getElementById('file-types-chart');
            const activityFeedTitle = document.getElementById('activity-feed-title');
            const activityBreakdownTitle = document.getElementById('activity-breakdown-title');
            const fileTypesTitle = document.getElementById('file-types-title');

            function timeAgo(dateString) {
                const date = new Date(dateString);
                const now = new Date();
                const seconds = Math.floor((now - date) / 1000);
                let interval = seconds / 3600;
                if (interval > 24) return `hace ${Math.floor(interval / 24)} días`;
                if (interval > 1) return `hace ${Math.floor(interval)} horas`;
                interval = seconds / 60;
                if (interval > 1) return `hace ${Math.floor(interval)} minutos`;
                return `hace ${Math.floor(seconds)} segundos`;
            }

            function getActivityIcon(action) {
                action = (action || '').toLowerCase();
                if (action.includes('subió') || action.includes('creó') || action.includes('creaciones')) {
                    return `<svg class="w-5 h-5 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>`;
                }
                if (action.includes('eliminó') || action.includes('eliminaciones')) {
                    return `<svg class="w-5 h-5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12.54 0c-.27.041-.54.082-.811.124m-1.022-.165L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12.54 0c-.27.041-.54.082-.811.124" /></svg>`;
                }
                if (action.includes('descargó') || action.includes('descargas')) {
                    return `<svg class="w-5 h-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>`;
                }
                return `<svg class="w-5 h-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /></svg>`;
            }
            
            function populateKPIs(data) {
                areaTitle.textContent = data.areaName || 'No Asignada';
                userCountEl.innerHTML = `<p class="text-3xl font-bold text-[#2c3856]">${data.userCount}</p>`;
                folderCountEl.innerHTML = `<p class="text-3xl font-bold text-[#2c3856]">${data.folderCount}</p>`;
                fileCountEl.innerHTML = `<p class="text-3xl font-bold text-[#2c3856]">${data.fileCount}</p>`;
                linkCountEl.innerHTML = `<p class="text-3xl font-bold text-[#2c3856]">${data.linkCount}</p>`;
                
                if (data.isAreaAdmin) {
                    welcomeTitle.textContent = 'Panel de Gestión:';
                    kpiTitle.textContent = 'Métricas del Área';
                    activityFeedTitle.textContent = 'Actividad Reciente del Área';
                    activityBreakdownTitle.textContent = 'Desglose de Actividad del Área';
                    fileTypesTitle.textContent = 'Tipos de Archivo del Área';
                } else {
                    welcomeTitle.textContent = 'Mi Panel de';
                    kpiTitle.textContent = 'Mis Métricas';
                    userCountWrapper.style.display = 'none';
                    activityFeedTitle.textContent = 'Mi Actividad Reciente';
                    activityBreakdownTitle.textContent = 'Desglose de Mi Actividad';
                    fileTypesTitle.textContent = 'Mis Tipos de Archivo';
                }
            }
            
            function populateRecentActivity(activities) {
                activityList.innerHTML = '';
                if (!activities || activities.length === 0) {
                    activityList.innerHTML = '<p class="text-sm text-gray-500">No hay actividad reciente.</p>';
                    return;
                }
                activities.forEach(activity => {
                    const icon = getActivityIcon(activity.action);
                    const userName = activity.user ? activity.user.name : 'Usuario Desconocido';
                    const relativeTime = timeAgo(activity.created_at);
                    activityList.innerHTML += `
                        <li class="flex items-center space-x-3">
                            <div class="bg-gray-100 p-2 rounded-full">${icon}</div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">${activity.action}</p>
                                <p class="text-xs text-gray-500">por ${userName} (${relativeTime})</p>
                            </div>
                        </li>
                    `;
                });
            }

            function populateTeamList(members) {
                teamList.innerHTML = '';
                if (!members || members.length === 0) {
                    teamList.innerHTML = '<p class="text-sm text-gray-500">No hay miembros en esta área.</p>';
                    return;
                }
                members.forEach(member => {
                    const profilePhoto = member.profile_photo_path 
                        ? `<img class="h-10 w-10 rounded-full object-cover" src="${member.profile_photo_path}" alt="${member.name}">`
                        : `<span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-gray-500">
                             <span class="font-medium leading-none text-white">${member.name.charAt(0)}</span>
                           </span>`; 
                    
                    teamList.innerHTML += `
                        <li class="flex items-center space-x-3">
                            <div>${profilePhoto}</div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-[#2c3856] truncate">${member.name}</p>
                                <p class="text-xs text-gray-500 truncate">${member.position || member.email}</p>
                            </div>
                        </li>
                    `;
                });
            }
            
            function renderActivityChart(breakdown) {
                if (!activityChartEl || !breakdown || breakdown.length === 0) {
                    activityChartEl.innerHTML = '<p class="text-center text-gray-500 h-64 flex items-center justify-center">No hay datos de actividad para mostrar.</p>';
                    return;
                }
                activityChartEl.innerHTML = '';
                new ApexCharts(activityChartEl, {
                    series: breakdown.map(item => item.total),
                    chart: { type: 'donut', height: 300, fontFamily: fontFamily },
                    labels: breakdown.map(item => item.action_type),
                    colors: colorPalette,
                    plotOptions: { pie: { donut: { size: '60%', labels: { show: true, total: { show: true, label: 'Total Acciones', color: colorPrimary } } } } },
                    legend: { position: 'bottom', fontFamily: fontFamily, labels: { colors: '#6c757d' } },
                    dataLabels: { enabled: false },
                    tooltip: { theme: 'dark', y: { formatter: (val) => val + " acciones" } }
                }).render();
            }

            function renderFileTypesChart(fileTypes) {
                if (!fileTypesChartEl || !fileTypes || fileTypes.length === 0) {
                    fileTypesChartEl.innerHTML = '<p class="text-center text-gray-500 h-64 flex items-center justify-center">No hay archivos para mostrar.</p>';
                    return;
                }
                fileTypesChartEl.innerHTML = '';
                new ApexCharts(fileTypesChartEl, {
                    series: fileTypes.map(item => item.total),
                    chart: { type: 'donut', height: 250, fontFamily: fontFamily },
                    labels: fileTypes.map(item => item.extension.toUpperCase()),
                    colors: colorPalette,
                    plotOptions: { pie: { donut: { size: '60%', labels: { show: true, total: { show: true, label: 'Total Archivos', color: colorPrimary } } } } },
                    legend: { position: 'bottom', fontFamily: fontFamily, labels: { colors: '#6c757d' } },
                    dataLabels: { enabled: false },
                    tooltip: { theme: 'dark', y: { formatter: (val) => val + " archivos" } }
                }).render();
            }

            async function renderDashboard() {
                try {
                    const response = await fetch('{{ route("area_admin.dashboard.data") }}');
                    if (!response.ok) throw new Error('Error al cargar los datos del dashboard.');
                    
                    const data = await response.json();
                    if (!data) throw new Error('Los datos recibidos están vacíos.');
                    
                    populateKPIs(data);
                    populateRecentActivity(data.recentActivities);
                    populateTeamList(data.teamMembers);
                    renderActivityChart(data.activityBreakdown);
                    renderFileTypesChart(data.fileTypes);
                    
                } catch (error) {
                    console.error(error);
                    areaTitle.textContent = 'Error';
                    activityList.innerHTML = '<p class="text-sm text-red-500">No se pudo cargar la actividad.</p>';
                    teamList.innerHTML = '<p class="text-sm text-red-500">No se pudo cargar el equipo.</p>';
                    activityChartEl.innerHTML = '<p class="text-center text-red-500 h-64 flex items-center justify-center">Error al cargar gráfico.</p>';
                    fileTypesChartEl.innerHTML = '<p class="text-center text-red-500 h-64 flex items-center justify-center">Error al cargar gráfico.</p>';
                }
            }

            renderDashboard();
        });
    </script>
</x-app-layout>