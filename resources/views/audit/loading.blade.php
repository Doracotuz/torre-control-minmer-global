@extends('layouts.audit-layout')

@section('content')
<div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8" 
     x-data="{
        incluyeTarimas: {{ old('incluye_tarimas', $audit->guia?->audit_carga_incluye_tarimas) ? 'true' : 'false' }},
        tipoTarima: '{{ old('tarimas_tipo', 'N/A') }}',
        incidenciasOpen: false,
        
        cajaVaciaPreview: null,
        marchamoPreview: null,
        photos: [], 
        cargaPreviews: [], 
        nextPhotoId: 1,

        init() {
            const initialCount = {{ old('fotos_carga') ? count(old('fotos_carga')) : 3 }};
            for (let i = 0; i < initialCount; i++) {
                this.addPhoto();
            }
        },

        addPhoto() {
            this.photos.push({ id: this.nextPhotoId++ });
        },

        removePhoto(index) {
            this.photos.splice(index, 1);
            this.cargaPreviews.splice(index, 1);
        },
        
        previewFile(event, target, index = null) {
            if (event.target.files.length > 0) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    if (index !== null) {
                        this.cargaPreviews[index] = e.target.result;
                    } else {
                        this[target] = e.target.result;
                    }
                };
                reader.readAsDataURL(event.target.files[0]);
            } else {
                if (index !== null) {
                    this.cargaPreviews[index] = null;
                } else {
                    this[target] = null;
                }
            }
        }
     }">

    <a href="{{ route('audit.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 mb-4 inline-block transition-colors">
        &larr; Volver al Dashboard de Auditoría
    </a>
    <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-xl">
        <div class="border-b pb-4 mb-6">
            <h1 class="text-3xl font-bold text-[#2c3856]">Auditoría de Carga de Unidad</h1>
            <p class="text-gray-600 mt-1">Guía: <span class="font-semibold text-gray-800">{{ $audit->guia->guia }}</span></p>
            <p class="text-gray-500 text-sm mt-1">Ubicación de Auditoría: <span class="font-medium">{{ $audit->location }}</span></p>
        </div>

        <form action="{{ route('audit.loading.store', $audit) }}" method="POST" enctype="multipart/form-data">
            @csrf

            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md" role="alert">
                    <p class="font-bold">Por favor, corrige los siguientes errores:</p>
                    <ul class="mt-2 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="space-y-8">
                
                <div>
                    <h3 class="font-bold text-lg text-gray-800 mb-2">Facturas en esta Carga</h3>
                    <div class="space-y-2">
                        @forelse($audit->guia->facturas as $factura)
                            <div class="border p-3 rounded-lg bg-gray-50"><p class="font-semibold text-gray-700">{{ $factura->numero_factura }}</p><p class="text-sm text-gray-500">Piezas: {{ $factura->botellas }}</p></div>
                        @empty
                            <div class="border p-3 rounded-lg bg-gray-50 text-center text-gray-500">No hay facturas asociadas a esta guía.</div>
                        @endforelse
                    </div>
                </div>

                @if(!empty($requirementsByCustomer))
                <div class="pt-6 border-t">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Especificaciones de Entrega por Cliente</h3>
                    @foreach($requirementsByCustomer as $customerName => $data)
                        <div class="mb-6 p-4 border rounded-lg bg-white shadow-sm">
                            <h4 class="font-semibold text-indigo-700">Checklist para Cliente: {{ $customerName }}</h4>
                            <p class="text-xs text-gray-500 mb-4">Aplica a Órdenes: {{ implode(', ', $data['orders']) }}</p>

                            @if(!empty($data['entrega']))
                                <div class="mt-4"><p class="font-medium text-gray-600">Requisitos de Entrega:</p><ul class="mt-2 space-y-2">@foreach($data['entrega'] as $spec)<li class="flex items-center"><input type="checkbox" name="validated_specs[{{ $customerName }}][{{ $spec }}]" class="rounded mr-3 text-indigo-600 shadow-sm"><label class="text-sm">{{ str_replace(' - Entrega', '', $spec) }}</label></li>@endforeach</ul></div>
                            @endif
                            @if(!empty($data['documentacion']))
                                <div class="mt-4"><p class="font-medium text-gray-600">Requisitos de Documentación:</p><ul class="mt-2 space-y-2">@foreach($data['documentacion'] as $spec)<li class="flex items-center"><input type="checkbox" name="validated_specs[{{ $customerName }}][{{ $spec }}]" class="rounded mr-3 text-indigo-600 shadow-sm"><label class="text-sm">{{ str_replace(' - Documentación', '', $spec) }}</label></li>@endforeach</ul></div>
                            @endif
                        </div>
                    @endforeach
                </div>
                @endif

                <div class="pt-6 border-t">
                    <h3 class="font-bold text-lg text-gray-800 mb-2">Información de Tarimas</h3>
                    <div class="mt-2 bg-gray-50 p-4 rounded-lg border">
                        <label class="flex items-center text-sm font-medium font-semibold"><input type="checkbox" name="incluye_tarimas" value="1" x-model="incluyeTarimas" class="rounded mr-2 text-indigo-600 shadow-sm">Incluye Tarimas</label>
                        <div x-show="incluyeTarimas" x-transition class="mt-4 space-y-4 pl-6 border-l-2 border-indigo-200">
                            <div>
                                <label for="tarimas_tipo" class="block text-sm font-medium text-gray-700">Tipo de Tarima</label>
                                <select name="tarimas_tipo" id="tarimas_tipo" x-model="tipoTarima" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="N/A" disabled>Selecciona...</option>
                                    <option value="Chep">Chep</option>
                                    <option value="Estándar">Estándar</option>
                                    <option value="Ambas">Ambas</option>
                                    <option value="Tarima Liverpool">Tarima Liverpool</option>
                                </select>
                            </div>
                            <div x-show="tipoTarima === 'Chep' || tipoTarima === 'Ambas'" x-transition>
                                <label for="tarimas_cantidad_chep" class="block text-sm font-medium text-gray-700">Cantidad Tarimas Chep</label>
                                <input type="number" name="tarimas_cantidad_chep" id="tarimas_cantidad_chep" value="{{ old('tarimas_cantidad_chep', 0) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" min="0">
                            </div>
                            <div x-show="tipoTarima === 'Estándar' || tipoTarima === 'Ambas'" x-transition>
                                <label for="tarimas_cantidad_estandar" class="block text-sm font-medium text-gray-700">Cantidad Tarimas Estándar</label>
                                <input type="number" name="tarimas_cantidad_estandar" id="tarimas_cantidad_estandar" value="{{ old('tarimas_cantidad_estandar', 0) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" min="0">
                            </div>
                            <div x-show="tipoTarima === 'Tarima Liverpool'" x-transition>
                                <label for="tarimas_cantidad_liverpool" class="block text-sm font-medium text-gray-700">Cantidad Tarimas Liverpool</label>
                                <input type="number" name="tarimas_cantidad_liverpool" id="tarimas_cantidad_liverpool" value="{{ old('tarimas_cantidad_liverpool', 0) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" min="0">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t">
                    <button type="button" @click="incidenciasOpen = !incidenciasOpen" class="w-full flex justify-between items-center text-left">
                        <h3 class="font-bold text-lg text-gray-800">Incidencias (opcional)</h3>
                        <i class="fas" :class="incidenciasOpen ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>
                    <div x-show="incidenciasOpen" x-transition class="mt-4 space-y-2 p-4 bg-gray-50 rounded-lg border grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-4 gap-y-2">
                        @php $incidencias = ['Producto cambiado','Producto no etiquetado','Producto sobrante','Distribución incorrecta','Producto dañado','Producto faltante','Retraso en almacén','Producto sin maquila VA','Administración Planus','Unidad no adecuada','Unidad sin maniobra','Falta de herramientas en transporte','Gestión de operador','Modificación de embarque']; @endphp
                        @foreach($incidencias as $incidencia)
                            <label class="flex items-center text-sm font-medium"><input type="checkbox" name="incidencias[]" value="{{ $incidencia }}" class="rounded mr-2 text-indigo-600 shadow-sm">{{ $incidencia }}</label>
                        @endforeach
                    </div>
                </div>

                <div class="pt-6 border-t">
                    <h3 class="font-bold text-lg text-gray-800 mb-4">Evidencias Fotográficas</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Foto de Caja Vacía <span class="text-red-500">*</span></label>
                            <input type="file" name="foto_caja_vacia" x-ref="fotoCajaVaciaInput" @change="previewFile($event, 'cajaVaciaPreview')" class="hidden" accept="image/*" capture="environment" required>
                            <div @click="$refs.fotoCajaVaciaInput.click()" class="cursor-pointer border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-indigo-500 transition-colors h-48 flex items-center justify-center">
                                <template x-if="!cajaVaciaPreview">
                                    <div><i class="fas fa-box-open text-4xl text-gray-400"></i><p class="mt-2 text-sm text-gray-600">Clic para seleccionar</p></div>
                                </template>
                                <template x-if="cajaVaciaPreview">
                                    <img :src="cajaVaciaPreview" class="max-h-full max-w-full mx-auto rounded-md object-contain">
                                </template>
                            </div>
                        </div>
                    </div>

                    <h4 class="font-semibold text-gray-700 mt-8 mb-2">Fotos del Proceso de Carga <span class="text-red-500">*</span></h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4">
                        <template x-for="(photo, index) in photos" :key="photo.id">
                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700 mb-2" x-text="'Foto de Carga ' + (index + 1)"></label>
                                <input type="file" name="fotos_carga[]" :id="'foto_carga_' + photo.id" @change="previewFile($event, 'cargaPreviews', index)" class="hidden" accept="image/*" capture="environment" required>
                                
                                <div @click="document.getElementById('foto_carga_' + photo.id).click()" class="cursor-pointer border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-indigo-500 transition-colors h-48 flex items-center justify-center">
                                    <template x-if="!cargaPreviews[index]">
                                        <div><i class="fas fa-camera text-4xl text-gray-400"></i><p class="mt-2 text-sm text-gray-600">Clic para seleccionar</p></div>
                                    </template>
                                    <template x-if="cargaPreviews[index]">
                                        <img :src="cargaPreviews[index]" class="max-h-full max-w-full mx-auto rounded-md object-contain">
                                    </template>
                                </div>
                                
                                <button type="button" @click.prevent="removePhoto(index)" x-show="photos.length > 3"
                                        class="absolute top-0 right-0 -mt-2 -mr-2 bg-red-500 text-white rounded-full h-6 w-6 flex items-center justify-center shadow-md hover:bg-red-700 transition-colors">
                                    &times;
                                </button>
                            </div>
                        </template>
                    </div>

                    <div class="mt-6">
                        <button type="button" @click="addPhoto" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-plus mr-2"></i>Agregar Foto
                        </button>
                    </div>
                </div>

                <div class="pt-6 border-t">
                     <h3 class="font-bold text-lg text-gray-800 mb-2">Seguridad</h3>
                     <div class="space-y-4 bg-gray-50 p-4 rounded-lg border">
                        <div><label class="block text-sm font-medium text-gray-700">Número de Marchamo (si aplica)</label><input type="text" name="marchamo_numero" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">¿Lleva Custodia?</label>
                            <select name="lleva_custodia" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required><option value="1">Sí</option><option value="0" selected>No</option></select>
                        </div>
                     </div>
                </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Foto de Marchamo</label>
                            <input type="file" name="foto_marchamo" x-ref="fotoMarchamoInput" @change="previewFile($event, 'marchamoPreview')" class="hidden" accept="image/*" capture="environment">
                            <div @click="$refs.fotoMarchamoInput.click()" class="cursor-pointer border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-indigo-500 transition-colors h-48 flex items-center justify-center">
                                <template x-if="!marchamoPreview">
                                    <div><i class="fas fa-lock text-4xl text-gray-400"></i><p class="mt-2 text-sm text-gray-600">Clic para seleccionar</p></div>
                                </template>
                                <template x-if="marchamoPreview">
                                    <img :src="marchamoPreview" class="max-h-full max-w-full mx-auto rounded-md object-contain">
                                </template>
                            </div>
                        </div>
                    </div>                

                <div class="pt-8">
                    <button type="submit" class="w-full px-6 py-3 bg-teal-600 text-white rounded-lg font-bold shadow-lg hover:bg-teal-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                        Finalizar Auditoría de Carga
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection