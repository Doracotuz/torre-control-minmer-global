<x-app-layout>
    <x-slot name="header"></x-slot>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Raleway:wght@700;800;900&display=swap');

        :root {
            --c-navy: #2c3856;
            --c-navy-light: #3b4b72;
            --c-orange: #ff9c00;
            --c-dark: #1a1f2e;
            --c-white: #ffffff;
        }

        body {
            background-color: #f0f2f5;
            font-family: 'Montserrat', sans-serif;
            overflow-x: hidden;
        }

        .complex-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -2;
            background: linear-gradient(125deg, #eef2f3 0%, #eef2f3 40%, #e2e6ea 100%);
        }
        .complex-bg::before {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background-image: 
                radial-gradient(var(--c-navy) 1px, transparent 1px),
                radial-gradient(var(--c-orange) 1px, transparent 1px);
            background-size: 40px 40px;
            background-position: 0 0, 20px 20px;
            opacity: 0.05;
        }
        .orb-float {
            position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.4;
            animation: floatOrb 20s infinite ease-in-out;
        }

        .card-complex {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(25px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 
                0 10px 40px -10px rgba(44, 56, 86, 0.1),
                inset 0 0 20px rgba(255, 255, 255, 0.5);
            border-radius: 24px;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative; overflow: hidden;
            z-index: 1;
        }
        
        .card-complex::after {
            content: ''; position: absolute; inset: 0; border-radius: 24px; padding: 2px;
            background: linear-gradient(45deg, transparent, rgba(44, 56, 86, 0.1), transparent);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor; mask-composite: exclude;
            pointer-events: none;
        }

        .card-complex:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(44, 56, 86, 0.25);
            background: rgba(255, 255, 255, 0.9);
            z-index: 10;
        }

        .font-impact { font-family: 'Raleway', sans-serif; letter-spacing: -0.02em; }
        .text-gradient {
            background: linear-gradient(135deg, var(--c-navy) 0%, var(--c-navy-light) 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }

        .input-cockpit {
            background: rgba(255,255,255,0.5);
            border: 1px solid rgba(44, 56, 86, 0.1);
            font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;
            color: var(--c-navy); padding: 8px 12px; border-radius: 8px;
            transition: all 0.3s;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
        }
        .input-cockpit:focus {
            background: #fff; border-color: var(--c-orange);
            box-shadow: 0 0 0 3px rgba(255, 156, 0, 0.2); outline: none;
        }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: rgba(0,0,0,0.05); }
        ::-webkit-scrollbar-thumb { background: var(--c-navy); border-radius: 10px; }
        
        @keyframes floatOrb {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(30px, -50px); }
        }
        @keyframes slideUpFade {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-enter { animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    </style>

    <div class="complex-bg">
        <div class="orb-float bg-[#ff9c00] w-96 h-96 top-0 right-0"></div>
        <div class="orb-float bg-[#2c3856] w-[500px] h-[500px] bottom-0 left-0" style="animation-delay: -5s;"></div>
    </div>

    <div class="relative min-h-screen p-4 sm:p-6 lg:p-8 max-w-[1920px] mx-auto flex flex-col gap-6">
        
        <div class="card-complex p-1 flex flex-col xl:flex-row justify-between items-center gap-4 animate-enter relative">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#2c3856] via-[#ff9c00] to-[#2c3856]"></div>
            
            <div class="px-6 py-4 flex items-center gap-6 border-b xl:border-b-0 xl:border-r border-slate-200 w-full xl:w-auto">
                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-[#ff9c00] to-[#2c3856] rounded-lg blur opacity-25 group-hover:opacity-75 transition duration-1000 group-hover:duration-200"></div>
                    <div class="relative px-4 py-2 bg-white ring-1 ring-gray-900/5 rounded-lg leading-none flex items-top justify-start space-x-6">
                        <span class="font-impact font-black text-2xl text-[#2c3856]">CONTROL TOWER<span class="text-[#ff9c00]"> REPORTES</span></span>
                    </div>
                </div>
                <div class="hidden md:block">
                    <div class="flex items-center gap-2 mt-1">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                    </div>
                </div>
            </div>

            <div class="flex-grow flex flex-col md:flex-row items-center justify-end gap-3 px-4 py-2 w-full">
                <form method="GET" action="{{ route('ff.reports.index') }}" class="w-full md:w-auto flex flex-col md:flex-row gap-3">
                    @if(Auth::user()->isSuperAdmin())
                        <div class="relative group">
                            <label class="absolute -top-2 left-2 bg-white px-1 text-[13px] font-bold text-[#ff9c00] z-10">ÁREA</label>
                            <select name="area_id" onchange="this.form.submit()" class="input-cockpit w-full md:w-48 cursor-pointer">
                                <option value="">GLOBAL</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>
                                        {{ strtoupper($area->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="relative group">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[13px] font-bold text-[#ff9c00] z-10">AGENTE</label>
                        <select name="user_id" onchange="this.form.submit()" class="input-cockpit w-full md:w-64 cursor-pointer">
                            <option value="">VISTA GLOBAL CORPORATIVA</option>
                            @foreach ($vendedores as $vendedor)
                                <option value="{{ $vendedor->id }}" @if ($userIdFilter == $vendedor->id) selected @endif>
                                    {{ strtoupper($vendedor->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>

                <form action="{{ route('ff.reports.generateExecutive') }}" method="POST" target="_blank" class="flex flex-col md:flex-row gap-2 w-full md:w-auto items-center">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $userIdFilter }}">
                    
                    @if(request()->filled('area_id'))
                        <input type="hidden" name="area_id" value="{{ request('area_id') }}">
                    @endif
                    
                    <div class="flex items-center gap-1 bg-white/50 p-1 rounded-lg border border-slate-200">
                        <div class="relative">
                            <label class="absolute -top-2.5 left-2 text-[13px] font-bold text-slate-400">INICIO</label>
                            <input type="date" name="start_date" required class="input-cockpit border-none bg-transparent w-28 focus:ring-0">
                        </div>
                        <span class="text-[#2c3856] font-bold text-xs">↔</span>
                        <div class="relative">
                            <label class="absolute -top-2.5 left-2 text-[13px] font-bold text-slate-400">FIN</label>
                            <input type="date" name="end_date" required class="input-cockpit border-none bg-transparent w-28 focus:ring-0">
                        </div>
                    </div>

                    <button type="submit" class="w-full md:w-auto px-6 py-2.5 bg-[#2c3856] hover:bg-[#1a2236] text-white text-[11px] font-black uppercase tracking-widest rounded-lg shadow-lg shadow-[#2c3856]/30 transition-all flex items-center justify-center gap-2 group">
                        <span>GENERAR PDF</span>
                        <i class="fas fa-file-export group-hover:translate-x-1 transition-transform text-[#ff9c00]"></i>
                    </button>
                </form>
            </div>
        </div>

        @if (session('success') || session('error'))
            <div class="card-complex p-4 border-l-4 {{ session('success') ? 'border-emerald-500' : 'border-red-500' }} flex items-center gap-4 animate-enter" style="animation-delay: 0.1s;">
                <div class="w-10 h-10 rounded-lg {{ session('success') ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600' }} flex items-center justify-center font-bold text-lg shadow-inner">
                    <i class="fas {{ session('success') ? 'fa-check-circle' : 'fa-radiation' }}"></i>
                </div>
                <div>
                    <h4 class="text-[10px] font-black uppercase text-slate-400">Registro de Evento</h4>
                    <p class="text-sm font-bold text-[#2c3856]">{{ session('success') ?? session('error') }}</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-6 lg:grid-cols-12 gap-6">
            
            <div onclick="showModal('Ingresos Totales', '{{ '$' . number_format($valorTotalVendido, 2) }}', 'Flujo Neto', 'sales')" 
                class="col-span-1 md:col-span-6 lg:col-span-4 card-complex p-0 group cursor-pointer animate-enter" style="animation-delay: 0.1s;">
                <div class="h-full bg-gradient-to-br from-[#2c3856] to-[#1a1f2e] text-white p-6 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <i class="fas fa-chart-area text-9xl transform rotate-12 translate-x-10 -translate-y-10"></i>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-[#ff9c00] to-transparent"></div>
                    
                    <div class="relative z-10 flex flex-col h-full justify-between">
                        <div class="flex justify-between items-start">
                            <div class="bg-white/10 backdrop-blur rounded px-2 py-1 text-[9px] font-bold uppercase tracking-widest border border-white/10 text-[#ff9c00]">Finanzas</div>
                            <i class="fas fa-arrow-up text-emerald-400 bg-emerald-500/20 p-1.5 rounded text-xs"></i>
                        </div>
                        <div class="mt-4">
                            <h2 class="text-4xl xl:text-5xl font-impact font-black tracking-tight text-white group-hover:scale-105 transition-transform origin-left">
                                {{ '$' . number_format($valorTotalVendido, 2) }}
                            </h2>
                            <p class="text-xs text-slate-400 mt-2 font-medium">Ingresos Totales Acumulados</p>
                        </div>
                        <div class="flex items-end gap-1 h-8 mt-4 opacity-50">
                            @for($i=0; $i<10; $i++)
                                <div class="w-full bg-[#ff9c00]" style="height: {{ rand(20, 100) }}%; border-radius: 2px;"></div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>

            <div onclick="showModal('Volumen de Ventas', '{{ number_format($totalUnidadesVendidas) }}', 'Unidades Desplazadas', 'sales')" 
                class="col-span-1 md:col-span-3 lg:col-span-3 card-complex p-6 group cursor-pointer animate-enter" style="animation-delay: 0.2s;">
                <div class="flex flex-col h-full justify-between">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-gray-100 to-gray-200 shadow-inner flex items-center justify-center border border-white">
                            <i class="fas fa-cubes text-[#2c3856]"></i>
                        </div>
                        <span class="text-[18px] font-bold text-slate-400 uppercase">Volumen</span>
                    </div>
                    <div>
                        <h2 class="text-4xl font-impact font-black text-[#2c3856] mt-2 group-hover:text-[#ff9c00] transition-colors">{{ number_format($totalUnidadesVendidas) }}</h2>
                        <div class="w-full bg-slate-100 h-1.5 rounded-full mt-3 overflow-hidden">
                            <div class="bg-[#2c3856] h-full w-2/3 group-hover:w-full transition-all duration-700 ease-out"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div onclick="showModal('Inventario Crítico', '{{ number_format($stockAgotadoCount) }}', 'SKUs en Cero', 'exhausted')" 
                class="col-span-1 md:col-span-3 lg:col-span-5 card-complex p-0 group cursor-pointer animate-enter" style="animation-delay: 0.3s;">
                <div class="h-full bg-white relative overflow-hidden flex items-center">
                    <div class="absolute left-0 top-0 bottom-0 w-2 bg-rose-500"></div>
                    <div class="absolute right-0 top-0 w-32 h-full bg-[repeating-linear-gradient(45deg,transparent,transparent_10px,#fecdd3_10px,#fecdd3_20px)] opacity-20"></div>
                    
                    <div class="p-6 pl-8 flex flex-row items-center justify-between w-full relative z-10">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="flex h-3 w-3 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                                </span>
                                <span class="text-[15px] font-black text-rose-500 uppercase tracking-widest">Atención Requerida</span>
                            </div>
                            <h2 class="text-5xl font-impact font-black text-[#2c3856]">
                                {{ number_format($stockAgotadoCount) }} <span class="text-xl text-slate-400 font-medium">Items</span>
                            </h2>
                            <p class="text-xs text-slate-500 font-bold mt-1">Inventario Físico Agotado</p>
                        </div>
                        <div class="hidden sm:flex h-16 w-16 rounded-full border-4 border-rose-100 items-center justify-center group-hover:bg-rose-500 group-hover:text-white transition-colors text-rose-500">
                            <i class="fas fa-arrow-right text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-span-1 md:col-span-6 lg:col-span-8 card-complex p-6 animate-enter" style="animation-delay: 0.4s;">
                <div class="flex items-center justify-between mb-6 border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-lg font-impact font-black text-[#2c3856] flex items-center gap-2">
                            <i class="fas fa-chart-bar text-[#ff9c00]"></i> TOP RENDIMIENTO
                        </h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Análisis comparativo por unidad</p>
                    </div>
                    <div class="flex bg-slate-100 p-1 rounded-lg">
                        <button id="btn-bar" class="px-3 py-1 text-[10px] font-black uppercase rounded bg-white shadow text-[#2c3856] transition-all">Barras</button>
                        <button id="btn-polar" class="px-3 py-1 text-[10px] font-black uppercase rounded text-slate-400 hover:text-[#2c3856] transition-all">Radar</button>
                    </div>
                </div>
                <div class="relative w-full h-[350px]">
                    <div id="chart-top-productos-bar" class="w-full h-full absolute inset-0 transition-opacity duration-300"></div>
                    <div id="chart-top-productos-polar" class="w-full h-full absolute inset-0 opacity-0 pointer-events-none transition-opacity duration-300"></div>
                </div>
            </div>

            <div class="col-span-1 md:col-span-6 lg:col-span-4 card-complex p-6 flex flex-col animate-enter" style="animation-delay: 0.5s;">
                <div class="mb-4">
                    <h3 class="text-lg font-impact font-black text-[#2c3856]">FUERZA DE VENTAS</h3>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Distribución comercial</p>
                </div>
                <div class="flex-grow flex items-center justify-center relative">
                    <div class="absolute inset-0 flex items-center justify-center opacity-5 pointer-events-none">
                        <div class="w-48 h-48 border-[10px] border-[#2c3856] rounded-full border-dashed animate-[spin_10s_linear_infinite]"></div>
                    </div>
                    <div id="chart-ventas-vendedor" class="w-full relative z-10"></div>
                </div>
                <div class="grid grid-cols-2 gap-2 mt-4">
                    <div class="bg-slate-50 p-2 rounded border border-slate-100 text-center">
                        <span class="block text-xl font-black text-[#2c3856]">{{ count($vendedores) }}</span>
                        <span class="text-[8px] font-bold text-slate-400 uppercase">Agentes</span>
                    </div>
                    <div class="bg-slate-50 p-2 rounded border border-slate-100 text-center">
                        <span class="block text-xl font-black text-[#ff9c00]">100%</span>
                        <span class="text-[8px] font-bold text-slate-400 uppercase">Cobertura</span>
                    </div>
                </div>
            </div>

        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 animate-enter" style="animation-delay: 0.6s;">
            @php
                $tools = [
                    ['route' => 'ff.reports.transactions', 'icon' => 'fa-receipt', 'title' => 'TRANSACCIONES', 'sub' => 'Historial Completo', 'bg' => 'from-blue-500 to-blue-600'],
                    ['route' => 'ff.reports.inventoryAnalysis', 'icon' => 'fa-dolly-flatbed', 'title' => 'MOVIMIENTOS', 'sub' => 'Entradas/Salidas', 'bg' => 'from-indigo-500 to-indigo-600'],
                    ['route' => 'ff.reports.stockAvailability', 'icon' => 'fa-warehouse', 'title' => 'DISPONIBILIDAD', 'sub' => 'Auditoría Stock', 'bg' => 'from-amber-500 to-amber-600'],
                    ['route' => 'ff.reports.catalogAnalysis', 'icon' => 'fa-tags', 'title' => 'CATÁLOGO', 'sub' => 'Precios & SKUs', 'bg' => 'from-teal-500 to-teal-600'],
                    ['route' => 'ff.reports.sellerPerformance', 'icon' => 'fa-users', 'title' => 'DESEMPEÑO', 'sub' => 'Ranking Ventas', 'bg' => 'from-pink-500 to-pink-600'],
                ];
            @endphp

            @foreach($tools as $tool)
            <a href="{{ route($tool['route']) }}" class="card-complex p-4 flex flex-row items-center gap-4 group hover:-translate-y-2 transition-transform duration-300">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br {{ $tool['bg'] }} flex items-center justify-center text-white shadow-lg group-hover:scale-110 transition-transform">
                    <i class="fas {{ $tool['icon'] }} text-lg"></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-black text-[#2c3856] group-hover:text-[#ff9c00] transition-colors">{{ $tool['title'] }}</span>
                    <span class="text-[9px] font-bold text-slate-400 uppercase">{{ $tool['sub'] }}</span>
                    <div class="h-0.5 w-0 bg-[#ff9c00] mt-1 group-hover:w-full transition-all duration-300"></div>
                </div>
            </a>
            @endforeach
        </div>

    </div>

    <div id="data-modal" class="hidden fixed inset-0 z-50 overflow-hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-[#2c3856]/80 backdrop-blur-xl transition-opacity" onclick="closeModal()"></div>
        
        <div class="relative w-full h-full flex items-center justify-center p-4 pointer-events-none">
            <div class="pointer-events-auto w-full max-w-2xl bg-white/90 backdrop-blur-md rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[80vh] border border-white/50 animate-enter">
                
                <div class="bg-[#2c3856] p-8 relative overflow-hidden flex-shrink-0">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-[#ff9c00] rounded-full opacity-30 blur-2xl"></div>
                    <div class="absolute left-0 bottom-0 w-full h-1 bg-gradient-to-r from-[#ff9c00] to-transparent"></div>
                    
                    <div class="relative z-10">
                        <p class="text-[10px] font-black text-[#ff9c00] uppercase tracking-[0.2em] mb-2" id="modal-subtitle">DETALLE</p>
                        <h3 class="text-4xl font-impact font-black text-white leading-none" id="modal-title">TITLE</h3>
                        <div class="mt-4 inline-block px-4 py-1 rounded bg-white/10 border border-white/20 backdrop-blur">
                            <span class="text-2xl font-bold text-white" id="modal-value">VALUE</span>
                        </div>
                    </div>
                    <button onclick="closeModal()" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center bg-white/10 rounded-full text-white hover:bg-white/20 transition-all">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="flex-grow overflow-y-auto p-6 bg-slate-50">
                    <div class="flex items-center gap-2 mb-4">
                        <i class="fas fa-list-ul text-[#2c3856]"></i>
                        <h4 class="text-xs font-black text-[#2c3856] uppercase tracking-wide">Desglose de Movimientos</h4>
                    </div>
                    <div id="modal-transaction-list" class="space-y-3">
                        </div>
                </div>
                
                <div class="p-4 bg-white border-t border-slate-100 flex justify-end flex-shrink-0">
                    <button onclick="closeModal()" class="px-6 py-2 bg-[#2c3856] text-white text-xs font-bold uppercase rounded-lg hover:bg-[#1a1f2e]">Cerrar Panel</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        const dataTopProductos = @json($chartTopProductos);
        const dataVentasVendedor = @json($chartVentasVendedor);
        const stockAgotadoCount = {{ $stockAgotadoCount }};
        const currentAreaId = "{{ request('area_id') }}";
        const currentUserId = document.getElementById('user_id') ? document.getElementById('user_id').value : '';

        const commonOptions = {
            fontFamily: 'Montserrat, sans-serif',
            chart: {
                background: 'transparent',
                toolbar: { show: false },
                animations: { enabled: true, easing: 'easeinout', speed: 800, animateGradually: { enabled: true, delay: 150 }, dynamicAnimation: { enabled: true, speed: 350 } }
            },
            tooltip: {
                theme: 'dark',
                style: { fontSize: '12px', fontFamily: 'Montserrat, sans-serif' },
                x: { show: true },
                y: { formatter: (val) => val.toLocaleString() }
            },
            states: {
                hover: { filter: { type: 'darken', value: 0.8 } }
            }
        };

        function closeModal() {
            const modal = document.getElementById('data-modal');
            modal.classList.add('hidden');
        }

        function showModal(title, value, subtitle, metricType) {
            const listContainer = document.getElementById('modal-transaction-list');
            document.getElementById('modal-title').textContent = title;
            document.getElementById('modal-value').textContent = value;
            document.getElementById('modal-subtitle').textContent = subtitle;
            document.getElementById('data-modal').classList.remove('hidden');

            listContainer.innerHTML = `
                <div class="flex flex-col items-center justify-center py-12">
                    <div class="w-12 h-12 border-4 border-[#2c3856] border-t-[#ff9c00] rounded-full animate-spin"></div>
                    <p class="text-[10px] font-bold text-slate-400 mt-4 uppercase tracking-widest">Cargando Datos...</p>
                </div>`;

            if (metricType === 'exhausted') {
                listContainer.innerHTML = `
                    <div class="bg-rose-50 border border-rose-100 rounded-xl p-8 text-center">
                        <div class="w-20 h-20 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg shadow-rose-200">
                            <i class="fas fa-exclamation-triangle text-rose-500 text-3xl animate-pulse"></i>
                        </div>
                        <h5 class="text-xl font-black text-[#2c3856] mb-2">Alerta de Stock</h5>
                        <p class="text-sm text-slate-600 mb-6">Se han detectado <strong>${stockAgotadoCount} productos</strong> con inventario en cero. Se requiere reposición inmediata.</p>
                        <a href="{{ route('ff.reports.stockAvailability') }}" class="inline-flex items-center px-6 py-3 bg-[#2c3856] hover:bg-rose-600 text-white text-xs font-bold uppercase rounded-lg transition-colors shadow-lg">
                            <i class="fas fa-clipboard-list mr-2"></i> Gestionar Inventario
                        </a>
                    </div>`;
                return;
            }

            let url = '{{ route('ff.reports.api.recentMovements') }}';
            let params = new URLSearchParams({ user_id: currentUserId, area_id: currentAreaId, limit: 10 });

            fetch(`${url}?${params.toString()}`)
                .then(r => r.ok ? r.json() : Promise.reject('Error'))
                .then(data => {
                    if (data.length === 0) {
                        listContainer.innerHTML = '<p class="text-xs text-slate-400 text-center py-8 bg-slate-100 rounded-lg">No hay datos registrados en este periodo con los filtros actuales.</p>';
                        return;
                    }

                    listContainer.innerHTML = data.map((item, i) => `
                        <div class="flex items-center justify-between p-4 bg-white rounded-xl border border-slate-100 shadow-sm hover:shadow-md hover:border-[#ff9c00] transition-all duration-300 group">
                            <div class="flex items-center gap-4">
                                <div class="w-8 h-8 rounded bg-slate-100 text-[#2c3856] font-black text-xs flex items-center justify-center group-hover:bg-[#2c3856] group-hover:text-white transition-colors">
                                    ${i+1}
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-[#2c3856] uppercase">${item.detail}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-600 text-[9px] font-bold uppercase">${item.user}</span>
                                        <span class="text-[9px] text-slate-400 font-medium">${item.time}</span>
                                    </div>
                                </div>
                            </div>
                            <span class="text-sm font-black text-[#2c3856] tracking-tight">${item.value}</span>
                        </div>
                    `).join('');
                })
                .catch(() => {
                    listContainer.innerHTML = '<div class="p-4 bg-red-50 text-red-600 text-xs font-bold text-center rounded-lg">Error de conexión al servidor.</div>';
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const barChart = new ApexCharts(document.querySelector("#chart-top-productos-bar"), {
                ...commonOptions,
                series: [{ name: 'Unidades', data: dataTopProductos.series[0].data.map(Number) }],
                chart: { type: 'bar', height: 350, toolbar: { show: false } },
                colors: ['#2c3856', '#3b4b72', '#4b5e8e', '#5c72ab', '#ff9c00'],
                plotOptions: {
                    bar: { horizontal: true, borderRadius: 4, barHeight: '50%', distributed: true, dataLabels: { position: 'bottom' } }
                },
                dataLabels: { enabled: true, textAnchor: 'start', style: { colors: ['#fff'], fontSize: '10px', fontWeight: 700 }, formatter: (val) => val, offsetX: 0, dropShadow: { enabled: true } },
                grid: { borderColor: '#e2e8f0', strokeDashArray: 4, xaxis: { lines: { show: true } } },
                xaxis: { categories: dataTopProductos.categories, labels: { style: { colors: '#64748b', fontSize: '10px', fontWeight: 600 } } },
                yaxis: { labels: { style: { colors: '#2c3856', fontSize: '11px', fontWeight: 700, fontFamily: 'Raleway' }, maxWidth: 150 } },
                legend: { show: false }
            });
            barChart.render();

            const polarChart = new ApexCharts(document.querySelector("#chart-top-productos-polar"), {
                ...commonOptions,
                series: dataTopProductos.series[0].data.map(Number),
                chart: { type: 'polarArea', height: 350 },
                labels: dataTopProductos.categories,
                colors: ['#2c3856', '#ff9c00', '#666666', '#d68300', '#1a2236'],
                fill: { opacity: 0.9 },
                stroke: { width: 1, colors: ['#fff'] },
                yaxis: { show: false },
                legend: { position: 'bottom', fontSize: '11px', fontFamily: 'Montserrat' }
            });
            polarChart.render();

            const donutChart = new ApexCharts(document.querySelector("#chart-ventas-vendedor"), {
                ...commonOptions,
                series: dataVentasVendedor.series.map(Number),
                chart: { type: 'donut', height: 320 },
                labels: dataVentasVendedor.labels,
                colors: ['#2c3856', '#ff9c00', '#666666', '#94a3b8', '#cbd5e1'],
                stroke: { show: true, colors: ['#fff'], width: 4 },
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '75%',
                            labels: {
                                show: true,
                                name: { show: true, fontSize: '11px', fontWeight: 600, color: '#64748b', offsetY: -5 },
                                value: { show: true, fontSize: '28px', fontWeight: 900, color: '#2c3856', offsetY: 10, formatter: (val) => val },
                                total: { show: true, showAlways: true, label: 'TOTAL', fontSize: '11px', fontWeight: 700, color: '#ff9c00', formatter: function (w) { return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString() } }
                            }
                        }
                    }
                },
                legend: { position: 'bottom', fontSize: '11px', markers: { radius: 12 } }
            });
            donutChart.render();

            const btnBar = document.getElementById('btn-bar');
            const btnPolar = document.getElementById('btn-polar');
            const cBar = document.getElementById('chart-top-productos-bar');
            const cPolar = document.getElementById('chart-top-productos-polar');

            const toggle = (showBar) => {
                cBar.style.opacity = showBar ? '1' : '0';
                cBar.style.pointerEvents = showBar ? 'auto' : 'none';
                cPolar.style.opacity = showBar ? '0' : '1';
                cPolar.style.pointerEvents = showBar ? 'none' : 'auto';
                
                btnBar.className = showBar ? "px-3 py-1 text-[10px] font-black uppercase rounded bg-white shadow text-[#2c3856] transition-all" : "px-3 py-1 text-[10px] font-black uppercase rounded text-slate-400 hover:text-[#2c3856] transition-all";
                btnPolar.className = !showBar ? "px-3 py-1 text-[10px] font-black uppercase rounded bg-white shadow text-[#2c3856] transition-all" : "px-3 py-1 text-[10px] font-black uppercase rounded text-slate-400 hover:text-[#2c3856] transition-all";
            };

            btnBar.onclick = () => toggle(true);
            btnPolar.onclick = () => toggle(false);
        });
    </script>
</x-app-layout>