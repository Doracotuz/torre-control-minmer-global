<h3 class="text-xl font-bold text-gray-800 border-b pb-2 mb-4">Fase 3: Auditoría de Carga</h3>

@php
    // Decodificamos el JSON de las fotos de carga una sola vez para acceder a sus datos
    $cargaData = json_decode($guia->audit_carga_fotos, true) ?? [];
    $validatedSpecs = $cargaData['validated_specifications'] ?? [];
    $loadingEvent = $guia->plannings->pluck('order')->flatten()->pluck('events')->flatten()->first(fn($e) => str_contains($e->description, 'Auditoría de carga finalizada'));
@endphp
<p class="text-xs text-gray-500">Auditado por: <strong>{{ $loadingEvent->user->name ?? 'N/A' }}</strong></p>
<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
        <div><strong>Marchamo:</strong><span class="ml-2 text-gray-700">{{ $guia->marchamo_numero ?: 'N/A' }}</span></div>
        <div><strong>Custodia:</strong><span class="ml-2 text-gray-700">{{ $guia->lleva_custodia ? 'Sí' : 'No' }}</span></div>
    </div>

    <div class="bg-gray-50 p-4 rounded-lg border">
        <h4 class="font-semibold mb-2 text-gray-700">Información de Tarimas</h4>
        @if($guia->audit_carga_incluye_tarimas)
            <div class="grid grid-cols-2 gap-4 text-sm">
                <p><strong>Tarimas Chep:</strong> {{ $guia->audit_carga_tarimas_chep ?? 0 }}</p>
                <p><strong>Tarimas Estándar:</strong> {{ $guia->audit_carga_tarimas_estandar ?? 0 }}</p>
            </div>
        @else
            <p class="text-sm text-gray-600">No se incluyeron tarimas en esta carga.</p>
        @endif
    </div>

    @if(!empty($validatedSpecs))
        <div>
            <h4 class="font-semibold text-gray-700">Checklist de Especificaciones de Entrega</h4>
            @foreach($validatedSpecs as $customerName => $specs)
                <div class="border rounded-md p-4 mt-2">
                    <p class="font-semibold text-indigo-700 text-sm">Cliente: {{ $customerName }}</p>
                    @php
                        $entregaSpecs = array_filter($specs, fn($key) => str_contains($key, 'Entrega'), ARRAY_FILTER_USE_KEY);
                        $docSpecs = array_filter($specs, fn($key) => str_contains($key, 'Documentación'), ARRAY_FILTER_USE_KEY);
                    @endphp

                    @if(!empty($entregaSpecs))
                        <p class="text-xs font-bold mt-3 text-gray-600">REQUISITOS DE ENTREGA</p>
                        <ul class="mt-1 space-y-1 text-sm">
                        @foreach($entregaSpecs as $spec => $was_checked)
                            <li class="flex items-center {{ $was_checked ? 'text-green-700' : 'text-red-700' }}">
                                <i class="fas {{ $was_checked ? 'fa-check-square' : 'fa-times-circle' }} w-5 text-center mr-2"></i>
                                <span>{{ str_replace(' - Entrega', '', $spec) }}</span>
                            </li>
                        @endforeach
                        </ul>
                    @endif
                    @if(!empty($docSpecs))
                        <p class="text-xs font-bold mt-3 text-gray-600">REQUISITOS DE DOCUMENTACIÓN</p>
                        <ul class="mt-1 space-y-1 text-sm">
                        @foreach($docSpecs as $spec => $was_checked)
                             <li class="flex items-center {{ $was_checked ? 'text-green-700' : 'text-red-700' }}">
                                <i class="fas {{ $was_checked ? 'fa-check-square' : 'fa-times-circle' }} w-5 text-center mr-2"></i>
                                <span>{{ str_replace(' - Documentación', '', $spec) }}</span>
                            </li>
                        @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <div>
        <h4 class="font-semibold text-gray-700">Incidencias Registradas</h4>
        @if($guia->incidencias->isNotEmpty())
            <ul class="list-disc list-inside text-sm text-gray-700 bg-yellow-50 p-3 rounded-md border border-yellow-200">
                @foreach($guia->incidencias as $incidencia)
                    <li>{{ $incidencia->tipo_incidencia }}</li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-600">No se registraron incidencias.</p>
        @endif
    </div>

    <div>
        <h4 class="font-semibold text-gray-700">Fotos de Carga:</h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mt-2">
            @if(!empty($cargaData['caja_vacia']))
                <div><p class="text-sm font-medium mb-1">Caja Vacía</p><a href="{{ Storage::disk('s3')->url($cargaData['caja_vacia']) }}" target="_blank"><img src="{{ Storage::disk('s3')->url($cargaData['caja_vacia']) }}" class="rounded-md w-full object-cover border"></a></div>
            @endif
            @if(!empty($cargaData['marchamo']))
                 <div><p class="text-sm font-medium mb-1">Marchamo</p><a href="{{ Storage::disk('s3')->url($cargaData['marchamo']) }}" target="_blank"><img src="{{ Storage::disk('s3')->url($cargaData['marchamo']) }}" class="rounded-md w-full object-cover border"></a></div>
            @endif
            @if(!empty($cargaData['proceso_carga']))
                @foreach($cargaData['proceso_carga'] as $foto_proceso)
                    <div><p class="text-sm font-medium mb-1">Proceso de Carga</p><a href="{{ Storage::disk('s3')->url($foto_proceso) }}" target="_blank"><img src="{{ Storage::disk('s3')->url($foto_proceso) }}" class="rounded-md w-full object-cover border"></a></div>
                @endforeach
            @endif
        </div>
    </div>
</div>