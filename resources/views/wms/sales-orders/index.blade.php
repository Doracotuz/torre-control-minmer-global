<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Órdenes de Venta</h2>
        <a href="{{ route('wms.sales-orders.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Crear SO</a>
    </x-slot>
    <div class="py-12"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-6 py-3 text-left">Nº SO</th>
                    <th class="px-6 py-3 text-left">Factura</th>
                    <th class="px-6 py-3 text-left">Cliente</th>
                    <th class="px-6 py-3 text-left">Fecha</th>
                    <th class="px-6 py-3 text-left">Estatus</th>
                    <th class="px-6 py-3 text-right">Acciones</th>
                </tr></thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($salesOrders as $so)
                        <tr>
                            <td class="px-6 py-4 font-mono">{{ $so->so_number }}</td>
                            <td class="px-6 py-4 font-mono">{{ $so->invoice_number ?? 'N/A' }}</td>
                            <td class="px-6 py-4">{{ $so->customer_name }}</td>
                            <td class="px-6 py-4">{{ Carbon\Carbon::parse($so->order_date)->format('d/m/Y') }}</td>
                            <td class="px-6 py-4"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">{{ $so->status }}</span></td>
                            <td class="px-6 py-4 text-right"><a href="{{ route('wms.sales-orders.show', $so) }}" class="text-indigo-600">Ver Detalles</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $salesOrders->links() }}</div>
    </div></div>
</x-app-layout>