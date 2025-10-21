<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Órdenes de Venta</h2>
            <a href="{{ route('wms.sales-orders.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md shadow-sm hover:bg-indigo-700">Crear SO</a>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-4 rounded-lg shadow-md mb-6">
                <form action="{{ route('wms.sales-orders.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <input type="text" name="so_number" placeholder="Buscar por Nº SO..." value="{{ request('so_number') }}" class="rounded-md border-gray-300">
                    <input type="text" name="customer_name" placeholder="Buscar por Cliente..." value="{{ request('customer_name') }}" class="rounded-md border-gray-300">
                    <select name="status" class="rounded-md border-gray-300">
                        <option value="">Todos los Estatus</option>
                        <option value="Pending" @selected(request('status') == 'Pending')>Pendiente</option>
                        <option value="Picking" @selected(request('status') == 'Picking')>En Surtido</option>
                        <option value="Packed" @selected(request('status') == 'Packed')>Empacado</option>
                    </select>
                    <div>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Filtrar</button>
                        <a href="{{ route('wms.sales-orders.index') }}" class="px-4 py-2 text-gray-600">Limpiar</a>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-6 py-3 text-left">Nº SO</th>
                        <th class="px-6 py-3 text-left">Cliente</th>
                        <th class="px-6 py-3 text-left">Fecha</th>
                        <th class="px-6 py-3 text-center">Items</th>
                        <th class="px-6 py-3 text-center">Unidades</th>
                        <th class="px-6 py-3 text-left">Creado por</th>
                        <th class="px-6 py-3 text-left">Estatus</th>
                        <th class="px-6 py-3 text-right">Acciones</th>
                    </tr></thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($salesOrders as $so)
                            <tr>
                                <td class="px-6 py-4 font-mono">{{ $so->so_number }}</td>
                                <td class="px-6 py-4">{{ $so->customer_name }}</td>
                                <td class="px-6 py-4">{{ $so->order_date->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-center">{{ $so->lines_count }}</td>
                                <td class="px-6 py-4 text-center font-bold">{{ (int)$so->lines_sum_quantity_ordered }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $so->user->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">{{ $so->status }}</span></td>
                                <td class="px-6 py-4 text-right"><a href="{{ route('wms.sales-orders.show', $so) }}" class="text-indigo-600 hover:underline">Ver Detalles</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-6 py-4 text-center text-gray-500">No se encontraron órdenes de venta.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $salesOrders->appends(request()->query())->links() }}</div>
        </div>
    </div>
</x-app-layout>