@extends('layouts.guest-rutas')

@section('content')
    <div x-data="maniobristaView('{{ $siguienteEvento }}', '{{ $numero_empleado }}', '{{ $googleMapsApiKey }}')">
        <div class="text-center mb-6">
            <p class="text-sm text-gray-500">Guía No.</p>
            <h2 class="text-3xl font-bold text-[#2c3856] tracking-wider">{{ $guia->guia }}</h2>
            <p class="mt-1 text-gray-600">Maniobrista: {{ $numero_empleado }}</p>
        </div>

        {{-- Notificaciones de Sesión --}}
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition class="bg-green-100 text-green-800 p-4 rounded-md mb-4 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition class="bg-red-100 text-red-600 p-4 rounded-md mb-4 text-sm">{{ session('error') }}</div>
        @endif

        <div class="bg-white p-6 rounded-xl shadow-lg">
            
            {{-- EVENTO 1, 2 y 3 (sin cambios) --}}
            <div x-show="nextEvent === 'Llegada a carga'">
                <h3 class="font-bold text-lg text-center text-gray-700 mb-4">Paso 1 de 4: Llegada a Carga</h3>
                <button @click="openModal('Llegada a carga')" class="w-full justify-center inline-flex items-center px-6 py-4 bg-[#2c3856] text-white rounded-lg font-semibold text-lg hover:bg-opacity-90">Registrar Llegada a Carga</button>
            </div>
            <div x-show="nextEvent === 'Inicio de ruta'">
                <h3 class="font-bold text-lg text-center text-gray-700 mb-4">Paso 2 de 4: Inicio de Ruta</h3>
                <button @click="openModal('Inicio de ruta')" class="w-full justify-center inline-flex items-center px-6 py-4 bg-[#2c3856] text-white rounded-lg font-semibold text-lg hover:bg-opacity-90">Registrar Inicio de Ruta</button>
            </div>
            <div x-show="nextEvent === 'Llegada a destino'">
                <h3 class="font-bold text-lg text-center text-gray-700 mb-4">Paso 3 de 4: Llegada a Destino</h3>
                <button @click="openModal('Llegada a destino')" class="w-full justify-center inline-flex items-center px-6 py-4 bg-[#2c3856] text-white rounded-lg font-semibold text-lg hover:bg-opacity-90">Registrar Llegada a Destino</button>
            </div>
            
            {{-- EVENTO 4: ENTREGA DE EVIDENCIAS (CON BOTÓN MEJORADO) --}}
            <div x-show="nextEvent === 'Entrega de evidencias'">
                <h3 class="font-bold text-lg text-center text-gray-700 mb-4">Paso 4 de 4: Entrega de Evidencias</h3>
                <form id="evidencias-form" action="{{ route('maniobrista.guia.evidencias.store', ['guia' => $guia->guia, 'empleado' => $numero_empleado]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="latitud" id="evidencias-latitud-input">
                    <input type="hidden" name="longitud" id="evidencias-longitud-input">
                    <input type="hidden" name="municipio" id="evidencias-municipio-input">

                    <div class="space-y-4">
                        @foreach($guia->facturas as $factura)
                            <div class="border p-4 rounded-lg">
                                <p class="font-bold text-gray-800">{{ $factura->numero_factura }}</p>
                                <p class="text-sm text-gray-600 mb-2">{{ $factura->destino }}</p>
                                
                                <label for="evidencia-{{ $factura->id }}" class="cursor-pointer inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md font-semibold text-xs hover:bg-gray-200 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    <span>Adjuntar (máx. 3)</span>
                                </label>
                                <input type="file" id="evidencia-{{ $factura->id }}" name="evidencias[{{ $factura->id }}][]" accept="image/*" multiple class="hidden evidencia-input" onchange="updateFileList(this)">
                                <div id="file-list-{{ $factura->id }}" class="text-xs text-gray-500 mt-2"></div>
                                </div>
                        @endforeach
                    </div>
                    <p x-show="locationError" x-text="locationError" class="text-red-600 text-sm mt-2 text-center"></p>
                    <div class="mt-6 text-right">
                        <button type="button" @click="submitEvidenciasForm()" :disabled="isLoading" class="w-full justify-center inline-flex items-center px-6 py-4 bg-green-600 text-white rounded-lg font-semibold text-lg hover:bg-green-700 disabled:opacity-50">
                            <span x-show="!isLoading">Finalizar y Enviar Evidencias</span>
                            <span x-show="isLoading" class="flex items-center"></span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- FLUJO COMPLETADO --}}
            <div x-show="nextEvent === 'Completado'">
                 <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-6 rounded-lg text-center">
                    <h3 class="text-xl font-bold">¡Flujo Completado!</h3>
                    <p class="mt-2 text-sm">Has registrado todos los eventos para esta guía. Gracias.</p>
                </div>
            </div>
        </div>

        </div>
@endsection

@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places"></script>
<script>
    // --- NUEVA FUNCIÓN PARA MOSTRAR NOMBRES DE ARCHIVOS ---
    function updateFileList(input) {
        const fileListContainer = document.getElementById(`file-list-${input.id.split('-')[1]}`);
        fileListContainer.innerHTML = '';
        if (input.files.length > 0) {
            const list = document.createElement('ul');
            list.className = 'list-disc list-inside';
            for (const file of input.files) {
                const listItem = document.createElement('li');
                listItem.textContent = file.name;
                list.appendChild(listItem);
            }
            fileListContainer.appendChild(list);
        }
    }
document.addEventListener('alpine:init', () => {
    Alpine.data('maniobristaView', (siguienteEvento, numEmpleado, apiKey) => ({
        isLoading: false,
        locationError: '',
        isModalOpen: false,
        nextEvent: siguienteEvento,
        evento: { tipo: '', empleado: numEmpleado },
        googleMapsApiKey: apiKey,

        openModal(tipoDeEvento) {
            this.evento.tipo = tipoDeEvento;
            this.isModalOpen = true;
        },
        
        async getMunicipality(lat, lng) {
            try {
                const response = await fetch(`https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key=${this.googleMapsApiKey}`);
                const data = await response.json();
                if (data.status === 'OK' && data.results.length > 0) {
                    for (const result of data.results) {
                        for (const component of result.address_components) {
                            // Buscamos el municipio (locality) o el estado (administrative_area_level_1)
                            if (component.types.includes('locality')) {
                                return component.long_name;
                            }
                            if (component.types.includes('administrative_area_level_1')) {
                                var state = component.long_name;
                            }
                        }
                    }
                    return state || 'Ubicación no encontrada'; // Devuelve el estado si no se encontró municipio
                }
                return 'N/A';
            } catch (error) {
                console.error('Error en geocodificación:', error);
                return 'Error de red';
            }
        },

        submitEventForm() {
            this.isLoading = true;
            this.locationError = '';
            
            const fileInput = document.getElementById('evidencia');
            if (!fileInput.files || fileInput.files.length === 0) {
                alert('La foto de evidencia es obligatoria.');
                this.isLoading = false;
                return;
            }

            if (!navigator.geolocation) {
                this.locationError = 'Geolocalización no soportada.';
                this.isLoading = false;
                return;
            }

            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    document.getElementById('event-latitud-input').value = lat;
                    document.getElementById('event-longitud-input').value = lng;
                    
                    // Obtener municipio y luego enviar
                    const municipio = await this.getMunicipality(lat, lng);
                    document.getElementById('event-municipio-input').value = municipio;
                    
                    document.getElementById('event-form').submit();
                },
                () => {
                    this.locationError = 'Activa el GPS y otorga los permisos para continuar.';
                    this.isLoading = false;
                },
                { enableHighAccuracy: true }
            );
        },

        submitEvidenciasForm() {
            let fileCount = 0;
            // Contamos cuántos archivos se seleccionaron en total
            document.querySelectorAll('.evidencia-input').forEach(input => {
                fileCount += input.files.length;
            });

            // Si no hay archivos, pedimos confirmación
            if (fileCount === 0) {
                if (!confirm('No has adjuntado archivos para ninguna factura. ¿Estás seguro de que quieres continuar?')) {
                    return; // Detiene la ejecución si el usuario cancela
                }
            }

            this.isLoading = true;
            this.locationError = '';

            // Capturamos la ubicación y enviamos el formulario
            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    document.getElementById('evidencias-latitud-input').value = lat;
                    document.getElementById('evidencias-longitud-input').value = lng;
                    
                    const municipio = await this.getMunicipality(lat, lng);
                    document.getElementById('evidencias-municipio-input').value = municipio;
                    
                    document.getElementById('evidencias-form').submit();
                },
                () => {
                    this.locationError = 'Activa el GPS y otorga los permisos para continuar.';
                    this.isLoading = false;
                },
                { enableHighAccuracy: true }
            );
        }

    }));
});
</script>
@endpush