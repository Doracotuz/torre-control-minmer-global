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
                <a href="{{ route('ff.inventory.index') }}" class="group flex items-center gap-3 px-5 py-3 bg-white border border-slate-200 rounded-2xl shadow-sm hover:shadow-md hover:border-[#2c3856] transition-all duration-300">
                    <span class="w-8 h-8 rounded-full bg-slate-50 group-hover:bg-[#2c3856] group-hover:text-white flex items-center justify-center transition-colors">
                        <i class="fas fa-arrow-left text-xs"></i>
                    </span>
                    <span class="text-xs font-bold text-slate-500 group-hover:text-[#2c3856] uppercase tracking-wider">Inventario</span>
                </a>
            </div>

            @if($backorders->isEmpty())
                <div class="bg-white rounded-[3rem] p-16 text-center shadow-[0_20px_60px_rgba(0,0,0,0.03)] border border-slate-100 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-b from-white to-emerald-50/30"></div>
                    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-[0.03]"></div>
                    
                    <div class="relative z-10">
                        <div class="w-24 h-24 bg-emerald-100 text-emerald-500 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-emerald-100 transition-transform group-hover:scale-110 duration-500">
                            <i class="fas fa-check-double text-4xl"></i>
                        </div>
                        <h3 class="text-3xl font-black text-[#2c3856] mb-2 tracking-tight">¡Todo Surtido!</h3>
                        <p class="text-slate-500 font-medium text-lg">No hay pedidos pendientes de entrega en este momento.</p>
                    </div>
                </div>
            @else
                <div class="space-y-10">
                    @foreach($backorders as $productId => $movements)
                        @php 
                            $product = $movements->first()->product;
                            $currentStock = $product->movements()->sum('quantity');
                            $totalDebt = $movements->sum(fn($m) => abs($m->quantity));
                            $canFulfillAll = $currentStock >= $totalDebt;
                            $canFulfillPartial = $currentStock > 0;
                        @endphp

                        <div class="bg-white rounded-[2.5rem] shadow-[0_15px_50px_rgba(0,0,0,0.04)] border border-slate-100 overflow-hidden relative group">
                            
                            <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-slate-50 to-transparent rounded-bl-[100px] -mr-10 -mt-10 transition-transform group-hover:scale-110 duration-700"></div>

                            <div class="p-8 relative z-10">
                                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8 pb-8 border-b border-slate-50">
                                    <div class="flex items-center gap-6">
                                        <div class="relative">
                                            <div class="w-24 h-24 bg-white border border-slate-100 rounded-2xl flex items-center justify-center p-2 shadow-sm group-hover:shadow-md transition-all duration-300">
                                                <img src="{{ $product->photo_url }}" class="max-w-full max-h-full object-contain mix-blend-multiply">
                                            </div>
                                            <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] font-mono font-bold px-3 py-1 rounded-full shadow-lg">
                                                {{ $product->sku }}
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <h3 class="text-2xl font-black text-[#2c3856] mb-2">{{ $product->description }}</h3>
                                            <div class="flex flex-wrap gap-2">
                                                @if($canFulfillAll)
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-bold border border-emerald-100 shadow-sm">
                                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Stock Suficiente
                                                    </span>
                                                @elseif($canFulfillPartial)
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 text-amber-700 rounded-lg text-xs font-bold border border-amber-100">
                                                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> Stock Parcial
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 text-rose-700 rounded-lg text-xs font-bold border border-rose-100">
                                                        <span class="w-2 h-2 rounded-full bg-rose-500"></span> Sin Stock
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-8 bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
                                        <div class="text-center px-4">
                                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Deuda</p>
                                            <p class="text-2xl font-black text-rose-500">-{{ $totalDebt }}</p>
                                        </div>
                                        <div class="w-px h-10 bg-slate-200"></div>
                                        <div class="text-center px-4">
                                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Stock Físico</p>
                                            <p class="text-2xl font-black {{ $currentStock > 0 ? 'text-emerald-600' : 'text-slate-400' }}">
                                                {{ $currentStock }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <table class="w-full text-left text-sm">
                                        <thead class="text-xs text-slate-400 font-bold uppercase bg-slate-50/50 rounded-xl">
                                            <tr>
                                                <th class="px-6 py-4 rounded-l-xl">Fecha</th>
                                                <th class="px-6 py-4">Folio</th>
                                                <th class="px-6 py-4">Cliente</th>
                                                <th class="px-6 py-4 text-center">Debemos</th>
                                                <th class="px-6 py-4 text-right rounded-r-xl">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-50">
                                            @foreach($movements as $mov)
                                                <tr class="hover:bg-slate-50/80 transition-colors group">
                                                    <td class="px-6 py-5 text-slate-500 font-mono text-xs">{{ $mov->created_at->format('d/m/Y') }}</td>
                                                    <td class="px-6 py-5">
                                                        <span class="font-bold text-[#2c3856] bg-slate-100 px-2 py-1 rounded group-hover:bg-[#2c3856] group-hover:text-white transition-colors">#{{ $mov->folio }}</span>
                                                    </td>
                                                    <td class="px-6 py-5">
                                                        <div class="font-bold text-slate-700">{{ $mov->client_name }}</div>
                                                        <div class="text-[10px] text-slate-400 font-medium uppercase mt-0.5">Vendedor: {{ $mov->user->name ?? 'N/A' }}</div>
                                                    </td>
                                                    <td class="px-6 py-5 text-center">
                                                        <span class="font-black text-rose-500 text-lg">{{ abs($mov->quantity) }}</span>
                                                    </td>
                                                    <td class="px-6 py-5 text-right">
                                                        @if($currentStock >= abs($mov->quantity))
                                                            <button @click="fulfillOrder({{ $mov->id }})" 
                                                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#2c3856] text-white text-xs font-bold uppercase tracking-wide rounded-xl shadow-lg shadow-blue-900/20 hover:bg-[#1e273d] hover:shadow-xl hover:-translate-y-0.5 transition-all">
                                                                <i class="fas fa-box-open"></i> Surtir
                                                            </button>
                                                        @else
                                                            <span class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-400 text-xs font-bold rounded-xl cursor-not-allowed opacity-70">
                                                                <i class="fas fa-clock"></i> Esperando
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
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
                            // Toast visual si tuvieras librería, por ahora reload
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