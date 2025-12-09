<x-app-layout>
    <x-slot name="header"></x-slot>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Raleway:wght@700;800;900&display=swap');

        :root {
            --minmer-navy: #2c3856;
            --minmer-orange: #ff9c00;
            --minmer-grey: #666666;
            --minmer-dark: #2b2b2b;
            --glass-bg: rgba(255, 255, 255, 0.65);
            --glass-border: rgba(255, 255, 255, 0.6);
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f3f4f6;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6, .brand-font {
            font-family: 'Raleway', sans-serif;
        }

        .minmer-gradient-text {
            background: linear-gradient(135deg, var(--minmer-navy) 0%, #4a5f8a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .bento-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: 0 8px 32px 0 rgba(44, 56, 86, 0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: hidden;
            position: relative;
        }

        .bento-card:hover {
            transform: translateY(-5px) scale(1.01);
            box-shadow: 0 20px 40px 0 rgba(44, 56, 86, 0.12);
            border-color: var(--minmer-orange);
            z-index: 10;
        }

        .bento-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: 0.5s;
            pointer-events: none;
        }

        .bento-card:hover::before {
            left: 100%;
        }

        .stat-icon-wrapper {
            background: linear-gradient(135deg, var(--minmer-navy) 0%, #1a2236 100%);
            color: white;
            box-shadow: 0 10px 20px -5px rgba(44, 56, 86, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .action-card {
            background: rgba(255, 255, 255, 0.9);
            position: relative;
            z-index: 1;
        }

        .action-card:hover .action-icon {
            background-color: var(--minmer-orange);
            color: white;
            transform: scale(1.1) rotate(5deg);
        }

        .action-icon {
            transition: all 0.3s ease;
        }

        .pulse-indicator {
            box-shadow: 0 0 0 0 rgba(255, 156, 0, 0.7);
            animation: pulse-orange 2s infinite;
        }

        @keyframes pulse-orange {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 156, 0, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(255, 156, 0, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 156, 0, 0); }
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: var(--minmer-navy);
        }

        .apexcharts-tooltip {
            background: rgba(44, 56, 86, 0.95) !important;
            color: #fff;
            border-radius: 8px !important;
            border: none !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2) !important;
        }
        .apexcharts-tooltip-title {
            background: var(--minmer-orange) !important;
            font-family: 'Raleway', sans-serif !important;
            border-bottom: none !important;
        }
        .apexcharts-text {
            font-family: 'Montserrat', sans-serif !important;
            fill: #666666 !important;
        }

        .bg-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background-image: radial-gradient(var(--minmer-navy) 1px, transparent 1px);
            background-size: 40px 40px;
            opacity: 0.04;
            z-index: -1;
            mask-image: radial-gradient(circle at center, black, transparent 90%);
        }

        .analytics-card {
            background: linear-gradient(135deg, var(--minmer-navy) 0%, #151b2b 100%);
        }
    </style>

    <div class="relative min-h-screen pb-12" x-data="dashboardData()">
        <div class="bg-pattern"></div>

        <div class="max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8 pt-8">
            
            <div class="flex flex-col md:flex-row justify-between items-end mb-10 header-animate opacity-0 translate-y-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="px-3 py-1 bg-white/50 text-[#2c3856] text-xs font-bold rounded-full border border-[#2c3856]/10 tracking-wider uppercase backdrop-blur-sm">
                            Admin Console v2.0
                        </span>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#ff9c00] pulse-indicator"></span>
                            <span class="text-xs text-gray-500 font-medium">Sistema Operativo</span>
                        </div>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-extrabold text-[#2c3856] tracking-tight">
                        Hola, <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#2c3856] to-[#ff9c00]">{{ Auth::user()->name }}</span>
                    </h1>
                    <p class="mt-2 text-lg text-gray-500 font-medium max-w-2xl">
                        Bienvenido a la Torre de Control de Minmer Global.
                    </p>
                </div>
                <div class="mt-4 md:mt-0 text-right hidden md:block">
                    <p class="text-4xl font-black text-[#2c3856] tracking-tighter" x-text="currentTime"></p>
                    <p class="text-sm text-[#ff9c00] font-bold uppercase tracking-widest" x-text="currentDate"></p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
                
                <a href="{{ route('admin.areas.index') }}" class="bento-card p-6 flex flex-col justify-between group h-44 action-card opacity-0 translate-y-4 stagger-animate">
                    <div class="flex justify-between items-start">
                        <div class="action-icon w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-[#2c3856] shadow-sm group-hover:shadow-md">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        </div>
                        <div class="w-8 h-8 rounded-full border border-gray-100 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-4 h-4 text-[#ff9c00]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-[#2c3856]">Áreas</h3>
                        <p class="text-xs text-gray-500 group-hover:text-[#ff9c00] transition-colors font-medium">Estructura Corporativa</p>
                    </div>
                </a>

                <a href="{{ route('admin.users.index') }}" class="bento-card p-6 flex flex-col justify-between group h-44 action-card opacity-0 translate-y-4 stagger-animate">
                    <div class="flex justify-between items-start">
                        <div class="action-icon w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-[#2c3856] shadow-sm group-hover:shadow-md">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </div>
                        <div class="w-8 h-8 rounded-full border border-gray-100 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-4 h-4 text-[#ff9c00]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-[#2c3856]">Usuarios</h3>
                        <p class="text-xs text-gray-500 group-hover:text-[#ff9c00] transition-colors font-medium">Control de Acceso</p>
                    </div>
                </a>

                <a href="{{ route('admin.organigram.index') }}" class="bento-card p-6 flex flex-col justify-between group h-44 action-card opacity-0 translate-y-4 stagger-animate">
                    <div class="flex justify-between items-start">
                        <div class="action-icon w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-[#2c3856] shadow-sm group-hover:shadow-md">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" /></svg>
                        </div>
                        <div class="w-8 h-8 rounded-full border border-gray-100 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-4 h-4 text-[#ff9c00]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-[#2c3856]">Organigrama</h3>
                        <p class="text-xs text-gray-500 group-hover:text-[#ff9c00] transition-colors font-medium">Jerarquía Visual</p>
                    </div>
                </a>

                <a href="{{ route('asset-management.dashboard') }}" class="bento-card p-6 flex flex-col justify-between group h-44 action-card opacity-0 translate-y-4 stagger-animate">
                    <div class="flex justify-between items-start">
                        <div class="action-icon w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-[#2c3856] shadow-sm group-hover:shadow-md">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                        </div>
                        <div class="w-8 h-8 rounded-full border border-gray-100 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-4 h-4 text-[#ff9c00]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-[#2c3856]">Activos</h3>
                        <p class="text-xs text-gray-500 group-hover:text-[#ff9c00] transition-colors font-medium">Gestión de Inventario</p>
                    </div>
                </a>

                <a href="{{ route('admin.ticket-categories.index') }}" class="bento-card p-6 flex flex-col justify-between group h-44 action-card opacity-0 translate-y-4 stagger-animate">
                    <div class="flex justify-between items-start">
                        <div class="action-icon w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-[#2c3856] shadow-sm group-hover:shadow-md">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                        </div>
                        <div class="w-8 h-8 rounded-full border border-gray-100 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-4 h-4 text-[#ff9c00]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-[#2c3856]">Tickets</h3>
                        <p class="text-xs text-gray-500 group-hover:text-[#ff9c00] transition-colors font-medium">Soporte y Ayuda</p>
                    </div>
                </a>

                <a href="{{ route('admin.statistics.index') }}" class="bento-card p-6 flex flex-col justify-between group h-44 analytics-card text-white opacity-0 translate-y-4 stagger-animate relative overflow-hidden border-none">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-[#ff9c00] rounded-full filter blur-[50px] opacity-30 group-hover:opacity-50 transition-all duration-500"></div>
                    <div class="absolute -left-10 -bottom-10 w-32 h-32 bg-blue-500 rounded-full filter blur-[40px] opacity-20"></div>
                    
                    <div class="relative z-10 flex justify-between items-start">
                        <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center border border-white/20">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center group-hover:bg-[#ff9c00] transition-colors">
                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </div>
                    </div>
                    <div class="relative z-10">
                        <h3 class="text-lg font-bold text-white tracking-wide">Estadísticas</h3>
                        <p class="text-xs text-blue-100 font-medium">Bitácora completa</p>
                    </div>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bento-card p-6 flex items-center gap-4 opacity-0 translate-y-4 stagger-animate group">
                    <div class="stat-icon-wrapper w-14 h-14 rounded-2xl shrink-0 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Usuarios</p>
                        <h4 class="text-3xl font-black text-[#2c3856]" x-text="formatNumber(stats.users)">-</h4>
                    </div>
                </div>

                <div class="bento-card p-6 flex items-center gap-4 opacity-0 translate-y-4 stagger-animate group">
                    <div class="stat-icon-wrapper w-14 h-14 rounded-2xl shrink-0 bg-gradient-to-br from-[#ff9c00] to-[#e08900] group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Áreas</p>
                        <h4 class="text-3xl font-black text-[#2c3856]" x-text="formatNumber(stats.areas)">-</h4>
                    </div>
                </div>

                <div class="bento-card p-6 flex items-center gap-4 opacity-0 translate-y-4 stagger-animate group">
                    <div class="stat-icon-wrapper w-14 h-14 rounded-2xl shrink-0 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Carpetas</p>
                        <h4 class="text-3xl font-black text-[#2c3856]" x-text="formatNumber(stats.folders)">-</h4>
                    </div>
                </div>

                <div class="bento-card p-6 flex items-center gap-4 opacity-0 translate-y-4 stagger-animate group">
                    <div class="stat-icon-wrapper w-14 h-14 rounded-2xl shrink-0 bg-gradient-to-br from-[#ff9c00] to-[#e08900] group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Archivos</p>
                        <h4 class="text-3xl font-black text-[#2c3856]" x-text="formatNumber(stats.files)">-</h4>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
                <div class="lg:col-span-8 flex flex-col gap-6">
                    <div class="bento-card p-6 opacity-0 translate-y-4 stagger-animate">
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex items-center gap-3">
                                <span class="w-1.5 h-6 bg-[#ff9c00] rounded-full"></span>
                                <h3 class="text-xl font-extrabold text-[#2c3856]">Actividad en el Sistema</h3>
                            </div>
                            <span class="px-3 py-1 bg-gray-100 rounded-lg text-xs font-bold text-gray-500">Últimos 30 días</span>
                        </div>
                        <div id="chart-activity" style="min-height: 350px;"></div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bento-card p-6 h-96 opacity-0 translate-y-4 stagger-animate">
                            <h3 class="text-lg font-bold text-[#2c3856] mb-4">Usuarios vs Tipo</h3>
                            <div id="chart-user-types" style="height: 100%;"></div>
                        </div>
                        <div class="bento-card p-6 h-96 opacity-0 translate-y-4 stagger-animate">
                            <h3 class="text-lg font-bold text-[#2c3856] mb-4">Creación vs Eliminación</h3>
                            <div id="chart-creation-deletion" style="height: 100%;"></div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 flex flex-col gap-6">
                    <div class="bento-card p-0 h-full opacity-0 translate-y-4 stagger-animate flex flex-col">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                            <h3 class="text-lg font-extrabold text-[#2c3856]">Live Feed</h3>
                            <span class="animate-pulse flex h-3 w-3 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#ff9c00] opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-[#ff9c00]"></span>
                            </span>
                        </div>
                        
                        <div class="flex-1 overflow-y-auto custom-scrollbar p-6 space-y-6" id="activity-feed" style="max-height: 600px;">
                            <template x-if="recentActivities.length === 0">
                                <div class="flex flex-col items-center justify-center h-full text-gray-400">
                                    <svg class="w-12 h-12 mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <span class="text-sm font-medium">Esperando eventos...</span>
                                </div>
                            </template>
                            
                            <template x-for="activity in recentActivities" :key="activity.id">
                                <div class="relative pl-6 pb-2 border-l-2 border-gray-100 last:border-0 group transition-all duration-300 hover:pl-8">
                                    <div class="absolute -left-[5px] top-1.5 h-2.5 w-2.5 rounded-full bg-gray-300 group-hover:bg-[#ff9c00] transition-colors ring-4 ring-white shadow-sm"></div>
                                    <div class="flex flex-col">
                                        <div class="flex justify-between items-baseline mb-1">
                                            <span class="text-[10px] font-black text-[#ff9c00] uppercase tracking-wider" x-text="timeAgo(activity.created_at)"></span>
                                        </div>
                                        <p class="text-sm font-bold text-[#2c3856] leading-tight mb-1" x-text="activity.action"></p>
                                        <div class="flex items-center gap-2">
                                            <div class="w-5 h-5 rounded-full bg-gray-200 flex items-center justify-center text-[10px] font-bold text-gray-600">
                                                <span x-text="(activity.user ? activity.user.name.charAt(0) : 'S')"></span>
                                            </div>
                                            <p class="text-xs text-gray-500 font-medium truncate" x-text="(activity.user ? activity.user.name : 'Sistema')"></p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        
                        <div class="p-4 border-t border-gray-100 bg-gray-50/30">
                            <a href="{{ route('admin.statistics.index') }}" class="flex items-center justify-center gap-2 w-full py-3 rounded-xl bg-[#2c3856] text-white text-xs font-bold uppercase tracking-widest hover:bg-[#ff9c00] transition-all duration-300 shadow-lg shadow-blue-900/10">
                                Ver Bitácora
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="bento-card p-6 opacity-0 translate-y-4 stagger-animate">
                    <h3 class="text-lg font-bold text-[#2c3856] mb-4">Tipos de Archivo</h3>
                    <div id="chart-file-types" style="min-height: 250px;"></div>
                </div>
                <div class="bento-card p-6 opacity-0 translate-y-4 stagger-animate">
                    <h3 class="text-lg font-bold text-[#2c3856] mb-4">Volumen de Carpetas por Área</h3>
                    <div id="chart-area-volume" style="min-height: 250px;"></div>
                </div>
                <div class="bento-card p-6 opacity-0 translate-y-4 stagger-animate">
                    <h3 class="text-lg font-bold text-[#2c3856] mb-4">Top 5 Usuarios Activos</h3>
                    <div id="chart-top-users" style="min-height: 250px;"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 mb-8">
                 <div class="bento-card p-6 opacity-0 translate-y-4 stagger-animate">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-extrabold text-[#2c3856]">Mapa de Calor (Intensidad por Hora)</h3>
                        <div class="flex gap-2 text-xs font-bold">
                            <span class="px-2 py-1 rounded bg-blue-50 text-[#2c3856]">Bajo</span>
                            <span class="px-2 py-1 rounded bg-[#2c3856] text-white">Alto</span>
                            <span class="px-2 py-1 rounded bg-[#ff9c00] text-white">Intenso</span>
                        </div>
                    </div>
                    <div id="chart-heatmap" style="min-height: 300px;"></div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboardData', () => ({
                stats: { users: 0, areas: 0, folders: 0, files: 0 },
                recentActivities: [],
                currentTime: '',
                currentDate: '',
                
                init() {
                    this.updateTime();
                    setInterval(() => this.updateTime(), 1000);
                    this.fetchData();
                    
                    // Comprobación de seguridad para GSAP
                    if (typeof gsap !== 'undefined') {
                        this.initAnimations();
                    } else {
                        console.warn('GSAP no cargó correctamente, las animaciones se omitirán.');
                    }
                },

                updateTime() {
                    const now = new Date();
                    this.currentTime = now.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
                    this.currentDate = now.toLocaleDateString('es-MX', { weekday: 'long', day: 'numeric', month: 'long' });
                },

                formatNumber(num) {
                    return new Intl.NumberFormat('es-MX').format(num);
                },

                timeAgo(dateString) {
                    const date = new Date(dateString);
                    const now = new Date();
                    const seconds = Math.floor((now - date) / 1000);
                    if (seconds < 60) return 'Hace un momento';
                    const minutes = Math.floor(seconds / 60);
                    if (minutes < 60) return `Hace ${minutes} min`;
                    const hours = Math.floor(minutes / 60);
                    if (hours < 24) return `Hace ${hours} horas`;
                    return `Hace ${Math.floor(hours / 24)} días`;
                },

                initAnimations() {
                    gsap.to('.header-animate', { opacity: 1, y: 0, duration: 1, ease: 'power3.out', delay: 0.2 });
                    gsap.to('.stagger-animate', { 
                        opacity: 1, 
                        y: 0, 
                        duration: 0.8, 
                        stagger: 0.1, 
                        ease: 'back.out(1.2)',
                        delay: 0.5 
                    });
                },

                async fetchData() {
                    try {
                        const response = await fetch('{{ route("admin.dashboard.data") }}');
                        const data = await response.json();
                        
                        this.animateValue('users', data.totalUsers);
                        this.animateValue('areas', data.totalAreas);
                        this.animateValue('folders', data.totalFolders);
                        this.animateValue('files', data.totalFileLinks);

                        this.recentActivities = data.recentActivities;
                        
                        // Comprobación de seguridad para ApexCharts
                        if (typeof ApexCharts !== 'undefined') {
                            this.renderCharts(data);
                        } else {
                            console.warn('ApexCharts no cargó correctamente, los gráficos se omitirán.');
                        }

                    } catch (error) {
                        console.error('Dashboard Error:', error);
                    }
                },

                animateValue(key, end) {
                    let start = 0;
                    const duration = 2000;
                    const startTime = performance.now();
                    
                    const animate = (currentTime) => {
                        const elapsed = currentTime - startTime;
                        const progress = Math.min(elapsed / duration, 1);
                        const ease = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
                        
                        this.stats[key] = Math.floor(ease * (end - start) + start);
                        
                        if (progress < 1) {
                            requestAnimationFrame(animate);
                        } else {
                            this.stats[key] = end;
                        }
                    };
                    requestAnimationFrame(animate);
                },

                renderCharts(data) {
                    const commonOptions = {
                        chart: {
                            fontFamily: 'Montserrat, sans-serif',
                            toolbar: { show: false },
                            background: 'transparent'
                        },
                        colors: ['#2c3856', '#ff9c00', '#4a5f8a', '#ffb340', '#666666'],
                        dataLabels: { enabled: false },
                        theme: { mode: 'light' }
                    };

                    if (data.actionData) {
                        new ApexCharts(document.querySelector("#chart-activity"), {
                            ...commonOptions,
                            series: [{ name: 'Acciones', data: data.actionData.map(d => d.total) }],
                            chart: { type: 'area', height: 350, toolbar: { show: false } },
                            stroke: { curve: 'smooth', width: 3 },
                            fill: {
                                type: 'gradient',
                                gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.1, stops: [0, 90, 100] }
                            },
                            xaxis: { 
                                categories: data.actionData.map(d => d.action),
                                labels: { style: { colors: '#666', fontSize: '10px' } } 
                            },
                            grid: { borderColor: 'rgba(0,0,0,0.05)' }
                        }).render();
                    }

                    if (data.userTypeData && data.userTypeData.length > 0) {
                         new ApexCharts(document.querySelector("#chart-user-types"), {
                            ...commonOptions,
                            series: data.userTypeData.map(d => d.total_actions),
                            labels: data.userTypeData.map(d => d.user_type_label),
                            chart: { type: 'donut', height: '100%' },
                            plotOptions: { pie: { donut: { size: '65%' } } },
                            legend: { position: 'bottom', fontSize: '12px' },
                            stroke: { show: false }
                        }).render();
                    }

                    if (data.creationDeletion) {
                        const series = Object.values(data.creationDeletion);
                        const labels = Object.keys(data.creationDeletion);
                        new ApexCharts(document.querySelector("#chart-creation-deletion"), {
                            ...commonOptions,
                            series: series.length ? series : [0],
                            labels: labels.length ? labels : ['Sin datos'],
                            chart: { type: 'donut', height: '100%' },
                            colors: ['#10b981', '#ef4444'], 
                            plotOptions: { pie: { donut: { size: '65%' } } },
                            legend: { position: 'bottom', fontSize: '12px' },
                            stroke: { show: false }
                        }).render();
                    }

                    if (data.fileTypes && data.fileTypes.length > 0) {
                         new ApexCharts(document.querySelector("#chart-file-types"), {
                            ...commonOptions,
                            series: data.fileTypes.map(d => d.total),
                            labels: data.fileTypes.map(d => d.file_extension.toUpperCase()),
                            chart: { type: 'polarArea', height: 250 },
                            fill: { opacity: 0.9 },
                            stroke: { width: 1, colors: ['#fff'] },
                            yaxis: { show: false },
                            legend: { position: 'bottom', fontSize: '10px' }
                        }).render();
                    }

                    if (data.activityByHour) {
                        const heatmapData = Array.from({length: 24}, (_, i) => ({
                            x: i.toString().padStart(2, '0') + ':00',
                            y: data.activityByHour[i] || 0
                        }));

                        new ApexCharts(document.querySelector("#chart-heatmap"), {
                            ...commonOptions,
                            series: [{ name: 'Actividad', data: heatmapData }],
                            chart: { type: 'heatmap', height: 300, toolbar: {show: false} },
                            plotOptions: {
                                heatmap: {
                                    shadeIntensity: 0.5,
                                    radius: 6,
                                    colorScale: {
                                        ranges: [
                                            { from: 0, to: 0, color: '#f3f4f6', name: 'Inactivo' },
                                            { from: 1, to: 10, color: '#93c5fd', name: 'Bajo' },
                                            { from: 11, to: 50, color: '#2c3856', name: 'Medio' },
                                            { from: 51, to: 1000, color: '#ff9c00', name: 'Alto' }
                                        ]
                                    }
                                }
                            },
                            dataLabels: { enabled: false }
                        }).render();
                    }
                    
                    if(data.foldersByArea) {
                         new ApexCharts(document.querySelector("#chart-area-volume"), {
                            ...commonOptions,
                            series: [{ name: 'Carpetas', data: data.foldersByArea.map(d => d.total_folders) }],
                            chart: { height: 250, type: 'bar', toolbar: {show: false} },
                            plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
                            xaxis: { categories: data.foldersByArea.map(d => d.name), labels: { style: { fontSize: '9px' } } },
                            colors: ['#2c3856']
                         }).render();
                    }

                    if (data.topUsers) {
                        new ApexCharts(document.querySelector("#chart-top-users"), {
                            ...commonOptions,
                            series: [{ name: 'Acciones', data: data.topUsers.map(d => d.total_actions) }],
                            chart: { type: 'bar', height: 250, toolbar: {show: false} },
                            plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '50%' } },
                            colors: ['#ff9c00'],
                            xaxis: { labels: { show: false } },
                            yaxis: { labels: { style: { fontSize: '11px', fontWeight: 600 } } },
                            dataLabels: { enabled: true, textAnchor: 'start', style: { colors: ['#fff'] }, offsetX: 0 }
                        }).render();
                    }
                }
            }));
        });
    </script>
</x-app-layout>