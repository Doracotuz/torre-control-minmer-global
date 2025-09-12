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
                // Obtenemos la primera auditoría de la guía para acceder a los datos de Patio y Carga,
                // ya que estos son compartidos a nivel de guía para una ubicación específica.
                $firstPlanning = $guia->plannings->first();
                $firstOrder = $firstPlanning?->order;
                $mainAudit = $firstOrder?->audits->where('location', $firstPlanning?->origen)->first();
                
                // Extraemos los datos de los campos JSON, con un array vacío como respaldo.
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
                <div class="space-y-4">
                    @foreach($guia->plannings as $planning)
                        @if($order = $planning->order)
                            @php
                                $audit = $order->audits->where('location', $planning->origen)->first();
                                $warehouseData = $audit?->warehouse_audit_data ?? [];
                                $warehouseEvent = $order->events->first(fn($e) => str_contains($e->description, 'Auditoría de almacén completada'));
                            @endphp
                            <div class="border p-4 rounded-md bg-gray-50">
                                <p class="font-semibold text-indigo-700">Resultados para SO: {{ $order->so_number }} (Ubicación: {{ $audit->location ?? 'N/A' }})</p>
                                <p class="text-xs text-gray-500">Auditado por: <strong>{{ $warehouseEvent->user->name ?? 'N/A' }}</strong></p>
                                <p class="mt-2 text-sm"><strong>Observaciones:</strong> {{ $warehouseData['observaciones'] ?? 'Ninguna' }}</p>
                                <div class="mt-3 text-xs space-y-1">
                                    @foreach($warehouseData['items'] ?? [] as $detailId => $itemData)
                                        @php $detail = \App\Models\CsOrderDetail::find($detailId); @endphp
                                        @if($detail)
                                        <p><strong>SKU {{ $detail->sku }}:</strong> Calidad {{ $itemData['calidad'] ?? 'N/A' }} / SKU {{ isset($itemData['sku_validado']) ? '✔' : '✘' }} / Piezas {{ isset($itemData['piezas_validadas']) ? '✔' : '✘' }} / UPC {{ isset($itemData['upc_validado']) ? '✔' : '✘' }}</p>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="text-xl font-bold text-gray-800 border-b pb-2 mb-4">Fase 2: Auditoría de Patio</h3>
                @php
                    $patioEvent = $guia->plannings->pluck('order')->flatten()->pluck('events')->flatten()->first(fn($e) => str_contains($e->description, 'Auditoría de patio completada'));
                @endphp
                <p class="text-xs text-gray-500">Auditado por: <strong>{{ $patioEvent->user->name ?? 'N/A' }}</strong></p>               
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div><strong>Fecha y Hora de Arribo:</strong><span class="ml-2 text-gray-700">{{ $patioData['arribo_completo'] ?? ($patioData['arribo_fecha'] ?? 'N/A') }}</span></div>
                    <div><strong>Estado de la Caja:</strong><span class="ml-2 text-gray-700">{{ $patioData['caja_estado'] ?? 'N/A' }}</span></div>
                    <div><strong>Estado de Llantas:</strong><span class="ml-2 text-gray-700">{{ $patioData['llantas_estado'] ?? 'N/A' }}</span></div>
                    <div><strong>Nivel de Combustible:</strong><span class="ml-2 text-gray-700">{{ $patioData['combustible_nivel'] ?? 'N/A' }}</span></div>
                    <div><strong>Equipo de Sujeción:</strong><span class="ml-2 text-gray-700">{{ $patioData['equipo_sujecion'] ?? 'N/A' }}</span></div>
                    <div><strong>Presenta Maniobra:</strong><span class="ml-2 text-gray-700">{{ isset($patioData['presenta_maniobra']) && $patioData['presenta_maniobra'] ? 'Sí (' . ($patioData['maniobra_personas'] ?? 'N/A') . ' personas)' : 'No' }}</span></div>
                </div>
                
                <h4 class="font-semibold mt-6 mb-2">Fotos de Patio:</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @php
                        // Lógica de retrocompatibilidad para encontrar la ruta de la imagen
                        $fotoUnidadUrl = $patioData['foto_unidad_path'] ?? null;
                        $fotoLlantasUrl = $patioData['foto_llantas_path'] ?? null;

                        // Si no se encuentra con la nueva clave, intentamos con la antigua estructura
                        if (!$fotoUnidadUrl && isset($patioData['audit_patio_fotos'])) {
                            $oldFotos = json_decode($patioData['audit_patio_fotos'], true);
                            $fotoUnidadUrl = $oldFotos['unidad'] ?? null;
                            $fotoLlantasUrl = $oldFotos['llantas'] ?? null;
                        }
                    @endphp

                    @if($fotoUnidadUrl)
                        <div>
                            <p class="text-sm font-medium mb-1">Unidad</p>
                            <a href="{{ Storage::disk('s3')->url($fotoUnidadUrl) }}" target="_blank"><img src="{{ Storage::disk('s3')->url($fotoUnidadUrl) }}" class="rounded-md w-full object-cover border"></a>
                        </div>
                    @endif
                    @if($fotoLlantasUrl)
                        <div>
                            <p class="text-sm font-medium mb-1">Llantas</p>
                            <a href="{{ Storage::disk('s3')->url($fotoLlantasUrl) }}" target="_blank"><img src="{{ Storage::disk('s3')->url($fotoLlantasUrl) }}" class="rounded-md w-full object-cover border"></a>
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