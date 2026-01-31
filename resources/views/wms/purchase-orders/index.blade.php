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
            padding: 0.6rem 0; font-family: 'Montserrat', sans-serif; font-weight: 600; color: #2c3856;
            transition: all 0.3s ease; width: 100%; font-size: 1rem;
        }
        .input-arch:focus { border-bottom-color: #ff9c00; box-shadow: none; outline: none; }
        .input-arch-select { background-image: none; cursor: pointer; padding-right: 1.5rem; }

        .btn-nexus { 
            background: #2c3856; color: white; border-radius: 0.8rem; font-weight: 700; 
            transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center;
            font-size: 0.95rem;
        }
        .btn-nexus:hover { background: #1a253a; transform: translateY(-2px); box-shadow: 0 5px 15px -5px rgba(44, 56, 86, 0.3); }
        
        .btn-ghost {
            background: transparent; color: #2c3856; border: 2px solid #e5e7eb; border-radius: 0.8rem; font-weight: 700;
            font-size: 0.95rem;
        }
        .btn-ghost:hover { border-color: #2c3856; background: #2c3856; color: white; }

        .nexus-table { width: 100%; border-collapse: separate; border-spacing: 0 0.8rem; }
        .nexus-table thead th {
            font-size: 0.85rem;
            text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; font-weight: 800;
            padding: 0 1.5rem 0.5rem 1.5rem; text-align: left;
        }
        
        .nexus-row-main td {
            background: white; padding: 1.25rem 1.5rem; vertical-align: middle;
            border-top: 1px solid #f3f4f6; border-bottom: 1px solid #f3f4f6; transition: all 0.2s;
        }
        .nexus-row-main td:first-child { border-left: 1px solid #f3f4f6; border-top-left-radius: 1rem; border-bottom-left-radius: 1rem; }
        .nexus-row-main td:last-child { border-right: 1px solid #f3f4f6; border-top-right-radius: 1rem; border-bottom-right-radius: 1rem; }
        .nexus-body:hover .nexus-row-main td { border-color: #e5e7eb; background: #fafafa; }
        
        .nexus-row-detail td { padding: 0; border: none; }
        .detail-content {
            background: #f8fafc; margin: 0 1rem 1rem 1rem; border-radius: 1rem; padding: 2rem; /* Padding aumentado */
            border: 1px solid #e2e8f0; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
        }

        .mobile-card {
            background: white; border-radius: 1.5rem; border: 1px solid #f3f4f6; padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02); margin-bottom: 1rem; position: relative; overflow: hidden;
        }

        [x-cloak] { display: none !important; }
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20 relative overflow-hidden">
        
        <div class="fixed inset-0 -z-10 pointer-events-none">
            <div class="absolute top-0 left-0 w-full h-full bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-10"></div>
            <div class="absolute top-[-20%] right-[-10%] w-[50rem] h-[50rem] bg-gradient-to-b from-[#2c3856]/5 to-transparent rounded-full blur-[120px]"></div>
        </div>

        <div class="max-w-[1920px] mx-auto px-6 pt-10 relative z-10">
            
            <div class="flex flex-col xl:flex-row justify-between items-end mb-12 stagger-enter" style="animation-delay: 0.1s;">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-12 h-1 bg-[#ff9c00]"></span>
                        <span class="text-sm font-bold text-[#2c3856] tracking-[0.3em] uppercase">Logística Inbound</span>
                    </div>
                    <h1 class="text-5xl md:text-7xl font-raleway font-black text-[#2c3856] leading-none">
                        DASHBOARD DE <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-600">ARRIBOS</span>
                    </h1>
                </div>

                <div class="flex flex-wrap gap-4 mt-6 xl:mt-0 items-center">
                    <a href="{{ route('wms.dashboard') }}" class="flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-[#666666] font-bold rounded-full shadow-sm hover:shadow-md hover:border-[#2c3856] hover:text-[#2c3856] transition-all">
                        <i class="fas fa-arrow-left"></i> <span>Dashboard</span>
                    </a>                    
                    <a href="{{ route('wms.purchase-orders.create') }}" class="btn-nexus px-8 py-4 h-14 shadow-lg shadow-[#2c3856]/20 text-base">
                        <i class="fas fa-plus-circle mr-2"></i> Nueva Orden
                    </a>
                    <a href="{{ route('wms.purchase-orders.export-csv', request()->query()) }}" class="btn-ghost px-6 py-3 h-14 flex items-center gap-2 text-sm uppercase tracking-wider font-bold">
                        <i class="fas fa-file-excel"></i> Exportar
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12 stagger-enter" style="animation-delay: 0.15s;">
                <div class="bg-white rounded-[2rem] p-8 border border-gray-100 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                    <div class="absolute right-0 top-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity"><i class="fas fa-truck-loading text-5xl text-blue-600"></i></div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">En Recepción</p>
                    <p class="text-5xl font-raleway font-black text-blue-600">{{ $kpis['receiving'] }}</p>
                </div>
                <div class="bg-white rounded-[2rem] p-8 border border-gray-100 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                    <div class="absolute right-0 top-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity"><i class="fas fa-calendar-day text-5xl text-[#2c3856]"></i></div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Arribos Hoy</p>
                    <p class="text-5xl font-raleway font-black text-[#2c3856]">{{ $kpis['arrivals_today'] }}</p>
                </div>
                <div class="bg-white rounded-[2rem] p-8 border border-gray-100 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                    <div class="absolute right-0 top-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity"><i class="fas fa-clock text-5xl text-[#ff9c00]"></i></div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Pendientes</p>
                    <p class="text-5xl font-raleway font-black text-[#ff9c00]">{{ $kpis['pending'] }}</p>
                </div>
                <div class="bg-white rounded-[2rem] p-8 border border-gray-100 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                    <div class="absolute right-0 top-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity"><i class="fas fa-hourglass-half text-5xl text-green-600"></i></div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Tiempo Promedio</p>
                    <p class="text-5xl font-raleway font-black text-green-600">{{ $kpis['avg_unload_time'] }} <span class="text-lg font-bold text-gray-400">min</span></p>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] p-10 border border-gray-100 shadow-lg mb-12 stagger-enter" style="animation-delay: 0.2s;">
                <form action="{{ route('wms.purchase-orders.index') }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-x-8 gap-y-8 items-end">
                        <div>
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest block mb-2">Almacén</label>
                            <select name="warehouse_id" onchange="this.form.submit()" class="input-arch input-arch-select font-bold">
                                <option value="">Global</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" @selected($warehouseId == $warehouse->id)>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="text-xs font-bold text-[#ff9c00] uppercase tracking-widest block mb-2">Cliente / Área</label>
                            <select name="area_id" onchange="this.form.submit()" class="input-arch input-arch-select text-[#ff9c00] font-bold">
                                <option value="">Todas</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}" @selected($areaId == $area->id)>{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest block mb-2">Orden / Contenedor</label>
                            <input type="text" name="search" value="{{ request('search') }}" class="input-arch font-medium" placeholder="Buscar..." onchange="this.form.submit()">
                        </div>

                        <div>
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest block mb-2">SKU Producto</label>
                            <input type="text" name="sku" value="{{ request('sku') }}" class="input-arch font-medium" placeholder="Código..." onchange="this.form.submit()">
                        </div>

                        <div>
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest block mb-2">Estatus</label>
                            <select name="status" onchange="this.form.submit()" class="input-arch input-arch-select font-medium">
                                <option value="">Todos</option>
                                <option value="Pending" @selected(request('status') == 'Pending')>Pendiente</option>
                                <option value="Arrived" @selected(request('status') == 'Arrived')>En Patio</option>
                                <option value="Receiving" @selected(request('status') == 'Receiving')>En Recepción</option>
                                <option value="Completed" @selected(request('status') == 'Completed')>Completado</option>
                            </select>
                        </div>

                        <div>
                            <a href="{{ route('wms.purchase-orders.index') }}" class="flex items-center justify-center w-full py-3 text-xs font-bold text-gray-400 hover:text-red-500 uppercase tracking-widest transition-colors border-b-2 border-transparent hover:border-red-200">
                                <i class="fas fa-undo mr-2"></i> Limpiar Filtros
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="hidden md:block overflow-x-auto pb-24 stagger-enter" style="animation-delay: 0.3s;">
                <table class="nexus-table">
                    <thead>
                        <tr>
                            <th class="w-12 text-center"></th> <th class="text-left">Orden / Cliente</th>
                            <th class="text-left">Referencia</th>
                            <th class="text-left">Fechas</th>
                            <th class="text-left">Transporte</th>
                            <th class="text-center">Progreso</th>
                            <th class="text-center">Estatus</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    
                    @forelse ($purchaseOrders as $order)
                        <tbody class="nexus-body" x-data="{ expanded: false }">
                            <tr class="nexus-row-main cursor-pointer" @click="expanded = !expanded">
                                <td class="text-center text-gray-400">
                                    <i class="fas fa-lg transition-transform duration-300" :class="expanded ? 'fa-chevron-down rotate-180' : 'fa-chevron-right'"></i>
                                </td>
                                
                                <td class="text-left">
                                    <div class="flex flex-col gap-1">
                                        <span class="font-mono font-black text-2xl text-[#2c3856]">{{ $order->po_number }}</span>
                                        <span class="inline-flex items-center w-fit px-3 py-1 rounded text-xs font-bold bg-[#fff8e6] text-[#b36b00] border border-[#ff9c00]/20 uppercase tracking-wide">
                                            {{ $order->area->name ?? 'N/A' }}
                                        </span>
                                    </div>
                                </td>
                                
                                <td class="text-left">
                                    <div class="text-base font-bold text-gray-700">{{ $order->container_number ?? 'S/C' }}</div>
                                    <div class="text-xs text-gray-400 font-mono mt-1">{{ $order->document_invoice ?? '-' }}</div>
                                </td>

                                <td class="text-left">
                                    <div class="flex flex-col gap-1">
                                        @if(\Carbon\Carbon::parse($order->expected_date)->isPast() && $order->status != 'Completed')
                                            <div class="text-sm text-red-500 font-bold flex items-center gap-2">
                                                <i class="fas fa-exclamation-circle"></i> 
                                                {{ \Carbon\Carbon::parse($order->expected_date)->format('d M') }}
                                            </div>
                                        @else
                                            <div class="text-sm text-gray-500">
                                                <span class="font-bold">Esp:</span> {{ \Carbon\Carbon::parse($order->expected_date)->format('d M') }}
                                            </div>
                                        @endif
                                        <div class="text-sm text-[#2c3856]">
                                            <span class="font-bold">Arr:</span> 
                                            {{ $order->download_start_time ? \Carbon\Carbon::parse($order->download_start_time)->format('d M H:i') : '--' }}
                                        </div>
                                    </div>
                                </td>

                                <td class="text-left">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-bold text-gray-700 truncate max-w-[180px]">{{ $order->latestArrival->driver_name ?? 'Sin Registro' }}</span>
                                        <span class="text-xs font-mono text-gray-500 bg-gray-50 px-2 py-0.5 rounded w-fit">
                                            {{ $order->latestArrival->truck_plate ?? '---' }}
                                        </span>
                                    </div>
                                </td>

                                <td class="w-48 text-center">
                                    @php $progress = $order->expected_bottles > 0 ? ($order->received_bottles / $order->expected_bottles) * 100 : 0; @endphp
                                    <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden mb-2">
                                        <div class="h-full bg-blue-500 transition-all duration-500" style="width: {{ $progress }}%"></div>
                                    </div>
                                    <div class="flex justify-between text-[10px] font-bold text-gray-400 uppercase">
                                        <span>{{ number_format($order->pallets_count) }} PLT</span>
                                        <span>{{ round($progress) }}%</span>
                                    </div>
                                </td>

                                <td class="text-center">
                                    @php
                                        $statusClass = match($order->status) {
                                            'Pending' => 'bg-gray-100 text-gray-500',
                                            'Arrived' => 'bg-blue-50 text-blue-600',
                                            'Receiving' => 'bg-orange-50 text-orange-600',
                                            'Completed' => 'bg-green-50 text-green-600',
                                            default => 'bg-gray-100 text-gray-600'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide {{ $statusClass }}">
                                        {{ $order->status_in_spanish }}
                                    </span>
                                </td>

                                <td class="text-right">
                                    <a href="{{ route('wms.purchase-orders.show', $order) }}" @click.stop class="btn-nexus px-5 py-2.5 text-xs uppercase tracking-widest shadow-sm">
                                        Gestionar
                                    </a>
                                </td>
                            </tr>
                            
                            <tr x-show="expanded" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="nexus-row-detail">
                                <td colspan="8">
                                    <div class="detail-content">
                                        <div class="grid grid-cols-5 gap-8 mb-8 border-b border-gray-200 pb-8">
                                            <div>
                                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Inicio Descarga</p>
                                                <p class="font-mono text-base font-bold text-[#2c3856]">{{ $order->download_start_time ? \Carbon\Carbon::parse($order->download_start_time)->format('h:i A') : '--:--' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Fin Descarga</p>
                                                <p class="font-mono text-base font-bold text-[#2c3856]">{{ $order->download_end_time ? \Carbon\Carbon::parse($order->download_end_time)->format('h:i A') : '--:--' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Unidades Recibidas</p>
                                                <p class="text-base font-bold text-[#2c3856]">{{ number_format($order->received_bottles) }} <span class="text-gray-400 text-sm font-normal">/ {{ number_format($order->expected_bottles) }}</span></p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Cajas Recibidas</p>
                                                <p class="text-base font-bold text-blue-600">{{ number_format($order->total_cases_received, 0) }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Tarimas Recibidas</p>
                                                <p class="text-base font-bold text-[#ff9c00]">{{ number_format($order->pallets_count, 0) }}</p>
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Productos Solicitados</p>
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                @foreach($order->lines as $line)
                                                    <div class="flex justify-between items-center bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                                                        <div class="overflow-hidden">
                                                            <span class="block font-mono font-bold text-[#2c3856] text-sm">{{ $line->product->sku }}</span>
                                                            <span class="block text-gray-500 truncate text-xs mt-1" title="{{ $line->product->name }}">{{ $line->product->name }}</span>
                                                        </div>
                                                        <span class="ml-3 font-bold bg-blue-50 text-blue-700 px-3 py-1.5 rounded text-sm">x{{ $line->quantity_ordered }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @empty
                        <tbody>
                            <tr>
                                <td colspan="8" class="text-center py-16">
                                    <div class="inline-block p-6 rounded-full bg-gray-50 mb-4">
                                        <i class="fas fa-inbox text-gray-300 text-4xl"></i>
                                    </div>
                                    <p class="text-gray-500 font-bold text-lg">No se encontraron órdenes de compra.</p>
                                </td>
                            </tr>
                        </tbody>
                    @endforelse
                </table>
            </div>

            <div class="md:hidden pb-20">
                @forelse ($purchaseOrders as $order)
                    <div x-data="{ expanded: false }" class="mobile-card">
                        <div @click="expanded = !expanded" class="cursor-pointer">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <p class="font-mono font-black text-2xl text-[#2c3856]">{{ $order->po_number }}</p>
                                    <p class="text-sm text-gray-500 font-bold mt-1">{{ $order->container_number ?? 'S/C' }}</p>
                                    <span class="inline-block mt-2 px-3 py-1 rounded text-xs font-bold bg-[#fff8e6] text-[#b36b00] border border-[#ff9c00]/20 uppercase">
                                        {{ $order->area->name ?? 'N/A' }}
                                    </span>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    @php
                                        $statusClass = match($order->status) {
                                            'Pending' => 'bg-gray-100 text-gray-500',
                                            'Arrived' => 'bg-blue-50 text-blue-600',
                                            'Receiving' => 'bg-orange-50 text-orange-600',
                                            'Completed' => 'bg-green-50 text-green-600',
                                            default => 'bg-gray-100 text-gray-600'
                                        };
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ $statusClass }}">
                                        {{ $order->status_in_spanish }}
                                    </span>
                                    <i class="fas text-gray-300 transition-transform duration-300 mt-2 text-lg" :class="expanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 text-sm mb-4 border-t border-gray-100 pt-4">
                                <div>
                                    <p class="text-xs font-bold text-gray-400 uppercase">Fecha Esp.</p>
                                    <p class="font-bold text-[#2c3856]">{{ \Carbon\Carbon::parse($order->expected_date)->format('d M Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-400 uppercase">Transporte</p>
                                    <p class="font-mono text-[#2c3856] truncate">{{ $order->latestArrival->truck_plate ?? '---' }}</p>
                                </div>
                            </div>

                            @php $progress = $order->expected_bottles > 0 ? ($order->received_bottles / $order->expected_bottles) * 100 : 0; @endphp
                            <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden mb-2">
                                <div class="h-full bg-blue-500" style="width: {{ $progress }}%"></div>
                            </div>
                            <div class="flex justify-between text-xs font-bold text-gray-400 uppercase">
                                <span>Rec: {{ number_format($order->received_bottles) }}</span>
                                <span>{{ round($progress) }}%</span>
                            </div>
                        </div>

                        <div x-show="expanded" x-transition class="mt-6 pt-6 border-t border-gray-100 space-y-6">
                            <div class="grid grid-cols-2 gap-y-6 gap-x-4 text-sm">
                                <div><p class="text-gray-400 font-bold uppercase text-xs">Llegada</p><p class="font-mono text-[#2c3856] font-bold">{{ $order->download_start_time ? \Carbon\Carbon::parse($order->download_start_time)->format('d/m H:i') : '--' }}</p></div>
                                <div><p class="text-gray-400 font-bold uppercase text-xs">Operador</p><p class="text-[#2c3856] truncate font-bold">{{ $order->latestArrival->driver_name ?? '-' }}</p></div>
                                <div><p class="text-gray-400 font-bold uppercase text-xs">Cajas Totales</p><p class="font-bold text-blue-600 text-lg">{{ number_format($order->total_cases_received, 0) }}</p></div>
                                <div><p class="text-gray-400 font-bold uppercase text-xs">Tarimas</p><p class="font-bold text-[#ff9c00] text-lg">{{ $order->pallets_count }}</p></div>
                            </div>
                            
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase mb-3">Productos</p>
                                <ul class="space-y-2">
                                    @foreach($order->lines as $line)
                                        <li class="text-sm flex justify-between items-center bg-gray-50 p-3 rounded-xl">
                                            <div class="overflow-hidden mr-3">
                                                <span class="block font-mono font-bold text-[#2c3856]">{{ $line->product->sku }}</span>
                                                <span class="block text-gray-500 truncate text-xs">{{ $line->product->name }}</span>
                                            </div>
                                            <span class="text-gray-600 font-bold whitespace-nowrap bg-white px-2 py-1 rounded border border-gray-200">x{{ $line->quantity_ordered }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <div class="pt-4 text-right">
                                <a href="{{ route('wms.purchase-orders.show', $order) }}" class="btn-nexus px-6 py-3 text-sm uppercase tracking-widest w-full shadow-md">
                                    Gestionar Orden
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10">
                        <p class="text-gray-400 font-bold text-lg">No hay órdenes disponibles.</p>
                    </div>
                @endforelse
            </div>

            <div class="pb-20">
                {{ $purchaseOrders->appends(request()->query())->links() }}
            </div>

        </div>
    </div>
</x-app-layout>