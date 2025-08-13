<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalle del Pedido: <span class="text-blue-600">{{ $order->so_number }}</span>
            </h2>
            <a href="{{ route('customer-service.orders.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-gray-700">
                &larr; Volver a la Lista
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-screen-xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h3 class="text-2xl font-bold text-[#2c3856]">{{ $order->customer_name }}</h3>
                        <p class="text-sm text-gray-500">SO: {{ $order->so_number }} | OC: {{ $order->purchase_order ?? 'N/A' }}</p>
                    </div>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full 
                        @switch($order->status)
                            @case('Cancelado') bg-red-100 text-red-800 @break
                            @case('En Planificación') bg-blue-100 text-blue-800 @break
                            @default bg-gray-100 text-gray-800
                        @endswitch
                    ">{{ $order->status }}</span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-6 text-sm border-b pb-6">
                    <div><strong class="block text-gray-500">Fecha Creación:</strong><span>{{ $order->creation_date->format('d/m/Y') }}</span></div>
                    <div><strong class="block text-gray-500">Fecha Autorización:</strong><span>{{ $order->authorization_date->format('d/m/Y') }}</span></div>
                    <div><strong class="block text-gray-500">Canal:</strong><span>{{ $order->channel }}</span></div>
                    <div><strong class="block text-gray-500">Almacén Origen:</strong><span>{{ $order->origin_warehouse }}</span></div>
                    <div><strong class="block text-gray-500">Total Botellas:</strong><span>{{ $order->total_bottles }}</span></div>
                    <div><strong class="block text-gray-500">Total Cajas:</strong><span>{{ number_format($order->total_boxes, 0) }}</span></div>
                    <div class="col-span-full"><strong class="block text-gray-500">Subtotal:</strong><span>${{ number_format($order->subtotal, 2) }}</span></div>
                </div>

                <h4 class="text-lg font-semibold text-gray-800 mt-6 mb-4">Información Logística y Facturación</h4>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-6 text-sm border-b pb-6">
                    <div><strong class="block text-gray-500">Factura:</strong><span>{{ $order->invoice_number ?? 'Sin dato' }}</span></div>
                    <div><strong class="block text-gray-500">Fecha Factura:</strong><span>{{ $order->invoice_date ? $order->invoice_date->format('d/m/Y') : 'Sin dato' }}</span></div>
                    <div><strong class="block text-gray-500">Fecha Entrega:</strong><span>{{ $order->delivery_date ? $order->delivery_date->format('d/m/Y') : 'Sin dato' }}</span></div>
                    <div><strong class="block text-gray-500">Horario:</strong><span>{{ $order->schedule ?? 'Sin dato' }}</span></div>
                    <div class="col-span-2"><strong class="block text-gray-500">Dirección:</strong><span>{{ $order->shipping_address ?? 'Sin dato' }}</span></div>
                    <div><strong class="block text-gray-500">Localidad Destino:</strong><span>{{ $order->destination_locality ?? 'Sin dato' }}</span></div>
                    <div><strong class="block text-gray-500">Contacto Cliente:</strong><span>{{ $order->client_contact ?? 'Sin dato' }}</span></div>
                    <div><strong class="block text-gray-500">Ejecutivo:</strong><span>{{ $order->executive ?? 'Sin dato' }}</span></div>
                    <div class="col-span-full"><strong class="block text-gray-500">Observaciones:</strong><p class="mt-1 text-gray-700">{{ $order->observations ?? 'Sin dato' }}</p></div>
                </div>
                
                <h4 class="text-lg font-semibold text-gray-800 mt-6 mb-4">Detalle de SKUs</h4>
                <div class="max-h-48 overflow-y-auto border rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-4 py-2 text-left">SKU</th>
                                <th class="px-4 py-2 text-left">Descripción</th>
                                <th class="px-4 py-2 text-left">Cantidad</th>
                                <th class="px-4 py-2 text-left">Enviada</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($order->details as $detail)
                                <tr>
                                    <td class="px-4 py-2">{{ $detail->sku }}</td>
                                    <td class="px-4 py-2">{{ $detail->product->description ?? 'N/A' }}</td>
                                    <td class="px-4 py-2">{{ $detail->quantity }}</td>
                                    <td class="px-4 py-2">{{ $detail->sent ?? '0' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                 <div class="mt-8 flex justify-end gap-4">
                    <a href="{{ route('customer-service.orders.edit', $order) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-indigo-700">Editar Información</a>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <h4 class="text-lg font-semibold text-gray-800 mb-6">Línea de Tiempo del Pedido</h4>
                <div class="border-l-2 border-blue-500 pl-6 space-y-8 relative">
                    @forelse($order->events as $event)
                        <div class="relative">
                            <div class="absolute -left-[33px] top-1 h-4 w-4 bg-blue-500 rounded-full border-4 border-white"></div>
                            <div class="ml-4">
                                <p class="font-medium text-gray-800 text-sm">{{ $event->description }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $event->created_at->format('d/m/Y H:i A') }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="relative"><div class="absolute -left-[33px] top-1 h-4 w-4 bg-gray-300 rounded-full border-4 border-white"></div><p class="text-sm text-gray-500 ml-4">No hay eventos registrados.</p></div>
                    @endforelse
                     <div class="relative">
                        <div class="absolute -left-[33px] top-1 h-4 w-4 bg-gray-300 rounded-full border-4 border-white"></div>
                        <div class="ml-4">
                            <p class="font-medium text-gray-800 text-sm">Pedido creado por {{ $order->createdBy->name ?? 'Sistema' }}</p>
                             <p class="text-xs text-gray-500 mt-1">{{ $order->created_at->format('d/m/Y H:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>