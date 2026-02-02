<x-app-layout>
    <x-slot name="header"></x-slot>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Raleway:wght@800;900&display=swap');
        
        .font-raleway { font-family: 'Raleway', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        
        .stagger-enter { opacity: 0; transform: translateY(20px); animation: enterUp 0.5s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; }
        @keyframes enterUp { to { opacity: 1; transform: translateY(0); } }
        
        .input-arch {
            background: transparent; border: none; border-bottom: 2px solid #e5e7eb; border-radius: 0;
            padding: 0.8rem 0; font-family: 'Montserrat', sans-serif; font-weight: 600; color: #2c3856;
            transition: all 0.3s ease; width: 100%; font-size: 0.9rem;
        }
        .input-arch:focus { border-bottom-color: #ff9c00; box-shadow: none; outline: none; }
        .input-arch-select { background-image: none; cursor: pointer; padding-right: 1.5rem; }

        .btn-nexus { 
            background: #2c3856; color: white; border-radius: 1rem; font-weight: 700;
            transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center;
        }
        .btn-nexus:hover { background: #1a253a; transform: translateY(-2px); box-shadow: 0 10px 20px -5px rgba(44, 56, 86, 0.2); }
        
        .btn-ghost {
            background: transparent; color: #2c3856; border: 2px solid #e5e7eb; border-radius: 1rem; font-weight: 700;
        }
        .btn-ghost:hover { border-color: #2c3856; background: #2c3856; color: white; }

        .nexus-table { width: 100%; border-collapse: separate; border-spacing: 0 0.8rem; }
        .nexus-table thead th {
            font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; font-weight: 800;
            padding: 0 1.5rem 0.5rem 1.5rem; text-align: left;
        }
        .nexus-row {
            background: white; transition: all 0.2s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .nexus-row td {
            padding: 1rem 1.5rem; vertical-align: middle; border-top: 1px solid #f3f4f6; border-bottom: 1px solid #f3f4f6;
            background-color: white; white-space: nowrap;
        }
        .nexus-row td:first-child { border-top-left-radius: 1rem; border-bottom-left-radius: 1rem; border-left: 1px solid #f3f4f6; }
        .nexus-row td:last-child { border-top-right-radius: 1rem; border-bottom-right-radius: 1rem; border-right: 1px solid #f3f4f6; }
        .nexus-row:hover { box-shadow: 0 10px 30px -10px rgba(44, 56, 86, 0.05); z-index: 10; position: relative; }

        .kpi-card {
            border-radius: 2rem; padding: 1.5rem; position: relative; overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .kpi-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px -10px rgba(0,0,0,0.15); }
        .kpi-icon-bg {
            position: absolute; right: -20px; bottom: -20px; font-size: 8rem; opacity: 0.1;
            transform: rotate(-15deg); transition: all 0.5s ease;
        }
        .kpi-card:hover .kpi-icon-bg { transform: rotate(0deg) scale(1.1); opacity: 0.15; }
        
        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        [x-cloak] { display: none !important; }
    </style>

    <div class="min-h-screen text-[#2b2b2b] font-montserrat pb-20 relative overflow-x-hidden">
        
        <div class="fixed inset-0 -z-10 pointer-events-none">
            <div class="absolute top-0 right-0 w-[60vw] h-[60vh] bg-gradient-to-bl from-[#e0e7ff] to-transparent opacity-50 blur-[100px]"></div>
            <div class="absolute bottom-0 left-0 w-[40rem] h-[40rem] bg-[#fff7ed] rounded-full blur-[120px]"></div>
        </div>

        <div class="max-w-[1920px] mx-auto px-4 md:px-8 pt-8 relative z-10">
            
            <div class="flex flex-col xl:flex-row justify-between items-end mb-12 stagger-enter" style="animation-delay: 0.1s;">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-12 h-1 bg-[#ff9c00] rounded-full"></span>
                        <span class="text-xs font-bold text-[#2c3856] tracking-[0.3em] uppercase">Centro de Distribución</span>
                    </div>
                    <h1 class="text-4xl md:text-6xl font-raleway font-black text-[#2c3856] leading-none">
                        ÓRDENES DE <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-500">VENTA</span>
                    </h1>
                </div>

                <div class="flex flex-wrap gap-3 mt-6 xl:mt-0">
                    <a href="{{ route('wms.dashboard') }}" class="flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-[#666666] font-bold rounded-full shadow-sm hover:shadow-md hover:border-[#2c3856] hover:text-[#2c3856] transition-all">
                        <i class="fas fa-arrow-left"></i> <span>Dashboard</span>
                    </a>                          
                    <a href="{{ route('wms.sales-orders.export-csv', request()->query()) }}" class="btn-ghost px-6 py-3 h-12 flex items-center gap-2 text-xs uppercase tracking-wider bg-white shadow-sm">
                        <i class="fas fa-file-csv"></i> Exportar
                    </a>
                    <a href="{{ route('wms.sales-orders.create') }}" class="btn-nexus px-8 py-3 h-12 flex items-center gap-2 text-xs uppercase tracking-wider shadow-lg shadow-[#2c3856]/20">
                        <i class="fas fa-plus"></i> Nueva Orden
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12 stagger-enter" style="animation-delay: 0.2s;">
                
                <div class="relative bg-[#2c3856] rounded-[2rem] p-8 overflow-hidden shadow-xl shadow-[#2c3856]/20 group transition-all duration-300 hover:-translate-y-2">
                    <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:opacity-20 transition-opacity transform group-hover:scale-110 duration-500">
                        <i class="fas fa-layer-group text-8xl text-white"></i>
                    </div>
                    <div class="relative z-10 flex flex-col h-full justify-between">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-white backdrop-blur-sm border border-white/10">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <span class="text-xs font-bold text-gray-300 uppercase tracking-widest">Total Órdenes</span>
                        </div>
                        <div>
                            <h3 class="text-5xl font-raleway font-black text-white tracking-tighter" x-data x-init="animateCountTo($el, {{ $kpis['total'] }})">0</h3>
                            <div class="w-full bg-white/20 h-1 mt-4 rounded-full overflow-hidden">
                                <div class="h-full bg-[#ff9c00] w-full"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative bg-white rounded-[2rem] p-8 overflow-hidden shadow-lg shadow-gray-100 border border-gray-100 group transition-all duration-300 hover:-translate-y-2 hover:shadow-xl">
                    <div class="absolute -right-6 -top-6 w-32 h-32 bg-orange-50 rounded-full blur-2xl group-hover:bg-orange-100 transition-colors"></div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-1">Requiere Atención</p>
                                <h4 class="text-lg font-bold text-gray-700">Pendientes</h4>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center text-xl group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <h3 class="text-4xl font-raleway font-black text-[#2c3856]" x-data x-init="animateCountTo($el, {{ $kpis['pending'] }})">0</h3>
                            <span class="text-xs font-bold text-orange-500 bg-orange-50 px-2 py-1 rounded-full">
                                {{ $kpis['total'] > 0 ? round(($kpis['pending'] / $kpis['total']) * 100) : 0 }}%
                            </span>
                        </div>
                    </div>
                </div>

                <div class="relative bg-white rounded-[2rem] p-8 overflow-hidden shadow-lg shadow-gray-100 border border-gray-100 group transition-all duration-300 hover:-translate-y-2 hover:shadow-xl">
                    <div class="absolute -right-6 -top-6 w-32 h-32 bg-blue-50 rounded-full blur-2xl group-hover:bg-blue-100 transition-colors"></div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-1">En Proceso</p>
                                <h4 class="text-lg font-bold text-gray-700">Picking</h4>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-dolly"></i>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <h3 class="text-4xl font-raleway font-black text-[#2c3856]" x-data x-init="animateCountTo($el, {{ $kpis['picking'] }})">0</h3>
                            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded-full">
                                {{ $kpis['total'] > 0 ? round(($kpis['picking'] / $kpis['total']) * 100) : 0 }}%
                            </span>
                        </div>
                    </div>
                </div>

                <div class="relative bg-white rounded-[2rem] p-8 overflow-hidden shadow-lg shadow-gray-100 border border-gray-100 group transition-all duration-300 hover:-translate-y-2 hover:shadow-xl">
                    <div class="absolute -right-6 -top-6 w-32 h-32 bg-emerald-50 rounded-full blur-2xl group-hover:bg-emerald-100 transition-colors"></div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-1">Finalizadas</p>
                                <h4 class="text-lg font-bold text-gray-700">Empacadas</h4>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <h3 class="text-4xl font-raleway font-black text-[#2c3856]" x-data x-init="animateCountTo($el, {{ $kpis['packed'] }})">0</h3>
                            <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">
                                {{ $kpis['total'] > 0 ? round(($kpis['packed'] / $kpis['total']) * 100) : 0 }}%
                            </span>
                        </div>
                    </div>
                </div>

            </div>

            <div class="glass-panel rounded-[2.5rem] p-8 mb-10 stagger-enter" style="animation-delay: 0.3s;">
                <form method="GET" action="{{ route('wms.sales-orders.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8 gap-6 items-end">
                    
                    <div class="xl:col-span-1">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Almacén</label>
                        <select name="warehouse_id" class="input-arch input-arch-select" onchange="this.form.submit()">
                            <option value="">Todos</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @selected($warehouseId == $warehouse->id)>{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="xl:col-span-1">
                        <label class="block text-[10px] font-bold text-[#ff9c00] uppercase tracking-widest mb-1">Área / Cliente</label>
                        <select name="area_id" class="input-arch input-arch-select text-[#ff9c00] font-bold" onchange="this.form.submit()">
                            <option value="">Todas</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}" @selected($areaId == $area->id)>{{ $area->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="xl:col-span-1">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Nº Orden</label>
                        <input type="text" name="so_number" value="{{ request('so_number') }}" class="input-arch" placeholder="SO-...">
                    </div>

                    <div class="xl:col-span-1">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Cliente</label>
                        <input type="text" name="customer_name" value="{{ request('customer_name') }}" class="input-arch" placeholder="Nombre...">
                    </div>

                    <div class="xl:col-span-1">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Estatus</label>
                        <select name="status" class="input-arch input-arch-select" onchange="this.form.submit()">
                            <option value="">Todos</option>
                            <option value="Pending" @selected(request('status') == 'Pending')>Pendiente</option>
                            <option value="Picking" @selected(request('status') == 'Picking')>En Surtido</option>
                            <option value="Packed" @selected(request('status') == 'Packed')>Empacado</option>
                            <option value="Cancelled" @selected(request('status') == 'Cancelled')>Cancelado</option>
                        </select>
                    </div>

                    <div class="xl:col-span-1">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Desde</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="input-arch text-gray-500 text-xs">
                    </div>

                    <div class="xl:col-span-1">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Hasta</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="input-arch text-gray-500 text-xs">
                    </div>

                    <div class="xl:col-span-1 pb-1">
                        <div class="flex gap-2">
                            <button type="submit" class="btn-nexus w-full py-3 text-[10px] uppercase tracking-widest shadow-md">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="{{ route('wms.sales-orders.index') }}" class="btn-ghost w-full py-3 text-[10px] uppercase tracking-widest flex items-center justify-center">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="stagger-enter" style="animation-delay: 0.4s;">
                
                <div class="grid grid-cols-1 gap-6 md:hidden">
                    @forelse ($salesOrders as $so)
                        <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100 relative overflow-hidden">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <span class="inline-block px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider mb-2
                                        @if($so->status == 'Pending') bg-yellow-100 text-yellow-700
                                        @elseif($so->status == 'Picking') bg-blue-100 text-blue-700
                                        @elseif($so->status == 'Packed') bg-green-100 text-green-700
                                        @elseif($so->status == 'Cancelled') bg-red-100 text-red-700
                                        @else bg-gray-100 text-gray-600 @endif">
                                        {{ $so->status }}
                                    </span>
                                    <h3 class="text-xl font-black text-[#2c3856]">{{ $so->so_number }}</h3>
                                    <p class="text-xs text-gray-400 font-mono">{{ $so->invoice_number ?? 'Sin Factura' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] font-bold text-gray-400 uppercase">Entrega</p>
                                    <p class="font-bold text-[#2c3856]">{{ $so->order_date->format('d M') }}</p>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Cliente</p>
                                <p class="font-bold text-gray-700">{{ Str::limit($so->customer_name, 30) }}</p>
                                @if($so->area)
                                    <span class="text-[10px] text-blue-500 font-bold bg-blue-50 px-2 py-0.5 rounded mt-1 inline-block">{{ $so->area->name }}</span>
                                @endif
                            </div>

                            <div class="flex justify-between items-center py-3 border-t border-b border-gray-50 mb-4">
                                <div class="text-center">
                                    <p class="text-lg font-black text-[#2c3856]">{{ $so->lines_count }}</p>
                                    <p class="text-[10px] text-gray-400 uppercase">Líneas</p>
                                </div>
                                <div class="w-px h-8 bg-gray-100"></div>
                                <div class="text-center">
                                    <p class="text-lg font-black text-[#ff9c00]">{{ number_format($so->lines_sum_quantity_ordered) }}</p>
                                    <p class="text-[10px] text-gray-400 uppercase">Unidades</p>
                                </div>
                            </div>

                            <a href="{{ route('wms.sales-orders.show', $so) }}" class="btn-nexus w-full py-3 text-xs uppercase tracking-widest shadow-md">
                                Gestionar Orden
                            </a>
                        </div>
                    @empty
                        <div class="text-center py-10">
                            <p class="text-gray-400 text-sm">No se encontraron órdenes.</p>
                        </div>
                    @endforelse
                </div>

                <div class="hidden md:block overflow-x-auto custom-scrollbar pb-4">
                    <table class="nexus-table min-w-full">
                        <thead>
                            <tr>
                                <th>Orden</th>
                                <th>Cliente</th>
                                <th>Área</th>
                                <th>Entrega</th>
                                <th>Estatus</th>
                                <th>Items / Unidades</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($salesOrders as $so)
                                <tr class="nexus-row group">
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-md font-bold text-sm transition-transform group-hover:scale-110 shrink-0
                                                @if($so->status == 'Pending') bg-yellow-500
                                                @elseif($so->status == 'Picking') bg-blue-500
                                                @elseif($so->status == 'Packed') bg-green-500
                                                @elseif($so->status == 'Cancelled') bg-red-500
                                                @else bg-gray-500 @endif">
                                                SO
                                            </div>
                                            <div>
                                                <p class="font-black text-[#2c3856] text-lg group-hover:text-[#ff9c00] transition-colors">{{ $so->so_number }}</p>
                                                <p class="text-xs text-gray-400 font-mono">{{ $so->invoice_number ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-sm font-bold text-gray-600 truncate max-w-[180px]">{{ $so->customer_name }}</p>
                                    </td>
                                    <td>
                                        @if($so->area)
                                            <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider border border-blue-100 whitespace-nowrap">{{ $so->area->name }}</span>
                                        @else
                                            <span class="text-gray-300 text-xs italic">General</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2 text-gray-500 text-sm font-mono font-bold whitespace-nowrap">
                                            <i class="far fa-calendar-alt text-[#ff9c00]"></i>
                                            {{ $so->order_date->format('d M Y') }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border whitespace-nowrap
                                            @if($so->status == 'Pending') bg-yellow-50 text-yellow-700 border-yellow-100
                                            @elseif($so->status == 'Picking') bg-blue-50 text-blue-700 border-blue-100
                                            @elseif($so->status == 'Packed') bg-green-50 text-green-700 border-green-100
                                            @elseif($so->status == 'Cancelled') bg-red-50 text-red-700 border-red-100
                                            @else bg-gray-50 text-gray-600 border-gray-100 @endif">
                                            {{ $so->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-sm whitespace-nowrap">
                                            <span class="font-black text-[#2c3856]">{{ $so->lines_count }}</span> líneas
                                            <span class="text-gray-300 mx-1">|</span>
                                            <span class="font-bold text-[#ff9c00]">{{ number_format($so->lines_sum_quantity_ordered) }}</span> pzas.
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('wms.sales-orders.show', $so) }}" class="btn-ghost px-5 py-2 text-xs uppercase tracking-widest border-indigo-100 text-indigo-600 hover:bg-indigo-50 hover:border-indigo-200 whitespace-nowrap transition-colors">
                                            Gestionar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-12">
                                        <div class="flex flex-col items-center justify-center opacity-50">
                                            <i class="fas fa-search text-5xl mb-4 text-gray-300"></i>
                                            <p class="text-gray-500 font-medium text-base">No se encontraron órdenes con estos filtros.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="mt-8 pb-8">
                {{ $salesOrders->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    <script>
        function animateCountTo(el, target) {
            let current = 0;
            const duration = 1500; 
            const step = Math.ceil(target / (duration / 16)); 
            
            if (target === 0) {
                el.innerText = '0';
                return;
            }

            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    el.innerText = new Intl.NumberFormat().format(target);
                    clearInterval(timer);
                } else {
                    el.innerText = new Intl.NumberFormat().format(current);
                }
            }, 16);
        }
    </script>
</x-app-layout>