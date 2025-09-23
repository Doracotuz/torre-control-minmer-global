<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalle del Pedido: <span class="text-blue-600">{{ $order->so_number }}</span>
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('customer-service.orders.reverse-logistics.create', $order) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-blue-700">
                    Logística Inversa
                </a>
                <a href="{{ route('customer-service.orders.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-gray-700">
                    &larr; Volver a la Lista
                </a>
            </div>
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

                {{-- ✅ INICIA CAMBIO: Cálculo del porcentaje de llenado --}}
                @php
                    $fields = [
                        $order->bt_oc, $order->invoice_number, $order->invoice_date,
                        $order->delivery_date, $order->schedule, $order->client_contact,
                        $order->shipping_address, $order->destination_locality, $order->executive,
                        $order->observations, $order->evidence_reception_date, $order->evidence_cutoff_date
                    ];
                    $totalFields = count($fields);
                    $filledFields = collect($fields)->filter(fn($value) => !empty($value))->count();
                    $percentage = ($totalFields > 0) ? ($filledFields / $totalFields) * 100 : 0;
                @endphp

                <div class="my-6">
                    <div class="flex justify-between mb-1">
                        <span class="text-base font-medium text-[#2c3856]">Progreso de Información Logística</span>
                        <span class="text-sm font-medium text-[#2c3856]">{{ number_format($percentage, 0) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
                    </div>
                </div>
                {{-- ⏹️ TERMINA CAMBIO --}}

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
                    <div>
                        <strong class="block text-gray-500">Sobredimensionado:</strong>
                        <span>
                            @if($order->is_oversized)
                                <span class="px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded-full">Sí</span>
                            @else
                                No
                            @endif
                        </span>
                    </div>                    
                    {{-- ✅ INICIA CAMBIO: Se añade el nuevo campo --}}
                    <div><strong class="block text-gray-500">Recepción de Evidencia:</strong><span>{{ $order->evidence_reception_date ? $order->evidence_reception_date->format('d/m/Y') : 'Sin dato' }}</span></div>
                    <div><strong class="block text-gray-500">Corte de Evidencias:</strong><span>{{ $order->evidence_cutoff_date ? $order->evidence_cutoff_date->format('d/m/Y') : 'Sin dato' }}</span></div>
                    {{-- ⏹️ TERMINA CAMBIO --}}
                    
                    <div class="col-span-full"><strong class="block text-gray-500">Observaciones:</strong><p class="mt-1 text-gray-700">{{ $order->observations ?? 'Sin dato' }}</p></div>
                </div>

                <h4 class="text-lg font-semibold text-gray-800 mt-6 mb-4">Evidencias Adjuntas</h4>
                <div class="space-y-2">
                    @forelse ($order->evidences as $evidence)
                        <a href="{{ Storage::disk('s3')->url($evidence->file_path) }}" target="_blank" class="flex items-center bg-gray-50 p-2 rounded-md border hover:bg-gray-100">
                            <i class="fas fa-file-alt text-gray-500 mr-2"></i>
                            <span class="text-blue-600 text-sm">{{ $evidence->file_name }}</span>
                        </a>
                    @empty
                        <p class="text-sm text-gray-500">No hay evidencias adjuntas.</p>
                    @endforelse
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
                <h4 class="text-lg font-semibold text-gray-800 mb-6">Línea de Tiempo</h4>
                <div class="border-l-2 border-gray-300 pl-6 space-y-8 relative">

                    @forelse($timelineEvents as $event)
                        <div class="relative">
                            {{-- El color del punto cambia dinámicamente --}}
                            <div class="absolute -left-[33px] top-1 h-4 w-4 bg-{{ $event['color'] }}-500 rounded-full border-4 border-white"></div>
                            <div class="ml-4">
                                <p class="font-medium text-gray-800 text-sm">
                                    {{-- La etiqueta de tipo también es dinámica --}}
                                    <span class="inline-block mr-2 px-2 py-0.5 text-xs font-semibold text-{{ $event['color'] }}-800 bg-{{ $event['color'] }}-100 rounded-full">
                                        {{ $event['type'] }}
                                    </span>
                                    {{ $event['description'] }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{-- La fecha y el nombre de usuario se muestran correctamente --}}
                                    {{ $event['date']->format('d/m/Y H:i A') }} por {{ $event['user_name'] }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No hay eventos registrados.</p>
                    @endforelse

                </div>
            </div>
        </div>
    </div>
</x-app-layout>