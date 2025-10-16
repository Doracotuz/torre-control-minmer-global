<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div>
                <h2 class="font-bold text-3xl text-gray-800 leading-tight tracking-tight">
                    Centro de Comando de Arribo
                </h2>
                <p class="text-md text-gray-500 mt-1">
                    Orden de Compra: <span class="font-mono text-indigo-600 font-semibold">{{ $purchaseOrder->po_number }}</span>
                </p>
            </div>
            <a href="{{ route('wms.purchase-orders.index') }}" class="mt-4 md:mt-0 px-5 py-2 bg-white border border-gray-300 text-gray-700 font-semibold rounded-lg shadow-sm hover:bg-gray-50 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Volver al Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-screen-xl mx-auto sm:px-6 lg:px-8 space-y-8">
            {{-- Bloque de Mensajes y Alertas --}}
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md shadow-md" role="alert">
                    <div class="flex"><div class="py-1"><i class="fas fa-check-circle mr-3"></i></div><div><p class="font-bold">{{ session('success') }}</p></div></div>
                </div>
            @endif
            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md shadow-md">
                    <p class="font-bold">Error de validación:</p>
                    <ul class="list-disc list-inside text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

                <div class="lg:col-span-2 bg-white p-8 rounded-2xl shadow-xl border border-gray-200 space-y-10">
                    
                    <div>
                        <div class="flex items-center mb-4">
                            <div class="bg-gray-100 p-3 rounded-full mr-4"><i class="fas fa-file-invoice-dollar text-gray-600 fa-lg"></i></div>
                            <h3 class="font-bold text-xl text-gray-800">Información General</h3>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4 text-sm pl-16">
                            <div><p class="text-gray-500">Nº de PO</p><p class="font-mono font-semibold">{{ $purchaseOrder->po_number }}</p></div>
                            <div><p class="text-gray-500">Fecha Esperada</p><p class="font-semibold">{{ Carbon\Carbon::parse($purchaseOrder->expected_date)->format('d M, Y') }}</p></div>
                            <div><p class="text-gray-500">Estado</p><span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $purchaseOrder->status == 'Pending' ? 'bg-yellow-100 text-yellow-800' : ($purchaseOrder->status == 'Receiving' ? 'bg-indigo-100 text-indigo-800' : 'bg-green-100 text-green-800') }}">{{ $purchaseOrder->status_in_spanish }}</span></div>
                            <div><p class="text-gray-500">Contenedor</p><p class="font-mono font-semibold">{{ $purchaseOrder->container_number ?? 'N/A' }}</p></div>
                            <div><p class="text-gray-500">Factura</p><p class="font-mono font-semibold">{{ $purchaseOrder->document_invoice ?? 'N/A' }}</p></div>
                            <div><p class="text-gray-500">Creado por</p><p class="font-semibold">{{ $purchaseOrder->user->name }}</p></div>
                        </div>
                    </div>

                    <div class="border-t pt-8">
                        <div class="flex items-center mb-4">
                            <div class="bg-gray-100 p-3 rounded-full mr-4"><i class="fas fa-boxes text-gray-600 fa-lg"></i></div>
                            <h3 class="font-bold text-xl text-gray-800">Resumen de Recepción de Productos</h3>
                        </div>
                        @php $summary = $purchaseOrder->getReceiptSummary(); @endphp
                        <div class="overflow-x-auto pl-16">
                            <table class="min-w-full">
                                <thead class="border-b-2 border-gray-200">
                                    <tr>
                                        <th class="px-2 py-2 text-left text-xs font-bold text-gray-500 uppercase">SKU / Producto</th>
                                        <th class="px-2 py-2 text-right text-xs font-bold text-gray-500 uppercase">Ordenado</th>
                                        <th class="px-2 py-2 text-right text-xs font-bold text-gray-500 uppercase">Recibido</th>
                                        <th class="px-2 py-2 text-right text-xs font-bold text-gray-500 uppercase">Pallets</th>
                                        <th class="px-2 py-2 text-right text-xs font-bold text-gray-500 uppercase">Diferencia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($summary as $line)
                                        @php $diff = $line->quantity_received - $line->quantity_ordered; @endphp
                                        <tr class="border-b border-gray-100">
                                            <td class="px-2 py-4"><p class="font-mono text-indigo-600 font-semibold">{{ $line->sku }}</p><p class="text-sm font-medium text-gray-800">{{ $line->product_name }}</p></td>
                                            <td class="px-2 py-4 text-right font-medium text-gray-600">{{ number_format($line->quantity_ordered) }}</td>
                                            <td class="px-2 py-4 text-right font-bold text-lg text-gray-900">{{ number_format($line->quantity_received) }}</td>
                                            <td class="px-2 py-4 text-right font-medium text-gray-600">{{ $line->pallet_count }}</td>
                                            <td class="px-2 py-4 text-right font-bold text-lg {{ $diff == 0 && $line->quantity_received > 0 ? 'text-green-600' : ($diff != 0 ? 'text-red-600' : 'text-gray-500') }}">{{ $diff > 0 ? '+' : '' }}{{ number_format($diff) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="border-t pt-8">
                        <div class="flex items-center mb-4">
                            <div class="bg-gray-100 p-3 rounded-full mr-4"><i class="fas fa-history text-gray-600 fa-lg"></i></div>
                            <h3 class="font-bold text-xl text-gray-800">Historial de Tarimas Recibidas</h3>
                        </div>
                        <div class="space-y-3 pl-16">
                            @forelse($purchaseOrder->pallets as $pallet)
                                <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                                    <div class="flex justify-between items-center">
                                        <p class="font-mono font-bold text-indigo-800">{{ $pallet->lpn }}</p>
                                        <div class="text-xs font-semibold text-gray-500 text-right">
                                            <p>Recibido por: {{ $pallet->user->name ?? 'N/A' }}</p>
                                            <p>{{ $pallet->updated_at->format('d/M/y h:i A') }}</p>
                                        </div>
                                    </div>
                                    <ul class="text-xs mt-2 space-y-1 border-t pt-2">
                                        @foreach($pallet->items as $item)
                                            <li class="flex justify-between">
                                                <span><strong class="text-indigo-700">[{{ $item->quality->name ?? 'N/A' }}]</strong> {{ $item->product->name ?? 'Producto no encontrado' }}</span>
                                                <span class="font-semibold">x {{ $item->quantity }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @empty
                                <div class="text-center text-gray-500 py-6">
                                    <i class="fas fa-pallet fa-2x mb-2"></i>
                                    <p>No se han registrado tarimas para esta orden.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="space-y-8">
                    <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-200">
                        <h3 class="font-bold text-lg text-gray-800 mb-4 flex items-center"><i class="fas fa-truck text-gray-400 mr-3"></i>Gestión de Patio</h3>
                        
                        @if(!$purchaseOrder->download_start_time)
                            <form action="{{ route('wms.purchase-orders.register-arrival', $purchaseOrder) }}" method="POST" class="space-y-3">
                                @csrf
                                <input type="text" name="truck_plate" placeholder="Placas del Vehículo" required class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <input type="text" name="driver_name" placeholder="Nombre del Operador" required class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <button type="submit" class="w-full mt-2 px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition-all"><i class="fas fa-sign-in-alt mr-2"></i>Registrar Llegada</button>
                            </form>
                        @else
                            <div class="text-sm space-y-3 bg-gray-50 p-4 rounded-lg">
                                <div class="flex justify-between"><span>Operador:</span> <strong class="text-gray-900">{{ $purchaseOrder->operator_name ?? 'N/A' }}</strong></div>
                                <div class="flex justify-between"><span>Llegada:</span> <strong class="text-gray-900">{{ Carbon\Carbon::parse($purchaseOrder->download_start_time)->format('d/M/y h:i A') }}</strong></div>
                                <div class="flex justify-between"><span>Salida:</span> <strong class="text-gray-900">{{ $purchaseOrder->download_end_time ? Carbon\Carbon::parse($purchaseOrder->download_end_time)->format('d/M/y h:i A') : '---' }}</strong></div>
                            </div>
                            @if(!$purchaseOrder->download_end_time)
                                <form action="{{ route('wms.purchase-orders.register-departure', $purchaseOrder) }}" method="POST" class="mt-4">
                                    @csrf
                                    <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white font-semibold rounded-lg shadow-md hover:bg-red-700 transition-all"><i class="fas fa-sign-out-alt mr-2"></i>Registrar Salida</button>
                                </form>
                            @endif
                        @endif
                    </div>
                    
                    @if (($purchaseOrder->status == 'Receiving' || $purchaseOrder->status == 'Pending') && $purchaseOrder->received_bottles < $purchaseOrder->expected_bottles)
                    <div class="bg-gradient-to-br from-green-500 to-green-600 p-6 rounded-2xl shadow-xl text-center">
                        <h3 class="text-white font-bold text-lg mb-2">Siguiente Paso</h3>
                        <p class="text-green-100 text-sm mb-4">Continúa con el registro de productos en la interfaz de recepción física.</p>
                        <a href="{{ route('wms.receiving.show', $purchaseOrder) }}" class="inline-block px-8 py-3 bg-white text-green-600 font-bold rounded-lg shadow-md hover:bg-green-50 transition-transform hover:scale-105">
                            <i class="fas fa-pallet mr-2"></i> Ir a Recepción Física
                        </a>
                    </div>
                    @endif
                    @if ($purchaseOrder->status == 'Receiving')
                    <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-200">
                        <h3 class="font-bold text-lg text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-tasks text-gray-400 mr-3"></i>Acciones Finales
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Una vez que toda la mercancía física haya sido registrada, cierra la orden para marcar el proceso de recepción como finalizado.
                        </p>
                        
                        <form action="{{ route('wms.purchase-orders.complete', $purchaseOrder) }}" method="POST"
                            onsubmit="return confirm('¿Estás seguro de que deseas cerrar y completar esta orden de compra? Esta acción no se puede deshacer.');">
                            @csrf
                            
                            {{-- Condición para mostrar advertencia si está incompleta --}}
                            @if ($purchaseOrder->received_bottles < $purchaseOrder->expected_bottles)
                                <div class="my-3 p-3 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 text-xs rounded-md">
                                    <strong>Atención:</strong> La recepción está incompleta. Al cerrar, se aceptará la diferencia.
                                </div>
                            @endif

                            <button type="submit" class="w-full mt-2 px-4 py-3 bg-gray-800 text-white font-bold rounded-lg shadow-md hover:bg-gray-900 transition-all">
                                <i class="fas fa-lock mr-2"></i> Cerrar y Completar Orden
                            </button>
                        </form>
                    </div>
                    @endif                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>