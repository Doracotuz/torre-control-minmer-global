@php
    $areas = [];
    if(Auth::user()->isSuperAdmin()) {
        $areas = \App\Models\Area::orderBy('name')->get();
    }
@endphp

<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="min-h-screen font-sans pb-12" x-data="backorderManager()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
            
            <div class="flex flex-col md:flex-row justify-between items-center gap-6 mb-10">
                <div class="relative pl-6 border-l-4 border-[#ff9c00]">
                    <h2 class="font-black text-3xl text-[#2c3856] leading-none tracking-tight">
                        Gestión de Backorders
                    </h2>
                    <p class="text-sm text-slate-500 font-medium mt-2">Surtido de pedidos pendientes por llegada de mercancía.</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
                    <form method="GET" action="{{ route('ff.inventory.backorders') }}" class="flex gap-2">
                        @if(Auth::user()->isSuperAdmin())
                            <div class="relative group">
                                <select name="area_id" onchange="this.form.submit()" class="appearance-none pl-5 pr-10 py-3 bg-white border border-slate-200 rounded-2xl text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#ff9c00] focus:border-transparent cursor-pointer shadow-sm hover:border-[#2c3856] transition-all outline-none">
                                    <option value="">Todas las Áreas</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        @endif

                        <div class="relative group">
                            <select name="warehouse_id" onchange="this.form.submit()" class="appearance-none pl-5 pr-10 py-3 bg-white border border-slate-200 rounded-2xl text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#ff9c00] focus:border-transparent cursor-pointer shadow-sm hover:border-[#2c3856] transition-all outline-none">
                                <option value="">Todos los Almacenes</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->description }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400">
                                <i class="fas fa-warehouse text-xs"></i>
                            </div>
                        </div>
                    </form>

                    <a href="{{ route('ff.inventory.index') }}" class="group flex items-center gap-3 px-5 py-3 bg-white border border-slate-200 rounded-2xl shadow-sm hover:shadow-md hover:border-[#2c3856] transition-all duration-300">
                        <span class="w-8 h-8 rounded-full bg-slate-50 group-hover:bg-[#2c3856] group-hover:text-white flex items-center justify-center transition-colors">
                            <i class="fas fa-arrow-left text-xs"></i>
                        </span>
                        <span class="text-sm font-bold text-slate-600 group-hover:text-[#2c3856]">Volver a Inventario</span>
                    </a>
                </div>
            </div>

            @if($backorders->isEmpty())
                <div class="bg-white rounded-3xl p-12 text-center shadow-sm border border-slate-100">
                    <div class="w-20 h-20 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4 animate-bounce">
                        <i class="fas fa-check-double text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-[#2c3856]">¡Todo Surtido!</h3>
                    <p class="text-slate-400 mt-2">No hay pedidos pendientes de surtir en este momento.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($backorders as $productId => $movements)
                    @php 
                        $product = $movements->first()->product; 
                        $totalNeeded = $movements->sum(fn($m) => abs($m->quantity));
                        
                        $currentStockQuery = $product->movements();
                        if(request('warehouse_id')) {
                            $currentStockQuery->where('ff_warehouse_id', request('warehouse_id'));
                        }
                        $currentStock = $currentStockQuery->sum('quantity');
                        
                        $canFulfillAny = $currentStock > 0;
                    @endphp

                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden flex flex-col hover:shadow-lg transition-all duration-300 relative group">
                        
                        <div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none group-hover:opacity-10 transition-opacity">
                            <i class="fas fa-box-open text-9xl text-[#2c3856]"></i>
                        </div>

                        <div class="p-6 pb-4 border-b border-slate-50 bg-gradient-to-b from-white to-slate-50/50">
                            <div class="flex items-start justify-between mb-4">
                                <div class="w-16 h-16 bg-white rounded-2xl shadow-sm p-1 border border-slate-100">
                                    <img src="{{ $product->photo_url }}" class="w-full h-full object-contain mix-blend-multiply">
                                </div>
                                <div class="text-right">
                                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wide mb-1">
                                        Stock {{ request('warehouse_id') ? 'Local' : 'Global' }}
                                    </span>
                                    <span class="text-2xl font-black {{ $currentStock >= $totalNeeded ? 'text-emerald-500' : ($currentStock > 0 ? 'text-amber-500' : 'text-rose-500') }}">
                                        {{ $currentStock }}
                                    </span>
                                </div>
                            </div>
                            
                            @if(Auth::user()->isSuperAdmin() && $product->area)
                                <div class="mb-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100 text-[10px] font-bold uppercase tracking-wide">
                                        {{ $product->area->name }}
                                    </span>
                                </div>
                            @endif

                            <h3 class="font-bold text-[#2c3856] leading-tight mb-1 line-clamp-2" title="{{ $product->description }}">
                                {{ $product->description }}
                            </h3>
                            <div class="text-xs font-mono text-slate-400 bg-slate-100 inline-block px-1.5 py-0.5 rounded">{{ $product->sku }}</div>
                        </div>

                        <div class="flex-1 bg-slate-50/30 p-4 space-y-3 overflow-y-auto max-h-[300px] custom-scroll">
                            @foreach($movements as $movement)
                                <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm relative pl-3 overflow-hidden">
                                    <div class="absolute left-0 top-0 bottom-0 w-1 {{ $currentStock >= abs($movement->quantity) ? 'bg-emerald-400' : 'bg-rose-400' }}"></div>
                                    
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">
                                                {{ $movement->created_at->format('d M, Y') }}
                                            </span>
                                            <a href="{{ route('ff.orders.show', $movement->folio) }}" class="text-sm font-black text-[#2c3856] hover:underline hover:text-[#ff9c00]">
                                                Folio #{{ $movement->folio }}
                                            </a>
                                        </div>
                                        <div class="text-right">
                                            <span class="block text-lg font-black text-rose-500">-{{ abs($movement->quantity) }}</span>
                                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded border 
                                                {{ $movement->warehouse ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-gray-50 text-gray-500 border-gray-100' }}">
                                                {{ $movement->warehouse ? $movement->warehouse->code : 'Global' }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 mb-3">
                                        <div class="w-5 h-5 rounded-full bg-slate-100 flex items-center justify-center text-[10px] text-slate-500 font-bold">
                                            {{ substr($movement->user->name, 0, 1) }}
                                        </div>
                                        <span class="text-xs font-medium text-slate-600 truncate">{{ $movement->user->name }}</span>
                                    </div>

                                    <button @click="fulfillOrder({{ $movement->id }})" 
                                            class="w-full py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-all shadow-sm flex items-center justify-center gap-2
                                            {{ $currentStock >= abs($movement->quantity) 
                                                ? 'bg-[#2c3856] text-white hover:bg-[#1e273d] hover:shadow-md' 
                                                : 'bg-slate-100 text-slate-400 cursor-not-allowed' }}"
                                            {{ $currentStock < abs($movement->quantity) ? 'disabled' : '' }}>
                                        <i class="fas fa-check"></i> Surtir Pedido
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <div class="p-4 bg-white border-t border-slate-100 text-center">
                            <span class="text-xs font-bold text-slate-400 uppercase">
                                Total Requerido: <span class="text-rose-500">{{ $totalNeeded }}</span>
                            </span>
                        </div>
                    </div>
                @endforeach
                </div>
            @endif
        </div>
    </div>

    <script>
        function backorderManager() {
            return {
                async fulfillOrder(movementId) {
                    if (!confirm('¿Confirmar surtido? Esto marcará el pedido como entregado y enviará notificación al vendedor.')) return;

                    try {
                        const response = await fetch("{{ route('ff.inventory.fulfillBackorder') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ movement_id: movementId })
                        });

                        const data = await response.json();
                        
                        if (response.ok) {
                            window.location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    } catch (e) {
                        alert('Error de conexión.');
                    }
                }
            }
        }
    </script>
</x-app-layout>