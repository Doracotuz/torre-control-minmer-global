<x-app-layout>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="font-bold text-3xl text-gray-800 text-center md:text-left">Dashboard de Arribos</h2>
            <div class="flex items-center space-x-2">
                <a href="{{ route('wms.purchase-orders.export-csv', request()->query()) }}" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-green-700">
                    <i class="fas fa-file-excel mr-2"></i> Exportar
                </a>
                <a href="{{ route('wms.purchase-orders.create') }}" class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-gray-700">
                    <i class="fas fa-plus-circle mr-2"></i> Nueva Orden
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 text-white p-6 rounded-2xl shadow-lg flex items-center justify-between"><div><span class="text-sm font-light">En Recepción</span><p class="text-3xl font-bold">{{ $kpis['receiving'] }}</p></div><i class="fas fa-truck-loading fa-3x opacity-50"></i></div>
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 rounded-2xl shadow-lg flex items-center justify-between"><div><span class="text-sm font-light">Arribos de Hoy</span><p class="text-3xl font-bold">{{ $kpis['arrivals_today'] }}</p></div><i class="fas fa-calendar-day fa-3x opacity-50"></i></div>
                <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white p-6 rounded-2xl shadow-lg flex items-center justify-between"><div><span class="text-sm font-light">Pendientes</span><p class="text-3xl font-bold">{{ $kpis['pending'] }}</p></div><i class="fas fa-clock fa-3x opacity-50"></i></div>
                <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-6 rounded-2xl shadow-lg flex items-center justify-between"><div><span class="text-sm font-light">T. Promedio Descarga</span><p class="text-3xl font-bold">{{ $kpis['avg_unload_time'] }} <span class="text-xl">min</span></p></div><i class="fas fa-hourglass-half fa-3x opacity-50"></i></div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-lg border mb-8">
                <form action="{{ route('wms.purchase-orders.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <select name="warehouse_id" id="warehouse_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" onchange="this.form.submit()">
                            <option value="">-- Todos los Almacenes --</option>
                            
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @if($warehouseId == $warehouse->id) selected @endif>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>                      
                    <input type="text" name="search" class="rounded-md border-gray-300 shadow-sm" placeholder="Buscar N° Orden o Contenedor..." value="{{ request('search') }}">
                    <input type="text" name="sku" class="rounded-md border-gray-300 shadow-sm" placeholder="Buscar por SKU..." value="{{ request('sku') }}">
                    <select name="status" class="rounded-md border-gray-300 shadow-sm">
                        <option value="">Todos los Estados</option>
                        <option value="Pending" @selected(request('status') == 'Pending')>Pendiente</option>
                        <option value="Receiving" @selected(request('status') == 'Receiving')>En Recepción</option>
                        <option value="Completed" @selected(request('status') == 'Completed')>Completada</option>
                    </select>                  
                    <div class="flex items-center space-x-2"><button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700">Filtrar</button><a href="{{ route('wms.purchase-orders.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300" title="Limpiar filtros"><i class="fas fa-undo"></i></a></div>
                </form>
            </div>

            <div class="hidden md:block bg-white overflow-hidden rounded-2xl shadow-lg border">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="w-1"></th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Orden / Contenedor</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Fechas</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Operador / Placas</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Progreso</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase">Estado</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($purchaseOrders as $order)
                                <tbody x-data="{ expanded: false }">
                                    <tr class="hover:bg-gray-50 cursor-pointer" @click="expanded = !expanded">
                                        <td class="px-2 py-4 text-center text-gray-400"><i class="fas" :class="expanded ? 'fa-chevron-down' : 'fa-chevron-right'"></i></td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <p class="font-bold text-gray-900 font-mono">{{ $order->po_number }}</p>
                                            <p class="text-xs text-gray-500 font-mono">{{ $order->container_number ?? 'N/A' }}</p>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm">
                                            @if(\Carbon\Carbon::parse($order->expected_date)->isPast() && $order->status != 'Completed')
                                                <p class="text-red-600 font-bold flex items-center">
                                                    <i class="fas fa-exclamation-triangle mr-2"></i> Atrasado
                                                </p>
                                            @else
                                                <p><span class="font-semibold">Esp:</span> {{ \Carbon\Carbon::parse($order->expected_date)->format('d/M/y') }}</p>
                                            @endif
                                            <p><span class="font-semibold">Arr:</span> {{ $order->download_start_time ? \Carbon\Carbon::parse($order->download_start_time)->format('d/M/y') : '---' }}</p>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm">
                                            <p>{{ $order->latestArrival->driver_name ?? 'No registrado' }}</p>
                                            <p class="text-xs text-gray-500 font-mono">{{ $order->latestArrival->truck_plate ?? 'N/A' }}</p>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            @php 
                                                $progress = $order->expected_bottles > 0 ? ($order->received_bottles / $order->expected_bottles) * 100 : 0; 
                                            @endphp
                                            <div class="w-full bg-gray-200 rounded-full h-2"><div class="bg-blue-600 h-2 rounded-full" style="width: {{ $progress }}%"></div></div>
                                            <p class="text-xs text-center mt-1">{{ round($progress) }}%</p>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $order->status == 'Pending' ? 'bg-yellow-100 text-yellow-800' : ($order->status == 'Receiving' ? 'bg-indigo-100 text-indigo-800' : 'bg-green-100 text-green-800') }}">{{ $order->status_in_spanish }}</span>
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <a href="{{ route('wms.purchase-orders.show', $order) }}" @click.stop class="px-3 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 text-xs">Gestionar</a>
                                        </td>
                                    </tr>
                                    <tr x-show="expanded" x-transition>
                                        <td colspan="7" class="p-4 bg-gray-50">
                                            <h4 class="font-bold text-sm mb-2">Detalles del Arribo:</h4>
                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs p-4 bg-white rounded-lg border">
                                                <div>
                                                    <p class="font-semibold text-gray-500">T. Inicio Descarga</p>
                                                    <p>{{ $order->download_start_time ? \Carbon\Carbon::parse($order->download_start_time)->format('h:i A') : '---' }}</p>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-gray-500">T. Fin Descarga</p>
                                                    <p>{{ $order->download_end_time ? \Carbon\Carbon::parse($order->download_end_time)->format('h:i A') : '---' }}</p>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-gray-500">Unidades Recibidas</p>
                                                    <p class="font-bold">{{ number_format($order->received_bottles) }} / {{ number_format($order->expected_bottles) }}</p>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-gray-500">Total Cajas Recibidas</p>
                                                    <p class="font-bold text-blue-600">{{ number_format($order->total_cases_received, 0) }}</p>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-gray-500">Pallets Recibidos</p>
                                                    <p class="font-bold">{{ $order->pallets_count }}</p>
                                                </div>
                                                @if($order->download_start_time && $order->download_end_time)
                                                        <div>
                                                            <p class="font-semibold text-gray-500">Duración de Descarga</p>
                                                            <p class="font-bold text-blue-600">
                                                                {{ \Carbon\Carbon::parse($order->download_start_time)->diffForHumans($order->download_end_time, true, false, 4) }}
                                                            </p>
                                                        </div>
                                                @endif
                                            </div>
                                            <h4 class="font-bold text-sm mt-4 mb-2">Productos en la Orden:</h4>
                                            <ul class="text-xs list-disc list-inside bg-white p-4 rounded-lg border">@foreach($order->lines as $line)<li><span class="font-mono">{{ $line->product->sku }}</span> - {{ $line->product->name }} (Cant: <span class="font-semibold">{{ $line->quantity_ordered }}</span>)</li>@endforeach</ul>
                                        </td>
                                    </tr>
                                </tbody>
                            @empty
                                <tr><td colspan="7" class="text-center text-gray-500 py-12"><p>No se encontraron órdenes de compra.</p></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-6 border-t">{{ $purchaseOrders->links() }}</div>
            </div>

            <div class="block md:hidden space-y-4">
                @forelse ($purchaseOrders as $order)
                    <div x-data="{ expanded: false }" class="bg-white rounded-2xl shadow-lg border">
                        <div class="p-4" @click="expanded = !expanded">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-bold text-gray-900 font-mono">{{ $order->po_number }}</p>
                                    <p class="text-xs text-gray-500 font-mono">{{ $order->container_number ?? 'N/A' }}</p>
                                </div>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $order->status == 'Pending' ? 'bg-yellow-100 text-yellow-800' : ($order->status == 'Receiving' ? 'bg-indigo-100 text-indigo-800' : 'bg-green-100 text-green-800') }}">{{ $order->status_in_spanish }}</span>
                            </div>

                            <div class="mt-4">
                                @php $progress = $order->expected_bottles > 0 ? ($order->received_bottles / $order->expected_bottles) * 100 : 0; @endphp
                                <div class="w-full bg-gray-200 rounded-full h-2"><div class="bg-blue-600 h-2 rounded-full" style="width: {{ $progress }}%"></div></div>
                                <div class="flex justify-between mt-1"><p class="text-xs text-gray-500">Recibido: <span class="font-semibold text-gray-800">{{ number_format($order->received_bottles) }} / {{ number_format($order->expected_bottles) }}</span></p><p class="text-xs font-semibold">{{ round($progress) }}%</p></div>
                            </div>

                            <div class="mt-4 pt-4 border-t text-xs grid grid-cols-2 gap-x-4 gap-y-2">
                                <div><p class="font-semibold text-gray-500">Fecha Esperada</p><p class="font-medium text-gray-800 @if(\Carbon\Carbon::parse($order->expected_date)->isPast() && $order->status != 'Completed') text-red-600 @endif">{{ \Carbon\Carbon::parse($order->expected_date)->format('d/M/y') }}</p></div>
                                <div><p class="font-semibold text-gray-500">Fecha Arribo</p><p class="font-medium text-gray-800">{{ $order->download_start_time ? \Carbon\Carbon::parse($order->download_start_time)->format('d/M/y') : '---' }}</p></div>
                                <div><p class="font-semibold text-gray-500">Operador</p><p class="font-medium text-gray-800 truncate">{{ $order->latestArrival->driver_name ?? 'No reg.' }}</p></div>
                                <div><p class="font-semibold text-gray-500">Placas</p><p class="font-medium text-gray-800 font-mono">{{ $order->latestArrival->truck_plate ?? 'N/A' }}</p></div>
                            </div>
                        </div>
                        
                        <div x-show="expanded" x-transition class="p-4 bg-gray-50 border-t">
                            <h4 class="font-bold text-sm mb-3">Detalles Adicionales</h4>
                            <div class="grid grid-cols-2 gap-4 text-xs mb-4">
                                <div><p class="font-semibold text-gray-500">Cajas Recibidas</p><p class="font-bold text-lg text-blue-600">{{ number_format($order->total_cases_received, 0) }}</p></div>
                                <div><p class="font-semibold text-gray-500">Pallets Recibidos</p><p class="font-bold text-lg">{{ $order->pallets_count }}</p></div>
                                <div><p class="font-semibold text-gray-500">Inicio Descarga</p><p class="font-medium">{{ $order->download_start_time ? \Carbon\Carbon::parse($order->download_start_time)->format('h:i A') : '---' }}</p></div>
                                <div><p class="font-semibold text-gray-500">Fin Descarga</p><p class="font-medium">{{ $order->download_end_time ? \Carbon\Carbon::parse($order->download_end_time)->format('h:i A') : '---' }}</p></div>
                            </div>
                            <h4 class="font-bold text-sm mt-3 mb-2">Productos en la Orden:</h4>
                            <ul class="text-xs list-disc list-inside bg-white p-3 rounded-lg border">
                                @foreach($order->lines as $line)<li><span class="font-mono">{{ $line->product->sku }}</span> - {{ $line->product->name }} (Cant: <span class="font-semibold">{{ $line->quantity_ordered }}</span>)</li>@endforeach
                            </ul>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 border-t flex justify-end">
                            <a href="{{ route('wms.purchase-orders.show', $order) }}" @click.stop class="px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow-md text-sm">Gestionar</a>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-500 py-12 bg-white rounded-2xl shadow-lg border"><p>No se encontraron órdenes de compra.</p></div>
                @endforelse

                 <div class="mt-4">{{ $purchaseOrders->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>