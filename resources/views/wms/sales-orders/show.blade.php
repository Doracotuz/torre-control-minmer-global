<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Detalle de SO: {{ $salesOrder->so_number }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md" role="alert">
                    <p class="font-bold">Éxito</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert">
                    <p class="font-bold">Error</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif
            <div class="bg-white p-6 rounded-lg shadow-xl space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm border-b pb-6">
                    <div>
                        <span class="font-semibold text-gray-500 block">Nº de Orden (SO)</span>
                        <span class="font-mono text-lg">{{ $salesOrder->so_number }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-500 block">Cliente</span>
                        <span class="text-lg">{{ $salesOrder->customer_name }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-500 block">Fecha de Orden</span>
                        <span class="text-lg">{{ $salesOrder->order_date->format('d \d\e F, Y') }}</span>
                    </div>
                </div>

                <div>
                    <h3 class="font-bold text-lg">Productos a Surtir</h3>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left">SKU</th>
                                    <th class="px-4 py-2 text-left">Producto</th>
                                    <th class="px-4 py-2 text-right">Cantidad Ordenada</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($salesOrder->lines as $line)
                                    <tr class="border-b">
                                        <td class="px-4 py-2 font-mono">{{ $line->product->sku }}</td>
                                        <td class="px-4 py-2">{{ $line->product->name }}</td>
                                        <td class="px-4 py-2 text-right font-bold text-lg">{{ $line->quantity_ordered }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="border-t pt-6">
                    <h3 class="font-bold text-lg mb-2">Estado de Surtido</h3>
                    
                    @php $pickList = $salesOrder->pickList; @endphp
                    
                    @if ($pickList)
                        {{-- Si ya existe una Pick List --}}
                        <div class="p-4 bg-green-50 border-l-4 border-green-500 text-green-800 rounded-md">
                            <p class="font-semibold">Pick List Generada. Estatus: <span class="font-bold">{{ $pickList->status }}</span></p>
                            @if ($pickList->picker_id)
                                <p class="text-sm mt-1">Confirmado por: <span class="font-semibold">{{ \App\Models\User::find($pickList->picker_id)->name ?? 'Usuario no encontrado' }}</span></p>
                            @endif
                            <div class="mt-4 space-x-2">
                                <a href="{{ route('wms.picking.show', $pickList) }}" class="px-4 py-2 bg-gray-600 text-white font-semibold rounded-md shadow-md hover:bg-gray-700">Ver Pick List</a>
                                <a href="{{ route('wms.picking.pdf', $pickList) }}" target="_blank" class="px-4 py-2 bg-red-600 text-white font-semibold rounded-md shadow-md hover:bg-red-700">Ver PDF</a>
                            </div>
                        </div>
                    @elseif ($salesOrder->status == 'Pending')
                        {{-- Si la orden está pendiente y NO tiene Pick List --}}
                        <form action="{{ route('wms.picking.generate', $salesOrder) }}" method="POST">
                            @csrf
                            <p class="text-sm text-gray-600 my-2">La orden está lista. Al generar la Pick List, el sistema buscará y asignará el inventario.</p>
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md shadow-md hover:bg-blue-700">Generar Pick List</button>
                        </form>
                    @else
                        {{-- Otro estado (Picking, Packed, etc.) --}}
                        <div class="p-4 bg-yellow-50 border-l-4 border-yellow-500 text-yellow-800 rounded-md">
                            Esta orden se encuentra en estado: <span class="font-bold">{{ $salesOrder->status }}</span>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-app-layout>