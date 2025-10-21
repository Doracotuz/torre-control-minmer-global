<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Pick List para SO: {{ $pickList->salesOrder->so_number }}</h2>
            <a href="{{ route('wms.picking.pdf', $pickList) }}" target="_blank" class="px-4 py-2 bg-red-600 text-white rounded-md shadow-sm hover:bg-red-700">Descargar PDF</a>
        </div>
    </x-slot>
    
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
        @if (session('error'))
            <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                <span class="font-medium">Error:</span> {{ session('error') }}
            </div>
        @endif
    </div>

    <div class="py-12"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-lg shadow-xl">
            <div class="mb-6 grid grid-cols-3 gap-4 text-sm">
                <div><strong>Cliente:</strong><br>{{ $pickList->salesOrder->customer_name }}</div>
                <div><strong>Fecha de Orden:</strong><br>{{ $pickList->salesOrder->order_date->format('d/m/Y') }}</div>
                <div><strong>Estatus:</strong><br><span class="font-bold text-blue-700">{{ $pickList->status }}</span></div>
            </div>

            <h3 class="font-bold text-lg">Productos a Surtir</h3>   
            <table class="min-w-full divide-y divide-gray-200 mt-4">
                <thead class="bg-gray-100"><tr>
                    <th class="px-4 py-2 text-left">SKU</th>
                    <th class="px-4 py-2 text-left">Producto</th>
                    <th class="px-4 py-2 text-left text-red-600 font-mono">Ubicación</th>
                    <th class="px-4 py-2 text-right">Cantidad</th>
                </tr></thead>
                <tbody>
                @foreach($pickList->items as $item)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $item->product->sku ?? 'N/A' }}</td>
                        <td class="px-4 py-2">{{ $item->product->name ?? 'N/A' }}</td>
                        <td class="px-4 py-2 text-red-600 font-mono font-bold">
                            @if($item->location)
                                {{ $item->location->aisle }}-{{ $item->location->rack }}-{{ $item->location->shelf }}-{{ $item->location->bin }}
                            @else
                                SIN UBICACIÓN
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right text-lg font-bold">{{ $item->quantity_to_pick }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            @if ($pickList->status !== 'Completed')
            <div class="mt-6 border-t pt-6 text-center">
                <h3 class="font-bold text-lg">Confirmar Surtido</h3>
                <p class="text-gray-600 my-2">Al confirmar, registrarás que has recolectado todos los productos de la lista.</p>
            <form action="{{ route('wms.picking.confirm', $pickList) }}" method="POST">
                @csrf
                <button type="submit" class="px-8 py-3 bg-green-600 text-white font-semibold rounded-md shadow-lg hover:bg-green-700">
                    Confirmar Surtido y Actualizar Inventario
                </button>
            </form>
            </div>
            @else
            <div class="mt-6 border-t pt-6 text-center text-gray-600 font-semibold p-4 bg-gray-50 rounded-lg">
                <p>Este surtido fue confirmado por <strong>{{ \App\Models\User::find($pickList->picker_id)->name ?? 'N/A' }}</strong>.</p>
            </div>
            @endif
        </div>
    </div></div>
</x-app-layout>