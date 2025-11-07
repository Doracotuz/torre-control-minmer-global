<x-app-layout>
    <x-slot name="header">
    </x-slot>    
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
            background: linear-gradient(to right, rgba(255,255,255,0.05) 4%, rgba(255,255,255,0.1) 25%, rgba(255,255,255,0.05) 36%);
            background-size: 1000px 100%;
        }

        .glass-widget {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .glass-widget:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.1), 0 0 25px rgba(44, 56, 86, 0.1);
        }
        
        .gradient-background {
            background-color: #E8ECF7;
            background-image: 
                radial-gradient(at 10% 20%, hsla(216, 65%, 95%, 1) 0px, transparent 50%),
                radial-gradient(at 80% 10%, hsla(38, 100%, 95%, 1) 0px, transparent 50%),
                radial-gradient(at 80% 80%, hsla(210, 65%, 90%, 1) 0px, transparent 50%);
        }
    </style>

    <div class="py-12 gradient-background min-h-screen rounded-2xl">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-8 px-4 sm:px-0 animate-fade-in-up" style="animation-delay: 0.1s;">
                <h3 class="text-3xl font-bold text-[#2c3856]" style="font-family: 'Raleway', sans-serif;" id="dynamic-greeting">
                    ¡Hola, {{ Auth::user()->name }}!
                </h3>
                <p class="mt-1 text-lg text-gray-600">
                    Bienvenido de vuelta a la Torre de Control.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-1 space-y-6">
                    <div class="glass-widget rounded-2xl shadow-lg p-6 animate-fade-in-up" style="animation-delay: 0.2s;">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Total de Usuarios') }}</p>
                                <div id="totalUsers-container">
                                    <div class="skeleton-bar h-9 w-20 mt-1 rounded-md"></div>
                                </div>
                            </div>
                            <div class="p-3 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 text-blue-700">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h-5v-2a3 3 0 013-3h2a3 3 0 013 3v2h-5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 12a3 3 0 10-6 0 3 3 0 006 0z"></path></svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="glass-widget rounded-2xl shadow-lg p-6 animate-fade-in-up" style="animation-delay: 0.3s;">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Total de Áreas') }}</p>
                                <div id="totalAreas-container">
                                    <div class="skeleton-bar h-9 w-20 mt-1 rounded-md"></div>
                                </div>
                            </div>
                            <div class="p-3 rounded-full bg-gradient-to-br from-green-100 to-green-200 text-green-700">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M18 10h4M2 10h4M12 2v4M9 20h6a2 2 0 002-2V6a2 2 0 00-2-2H9a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="glass-widget rounded-2xl shadow-lg p-6 animate-fade-in-up" style="animation-delay: 0.4s;">
                        <h4 class="text-lg font-semibold text-[#2c3856] mb-4">Actividad Reciente</h4>
                        <ul id="recent-activity-list" class="space-y-4">
                            <li class="flex items-center space-x-3">
                                <div class="skeleton-bar rounded-full w-10 h-10"></div>
                                <div class="flex-1 space-y-2">
                                    <div class="skeleton-bar h-4 w-3/4 rounded-md"></div>
                                    <div class="skeleton-bar h-3 w-1/2 rounded-md"></div>
                                </div>
                            </li>
                            <li class="flex items-center space-x-3">
                                <div class="skeleton-bar rounded-full w-10 h-10"></div>
                                <div class="flex-1 space-y-2">
                                    <div class="skeleton-bar h-4 w-3/4 rounded-md"></div>
                                    <div class="skeleton-bar h-3 w-1/2 rounded-md"></div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="glass-widget rounded-2xl shadow-lg p-6 animate-fade-in-up" style="animation-delay: 0.3s;">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('Total de Carpetas') }}</p>
                                    <div id="totalFolders-container">
                                        <div class="skeleton-bar h-9 w-20 mt-1 rounded-md"></div>
                                    </div>
                                </div>
                                <div class="p-3 rounded-full bg-gradient-to-br from-yellow-100 to-yellow-200 text-yellow-700">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                                </div>
                            </div>
                        </div>
                        <div class="glass-widget rounded-2xl shadow-lg p-6 animate-fade-in-up" style="animation-delay: 0.4s;">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ __('Total de Archivos/Enlaces') }}</p>
                                    <div id="totalFileLinks-container">
                                        <div class="skeleton-bar h-9 w-20 mt-1 rounded-md"></div>
                                    </div>
                                </div>
                                <div class="p-3 rounded-full bg-gradient-to-br from-red-100 to-red-200 text-red-700">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-3-3v6m-5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="glass-widget rounded-2xl shadow-lg p-6 animate-fade-in-up" style="animation-delay: 0.5s;">
                        <h4 class="text-lg font-semibold text-[#2c3856] mb-4">Peso de Carpetas por Área</h4>
                        <div id="foldersByAreaChart">
                            <div class="skeleton-bar h-72 w-full rounded-md"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
                <div class="glass-widget rounded-2xl shadow-lg p-6 animate-fade-in-up" style="animation-delay: 0.6s;">
                    <h4 class="text-lg font-semibold text-[#2c3856] mb-4">Distribución de Tipos de Usuario</h4>
                    <div id="userTypeChart" class="flex justify-center items-center">
                        <div class="skeleton-bar h-64 w-64 rounded-full"></div>
                    </div>
                </div>
                
                <div class="glass-widget rounded-2xl shadow-lg p-6 animate-fade-in-up" style="animation-delay: 0.7s;">
                    <h4 class="text-lg font-semibold text-[#2c3856] mb-4">Top 5 Tipos de Archivo (Últ. 30 días)</h4>
                    <div id="fileTypesChart" class="flex justify-center items-center">
                        <div class="skeleton-bar h-64 w-64 rounded-full"></div>
                    </div>
                </div>

                <div class="space-y-6">
                    <a href="{{ route('folders.index') }}" 
                       class="group glass-widget rounded-2xl shadow-lg p-6 flex items-center space-x-5 animate-fade-in-up" style="animation-delay: 0.8s;">
                        <div class="p-4 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 text-blue-700 transition-all duration-300 group-hover:from-blue-700 group-hover:to-blue-500 group-hover:text-white">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-[#2c3856] group-hover:text-blue-700">Mis Archivos</h4>
                            <p class="text-sm text-gray-600">Explorar carpetas y documentos.</p>
                        </div>
                    </a>
                    
                    @if(Auth::user()->isSuperAdmin())
                        <a href="{{ route('admin.dashboard') }}" 
                           class="group glass-widget rounded-2xl shadow-lg p-6 flex items-center space-x-5 animate-fade-in-up" style="animation-delay: 0.9s;">
                            <div class="p-4 rounded-full bg-gradient-to-br from-orange-100 to-orange-200 text-[#ff9c00] transition-all duration-300 group-hover:from-[#ff9c00] group-hover:to-[#f2b04c] group-hover:text-white">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-[#2c3856] group-hover:text-orange-600">Panel de Admin</h4>
                                <p class="text-sm text-gray-600">Gestionar usuarios y áreas.</p>
                            </div>
                        </a>
                    @endif
                </div>
            </div>

            @if (Auth::user()->isSuperAdmin())
                <div class="glass-widget rounded-2xl shadow-lg p-6 mt-6 animate-fade-in-up" style="animation-delay: 1.0s;">
                    <h4 class="text-lg font-semibold text-[#2c3856] mb-4">{{ __('Usuarios por Área (Administración General)') }}</h4>
                    <div id="usersByAreaChart">
                        <div class="skeleton-bar h-64 w-full rounded-md"></div>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            const colorPrimary = '#2c3856';
            const colorSecondary = '#ff9c00';
            const colorGray = '#6c757d';
            const fontFamily = 'Raleway, sans-serif';
            const chartColors = [colorPrimary, colorSecondary, '#667eb8', '#f5c07b', colorGray];

            const greetingEl = document.getElementById('dynamic-greeting');
            if (greetingEl) {
                const hour = new Date().getHours();
                if (hour < 12) {
                    greetingEl.textContent = `Buenos días, {{ Auth::user()->name }}!`;
                } else if (hour < 18) {
                    greetingEl.textContent = `Buenas tardes, {{ Auth::user()->name }}!`;
                } else {
                    greetingEl.textContent = `Buenas noches, {{ Auth::user()->name }}!`;
                }
            }
            
            function populateRecentActivity(activities) {
                const list = document.getElementById('recent-activity-list');
                if (!list) return;
                
                list.innerHTML = '';
                
                if (!activities || activities.length === 0) {
                    list.innerHTML = '<p class="text-sm text-gray-500">No hay actividad reciente.</p>';
                    return;
                }

                activities.forEach(activity => {
                    const userName = activity.user ? activity.user.name : 'Usuario Desconocido';
                    const relativeTime = timeAgo(activity.created_at);
                    
                    const li = document.createElement('li');
                    li.className = 'flex items-center space-x-3';
                    li.innerHTML = `
                        <div class="p-2 rounded-full bg-gray-100 border border-gray-200">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-800">${activity.action}</p>
                            <p class="text-xs text-gray-500">por ${userName} (${relativeTime})</p>
                        </div>
                    `;
                    list.appendChild(li);
                });
            }

            function timeAgo(dateString) {
                const date = new Date(dateString);
                const now = new Date();
                const seconds = Math.floor((now - date) / 1000);
                let interval = seconds / 31536000;
                if (interval > 1) return `hace ${Math.floor(interval)} años`;
                interval = seconds / 2592000;
                if (interval > 1) return `hace ${Math.floor(interval)} meses`;
                interval = seconds / 86400;
                if (interval > 1) return `hace ${Math.floor(interval)} días`;
                interval = seconds / 3600;
                if (interval > 1) return `hace ${Math.floor(interval)} horas`;
                interval = seconds / 60;
                if (interval > 1) return `hace ${Math.floor(interval)} minutos`;
                return `hace ${Math.floor(seconds)} segundos`;
            }

            async function fetchDashboardData() {
                try {
                    const response = await fetch('{{ route("admin.main_dashboard.data") }}');
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    const data = await response.json();
                    
                    if (!data.foldersByArea || !Array.isArray(data.foldersByArea)) {
                        data.foldersByArea = [];
                    }
                    if (!data.usersByArea || !Array.isArray(data.usersByArea)) {
                        data.usersByArea = [];
                    }
                    if (!data.fileTypes || typeof data.fileTypes !== 'object') {
                        data.fileTypes = {};
                    }
                     if (!data.userTypeData || typeof data.userTypeData !== 'object') {
                        data.userTypeData = {};
                    }
                    
                    return data;
                    
                } catch (error) {
                    console.error('Error fetching dashboard data:', error);
                    document.getElementById('totalUsers-container').innerHTML = '<p class="text-red-500">Error</p>';
                    document.getElementById('totalAreas-container').innerHTML = '<p class="text-red-500">Error</p>';
                    document.getElementById('totalFolders-container').innerHTML = '<p class="text-red-500">Error</p>';
                    document.getElementById('totalFileLinks-container').innerHTML = '<p class="text-red-500">Error</p>';
                    return null;
                }
            }

            async function renderDashboard() {
                const data = await fetchDashboardData();
                if (!data) {
                    console.log('No data received, stopping dashboard rendering.');
                    return;
                }

                document.getElementById('totalUsers-container').innerHTML = `<p class="text-3xl font-bold text-[#2c3856] mt-1">${data.totalUsers}</p>`;
                document.getElementById('totalAreas-container').innerHTML = `<p class="text-3xl font-bold text-[#2c3856] mt-1">${data.totalAreas}</p>`;
                document.getElementById('totalFolders-container').innerHTML = `<p class="text-3xl font-bold text-[#2c3856] mt-1">${data.totalFolders}</p>`;
                document.getElementById('totalFileLinks-container').innerHTML = `<p class="text-3xl font-bold text-[#2c3856] mt-1">${data.totalFileLinks}</p>`;
                
                populateRecentActivity(data.recentActivities);

                const chartFoldersEl = document.querySelector("#foldersByAreaChart");
                if (chartFoldersEl && data.foldersByArea.length > 0) {
                    chartFoldersEl.innerHTML = '';
                    new ApexCharts(chartFoldersEl, {
                        series: [{ data: data.foldersByArea }],
                        chart: { type: 'treemap', height: 350, fontFamily: fontFamily, toolbar: { show: false } },
                        colors: chartColors,
                        plotOptions: { treemap: { distributed: true, enableShades: false } },
                        tooltip: { theme: 'dark', y: { formatter: (val) => val + " carpetas" } }
                    }).render();
                } else if (chartFoldersEl) {
                    chartFoldersEl.innerHTML = '<p class="text-center text-gray-500 h-72 flex items-center justify-center">No hay datos de carpetas para mostrar.</p>';
                }

                const chartUserTypeEl = document.querySelector("#userTypeChart");
                if (chartUserTypeEl && Object.keys(data.userTypeData).length > 0) {
                    chartUserTypeEl.innerHTML = '';
                    new ApexCharts(chartUserTypeEl, {
                        series: Object.values(data.userTypeData),
                        chart: { type: 'radialBar', height: 350, fontFamily: fontFamily },
                        plotOptions: {
                            radialBar: {
                                offsetY: -10,
                                dataLabels: {
                                    name: { fontSize: '16px', fontFamily: fontFamily },
                                    value: { fontSize: '14px', fontFamily: fontFamily },
                                    total: {
                                        show: true,
                                        label: 'Total Usuarios',
                                        color: colorPrimary,
                                        formatter: () => data.totalUsers
                                    }
                                }
                            }
                        },
                        labels: Object.keys(data.userTypeData),
                        colors: chartColors,
                        legend: { position: 'bottom', fontFamily: fontFamily, labels: { colors: colorGray } }
                    }).render();
                } else if (chartUserTypeEl) {
                    chartUserTypeEl.innerHTML = '<p class="text-center text-gray-500 h-64 flex items-center justify-center">No hay datos de usuarios para mostrar.</p>';
                }

                const chartFileTypesEl = document.querySelector("#fileTypesChart");
                if (chartFileTypesEl && Object.keys(data.fileTypes).length > 0) {
                    chartFileTypesEl.innerHTML = '';
                    new ApexCharts(chartFileTypesEl, {
                        series: Object.values(data.fileTypes),
                        chart: { type: 'donut', height: 350, fontFamily: fontFamily },
                        labels: Object.keys(data.fileTypes).map(ext => ext.toUpperCase()),
                        colors: chartColors,
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '60%',
                                    labels: {
                                        show: true,
                                        total: {
                                            show: true,
                                            label: 'Total Archivos',
                                            color: colorPrimary,
                                            formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                        }
                                    }
                                }
                            }
                        },
                        legend: { position: 'bottom', fontFamily: fontFamily, labels: { colors: colorGray } },
                        dataLabels: { enabled: false },
                        tooltip: { theme: 'dark', y: { formatter: (val) => val + " archivos" } }
                    }).render();
                } else if (chartFileTypesEl) {
                    chartFileTypesEl.innerHTML = '<p class="text-center text-gray-500 h-64 flex items-center justify-center">No hay datos de archivos para mostrar.</p>';
                }

                @if (Auth::user()->isSuperAdmin())
                    const chartUsersByAreaEl = document.querySelector("#usersByAreaChart");
                    if (chartUsersByAreaEl && data.usersByArea.length > 0) {
                        chartUsersByAreaEl.innerHTML = '';
                        new ApexCharts(chartUsersByAreaEl, {
                            series: [{
                                name: 'Usuarios',
                                data: data.usersByArea.map(item => item.count)
                            }],
                            chart: { type: 'bar', height: 350, toolbar: { show: false }, fontFamily: fontFamily },
                            plotOptions: { bar: { columnWidth: '50%', borderRadius: 6 } },
                            dataLabels: { enabled: false },
                            xaxis: {
                                categories: data.usersByArea.map(item => item.name),
                                labels: { style: { colors: colorGray, fontFamily: fontFamily } },
                            },
                            yaxis: {
                                title: { text: 'Número de Usuarios', style: { color: colorPrimary, fontFamily: fontFamily } },
                                labels: { style: { colors: colorGray, fontFamily: fontFamily } },
                            },
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shade: 'dark', type: "vertical", shadeIntensity: 0.5,
                                    gradientToColors: [colorPrimary, '#5A6F9A'],
                                    inverseColors: true, opacityFrom: 1, opacityTo: 1, stops: [0, 100]
                                }
                            },
                            tooltip: { theme: 'dark', y: { formatter: (val) => val + " usuarios" } },
                            grid: { borderColor: '#f1f1f1' },
                        }).render();
                    } else if (chartUsersByAreaEl) {
                        chartUsersByAreaEl.innerHTML = '<p class="text-center text-gray-500 h-64 flex items-center justify-center">No hay datos de usuarios para mostrar.</p>';
                    }
                @endif
            }

            renderDashboard();
        });
    </script>
</x-app-layout>