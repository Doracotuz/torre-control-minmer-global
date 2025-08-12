@extends('layouts.guest-rutas')

@section('content')
    <div x-data="maniobristaView('{{ $estadoActual }}', '{{ $numero_empleado }}', '{{ $googleMapsApiKey }}', {{ $facturasPendientes->values()->toJson() }})">
        <div class="text-center mb-6">
            <p class="text-sm text-gray-500">Guía No.</p>
            <h2 class="text-3xl font-bold text-[#2c3856] tracking-wider">{{ $guia->guia }}</h2>
            <p class="mt-1 text-gray-600">Maniobrista: {{ $numero_empleado }}</p>
        </div>

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition class="bg-green-100 text-green-800 p-4 rounded-md mb-4 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition class="bg-red-100 text-red-600 p-4 rounded-md mb-4 text-sm">{{ session('error') }}</div>
        @endif

        <div class="bg-white p-6 rounded-xl shadow-lg">
            
            {{-- EVENTO 1: LLEGADA A CARGA --}}
            <div x-show="currentState === 'Llegada a carga'">
                <h3 class="font-bold text-lg text-center text-gray-700 mb-4">Paso 1: Llegada a Carga</h3>
                <button @click="openEventModal('Llegada a carga')" class="w-full justify-center inline-flex items-center px-6 py-4 bg-[#2c3856] text-white rounded-lg font-semibold text-lg hover:bg-opacity-90">
                    Registrar Llegada a Carga
                </button>
            </div>

            {{-- EVENTO 2: INICIO DE RUTA --}}
            <div x-show="currentState === 'Inicio de ruta'">
                <h3 class="font-bold text-lg text-center text-gray-700 mb-4">Paso 2: Inicio de Ruta</h3>
                <button @click="openEventModal('Inicio de ruta')" class="w-full justify-center inline-flex items-center px-6 py-4 bg-[#2c3856] text-white rounded-lg font-semibold text-lg hover:bg-opacity-90">
                    Registrar Inicio de Ruta
                </button>
            </div>

            {{-- FASE 3: EN RUTA (ENTREGAS) --}}
            <div x-show="currentState === 'En Ruta (Entregas)'">
                <h3 class="font-bold text-lg text-gray-700 mb-4">Paso 3: Entregas en Destino</h3>
                
                <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <button @click="openEventModal('Llegada a destino')" class="w-full justify-center inline-flex items-center px-6 py-3 bg-[#ff9c00] text-white rounded-lg font-semibold hover:bg-orange-600">
                        <i class="fas fa-map-marker-alt mr-2"></i> Registrar Llegada a Destino
                    </button>
                    <button @click="openDeliveryModal()" class="w-full justify-center inline-flex items-center px-6 py-3 bg-[#2c3856] text-white rounded-lg font-semibold hover:bg-opacity-90">
                        <i class="fas fa-camera mr-2"></i> Entregar Evidencias
                    </button>
                </div>
                
                <h4 class="font-semibold text-gray-600 mb-2">Estado de Facturas</h4>
                <div class="space-y-2">
                    @foreach($guia->facturas as $factura)
                        <div class="flex justify-between items-center p-3 rounded-lg {{ $factura->evidenciasManiobra->isNotEmpty() ? 'bg-green-50 text-green-800' : 'bg-gray-50 text-gray-700' }}">
                            <div>
                                <p class="font-bold">{{ $factura->numero_factura }}</p>
                                <p class="text-xs">{{ $factura->destino }}</p>
                            </div>
                            @if($factura->evidenciasManiobra->isNotEmpty())
                                <span class="text-xs font-bold flex items-center"><i class="fas fa-check-circle mr-1"></i> Evidencia Subida</span>
                            @else
                                <span class="text-xs font-bold">Pendiente</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- FLUJO COMPLETADO --}}
            <div x-show="currentState === 'Completado'">
                 <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-6 rounded-lg text-center">
                    <h3 class="text-xl font-bold">¡Flujo Completado!</h3>
                    <p class="mt-2 text-sm">Has registrado todas las evidencias para esta guía. Gracias.</p>
                </div>
            </div>
        </div>

        <div x-show="isEventModalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
            <div @click.outside="isEventModalOpen = false" class="bg-white rounded-lg shadow-xl p-6 w-full max-w-sm">
                <h3 class="text-lg font-bold text-[#2c3856] mb-4" x-text="`Registrar: ${evento.tipo}`"></h3>
                <form id="event-form" action="{{ route('maniobrista.guia.event.store', ['guia' => $guia->guia, 'empleado' => $numero_empleado]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="evento_tipo" x-model="evento.tipo">
                    <input type="hidden" name="latitud" id="event-latitud-input">
                    <input type="hidden" name="longitud" id="event-longitud-input">
                    <input type="hidden" name="municipio" id="event-municipio-input">
                    
                    <div>
                        <label for="evidencia-evento" class="block text-sm font-medium text-gray-700">Evidencia (Cámara obligatoria)</label>
                        <input type="file" name="evidencia" id="evidencia-evento" accept="image/*" capture="camera" required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#ff9c00]/20 file:text-[#ff9c00] hover:file:bg-[#ff9c00]/30">
                        <p class="text-xs text-gray-500 mt-1">Se abrirá la cámara de tu dispositivo.</p>
                    </div>
                    <p x-show="locationError" x-text="locationError" class="text-red-600 text-sm mt-2"></p>
                    
                    <div class="mt-6 flex justify-end gap-4">
                        <button type="button" @click="isEventModalOpen = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md">Cancelar</button>
                        <button type="button" @click="submitSingleEventForm()" :disabled="isLoading" class="px-4 py-2 bg-blue-600 text-white rounded-md disabled:opacity-50">
                             <span x-show="!isLoading">Confirmar</span>
                             <span x-show="isLoading" class="flex items-center"><svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Enviando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="isDeliveryModalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
            <div @click.outside="isDeliveryModalOpen = false" class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
                <h3 class="text-lg font-bold text-[#2c3856] mb-4">Registrar Entrega de Facturas</h3>
                <form id="evidencias-form" action="{{ route('maniobrista.guia.evidencias.store', ['guia' => $guia->guia, 'empleado' => $numero_empleado]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="latitud" id="evidencias-latitud-input">
                    <input type="hidden" name="longitud" id="evidencias-longitud-input">
                    <input type="hidden" name="municipio" id="evidencias-municipio-input">
                    
                    <p class="text-sm text-gray-600 mb-4">Selecciona las facturas que estás entregando y adjunta las evidencias.</p>
                    
                    <div class="space-y-4 max-h-64 overflow-y-auto">
                        <template x-for="factura in pendingInvoices" :key="factura.id">
                            <div class="border p-4 rounded-lg">
                                <label class="flex items-start cursor-pointer">
                                    <input type="checkbox" :value="factura.id" @change="toggleFiles(factura.id, $event.target.checked)" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mt-1">
                                    <div class="ml-3">
                                        <p class="font-bold text-gray-800" x-text="factura.numero_factura"></p>
                                        <p class="text-xs text-gray-600" x-text="factura.destino"></p>
                                    </div>
                                </label>
                                <div x-show="selectedInvoices.includes(factura.id)" x-transition class="mt-3 pl-7">
                                    <label :for="'evidencia-' + factura.id" class="cursor-pointer inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md font-semibold text-xs hover:bg-gray-200 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        <span>Adjuntar (máx. 3)</span>
                                    </label>
                                    <input type="file" :id="'evidencia-' + factura.id" :name="`evidencias[${factura.id}][]`" accept="image/*" multiple class="hidden evidencia-input" onchange="updateFileList(this)">
                                    <div :id="'file-list-' + factura.id" class="text-xs text-gray-500 mt-2"></div>
                                </div>
                            </div>
                        </template>
                        <div x-show="pendingInvoices.length === 0" class="text-center text-gray-500 p-4">No hay más facturas pendientes.</div>
                    </div>

                    <p x-show="locationError" x-text="locationError" class="text-red-600 text-sm mt-2"></p>
                    <div class="mt-6 flex justify-end gap-4">
                        <button type="button" @click="isDeliveryModalOpen = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md">Cancelar</button>
                        <button type="button" @click="submitEvidenciasForm()" :disabled="isLoading" class="px-4 py-2 bg-blue-600 text-white rounded-md disabled:opacity-50">
                             <span x-show="!isLoading">Confirmar Entregas</span>
                             <span x-show="isLoading" class="flex items-center"><svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Enviando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places"></script>
