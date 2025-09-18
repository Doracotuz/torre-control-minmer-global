@extends('layouts.audit-layout')

@section('content')
<div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8" 
     x-data="{
        incluyeTarimas: {{ old('incluye_tarimas', $audit->guia?->audit_carga_incluye_tarimas) ? 'true' : 'false' }},
        tipoTarima: '{{ old('tarimas_tipo', 'N/A') }}',
        incidenciasOpen: false,
        
        cargaPhotos: [ { id: 1 }, { id: 2 }, { id: 3 } ],
        
        previews: { cajaVacia: null, marchamo: null, cargas: [null, null, null] },
        loading: { cajaVacia: false, marchamo: false, cargas: [false, false, false] },

        addPhotoField() {
            this.cargaPhotos.push({ id: Date.now() });
            this.previews.cargas.push(null);
            this.loading.cargas.push(false);
        },

        removePhotoField(index) {
            if (this.cargaPhotos.length > 3) {
                this.cargaPhotos.splice(index, 1);
                this.previews.cargas.splice(index, 1);
                this.loading.cargas.splice(index, 1);
            }
        },

        async handleImageSelection(event, target, index = null) {
            const file = event.target.files[0];
            if (!file) return;

            // --- INICIA CÓDIGO MEJORADO ---
            // Verificamos si la librería existe ANTES de intentar usarla
            if (typeof imageCompression === 'undefined') {
                alert('Error crítico: La librería de compresión de imágenes no se ha cargado.');
                return;
            }
            // --- FIN CÓDIGO MEJORADO ---

            const setLoading = (val) => {
                if (index !== null) this.loading[target][index] = val; else this.loading[target] = val;
            };
            const setPreview = (val) => {
                if (index !== null) this.previews[target][index] = val; else this.previews[target] = val;
            };

            setLoading(true);
            setPreview(null);

            const options = { maxSizeMB: 1.5, maxWidthOrHeight: 1920, useWebWorker: true };

            try {
                const compressedFile = await imageCompression(file, options);
                setPreview(await imageCompression.getDataUrlFromFile(compressedFile));

                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(compressedFile);
                event.target.files = dataTransfer.files;
            } catch (error) {
                console.error('Error al comprimir:', error);
                // Mostramos un error más detallado
                alert(`Hubo un error al procesar la imagen: ${error.message}`);
            } finally {
                setLoading(false);
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
                    <ul class="mt-2 list-disc list-inside text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
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
                            <div><label for="tarimas_tipo" class="block text-sm font-medium text-gray-700">Tipo de Tarima</label><select name="tarimas_tipo" id="tarimas_tipo" x-model="tipoTarima" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="N/A" disabled>Selecciona...</option><option value="Chep">Chep</option><option value="Estándar">Estándar</option><option value="Ambas">Ambas</option></select></div>
                            <div x-show="tipoTarima === 'Chep' || tipoTarima === 'Ambas'" x-transition><label for="tarimas_cantidad_chep" class="block text-sm font-medium text-gray-700">Cantidad Tarimas Chep</label><input type="number" name="tarimas_cantidad_chep" id="tarimas_cantidad_chep" value="{{ old('tarimas_cantidad_chep', 0) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" min="0"></div>
                            <div x-show="tipoTarima === 'Estándar' || tipoTarima === 'Ambas'" x-transition><label for="tarimas_cantidad_estandar" class="block text-sm font-medium text-gray-700">Cantidad Tarimas Estándar</label><input type="number" name="tarimas_cantidad_estandar" id="tarimas_cantidad_estandar" value="{{ old('tarimas_cantidad_estandar', 0) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" min="0"></div>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t">
                    <button type="button" @click="incidenciasOpen = !incidenciasOpen" class="w-full flex justify-between items-center text-left"><h3 class="font-bold text-lg text-gray-800">Incidencias (opcional)</h3><i class="fas" :class="incidenciasOpen ? 'fa-chevron-up' : 'fa-chevron-down'"></i></button>
                    <div x-show="incidenciasOpen" x-transition class="mt-4 space-y-2 p-4 bg-gray-50 rounded-lg border grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-4 gap-y-2">
                        @php $incidencias = ['Producto cambiado','Producto no etiquetado','Producto sobrante','Distribución incorrecta','Producto dañado','Producto faltante','Retraso en almacén','Producto sin maquila VA','Administración Planus','Unidad no adecuada','Unidad sin maniobra','Falta de herramientas en transporte','Gestión de operador','Modificación de embarque']; @endphp
                        @foreach($incidencias as $incidencia)<label class="flex items-center text-sm font-medium"><input type="checkbox" name="incidencias[]" value="{{ $incidencia }}" class="rounded mr-2 text-indigo-600 shadow-sm">{{ $incidencia }}</label>@endforeach
                    </div>
                </div>
                
                <div class="pt-6 border-t">
                    <h3 class="font-bold text-lg text-gray-800 mb-4">1. Evidencias Fotográficas de Carga</h3>
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Foto de Caja Vacía <span class="text-red-500">*</span></label>
                            <input type="file" name="foto_caja_vacia" x-ref="cajaVaciaInput" @change="handleImageSelection($event, 'cajaVacia')" class="hidden" accept="image/*" capture="environment" required>
                            <div @click="!loading.cajaVacia && $refs.cajaVaciaInput.click()" class="cursor-pointer border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-indigo-500 transition-colors h-48 flex items-center justify-center">
                                 <template x-if="loading.cajaVacia"><div><i class="fas fa-spinner fa-spin text-4xl text-gray-400"></i><p class="mt-2 text-sm text-gray-600">Comprimiendo...</p></div></template>
                                 <template x-if="!loading.cajaVacia && !previews.cajaVacia"><div><i class="fas fa-box-open text-4xl text-gray-400"></i><p class="mt-2 text-sm text-gray-600">Clic para seleccionar</p></div></template>
                                 <template x-if="!loading.cajaVacia && previews.cajaVacia"><img :src="previews.cajaVacia" class="max-h-full max-w-full mx-auto rounded-md object-contain"></template>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fotos del Proceso de Carga <span class="text-red-500">*</span> (Mínimo 3)</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4">
                                <template x-for="(photo, index) in cargaPhotos" :key="photo.id">
                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-2" x-text="`Foto ` + (index + 1)"></label>
                                        <input type="file" name="fotos_carga[]" :id="'cargaInput' + index" @change="handleImageSelection($event, 'cargas', index)" class="hidden" accept="image/*" capture="environment" required>
                                        <div @click="!loading.cargas[index] && document.getElementById('cargaInput' + index).click()" class="cursor-pointer border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-indigo-500 transition-colors h-48 flex items-center justify-center">
                                            <template x-if="loading.cargas[index]"><div><i class="fas fa-spinner fa-spin text-4xl text-gray-400"></i><p class="mt-2 text-sm text-gray-600">Comprimiendo...</p></div></template>
                                            <template x-if="!loading.cargas[index] && !previews.cargas[index]"><div><i class="fas fa-camera text-4xl text-gray-400"></i><p class="mt-2 text-sm text-gray-600">Clic para seleccionar</p></div></template>
                                            <template x-if="!loading.cargas[index] && previews.cargas[index]"><img :src="previews.cargas[index]" class="max-h-full max-w-full mx-auto rounded-md object-contain"></template>
                                        </div>
                                        <template x-if="cargaPhotos.length > 3">
                                            <button type="button" @click="removePhotoField(index)" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full h-6 w-6 flex items-center justify-center text-xs shadow">&times;</button>
                                        </template>
                                    </div>
                                </template>
                            </div>
                            <button type="button" @click="addPhotoField()" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold hover:bg-blue-700">
                                <i class="fas fa-plus mr-2"></i>Agregar Otra Foto
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="pt-6 border-t">
                     <h3 class="font-bold text-lg text-gray-800 mb-2">2. Finalización y Seguridad</h3>
                     <div class="space-y-4 bg-gray-50 p-4 rounded-lg border">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Número de Marchamo (si aplica)</label>
                            <input type="text" name="marchamo_numero" value="{{ old('marchamo_numero') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">¿Lleva Custodia?</label>
                            <select name="lleva_custodia" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                <option value="1" @selected(old('lleva_custodia') == '1')>Sí</option>
                                <option value="0" @selected(old('lleva_custodia', '0') == '0')>No</option>
                            </select>
                        </div>
                        <div class="pt-4 border-t">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Foto de Marchamo (si aplica)</label>
                            <input type="file" name="foto_marchamo" x-ref="marchamoInput" @change="handleImageSelection($event, 'marchamo')" class="hidden" accept="image/*" capture="environment">
                            <div @click="!loading.marchamo && $refs.marchamoInput.click()" class="cursor-pointer border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-indigo-500 transition-colors h-48 flex items-center justify-center">
                                <template x-if="loading.marchamo"><div><i class="fas fa-spinner fa-spin text-4xl text-gray-400"></i><p class="mt-2 text-sm text-gray-600">Comprimiendo...</p></div></template>
                                <template x-if="!loading.marchamo && !previews.marchamo"><div><i class="fas fa-lock text-4xl text-gray-400"></i><p class="mt-2 text-sm text-gray-600">Clic para seleccionar</p></div></template>
                                <template x-if="!loading.marchamo && previews.marchamo"><img :src="previews.marchamo" class="max-h-full max-w-full mx-auto rounded-md object-contain"></template>
                            </div>
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