<x-app-layout>
    <x-slot name="header"></x-slot>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Raleway:wght@700;800;900&display=swap');

        :root {
            --minmer-navy: #2c3856;
            --minmer-orange: #ff9c00;
            --minmer-grey: #666666;
            --minmer-dark: #2b2b2b;
        }

        .font-raleway { font-family: 'Raleway', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }

        @keyframes ken-burns { 0% { transform: scale(1); } 100% { transform: scale(1.1); } }
        .animate-ken-burns { animation: ken-burns 20s ease-in-out infinite alternate; }
        
        .shadow-soft { box-shadow: 0 20px 40px -10px rgba(44, 56, 86, 0.1); }
        
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }

        .img-placeholder { background-color: #e2e8f0; }
    </style>

    <div class="min-h-screen text-[#2b2b2b] font-montserrat pb-20 relative overflow-x-hidden"
         x-data="{ 
            tab: 'mando',
            currentSlide: 0,
            init() {
                setInterval(() => { this.currentSlide = (this.currentSlide < 2) ? this.currentSlide + 1 : 0; }, 6000);
            }
         }">

        <div class="fixed inset-0 -z-10 pointer-events-none">
            <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-[#2c3856] rounded-full blur-[150px] opacity-5"></div>
            <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-[#ff9c00] rounded-full blur-[150px] opacity-5"></div>
        </div>

        <div class="max-w-[1800px] mx-auto px-6 pt-10 relative z-10">
            
            <div class="flex flex-col xl:flex-row justify-between items-end mb-10 border-b border-gray-200 pb-6">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <div class="h-1 w-8 bg-[#ff9c00]"></div>
                        <p class="text-xs font-bold text-[#666666] uppercase tracking-[0.2em]">Centro de Operaciones</p>
                    </div>
                    <h1 class="text-5xl md:text-6xl font-raleway font-black text-[#2c3856] mb-1 leading-none">
                        MINMER <span class="text-[#ff9c00]">WMS</span>
                    </h1>
                    <p class="text-[#666666] text-lg font-medium">Panel de control logístico global</p>
                </div>

                <div class="bg-white shadow-soft border border-gray-100 rounded-full p-2 flex items-center gap-2 mt-8 xl:mt-0 transition-shadow hover:shadow-lg">
                    <a href="{{ route('wms.service-requests.index') }}" class="flex items-center gap-2 bg-[#2c3856] text-white px-5 py-3 rounded-full font-bold text-sm hover:bg-[#1a253a] transition-all shadow-md hover:shadow-lg" title="Solicitudes de Servicio">
                        <i class="fas fa-concierge-bell"></i>
                        <span class="hidden md:inline">Solicitudes</span>
                    </a>
                    <form action="{{ route('wms.dashboard') }}" method="GET" class="flex gap-2">
                        <div class="relative group">
                            <select name="warehouse_id" onchange="this.form.submit()" class="appearance-none bg-gray-50 hover:bg-gray-100 text-[#2c3856] font-bold text-sm py-3 pl-6 pr-10 rounded-full border-none focus:ring-0 transition-colors cursor-pointer min-w-[200px]">
                                <option value="">Todas las Ubicaciones</option>
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}" {{ (isset($warehouseId) && $warehouseId == $w->id) ? 'selected' : '' }}>{{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="h-8 w-px bg-gray-200 my-auto"></div>
                        <div class="relative group">
                            <select name="area_id" onchange="this.form.submit()" class="appearance-none bg-gray-50 hover:bg-gray-100 text-[#ff9c00] font-bold text-sm py-3 pl-6 pr-10 rounded-full border-none focus:ring-0 transition-colors cursor-pointer min-w-[200px]">
                                <option value="">Todos los Clientes</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}" {{ (isset($areaId) && $areaId == $area->id) ? 'selected' : '' }}>{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <a href="{{ route('wms.dashboard') }}" class="w-11 h-11 rounded-full bg-[#2c3856] hover:bg-[#1a253a] flex items-center justify-center text-white transition-all hover:rotate-180 shadow-md">
                            ⟳
                        </a>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-12">
                <button @click="tab = 'mando'" class="group relative h-40 rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500" :class="tab === 'mando' ? 'ring-4 ring-[#ff9c00]/30 translate-y-[-4px]' : 'hover:translate-y-[-4px]'">
                    <img src="https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?q=80&w=800&auto=format&fit=crop" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000 grayscale group-hover:grayscale-0">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#2c3856]/90 via-[#2c3856]/30 to-transparent"></div>
                    <div class="absolute bottom-5 left-6 text-left">
                        <p class="text-xs font-bold text-[#ff9c00] uppercase tracking-widest mb-1">General</p>
                        <p class="text-2xl font-raleway font-black text-white">Dashboard</p>
                    </div>
                </button>
                <button @click="tab = 'inventario'" class="group relative h-40 rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500" :class="tab === 'inventario' ? 'ring-4 ring-[#2c3856]/30 translate-y-[-4px]' : 'hover:translate-y-[-4px]'">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRJZdguUbIDEdJl_gQot6lco0c45KS0fCuBag&s" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000 grayscale group-hover:grayscale-0">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#2c3856]/90 via-[#2c3856]/30 to-transparent"></div>
                    <div class="absolute bottom-5 left-6 text-left">
                        <p class="text-xs font-bold text-gray-300 uppercase tracking-widest mb-1">Acciones</p>
                        <p class="text-2xl font-raleway font-black text-white">Inventario</p>
                    </div>
                </button>
                @if(Auth::user()->hasFfPermission('wms.products.view') || Auth::user()->hasFfPermission('wms.locations.view'))
                <button @click="tab = 'catalogos'" class="group relative h-40 rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500" :class="tab === 'catalogos' ? 'ring-4 ring-[#666666]/30 translate-y-[-4px]' : 'hover:translate-y-[-4px]'">
                    <img src="https://images.unsplash.com/photo-1553413077-190dd305871c?q=80&w=800&auto=format&fit=crop" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000 grayscale group-hover:grayscale-0">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#2c3856]/90 via-[#2c3856]/30 to-transparent"></div>
                    <div class="absolute bottom-5 left-6 text-left">
                        <p class="text-xs font-bold text-[#ff9c00] uppercase tracking-widest mb-1">Maestros</p>
                        <p class="text-2xl font-raleway font-black text-white">Catálogos</p>
                    </div>
                </button>
                @endif
                @if(Auth::user()->hasFfPermission('wms.reports'))
                <button @click="tab = 'reportes'" class="group relative h-40 rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500" :class="tab === 'reportes' ? 'ring-4 ring-[#2c3856]/30 translate-y-[-4px]' : 'hover:translate-y-[-4px]'">
                    <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=800&auto=format&fit=crop" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000 grayscale group-hover:grayscale-0">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#2c3856]/90 via-[#2c3856]/30 to-transparent"></div>
                    <div class="absolute bottom-5 left-6 text-left">
                        <p class="text-xs font-bold text-white uppercase tracking-widest mb-1">BI</p>
                        <p class="text-2xl font-raleway font-black text-white">Reportes</p>
                    </div>
                </button>
                @endif
            </div>

            <div x-show="tab === 'mando'" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 translate-y-10" x-transition:enter-end="opacity-100 translate-y-0">
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-10">
                    @foreach($kpis as $kpi)
                    <div class="bg-white p-8 rounded-3xl shadow-soft border border-gray-100 group hover:-translate-y-1 transition-transform duration-300">
                        <p class="text-[#666666] text-xs font-bold uppercase tracking-widest mb-3">{{ $kpi['label'] }}</p>
                        <div class="flex items-baseline gap-2">
                            <span class="text-5xl font-raleway font-black text-[#2c3856] tracking-tighter" 
                                  x-data x-init="animateCountTo($el, {{ $kpi['value'] }}, '{{ $kpi['format'] }}')">0</span>
                            @if($kpi['format'] === 'percent')<span class="text-xl text-[#ff9c00] font-bold">%</span>@endif
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 h-[650px]">
                    <div class="bg-white rounded-[2.5rem] shadow-soft border border-gray-100 overflow-hidden flex flex-col h-full group hover:shadow-xl transition-shadow duration-500">
                        <div class="relative h-40 overflow-hidden shrink-0 img-placeholder">
                            <img src="https://images.unsplash.com/photo-1578575437130-527eed3abbec?q=80&w=800&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-[#2c3856]/90 to-transparent"></div>
                            <div class="absolute bottom-5 left-8 text-white">
                                <h3 class="text-2xl font-raleway font-black">Recepciones</h3>
                                <p class="text-[#ff9c00] font-bold text-sm">Últimos 5 Creados</p>
                            </div>
                        </div>
                        <div class="px-6 py-4 border-b border-gray-50 flex gap-2 justify-between bg-white text-xs font-bold uppercase tracking-wider">
                            <div class="flex flex-col items-center w-1/3">
                                <span class="text-yellow-600 bg-yellow-50 px-2 py-1 rounded-lg w-full text-center">Pendientes</span>
                                <span class="text-lg text-[#2c3856] mt-1">{{ $poStats['pending'] }}</span>
                            </div>
                            <div class="flex flex-col items-center w-1/3 border-l border-gray-100">
                                <span class="text-blue-600 bg-blue-50 px-2 py-1 rounded-lg w-full text-center">En Proceso</span>
                                <span class="text-lg text-[#2c3856] mt-1">{{ $poStats['receiving'] }}</span>
                            </div>
                            <div class="flex flex-col items-center w-1/3 border-l border-gray-100">
                                <span class="text-green-600 bg-green-50 px-2 py-1 rounded-lg w-full text-center">Hoy</span>
                                <span class="text-lg text-[#2c3856] mt-1">{{ $poStats['completed_today'] }}</span>
                            </div>
                        </div>
                        <div class="flex-1 overflow-y-auto hide-scroll p-6 space-y-3">
                            @foreach($recentPOs as $po)
                                <a href="{{ route('wms.purchase-orders.show', $po) }}" class="flex items-center justify-between p-4 rounded-2xl bg-gray-50 border border-gray-100 hover:bg-blue-50 hover:border-blue-100 transition-colors">
                                    <div class="min-w-0 flex-1 pr-2">
                                        <div class="flex items-center gap-2 mb-1">
                                            <p class="font-bold text-[#2c3856] text-sm">{{ $po->po_number }}</p>
                                            <span class="text-[9px] px-2 py-0.5 rounded uppercase font-bold
                                                {{ $po->status == 'Completed' ? 'bg-green-100 text-green-700' : 
                                                  ($po->status == 'Receiving' ? 'bg-blue-100 text-blue-700' : 
                                                  ($po->status == 'Pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700')) }}">
                                                {{ $po->status }}
                                            </span>
                                        </div>
                                        <p class="text-[10px] text-[#666666] font-medium mt-0.5 flex items-center gap-1 truncate">
                                            <i class="far fa-user"></i> {{ $po->user->name ?? 'Sistema' }} • <i class="far fa-clock"></i> {{ $po->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-black text-blue-600">{{ number_format($po->lines_sum_quantity_ordered) }}</p>
                                        <p class="text-[8px] text-gray-400 uppercase font-bold">Unidades</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        <div class="p-6 border-t border-gray-50"><a href="{{ route('wms.purchase-orders.index') }}" class="block w-full py-4 text-center bg-[#2c3856] hover:bg-[#1a253a] text-white font-bold rounded-2xl shadow-lg">Ver Todas</a></div>
                    </div>

                    <div class="bg-white rounded-[2.5rem] shadow-soft border border-gray-100 overflow-hidden flex flex-col h-full group hover:shadow-xl transition-shadow duration-500">
                        <div class="relative h-40 overflow-hidden shrink-0 img-placeholder">
                            <img src="https://images.unsplash.com/photo-1532635042-a6f6ad4745f9?q=80&w=1170&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-[#2c3856]/90 to-transparent"></div>
                            <div class="absolute bottom-5 left-8 text-white">
                                <h3 class="text-2xl font-raleway font-black">Despachos</h3>
                                <p class="text-[#ff9c00] font-bold text-sm">Últimos 5 Creados</p>
                            </div>
                        </div>
                        <div class="px-6 py-4 border-b border-gray-50 flex gap-2 justify-between bg-white text-xs font-bold uppercase tracking-wider">
                            <div class="flex flex-col items-center w-1/3">
                                <span class="text-yellow-600 bg-yellow-50 px-2 py-1 rounded-lg w-full text-center">Pendientes</span>
                                <span class="text-lg text-[#2c3856] mt-1">{{ $soStats['pending'] }}</span>
                            </div>
                            <div class="flex flex-col items-center w-1/3 border-l border-gray-100">
                                <span class="text-blue-600 bg-blue-50 px-2 py-1 rounded-lg w-full text-center">Picking</span>
                                <span class="text-lg text-[#2c3856] mt-1">{{ $soStats['picking'] }}</span>
                            </div>
                            <div class="flex flex-col items-center w-1/3 border-l border-gray-100">
                                <span class="text-green-600 bg-green-50 px-2 py-1 rounded-lg w-full text-center">Hoy</span>
                                <span class="text-lg text-[#2c3856] mt-1">{{ $soStats['completed_today'] }}</span>
                            </div>
                        </div>
                        <div class="flex-1 overflow-y-auto hide-scroll p-6 space-y-3">
                            @foreach($recentSOs as $so)
                                <a href="{{ route('wms.sales-orders.show', $so) }}" class="flex items-center justify-between p-4 rounded-2xl bg-gray-50 border border-gray-100 hover:bg-green-50 hover:border-green-100 transition-colors">
                                    <div class="min-w-0 flex-1 pr-2">
                                        <div class="flex items-center gap-2 mb-1">
                                            <p class="font-bold text-[#2c3856] text-sm">{{ $so->so_number }}</p>
                                            <span class="text-[9px] px-2 py-0.5 rounded uppercase font-bold
                                                {{ in_array($so->status, ['Shipped', 'Completed']) ? 'bg-green-100 text-green-700' : 
                                                  ($so->status == 'Picking' ? 'bg-blue-100 text-blue-700' : 
                                                  ($so->status == 'Pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700')) }}">
                                                {{ $so->status }}
                                            </span>
                                        </div>
                                        <p class="text-[10px] text-[#666666] font-medium mt-0.5 flex items-center gap-1 truncate">
                                            <i class="far fa-user"></i> {{ $so->user->name ?? 'Sistema' }} • <i class="far fa-clock"></i> {{ $so->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-black text-green-600">{{ number_format($so->lines_sum_quantity_ordered) }}</p>
                                        <p class="text-[8px] text-gray-400 uppercase font-bold">Unidades</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        <div class="p-6 border-t border-gray-50"><a href="{{ route('wms.sales-orders.index') }}" class="block w-full py-4 text-center bg-[#2c3856] hover:bg-[#1a253a] text-white font-bold rounded-2xl shadow-lg">Ver Todos</a></div>
                    </div>

                    <div class="bg-white rounded-[2.5rem] shadow-soft border border-gray-100 overflow-hidden flex flex-col h-full group hover:shadow-xl transition-shadow duration-500">
                        <div class="relative h-40 overflow-hidden shrink-0 img-placeholder">
                            <img src="https://images.unsplash.com/photo-1572670014853-1d3a3f22b40f?q=80&w=1171&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-red-900/80 to-transparent mix-blend-multiply"></div>
                            <div class="absolute bottom-5 left-8 text-white">
                                <h3 class="text-2xl font-raleway font-black">Acciones Req.</h3>
                                <p class="text-red-100 font-bold text-sm">{{ $discrepancyTasks->count() + $nonAvailableStock->count() }} Incidentes</p>
                            </div>
                        </div>
                        
                        <div class="flex-1 overflow-y-auto hide-scroll p-6 space-y-3">
                            
                            @if($discrepancyTasks->isEmpty() && $nonAvailableStock->isEmpty())
                                <div class="h-full flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12 text-green-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p class="text-sm font-bold">Sistema Saludable</p>
                                </div>
                            @endif

                            @foreach($discrepancyTasks as $task)
                                <a href="{{ route('wms.physical-counts.show', $task->physical_count_session_id) }}" class="flex items-start gap-4 p-4 rounded-2xl bg-red-50 border border-red-100 hover:bg-red-100 transition-colors">
                                    <div class="mt-1 w-2 h-2 rounded-full bg-red-500 flex-shrink-0"></div>
                                    <div>
                                        <p class="text-xs text-red-600 font-bold uppercase tracking-wider mb-1">Discrepancia Conteo</p>
                                        <p class="font-bold text-[#2c3856] text-sm">{{ $task->location->code ?? 'N/A' }}</p>
                                        <p class="text-xs text-[#666666]">Producto: {{ $task->product->sku ?? 'N/A' }}</p>
                                    </div>
                                </a>
                            @endforeach

                            @foreach($nonAvailableStock as $item)
                                <a href="{{ route('wms.reports.non-available-inventory') }}" class="flex items-start gap-4 p-4 rounded-2xl bg-[#fff7ed] border border-orange-100 hover:bg-orange-50 transition-colors">
                                    <div class="mt-1 w-2 h-2 rounded-full bg-[#ff9c00] flex-shrink-0"></div>
                                    <div>
                                        <p class="text-xs text-[#ff9c00] font-bold uppercase tracking-wider mb-1">{{ $item->quality->name ?? 'Bloqueado' }}</p>
                                        <p class="font-bold text-[#2c3856] text-sm">LPN: {{ $item->pallet->lpn ?? 'N/A' }}</p>
                                        <p class="text-xs text-[#666666]">SKU: {{ $item->product->sku ?? 'N/A' }}</p>
                                    </div>
                                </a>
                            @endforeach

                        </div>
                    </div>
                </div>
            </div>

            <div x-show="tab === 'inventario'" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-10" x-transition:enter-end="opacity-100 translate-y-0"
                 x-data="lpnSearch('{{ route('wms.api.find-lpn') }}')">
                
                <div class="relative h-[450px] rounded-[3rem] bg-white shadow-soft border border-gray-100 mb-12 flex items-center z-20">
                    
                    <div class="w-full lg:w-1/2 p-12 lg:p-16 relative z-10">
                        <span class="inline-block px-3 py-1 bg-gray-100 text-[#2c3856] rounded-full text-xs font-bold uppercase tracking-wider mb-4">Herramienta de Búsqueda</span>
                        <h2 class="text-5xl font-raleway font-black text-[#2c3856] mb-4">Scanner LPN</h2>
                        <p class="text-[#666666] text-lg mb-8">Localiza cualquier unidad logística. Ingresa el código de etiqueta.</p>
                        
                        <div class="relative max-w-lg">
                            <input type="text" x-model="lpn" @keydown.enter.prevent="findLpn" 
                                class="w-full h-16 pl-6 pr-32 bg-gray-50 border-2 border-gray-100 rounded-2xl text-xl text-[#2c3856] placeholder-gray-400 focus:ring-0 focus:border-[#ff9c00] transition-all font-mono uppercase tracking-widest"
                                placeholder="LPN-XXXXXX">
                            
                            <button @click="findLpn" class="absolute right-2 top-2 bottom-2 px-6 bg-[#ff9c00] hover:bg-orange-600 text-white font-bold rounded-xl transition-all flex items-center justify-center shadow-lg" :disabled="loading">
                                <span x-show="!loading">IR</span>
                                <span x-show="loading" class="animate-spin">⟳</span>
                            </button>

                            <div x-show="pallet" @click.away="pallet = null; error = ''" 
                                 class="absolute top-full left-0 w-full mt-4 bg-white border border-gray-100 rounded-2xl p-6 shadow-2xl z-50">
                                <template x-if="pallet">
                                    <div>
                                        <div class="flex justify-between items-start border-b border-gray-100 pb-4 mb-4">
                                            <div>
                                                <p class="text-xs text-gray-400 uppercase tracking-widest">Código</p>
                                                <p class="text-2xl font-mono text-[#2c3856] font-black" x-text="pallet.lpn"></p>
                                                
                                                <div class="mt-2 flex gap-6">
                                                    <div>
                                                        <p class="text-[9px] text-gray-400 uppercase tracking-widest mb-0.5">Orden Origen</p>
                                                        <p class="text-xs font-bold text-[#2c3856]" x-text="pallet.purchase_order ? pallet.purchase_order.po_number : 'N/A'"></p>
                                                    </div>
                                                    <div>
                                                        <p class="text-[9px] text-gray-400 uppercase tracking-widest mb-0.5">Estatus</p>
                                                        <span class="inline-block px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-gray-100 text-gray-600" x-text="pallet.status"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="text-right">
                                                <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">Ubicación</p>
                                                <div class="px-4 py-2 rounded-lg text-lg font-bold font-mono border"
                                                     :class="pallet.location ? 'bg-blue-50 text-[#2c3856] border-blue-100' : 'bg-red-50 text-red-500 border-red-100'" 
                                                     x-text="pallet.location ? pallet.location.code : 'SIN UBICACIÓN'">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="space-y-2 max-h-48 overflow-y-auto custom-scrollbar">
                                            <template x-for="item in pallet.items" :key="item.id">
                                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl">
                                                    <div>
                                                        <p class="font-bold text-[#2c3856]" x-text="item.product.sku"></p>
                                                        <p class="text-xs text-gray-500" x-text="item.product.name"></p>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="text-xl font-mono text-[#ff9c00] font-bold" x-text="item.quantity"></p>
                                                        <p class="text-[10px] text-gray-400 uppercase" x-text="item.quality.name"></p>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div x-show="error" class="absolute top-full left-0 w-full mt-4 bg-red-50 text-red-600 font-bold p-4 rounded-xl text-center border border-red-100 z-50" x-text="error"></div>
                        </div>
                    </div>
                    
                    <div class="absolute right-0 top-0 w-1/2 h-full hidden lg:block img-placeholder rounded-r-[3rem] overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1550751827-4bd374c3f58b?q=80&w=1000&auto=format&fit=crop" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-r from-white via-white/20 to-transparent"></div>
                    </div>
                </div>

                <h3 class="text-2xl font-raleway font-black text-[#2c3856] mb-6">Acciones Operativas</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @php
                        $ops = [
                            ['route' => 'wms.inventory.index', 'title' => 'Inventario General', 'desc' => 'Vista tabular de todo el stock', 'img' => 'https://images.unsplash.com/photo-1587293852726-70cdb56c2866?q=80&w=500&auto=format&fit=crop', 'perm' => 'wms.inventory'],
                            ['route' => 'wms.inventory.transfer.create', 'title' => 'Transferencias', 'desc' => 'Reubicar mercancía', 'img' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?q=80&w=500&auto=format&fit=crop', 'perm' => 'wms.inventory_move'],
                            ['route' => 'wms.inventory.split.create', 'title' => 'Split (Dividir)', 'desc' => 'Separar pallets', 'img' => 'https://img1.wsimg.com/isteam/ip/2fa41b42-239e-4744-a6f7-1f8a6642ec41/0c955f09-8ae0-4fac-9a5f-3d9257bc05e4.png', 'perm' => 'wms.inventory_move'],
                            ['route' => 'wms.physical-counts.index', 'title' => 'Conteos Cíclicos', 'desc' => 'Auditorías', 'img' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=500&auto=format&fit=crop', 'perm' => 'wms.inventory_adjust'],
                            ['route' => 'wms.inventory.adjustments.log', 'title' => 'Bitácora Ajustes', 'desc' => 'Historial de cambios', 'img' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=500&auto=format&fit=crop', 'perm' => 'wms.inventory'],
                            ['route' => 'wms.inventory.pallet-info.index', 'title' => 'Consulta Pallet', 'desc' => 'Búsqueda por LPN', 'img' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?q=80&w=500&auto=format&fit=crop', 'perm' => 'wms.inventory'],
                            ['route' => 'wms.inventory.location-info.index', 'title' => 'Contenido Ubicación', 'desc' => 'Ver stock en ubicación', 'img' => 'https://images.unsplash.com/photo-1590247813693-5541d1c609fd?q=80&w=500&auto=format&fit=crop', 'perm' => 'wms.inventory'],
                        ];
                    @endphp
                    @foreach($ops as $op)
                    @if(Auth::user()->hasFfPermission($op['perm']))
                    <a href="{{ route($op['route']) }}" class="group relative h-48 rounded-[2rem] overflow-hidden shadow-soft hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 img-placeholder">
                        <img src="{{ $op['img'] }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000 grayscale group-hover:grayscale-0 opacity-60 group-hover:opacity-100">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#2c3856]/90 via-[#2c3856]/40 to-transparent"></div>
                        <div class="absolute bottom-6 left-8 text-white">
                            <h3 class="text-xl font-bold font-raleway">{{ $op['title'] }}</h3>
                            <p class="text-xs text-gray-300 font-montserrat">{{ $op['desc'] }}</p>
                            <div class="h-1 w-10 bg-[#ff9c00] mt-3 group-hover:w-20 transition-all duration-300"></div>
                        </div>
                    </a>
                    @endif
                    @endforeach
                </div>
            </div>

            <div x-show="tab === 'catalogos'" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 translate-y-10" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    @php
                        $catalogs = [
                            ['r' => 'wms.products.index', 't' => 'Productos', 'img' => 'https://images.unsplash.com/photo-1595246140625-573b715d11dc?q=80&w=500&auto=format&fit=crop', 'perm' => 'wms.products.view'],
                            ['r' => 'wms.locations.index', 't' => 'Ubicaciones', 'img' => 'https://images.unsplash.com/photo-1590247813693-5541d1c609fd?q=80&w=500&auto=format&fit=crop', 'perm' => 'wms.locations.view'],
                            ['r' => 'wms.warehouses.index', 't' => 'Almacenes', 'img' => 'https://images.unsplash.com/photo-1644079446600-219068676743?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D', 'perm' => 'wms.warehouses'],
                            ['r' => 'wms.brands.index', 't' => 'Marcas', 'img' => 'https://www.headsem.com/wp-content/uploads/2015/11/ver-las-propiedades-de-hardware-de-mi-pc-en-windows.jpg', 'perm' => 'wms.brands'],
                            ['r' => 'wms.product-types.index', 't' => 'Tipos', 'img' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTgJ4DjVBL8UI8GkgNeVWaBcZSHa_ANC8OEcQ&s', 'perm' => 'wms.product_types'],
                            ['r' => 'wms.qualities.index', 't' => 'Calidades', 'img' => 'https://img.freepik.com/fotos-premium/botella-rota-aislada-sobre-fondo-blanco_51524-17283.jpg', 'perm' => 'wms.quality'],
                            ['r' => 'wms.lpns.index', 't' => 'Generar LPNs', 'img' => 'https://infraon.io/blog/wp-content/uploads/2023/01/BARSCANNERBLOGFinal.png', 'perm' => 'wms.lpns'],
                            ['r' => 'wms.value-added-services.index', 't' => 'Catálogo Serv.', 'img' => 'https://images.unsplash.com/photo-1556740738-b6a63e27c4df?q=80&w=500&auto=format&fit=crop', 'perm' => 'wms.products.view'],
                        ];
                    @endphp

                    @foreach($catalogs as $cat)
                    @if(Auth::user()->hasFfPermission($cat['perm']))
                    <a href="{{ route($cat['r']) }}" class="group relative aspect-square rounded-[2rem] overflow-hidden shadow-soft hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 img-placeholder">
                        <img src="{{ $cat['img'] }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000 grayscale group-hover:grayscale-0">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#2c3856]/80 via-transparent to-transparent opacity-80 group-hover:opacity-60 transition-opacity"></div>
                        <div class="absolute bottom-0 left-0 w-full p-6 text-center">
                            <h3 class="text-lg font-raleway font-black text-white uppercase tracking-widest">{{ $cat['t'] }}</h3>
                        </div>
                    </a>
                    @endif
                    @endforeach
                </div>
            </div>

            <div x-show="tab === 'reportes'" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 translate-y-10" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @php
                        $reports = [
                            ['r' => 'wms.reports.inventory', 't' => 'Dashboard Inventario', 'd' => 'Stock global y valores.', 'img' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=800&auto=format&fit=crop'],
                            ['r' => 'wms.reports.billing.index', 't' => 'Facturación', 'd' => 'Costos de servicios y almacenaje.', 'img' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?q=80&w=800&auto=format&fit=crop'],
                            ['r' => 'wms.reports.stock-movements', 't' => 'Kardex Movimientos', 'd' => 'Entradas, salidas y ajustes.', 'img' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?q=80&w=800&auto=format&fit=crop'],
                            ['r' => 'wms.reports.slotting-heatmap', 't' => 'Mapa de Calor', 'd' => 'Eficiencia de ubicaciones.', 'img' => 'https://images.unsplash.com/photo-1504868584819-f8e8b4b6d7e3?q=80&w=800&auto=format&fit=crop'],
                            ['r' => 'wms.reports.non-available-inventory', 't' => 'Stock No Disponible', 'd' => 'Cuarentenas y daños.', 'img' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS7Uet4FxXLOk_9sb00Im42tkm1Hn7LlH3Ajw&s'],
                            ['r' => 'wms.reports.inventory-aging', 't' => 'Antigüedad', 'd' => 'Días de inventario.', 'img' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTeZO6JGauFbQ-S4YABbn_soFZAOsRYzRxQ0Q&s'],
                            ['r' => 'wms.reports.abc-analysis', 't' => 'Análisis ABC', 'd' => 'Clasificación por valor.', 'img' => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?q=80&w=800&auto=format&fit=crop'],
                        ];
                    @endphp
                    @foreach($reports as $rep)
                    <a href="{{ route($rep['r']) }}" class="group relative h-64 rounded-[2.5rem] overflow-hidden shadow-soft hover:shadow-2xl transition-all duration-500 img-placeholder">
                        <img src="{{ $rep['img'] }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-all duration-1000 grayscale group-hover:grayscale-0">
                        <div class="absolute inset-0 bg-[#2c3856]/80 group-hover:bg-[#2c3856]/40 transition-colors duration-500"></div>
                        <div class="absolute bottom-0 left-0 p-8 max-w-lg">
                            <h3 class="text-2xl font-raleway font-black text-white mb-1">{{ $rep['t'] }}</h3>
                            <p class="text-gray-200 text-sm group-hover:text-white transition-colors">{{ $rep['d'] }}</p>
                        </div>
                        <div class="absolute top-6 right-6 w-12 h-12 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-all transform translate-x-4 group-hover:translate-x-0">
                            ➜
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    <script>
        function lpnSearch(apiUrl) {
            return {
                lpn: '',
                pallet: null,
                loading: false,
                error: '',
                findLpn() {
                    if (!this.lpn.trim()) return;
                    this.loading = true;
                    this.pallet = null;
                    this.error = '';

                    fetch(`${apiUrl}?lpn=${this.lpn}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    })
                    .then(r => {
                        if (r.ok) return r.json();
                        if (r.status === 404) throw new Error('LPN NO EXISTE EN SISTEMA');
                        throw new Error('ERROR DE CONEXIÓN');
                    })
                    .then(data => { this.pallet = data; this.lpn = ''; })
                    .catch(e => { this.error = e.message; })
                    .finally(() => { this.loading = false; });
                }
            }
        }

        function animateCountTo(el, target, format = 'number') {
            let current = 0;
            const duration = 1500; 
            const step = target / (duration / 16);
            
            if (target === 0) {
                el.innerText = '0';
                return;
            }

            const isPercent = format === 'percent';
            const formatter = new Intl.NumberFormat('en-US', {
                minimumFractionDigits: isPercent ? 2 : 0,
                maximumFractionDigits: isPercent ? 2 : 0
            });

            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    el.innerText = formatter.format(target);
                    clearInterval(timer);
                } else {
                    el.innerText = formatter.format(current);
                }
            }, 16);
        }
    </script>
</x-app-layout>