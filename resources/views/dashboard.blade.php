<x-app-layout>
    <x-slot name="header"></x-slot>

    @php
        $areaIconSvg = '';
        $areaCustomIconPath = null;
        $areaName = 'General';

        if (Auth::user()->area) {
            $areaName = Auth::user()->area->name;
            $areaCustomIconPath = Auth::user()->area->icon_path;

            if (!$areaCustomIconPath) {
                switch ($areaName) {
                    case 'Recursos Humanos': 
                        $areaIconSvg = '<svg class="w-full h-full text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h-5v-2a3 3 0 013-3h2a3 3 0 013 3v2h-5zM9 10a3 3 0 11-6 0 3 3 0 016 0zM11 12a3 3 0 10-6 0 3 3 0 006 0z"></path></svg>'; 
                        break;
                    case 'Customer Service': 
                        $areaIconSvg = '<svg class="w-full h-full text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>'; 
                        break;
                    case 'Tráfico': 
                        $areaIconSvg = '<svg class="w-full h-full text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17l-4 4m0 0l-4-4m4 4V3m6 18v-3.586a1 1 0 01.293-.707l2.414-2.414A1 1 0 0115.586 14H18a2 2 0 002-2V7a2 2 0 00-2-2h-3.586a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 0110 2.414V1H4a2 2 0 00-2 2v14a2 2 0 002 2h5z"></path></svg>'; 
                        break;
                    case 'Almacén': 
                        $areaIconSvg = '<svg class="w-full h-full text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>'; 
                        break;
                    case 'Valor Agregado': 
                        $areaIconSvg = '<svg class="w-full h-full text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>'; 
                        break;
                    case 'POSM': 
                        $areaIconSvg = '<svg class="w-full h-full text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>'; 
                        break;
                    case 'Brokerage': 
                        $areaIconSvg = '<svg class="w-full h-full text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 15l-3-3m0 0l-3 3m3-3V3m3 12h8a2 2 0 002-2V7a2 2 0 00-2-2h-3l-4-3H9a2 2 0 00-2 2v4m-7 10h14a2 2 0 002-2V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4"></path></svg>'; 
                        break;
                    case 'Innovación y Desarrollo': 
                        $areaIconSvg = '<svg class="w-full h-full text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 20v-3m0 0l.688-.688c.482-.482 1.132-.756 1.802-.756h.71a2 2 0 002-2V8.5a2 2 0 00-2-2h-.71c-.67 0-1.32-.274-1.802-.756L12 5m0 15v-3m0 0l-.688-.688c-.482-.482-1.132-.756-1.802-.756H6a2 2 0 01-2-2V8.5a2 2 0 012-2h.71c.67 0 1.32-.274 1.802-.756L12 5"></path></svg>'; 
                        break;
                    case 'Administración': 
                        $areaIconSvg = '<svg class="w-full h-full text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.827 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.827 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.827-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.827-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>'; 
                        break;
                    default: 
                        $areaIconSvg = '<svg class="w-full h-full text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>'; 
                        break;
                }
            }
        }
    @endphp

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
                width: 50px;
                height: 50px;
                border-radius: 16px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                transition: transform 0.3s ease;
            }

            .bento-card:hover .stat-icon {
                transform: scale(1.1) rotate(5deg);
            }

            .icon-navy { background: rgba(44, 56, 86, 0.1); color: var(--minmer-navy); }
            .icon-orange { background: rgba(255, 156, 0, 0.1); color: var(--minmer-orange); }

            .custom-scrollbar::-webkit-scrollbar { width: 5px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: var(--minmer-navy); }

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
            .nav-button-orange:hover {
                box-shadow: 0 10px 20px rgba(255, 156, 0, 0.3);
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

            .loader-bar {
                height: 4px;
                width: 100%;
                background: #e2e8f0;
                overflow: hidden;
                border-radius: 2px;
                position: relative;
            }
            .loader-bar::after {
                content: '';
                position: absolute;
                left: -50%;
                height: 100%;
                width: 50%;
                background: var(--minmer-orange);
                animation: loading 1.5s infinite;
            }
            @keyframes loading { 0% { left: -50%; } 100% { left: 100%; } }
    </style>

    <div class="bg-animated"></div>

    <div class="min-h-screen pb-12" x-data="dashboardLogic()">
        <div class="max-w-[1800px] mx-auto px-4 sm:px-6 lg:px-8 pt-8">
            
            <div class="flex flex-col md:flex-row justify-between items-end mb-10 opacity-0 animate-intro">
                
                <div class="flex items-center gap-6">
                    @if (Auth::user()->area)
                    <div class="hidden md:flex items-center justify-center w-32 h-32 rounded-3xl bg-white shadow-lg border border-gray-100 p-4 shrink-0 transition-transform hover:scale-125 hover:shadow-2xl duration-300 z-10 relative">
                        @if ($areaCustomIconPath)
                            <img src="{{ Storage::disk('s3')->url($areaCustomIconPath) }}" 
                                 alt="{{ $areaName }} Icon" 
                                 class="w-full h-full object-contain">
                        @else
                            {!! $areaIconSvg !!}
                        @endif
                    </div>
                    @endif

                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <div class="flex items-center gap-1.5 px-3 py-1 bg-white border border-gray-200 rounded-full">
                                <span class="w-2 h-2 rounded-full bg-[#ff9c00] animate-pulse"></span>
                                <span class="text-xs font-bold text-gray-500">En línea</span>
                            </div>
                            @if(Auth::user()->area)
                            <div class="flex items-center gap-1.5 px-3 py-1 bg-white border border-gray-200 rounded-full">
                                <span class="text-xs font-bold text-[#2c3856]">{{ $areaName }}</span>
                            </div>
                            @endif
                        </div>
                        <h1 class="text-4xl md:text-5xl tracking-tight mt-1 brand-font">
                            <span class="font-bold text-slate-400">
                                <span x-text="greeting"></span>,
                            </span>
                            
                            <span class="font-black text-[#2c3856]">{{ Auth::user()->name }}</span>
                        </h1>
                        <p class="mt-2 text-lg text-gray-500 font-medium">
                            Bienvenido al Control Tower de Minmer Global.
                        </p>
                    </div>
                </div>

                <div class="mt-4 md:mt-0 text-right hidden md:block">
                    <p class="text-4xl font-black text-[#2c3856] tracking-tight" x-text="time"></p>
                    <p class="text-sm font-bold text-[#ff9c00] uppercase tracking-widest" x-text="date"></p>
                </div>
            </div>

            <template x-if="loading">
                <div class="w-full h-96 flex flex-col items-center justify-center opacity-0 animate-intro">
                    <div class="w-64">
                        <div class="loader-bar mb-4"></div>
                        <p class="text-center text-sm font-bold text-[#2c3856] animate-pulse">Sincronizando datos...</p>
                    </div>
                </div>
            </template>

            <div x-show="!loading" x-cloak class="space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bento-card p-6 flex items-center justify-between opacity-0 animate-stagger">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Total Usuarios</p>
                            <h3 class="text-3xl font-black text-[#2c3856]" x-text="formatNum(data.totalUsers)">0</h3>
                        </div>
                        <div class="stat-icon icon-navy">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </div>
                    </div>

                    <div class="bento-card p-6 flex items-center justify-between opacity-0 animate-stagger">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Total Áreas</p>
                            <h3 class="text-3xl font-black text-[#2c3856]" x-text="formatNum(data.totalAreas)">0</h3>
                        </div>
                        <div class="stat-icon icon-orange">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        </div>
                    </div>

                    <div class="bento-card p-6 flex items-center justify-between opacity-0 animate-stagger">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Total Carpetas</p>
                            <h3 class="text-3xl font-black text-[#2c3856]" x-text="formatNum(data.totalFolders)">0</h3>
                        </div>
                        <div class="stat-icon icon-navy">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg>
                        </div>
                    </div>

                    <div class="bento-card p-6 flex items-center justify-between opacity-0 animate-stagger">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Total Archivos</p>
                            <h3 class="text-3xl font-black text-[#2c3856]" x-text="formatNum(data.totalFileLinks)">0</h3>
                        </div>
                        <div class="stat-icon icon-orange">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    
                    <div class="lg:col-span-3 flex flex-col gap-6 opacity-0 animate-stagger">
                        <div class="bento-card p-6">
                            <h3 class="text-lg font-bold text-[#2c3856] mb-4">Accesos Directos</h3>
                            <div class="space-y-3">
                                <a href="{{ route('folders.index') }}" class="nav-button w-full p-4 rounded-xl flex items-center justify-between group">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-white/20 rounded-lg">
                                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z" /></svg>
                                        </div>
                                        <div class="text-left">
                                            <p class="text-sm font-bold">Mis Archivos</p>
                                            <p class="text-[10px] opacity-70">Explorador de documentos</p>
                                        </div>
                                    </div>
                                    <svg class="w-4 h-4 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </a>

                                @if(Auth::user()->isSuperAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="nav-button nav-button-orange w-full p-4 rounded-xl flex items-center justify-between group">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2 bg-white/20 rounded-lg">
                                                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /></svg>
                                            </div>
                                            <div class="text-left">
                                                <p class="text-sm font-bold">Admin Panel</p>
                                                <p class="text-[10px] opacity-70">Configuración global</p>
                                            </div>
                                        </div>
                                        <svg class="w-4 h-4 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div class="bento-card p-6 flex-1 flex flex-col">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold text-[#2c3856]">Actividad Reciente</h3>
                                <div class="w-2 h-2 bg-[#ff9c00] rounded-full animate-ping"></div>
                            </div>
                            <div class="flex-1 overflow-y-auto custom-scrollbar pr-2 space-y-4 max-h-[250px]">
                                <template x-if="data.recentActivities.length === 0">
                                    <p class="text-sm text-gray-400 text-center py-4">Sin actividad registrada</p>
                                </template>
                                <template x-for="activity in data.recentActivities" :key="activity.id">
                                    <div class="activity-item group">
                                        <div class="flex justify-between items-start mb-0.5">
                                            <span class="text-[10px] font-bold text-[#ff9c00] uppercase" x-text="timeAgo(activity.created_at)"></span>
                                        </div>
                                        <p class="text-xs font-bold text-[#2c3856]" x-text="activity.action"></p>
                                        <p class="text-[10px] text-gray-500" x-text="activity.user ? activity.user.name : 'Sistema'"></p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-5 flex flex-col gap-6 opacity-0 animate-stagger">
                        <div class="bento-card p-6 h-full flex flex-col">
                            <div class="flex justify-between items-center mb-4 h-10 shrink-0">
                                <h3 class="text-lg font-bold text-[#2c3856]">Peso de Carpetas por Área</h3>
                                <span class="text-xs text-gray-400 font-bold uppercase">Treemap</span>
                            </div>
                            <div id="chart-folders" class="flex-grow w-full h-full min-h-[300px]"></div>
                        </div>
                    </div>

                    <div class="lg:col-span-4 flex flex-col gap-6 opacity-0 animate-stagger">
                        <div class="bento-card p-6">
                            <h3 class="text-lg font-bold text-[#2c3856] mb-2">Tipos de Archivo</h3>
                            <div id="chart-files" class="min-h-[200px]"></div>
                        </div>
                        <div class="bento-card p-6">
                            <h3 class="text-lg font-bold text-[#2c3856] mb-2">Distribución Usuarios</h3>
                            <div id="chart-users-radial" class="min-h-[200px]"></div>
                        </div>
                    </div>

                </div>

                @if(Auth::user()->isSuperAdmin())
                <div class="grid grid-cols-1 opacity-0 animate-stagger">
                    <div class="bento-card p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-bold text-[#2c3856]">Usuarios por Área</h3>
                            <span class="px-2 py-1 bg-[#2c3856] text-white text-xs font-bold rounded">Vista Admin</span>
                        </div>
                        <div id="chart-users-bar" class="w-full min-h-[350px]"></div>
                    </div>
                </div>
                @endif
            </div>

        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboardLogic', () => ({
                loading: true,
                time: '',
                date: '',
                greeting: '',
                data: {
                    totalUsers: 0,
                    totalAreas: 0,
                    totalFolders: 0,
                    totalFileLinks: 0,
                    recentActivities: [],
                    foldersByArea: [],
                    usersByArea: [],
                    fileTypes: {},
                    userTypeData: {}
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
                    const hr = now.getHours();
                    this.greeting = hr < 12 ? 'Buenos días' : hr < 19 ? 'Buenas tardes' : 'Buenas noches';
                },

                formatNum(n) { return new Intl.NumberFormat('es-MX').format(n); },

                timeAgo(date) {
                    const diff = Math.floor((new Date() - new Date(date)) / 1000);
                    if (diff < 60) return 'Ahora';
                    if (diff < 3600) return `Hace ${Math.floor(diff/60)} min`;
                    if (diff < 86400) return `Hace ${Math.floor(diff/3600)} h`;
                    return `Hace ${Math.floor(diff/86400)} d`;
                },

                async loadData() {
                    try {
                        const res = await fetch('{{ route("admin.main_dashboard.data") }}');
                        if(!res.ok) throw new Error();
                        const fetched = await res.json();
                        
                        this.data = {
                            ...this.data,
                            ...fetched,
                            foldersByArea: fetched.foldersByArea || [],
                            usersByArea: fetched.usersByArea || [],
                            fileTypes: fetched.fileTypes || {},
                            userTypeData: fetched.userTypeData || {}
                        };
                        
                        this.loading = false;
                        
                        this.$nextTick(() => {
                            if(typeof gsap !== 'undefined') {
                                gsap.to('.animate-stagger', { 
                                    opacity: 1, y: 0, duration: 0.8, stagger: 0.15, ease: 'power2.out' 
                                });
                            }
                            if(typeof ApexCharts !== 'undefined') this.renderCharts();
                        });

                    } catch(e) { console.error(e); this.loading = false; }
                },

                renderCharts() {
                    const common = {
                        chart: { fontFamily: 'Montserrat, sans-serif', toolbar: { show: false }, background: 'transparent' },
                        colors: ['#2c3856', '#ff9c00', '#0ea5e9', '#10b981', '#8b5cf6', '#f59e0b', '#f43f5e', '#6366f1', '#14b8a6'],
                        theme: { mode: 'light' }
                    };

                    if(this.data.foldersByArea && this.data.foldersByArea.length > 0) {
                        new ApexCharts(document.querySelector("#chart-folders"), {
                            ...common,
                            series: [{ data: this.data.foldersByArea }],
                            chart: { type: 'treemap', height: '100%', toolbar: {show:false}, parentHeightOffset: 0 },
                            plotOptions: { treemap: { distributed: true, enableShades: false } }
                        }).render();
                    }

                    if(this.data.fileTypes && this.data.fileTypes.length > 0) {
                        new ApexCharts(document.querySelector("#chart-files"), {
                            ...common,
                            series: this.data.fileTypes.map(d => d.y),
                            labels: this.data.fileTypes.map(d => d.x.toUpperCase()),
                            chart: { type: 'donut', height: 250 },
                            legend: { position: 'bottom', fontSize: '10px' },
                            plotOptions: { pie: { donut: { size: '65%' } } }
                        }).render();
                    }

                    if(this.data.userTypeData && this.data.userTypeData.length > 0) {
                        new ApexCharts(document.querySelector("#chart-users-radial"), {
                            ...common,
                            series: this.data.userTypeData.map(d => d.y),
                            labels: this.data.userTypeData.map(d => d.x),
                            chart: { type: 'radialBar', height: 280 },
                            colors: ['#2c3856', '#ff9c00', '#10b981', '#666666'], 
                            plotOptions: { 
                                radialBar: { 
                                    dataLabels: { 
                                        total: { 
                                            show: true, 
                                            label: 'TOTAL', 
                                            color: '#2c3856',
                                            formatter: function (w) {
                                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                            }
                                        } 
                                    } 
                                } 
                            }
                        }).render();
                    }

                    @if(Auth::user()->isSuperAdmin())
                        if(this.data.usersByArea && this.data.usersByArea.length > 0) {
                            new ApexCharts(document.querySelector("#chart-users-bar"), {
                                ...common,
                                series: [{ name: 'Usuarios', data: this.data.usersByArea.map(i => i.count) }],
                                chart: { type: 'bar', height: 350, toolbar: {show:false} },
                                xaxis: { categories: this.data.usersByArea.map(i => i.name), labels: { style: { fontSize: '10px' } } },
                                plotOptions: { bar: { borderRadius: 4, columnWidth: '50%', distributed: true } },
                                legend: { show: false }
                            }).render();
                        }
                    @endif
                }
            }));
        });
    </script>
</x-app-layout>