<x-app-layout>
    <x-slot name="header"></x-slot>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Raleway:wght@700;800;900&display=swap');

        :root {
            --minmer-navy: #2c3856;
            --minmer-orange: #ff9c00;
            --minmer-grey: #666666;
            --glass-bg: rgba(255, 255, 255, 0.75);
            --glass-border: rgba(255, 255, 255, 0.8);
            --card-shadow: 0 8px 32px 0 rgba(44, 56, 86, 0.08);
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f0f2f5;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, .brand-font {
            font-family: 'Raleway', sans-serif;
        }

        .bg-animated {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100vh;
            background: radial-gradient(circle at 10% 20%, rgba(44, 56, 86, 0.05) 0%, transparent 50%),
                        radial-gradient(circle at 90% 80%, rgba(255, 156, 0, 0.05) 0%, transparent 50%);
            z-index: -1;
            pointer-events: none;
        }

        .bento-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: var(--card-shadow);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .bento-card:hover {
            transform: translateY(-5px) scale(1.01);
            box-shadow: 0 20px 40px -10px rgba(44, 56, 86, 0.15);
            border-color: var(--minmer-orange);
            z-index: 10;
        }

        .stat-icon {
            width: 50px; height: 50px;
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px;
            transition: transform 0.3s ease;
        }

        .bento-card:hover .stat-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .icon-navy { background: rgba(44, 56, 86, 0.1); color: var(--minmer-navy); }
        .icon-orange { background: rgba(255, 156, 0, 0.1); color: var(--minmer-orange); }
        .icon-purple { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
        .icon-blue { background: rgba(14, 165, 233, 0.1); color: #0ea5e9; }

        .nav-button {
            background: linear-gradient(135deg, var(--minmer-navy) 0%, #1a2236 100%);
            color: white;
            transition: all 0.3s ease;
        }
        .nav-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(44, 56, 86, 0.3);
        }
        .nav-button-orange {
            background: linear-gradient(135deg, var(--minmer-orange) 0%, #e68a00 100%);
        }
        .nav-button-purple {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }

        .activity-item {
            border-left: 2px solid #e2e8f0;
            padding-left: 1rem;
            position: relative;
            transition: all 0.3s;
        }
        .activity-item::before {
            content: '';
            position: absolute;
            left: -5px; top: 0;
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #cbd5e1;
            transition: background 0.3s;
        }
        .activity-item:hover { border-left-color: var(--minmer-orange); }
        .activity-item:hover::before { background: var(--minmer-orange); }

        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: var(--minmer-navy); }
    </style>

    <div class="bg-animated"></div>

    <div class="min-h-screen pb-12" x-data="areaDashboardData()">
        <div class="max-w-[1800px] mx-auto px-4 sm:px-6 lg:px-8 pt-8">
            
            <div class="flex flex-col md:flex-row justify-between items-end mb-10 opacity-0 animate-intro">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-3 py-1 bg-white border border-gray-200 rounded-full text-xs font-bold text-[#2c3856] uppercase tracking-wider">
                            <span id="welcome-subtitle">Panel de Control</span>
                        </span>
                        <div class="flex items-center gap-1.5 px-3 py-1 bg-white border border-gray-200 rounded-full">
                            <span class="w-2 h-2 rounded-full bg-[#ff9c00] animate-pulse"></span>
                            <span class="text-xs font-bold text-gray-500" id="area-name-display">Cargando...</span>
                        </div>
                    </div>
                    
                    <h1 class="text-4xl md:text-5xl tracking-tight mt-2 brand-font">
                        <span class="font-bold text-gray-400">Hola,</span>
                        <span class="font-black text-[#2c3856]">{{ Auth::user()->name }}</span>
                    </h1>
                </div>
                <div class="mt-4 md:mt-0 text-right hidden md:block">
                    <p class="text-4xl font-black text-[#2c3856] tracking-tight" x-text="time"></p>
                    <p class="text-sm font-bold text-[#ff9c00] uppercase tracking-widest" x-text="date"></p>
                </div>
            </div>

            <template x-if="loading">
                <div class="w-full h-96 flex flex-col items-center justify-center">
                    <div class="w-64">
                        <div class="h-1 w-full bg-gray-200 overflow-hidden rounded-full relative">
                            <div class="absolute h-full w-1/2 bg-[#ff9c00] animate-[loading_1.5s_infinite]"></div>
                        </div>
                        <p class="text-center text-sm font-bold text-[#2c3856] mt-4 animate-pulse">Sincronizando datos del área...</p>
                    </div>
                </div>
            </template>

            <div x-show="!loading" x-cloak class="space-y-6">

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bento-card p-6 flex items-center justify-between opacity-0 animate-stagger">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Total Carpetas</p>
                            <h3 class="text-3xl font-black text-[#2c3856]" x-text="data.folderCount">0</h3>
                        </div>
                        <div class="stat-icon icon-navy">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg>
                        </div>
                    </div>

                    <div class="bento-card p-6 flex items-center justify-between opacity-0 animate-stagger">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Total Archivos</p>
                            <h3 class="text-3xl font-black text-[#2c3856]" x-text="data.fileCount">0</h3>
                        </div>
                        <div class="stat-icon icon-orange">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                    </div>

                    <div class="bento-card p-6 flex items-center justify-between opacity-0 animate-stagger">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Total Enlaces</p>
                            <h3 class="text-3xl font-black text-[#2c3856]" x-text="data.linkCount">0</h3>
                        </div>
                        <div class="stat-icon icon-blue">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                        </div>
                    </div>

                    <template x-if="data.isAreaAdmin">
                        <div class="bento-card p-6 flex items-center justify-between opacity-0 animate-stagger">
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Usuarios</p>
                                <h3 class="text-3xl font-black text-[#2c3856]" x-text="data.userCount">0</h3>
                            </div>
                            <div class="stat-icon icon-purple">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    
                    <div class="lg:col-span-3 flex flex-col gap-6 opacity-0 animate-stagger">
                        <div class="bento-card p-6">
                            <h3 class="text-lg font-bold text-[#2c3856] mb-4">Acciones Rápidas</h3>
                            <div class="space-y-3">
                                <template x-if="data.isAreaAdmin">
                                    <div class="space-y-3">
                                        <a href="{{ route('area_admin.users.index') }}" class="nav-button w-full p-4 rounded-xl flex items-center justify-between group">
                                            <div class="flex items-center gap-3">
                                                <div class="p-2 bg-white/20 rounded-lg">
                                                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                                </div>
                                                <div class="text-left">
                                                    <p class="text-sm font-bold">Gestionar Usuarios</p>
                                                </div>
                                            </div>
                                            <svg class="w-4 h-4 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                        </a>

                                        <a href="{{ route('area_admin.folder_permissions.index') }}" class="nav-button nav-button-orange w-full p-4 rounded-xl flex items-center justify-between group">
                                            <div class="flex items-center gap-3">
                                                <div class="p-2 bg-white/20 rounded-lg">
                                                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                                </div>
                                                <div class="text-left">
                                                    <p class="text-sm font-bold">Permisos</p>
                                                </div>
                                            </div>
                                            <svg class="w-4 h-4 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                        </a>
                                        
                                        <div class="bg-purple-50 rounded-xl p-4 border border-purple-100 relative overflow-hidden group hover:border-purple-300 transition-colors">
                                            <div class="flex justify-between items-center mb-2">
                                                <h4 class="font-bold text-[#2c3856]">Deuda Stock</h4>
                                                <span class="bg-purple-200 text-purple-700 text-[10px] font-bold px-2 py-0.5 rounded-full">Status</span>
                                            </div>
                                            <h2 class="text-3xl font-black text-purple-600 mb-2" x-text="data.backorderCount || 0">0</h2>
                                            <p class="text-xs text-gray-500 mb-3">Pedidos pendientes</p>
                                            <a href="{{ route('ff.inventory.backorders') }}" class="text-xs font-bold text-purple-600 hover:text-purple-800 flex items-center gap-1">
                                                Ver Detalles <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                                            </a>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="!data.isAreaAdmin">
                                    <div class="space-y-3">
                                        <a href="{{ route('folders.index') }}" class="nav-button w-full p-4 rounded-xl flex items-center justify-between group">
                                            <div class="flex items-center gap-3">
                                                <div class="p-2 bg-white/20 rounded-lg">
                                                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z" /></svg>
                                                </div>
                                                <div class="text-left">
                                                    <p class="text-sm font-bold">Mis Archivos</p>
                                                </div>
                                            </div>
                                        </a>
                                        <a href="{{ route('profile.edit') }}" class="nav-button nav-button-purple w-full p-4 rounded-xl flex items-center justify-between group">
                                            <div class="flex items-center gap-3">
                                                <div class="p-2 bg-white/20 rounded-lg">
                                                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                </div>
                                                <div class="text-left">
                                                    <p class="text-sm font-bold">Mi Perfil</p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="bento-card p-6 flex-1 flex flex-col">
                            <h3 class="text-lg font-bold text-[#2c3856] mb-4">Equipo</h3>
                            <div class="flex-1 overflow-y-auto custom-scrollbar pr-2 space-y-4 max-h-[250px]" id="team-list-container">
                                
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-5 flex flex-col gap-6 opacity-0 animate-stagger">
                        <template x-if="data.isAreaAdmin">
                            <div class="bento-card p-6 h-full flex flex-col">
                                <div class="flex justify-between items-center mb-4 h-10 shrink-0">
                                    <h3 class="text-lg font-bold text-[#2c3856]">Desglose de Actividad</h3>
                                    <span class="text-xs text-gray-400 font-bold uppercase">Últimos 30 días</span>
                                </div>
                                <div id="chart-activity-breakdown" class="flex-grow w-full h-full min-h-[300px]"></div>
                            </div>
                        </template>
                        <template x-if="!data.isAreaAdmin">
                             <div class="bento-card p-6 h-full flex flex-col">
                                <h3 class="text-lg font-bold text-[#2c3856] mb-4">Archivos Recientes</h3>
                                <div class="space-y-3 overflow-y-auto custom-scrollbar max-h-[300px]" id="recent-files-container">
                                    </div>
                            </div>
                        </template>
                    </div>

                    <div class="lg:col-span-4 flex flex-col gap-6 opacity-0 animate-stagger">
                        <div class="bento-card p-6">
                            <h3 class="text-lg font-bold text-[#2c3856] mb-2">Tipos de Archivo</h3>
                            <div id="chart-file-types" class="min-h-[200px]"></div>
                        </div>

                        <div class="bento-card p-6 flex-1 flex flex-col">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold text-[#2c3856]">Actividad Reciente</h3>
                                <div class="w-2 h-2 bg-[#ff9c00] rounded-full animate-ping"></div>
                            </div>
                            <div class="flex-1 overflow-y-auto custom-scrollbar pr-2 space-y-4 max-h-[300px]" id="activity-list-container">
                                </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('areaDashboardData', () => ({
                loading: true,
                time: '',
                date: '',
                data: {
                    isAreaAdmin: false,
                    areaName: '',
                    userCount: 0,
                    folderCount: 0,
                    fileCount: 0,
                    linkCount: 0,
                    activityBreakdown: [],
                    fileTypes: [],
                    recentActivities: [],
                    teamMembers: [],
                    backorderCount: 0,
                    recentFiles: [],
                    myProfile: null
                },

                init() {
                    this.updateTime();
                    setInterval(() => this.updateTime(), 1000);
                    this.loadData();
                    
                    if(typeof gsap !== 'undefined') {
                        gsap.fromTo('.animate-intro', 
                            { opacity: 0, y: -20 }, 
                            { opacity: 1, y: 0, duration: 1, ease: 'power3.out' }
                        );
                    }
                },

                updateTime() {
                    const now = new Date();
                    this.time = now.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
                    this.date = now.toLocaleDateString('es-MX', { weekday: 'long', day: 'numeric', month: 'long' });
                },

                timeAgo(dateString) {
                    const diff = Math.floor((new Date() - new Date(dateString)) / 1000);
                    if (diff < 60) return 'Ahora';
                    if (diff < 3600) return `Hace ${Math.floor(diff/60)} min`;
                    if (diff < 86400) return `Hace ${Math.floor(diff/3600)} h`;
                    return `Hace ${Math.floor(diff/86400)} d`;
                },

                async loadData() {
                    try {
                        const response = await fetch('{{ route("area_admin.dashboard.data") }}');
                        if (!response.ok) throw new Error('Error de red');
                        
                        this.data = await response.json();
                        this.loading = false;
                        
                        document.getElementById('area-name-display').textContent = this.data.areaName;

                        this.$nextTick(() => {
                            if(typeof gsap !== 'undefined') {
                                gsap.to('.animate-stagger', { 
                                    opacity: 1, y: 0, duration: 0.8, stagger: 0.15, ease: 'power2.out' 
                                });
                            }
                            this.renderCharts();
                            this.populateLists();
                        });

                    } catch (error) {
                        console.error(error);
                        this.loading = false;
                    }
                },

                renderCharts() {
                    const common = {
                        chart: { fontFamily: 'Montserrat, sans-serif', toolbar: { show: false }, background: 'transparent' },
                        colors: ['#2c3856', '#ff9c00', '#0ea5e9', '#10b981', '#8b5cf6', '#f59e0b', '#f43f5e', '#6366f1', '#14b8a6'],
                        theme: { mode: 'light' }
                    };

                    if (this.data.fileTypes && this.data.fileTypes.length > 0) {
                        new ApexCharts(document.querySelector("#chart-file-types"), {
                            ...common,
                            series: this.data.fileTypes.map(item => item.total),
                            labels: this.data.fileTypes.map(item => item.extension.toUpperCase()),
                            chart: { type: 'donut', height: 250 },
                            legend: { position: 'bottom', fontSize: '10px' },
                            plotOptions: { pie: { donut: { size: '65%' } } }
                        }).render();
                    }

                    if (this.data.isAreaAdmin && this.data.activityBreakdown && this.data.activityBreakdown.length > 0) {
                        new ApexCharts(document.querySelector("#chart-activity-breakdown"), {
                            ...common,
                            series: this.data.activityBreakdown.map(item => item.total),
                            labels: this.data.activityBreakdown.map(item => item.action_type),
                            // CAMBIO CLAVE AQUÍ: height 100% para usar el espacio vertical verticalmente
                            chart: { type: 'donut', height: '100%', parentHeightOffset: 0 },
                            legend: { position: 'bottom', fontSize: '11px', offsetY: 0 },
                            plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'TOTAL', color: '#2c3856' } } } } }
                        }).render();
                    }
                },

                populateLists() {
                    const activityContainer = document.getElementById('activity-list-container');
                    if (activityContainer && this.data.recentActivities) {
                        activityContainer.innerHTML = '';
                        this.data.recentActivities.forEach(act => {
                            activityContainer.innerHTML += `
                                <div class="activity-item group">
                                    <div class="flex justify-between items-start mb-0.5">
                                        <span class="text-[10px] font-bold text-[#ff9c00] uppercase">${this.timeAgo(act.created_at)}</span>
                                    </div>
                                    <p class="text-xs font-bold text-[#2c3856]">${act.action}</p>
                                    <p class="text-[10px] text-gray-500">${act.user ? act.user.name : 'Sistema'}</p>
                                </div>
                            `;
                        });
                    }

                    const teamContainer = document.getElementById('team-list-container');
                    if (teamContainer && this.data.teamMembers) {
                        teamContainer.innerHTML = '';
                        this.data.teamMembers.forEach(member => {
                            const img = member.profile_photo_path_url 
                                ? `<img src="${member.profile_photo_path_url}" class="w-8 h-8 rounded-full object-cover border border-gray-200">`
                                : `<div class="w-8 h-8 rounded-full bg-[#2c3856] text-white flex items-center justify-center text-xs font-bold">${member.name.charAt(0)}</div>`;
                            
                            teamContainer.innerHTML += `
                                <div class="flex items-center gap-3 p-2 hover:bg-white/30 rounded-lg transition-colors">
                                    ${img}
                                    <div>
                                        <p class="text-xs font-bold text-[#2c3856]">${member.name}</p>
                                        <p class="text-[10px] text-gray-500">${member.email}</p>
                                    </div>
                                </div>
                            `;
                        });
                    }

                    if (!this.data.isAreaAdmin) {
                        const fileContainer = document.getElementById('recent-files-container');
                        if (fileContainer && this.data.recentFiles) {
                            fileContainer.innerHTML = '';
                            this.data.recentFiles.forEach(file => {
                                fileContainer.innerHTML += `
                                    <div class="flex items-center gap-3 p-3 bg-white/40 rounded-xl border border-white/50">
                                        <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-[#2c3856] truncate">${file.name}</p>
                                            <p class="text-[10px] text-gray-500">En: ${file.folder ? file.folder.name : 'Raíz'}</p>
                                        </div>
                                        <a href="/files/${file.id}/download" class="text-gray-400 hover:text-[#2c3856] transition-colors">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                        </a>
                                    </div>
                                `;
                            });
                        }
                    }
                }
            }));
        });
    </script>
</x-app-layout>