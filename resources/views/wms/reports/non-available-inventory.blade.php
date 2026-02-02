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
                        <span class="text-sm font-bold text-[#2c3856] tracking-[0.3em] uppercase">Control de Calidad</span>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-raleway font-black text-[#2c3856] leading-none">
                        INVENTARIO <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-600">NO DISPONIBLE</span>
                    </h1>
                    <p class="text-gray-500 font-medium mt-2 text-sm max-w-2xl">
                        Seguimiento de LPNs en estado de cuarentena, daño o inspección.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3 mt-6 xl:mt-0 items-center">
                    <a href="{{ route('wms.reports.index') }}" class="flex items-center gap-2 px-5 py-3 bg-white border border-gray-200 text-[#666666] font-bold rounded-xl shadow-sm hover:shadow-md hover:border-[#2c3856] hover:text-[#2c3856] transition-all text-sm uppercase tracking-wider">
                        <i class="fas fa-arrow-left"></i> <span>Volver a Reportes</span>
                    </a>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] p-8 border border-gray-100 shadow-xl mb-10 stagger-enter" style="animation-delay: 0.2s;">
                <form method="GET" action="{{ route('wms.reports.non-available-inventory') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-8 items-end">
                    
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Almacén</label>
                        <select name="warehouse_id" class="input-arch input-arch-select text-sm">
                            <option value="">Todos</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-[#ff9c00] uppercase tracking-widest block mb-1">Área / Cliente</label>
                        <select name="area_id" class="input-arch input-arch-select text-sm text-[#ff9c00]">
                            <option value="">Todas</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>
                                    {{ $area->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">SKU</label>
                        <input type="text" name="sku" value="{{ request('sku') }}" class="input-arch text-sm" placeholder="Buscar SKU...">
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">LPN</label>
                        <input type="text" name="lpn" value="{{ request('lpn') }}" class="input-arch text-sm font-mono" placeholder="Buscar LPN...">
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Estado / Calidad</label>
                        <select name="quality_id" class="input-arch input-arch-select text-sm">
                            <option value="">Todas (No Disponibles)</option>
                            @foreach($qualities as $quality)
                                <option value="{{ $quality->id }}" {{ request('quality_id') == $quality->id ? 'selected' : '' }}>
                                    {{ $quality->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="btn-nexus px-4 py-3 flex-1 flex items-center justify-center shadow-lg uppercase tracking-wider text-xs">
                            <i class="fas fa-filter mr-2"></i> Filtrar
                        </button>
                        <a href="{{ route('wms.reports.non-available-inventory.export', request()->query()) }}" class="btn-ghost px-4 py-3 flex items-center justify-center border-green-200 text-green-600 hover:bg-green-50 hover:border-green-300" title="Exportar CSV">
                            <i class="fas fa-file-csv text-lg"></i>
                        </a>
                        <a href="{{ route('wms.reports.non-available-inventory') }}" class="btn-ghost px-3 py-3 flex items-center justify-center text-gray-400 hover:text-red-500" title="Limpiar">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto pb-12 stagger-enter" style="animation-delay: 0.3s;">
                <table class="nexus-table w-full">
                    <thead>
                        <tr>
                            <th>Estado</th>
                            <th>LPN / Ubicación</th>
                            <th>Producto</th>
                            <th class="text-right">Cantidad</th>
                            <th>Área / Origen</th>
                            <th>Fechas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($nonAvailableItems as $item)
                            <tr class="nexus-row group">
                                <td>
                                    <span class="px-4 py-1.5 inline-flex text-xs font-black uppercase tracking-wider rounded-xl shadow-sm bg-red-50 text-red-600 border border-red-100">
                                        {{ $item->quality->name ?? 'N/A' }}
                                    </span>
                                </td>
                                
                                <td>
                                    <div class="font-mono text-sm font-bold text-[#2c3856] group-hover:text-[#ff9c00] transition-colors">
                                        {{ $item->pallet->lpn ?? 'N/A' }}
                                    </div>
                                    <div class="flex items-center gap-1 mt-1">
                                        <i class="fas fa-map-marker-alt text-gray-300 text-[10px]"></i>
                                        <span class="text-xs font-bold text-gray-500">
                                            {{ $item->pallet->location->code ?? 'SIN UBICACIÓN' }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <div class="font-bold text-gray-800 text-sm truncate max-w-xs" title="{{ $item->product->name ?? '' }}">
                                        {{ $item->product->name ?? 'N/A' }}
                                    </div>
                                    <div class="text-xs text-gray-500 font-mono mt-0.5">
                                        {{ $item->product->sku ?? 'N/A' }}
                                    </div>
                                </td>

                                <td class="text-right">
                                    <span class="text-lg font-black text-[#2c3856]">
                                        {{ number_format($item->quantity) }}
                                    </span>
                                </td>

                                <td>
                                    <div class="flex flex-col gap-1">
                                        @if($item->pallet->purchaseOrder && $item->pallet->purchaseOrder->area)
                                            <span class="inline-flex text-[10px] font-bold text-[#ff9c00] bg-orange-50 px-2 py-0.5 rounded border border-orange-100 w-fit">
                                                {{ $item->pallet->purchaseOrder->area->name }}
                                            </span>
                                        @else
                                            <span class="text-[10px] text-gray-400 italic">General</span>
                                        @endif
                                        <div class="text-xs font-bold text-gray-600 flex items-center gap-1">
                                            <i class="fas fa-file-invoice text-gray-300 text-[10px]"></i>
                                            {{ $item->pallet->purchaseOrder->po_number ?? 'N/A' }}
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="text-[10px] text-gray-500">
                                        <span class="block"><strong class="text-gray-700">Entrada:</strong> {{ $item->pallet->created_at->format('d/m/Y') }}</span>
                                        <span class="block"><strong class="text-gray-700">Actualizado:</strong> {{ $item->updated_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-12">
                                    <div class="inline-block p-4 rounded-full bg-green-50 mb-3">
                                        <i class="fas fa-check-circle text-green-400 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 font-bold text-sm">¡Excelente! No se encontró inventario no disponible.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pb-20 nexus-pagination">
                {{ $nonAvailableItems->appends(request()->query())->links() }}
            </div>

        </div>
    </div>
</x-app-layout>