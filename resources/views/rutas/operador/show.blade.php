@extends('layouts.guest-rutas')

@section('content')
    {{-- Se inicializa el componente AlpineJS con todos los datos necesarios de la guía y sus facturas --}}
    <div x-data="operatorView({{ json_encode($guia->load('facturas')) }})">
        <div class="text-center mb-6">
            <p class="text-sm text-gray-500">Guía No. / Estatus Actual</p>
            <h2 class="text-3xl font-bold text-[#2c3856] tracking-wider">{{ $guia->guia }}</h2>
            {{-- El estatus se actualiza dinámicamente con AlpineJS --}}
            <p class="mt-1 text-lg font-semibold" :class="getBadgeClass(guia.estatus, true)" x-text="guia.estatus"></p>
        </div>

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition class="bg-green-100 text-green-800 p-4 rounded-md mb-4 text-sm font-semibold">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition class="bg-red-100 text-red-700 p-4 rounded-md mb-4 text-sm font-semibold">{{ session('error') }}</div>
        @endif

        <div class="bg-white p-6 rounded-xl shadow-lg">
            
            <div x-show="guia.estatus === 'Planeada'">
                <h3 class="font-bold text-lg text-center text-gray-700 mb-4">Paso 1: Iniciar Viaje a Carga</h3>
                <form id="start-form" action="{{ route('operador.guia.start', $guia->guia) }}" method="POST">
                    @csrf
                    <input type="hidden" name="latitud" id="start-latitud">
                    <input type="hidden" name="longitud" id="start-longitud">
                    <input type="hidden" name="municipio" id="start-municipio">
                    <button type="button" @click="submitStartForm()" :disabled="isLoading" class="w-full justify-center inline-flex items-center px-6 py-4 bg-green-600 text-white rounded-lg font-semibold text-lg hover:bg-green-700 disabled:opacity-50">
                        <span x-show="!isLoading">Iniciar Viaje</span>
                        <span x-show="isLoading" class="flex items-center"><svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Enviando...</span>
                    </button>
                </form>
            </div>

            <div x-show="guia.estatus === 'Camino a carga'">
                <h3 class="font-bold text-lg text-center text-gray-700 mb-4">Paso 2: Llegada a punto de Carga</h3>
                <button @click="openModal('Sistema', 'Llegada a carga')" class="w-full justify-center inline-flex items-center px-6 py-4 bg-[#2c3856] text-white rounded-lg font-semibold text-lg hover:bg-opacity-90">Registrar Llegada a Carga</button>
            </div>

            <div x-show="guia.estatus === 'En espera de carga'">
                <h3 class="font-bold text-lg text-center text-gray-700 mb-4">Paso 3: Finalizar Carga de Unidad</h3>
                <button @click="openModal('Sistema', 'Fin de carga')" class="w-full justify-center inline-flex items-center px-6 py-4 bg-[#2c3856] text-white rounded-lg font-semibold text-lg hover:bg-opacity-90">Registrar Fin de Carga</button>
            </div>

            <div x-show="guia.estatus === 'Por iniciar ruta'">
                 <h3 class="font-bold text-lg text-center text-gray-700 mb-4">Paso 4: Iniciar Ruta a Destino</h3>
                <button @click="openModal('Sistema', 'En ruta')" class="w-full justify-center inline-flex items-center px-6 py-4 bg-green-600 text-white rounded-lg font-semibold text-lg hover:bg-green-700">Confirmar Inicio de Ruta</button>
            </div>

            <div x-show="guia.estatus === 'En tránsito' || guia.estatus === 'En Pernocta'">
                <h3 class="font-bold text-lg text-center text-gray-700 mb-4">Acciones en Ruta</h3>
                <div class="mb-6 grid grid-cols-2 gap-4">
                    <button x-show="guia.estatus === 'En Pernocta'" @click="openModal('Sistema', 'En ruta')" class="w-full justify-center inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700">Reanudar Ruta</button>
                    <button x-show="guia.estatus === 'En tránsito'" @click="openModal('Notificacion', 'Pernocta')" class="w-full justify-center inline-flex items-center px-6 py-3 bg-yellow-500 text-black rounded-lg font-semibold hover:bg-yellow-600">Registrar Pernocta</button>
                    <button @click="openModal('Sistema', 'Llegada a cliente', true)" class="w-full justify-center inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700">Llegada a Cliente</button>
                    
                    <button @click="openEventSelectionModal('Notificacion')" class="col-span-1 w-full justify-center inline-flex items-center px-6 py-3 bg-gray-500 text-white rounded-lg font-semibold hover:bg-gray-600">
                        Notificar Evento
                    </button>
                    <button @click="openEventSelectionModal('Incidencias')" class="col-span-1 w-full justify-center inline-flex items-center px-6 py-3 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700">
                        Reportar Incidencia
                    </button>
                </div>
                
                <h4 class="font-semibold text-gray-600 mb-2 border-b pb-2">Estado de Facturas</h4>
                <div class="space-y-2 mt-4">
                     <template x-for="factura in guia.facturas" :key="factura.id">
                        <div class="p-3 rounded-lg flex justify-between items-center bg-gray-50 border">
                            <div>
                                <p class="font-bold text-gray-800" x-text="factura.numero_factura"></p>
                                <p class="text-xs" x-text="factura.destino"></p>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="getBadgeClass(factura.estatus_entrega, false)" x-text="factura.estatus_entrega"></span>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <button x-show="factura.estatus_entrega === 'En cliente'" @click="openModal('Sistema', 'Proceso de entrega', false, [factura.id])" class="text-xs bg-indigo-600 text-white px-3 py-1 rounded-full font-semibold">Iniciar Entrega</button>
                                <button x-show="factura.estatus_entrega === 'Entregando'" @click="openModal('Entrega', 'Entregada', false, [factura.id])" class="text-xs bg-green-600 text-white px-3 py-1 rounded-full font-semibold">Entregada OK</button>
                                <button x-show="factura.estatus_entrega === 'Entregando'" @click="openModal('Entrega', 'No entregada', false, [factura.id])" class="text-xs bg-red-600 text-white px-3 py-1 rounded-full font-semibold">No Entregada</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div x-show="guia.estatus === 'Completada'">
                 <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-6 rounded-lg text-center">
                    <h3 class="text-xl font-bold">¡Ruta Finalizada!</h3>
                    <p class="mt-2 text-sm">Has concluido todas las entregas de esta guía. Gracias.</p>
                </div>
            </div>
        </div>

        <div x-show="isModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" @keydown.escape.window="isModalOpen = false">
            <div @click.outside="isModalOpen = false" class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg max-h-[90vh] flex flex-col">
                <h3 class="text-lg font-bold text-[#2c3856] mb-4" x-text="modal.title"></h3>
                <form id="event-form" action="{{ route('operador.guia.event.store', $guia->guia) }}" method="POST" enctype="multipart/form-data" class="flex-grow flex flex-col">
                    @csrf
                    <input type="hidden" name="tipo" x-model="modal.tipo">
                    <input type="hidden" name="subtipo" x-model="modal.subtipo">
                    <input type="hidden" name="latitud" id="event-latitud">
                    <input type="hidden" name="longitud" id="event-longitud">
                    <input type="hidden" name="municipio" id="event-municipio">

                    <div class="flex-grow overflow-y-auto pr-2">
                        
                        <div x-show="modal.isSelection" class="mb-4">
                            <label for="subtipo_select" class="block text-sm font-medium text-gray-700">Selecciona el tipo de evento</label>
                            <select id="subtipo_select" x-model="modal.subtipo" name="subtipo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <template x-for="subtype in availableSubtypes" :key="subtype">
                                    <option :value="subtype" x-text="subtype"></option>
                                </template>
                            </select>
                        </div>
                        
                        <div x-show="modal.needsInvoices" class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Seleccionar Facturas Afectadas</label>
                            <div class="max-h-40 overflow-y-auto border rounded-md p-2 mt-1 space-y-1">
                                <template x-for="factura in availableInvoicesForModal" :key="factura.id">
                                    <div>
                                        <label class="inline-flex items-center w-full p-2 hover:bg-gray-100 rounded-md">
                                            <input type="checkbox" name="factura_ids[]" :value="factura.id" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                            <span class="ml-2 text-sm" x-text="factura.numero_factura + ' (' + factura.destino + ')'"></span>
                                        </label>
                                    </div>
                                </template>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Evidencia Fotográfica</label>
                            <input type="file" id="original-evidencia" accept="image/*" multiple @change="processImages" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100">

                            {{-- Este input oculto recibirá las imágenes ya procesadas --}}
                            <input type="hidden" name="evidencia[]" id="processed-evidencia">
                            <p class="text-xs text-gray-500 mt-1" x-text="modal.evidenceRequired ? 'Evidencia obligatoria (máx. 10 fotos).' : 'Evidencia opcional.'"></p>
                        </div>

                        <div class="mb-4">
                            <label for="nota" class="block text-sm font-medium text-gray-700">Notas (Opcional)</label>
                            <textarea name="nota" id="nota" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                        </div>
                    </div>

                    <p x-show="locationError" x-text="locationError" class="text-red-600 text-sm mt-2"></p>
                    
                    <div class="mt-6 flex justify-end gap-4 border-t pt-4">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancelar</button>
                        <button type="button" @click="submitEventForm()" :disabled="isLoading" class="px-4 py-2 bg-blue-600 text-white rounded-md disabled:opacity-50">
                             <span x-show="!isLoading">Confirmar</span>
                             <span x-show="isLoading" class="flex items-center"><svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Enviando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places" async defer></script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('operatorView', (guiaData) => ({
            guia: guiaData,
            isModalOpen: false,
            isLoading: false,
            locationError: '',
            modal: { title: '', tipo: '', subtipo: '', needsInvoices: false, evidenceRequired: false, isSelection: false },

            eventSubtypes: {
                'Notificacion': ['Alimentos', 'Combustible', 'Sanitario', 'Otro'],
                'Incidencias': ['Rechazo', 'Percance', 'Tráfico', 'Falla mecánica', 'Incidencia con autoridad', 'Otro']
            },
            availableSubtypes: [],

            openModal(tipo, subtipo, needsInvoices = false, fixedFacturaIds = []) {
                this.modal = {
                    title: `Registrar: ${subtipo}`,
                    tipo: tipo,
                    subtipo: subtipo,
                    needsInvoices: needsInvoices,
                    evidenceRequired: (subtipo === 'Entregada' || subtipo === 'No entregada'),
                    fixedFacturaIds: fixedFacturaIds,
                    isSelection: false 
                };
                
                if(fixedFacturaIds.length > 0){
                    this.modal.needsInvoices = false;
                }

                this.isModalOpen = true;
            },

            openEventSelectionModal(tipo) {
                this.availableSubtypes = this.eventSubtypes[tipo] || [];
                this.modal = {
                    title: `Seleccionar ${tipo}`,
                    tipo: tipo,
                    subtipo: this.availableSubtypes[0],
                    needsInvoices: false,
                    evidenceRequired: false,
                    fixedFacturaIds: [],
                    isSelection: true
                };
                this.isModalOpen = true;
            },
            
            get availableInvoicesForModal() {
                if (this.modal.subtipo === 'Llegada a cliente') {
                    return this.guia.facturas.filter(f => f.estatus_entrega === 'En tránsito');
                }
                if (this.modal.subtipo === 'Proceso de entrega') {
                    return this.guia.facturas.filter(f => f.estatus_entrega === 'En cliente');
                }
                return this.guia.facturas;
            },
            
            getBadgeClass(status, isGuia) {
                const colors = {
                    'Planeada': 'bg-gray-200 text-gray-800',
                    'Camino a carga': 'bg-cyan-100 text-cyan-800',
                    'En espera de carga': 'bg-yellow-100 text-yellow-800',
                    'Por iniciar ruta': 'bg-orange-100 text-orange-800',
                    'En tránsito': 'bg-blue-100 text-blue-800',
                    'En Pernocta': 'bg-indigo-100 text-indigo-800',
                    'En cliente': 'bg-purple-100 text-purple-800',
                    'Entregando': 'bg-fuchsia-100 text-fuchsia-800',
                    'Entregada': 'bg-green-100 text-green-800',
                    'No entregada': 'bg-red-100 text-red-800',
                    'Completada': 'bg-green-200 text-green-900 font-bold',
                    'default': 'bg-gray-200 text-gray-800'
                };
                if (isGuia) {
                    return `px-3 py-1 text-lg rounded-full transition-colors duration-300 ${colors[status] || colors.default}`;
                }
                return colors[status] || colors.default;
            },

            processImages(event) {
                const files = event.target.files;
                const processedFiles = [];
                
                // Función para procesar un solo archivo de forma asíncrona
                const processFile = (file) => {
                    return new Promise((resolve) => {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            const img = new Image();
                            img.onload = () => {
                                const canvas = document.createElement('canvas');
                                const MAX_WIDTH = 800; // Define el ancho máximo deseado para la imagen
                                let width = img.width;
                                let height = img.height;

                                // Redimensionar si la imagen es más grande que el ancho máximo
                                if (width > MAX_WIDTH) {
                                    height = height * (MAX_WIDTH / width);
                                    width = MAX_WIDTH;
                                }

                                canvas.width = width;
                                canvas.height = height;
                                const ctx = canvas.getContext('2d');
                                ctx.drawImage(img, 0, 0, width, height);
                                
                                // Comprimir la imagen a un 70% de calidad y convertirla en un Blob
                                canvas.toBlob((blob) => {
                                    const newFile = new File([blob], file.name, {
                                        type: 'image/jpeg',
                                        lastModified: Date.now()
                                    });
                                    resolve(newFile);
                                }, 'image/jpeg', 0.7);
                            };
                            img.src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    });
                };

                // Procesar todos los archivos seleccionados en paralelo
                Promise.all(Array.from(files).map(processFile)).then(processedBlobs => {
                    // Usa DataTransfer para crear una lista de archivos
                    const dataTransfer = new DataTransfer();
                    processedBlobs.forEach(blob => {
                        dataTransfer.items.add(blob);
                    });
                    // Asigna la lista de archivos al input oculto
                    document.getElementById('processed-evidencia').files = dataTransfer.files;
                });
            },           

            submitEventForm() {
                const form = document.getElementById('event-form');
                // Se valida el nuevo input de tipo 'file' que ya tiene las fotos procesadas
                const evidenceInput = document.getElementById('processed-evidencia');
                const facturasCheckboxes = form.querySelectorAll('input[name="factura_ids[]"]:checked');

                if (this.modal.evidenceRequired && evidenceInput.files.length === 0) {
                    alert('La evidencia fotográfica es obligatoria para este evento.');
                    return;
                }
                if (this.modal.needsInvoices && facturasCheckboxes.length === 0) {
                    alert('Debes seleccionar al menos una factura para esta acción.');
                    return;
                }

                this.isLoading = true;
                this.locationError = '';
                this.getLocationAndSubmit('event-form', this.modal.fixedFacturaIds);
            },
            
            submitStartForm() {
                this.isLoading = true;
                this.locationError = '';
                this.getLocationAndSubmit('start-form');
            },

            getLocationAndSubmit(formId, fixedFacturaIds = []) {
                if (!navigator.geolocation) {
                    this.locationError = 'Geolocalización no está disponible en tu navegador.';
                    this.isLoading = false;
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    async (position) => {
                        const form = document.getElementById(formId);
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        
                        form.querySelector(`input[name="latitud"]`).value = lat;
                        form.querySelector(`input[name="longitud"]`).value = lng;
                        
                        // Asegurarse de que el objeto 'google' exista antes de usarlo
                        if (typeof google !== 'undefined' && google.maps) {
                            const geocoder = new google.maps.Geocoder();
                            const latlng = { lat: parseFloat(lat), lng: parseFloat(lng) };
                            try {
                                const { results } = await geocoder.geocode({ location: latlng });
                                let municipio = 'N/A';
                                if (results[0]) {
                                    for (const component of results[0].address_components) {
                                        if (component.types.includes("locality")) {
                                            municipio = component.long_name;
                                            break;
                                        }
                                    }
                                }
                                form.querySelector(`input[name="municipio"]`).value = municipio;
                            } catch (e) {
                                 console.error("Error de Geocodificación: ", e);
                                 form.querySelector(`input[name="municipio"]`).value = "Error al obtener municipio";
                            }
                        } else {
                            form.querySelector(`input[name="municipio"]`).value = "API de Google no disponible";
                        }
                        
                        // Limpiar y añadir IDs de factura fijos si existen
                        form.querySelectorAll('input[type="hidden"][name="factura_ids[]"]').forEach(el => el.remove());
                        if (fixedFacturaIds.length > 0) {
                            fixedFacturaIds.forEach(id => {
                                const hiddenInput = document.createElement('input');
                                hiddenInput.type = 'hidden';
                                hiddenInput.name = 'factura_ids[]';
                                hiddenInput.value = id;
                                form.appendChild(hiddenInput);
                            });
                        }
                        
                        form.submit();
                    },
                    () => {
                        this.locationError = 'No se pudo obtener la ubicación. Por favor, activa el GPS y otorga los permisos necesarios.';
                        this.isLoading = false;
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            }
        }));
    });
</script>
@endpush