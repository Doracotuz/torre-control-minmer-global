<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Detalle de SO: {{ $salesOrder->so_number }}</h2></x-slot>
    <div class="py-12"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-lg shadow-xl">
             <h3 class="font-bold text-lg">Productos a Surtir</h3>
             <table class="min-w-full divide-y divide-gray-200 mt-4">
                 <thead><tr><th class="px-4 py-2 text-left">SKU</th><th class="px-4 py-2 text-left">Producto</th><th class="px-4 py-2 text-right">Cantidad Ordenada</th></tr></thead>
                 <tbody>
                     @foreach($salesOrder->lines as $line)
                         <tr class="border-b"><td class="px-4 py-2">{{ $line->product->sku }}</td><td class="px-4 py-2">{{ $line->product->name }}</td><td class="px-4 py-2 text-right">{{ $line->quantity_ordered }}</td></tr>
                     @endforeach
                 </tbody>
             </table>
                @if ($salesOrder->status == 'Pending')
                    <div class="mt-6 border-t pt-6">
                        <h3 class="font-bold text-lg">Acciones</h3>
                        <form action="{{ route('wms.picking.generate', $salesOrder) }}" method="POST">
                            @csrf
                            <p class="text-sm text-gray-600 my-2">La orden está lista para ser surtida. Al generar la Pick List, el sistema buscará y asignará el inventario disponible.</p>
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md shadow-md hover:bg-blue-700">Generar Pick List</button>
                        </form>
                    </div>
                @else
                    <div class="mt-6 text-center text-gray-600 font-semibold p-4 bg-gray-50 rounded-lg">
                        Esta orden se encuentra en estado: {{ $salesOrder->status }}
                    </div>
                @endif
        </div>
    </div></div>
</x-app-layout>