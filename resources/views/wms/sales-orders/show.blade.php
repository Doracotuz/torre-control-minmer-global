<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Orden de Venta: <span class="text-indigo-600">{{ $salesOrder->so_number }}</span>
            </h2>
            <div class="mt-4 md:mt-0">
                <a href="{{ route('wms.sales-orders.index') }}" class="text-gray-600 hover:text-indigo-600">
                    &larr; Volver al Listado
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                    <span class="font-medium">Éxito:</span> {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                    <span class="font-medium">Error:</span> {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:px-10 bg-white">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <span class="text-sm font-medium text-gray-500">Cliente</span>
                            <p class="text-lg font-semibold text-gray-900">{{ $salesOrder->customer_name }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Fecha de Entrega</span>
                            <p class="text-lg font-semibold text-gray-900">{{ $salesOrder->order_date->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Estado</span>
                            <p class="text-lg font-bold">
                                @if($salesOrder->status == 'Pending')
                                    <span class="text-yellow-600">Pendiente</span>
                                @elseif($salesOrder->status == 'Picking')
                                    <span class="text-blue-600">En Surtido (Picking)</span>
                                @elseif($salesOrder->status == 'Packed')
                                    <span class="text-green-600">Empacado</span>
                                @elseif($salesOrder->status == 'Cancelled')
                                    <span class="text-red-600">Cancelado</span>
                                @else
                                    <span class="text-gray-600">{{ $salesOrder->status }}</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Nº Factura</span>
                            <p class="text-lg text-gray-800">{{ $salesOrder->invoice_number ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Creado por</span>
                            <p class="text-lg text-gray-800">{{ $salesOrder->user->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="mt-8 border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Productos 
                        @if($salesOrder->pickList)
                            <span class="text-gray-500">(Surtidos desde Pick List #{{ $salesOrder->pickList->id }})</span>
                        @else
                            <span class="text-gray-500">(Ordenados)</span>
                        @endif
                    </h3>

                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU / Producto</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lote (LPN)</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ubicación</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Calidad</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pedimento</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                
                                @if($salesOrder->pickList && $salesOrder->pickList->items->count() > 0)
                                    
                                    @foreach($salesOrder->pickList->items as $item)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <div class="font-medium text-gray-900">{{ $item->product->sku ?? 'N/A' }}</div>
                                                <div class="text-gray-500">{{ $item->product->name ?? 'N/A' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-indigo-600">
                                                {{ $item->pallet->lpn ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-red-600">
                                                {{ $item->pallet->location->code ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                {{ $item->quality->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-500">
                                                {{ $item->pallet->purchaseOrder->pedimento_a4 ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                                {{ $item->quantity_picked ?? $item->quantity_to_pick }}
                                            </td>
                                        </tr>
                                    @endforeach

                                @else

                                    @foreach($salesOrder->lines as $line)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <div class="font-medium text-gray-900">{{ $line->product->sku ?? 'N/A' }}</div>
                                                <div class="text-gray-500">{{ $line->product->name ?? 'N/A' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 italic">
                                                {{-- Si el LPN fue pre-asignado, muéstralo --}}
                                                {{ $line->palletItem->pallet->lpn ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 italic">
                                                {{ $line->palletItem->pallet->location->code ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 italic">
                                                {{ $line->quality->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 italic">
                                                {{ $line->palletItem->pallet->purchaseOrder->pedimento_a4 ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                                {{ $line->quantity_ordered }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    
                                @endif

                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8 border-t border-gray-200 pt-6">
                        <div class="flex items-center justify-between">
                            
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Estado de Surtido</h3>
                                @if ($salesOrder->status == 'Pending')
                                    <form action="{{ route('wms.picking.generate', $salesOrder) }}" method="POST" class="mt-2">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md shadow-sm hover:bg-blue-700">
                                            Generar Pick List
                                        </button>
                                    </form>
                                @elseif ($salesOrder->pickList)
                                    <div class="mt-2 text-sm text-gray-600">
                                        <p>Pick List <strong>#{{ $salesOrder->pickList->id }}</strong> generada.</p>
                                        <p>Estado: <span class="font-medium">{{ $salesOrder->pickList->status }}</span></p>
                                        <div class="mt-2 space-x-4">
                                            <a href="{{ route('wms.picking.show', $salesOrder->pickList) }}" class="text-indigo-600 hover:underline">Ver Tarea de Surtido</a>
                                            <a href="{{ route('wms.picking.pdf', $salesOrder->pickList) }}" target="_blank" class="text-red-600 hover:underline">Descargar PDF</a>
                                        </div>
                                    </div>
                                @elseif ($salesOrder->status == 'Cancelled')
                                    <p class="mt-2 text-sm text-red-600">La orden está cancelada.</p>
                                @else
                                    <p class="mt-2 text-sm text-gray-500">La orden ha sido procesada.</p>
                                @endif
                            </div>

                            @if ($salesOrder->status == 'Pending')
                                <div class="flex items-center gap-x-4">
                                    <a href="{{ route('wms.sales-orders.edit', $salesOrder) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-md shadow-sm hover:bg-yellow-600">
                                        Editar Orden
                                    </a>
                                    <form action="{{ route('wms.sales-orders.cancel', $salesOrder) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres cancelar esta orden? Esta acción no se puede deshacer y liberará el inventario comprometido.');">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md shadow-sm hover:bg-red-700">
                                            Cancelar Orden
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>