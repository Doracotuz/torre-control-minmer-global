@php $productsCount = \App\Models\ffProduct::where('is_active', true)->count(); 
    $todayMovements = \App\Models\ffInventoryMovement::whereDate('created_at', \Carbon\Carbon::today())->count(); 
    $lowStockCount = \App\Models\ffProduct::withSum('movements', 'quantity') ->get() ->filter(function ($product) { return ($product->movements_sum_quantity ?? 0) < 10; }) ->count(); 
    $lastSale = \App\Models\ffInventoryMovement::where('quantity', '<', 0) ->latest('created_at') ->first(); 
@endphp

<x-app-layout> 
    <x-slot name="header"></x-slot>
    <link rel="preconnect" href="https://fonts.googleapis.com"> 
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin> 
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Raleway:wght@700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --minmer-blue: #2c3856;
            --minmer-orange: #ff9c00;
            --minmer-dark: #1a2236;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f0f2f5;
        }

        .font-brand {
            font-family: 'Raleway', sans-serif;
        }

        .animate-entry {
            animation: slideUpFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(40px);
        }

        @keyframes slideUpFade {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            grid-auto-rows: minmax(240px, auto);
            gap: 1.5rem;
        }

        .op-card {
            position: relative;
            overflow: hidden;
            border-radius: 16px;
            box-shadow: 0 10px 30px -10px rgba(44, 56, 86, 0.3);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--minmer-blue);
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

        .op-card:hover .card-bg-image {
            transform: scale(1.15);
            filter: grayscale(0%);
            opacity: 0.8;
        }

        .card-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(44, 56, 86, 0.95) 0%, rgba(44, 56, 86, 0.4) 60%, transparent 100%);
            transition: all 0.5s ease;
        }

        .op-card:hover .card-overlay {
            background: linear-gradient(to top, rgba(44, 56, 86, 0.9) 0%, rgba(255, 156, 0, 0.2) 100%);
        }

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
            transform: translateY(0);
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

        .op-card:hover .stat-badge {
            transform: translateX(0);
            opacity: 1;
        }

        .col-span-12 { grid-column: span 12; }
        .col-span-8 { grid-column: span 8; }
        .col-span-4 { grid-column: span 4; }
        .row-span-2 { grid-row: span 2; }

        @media (max-width: 1024px) {
            .dashboard-grid { display: flex; flex-direction: column; }
            .op-card { min-height: 300px; }
        }
    </style>

    <div class="min-h-screen p-6 lg:p-10 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-blue-900/5 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-orange-500/5 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="max-w-7xl mx-auto relative z-10">
            
            <header class="flex justify-between items-end mb-12 border-b border-gray-300/50 pb-6 animate-entry" style="animation-delay: 0s;">
                <div>
                    <div class="flex items-center space-x-3 mb-2">
                        <div class="h-1 w-10 bg-[#ff9c00] rounded-full"></div>
                        <span class="text-xs font-bold tracking-[0.3em] text-[#2c3856] uppercase">Control Tower</span>
                    </div>
                    <h1 class="text-4xl md:text-6xl font-brand font-black text-[#2c3856] leading-tight">
                        HOLA, <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#2c3856] to-[#ff9c00]">{{ strtoupper(Auth::user()->name) }}</span>
                    </h1>
                </div>
                <div class="hidden md:block text-right">
                    <div class="text-3xl font-brand font-bold text-[#2c3856]">{{ now()->format('d M, Y') }}</div>
                    <div class="flex items-center justify-end gap-2 mt-1">
                        <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                        </span>
                        <span class="text-sm font-bold text-gray-500 tracking-wider">SISTEMA ONLINE</span>
                    </div>
                </div>
            </header>

            <div class="dashboard-grid">

                <a href="{{ route('ff.sales.index') }}" class="op-card col-span-12 lg:col-span-8 row-span-2 group animate-entry" style="animation-delay: 0.1s;">
                    <img src="https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1200&q=80" 
                        alt="Logística" class="card-bg-image">
                    <div class="card-overlay"></div>
                    
                    <div class="card-icon-float">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>

                    <div class="card-content">
                        <div class="stat-badge">
                            <span>Última venta: {{ $lastSale ? $lastSale->created_at->diffForHumans() : 'N/A' }}</span>
                        </div>
                        <h2 class="text-4xl font-brand font-bold mb-2 leading-tight">Captura de Pedidos</h2>
                        <p class="text-gray-200 text-lg font-light max-w-xl mb-6">
                            Gestión operativa de transacciones. Inicia nuevos pedidos y coordina la logística de entrega en tiempo real.
                        </p>
                        <div class="flex items-center text-[#ff9c00] font-bold tracking-wider text-sm uppercase group-hover:translate-x-2 transition-transform">
                            Acceder al módulo <span class="ml-2 text-xl">→</span>
                        </div>
                    </div>
                </a>

                <a href="{{ route('ff.inventory.index') }}" class="op-card col-span-12 lg:col-span-4 row-span-2 group animate-entry" style="animation-delay: 0.2s;">
                    <img src="https://images.unsplash.com/photo-1553413077-190dd305871c?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                        alt="Almacén" class="card-bg-image">
                    <div class="card-overlay"></div>

                    <div class="card-icon-float">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>

                    <div class="card-content">
                        <div class="flex flex-col gap-2 mb-4">
                            <div class="stat-badge w-max">
                                <span>Movimientos Hoy: {{ $todayMovements }}</span>
                            </div>
                            @if($lowStockCount > 0)
                            <div class="stat-badge w-max !border-red-500 !bg-red-500/20">
                                <span>⚠️ {{ $lowStockCount }} Alertas de Stock</span>
                            </div>
                            @endif
                        </div>
                        <h2 class="text-3xl font-brand font-bold mb-2">Inventario</h2>
                        <p class="text-gray-300 text-sm font-light leading-relaxed">
                            Control total de existencias, entradas y salidas de almacén.
                        </p>
                    </div>
                </a>

                <a href="{{ route('ff.catalog.index') }}" class="op-card col-span-12 md:col-span-6 lg:col-span-6 row-span-1 group animate-entry" style="animation-delay: 0.3s;">
                    <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                        alt="Catálogo" class="card-bg-image">
                    <div class="card-overlay"></div>

                    <div class="card-content !justify-center">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-brand font-bold mb-1">Catálogo</h2>
                                <p class="text-gray-300 text-sm">Base maestra de productos.</p>
                            </div>
                            <div class="text-right">
                                <span class="block text-4xl font-brand font-bold text-white">{{ $productsCount }}</span>
                                <span class="text-xs font-bold text-[#ff9c00] uppercase tracking-wider">SKUs Activos</span>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('ff.reports.index') }}" class="op-card col-span-12 md:col-span-6 lg:col-span-6 row-span-1 group animate-entry" style="animation-delay: 0.4s;">
                    <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                        alt="Reportes" class="card-bg-image">
                    <div class="card-overlay"></div>

                    <div class="card-content !justify-center">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-brand font-bold mb-1">Reportes</h2>
                                <p class="text-gray-300 text-sm">Inteligencia de negocios y KPIs.</p>
                            </div>
                            <div class="w-12 h-12 rounded-full border-2 border-[#ff9c00] flex items-center justify-center">
                                <svg class="w-6 h-6 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>

            </div>

            <div class="mt-16 text-center opacity-60 animate-entry" style="animation-delay: 0.6s;">
                <div class="inline-flex items-center space-x-4">
                    <div class="h-px w-12 bg-[#2c3856]"></div>
                    <p class="text-[10px] font-bold text-[#2c3856] uppercase tracking-[0.3em]">Minmer Global Operations</p>
                    <div class="h-px w-12 bg-[#2c3856]"></div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>