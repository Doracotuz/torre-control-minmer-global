<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Órdenes de Compra</h2>
        <a href="{{ route('wms.purchase-orders.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Crear PO</a>
    </x-slot>
    <div class="py-12"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        {{-- Mensajes --}}
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">Nº PO</th>
                        <th class="px-6 py-3 text-left">Fecha Esperada</th>
                        <th class="px-6 py-3 text-left">Estatus</th>
                        <th class="px-6 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($purchaseOrders as $po)
                        <tr>
                            <td class="px-6 py-4 font-mono">{{ $po->po_number }}</td>
                            <td class="px-6 py-4">{{ Carbon\Carbon::parse($po->expected_date)->format('d/m/Y') }}</td>
                            <td class="px-6 py-4"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">{{ $po->status }}</span></td>
                            <td class="px-6 py-4 text-right"><a href="{{ route('wms.purchase-orders.show', $po) }}" class="text-indigo-600">Ver Detalles</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $purchaseOrders->links() }}</div>
    </div></div>
</x-app-layout>