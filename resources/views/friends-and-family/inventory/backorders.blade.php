<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="bg-[#E8ECF7] min-h-screen py-8 font-sans" x-data="backorderManager()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="font-bold text-3xl text-[#2c3856] leading-tight">
                        <i class="fas fa-history mr-2 text-[#ff9c00]"></i> Reporte de Backorders
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Pedidos vendidos sin stock que requieren surtido.</p>
                </div>
                <a href="{{ route('ff.inventory.index') }}" class="text-sm font-bold text-gray-500 hover:text-[#2c3856]">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al Inventario
                </a>
            </div>

            @if($backorders->isEmpty())
                <div class="bg-white rounded-3xl p-12 text-center shadow-sm border border-gray-100">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check text-3xl text-green-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">¡Todo al día!</h3>
                    <p class="text-gray-500">No hay pedidos pendientes de surtir (Backorders).</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-8">
                    @foreach($backorders as $productId => $movements)
                        @php 
                            $product = $movements->first()->product;
                            $currentStock = $product->movements()->sum('quantity');
                            $totalDebt = $movements->sum(fn($m) => abs($m->quantity));
                            $canFulfillAll = $currentStock >= $totalDebt;
                            $canFulfillPartial = $currentStock > 0;
                        @endphp

                        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 {{ $canFulfillAll ? 'bg-green-50/50' : '' }}">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 bg-white rounded-xl border border-gray-200 flex items-center justify-center overflow-hidden flex-shrink-0">
                                        <img src="{{ $product->photo_url }}" class="max-w-full max-h-full object-contain p-2 mix-blend-multiply">
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-xs font-mono font-bold text-gray-500 bg-gray-100 px-2 py-0.5 rounded">{{ $product->sku }}</span>
                                            @if($canFulfillAll)
                                                <span class="text-[10px] font-bold bg-green-100 text-green-700 px-2 py-0.5 rounded-full uppercase">Stock Suficiente</span>
                                            @elseif($canFulfillPartial)
                                                <span class="text-[10px] font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full uppercase">Stock Parcial</span>
                                            @else
                                                <span class="text-[10px] font-bold bg-red-100 text-red-700 px-2 py-0.5 rounded-full uppercase">Sin Stock</span>
                                            @endif
                                        </div>
                                        <h3 class="font-bold text-lg text-[#2c3856]">{{ $product->description }}</h3>
                                    </div>
                                </div>

                                <div class="flex items-center gap-6 text-right">
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase">Deuda Total</p>
                                        <p class="text-xl font-bold text-red-500">-{{ $totalDebt }} pzas</p>
                                    </div>
                                    <div class="w-px h-8 bg-gray-200"></div>
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase">Stock Físico</p>
                                        <p class="text-xl font-bold {{ $currentStock > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                            {{ $currentStock }} pzas
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-bold">
                                        <tr>
                                            <th class="px-6 py-3">Fecha Venta</th>
                                            <th class="px-6 py-3">Folio</th>
                                            <th class="px-6 py-3">Cliente</th>
                                            <th class="px-6 py-3 text-center">Debemos</th>
                                            <th class="px-6 py-3 text-right">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        @foreach($movements as $mov)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 text-gray-500">{{ $mov->created_at->format('d/m/Y') }}</td>
                                                <td class="px-6 py-4 font-bold text-[#2c3856]">#{{ $mov->folio }}</td>
                                                <td class="px-6 py-4">
                                                    <div class="font-bold">{{ $mov->client_name }}</div>
                                                    <div class="text-xs text-gray-400">{{ $mov->user->name ?? 'N/A' }}</div>
                                                </td>
                                                <td class="px-6 py-4 text-center font-bold text-red-500">
                                                    {{ abs($mov->quantity) }}
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    @if($currentStock >= abs($mov->quantity))
                                                        <button @click="fulfillOrder({{ $mov->id }})" class="px-3 py-1.5 bg-[#2c3856] text-white text-xs font-bold rounded-lg hover:bg-[#1e273d] shadow-sm transition-colors">
                                                            <i class="fas fa-box-open mr-1"></i> Surtir
                                                        </button>
                                                    @else
                                                        <span class="text-xs font-bold text-gray-400 italic" title="Falta stock para cubrir este pedido completo">
                                                            Esperando Stock
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
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
                    if (!confirm('¿Confirmar surtido? Esto marcará el pedido como entregado y notificará disponibilidad.')) return;

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
                            alert('¡Surtido exitoso!');
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