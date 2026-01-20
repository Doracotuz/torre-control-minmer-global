@php
    $filterAreas = [];
    if(Auth::user()->isSuperAdmin()) {
        $filterAreas = \App\Models\Area::orderBy('name')->get();
    }
@endphp

<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="bg-[#E8ECF7] min-h-screen py-8 font-sans">
        <div class="max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="flex flex-col md:flex-row justify-between items-end md:items-center gap-4 mb-8">
                <div>
                    <a href="{{ route('ff.dashboard.index') }}" class="inline-flex items-center text-xs font-bold text-gray-400 hover:text-[#2c3856] mb-2 transition-colors">
                        <i class="fas fa-arrow-left mr-1"></i> Volver al Dashboard
                    </a>
                    <h2 class="font-bold text-3xl text-[#2c3856] leading-tight font-[Montserrat]">
                        <i class="fas fa-clipboard-list mr-2 text-[#ff9c00]"></i> Monitor de Pedidos
                    </h2>
                    <p class="text-sm text-gray-500 font-[Montserrat] mt-1 ml-1">Seguimiento de estatus y autorizaciones</p>
                </div>
                
                <div class="flex gap-3">
                    <a href="{{ route('ff.sales.index') }}" 
                       class="inline-flex items-center px-6 py-3 bg-[#ff9c00] text-white rounded-2xl text-sm font-bold shadow-lg hover:bg-orange-600 hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300">
                        <i class="fas fa-plus mr-2"></i> Crear Pedido
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 p-6 mb-8">
                <form method="GET" action="{{ route('ff.orders.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    
                    <div class="{{ Auth::user()->isSuperAdmin() ? 'md:col-span-2' : 'md:col-span-3' }} relative group">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-1 mb-1 block">Búsqueda</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-300 group-focus-within:text-[#ff9c00] transition-colors"></i>
                            </div>
                            <input type="text" name="client" value="{{ request('client') }}" 
                                   placeholder="Folio, Cliente..."
                                   class="block w-full pl-11 pr-4 py-3 bg-[#F3F4F6] border-none text-gray-700 rounded-xl focus:ring-2 focus:ring-[#ff9c00] focus:bg-white transition-all text-sm font-semibold placeholder-gray-400">
                        </div>
                    </div>

                    @if(Auth::user()->isSuperAdmin())
                    <div class="md:col-span-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-1 mb-1 block">Área</label>
                        <div class="relative">
                            <select name="area_id" class="block w-full pl-4 pr-10 py-3 bg-[#F3F4F6] border-none text-gray-700 rounded-xl focus:ring-2 focus:ring-[#ff9c00] focus:bg-white transition-all text-sm font-semibold cursor-pointer appearance-none">
                                <option value="">Todas</option>
                                @foreach($filterAreas as $area)
                                    <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="md:col-span-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-1 mb-1 block">Almacén</label>
                        <div class="relative">
                            <select name="warehouse_id" class="block w-full pl-4 pr-10 py-3 bg-[#F3F4F6] border-none text-gray-700 rounded-xl focus:ring-2 focus:ring-[#ff9c00] focus:bg-white transition-all text-sm font-semibold cursor-pointer appearance-none">
                                <option value="">Todos</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                        {{ $wh->code }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-1 mb-1 block">Estatus</label>
                        <div class="relative">
                            <select name="status" class="block w-full pl-4 pr-10 py-3 bg-[#F3F4F6] border-none text-gray-700 rounded-xl focus:ring-2 focus:ring-[#ff9c00] focus:bg-white transition-all text-sm font-semibold cursor-pointer appearance-none">
                                <option value="">Todos</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Aprobado</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rechazado</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-1 mb-1 block">Tipo</label>
                        <div class="relative">
                            <select name="type" class="block w-full pl-4 pr-10 py-3 bg-[#F3F4F6] border-none text-gray-700 rounded-xl focus:ring-2 focus:ring-[#ff9c00] focus:bg-white transition-all text-sm font-semibold cursor-pointer appearance-none">
                                <option value="">Todos</option>
                                <option value="normal" {{ request('type') == 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="remision" {{ request('type') == 'remision' ? 'selected' : '' }}>Remisión</option>
                                <option value="prestamo" {{ request('type') == 'prestamo' ? 'selected' : '' }}>Préstamo</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>

                    <div class="{{ Auth::user()->isSuperAdmin() ? 'md:col-span-2' : 'md:col-span-3' }} grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-1 mb-1 block">Desde</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="block w-full px-2 py-3 bg-[#F3F4F6] border-none text-gray-700 rounded-xl focus:ring-2 focus:ring-[#ff9c00] focus:bg-white transition-all text-xs font-semibold">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider ml-1 mb-1 block">Hasta</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="block w-full px-2 py-3 bg-[#F3F4F6] border-none text-gray-700 rounded-xl focus:ring-2 focus:ring-[#ff9c00] focus:bg-white transition-all text-xs font-semibold">
                        </div>
                    </div>
                    
                    <div class="md:col-span-1 flex items-center justify-center pb-3">
                        <label class="inline-flex items-center cursor-pointer" title="Mostrar solo pedidos con backorders activos">
                            <input type="checkbox" name="show_backorders" value="1" {{ request('show_backorders') ? 'checked' : '' }} class="rounded border-gray-300 text-[#2c3856] shadow-sm focus:border-[#2c3856] focus:ring focus:ring-[#2c3856] focus:ring-opacity-50">
                            <span class="ml-2 text-xs font-bold text-gray-500 hidden md:inline">Backorders</span>
                            <span class="ml-2 text-xs font-bold text-gray-500 md:hidden">Solo Backorders</span>
                        </label>
                    </div>

                    <div class="md:col-span-1">
                        <button type="submit" class="w-full bg-[#2c3856] text-white font-bold py-3 rounded-xl hover:bg-[#1e273d] shadow-md transition-all text-sm">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100">
                                <th class="px-6 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider font-[Montserrat]">Folio</th>
                                
                                @if(Auth::user()->isSuperAdmin())
                                    <th class="px-6 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider font-[Montserrat]">Área</th>
                                @endif

                                <th class="px-6 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider font-[Montserrat]">Almacén</th>
                                <th class="px-6 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider font-[Montserrat] text-center">Estatus</th>
                                <th class="px-6 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider font-[Montserrat]">Cliente</th>
                                <th class="px-6 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider font-[Montserrat]">Tipo</th>
                                <th class="px-6 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider font-[Montserrat] text-center">Fecha Entrega</th>
                                <th class="px-6 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider font-[Montserrat] text-center">Items</th>
                                <th class="px-6 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider font-[Montserrat] text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($orders as $order)
                                <tr class="hover:bg-blue-50/30 transition-colors duration-200 group">
                                    
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 rounded-xl bg-[#E8ECF7] text-[#2c3856] flex items-center justify-center font-bold text-sm mr-3">
                                                #
                                            </div>
                                            <span class="font-bold text-[#2c3856] text-lg">{{ $order->folio }}</span>
                                        </div>
                                    </td>

                                    @if(Auth::user()->isSuperAdmin())
                                        <td class="px-6 py-4">
                                            @if($order->area)
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                                    <i class="fas fa-building text-[10px]"></i>
                                                    {{ $order->area->name }}
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400 italic">N/A</span>
                                            @endif
                                        </td>
                                    @endif

                                    <td class="px-6 py-4">
                                        @if($order->warehouse)
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                                <i class="fas fa-warehouse text-[10px]"></i> {{ $order->warehouse->code }}
                                            </span>
                                        @else
                                            <span class="text-xs font-bold text-slate-400 italic">General</span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <div class="flex flex-col gap-1 items-center">
                                            @if($order->status == 'pending')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-600 border border-amber-100 w-fit">
                                                    <i class="fas fa-clock mr-1.5"></i> Pendiente
                                                </span>
                                            @elseif($order->status == 'approved')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-600 border border-emerald-100 w-fit">
                                                    <i class="fas fa-check-circle mr-1.5"></i> Aprobado
                                                </span>
                                            @elseif($order->status == 'rejected')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-50 text-red-600 border border-red-100 w-fit">
                                                    <i class="fas fa-times-circle mr-1.5"></i> Rechazado
                                                </span>
                                            @endif

                                            @if($order->has_active_backorder)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-700 border border-purple-200 mt-1 w-fit animate-pulse">
                                                    <i class="fas fa-history mr-1"></i> En Backorder
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-800">{{ $order->client_name }}</span>
                                            <span class="text-xs text-gray-400 mt-0.5">{{ $order->company_name }}</span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="text-xs font-bold uppercase tracking-wide
                                            {{ $order->order_type == 'normal' ? 'text-blue-600' : '' }}
                                            {{ $order->order_type == 'remision' ? 'text-teal-600' : '' }}
                                            {{ $order->order_type == 'prestamo' ? 'text-purple-600' : '' }}">
                                            {{ $order->order_type }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <div class="text-sm font-medium text-gray-600">
                                            {{ $order->delivery_date ? $order->delivery_date->format('d M, Y') : 'N/A' }}
                                        </div>
                                        <div class="text-[10px] text-gray-400">
                                            {{ $order->delivery_date ? $order->delivery_date->format('H:i A') : '' }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <span class="bg-gray-100 text-gray-600 py-1 px-2.5 rounded-lg text-xs font-bold border border-gray-200">
                                            {{ $order->total_items }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('ff.orders.show', $order->folio) }}" 
                                           class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white border border-gray-200 text-gray-400 hover:text-[#2c3856] hover:border-[#2c3856] hover:bg-gray-50 transition-all shadow-sm group-hover:scale-105"
                                           title="Ver Detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ Auth::user()->isSuperAdmin() ? '9' : '8' }}" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="bg-gray-50 rounded-full p-6 mb-4 shadow-inner">
                                                <i class="fas fa-folder-open text-gray-300 text-3xl"></i>
                                            </div>
                                            <h3 class="text-lg font-bold text-[#2c3856]">Sin Resultados</h3>
                                            <p class="text-gray-500 text-sm mt-1">No hay pedidos que coincidan con los filtros.</p>
                                            <a href="{{ route('ff.orders.index') }}" class="mt-4 text-[#ff9c00] hover:text-orange-600 font-bold text-sm underline">
                                                Limpiar filtros
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
                    {{ $orders->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>