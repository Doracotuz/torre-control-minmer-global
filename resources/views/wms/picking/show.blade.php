<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Ejecutando Pick List #{{ $pickList->id }} (SO: {{ $pickList->salesOrder->so_number }})</h2>
    </x-slot>
    <div class="py-12"><div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-8 rounded-lg shadow-xl">
            <form action="{{ route('wms.picking.confirm', $pickList) }}" method="POST" onsubmit="return confirm('¿Confirmas que has recogido todos los productos de la lista? Esta acción descontará el inventario.');">
                @csrf
                <p class="text-gray-600 mb-6">Sigue la lista para recoger los productos de sus ubicaciones. Una vez completado, presiona el botón de confirmar.</p>
                <div class="space-y-6">
                    @foreach ($pickList->items as $item)
                        <div class="flex items-center p-4 border rounded-lg">
                            <div class="w-1/3">
                                <p class="text-sm text-gray-500">UBICACIÓN</p>
                                <p class="text-2xl font-mono font-bold text-indigo-600">{{ $item->location->code }}</p>
                            </div>
                            <div class="w-2/3 border-l pl-4">
                                 <p class="text-sm text-gray-500">PRODUCTO</p>
                                 <p class="font-bold text-lg">{{ $item->product->name }}</p>
                                 <p class="font-mono text-sm text-gray-600">{{ $item->product->sku }}</p>
                            </div>
                            <div class="text-right">
                                 <p class="text-sm text-gray-500">CANTIDAD</p>
                                 <p class="text-3xl font-bold">{{ $item->quantity_to_pick }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                 <div class="mt-8 flex justify-end">
                    <button type="submit" class="px-8 py-3 bg-green-600 text-white font-semibold rounded-md shadow-md hover:bg-green-700 text-lg">
                        Confirmar Picking Completo
                    </button>
                </div>
            </form>
        </div>
    </div></div>
</x-app-layout>