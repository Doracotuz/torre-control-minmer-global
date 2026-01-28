@php
    $areas = [];
    if(Auth::user()->isSuperAdmin()) {
        $areas = \App\Models\Area::orderBy('name')->get();
    }
@endphp

<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="min-h-screen font-sans pb-12" 
         x-data="{ 
            expandedCard: null,
            showResolveModal: {{ $errors->any() ? 'true' : 'false' }}, 
            resolveAction: '{{ session('last_resolve_action', '') }}',
            resolveProduct: {{ session('last_resolve_product', '{ desc: \'\', sku: \'\', debt: 0 }') }},
            
            async fulfillOrder(movementId) {
                if (!confirm('¿Confirmar surtido de este pedido? Se descontará del stock disponible.')) return;

                try {
                    const response = await fetch('{{ route('ff.inventory.fulfillBackorder') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ movement_id: movementId })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        window.location.reload();
                    } else {
                        alert('No se pudo surtir: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Ocurrió un error de conexión al intentar surtir.');
                }
            }
         }">
        
        <div class="max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8 pt-8">
            
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-4 animate-bounce-in">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                        <i class="fas fa-check"></i>
                    </div>
                    <p class="text-emerald-700 font-bold text-sm">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center gap-4">
                    <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center">
                        <i class="fas fa-times"></i>
                    </div>
                    <p class="text-rose-700 font-bold text-sm">{{ session('error') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center gap-4">
                    <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center">
                        <i class="fas fa-exclamation"></i>
                    </div>
                    <div>
                        <p class="text-rose-700 font-bold text-sm">No se pudo procesar el ingreso:</p>
                        <ul class="list-disc list-inside text-xs text-rose-600 mt-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
            
            <div class="flex flex-col md:flex-row justify-between items-end gap-4 mb-8">
                <div class="relative group">
                    <a href="{{ route('ff.inventory.index') }}" class="text-xs font-bold text-slate-400 hover:text-[#2c3856] mb-2 flex items-center transition-colors">
                        <div class="w-6 h-6 rounded-full bg-white border border-slate-200 flex items-center justify-center mr-2 group-hover:bg-[#ff9c00] group-hover:border-[#ff9c00] group-hover:text-white transition-all">
                            <i class="fas fa-arrow-left text-[10px]"></i>
                        </div>
                        Regresar a Inventario
                    </a>
                    <h1 class="text-4xl font-black text-[#2c3856] tracking-tight relative z-10">Reporte de productos pendientes</h1>
                    <div class="absolute -bottom-2 left-0 w-12 h-1 bg-[#ff9c00] rounded-full"></div>
                    <p class="text-sm text-slate-500 font-medium mt-3 max-w-lg leading-relaxed">
                        Análisis detallado de la deuda operativa. Monitoreo de productos con stock negativo y su impacto en pedidos pendientes.
                    </p>
                </div>
                
                <div class="flex flex-wrap items-center gap-4 h-full">
                    <form method="GET" action="{{ route('ff.inventory.backorder_relations') }}" class="flex gap-2 h-full">
                        @if(Auth::user()->isSuperAdmin())
                            <div class="relative group h-full">
                                <select name="area_id" onchange="this.form.submit()" class="appearance-none h-full pl-5 pr-10 py-4 bg-white border border-slate-100 rounded-[2rem] text-sm font-bold text-[#2c3856] focus:ring-2 focus:ring-[#ff9c00] focus:border-transparent cursor-pointer shadow-[0_10px_40px_rgba(0,0,0,0.03)] outline-none hover:-translate-y-1 transition-transform duration-300">
                                    <option value="">Todas las Áreas</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        @endif

                        <div class="relative group h-full">
                            <select name="warehouse_id" onchange="this.form.submit()" class="appearance-none h-full pl-5 pr-10 py-4 bg-white border border-slate-100 rounded-[2rem] text-sm font-bold text-[#2c3856] focus:ring-2 focus:ring-[#ff9c00] focus:border-transparent cursor-pointer shadow-[0_10px_40px_rgba(0,0,0,0.03)] outline-none hover:-translate-y-1 transition-transform duration-300">
                                <option value="">Todos los Almacenes</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->description }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400">
                                <i class="fas fa-warehouse text-xs"></i>
                            </div>
                        </div>
                    </form>

                    <div class="bg-white px-6 py-4 rounded-[2rem] shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 flex items-center gap-4 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                        <div class="absolute top-0 right-0 w-16 h-16 bg-purple-50 rounded-bl-[50px] -mr-6 -mt-6 transition-transform group-hover:scale-110"></div>
                        <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shadow-sm relative z-10">
                            <i class="fas fa-cubes-stacked"></i>
                        </div>
                        <div class="relative z-10">
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Productos Afectados</p>
                            <p class="text-2xl font-black text-[#2c3856]">{{ $products->count() }}</p>
                        </div>
                    </div>
                    
                    <div class="bg-white px-6 py-4 rounded-[2rem] shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 flex items-center gap-4 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                        <div class="absolute top-0 right-0 w-16 h-16 bg-rose-50 rounded-bl-[50px] -mr-6 -mt-6 transition-transform group-hover:scale-110"></div>
                        <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl shadow-sm relative z-10">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div class="relative z-10">
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Deuda Total</p>
                            <p class="text-2xl font-black text-rose-600">{{ $products->sum('total_debt') }} <span class="text-xs font-bold text-slate-400 align-top mt-1 inline-block">Pzas</span></p>
                        </div>
                    </div>
                </div>
            </div>

            @if($products->isEmpty())
                <div class="flex flex-col items-center justify-center py-32 bg-white rounded-[3rem] border border-slate-100 shadow-[0_20px_60px_rgba(0,0,0,0.02)] relative overflow-hidden">
                    <div class="absolute inset-0 opacity-[0.02]" style="background-image: radial-gradient(#2c3856 1px, transparent 1px); background-size: 24px 24px;"></div>
                    <div class="w-24 h-24 bg-gradient-to-tr from-emerald-400 to-emerald-600 rounded-full flex items-center justify-center mb-6 shadow-xl shadow-emerald-200 animate-bounce">
                        <i class="fas fa-check text-4xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-black text-[#2c3856] mb-2">¡Todo en Orden!</h3>
                    <p class="text-slate-500 font-medium">No existen pasivos de inventario activos.</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-6">
                    @foreach($products as $index => $product)
                        <div class="bg-white rounded-[2rem] shadow-[0_4px_20px_rgba(0,0,0,0.02)] border border-slate-100 overflow-hidden transition-all duration-500 hover:shadow-[0_20px_40px_rgba(0,0,0,0.06)]"
                             :class="expandedCard === {{ $index }} ? 'ring-1 ring-[#2c3856]/10' : ''">
                            
                            <div @click="expandedCard === {{ $index }} ? expandedCard = null : expandedCard = {{ $index }}" 
                                 class="p-6 cursor-pointer relative group bg-white hover:bg-slate-50/50 transition-colors">
                                
                                <div class="flex flex-col md:flex-row justify-between items-center gap-6 relative z-10">
                                    <div class="flex items-center gap-6 w-full md:w-auto">
                                        <div class="relative">
                                            <div class="w-20 h-20 bg-white border border-slate-100 rounded-2xl flex items-center justify-center p-2 shadow-sm group-hover:scale-105 transition-transform duration-300">
                                                @if($product->photo_url)
                                                    <img src="{{ $product->photo_url }}" class="max-w-full max-h-full object-contain mix-blend-multiply">
                                                @else
                                                    <i class="fas fa-box text-slate-200 text-3xl"></i>
                                                @endif
                                            </div>
                                            <div class="absolute -top-2 -right-2 w-7 h-7 bg-rose-500 text-white rounded-xl flex items-center justify-center text-[10px] font-bold shadow-lg shadow-rose-200 border-2 border-white animate-pulse">!</div>
                                        </div>
                                        
                                        <div>
                                            @if(Auth::user()->isSuperAdmin() && $product->area)
                                                <div class="mb-1"><span class="inline-block px-2 py-0.5 rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-100 text-[9px] font-black uppercase tracking-wider">{{ $product->area->name }}</span></div>
                                            @endif
                                            <h3 class="text-xl font-bold text-[#2c3856] group-hover:text-[#ff9c00] transition-colors">{{ $product->description }}</h3>
                                            <div class="flex items-center gap-3 mt-1.5">
                                                <span class="text-xs font-mono font-bold text-slate-500 bg-slate-100 px-2 py-1 rounded-lg border border-slate-200">{{ $product->sku }}</span>
                                                <span class="text-[10px] font-bold text-purple-600 bg-purple-50 px-2 py-1 rounded-lg border border-purple-100">STOCK NEGATIVO</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-12 w-full md:w-auto justify-between md:justify-end pr-4">
                                        <button @click.stop="
                                                    resolveProduct = { 
                                                        desc: '{{ addslashes($product->description) }}', 
                                                        sku: '{{ $product->sku }}', 
                                                        debt: {{ $product->total_debt }} 
                                                    };
                                                    resolveAction = '{{ route('inventory.resolveBackorder', $product->id) }}';
                                                    showResolveModal = true;
                                                "
                                                class="hidden md:flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-xl hover:bg-emerald-500 hover:text-white transition-all shadow-sm group/btn">
                                            <i class="fas fa-plus text-xs"></i>
                                            <span class="text-xs font-bold uppercase tracking-wider">Ingresar</span>
                                        </button>
                                        
                                        <div class="text-right">
                                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Pedidos</p>
                                            <div class="flex items-center justify-end gap-2">
                                                <span class="w-2 h-2 rounded-full bg-purple-400"></span>
                                                <span class="text-2xl font-black text-[#2c3856]">{{ $product->movements->count() }}</span>
                                            </div>
                                        </div>
                                        <div class="w-px h-12 bg-slate-100 hidden md:block"></div>
                                        <div class="text-right">
                                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Deuda Total</p>
                                            <div class="flex items-center justify-end gap-1">
                                                <span class="text-3xl font-black text-rose-500">-{{ $product->total_debt }}</span>
                                            </div>
                                        </div>
                                        <div class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center transition-all duration-500 group-hover:bg-[#2c3856] group-hover:text-white"
                                             :class="expandedCard === {{ $index }} ? 'rotate-180 bg-[#2c3856] text-white shadow-lg' : 'text-slate-400'">
                                            <i class="fas fa-chevron-down"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div x-show="expandedCard === {{ $index }}" x-collapse.duration.500ms class="bg-slate-50/80 border-t border-slate-100">
                                <div class="p-8">
                                    <div class="flex justify-between items-center mb-6">
                                        <p class="text-xs font-bold text-[#2c3856] uppercase tracking-widest flex items-center">
                                            <span class="w-2 h-2 bg-[#ff9c00] rounded-full mr-3 animate-pulse"></span>
                                            Detalle de Compromisos
                                        </p>
                                    </div>
                                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                                        <table class="w-full text-sm text-left">
                                            <thead class="text-[10px] font-bold text-slate-400 uppercase bg-slate-50/50 border-b border-slate-100 tracking-wider">
                                                <tr>
                                                    <th class="px-6 py-4">Folio</th>
                                                    <th class="px-6 py-4">Almacén</th>
                                                    <th class="px-6 py-4">Cliente / Empresa</th>
                                                    <th class="px-6 py-4">Vendedor</th>
                                                    <th class="px-6 py-4 text-center">Fecha</th>
                                                    <th class="px-6 py-4 text-center">Antigüedad</th>
                                                    <th class="px-6 py-4 text-right">Deuda</th>
                                                    <th class="px-6 py-4 text-center">Acciones</th>
                                                </tr>
                                            </thead>
                                            @foreach($product->movements->groupBy('folio') as $folio => $movements)
                                                <tbody class="divide-y divide-slate-50 border-b border-slate-100 last:border-0 hover:bg-slate-50/30 transition-colors">
                                                    @foreach($movements as $mov)
                                                        <tr class="group">
                                                            <td class="px-6 py-4">
                                                                <a href="{{ route('ff.orders.show', $mov->folio) }}" class="font-mono font-bold text-[#2c3856] bg-slate-100 px-2 py-1 rounded hover:bg-[#2c3856] hover:text-white transition-colors">#{{ $mov->folio }}</a>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg border text-[10px] font-bold uppercase tracking-wide {{ $mov->warehouse ? 'bg-indigo-50 text-indigo-700 border-indigo-100' : 'bg-gray-50 text-gray-500 border-gray-100' }}">
                                                                    {{ $mov->warehouse ? $mov->warehouse->description : 'Global' }}
                                                                </span>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="font-bold text-slate-700">{{ $mov->client_name }}</p>
                                                                <p class="text-[10px] text-slate-400 uppercase">{{ $mov->company_name }}</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <span class="text-slate-600 text-xs font-medium">{{ $mov->user->name ?? 'N/A' }}</span>
                                                            </td>
                                                            <td class="px-6 py-4 text-center font-mono text-slate-500 text-xs">{{ $mov->created_at->format('d/m/Y') }}</td>
                                                            <td class="px-6 py-4 text-center">
                                                                <span class="px-2 py-1 rounded-lg bg-orange-50 text-orange-700 text-[10px] font-bold border border-orange-100">{{ $mov->created_at->diffForHumans(null, true) }}</span>
                                                            </td>
                                                            <td class="px-6 py-4 text-right">
                                                                <span class="font-black text-rose-500 text-lg">-{{ abs($mov->quantity) }}</span>
                                                            </td>
                                                            <td class="px-6 py-4 text-center flex items-center justify-center gap-2">
                                                                
                                                                <button @click="fulfillOrder({{ $mov->id }})" 
                                                                        class="px-3 py-1.5 bg-blue-50 text-blue-600 border border-blue-100 rounded-lg text-xs font-bold uppercase hover:bg-blue-600 hover:text-white transition-colors"
                                                                        title="Surtir (Usar stock existente)">
                                                                    <i class="fas fa-check-double mr-1"></i> Surtir
                                                                </button>

                                                                <a href="{{ route('ff.orders.show', $mov->folio) }}" class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-white hover:bg-[#2c3856] hover:border-[#2c3856] flex items-center justify-center transition-all shadow-sm">
                                                                    <i class="fas fa-arrow-right"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            @endforeach
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div x-show="showResolveModal" 
             style="display: none;"
             class="fixed inset-0 z-50 flex items-center justify-center px-4"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="fixed inset-0 bg-[#2c3856]/40 backdrop-blur-sm transition-opacity" @click="showResolveModal = false"></div>

            <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md relative z-10 overflow-hidden transform transition-all border border-slate-100"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-8 scale-95">
                
                <div class="relative bg-emerald-500 p-8 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-8 -mt-8"></div>
                    <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/10 rounded-full -ml-8 -mb-8"></div>
                    
                    <button @click="showResolveModal = false" class="absolute top-4 right-4 w-8 h-8 bg-black/10 text-white rounded-full flex items-center justify-center hover:bg-black/20 transition-colors">
                        <i class="fas fa-times text-sm"></i>
                    </button>

                    <div class="relative z-10">
                        <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-emerald-500 text-xl shadow-lg mb-4">
                            <i class="fas fa-dolly"></i>
                        </div>
                        <h3 class="text-2xl font-black text-white">Ingresar Stock</h3>
                        <p class="text-emerald-100 text-sm mt-1">Resolver deuda operativa</p>
                    </div>
                </div>

                <div class="p-8">
                    @if($errors->any())
                        <div class="mb-4 p-3 bg-rose-50 border border-rose-100 rounded-xl text-xs text-rose-600 font-bold">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Verifica los campos.
                        </div>
                    @endif

                    <div class="mb-6 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Producto</p>
                        <p class="text-[#2c3856] font-bold" x-text="resolveProduct.desc"></p>
                        <p class="text-xs text-slate-500 font-mono mt-1" x-text="resolveProduct.sku"></p>
                        <div class="mt-3 flex items-center gap-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Deuda Actual:</span>
                            <span class="text-rose-500 font-black text-sm" x-text="'-' + resolveProduct.debt + ' pzas'"></span>
                        </div>
                    </div>

                    <form method="POST" :action="resolveAction">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-[#2c3856] uppercase tracking-wider mb-2">Almacén de Ingreso</label>
                                <div class="relative">
                                    <select name="warehouse_id" required class="w-full appearance-none pl-4 pr-10 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-600 focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none transition-all">
                                        <option value="" disabled selected>Selecciona un almacén</option>
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->id }}">{{ $wh->description }} ({{ $wh->code }})</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#2c3856] uppercase tracking-wider mb-2">Cantidad a Ingresar</label>
                                <div class="relative">
                                    <input type="number" name="quantity" min="1" required class="w-full pl-4 pr-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-600 focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none transition-all" placeholder="0">
                                </div>
                                <p class="text-[10px] text-slate-400 mt-2 leading-relaxed">
                                    * Esta cantidad se descontará automáticamente de las órdenes pendientes más antiguas (FIFO).
                                </p>
                            </div>
                        </div>

                        <button type="submit" class="w-full mt-8 py-4 bg-[#2c3856] text-white font-bold rounded-xl shadow-lg shadow-slate-200 hover:bg-[#1a233a] hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-3">
                            <span>Confirmar Ingreso</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>