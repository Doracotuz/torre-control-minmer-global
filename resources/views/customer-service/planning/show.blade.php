<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                @if($planning->order)
                    Detalle de Planificación: <span class="text-blue-600">{{ $planning->so_number }}</span>
                @else
                    Detalle de Planificación Manual: <span class="text-blue-600">{{ $planning->factura }}</span>
                @endif
            </h2>
            <a href="{{ route('customer-service.planning.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-gray-700">
                &larr; Volver a la Lista
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-screen-xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h3 class="text-2xl font-bold text-[#2c3856]">{{ $planning->razon_social }}</h3>
                        <p class="text-sm text-gray-500">
                            @if($planning->so_number) SO: {{ $planning->so_number }} | @endif
                            Factura: {{ $planning->factura ?? 'N/A' }}
                        </p>
                    </div>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full 
                        @if($planning->status == 'En Espera') bg-yellow-100 text-yellow-800
                        @elseif($planning->status == 'Programada') bg-green-100 text-green-800
                        @elseif($planning->status == 'Asignado en Guía') bg-blue-100 text-blue-800
                        @else bg-gray-100 text-gray-800 @endif
                    ">{{ $planning->status }}</span>
                </div>

                <h4 class="text-lg font-semibold text-gray-800 mt-6 mb-4">Información de la Ruta</h4>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-6 text-sm border-b pb-6">
                    <div><strong class="block text-gray-500">Origen:</strong><span>{{ $planning->origen ?? 'Sin dato' }}</span></div>
                    <div><strong class="block text-gray-500">Destino:</strong><span>{{ $planning->destino ?? 'Sin dato' }}</span></div>
                    <div><strong class="block text-gray-500">Fecha Entrega:</strong><span>{{ $planning->fecha_entrega?->format('d/m/Y') ?? 'N/A' }}</span></div>
                    <div class="col-span-full"><strong class="block text-gray-500">Dirección:</strong><span>{{ $planning->direccion ?? 'Sin dato' }}</span></div>
                    <div><strong class="block text-gray-500">Fecha Carga:</strong><span>{{ $planning->fecha_carga?->format('d/m/Y') ?? 'Sin dato' }}</span></div>
                    <div><strong class="block text-gray-500">Hora Carga:</strong><span>{{ $planning->hora_carga ?? 'Sin dato' }}</span></div>
                    <div><strong class="block text-gray-500">Hora Cita:</strong><span>{{ $planning->hora_cita ?? 'Sin dato' }}</span></div>
                    <div><strong class="block text-gray-500">Servicio:</strong><span class="font-bold text-blue-700">{{ $planning->servicio ?? 'Sin dato' }}</span></div>
                    <div><strong class="block text-gray-500">Tipo de Ruta:</strong><span class="font-bold text-blue-700">{{ $planning->tipo_ruta ?? 'Sin dato' }}</span></div>
                    <div><strong class="block text-gray-500">Región:</strong><span>{{ $planning->region ?? 'Sin dato' }}</span></div>
                    <div><strong class="block text-gray-500">Urgente:</strong><span class="{{ $planning->urgente === 'Si' ? 'text-red-600 font-bold' : '' }}">{{ $planning->urgente ?? 'No' }}</span></div>
                    <div><strong class="block text-gray-500">Maniobras:</strong><span>{{ $planning->maniobras ?? '0' }}</span></div>
                    <div class="col-span-full"><strong class="block text-gray-500">Observaciones:</strong><p class="mt-1 text-gray-900 bg-gray-50 p-2 rounded">{{ $planning->observaciones ?? 'Sin dato' }}</p></div>                
                </div>

                <h4 class="text-lg font-semibold text-gray-800 mt-6 mb-4">Información del Transporte</h4>
                 <div class="grid grid-cols-2 md:grid-cols-3 gap-6 text-sm border-b pb-6">
                    <div><strong class="block text-gray-500">Transporte:</strong><span>{{ $planning->transporte ?? 'Sin dato' }}</span></div>
                    <div><strong class="block text-gray-500">Operador:</strong><span>{{ $planning->operador ?? 'Sin dato' }}</span></div>
                    <div><strong class="block text-gray-500">Placas:</strong><span>{{ $planning->placas ?? 'Sin dato' }}</span></div>
                    <div><strong class="block text-gray-500">Teléfono:</strong><span>{{ $planning->telefono ?? 'Sin dato' }}</span></div>
                    <div><strong class="block text-gray-500">Capacidad:</strong><span>{{ $planning->capacidad ?? 'Sin dato' }}</span></div>
                    <div><strong class="block text-gray-500">Custodia:</strong><span>{{ $planning->custodia ?? 'Sin dato' }}</span></div>
                 </div>
                
                @if($planning->order && $planning->order->details->isNotEmpty())
                    <h4 class="text-lg font-semibold text-gray-800 mt-6 mb-4">Detalle de SKUs del Pedido</h4>
                    <div class="max-h-48 overflow-y-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-4 py-2 text-left">SKU</th>
                                    <th class="px-4 py-2 text-left">Descripción</th>
                                    <th class="px-4 py-2 text-left">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($planning->order->details as $detail)
                                    <tr>
                                        <td class="px-4 py-2">{{ $detail->sku }}</td>
                                        <td class="px-4 py-2">{{ $detail->product->description ?? 'N/A' }}</td>
                                        <td class="px-4 py-2">{{ $detail->quantity }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                 
                 <div class="mt-8 flex justify-end gap-4">
                    <a href="{{ route('customer-service.planning.edit', $planning) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-indigo-700">
                        <i class="fas fa-edit mr-2"></i>Editar Planificación
                    </a>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                @if($planning->order)
                    <h4 class="text-lg font-semibold text-gray-800 mb-6">Línea de Tiempo del Pedido</h4>
                    <div class="border-l-2 border-blue-500 pl-6 space-y-8 relative">
                        @forelse($planning->order->events as $event)
                            <div class="relative">
                                <div class="absolute -left-[33px] top-1 h-4 w-4 bg-blue-500 rounded-full border-4 border-white"></div>
                                <div class="ml-4">
                                    <p class="font-medium text-gray-800 text-sm">{{ $event->description }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $event->created_at->format('d/m/Y H:i A') }} por {{ $event->user->name ?? 'Sistema' }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No hay eventos registrados para este pedido.</p>
                        @endforelse
                    </div>
                @else
                    <h4 class="text-lg font-semibold text-gray-800 mb-6">Línea de Tiempo de Planificación</h4>
                    <div class="border-l-2 border-purple-500 pl-6 space-y-8 relative">
                        @forelse($planning->events as $event)
                            <div class="relative">
                                <div class="absolute -left-[33px] top-1 h-4 w-4 bg-purple-500 rounded-full border-4 border-white"></div>
                                <div class="ml-4">
                                    <p class="font-medium text-gray-800 text-sm">{{ $event->description }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $event->created_at->format('d/m/Y H:i A') }} por {{ $event->user->name ?? 'Sistema' }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No hay eventos registrados para esta planificación manual.</p>
                        @endforelse
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>