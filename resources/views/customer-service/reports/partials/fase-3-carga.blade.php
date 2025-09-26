<div class="flex justify-between items-center border-b pb-2 mb-4">
    <h3 class="text-xl font-bold text-gray-800">Fase 3: Auditoría de Carga</h3>
    @php
        $loadingEvent = $guia->plannings->pluck('order')->flatten()->pluck('events')->flatten()->first(fn($e) => str_contains($e->description, 'Auditoría de carga finalizada en ') || str_contains($e->description, 'Proceso de auditoría de carga finalizado'));
    @endphp
    <p class="text-xs text-gray-500">Auditado por: <strong>{{ $loadingEvent->user->name ?? 'N/A' }}</strong></p>
</div>

<div class="overflow-x-auto border rounded-md mb-6">
    <table class="min-w-full text-sm">
        <tbody class="divide-y divide-gray-200">
            <tr class="hover:bg-gray-50">
                <td class="px-3 py-2 font-medium text-gray-500 w-1/3">Marchamo</td>
                <td class="px-3 py-2 text-gray-800">{{ $loadingData['marchamo_numero'] ?? 'N/A' }}</td>
            </tr>
            <tr class="hover:bg-gray-50">
                <td class="px-3 py-2 font-medium text-gray-500">Custodia</td>
                <td class="px-3 py-2 text-gray-800">{{ isset($loadingData['lleva_custodia']) && $loadingData['lleva_custodia'] ? 'Sí' : 'No' }}</td>
            </tr>
            <tr class="hover:bg-gray-50">
                <td class="px-3 py-2 font-medium text-gray-500">Incluye Tarimas</td>
                <td class="px-3 py-2 text-gray-800">{{ isset($loadingData['incluye_tarimas']) && $loadingData['incluye_tarimas'] ? 'Sí' : 'No' }}</td>
            </tr>
            @if(isset($loadingData['incluye_tarimas']) && $loadingData['incluye_tarimas'])
            <tr class="hover:bg-gray-50">
                <td class="px-3 py-2 font-medium text-gray-500 pl-8">↳ Cantidad Chep</td>
                <td class="px-3 py-2 text-gray-800">{{ $loadingData['tarimas_cantidad_chep'] ?? 0 }}</td>
            </tr>
            <tr class="hover:bg-gray-50">
                <td class="px-3 py-2 font-medium text-gray-500 pl-8">↳ Cantidad Estándar</td>
                <td class="px-3 py-2 text-gray-800">{{ $loadingData['tarimas_cantidad_estandar'] ?? 0 }}</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>

@php $validatedSpecs = $loadingData['validated_specs'] ?? []; @endphp
@if(!empty($validatedSpecs))
    <div class="mb-6">
        <h4 class="font-semibold text-gray-700 mb-2">Checklist de Especificaciones de Entrega</h4>
        @foreach($validatedSpecs as $customerName => $specs)
            <div class="border rounded-md p-4 mt-2 bg-gray-50">
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

<div class="mb-6">
    <h4 class="font-semibold text-gray-700 mb-2">Incidencias Registradas</h4>
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
    <h4 class="font-semibold text-gray-700 mb-2">Fotos de Carga:</h4>
    @php $cargaFotos = $loadingData['audit_carga_fotos'] ?? []; @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        @if(!empty($cargaFotos['caja_vacia']))
            <div class="border rounded-lg p-2 bg-gray-50"><p class="text-sm font-medium text-center mb-2 text-gray-600">Caja Vacía</p><a href="{{ Storage::disk('s3')->url($cargaFotos['caja_vacia']) }}" target="_blank"><img src="{{ Storage::disk('s3')->url($cargaFotos['caja_vacia']) }}" class="rounded-md w-full object-cover"></a></div>
        @endif
        @if(!empty($cargaFotos['marchamo']))
             <div class="border rounded-lg p-2 bg-gray-50"><p class="text-sm font-medium text-center mb-2 text-gray-600">Marchamo</p><a href="{{ Storage::disk('s3')->url($cargaFotos['marchamo']) }}" target="_blank"><img src="{{ Storage::disk('s3')->url($cargaFotos['marchamo']) }}" class="rounded-md w-full object-cover"></a></div>
        @endif
        @if(!empty($cargaFotos['proceso_carga']))
            @foreach($cargaFotos['proceso_carga'] as $key => $foto_proceso)
                <div class="border rounded-lg p-2 bg-gray-50"><p class="text-sm font-medium text-center mb-2 text-gray-600">Proceso de Carga {{ $key + 1 }}</p><a href="{{ Storage::disk('s3')->url($foto_proceso) }}" target="_blank"><img src="{{ Storage::disk('s3')->url($foto_proceso) }}" class="rounded-md w-full object-cover"></a></div>
            @endforeach
        @endif
    </div>
</div>