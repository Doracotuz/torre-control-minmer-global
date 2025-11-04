<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-[#2c3856] leading-tight">
            {{ __('Reporte de Antigüedad de Inventario') }}
        </h2>
        <p class="text-gray-600 text-sm mt-1">Inventario detallado por LPN, ordenado del más antiguo al más nuevo.</p>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">

            <div class="bg-white p-4 rounded-lg shadow-md mb-6">
                <form method="GET" action="{{ route('wms.reports.inventory-aging') }}" class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <div>
                        <label for="warehouse_id" class="block text-sm font-medium text-gray-700">Almacén</label>
                        <select name="warehouse_id" id="warehouse_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Todos</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>                    
                    <div>
                        <label for="sku" class="block text-sm font-medium text-gray-700">SKU</label>
                        <input type="text" name="sku" id="sku" value="{{ request('sku') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Buscar SKU...">
                    </div>
                    <div>
                        <label for="lpn" class="block text-sm font-medium text-gray-700">LPN</label>
                        <input type="text" name="lpn" id="lpn" value="{{ request('lpn') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Buscar LPN...">
                    </div>
                    <div>
                        <label for="age_bucket" class="block text-sm font-medium text-gray-700">Rango de Antigüedad</label>
                        <select name="age_bucket" id="age_bucket" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Todos</option>
                            <option value="0-30" {{ request('age_bucket') == '0-30' ? 'selected' : '' }}>0-30 días</option>
                            <option value="31-60" {{ request('age_bucket') == '31-60' ? 'selected' : '' }}>31-60 días</option>
                            <option value="61-90" {{ request('age_bucket') == '61-90' ? 'selected' : '' }}>61-90 días</option>
                            <option value="90+" {{ request('age_bucket') == '90+' ? 'selected' : '' }}>90+ días</option>
                        </select>
                    </div>
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#2c3856] hover:bg-[#1f2940]">
                            Filtrar
                        </button>
                        <a href="{{ route('wms.reports.inventory-aging') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Limpiar
                        </a>
                    </div>
                    <div class="flex items-end justify-end">
                         <a href="{{ route('wms.reports.inventory-aging.export', request()->query()) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                            Exportar a CSV
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Antigüedad</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">LPN / Ubicación</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU / Producto</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Calidad</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Origen / Fecha Rec.</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($agingItems as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @php
                                                $color = 'bg-green-100 text-green-800';
                                                if ($item->age_in_days > 60) $color = 'bg-yellow-100 text-yellow-800';
                                                if ($item->age_in_days > 90) $color = 'bg-red-100 text-red-800';
                                            @endphp
                                            <span class="px-3 py-1 inline-flex text-lg font-bold rounded-full {{ $color }}">
                                                {{ $item->age_in_days }} días
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->pallet->lpn ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">{{ $item->pallet->location->code ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->product->sku ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">{{ $item->product->name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $item->quality->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-lg font-bold text-gray-900">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div>{{ $item->pallet->purchaseOrder->po_number ?? 'N/A' }}</div>
                                        <div>{{ $item->pallet->created_at->format('Y-m-d') }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        No se encontró inventario que coincida con los filtros seleccionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $agingItems->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>