<script>
    function updateFileList(input) {
        const fileListContainer = document.getElementById(`file-list-${input.id.split('-')[1]}`);
        fileListContainer.innerHTML = '';
        if (input.files.length > 0) {
            const list = document.createElement('ul'); list.className = 'list-disc list-inside';
            for (const file of input.files) {
                const listItem = document.createElement('li'); listItem.textContent = file.name; list.appendChild(listItem);
            }
            fileListContainer.appendChild(list);
        }
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('maniobristaView', (estadoActual, numEmpleado, apiKey, facturasPendientes) => ({
            isLoading: false, locationError: '', isEventModalOpen: false, isDeliveryModalOpen: false,
            currentState: estadoActual, evento: { tipo: '', empleado: numEmpleado }, googleMapsApiKey: apiKey,
            pendingInvoices: facturasPendientes, selectedInvoices: [],

            openEventModal(tipoDeEvento) { this.evento.tipo = tipoDeEvento; this.isEventModalOpen = true; },
            openDeliveryModal() { this.selectedInvoices = []; this.isDeliveryModalOpen = true; },

            toggleFiles(facturaId, isChecked) {
                if (isChecked) {
                    if (!this.selectedInvoices.includes(facturaId)) this.selectedInvoices.push(facturaId);
                } else {
                    this.selectedInvoices = this.selectedInvoices.filter(id => id !== facturaId);
                }
            },
            
            async getMunicipality(lat, lng) {
                try {
                    const response = await fetch(`https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key=${this.googleMapsApiKey}`);
                    const data = await response.json();
                    if (data.status === 'OK' && data.results.length > 0) {
                        for (const result of data.results) {
                            for (const component of result.address_components) {
                                if (component.types.includes('locality')) return component.long_name;
                                if (component.types.includes('administrative_area_level_1')) var state = component.long_name;
                            }
                        }
                        return state || 'Ubicación no encontrada';
                    } return 'N/A';
                } catch (error) { console.error('Error en geocodificación:', error); return 'Error de red'; }
            },

            submitSingleEventForm() {
                this.isLoading = true; this.locationError = '';
                if (!document.getElementById('evidencia-evento').files.length) {
                    alert('La foto de evidencia es obligatoria.'); this.isLoading = false; return;
                }
                this.getLocationAndSubmit('event-form');
            },

            submitEvidenciasForm() {
                if (this.selectedInvoices.length === 0) { alert('Debes seleccionar al menos una factura.'); return; }
                let filesAttached = false;
                this.selectedInvoices.forEach(id => {
                    const input = document.getElementById(`evidencia-${id}`);
                    if (input && input.files.length > 0) filesAttached = true;
                });
                if (!filesAttached) {
                    if (!confirm('No has adjuntado archivos. ¿Continuar?')) return;
                }
                this.isLoading = true; this.locationError = '';
                this.getLocationAndSubmit('evidencias-form');
            },

            getLocationAndSubmit(formId) {
                if (!navigator.geolocation) { this.locationError = 'Geolocalización no soportada.'; this.isLoading = false; return; }
                navigator.geolocation.getCurrentPosition(
                    async (position) => {
                        const lat = position.coords.latitude; const lng = position.coords.longitude;
                        document.getElementById(`${formId.split('-')[0]}-latitud-input`).value = lat;
                        document.getElementById(`${formId.split('-')[0]}-longitud-input`).value = lng;
                        
                        const municipio = await this.getMunicipality(lat, lng);
                        document.getElementById(`${formId.split('-')[0]}-municipio-input`).value = municipio;
                        
                        document.getElementById(formId).submit();
                    },
                    () => { this.locationError = 'Activa el GPS y otorga los permisos.'; this.isLoading = false; },
                    { enableHighAccuracy: true }
                );
            }
        }));
    });
</script>
@endpush