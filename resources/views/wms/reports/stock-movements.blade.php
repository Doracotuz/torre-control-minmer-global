<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-y-4">
            <div class="flex items-center space-x-3">
                <h2 class="font-bold text-3xl text-gray-800 leading-tight tracking-tight">
                    Historial de Movimientos
                </h2>
            </div>

            <a href="{{ route('wms.reports.stock-movements.export', request()->query()) }}"
               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-500 to-green-600 text-white rounded-lg shadow-md hover:shadow-lg hover:from-emerald-600 hover:to-green-700 transition-all duration-300 ease-in-out transform hover:-translate-y-px">
                <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span class="text-sm font-semibold tracking-wide">Exportar a CSV</span>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-screen-3xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <div class="bg-gradient-to-br from-white to-gray-50 p-6 rounded-2xl shadow-lg border border-gray-200">
                <form id="filters-form" action="{{ route('wms.reports.stock-movements') }}" method="GET">
                     <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-7 gap-x-6 gap-y-4 items-end">
                        <div>
                            <label for="warehouse_id" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Almacén</label>
                            <select id="warehouse_id" name="warehouse_id" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3 appearance-none bg-white pr-8 bg-no-repeat" style="background-image: url('data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3E%3Cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3E%3C/svg%3E'); background-position: right 0.5rem center; background-size: 1.5em 1.5em;">
                                <option value="">Todos</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>                        
                        <div><label for="start_date" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Desde</label><input type="date" id="start_date" name="start_date" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3" value="{{ request('start_date') }}"></div>
                        <div><label for="end_date" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Hasta</label><input type="date" id="end_date" name="end_date" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3" value="{{ request('end_date') }}"></div>
                        <div><label for="sku" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">SKU</label><input type="text" id="sku" name="sku" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3" placeholder="Buscar SKU..." value="{{ request('sku') }}"></div>
                        <div><label for="lpn" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">LPN</label><input type="text" id="lpn" name="lpn" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3" placeholder="Buscar LPN..." value="{{ request('lpn') }}"></div>
                        <div><label for="movement_type" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Tipo Movimiento</label><select id="movement_type" name="movement_type" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3 appearance-none bg-white pr-8 bg-no-repeat" style="background-image: url('data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3E%3Cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3E%3C/svg%3E'); background-position: right 0.5rem center; background-size: 1.5em 1.5em;"><option value="">Todos los Tipos</option>@foreach($movementTypes as $type)<option value="{{ $type }}" @selected(request('movement_type') == $type)>{{ Str::title(str_replace('-', ' ', $type)) }}</option>@endforeach</select></div>
                        <div class="pt-5"><a href="{{ route('wms.reports.stock-movements') }}" class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg shadow-sm hover:bg-gray-300 text-sm font-semibold transition duration-150 ease-in-out" title="Limpiar filtros"><svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>Limpiar</a></div>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden rounded-2xl shadow-lg border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Hora</th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Usuario</th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Producto</th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">LPN</th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Ubicación</th>
                                <th scope="col" class="px-5 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Cantidad</th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">PO Origen</th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Pedimento</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($movements as $mov)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-700">{{ $mov->created_at->format('d/m/Y') }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500">{{ $mov->created_at->format('h:i A') }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm font-medium text-gray-800">{{ $mov->user->name ?? 'Sistema' }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        @php
                                            $isPositive = $mov->quantity > 0;
                                            $tagColor = 'bg-blue-100 text-blue-800'; // Default: Ajuste
                                            if (Str::contains($mov->movement_type, ['RECEPCION', 'TRANSFER-IN', 'SPLIT-IN'])) $tagColor = 'bg-green-100 text-green-800';
                                            elseif (Str::contains($mov->movement_type, ['SALIDA', 'PICKING', 'TRANSFER-OUT', 'SPLIT-OUT'])) $tagColor = 'bg-red-100 text-red-800';
                                            elseif (Str::contains($mov->movement_type, 'AJUSTE')) $tagColor = 'bg-yellow-100 text-yellow-800';
                                        @endphp
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $tagColor }}">
                                            {{ Str::title(str_replace('-', ' ', $mov->movement_type)) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $mov->product->name ?? 'N/A' }}</div>
                                        <div class="text-xs text-gray-500 font-mono">{{ $mov->product->sku ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm font-mono text-indigo-600 font-semibold">{{ $mov->palletItem->pallet->lpn ?? 'N/A' }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-700">
                                        @if ($mov->location)
                                            {{ $mov->location->aisle ?? '?' }}-{{ $mov->location->rack ?? '?' }}-{{ $mov->location->shelf ?? '?' }}-{{ $mov->location->bin ?? '?' }}
                                            {{-- ({{ $mov->location->code }}) --}}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-center">
                                        <span class="text-lg font-bold {{ $mov->quantity > 0 ? 'text-green-600' : ($mov->quantity < 0 ? 'text-red-600' : 'text-gray-500') }}">
                                            {{ $mov->quantity > 0 ? '+' : '' }}{{ number_format($mov->quantity) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm font-mono text-gray-600">{{ $mov->palletItem->pallet->purchaseOrder->po_number ?? 'N/A' }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm font-mono text-gray-600">{{ $mov->palletItem->pallet->purchaseOrder->pedimento_a4 ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-16 px-6">
                                         <svg class="mx-auto h-12 w-12 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                        </svg>
                                        <h3 class="mt-2 text-lg font-semibold text-gray-800">No se encontraron movimientos</h3>
                                        <p class="mt-1 text-sm text-gray-500">Intenta ajustar los filtros o verifica si se han registrado transacciones.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($movements->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        {{ $movements->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>