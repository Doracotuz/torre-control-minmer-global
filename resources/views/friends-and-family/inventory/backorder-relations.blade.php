@php
    $areas = [];
    if(Auth::user()->isSuperAdmin()) {
        $areas = \App\Models\Area::orderBy('name')->get();
    }
@endphp

<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="min-h-screen font-sans pb-12" x-data="{ expandedCard: null }">
        <div class="max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8 pt-8">
            
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
                
                <div class="flex flex-wrap items-center gap-4">
                    @if(Auth::user()->isSuperAdmin())
                        <form method="GET" action="{{ route('ff.inventory.backorder_relations') }}" class="h-full">
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
                        </form>
                    @endif

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
                    <p class="text-slate-500 font-medium">No existen pasivos de inventario ni backorders activos.</p>
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
                                            <div class="absolute -top-2 -right-2 w-7 h-7 bg-rose-500 text-white rounded-xl flex items-center justify-center text-[10px] font-bold shadow-lg shadow-rose-200 border-2 border-white animate-pulse">
                                                !
                                            </div>
                                        </div>
                                        
                                        <div>
                                            @if(Auth::user()->isSuperAdmin() && $product->area)
                                                <div class="mb-1">
                                                    <span class="inline-block px-2 py-0.5 rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-100 text-[9px] font-black uppercase tracking-wider">
                                                        {{ $product->area->name }}
                                                    </span>
                                                </div>
                                            @endif
                                            <h3 class="text-xl font-bold text-[#2c3856] group-hover:text-[#ff9c00] transition-colors">{{ $product->description }}</h3>
                                            <div class="flex items-center gap-3 mt-1.5">
                                                <span class="text-xs font-mono font-bold text-slate-500 bg-slate-100 px-2 py-1 rounded-lg border border-slate-200">{{ $product->sku }}</span>
                                                <span class="text-[10px] font-bold text-purple-600 bg-purple-50 px-2 py-1 rounded-lg border border-purple-100">STOCK NEGATIVO</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-12 w-full md:w-auto justify-between md:justify-end pr-4">
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
                                        <button class="text-xs font-bold text-blue-500 hover:text-blue-700 transition-colors">Exportar Detalle</button>
                                    </div>
                                    
                                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                                        <table class="w-full text-sm text-left">
                                            <thead class="text-[10px] font-bold text-slate-400 uppercase bg-slate-50/50 border-b border-slate-100 tracking-wider">
                                                <tr>
                                                    <th class="px-6 py-4">Folio</th>
                                                    <th class="px-6 py-4">Cliente / Empresa</th>
                                                    <th class="px-6 py-4">Vendedor</th>
                                                    <th class="px-6 py-4 text-center">Fecha Venta</th>
                                                    <th class="px-6 py-4 text-center">Antigüedad</th>
                                                    <th class="px-6 py-4 text-right">Cantidad Debida</th>
                                                    <th class="px-6 py-4 text-right"></th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-50">
                                                @foreach($product->movements as $mov)
                                                    <tr class="hover:bg-purple-50/20 transition-colors group">
                                                        <td class="px-6 py-4">
                                                            <a href="{{ route('ff.orders.show', $mov->folio) }}" class="font-mono font-bold text-[#2c3856] bg-slate-100 px-2 py-1 rounded hover:bg-[#2c3856] hover:text-white transition-colors">
                                                                #{{ $mov->folio }}
                                                            </a>
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <p class="font-bold text-slate-700">{{ $mov->client_name }}</p>
                                                            <p class="text-[10px] text-slate-400 uppercase">{{ $mov->company_name }}</p>
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <div class="flex items-center gap-2">
                                                                <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[10px] font-bold">
                                                                    {{ substr($mov->user->name ?? 'U', 0, 1) }}
                                                                </div>
                                                                <span class="text-slate-600 text-xs font-medium">{{ $mov->user->name ?? 'N/A' }}</span>
                                                            </div>
                                                        </td>
                                                        <td class="px-6 py-4 text-center font-mono text-slate-500 text-xs">{{ $mov->created_at->format('d/m/Y') }}</td>
                                                        <td class="px-6 py-4 text-center">
                                                            <span class="px-2 py-1 rounded-lg bg-orange-50 text-orange-700 text-[10px] font-bold border border-orange-100">
                                                                {{ $mov->created_at->diffForHumans(null, true) }}
                                                            </span>
                                                        </td>
                                                        <td class="px-6 py-4 text-right">
                                                            <span class="font-black text-rose-500 text-lg">-{{ abs($mov->quantity) }}</span>
                                                        </td>
                                                        <td class="px-6 py-4 text-right">
                                                            <a href="{{ route('ff.orders.show', $mov->folio) }}" class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-white hover:bg-[#2c3856] hover:border-[#2c3856] flex items-center justify-center transition-all shadow-sm">
                                                                <i class="fas fa-arrow-right"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>