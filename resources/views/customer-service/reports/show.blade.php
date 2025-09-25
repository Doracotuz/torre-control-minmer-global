<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <h2 class="font-bold text-2xl text-[#2c3856] leading-tight">
                Detalle de Auditoría: Guía {{ $guia->guia }}
            </h2>
            <a href="{{ route('customer-service.audit-reports.pdf', $guia) }}" target="_blank" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-semibold shadow hover:bg-red-700 transition-colors">
                <i class="fas fa-file-pdf mr-2"></i>Exportar a PDF
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @php
                $firstPlanning = $guia->plannings->first();
                $firstOrder = $firstPlanning?->order;
                $mainAudit = $firstOrder?->audits->where('location', $firstPlanning?->origen)->first();
                $patioData = $mainAudit?->patio_audit_data ?? [];
                $loadingData = $mainAudit?->loading_audit_data ?? [];
            @endphp

            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="text-xl font-bold text-gray-800 border-b pb-2 mb-4">Resumen General del Envío</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div><strong>Guía:</strong><span class="ml-2 text-gray-700">{{ $guia->guia }}</span></div>
                    <div><strong>Estatus Final:</strong><span class="ml-2 text-gray-700">{{ $guia->estatus }}</span></div>
                    <div><strong>Operador:</strong><span class="ml-2 text-gray-700">{{ $guia->operador }}</span></div>
                    <div><strong>Placas:</strong><span class="ml-2 text-gray-700">{{ $guia->placas }}</span></div>
                    <div class="sm:col-span-2"><strong>Órdenes (SOs):</strong><span class="ml-2 text-gray-700">{{ $guia->plannings->pluck('order.so_number')->filter()->unique()->join(', ') }}</span></div>
                    <div class="sm:col-span-2"><strong>Clientes:</strong><span class="ml-2 text-gray-700">{{ $guia->plannings->pluck('order.customer_name')->filter()->unique()->join(', ') }}</span></div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="text-xl font-bold text-gray-800 border-b pb-2 mb-4">Fase 1: Auditoría de Almacén</h3>
                <div class="space-y-6">
                    @forelse($guia->plannings as $planning)
                        @if($order = $planning->order)
                            @php
                                $audit = $order->audits->where('location', $planning->origen)->first();
                                $warehouseData = $audit?->warehouse_audit_data ?? [];
                                $warehouseEvent = $order->events->first(fn($e) => str_contains($e->description, 'Auditoría de almacén completada'));
                            @endphp
                            <div class="border p-4 rounded-lg bg-gray-50">
                                <div class="flex justify-between items-center mb-3">
                                    <div>
                                        <p class="font-semibold text-indigo-700">Resultados para SO: {{ $order->so_number }}</p>
                                        <p class="text-xs text-gray-500">Ubicación: <strong>{{ $audit->location ?? 'N/A' }}</strong></p>
                                    </div>
                                    <p class="text-xs text-gray-500">Auditado por: <strong>{{ $warehouseEvent->user->name ?? 'N/A' }}</strong></p>
                                </div>
                                <div class="mb-4 text-sm">
                                    <strong class="text-gray-600">Observaciones:</strong>
                                    <p class="p-2 bg-white rounded-md border mt-1">{{ $warehouseData['observaciones'] ?? 'Ninguna.' }}</p>
                                </div>
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">Checklist de Validación por SKU</h4>
                                <div class="overflow-x-auto border rounded-md">
                                    <table class="min-w-full text-sm">
                                        <thead class="bg-gray-200">
                                            <tr>
                                                <th class="px-3 py-2 text-left font-medium text-gray-600">SKU</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-600">Calidad</th>
                                                <th class="px-3 py-2 text-center font-medium text-gray-600">SKU OK?</th>
                                                <th class="px-3 py-2 text-center font-medium text-gray-600">Piezas OK?</th>
                                                <th class="px-3 py-2 text-center font-medium text-gray-600">UPC OK?</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-100">
                                            @forelse($warehouseData['items'] ?? [] as $detailId => $itemData)
                                                @php $detail = \App\Models\CsOrderDetail::find($detailId); @endphp
                                                @if($detail)
                                                <tr>
                                                    <td class="px-3 py-2 font-mono">{{ $detail->sku }}</td>
                                                    <td class="px-3 py-2">{{ $itemData['calidad'] ?? 'N/A' }}</td>
                                                    <td class="px-3 py-2 text-center text-lg {{ isset($itemData['sku_validado']) && $itemData['sku_validado'] ? 'text-green-500' : 'text-red-500' }}">
                                                        <span>{{ isset($itemData['sku_validado']) && $itemData['sku_validado'] ? '✔' : '✘' }}</span>
                                                    </td>
                                                    <td class="px-3 py-2 text-center text-lg {{ isset($itemData['piezas_validadas']) && $itemData['piezas_validadas'] ? 'text-green-500' : 'text-red-500' }}">
                                                        <span>{{ isset($itemData['piezas_validadas']) && $itemData['piezas_validadas'] ? '✔' : '✘' }}</span>
                                                    </td>
                                                    <td class="px-3 py-2 text-center text-lg {{ isset($itemData['upc_validado']) && $itemData['upc_validado'] ? 'text-green-500' : 'text-red-500' }}">
                                                        <span>{{ isset($itemData['upc_validado']) && $itemData['upc_validado'] ? '✔' : '✘' }}</span>
                                                    </td>
                                                </tr>
                                                @endif
                                            @empty
                                                <tr><td colspan="5" class="px-3 py-2 text-center text-gray-500">No hay detalles de validación para esta orden.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    @empty
                        <p class="text-center text-gray-500 py-4">No hay órdenes en esta guía para mostrar.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg">
                <div class="flex justify-between items-center border-b pb-2 mb-4">
                    <h3 class="text-xl font-bold text-gray-800">Fase 2: Auditoría de Patio</h3>
                    @php
                        $patioEvent = $guia->plannings->pluck('order')->flatten()->pluck('events')->flatten()->first(fn($e) => str_contains($e->description, 'Auditoría de patio completada'));
                    @endphp
                    <p class="text-xs text-gray-500">Auditado por: <strong>{{ $patioEvent->user->name ?? 'N/A' }}</strong></p>
                </div>
                <div class="overflow-x-auto border rounded-md">
                    <table class="min-w-full text-sm">
                        <tbody class="divide-y divide-gray-200">
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 font-medium text-gray-500 w-1/3">Fecha y Hora de Arribo</td>
                                <td class="px-3 py-2 text-gray-800">{{ $patioData['arribo_completo'] ?? ($patioData['arribo_fecha'] ?? 'N/A') }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 font-medium text-gray-500">Estado de la Caja</td>
                                <td class="px-3 py-2 text-gray-800">{{ $patioData['caja_estado'] ?? 'N/A' }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 font-medium text-gray-500">Estado de Llantas</td>
                                <td class="px-3 py-2 text-gray-800">{{ $patioData['llantas_estado'] ?? 'N/A' }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 font-medium text-gray-500">Nivel de Combustible</td>
                                <td class="px-3 py-2 text-gray-800">{{ $patioData['combustible_nivel'] ?? 'N/A' }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 font-medium text-gray-500">Equipo de Sujeción</td>
                                <td class="px-3 py-2 text-gray-800">{{ $patioData['equipo_sujecion'] ?? 'N/A' }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 font-medium text-gray-500">Presenta Maniobra</td>
                                <td class="px-3 py-2 text-gray-800">{{ isset($patioData['presenta_maniobra']) && $patioData['presenta_maniobra'] ? 'Sí (' . ($patioData['maniobra_personas'] ?? 'N/A') . ' personas)' : 'No' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <h4 class="font-semibold mt-6 mb-2 text-gray-700">Fotos de Patio:</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @php
                        $fotoUnidadUrl = $patioData['foto_unidad_path'] ?? null;
                        $fotoLlantasUrl = $patioData['foto_llantas_path'] ?? null;
                        if (!$fotoUnidadUrl && isset($patioData['audit_patio_fotos'])) {
                            $oldFotos = is_string($patioData['audit_patio_fotos']) ? json_decode($patioData['audit_patio_fotos'], true) : $patioData['audit_patio_fotos'];
                            $fotoUnidadUrl = $oldFotos['unidad'] ?? null;
                            $fotoLlantasUrl = $oldFotos['llantas'] ?? null;
                        }
                    @endphp

                    @if($fotoUnidadUrl)
                        <div class="border rounded-lg p-2 bg-gray-50">
                            <p class="text-sm font-medium text-center mb-2 text-gray-600">Unidad</p>
                            <a href="{{ Storage::disk('s3')->url($fotoUnidadUrl) }}" target="_blank">
                                <img src="{{ Storage::disk('s3')->url($fotoUnidadUrl) }}" class="rounded-md w-full object-cover">
                            </a>
                        </div>
                    @else
                        <div class="border rounded-lg p-2 bg-gray-50 text-center text-gray-400">
                            <p class="text-sm font-medium">Unidad</p>
                            <i class="fas fa-image fa-3x mt-4"></i>
                            <p class="mt-2 text-xs">Sin foto</p>
                        </div>
                    @endif
                    @if($fotoLlantasUrl)
                        <div class="border rounded-lg p-2 bg-gray-50">
                            <p class="text-sm font-medium text-center mb-2 text-gray-600">Llantas</p>
                            <a href="{{ Storage::disk('s3')->url($fotoLlantasUrl) }}" target="_blank">
                                <img src="{{ Storage::disk('s3')->url($fotoLlantasUrl) }}" class="rounded-md w-full object-cover">
                            </a>
                        </div>
                    @else
                        <div class="border rounded-lg p-2 bg-gray-50 text-center text-gray-400">
                            <p class="text-sm font-medium">Llantas</p>
                            <i class="fas fa-image fa-3x mt-4"></i>
                            <p class="mt-2 text-xs">Sin foto</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg">
                @include('customer-service.reports.partials.fase-3-carga', ['guia' => $guia, 'loadingData' => $loadingData])
            </div>
            
            <div class="text-center mt-8">
                <a href="{{ route('customer-service.audit-reports.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors">
                    &larr; Volver al Historial de Auditorías
                </a>
            </div>
        </div>
    </div>
</x-app-layout>