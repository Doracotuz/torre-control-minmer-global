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

            {{-- Mensajes de éxito o error --}}
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

                    {{-- SECCIÓN 1: Información de Cabecera --}}
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

                    {{-- SECCIÓN 2: Líneas de Producto (Tabla Detallada) --}}
                    <div class="mt-8 border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-medium text-gray-900">Productos Ordenados</h3>
                        
                        <div class="mt-4 flow-root">
                            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                    <table class="min-w-full divide-y divide-gray-300">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">SKU / Producto</th>
                                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Lote (LPN)</th>
                                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Ubicación</th>
                                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Calidad</th>
                                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Pedimento</th>
                                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0 text-right text-sm font-semibold text-gray-900">Cantidad</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @forelse ($salesOrder->lines as $line)
                                                <tr>
                                                    <td class="py-4 pl-4 pr-3 text-sm sm:pl-0">
                                                        <div class="font-medium text-gray-900">{{ $line->product->name ?? 'Producto no encontrado' }}</div>
                                                        <div class="text-gray-500">{{ $line->product->sku ?? 'N/A' }}</div>
                                                    </td>
                                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700 font-mono">
                                                        {{ $line->palletItem->pallet->lpn ?? 'N/A' }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 font-mono">
                                                        @if($location = $line->palletItem->pallet->location ?? null)
                                                            {{ $location->aisle }}-{{ $location->rack }}-{{ $location->shelf }}
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                        {{ $line->palletItem->quality->name ?? 'N/A' }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                        {{ $line->palletItem->pallet->purchaseOrder->pedimento_a4 ?? 'N/A' }}
                                                    </td>
                                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-bold text-gray-900 sm:pr-0">
                                                        {{ $line->quantity_ordered }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="px-3 py-4 text-center text-sm text-gray-500">
                                                        No hay productos en esta orden.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 3: Acciones --}}
                    <div class="mt-8 border-t border-gray-200 pt-6">
                        <div class="flex items-center justify-between">
                            
                            {{-- Lado Izquierdo: Acciones de Flujo (Picking) --}}
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

                            {{-- Lado Derecho: Acciones de Edición/Cancelación --}}
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