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
            padding: 0.5rem 0; font-family: 'Montserrat', sans-serif; font-weight: 600; color: #2c3856;
            transition: all 0.3s ease; width: 100%; font-size: 0.9rem;
        }
        .input-arch:focus { border-bottom-color: #ff9c00; box-shadow: none; outline: none; }
        .input-arch-select { background-image: none; cursor: pointer; padding-right: 1.5rem; }

        .btn-nexus { background: #2c3856; color: white; border-radius: 0.8rem; font-weight: 700; transition: all 0.2s; }
        .btn-nexus:hover { background: #1a253a; transform: translateY(-1px); }
        
        .btn-ghost { background: transparent; color: #2c3856; border: 2px solid #e5e7eb; border-radius: 1rem; font-weight: 700; }
        .btn-ghost:hover { border-color: #2c3856; background: #2c3856; color: white; }

        .nexus-table { width: 100%; border-collapse: separate; border-spacing: 0 0.5rem; }
        .nexus-table thead th {
            font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: #9ca3af; font-weight: 800;
            padding: 1rem; text-align: left;
        }
        .nexus-row { background: white; transition: all 0.2s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
        .nexus-row td { padding: 1rem; vertical-align: middle; border-top: 1px solid #f3f4f6; border-bottom: 1px solid #f3f4f6; }
        .nexus-row td:first-child { border-top-left-radius: 1rem; border-bottom-left-radius: 1rem; border-left: 1px solid #f3f4f6; }
        .nexus-row td:last-child { border-top-right-radius: 1rem; border-bottom-right-radius: 1rem; border-right: 1px solid #f3f4f6; }
        .nexus-row:hover { box-shadow: 0 10px 30px -10px rgba(44, 56, 86, 0.08); z-index: 10; position: relative; }

        [x-cloak] { display: none !important; }
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20 relative overflow-hidden">
        
        <div class="fixed inset-0 -z-10 pointer-events-none">
            <div class="absolute top-0 left-0 w-full h-full bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-10"></div>
            <div class="absolute top-[-20%] right-[-10%] w-[50rem] h-[50rem] bg-gradient-to-b from-[#2c3856]/5 to-transparent rounded-full blur-[120px]"></div>
        </div>

        <div class="max-w-[1920px] mx-auto px-6 pt-10 relative z-10">
            
            <div class="flex flex-col xl:flex-row justify-between items-end mb-10 stagger-enter" style="animation-delay: 0.1s;">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-12 h-1 bg-[#ff9c00]"></span>
                        <span class="text-sm font-bold text-[#2c3856] tracking-[0.3em] uppercase">Trazabilidad</span>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-raleway font-black text-[#2c3856] leading-none">
                        HISTORIAL <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-600">MOVIMIENTOS</span>
                    </h1>
                </div>

                <div class="flex flex-wrap gap-3 mt-6 xl:mt-0 items-center">
                    <a href="{{ route('wms.reports.index') }}" class="flex items-center gap-2 px-5 py-3 bg-white border border-gray-200 text-[#666666] font-bold rounded-xl shadow-sm hover:shadow-md hover:border-[#2c3856] hover:text-[#2c3856] transition-all text-sm uppercase tracking-wider">
                        <i class="fas fa-arrow-left"></i> <span>Volver a Reportes</span>
                    </a>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] p-8 border border-gray-100 shadow-xl mb-10 stagger-enter" style="animation-delay: 0.2s;">
                <form method="GET" action="{{ route('wms.reports.stock-movements') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 items-end">
                    
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Almacén</label>
                        <select name="warehouse_id" class="input-arch input-arch-select text-sm">
                            <option value="">Todos</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-[#ff9c00] uppercase tracking-widest block mb-1">Área / Cliente</label>
                        <select name="area_id" class="input-arch input-arch-select text-sm text-[#ff9c00]">
                            <option value="">Todas</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}" @selected(request('area_id') == $area->id)>{{ $area->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Desde</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}" class="input-arch text-sm">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Hasta</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}" class="input-arch text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Tipo Movimiento</label>
                        <select name="movement_type" class="input-arch input-arch-select text-sm">
                            <option value="">Todos</option>
                            @foreach($movementTypes as $type)
                                <option value="{{ $type }}" @selected(request('movement_type') == $type)>{{ Str::title(str_replace('-', ' ', $type)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">SKU / Producto</label>
                        <input type="text" name="sku" value="{{ request('sku') }}" class="input-arch text-sm" placeholder="Buscar SKU...">
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">LPN</label>
                        <input type="text" name="lpn" value="{{ request('lpn') }}" class="input-arch text-sm font-mono" placeholder="Buscar LPN...">
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="btn-nexus px-6 py-3 w-full shadow-lg uppercase tracking-wider text-xs">
                            <i class="fas fa-filter mr-2"></i> Filtrar
                        </button>
                    </div>

                    <div class="flex gap-3">
                        <a href="{{ route('wms.reports.stock-movements.export', request()->query()) }}" class="btn-ghost px-4 py-3 flex-1 flex items-center justify-center border-green-200 text-green-600 hover:bg-green-50 hover:border-green-300">
                            <i class="fas fa-file-csv text-lg"></i>
                        </a>
                        <a href="{{ route('wms.reports.stock-movements') }}" class="btn-ghost px-4 py-3 flex-1 flex items-center justify-center text-gray-400 hover:text-red-500">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto pb-12 stagger-enter" style="animation-delay: 0.3s;">
                <table class="nexus-table w-full">
                    <thead>
                        <tr>
                            <th>Fecha / Hora</th>
                            <th>Usuario</th>
                            <th>Tipo</th>
                            <th>Producto</th>
                            <th>LPN / Ubicación</th>
                            <th class="text-center">Cantidad</th>
                            <th>Área</th>
                            <th>Origen (PO/Ped)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($movements as $mov)
                            <tr class="nexus-row group">
                                <td>
                                    <div class="font-bold text-[#2c3856] text-sm">{{ $mov->created_at->format('d/m/Y') }}</div>
                                    <div class="text-xs text-gray-400 font-mono">{{ $mov->created_at->format('H:i A') }}</div>
                                </td>
                                
                                <td>
                                    <div class="text-sm font-medium text-gray-700 flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-[10px] font-bold text-gray-500">
                                            {{ substr($mov->user->name ?? 'S', 0, 1) }}
                                        </div>
                                        {{ $mov->user->name ?? 'Sistema' }}
                                    </div>
                                </td>

                                <td>
                                    @php
                                        $typeColor = 'bg-blue-100 text-blue-800';
                                        if (Str::contains($mov->movement_type, ['RECEPCION', 'TRANSFER-IN', 'SPLIT-IN'])) $typeColor = 'bg-emerald-100 text-emerald-800';
                                        elseif (Str::contains($mov->movement_type, ['SALIDA', 'PICKING', 'TRANSFER-OUT', 'SPLIT-OUT'])) $typeColor = 'bg-red-100 text-red-800';
                                        elseif (Str::contains($mov->movement_type, 'AJUSTE')) $typeColor = 'bg-yellow-100 text-yellow-800';
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ $typeColor }}">
                                        {{ Str::title(str_replace('-', ' ', $mov->movement_type)) }}
                                    </span>
                                </td>

                                <td>
                                    <div class="font-bold text-[#2c3856] text-sm truncate max-w-[200px]" title="{{ $mov->product->name ?? '' }}">
                                        {{ $mov->product->name ?? 'N/A' }}
                                    </div>
                                    <div class="text-xs text-gray-500 font-mono">{{ $mov->product->sku ?? 'N/A' }}</div>
                                </td>

                                <td>
                                    <div class="font-mono text-xs font-bold text-indigo-600 mb-1">{{ $mov->palletItem->pallet->lpn ?? 'N/A' }}</div>
                                    <div class="flex items-center gap-1">
                                        <span class="bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded text-[10px] font-bold font-mono">
                                            @if ($mov->location)
                                                {{ $mov->location->aisle }}-{{ $mov->location->rack }}-{{ $mov->location->shelf }}-{{ $mov->location->bin }}
                                            @else
                                                N/A
                                            @endif
                                        </span>
                                    </div>
                                </td>

                                <td class="text-center">
                                    <span class="text-lg font-black {{ $mov->quantity > 0 ? 'text-emerald-600' : ($mov->quantity < 0 ? 'text-red-500' : 'text-gray-400') }}">
                                        {{ $mov->quantity > 0 ? '+' : '' }}{{ number_format($mov->quantity) }}
                                    </span>
                                </td>

                                <td>
                                    @if($mov->palletItem && $mov->palletItem->pallet && $mov->palletItem->pallet->purchaseOrder && $mov->palletItem->pallet->purchaseOrder->area)
                                        <span class="text-xs font-bold text-[#ff9c00]">{{ $mov->palletItem->pallet->purchaseOrder->area->name }}</span>
                                    @else
                                        <span class="text-xs text-gray-300 italic">General</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="text-xs font-bold text-gray-600">{{ $mov->palletItem->pallet->purchaseOrder->po_number ?? 'N/A' }}</div>
                                    <div class="text-[10px] text-gray-400 font-mono">{{ $mov->palletItem->pallet->purchaseOrder->pedimento_a4 ?? '-' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-12">
                                    <div class="inline-block p-4 rounded-full bg-gray-50 mb-3">
                                        <i class="fas fa-exchange-alt text-gray-300 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 font-bold text-sm">No se encontraron movimientos con los filtros seleccionados.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pb-20 nexus-pagination">
                {{ $movements->appends(request()->query())->links() }}
            </div>

        </div>
    </div>
</x-app-layout>