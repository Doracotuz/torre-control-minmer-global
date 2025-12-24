@php 
    // Variables simuladas para los contadores (puedes conectarlas a tu backend después)
    $activeRequests = 0; 
    $pendingValidations = 0; 
    $registeredProducts = 0; 
    $lastActivity = null; 
@endphp

<x-app-layout> 
    <x-slot name="header"></x-slot>
    
    <div x-data="{ 
            layout: localStorage.getItem('el_dashboard_layout') || 'original',
            setLayout(mode) {
                this.layout = mode;
                localStorage.setItem('el_dashboard_layout', mode);
            }
         }" 
         class="min-h-screen p-6 lg:p-10 relative overflow-hidden font-sans text-slate-800">

        <link rel="preconnect" href="https://fonts.googleapis.com"> 
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin> 
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Raleway:wght@700;800;900&display=swap" rel="stylesheet">
        
        <style>
            :root {
                --minmer-blue: #2c3856;
                --minmer-orange: #ff9c00;
                --minmer-dark: #1a2236;
            }

            body { font-family: 'Montserrat', sans-serif; background-color: #f0f2f5; }
            .font-brand { font-family: 'Raleway', sans-serif; }

            .animate-entry {
                animation: slideUpFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
                opacity: 0;
                transform: translateY(40px);
            }

            @keyframes slideUpFade {
                to { opacity: 1; transform: translateY(0); }
            }

            .dashboard-grid {
                display: grid;
                gap: 1.5rem;
                transition: all 0.5s ease-in-out;
            }

            .op-card {
                position: relative;
                overflow: hidden;
                border-radius: 16px;
                box-shadow: 0 10px 30px -10px rgba(44, 56, 86, 0.3);
                transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
                background: var(--minmer-blue);
                display: flex;
                flex-direction: column;
            }

            .op-card:hover {
                transform: translateY(-8px) scale(1.01);
                box-shadow: 0 25px 50px -12px rgba(44, 56, 86, 0.5);
                z-index: 10;
            }

            .card-bg-image {
                position: absolute;
                inset: 0;
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.7s ease;
                opacity: 0.6;
                filter: grayscale(30%);
            }
            .op-card:hover .card-bg-image { transform: scale(1.15); filter: grayscale(0%); opacity: 0.8; }

            .card-overlay {
                position: absolute;
                inset: 0;
                background: linear-gradient(to top, rgba(44, 56, 86, 0.95) 0%, rgba(44, 56, 86, 0.4) 60%, transparent 100%);
                transition: all 0.5s ease;
            }
            .op-card:hover .card-overlay { background: linear-gradient(to top, rgba(44, 56, 86, 0.9) 0%, rgba(255, 156, 0, 0.2) 100%); }

            .card-content {
                position: relative;
                z-index: 20;
                height: 100%;
                display: flex;
                flex-direction: column;
                justify-content: flex-end;
                padding: 2rem;
                color: white;
            }
            
            .card-icon-float {
                position: absolute;
                top: 2rem;
                right: 2rem;
                width: 64px;
                height: 64px;
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 1px solid rgba(255, 255, 255, 0.2);
                transition: all 0.5s ease;
            }
            .op-card:hover .card-icon-float {
                background: var(--minmer-orange);
                transform: rotate(15deg) scale(1.1);
                border-color: var(--minmer-orange);
                box-shadow: 0 0 20px rgba(255, 156, 0, 0.6);
            }

            .stat-badge {
                display: inline-flex;
                align-items: center;
                padding: 0.5rem 1rem;
                background: rgba(255, 255, 255, 0.15);
                backdrop-filter: blur(4px);
                border-radius: 8px;
                font-size: 0.85rem;
                font-weight: 600;
                margin-bottom: 1rem;
                border-left: 3px solid var(--minmer-orange);
                transform: translateX(-10px);
                opacity: 0;
                transition: all 0.4s ease;
            }
            .op-card:hover .stat-badge { transform: translateX(0); opacity: 1; }
        </style>

        <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-blue-900/5 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-orange-500/5 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="max-w-[1600px] mx-auto relative z-10">
            
            <header class="flex flex-col md:flex-row justify-between items-end mb-12 border-b border-gray-200 pb-6 animate-entry" style="animation-delay: 0s;">
                <div class="w-full md:w-auto">
                    <div class="mb-2">
                        <span class="text-[10px] font-semibold tracking-[0.4em] text-gray-400 uppercase">Control Tower</span>
                    </div>
                    <h1 class="text-3xl md:text-5xl font-brand leading-tight">
                        <span class="font-light text-gray-400">Marbete</span> 
                        <span class="font-bold text-[#2c3856]">Electrónico</span>
                    </h1>
                </div>
                
                <div class="w-full md:w-auto flex flex-row md:flex-col justify-between md:items-end mt-4 md:mt-0 gap-4">
                    <div class="bg-white/50 backdrop-blur-sm p-1 rounded-lg border border-gray-200 flex gap-1 shadow-sm">
                        <button @click="setLayout('original')" 
                            :class="layout === 'original' ? 'bg-[#2c3856] text-white shadow-md' : 'text-gray-400 hover:text-gray-600'"
                            class="p-2 rounded-md transition-all duration-300" title="Vista Original (Mosaico)">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        </button>
                        <button @click="setLayout('columns')" 
                            :class="layout === 'columns' ? 'bg-[#2c3856] text-white shadow-md' : 'text-gray-400 hover:text-gray-600'"
                            class="p-2 rounded-md transition-all duration-300" title="Vista Columnas (Side by Side)">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path></svg>
                        </button>
                    </div>

                    <div class="hidden md:block text-right">
                        <div class="text-3xl font-brand font-bold text-[#2c3856]">{{ now()->format('d M, Y') }}</div>
                        <div class="flex items-center justify-end gap-2 mt-1">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <span class="text-[10px] font-bold text-gray-400 tracking-widest uppercase">Sistema Online</span>
                        </div>
                    </div>
                </div>
            </header>

            <div class="dashboard-grid"
                 :class="layout === 'original' 
                    ? 'grid-cols-12 auto-rows-[minmax(240px,auto)]' 
                    : 'grid-cols-1 md:grid-cols-2 lg:grid-cols-2 auto-rows-auto'">

                <a href="{{ route('electronic-label.create') }}" 
                   class="op-card group animate-entry" 
                   :class="layout === 'original' ? 'col-span-12 lg:col-span-6 row-span-1' : 'col-span-1 h-[300px]'"
                   style="animation-delay: 0.1s;">
                   
                    <img src="https://images.unsplash.com/photo-1576595580361-90a855b84b20?ixlib=rb-1.2.1&auto=format&fit=crop&w=1200&q=80" 
                        alt="Creación" class="card-bg-image">
                    <div class="card-overlay"></div>
                    
                    <div class="card-icon-float">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>

                    <div class="card-content">
                        <h2 class="font-brand font-bold mb-2 leading-tight text-3xl">Creación de Marbetes</h2>
                        <p class="text-gray-200 text-lg font-light mb-6">
                            Generar nueva solicitud de marbetes electrónicos.
                        </p>
                        <div class="flex items-center text-[#ff9c00] font-bold tracking-wider text-sm uppercase group-hover:translate-x-2 transition-transform">
                            Comenzar <span class="ml-2 text-xl">→</span>
                        </div>
                    </div>
                </a>

                <a href="#" 
                   class="op-card group animate-entry grayscale opacity-90"
                   :class="layout === 'original' ? 'col-span-12 lg:col-span-6 row-span-1' : 'col-span-1 h-[300px]'" 
                   style="animation-delay: 0.2s;">
                   
                    <img src="https://images.unsplash.com/photo-1517048676732-d65bc937f952?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                        alt="Pendiente" class="card-bg-image">
                    <div class="card-overlay"></div>

                    <div class="card-icon-float">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <div class="card-content" :class="layout === 'original' ? '!justify-center' : ''">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-3xl font-brand font-bold mb-2">Pendiente</h2>
                                <p class="text-gray-300 text-sm">Módulo en construcción.</p>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="#" 
                   class="op-card group animate-entry grayscale opacity-90"
                   :class="layout === 'original' ? 'col-span-12 lg:col-span-6 row-span-1' : 'col-span-1 h-[300px]'"
                   style="animation-delay: 0.3s;">
                   
                    <img src="https://images.unsplash.com/photo-1555421689-491a97ff2040?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                        alt="Pendiente" class="card-bg-image">
                    <div class="card-overlay"></div>

                    <div class="card-icon-float">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>

                    <div class="card-content" :class="layout === 'original' ? '!justify-center' : ''">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-3xl font-brand font-bold mb-2">Pendiente</h2>
                                <p class="text-gray-300 text-sm">Módulo en construcción.</p>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="#" 
                   class="op-card group animate-entry grayscale opacity-90"
                   :class="layout === 'original' ? 'col-span-12 md:col-span-12 lg:col-span-6 row-span-1' : 'col-span-1 h-[300px]'"
                   style="animation-delay: 0.4s;">
                   
                    <img src="https://images.unsplash.com/photo-1526628953301-3e589a6a8b74?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                        alt="Pendiente" class="card-bg-image">
                    <div class="card-overlay"></div>

                    <div class="card-icon-float">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>

                    <div class="card-content" :class="layout === 'original' ? '!justify-center' : ''">
                        <div>
                            <h2 class="text-3xl font-brand font-bold mb-2">Pendiente</h2>
                            <p class="text-gray-300 text-sm">Módulo en construcción.</p>
                        </div>
                    </div>
                </a>

            </div>

            <div class="mt-16 text-center opacity-60 animate-entry" style="animation-delay: 0.6s;">
                <div class="inline-flex items-center space-x-4">
                    <div class="h-px w-12 bg-[#2c3856]"></div>
                    <p class="text-[10px] font-bold text-[#2c3856] uppercase tracking-[0.3em]">Control Tower - Minmer Global</p>
                    <div class="h-px w-12 bg-[#2c3856]"></div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>