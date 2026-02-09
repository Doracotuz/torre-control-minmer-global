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
            transition: all 0.3s ease; width: 100%;
        }
        .input-arch:focus { border-bottom-color: #ff9c00; box-shadow: none; outline: none; }
        .input-arch-select { background-image: none; cursor: pointer; padding-right: 1.5rem; }

        .nexus-table { width: 100%; border-collapse: separate; border-spacing: 0 0.8rem; }
        .nexus-table thead th {
            font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.1em; color: #9ca3af; font-weight: 800;
            padding: 0 1.5rem 1rem 1.5rem; text-align: left;
        }
        .nexus-row {
            background: white; transition: all 0.2s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .nexus-row td {
            padding: 1.25rem 1.5rem; vertical-align: middle; border-top: 1px solid #f3f4f6; border-bottom: 1px solid #f3f4f6;
        }
        .nexus-row td:first-child { border-top-left-radius: 1rem; border-bottom-left-radius: 1rem; border-left: 1px solid #f3f4f6; }
        .nexus-row td:last-child { border-top-right-radius: 1rem; border-bottom-right-radius: 1rem; border-right: 1px solid #f3f4f6; }
        
        .nexus-row:hover {
            box-shadow: 0 10px 30px -10px rgba(44, 56, 86, 0.08); z-index: 10; position: relative; border-color: transparent;
        }

        .btn-nexus { background: #2c3856; color: white; border-radius: 0.8rem; font-weight: 700; transition: all 0.2s; }
        .btn-nexus:hover { background: #1a253a; transform: translateY(-1px); }
        .btn-ghost { background: transparent; color: #2c3856; border: 2px solid #e5e7eb; border-radius: 1rem; font-weight: 700; }
        .btn-ghost:hover { border-color: #2c3856; background: #2c3856; color: white; }

        [x-cloak] { display: none !important; }
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20 relative overflow-hidden"
         x-data="inventoryPage('{{ session('open_adjustment_modal_for_item') }}')">
        
        <div class="fixed inset-0 -z-10 pointer-events-none">
            <div class="absolute top-0 left-0 w-full h-full bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-10"></div>
            <div class="absolute top-[-20%] right-[-10%] w-[50rem] h-[50rem] bg-gradient-to-b from-[#2c3856]/5 to-transparent rounded-full blur-[120px]"></div>
        </div>

        <div class="max-w-[1920px] mx-auto px-6 pt-10 relative z-10">
            
            <div class="flex flex-col xl:flex-row justify-between items-end mb-10 stagger-enter" style="animation-delay: 0.1s;">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-12 h-1 bg-[#ff9c00]"></span>
                        <span class="text-sm font-bold text-[#2c3856] tracking-[0.3em] uppercase">Control de Stock</span>
                    </div>
                    <h1 class="text-5xl md:text-6xl font-raleway font-black text-[#2c3856] leading-none">
                        MATRIZ <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-600">INVENTARIO</span>
                    </h1>
                </div>

                <div class="flex flex-wrap gap-3 mt-6 xl:mt-0 items-center">
                    <a href="{{ route('wms.dashboard') }}" class="flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-[#666666] font-bold rounded-full shadow-sm hover:shadow-md hover:border-[#2c3856] hover:text-[#2c3856] transition-all">
                        <i class="fas fa-arrow-left"></i> <span>Dashboard</span>
                    </a>                    
                    <div class="bg-white p-1.5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-1">                    
                        @if(Auth::user()->hasFfPermission('wms.inventory_move'))
                        <a href="{{ route('wms.inventory.transfer.create') }}" class="w-10 h-10 flex items-center justify-center rounded-xl hover:bg-gray-50 text-gray-400 hover:text-[#2c3856] transition-colors" title="Transferencias"><i class="fas fa-random"></i></a>
                        <a href="{{ route('wms.inventory.split.create') }}" class="w-10 h-10 flex items-center justify-center rounded-xl hover:bg-gray-50 text-gray-400 hover:text-[#2c3856] transition-colors" title="Split"><i class="fas fa-cut"></i></a>
                        @endif
                        <a href="{{ route('wms.inventory.pallet-info.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl hover:bg-gray-50 text-gray-400 hover:text-[#2c3856] transition-colors" title="Buscar LPN"><i class="fas fa-search"></i></a>
                        <a href="{{ route('wms.inventory.adjustments.log') }}" class="w-10 h-10 flex items-center justify-center rounded-xl hover:bg-gray-50 text-gray-400 hover:text-[#2c3856] transition-colors" title="Historial"><i class="fas fa-history"></i></a>
                    </div>
                    <a href="{{ route('wms.inventory.export-csv', request()->query()) }}" class="btn-ghost px-5 py-2.5 flex items-center gap-2 text-xs uppercase tracking-wider h-12">
                        <i class="fas fa-file-csv"></i> Exportar
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8 stagger-enter" style="animation-delay: 0.15s;">
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1">Tarimas</p>
                    <p class="text-3xl font-raleway font-black text-[#2c3856]">{{ number_format($kpis['total_pallets']) }}</p>
                </div>
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1">Unidades</p>
                    <p class="text-3xl font-raleway font-black text-[#ff9c00]">{{ number_format($kpis['total_units']) }}</p>
                </div>
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1">SKUs</p>
                    <p class="text-3xl font-raleway font-black text-blue-600">{{ number_format($kpis['total_skus']) }}</p>
                </div>
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1">Ubicaciones Libres</p>
                    <p class="text-3xl font-raleway font-black text-green-600">{{ number_format($kpis['available_locations']) }}</p>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] p-6 border border-gray-100 shadow-lg mb-8 stagger-enter" style="animation-delay: 0.2s;">
                <form id="filters-form" action="{{ route('wms.inventory.index') }}" method="GET">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 items-end">
                        <div>
                            <label class="text-[12px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Almacén</label>
                            <select name="warehouse_id" onchange="this.form.submit()" class="input-arch input-arch-select text-sm">
                                <option value="">Global</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" @selected($warehouseId == $warehouse->id)>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-[12px] font-bold text-[#ff9c00] uppercase tracking-widest block mb-1">Cliente / Área</label>
                            <select name="area_id" onchange="this.form.submit()" class="input-arch input-arch-select text-sm text-[#ff9c00]">
                                <option value="">Todas</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}" @selected($areaId == $area->id)>{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-[12px] font-bold text-gray-400 uppercase tracking-widest block mb-1">LPN / Ubicación</label>
                            <input type="text" name="lpn" value="{{ request('lpn') ?? request('location') }}" class="input-arch text-sm font-mono" placeholder="Buscar..." onchange="this.form.submit()">
                        </div>
                        <div>
                            <label class="text-[12px] font-bold text-gray-400 uppercase tracking-widest block mb-1">SKU / Producto</label>
                            <input type="text" name="sku" value="{{ request('sku') }}" class="input-arch text-sm" placeholder="SKU..." onchange="this.form.submit()">
                        </div>
                        <div>
                            <label class="text-[12px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Orden (PO)</label>
                            <input type="text" name="po_number" value="{{ request('po_number') }}" class="input-arch text-sm" placeholder="PO-..." onchange="this.form.submit()">
                        </div>
                        <div>
                            <a href="{{ route('wms.inventory.index') }}" class="flex items-center justify-center w-full py-2 text-[12px] font-bold text-gray-400 hover:text-red-500 uppercase tracking-widest transition-colors">
                                <i class="fas fa-undo mr-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto pb-12 stagger-enter" style="animation-delay: 0.3s;">
                <table class="nexus-table">
                    <thead>
                        <tr>
                            <th>Área</th>
                            <th>LPN (Identificador)</th>
                            <th>Ubicación</th>
                            <th>Contenido Principal</th>
                            <th>Calidad</th>
                            <th class="text-center">Stock</th>
                            <th>Estado LPN</th>
                            <th>Origen (PO)</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pallets as $pallet)
                            <tr class="nexus-row group">
                                <td class="text-center">
                                    <span class="text-xs font-bold text-blue-600">
                                        {{ $pallet->purchaseOrder->area->name ?? 'General' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="flex flex-col">
                                        <span class="font-mono font-black text-lg text-[#2c3856] group-hover:text-[#ff9c00] transition-colors cursor-pointer" @click="openModal({{ json_encode($pallet) }})">
                                            {{ $pallet->lpn }}
                                        </span>
                                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                                            {{ $pallet->updated_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    @if($pallet->location)
                                        <div class="flex items-center gap-2">
                                            @php
                                                $locTypeColor = match($pallet->location->type) {
                                                    'storage' => 'bg-blue-100 text-blue-700',
                                                    'picking' => 'bg-green-100 text-green-700',
                                                    default => 'bg-gray-100 text-gray-600'
                                                };
                                            @endphp
                                            <span class="px-2 py-1 rounded text-[11px] font-bold uppercase {{ $locTypeColor }}">
                                                {{ substr($pallet->location->translated_type, 0, 3) }}
                                            </span>
                                            <span class="font-bold text-[#2c3856] text-md">
                                                {{ $pallet->location->code }}
                                            </span>
                                        </div>
                                        <div class="text-[12px] text-gray-400 font-mono mt-1">
                                            {{ $pallet->location->aisle }}-{{ $pallet->location->rack }}-{{ $pallet->location->shelf }}-{{ $pallet->location->bin }}
                                        </div>
                                    @else
                                        <span class="text-red-400 text-xs font-bold">SIN UBICACIÓN</span>
                                    @endif
                                </td>

                                <td>
                                    @if($pallet->items->count() > 0)
                                        @php $mainItem = $pallet->items->first(); @endphp
                                        <div class="font-bold text-sm text-gray-700 truncate max-w-[200px]" title="{{ $mainItem->product->name }}">
                                            {{ $mainItem->product->name }}
                                        </div>
                                        <div class="text-xs text-gray-500 font-mono flex items-center gap-2">
                                            {{ $mainItem->product->sku }}
                                            @if($pallet->items->count() > 1)
                                                <span class="px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-600 text-[10px] font-bold">
                                                    +{{ $pallet->items->count() - 1 }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic text-sm">Vacío</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    @if($pallet->items->count() > 0)
                                        @php 
                                            $uniqueQualities = $pallet->items->pluck('quality.name')->unique();
                                        @endphp
                                        
                                        @if($uniqueQualities->count() > 1)
                                            <div class="flex flex-col gap-1.5 items-start justify-center p-1">
                                                @foreach($pallet->items->take(3) as $item)
                                                    <div class="flex items-center gap-2 w-full">
                                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-orange-50 text-orange-700 border border-orange-100 min-w-[25px] text-center">
                                                            {{ $item->quality->name }}
                                                        </span>
                                                        <span class="text-[10px] text-gray-600 font-mono truncate max-w-[80px]" title="{{ $item->product->name }}">
                                                            {{ $item->product->sku }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                                @if($pallet->items->count() > 3)
                                                    <span class="text-[9px] text-gray-400 pl-1">+{{ $pallet->items->count() - 3 }} más</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="px-2 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-100">
                                                {{ $uniqueQualities->first() ?? 'N/A' }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endif
                                </td>

                                <td class="text-center w-32">
                                    @php
                                        $totalQty = $pallet->items->sum('quantity');
                                        $committed = $pallet->items->sum('committed_quantity') ?? 0;
                                        $available = $totalQty - $committed;
                                        $percent = $totalQty > 0 ? ($available / $totalQty) * 100 : 0;
                                    @endphp
                                    <div class="font-black text-lg text-[#2c3856]">{{ number_format($totalQty) }}</div>
                                    <div class="w-full h-1.5 bg-gray-100 rounded-full mt-1 overflow-hidden flex">
                                        <div class="h-full bg-green-400" style="width: {{ $percent }}%"></div>
                                        <div class="h-full bg-red-400" style="width: {{ 100 - $percent }}%"></div>
                                    </div>
                                    <div class="flex justify-between text-[10px] font-bold text-gray-400 mt-1">
                                        <span>Disp: {{ $available }}
                                        <span class="text-red-300">Comp: {{ $committed }}</span>
                                    </div>
                                </td>

                                <td>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $pallet->status === 'Finished' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $pallet->status }}
                                    </span>
                                </td>

                                <td>
                                    <div class="text-xs font-bold text-[#2c3856]">{{ $pallet->purchaseOrder->po_number ?? 'N/A' }}</div>
                                    <div class="text-[12px] text-gray-400">
                                        {{-- Muestra el área también en la tabla --}}
                                        <span class="text-[#ff9c00] font-bold">{{ $pallet->purchaseOrder->area->name ?? 'General' }}</span>
                                    </div>
                                </td>

                                <td class="text-right">
                                    <button @click="openModal({{ json_encode($pallet) }})" class="w-8 h-8 rounded-lg bg-gray-50 hover:bg-[#2c3856] hover:text-white text-gray-400 transition-all flex items-center justify-center shadow-sm">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-12">
                                    <div class="inline-block p-4 rounded-full bg-gray-50 mb-3">
                                        <i class="fas fa-search text-gray-300 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 font-bold text-sm">No se encontraron registros.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pb-20 nexus-pagination">
                {{ $pallets->appends(request()->query())->links() }}
            </div>

        </div>

        <div x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
            <div class="fixed inset-0 bg-[#2c3856]/80 backdrop-blur-sm transition-opacity" @click="closeModal()"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="modalOpen" 
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-10" x-transition:enter-end="opacity-100 translate-y-0"
                     class="relative w-full max-w-5xl bg-white rounded-[2.5rem] shadow-2xl overflow-hidden">
                    
                    <template x-if="selectedPallet">
                        <div class="flex flex-col h-full max-h-[90vh]">
                            <div class="bg-[#2c3856] p-8 text-white relative overflow-hidden">
                                <div class="absolute top-0 right-0 w-40 h-40 bg-[#ff9c00] rounded-full blur-[80px] opacity-20 -mr-10 -mt-10"></div>
                                <div class="relative z-10 flex justify-between">
                                    <div>
                                        <p class="text-[#ff9c00] text-xs font-bold uppercase tracking-widest mb-1">Detalle de Inventario</p>
                                        <h2 class="text-4xl md:text-5xl font-mono font-black" x-text="selectedPallet.lpn"></h2>
                                        
                                        <div class="mt-3 inline-flex items-center gap-2 bg-white/10 px-3 py-1.5 rounded-lg border border-white/10">
                                            <i class="fas fa-building text-sm text-[#ff9c00]"></i>
                                            <span class="text-sm font-bold" x-text="selectedPallet.purchase_order?.area?.name || 'General (Sin Área)'"></span>
                                        </div>
                                    </div>
                                    <button @click="closeModal()" class="text-white/50 hover:text-white transition-colors bg-white/10 w-10 h-10 rounded-full flex items-center justify-center"><i class="fas fa-times text-xl"></i></button>
                                </div>
                            </div>

                            <div class="p-8 overflow-y-auto bg-white custom-scrollbar">
                                
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                                    
                                    <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 relative overflow-hidden group hover:border-blue-100 transition-colors">
                                        <div class="absolute top-0 right-0 p-3 opacity-10 group-hover:opacity-20 transition-opacity">
                                            <i class="fas fa-map-marker-alt text-4xl text-[#2c3856]"></i>
                                        </div>
                                        <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Ubicación Física</h4>
                                        <p class="text-2xl font-black text-[#2c3856]" x-text="selectedPallet.location ? selectedPallet.location.code : 'SIN UBICACIÓN'"></p>
                                        <p class="text-xs text-gray-500 mt-1 font-mono" x-text="selectedPallet.location ? `Pasillo ${selectedPallet.location.aisle} • Rack ${selectedPallet.location.rack} • Nivel ${selectedPallet.location.shelf}` : '-'"></p>
                                    </div>

                                    <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 relative overflow-hidden group hover:border-blue-100 transition-colors">
                                        <div class="absolute top-0 right-0 p-3 opacity-10 group-hover:opacity-20 transition-opacity">
                                            <i class="fas fa-file-invoice text-4xl text-blue-600"></i>
                                        </div>
                                        <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Orden de Compra (PO)</h4>
                                        <div class="flex items-baseline gap-2 mb-3">
                                            <p class="text-xl font-black text-blue-600" x-text="selectedPallet.purchase_order?.po_number || 'N/A'"></p>
                                            <span class="text-[10px] font-bold bg-blue-100 text-blue-700 px-2 py-0.5 rounded uppercase" x-text="selectedPallet.purchase_order?.status || '-'"></span>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="flex justify-between text-xs border-b border-gray-200 pb-1">
                                                <span class="text-gray-500">Factura:</span>
                                                <span class="font-bold text-gray-700 font-mono" x-text="selectedPallet.purchase_order?.document_invoice || '-'"></span>
                                            </div>
                                            <div class="flex justify-between text-xs pt-1">
                                                <span class="text-gray-500">Contenedor:</span>
                                                <span class="font-bold text-gray-700 font-mono" x-text="selectedPallet.purchase_order?.container_number || '-'"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 relative overflow-hidden group hover:border-blue-100 transition-colors">
                                        <div class="absolute top-0 right-0 p-3 opacity-10 group-hover:opacity-20 transition-opacity">
                                            <i class="fas fa-globe-americas text-4xl text-green-600"></i>
                                        </div>
                                        <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Importación / Aduana</h4>
                                        <div class="space-y-2 mt-2">
                                            <div class="flex justify-between items-center bg-white p-2 rounded-lg border border-gray-100">
                                                <p class="text-[9px] text-gray-400 uppercase font-bold">Pedimento A4</p>
                                                <p class="text-xs font-mono font-bold text-gray-700" x-text="selectedPallet.purchase_order?.pedimento_a4 || 'N/A'"></p>
                                            </div>
                                            <div class="flex justify-between items-center bg-white p-2 rounded-lg border border-gray-100">
                                                <p class="text-[9px] text-gray-400 uppercase font-bold">Pedimento G1</p>
                                                <p class="text-xs font-mono font-bold text-gray-700" x-text="selectedPallet.purchase_order?.pedimento_g1 || 'N/A'"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-8 border-t border-gray-100 pt-6">
                                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                        <i class="fas fa-info-circle text-[#ff9c00]"></i> Detalles de Operación
                                    </h4>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                        <div class="p-3 rounded-xl border border-gray-100 bg-gray-50/50">
                                            <p class="text-[9px] text-gray-400 uppercase font-bold mb-1">Área Propietaria</p>
                                            <p class="text-xs font-bold text-[#ff9c00]" x-text="selectedPallet.purchase_order?.area?.name || 'General'"></p>
                                        </div>
                                        <div class="p-3 rounded-xl border border-gray-100 bg-gray-50/50">
                                            <p class="text-[9px] text-gray-400 uppercase font-bold mb-1">Operador Recibo</p>
                                            <p class="text-xs font-bold text-gray-700 truncate" x-text="selectedPallet.purchase_order?.operator_name || 'N/A'"></p>
                                        </div>
                                        <div class="p-3 rounded-xl border border-gray-100 bg-gray-50/50">
                                            <p class="text-[9px] text-gray-400 uppercase font-bold mb-1">Inicio Descarga</p>
                                            <p class="text-[10px] font-mono text-gray-600" x-text="selectedPallet.purchase_order?.download_start_time || '-'"></p>
                                        </div>
                                        <div class="p-3 rounded-xl border border-gray-100 bg-gray-50/50">
                                            <p class="text-[9px] text-gray-400 uppercase font-bold mb-1">Fin Descarga</p>
                                            <p class="text-[10px] font-mono text-gray-600" x-text="selectedPallet.purchase_order?.download_end_time || '-'"></p>
                                        </div>
                                    </div>

                                    <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4">
                                        <h5 class="text-[10px] font-bold text-blue-400 uppercase tracking-widest mb-2 flex items-center gap-2">
                                            <i class="fas fa-history"></i> Auditoría / Última Acción
                                        </h5>
                                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-2">
                                            <div>
                                                <p class="text-sm font-bold text-[#2c3856]" x-text="selectedPallet.last_action || 'Sin registro'"></p>
                                                <p class="text-[10px] text-gray-500">
                                                    Realizado por: <span class="font-bold text-gray-700" x-text="selectedPallet.user?.name || 'Sistema/Usuario ' + (selectedPallet.user_id || '?')"></span>
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xs font-mono font-bold text-blue-600" x-text="new Date(selectedPallet.updated_at).toLocaleString()"></p>
                                                <p class="text-[9px] text-gray-400">Fecha de Actualización</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h4 class="text-sm font-bold text-[#2c3856] uppercase tracking-widest mb-4 border-b border-gray-100 pb-2">Contenido de la Tarima</h4>
                                <div class="space-y-3">
                                    <template x-for="item in selectedPallet.items" :key="item.id">
                                        <div class="flex justify-between items-center p-4 rounded-xl border border-gray-100 hover:bg-gray-50 transition-colors">
                                            <div>
                                                <p class="font-bold text-gray-800" x-text="item.product.name"></p>
                                                <p class="text-xs text-gray-500 font-mono mt-0.5" x-text="item.product.sku"></p>
                                            </div>
                                            <div class="flex items-center gap-6">
                                                <span class="text-xs font-bold bg-blue-50 text-blue-700 px-2 py-1 rounded border border-blue-100" x-text="item.quality.name"></span>
                                                <div class="text-right">
                                                    <span class="block text-2xl font-black text-[#2c3856]" x-text="item.quantity"></span>
                                                    <span class="text-[10px] text-gray-400 font-bold uppercase">Piezas</span>
                                                </div>
                                                @if(Auth::user()->hasFfPermission('wms.inventory_adjust'))
                                                    <button @click="openAdjustmentModal(item)" class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 text-gray-400 hover:bg-[#ff9c00] hover:text-white transition-all" title="Ajustar Inventario">
                                                        <i class="fas fa-cog"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div x-show="adjustmentModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="display: none;" x-cloak>
            <div @click.away="closeAdjustmentModal()" class="bg-white rounded-3xl p-8 w-full max-w-md shadow-2xl">
                <template x-if="itemToAdjust">
                    <form :action="`/wms/inventory/pallet-items/${itemToAdjust.id}/adjust`" method="POST">
                        @csrf
                        <h3 class="text-xl font-bold text-[#2c3856] mb-6">Ajuste Manual</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Cantidad Real</label>
                                <input type="number" name="new_quantity" class="input-arch text-2xl" :value="itemToAdjust.quantity" required>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Motivo</label>
                                <textarea name="reason" rows="2" class="input-arch text-sm resize-none" required></textarea>
                            </div>
                        </div>
                        <div class="flex gap-3 mt-8">
                            <button type="button" @click="closeAdjustmentModal()" class="flex-1 py-3 text-sm font-bold text-gray-500 hover:bg-gray-50 rounded-xl transition">Cancelar</button>
                            <button type="submit" class="flex-1 py-3 text-sm font-bold bg-[#ff9c00] text-white rounded-xl hover:bg-orange-600 transition shadow-lg shadow-orange-200">Confirmar</button>
                        </div>
                    </form>
                </template>
            </div>
        </div>

    </div>
    
    <script>
        function inventoryPage(failedItemId = null) {
            return {
                modalOpen: false, 
                selectedPallet: null,
                adjustmentModalOpen: false, 
                itemToAdjust: null,
                palletsOnPage: @json($pallets->items()),

                init() {
                    if (failedItemId) {
                        for (const pallet of this.palletsOnPage) {
                            if (pallet && pallet.items) {
                                const foundItem = pallet.items.find(item => item.id == failedItemId);
                                if (foundItem) {
                                    this.openAdjustmentModal(foundItem);
                                    break;
                                }
                            }
                        }
                    }
                },
                
                openModal(pallet) { this.selectedPallet = pallet; this.modalOpen = true; },
                closeModal() { this.modalOpen = false; this.selectedPallet = null; },
                
                openAdjustmentModal(item) { this.itemToAdjust = item; this.adjustmentModalOpen = true; },
                closeAdjustmentModal() { 
                    this.adjustmentModalOpen = false; 
                    this.itemToAdjust = null; 
                },
            }
        }
        
        document.addEventListener('alpine:init', () => {
            Alpine.data('inventoryPage', inventoryPage);
        });
    </script>
</x-app-layout>