@extends('layouts.audit-layout')
@section('content')
    <div class="max-w-4xl mx-auto">
        <a href="{{ route('audit.index') }}" class="text-sm font-semibold text-gray-600 mb-4 inline-block">&larr; Volver al Dashboard</a>
        <h1 class="text-2xl font-bold text-[#2c3856]">Auditoría de Carga de Unidad</h1>
        <p class="text-gray-600 mb-6">Guía: {{ $guia->guia }}</p>

        <form action="{{ route('audit.loading.store', $guia->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="bg-white p-4 rounded-lg shadow-md space-y-6">
                <!-- Confirmación de Facturas -->
                <div>
                    <h3 class="font-bold text-lg mb-2">Facturas en esta Carga</h3>
                    @foreach($guia->facturas as $factura)
                        <div class="border p-3 rounded-md mb-2 bg-gray-50">
                            <p class="font-semibold">{{ $factura->numero_factura }}</p>
                            <p class="text-sm text-gray-500">Botellas: {{ $factura->botellas }}</p>
                        </div>
                    @endforeach
                </div>

                <!-- Evidencias Fotográficas -->
                <div>
                    <h3 class="font-bold text-lg mb-2">Evidencias Fotográficas</h3>
                    <div class="space-y-4">
                        <div><label class="block text-sm font-medium">Foto de Caja Vacía <span class="text-red-500">*</span></label><input type="file" name="foto_caja_vacia" class="mt-1 block w-full" required></div>
                        <div><label class="block text-sm font-medium">Fotos de Carga (mínimo 3) <span class="text-red-500">*</span></label><input type="file" name="fotos_carga[]" class="mt-1 block w-full" multiple required></div>
                    </div>
                </div>

                <!-- Marchamo y Custodia -->
                <div>
                     <h3 class="font-bold text-lg mb-2">Seguridad</h3>
                     <div class="space-y-4">
                        <div><label class="block text-sm font-medium">Número de Marchamo (si aplica)</label><input type="text" name="marchamo_numero" class="mt-1 block w-full rounded-md border-gray-300"></div>
                        <div><label class="block text-sm font-medium">Foto de Marchamo</label><input type="file" name="foto_marchamo" class="mt-1 block w-full"></div>
                        <div>
                            <label class="block text-sm font-medium">¿Lleva Custodia?</label>
                            <select name="lleva_custodia" class="mt-1 block w-full rounded-md border-gray-300" required>
                                <option value="1">Sí</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                     </div>
                </div>

                @if(!empty($requirementsByOrder))
                <div class="mt-8 bg-gray-50 p-6 rounded-lg shadow">
                    <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Requisitos de Cliente a Validar</h3>

                    @foreach($requirementsByOrder as $identifier => $categories)
                        <div class="mb-6 p-4 border rounded-md bg-white">
                            <h4 class="font-semibold text-indigo-700">Orden/Factura: {{ $identifier }}</h4>
                            
                            @if(!empty($categories['entrega']))
                                <div class="mt-4">
                                    <p class="font-medium text-gray-600">Requisitos de Entrega:</p>
                                    <ul class="list-disc list-inside mt-2 space-y-2">
                                        @foreach($categories['entrega'] as $spec)
                                            <li class="flex items-center">
                                                <input type="checkbox" name="validated_specs[{{ $identifier }}][{{ $spec }}]" class="rounded mr-3">
                                                <label>{{ str_replace(' - Entrega', '', $spec) }}</label>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if(!empty($categories['documentacion']))
                                <div class="mt-4">
                                    <p class="font-medium text-gray-600">Requisitos de Documentación:</p>
                                    <ul class="list-disc list-inside mt-2 space-y-2">
                                        @foreach($categories['documentacion'] as $spec)
                                            <li class="flex items-center">
                                                <input type="checkbox" name="validated_specs[{{ $identifier }}][{{ $spec }}]" class="rounded mr-3">
                                                <label>{{ str_replace(' - Documentación', '', $spec) }}</label>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                @endif                

                <!-- Incidencias -->
                <div>
                    <h3 class="font-bold text-lg mb-2">Incidencias (opcional)</h3>
                    <div class="space-y-2">
                        @php
                            $incidencias = ['Producto cambiado','Producto no etiquetado','Producto sobrante','Distribución incorrecta','Producto dañado','Producto faltante','Retraso en almacén','Producto sin maquila VA','Administración Planus','Unidad no adecuada','Unidad sin maniobra','Falta de herramientas en transporte','Gestión de operador','Modificación de embarque'];
                        @endphp
                        @foreach($incidencias as $incidencia)
                            <label class="flex items-center"><input type="checkbox" name="incidencias[]" value="{{ $incidencia }}" class="rounded mr-2">{{ $incidencia }}</label>
                        @endforeach
                    </div>
                </div>
                
                <div class="pt-4">
                    <button type="submit" class="w-full px-6 py-3 bg-teal-600 text-white rounded-lg font-bold shadow-lg hover:bg-teal-700 transition-colors">Finalizar Carga y Poner en Tránsito</button>
                </div>
            </div>
        </form>
    </div>
@endsection
