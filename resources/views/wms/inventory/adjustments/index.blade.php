<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-3xl text-gray-800">Registro de Ajustes de Inventario</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden rounded-2xl shadow-xl border">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase">Usuario</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase">Origen</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase">LPN / Ubicación</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase">Producto (SKU)</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-600 uppercase">Cant. Anterior</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-600 uppercase">Cant. Nueva</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-600 uppercase">Diferencia</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase">Motivo</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($adjustments as $adjustment)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $adjustment->created_at->format('d/m/Y h:i A') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">{{ $adjustment->user->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">{{ $adjustment->source ?? 'N/A' }}</span></td>
                                    
                                    {{-- Lógica condicional para LPN o Ubicación --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">
                                        @if ($adjustment->palletItem)
                                            {{ $adjustment->palletItem->pallet->lpn ?? 'N/A' }}
                                        @else
                                            <span class="text-gray-500 italic">{{ $adjustment->location->code ?? 'N/A' }}</span>
                                        @endif
                                    </td>
                                    
                                    {{-- Lógica condicional para Producto --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @php
                                            $product = $adjustment->palletItem->product ?? $adjustment->product;
                                        @endphp
                                        @if ($product)
                                            <p class="font-semibold text-gray-800">{{ $product->name }}</p>
                                            <p class="text-gray-500 font-mono">{{ $product->sku }}</p>
                                        @else
                                            <p>Producto no encontrado</p>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 text-center text-gray-600">{{ $adjustment->quantity_before }}</td>
                                    <td class="px-6 py-4 text-center text-blue-600 font-bold text-lg">{{ $adjustment->quantity_after }}</td>
                                    <td class="px-6 py-4 text-center font-bold text-lg {{ $adjustment->quantity_difference >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $adjustment->quantity_difference > 0 ? '+' : '' }}{{ $adjustment->quantity_difference }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" title="{{ $adjustment->reason }}">{{ $adjustment->reason }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-gray-500 py-16"><p>No se han registrado ajustes de inventario.</p></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                 <div class="p-6 border-t">
                    {{ $adjustments->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>