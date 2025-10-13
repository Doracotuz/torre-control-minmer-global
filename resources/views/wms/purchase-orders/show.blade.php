<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalle de Orden de Compra: {{ $purchaseOrder->po_number }}
            </h2>
            <a href="{{ route('wms.purchase-orders.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm font-semibold hover:bg-gray-300">
                <i class="fas fa-arrow-left mr-2"></i> Volver al Listado
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Muestra mensajes de éxito o error --}}
            @if (session('success'))
                <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert"><p>{{ session('success') }}</p></div>
            @endif
            @if (session('error'))
                 <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert"><p>{{ session('error') }}</p></div>
            @endif

            <div class="bg-white p-8 rounded-lg shadow-xl space-y-8">

                <div>
                    <h3 class="font-bold text-xl text-gray-800 border-b pb-3 mb-4">Información General</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-sm">
                        <div>
                            <p class="text-gray-500 font-semibold">Nº de PO</p>
                            <p class="text-gray-900 font-mono">{{ $purchaseOrder->po_number }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 font-semibold">Fecha Esperada</p>
                            <p class="text-gray-900">{{ Carbon\Carbon::parse($purchaseOrder->expected_date)->format('d M, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 font-semibold">Creado por</p>
                            <p class="text-gray-900">{{ $purchaseOrder->user->name }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 font-semibold">Estatus</p>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">{{ $purchaseOrder->status }}</span>
                        </div>
                        <div>
                            <p class="text-gray-500 font-semibold">Contenedor</p>
                            <p class="text-gray-900 font-mono">{{ $purchaseOrder->container_number ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 font-semibold">Documento/Factura</p>
                            <p class="text-gray-900 font-mono">{{ $purchaseOrder->document_invoice ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 font-semibold">Pedimento A4</p>
                            <p class="text-gray-900 font-mono">{{ $purchaseOrder->pedimento_a4 ?? 'N/A' }}</p>
                        </div>
                         <div>
                            <p class="text-gray-500 font-semibold">Pedimento G1</p>
                            <p class="text-gray-900 font-mono">{{ $purchaseOrder->pedimento_g1 ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="font-bold text-xl text-gray-800 border-b pb-3 mb-4">Productos a Recibir</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Cantidad Ordenada</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($purchaseOrder->lines as $line)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-mono">{{ $line->product->sku }}</td>
                                        <td class="px-4 py-3 font-medium">{{ $line->product->name }}</td>
                                        <td class="px-4 py-3 text-right font-bold text-lg">{{ $line->quantity_ordered }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="border-t pt-6">
                    @if ($purchaseOrder->status == 'Pending')
                        <h3 class="font-bold text-xl text-gray-800 mb-2">Iniciar Proceso de Recepción</h3>
                        <p class="text-sm text-gray-600 mb-4">Esto te llevará a la interfaz de recepción optimizada para dispositivos móviles donde podrás crear tarimas y registrar los productos recibidos.</p>
                        <a href="{{ route('wms.receiving.show', $purchaseOrder) }}" class="inline-block px-8 py-3 bg-green-600 text-white font-semibold rounded-md shadow-md hover:bg-green-700 text-lg transition-transform hover:scale-105">
                            <i class="fas fa-pallet mr-2"></i> Iniciar Recepción
                        </a>
                    @else
                        <div class="text-center text-gray-600 font-semibold p-4 bg-gray-50 rounded-lg">
                            <i class="fas fa-info-circle mr-2"></i>
                            Esta orden ya se encuentra en proceso (Estatus: {{ $purchaseOrder->status }}) y no puede ser recibida nuevamente.
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-app-layout>