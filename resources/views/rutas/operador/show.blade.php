@extends('layouts.guest-rutas')

@section('content')
    <div x-data="operatorView()">
        <div class="text-center mb-6">
            <p class="text-sm text-gray-500">Guía No.</p>
            <h2 class="text-3xl font-bold text-[#2c3856] tracking-wider">{{ $guia->guia }}</h2>
            <p class="mt-1 text-gray-600">Operador: {{ $guia->operador }}</p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 p-4 rounded-md mb-4 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 text-red-600 p-4 rounded-md mb-4 text-sm">{{ session('error') }}</div>
        @endif

        {{-- ===================== SECCIÓN DE BOTONES ACTUALIZADA ===================== --}}
        @if($guia->estatus === 'Planeada')
            <div class="mb-6">
                <form id="start-route-form" action="{{ route('operador.guia.start', ['guia' => $guia->guia]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="latitud" id="latitud-input">
                    <input type="hidden" name="longitud" id="longitud-input">
                    
                    <button type="button" @click="startRoute()" :disabled="isLoading" class="w-full justify-center inline-flex items-center px-6 py-4 bg-green-600 text-white rounded-lg font-semibold text-lg hover:bg-green-700 disabled:opacity-50">
                        <span x-show="!isLoading">Iniciar Ruta</span>
                        <span x-show="isLoading">Obteniendo ubicación...</span>
                    </button>
                </form>
                <p x-show="locationError" x-text="locationError" class="text-red-600 text-sm mt-2 text-center"></p>
            </div>
        @elseif($guia->estatus === 'En Transito')
            <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Botón para Notificaciones de Rutina --}}
                <button @click="openNotificationModal()" class="w-full justify-center inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold text-lg hover:bg-blue-700">
                    Notificar Evento
                </button>
                {{-- Botón para Incidencias --}}
                <button @click="openIncidenceModal()" class="w-full justify-center inline-flex items-center px-6 py-3 bg-red-600 text-white rounded-lg font-semibold text-lg hover:bg-red-700">
                    Reportar Incidencia
                </button>
            </div>
        @elseif($guia->estatus === 'Completada')
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-800 p-6 rounded-lg text-center">
                <div class="flex items-center justify-center">
                    <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <h3 class="text-xl font-bold">¡Ruta Finalizada!</h3>
                </div>
                @if($guia->fecha_fin_ruta)
                    <p class="mt-2 text-sm">La guía fue completada el: {{ $guia->fecha_fin_ruta->format('d/m/Y h:i A') }}</p>
                @endif
            </div>

        @endif
        {{-- ========================================================================= --}}
        
        <div class="space-y-4">
            <h3 class="font-bold text-gray-700">Entregas</h3>
            @foreach($guia->facturas as $factura)
                <div class="bg-white border rounded-lg p-4">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="font-bold text-gray-800">{{ $factura->numero_factura }}</p>
                            <p class="text-sm text-gray-600">{{ $factura->destino }}</p>
                        </div>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($factura->estatus_entrega == 'Pendiente') bg-yellow-100 text-yellow-800
                            @elseif($factura->estatus_entrega == 'Entregada') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ $factura->estatus_entrega }}
                        </span>
                    </div>
                    @if($guia->estatus === 'En Transito' && $factura->estatus_entrega === 'Pendiente')
                    <div class="grid grid-cols-2 gap-2 mt-2">
                        <button @click="openDeliveryModal({{ $factura->id }}, 'Factura Entregada')" class="w-full text-sm py-2 px-3 bg-green-100 text-green-800 rounded-md hover:bg-green-200">Entregada</button>
                        <button @click="openDeliveryModal({{ $factura->id }}, 'Factura no entregada')" class="w-full text-sm py-2 px-3 bg-red-100 text-red-800 rounded-md hover:bg-red-200">No Entregada</button>
                    </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- ===================== MODAL ÚNICO ACTUALIZADO ===================== --}}
        <div x-show="isModalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
            <div @click.outside="isModalOpen = false" class="bg-white rounded-lg shadow-xl p-6 w-full max-w-sm">
                <h3 class="text-lg font-bold text-[#2c3856] mb-4" x-text="modalTitle"></h3>
                <form id="event-form" action="{{ route('operador.guia.event.store', ['guia' => $guia->guia]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    {{-- Inputs ocultos que guardan el estado del evento --}}
                    <input type="hidden" name="tipo" x-model="evento.tipo">
                    <input type="hidden" name="subtipo" x-model="evento.subtipo">
                    <input type="hidden" name="factura_id" x-model="evento.facturaId">
                    <input type="hidden" name="latitud" id="event-latitud-input">
                    <input type="hidden" name="longitud" id="event-longitud-input">
                    
                    <div class="space-y-4">
                        {{-- Este selector solo aparece para Notificaciones e Incidencias --}}
                        <div x-show="evento.tipo === 'Notificacion' || evento.tipo === 'Incidencias'" x-transition>
                            <label for="event_subtype_selector" class="block text-sm font-medium text-gray-700">Selecciona el Detalle</label>
                            <select name="subtipo_selector" id="event_subtype_selector" x-model="evento.subtipo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <template x-for="subtype in eventSubtypes[evento.tipo]" :key="subtype">
                                    <option :value="subtype" x-text="subtype"></option>
                                </template>
                            </select>
                        </div>
                        
                        {{-- Campo de Evidencia --}}
                        <div>
                            <label for="evidencia" class="block text-sm font-medium text-gray-700">Evidencia (Foto)</label>
                            <input type="file" name="evidencia[]" id="evidencia" required accept="image/*" multiple 
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#ff9c00]/20 file:text-[#ff9c00] hover:file:bg-[#ff9c00]/30">
                        </div>

                        {{-- Campo de Notas --}}
                        <div>
                            <label for="nota" class="block text-sm font-medium text-gray-700">Nota (Opcional)</label>
                            <textarea name="nota" id="nota" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                        </div>
                        <p x-show="locationError" x-text="locationError" class="text-red-600 text-sm"></p>
                    </div>

                    <div class="mt-6 flex justify-end gap-4">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md">Cancelar</button>
                        <button type="button" @click="submitEventForm()" :disabled="isLoading" class="px-4 py-2 bg-blue-600 text-white rounded-md disabled:opacity-50">
                             <span x-show="!isLoading">Confirmar</span>
                             <span x-show="isLoading">Enviando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        {{-- ================================================================= --}}
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('operatorView', () => ({
            isLoading: false,
            locationError: '',
            isModalOpen: false,
            modalTitle: '',
            evento: { tipo: '', subtipo: '', facturaId: null },
            
            // --- ESTRUCTURA DE EVENTOS ACTUALIZADA ---
            eventSubtypes: {
                'Entrega': ['Factura Entregada', 'Factura no entregada'],
                'Notificacion': ['Sanitario', 'Alimentos', 'Combustible', 'Pernocta', 'Llegada a carga', 'Fin de carga', 'En ruta', 'Llegada a cliente', 'Proceso de entrega'],
                'Incidencias': ['Rechazo', 'Percance', 'Cambios de datos de unidad', 'Datos Incorrectos', 'Datos Incompletos', 'Cambio de dirección de entrega', 'Capacidad de unidad errónea', 'Carga Tardía', 'Daños al cliente', 'Datos incompletos en planeación', 'Desvío de ruta', 'Entrega en dirección errónea', 'Extravío del producto', 'Falla mecánica', 'Falta de maniobristas', 'Falta de evidencia', 'Incidencia con transito', 'Ingreso de unidades a resguardo', 'Llegada tardía de custodia', 'Mercancía robada', 'No comparten datos de unidad', 'No cuenta con herramientas de embarque', 'No cuenta con herramientas de entrega', 'No cumple con capacidad requerida', 'No cumple con solicitud de unidad', 'No envía estatus', 'No envía evidencias de entrega', 'No llega a tiempo a embarque', 'No llega a tiempo de entrega', 'No lleva gastos', 'No lleva combustible', 'No presenta checklist', 'No regresa producto', 'No reporta incidencias en tiempo', 'No respeta especificaciones del cliente', 'No respeta instrucciones de custodia', 'No valido carga', 'No valido documentos de entrega', 'Salió sin custodia', 'Solicitud de unidades sin antelación', 'Transporte accidentado']
            },
            
            // Lógica para el botón Iniciar Ruta (sin cambios)
            startRoute() {
                this.isLoading = true;
                this.locationError = '';
                if (!navigator.geolocation) { this.locationError = 'Geolocalización no soportada.'; this.isLoading = false; return; }
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        document.getElementById('latitud-input').value = position.coords.latitude;
                        document.getElementById('longitud-input').value = position.coords.longitude;
                        document.getElementById('start-route-form').submit();
                    },
                    () => { this.locationError = 'Activa el GPS y otorga los permisos.'; this.isLoading = false; }
                );
            },

            // --- LÓGICA DE MODALES ACTUALIZADA ---
            openDeliveryModal(facturaId, subtipo) {
                this.modalTitle = subtipo;
                this.evento.tipo = 'Entrega';
                this.evento.subtipo = subtipo;
                this.evento.facturaId = facturaId;
                this.isModalOpen = true;
            },
            openNotificationModal() {
                this.modalTitle = 'Notificar Evento';
                this.evento.tipo = 'Notificacion';
                this.evento.subtipo = this.eventSubtypes['Notificacion'][0]; // Selecciona el primer subtipo por defecto
                this.evento.facturaId = null;
                this.isModalOpen = true;
            },
            // NUEVA FUNCIÓN PARA ABRIR EL MODAL DE INCIDENCIAS
            openIncidenceModal() {
                this.modalTitle = 'Reportar Incidencia';
                this.evento.tipo = 'Incidencias';
                this.evento.subtipo = this.eventSubtypes['Incidencias'][0]; // Selecciona el primer subtipo por defecto
                this.evento.facturaId = null;
                this.isModalOpen = true;
            },
            submitEventForm() {
                this.isLoading = true;
                this.locationError = '';
                // Asignamos el valor del selector al input oculto antes de enviar
                // Esto es por si el usuario no cambia el valor por defecto
                const selector = document.getElementById('event_subtype_selector');
                if (selector) {
                    this.evento.subtipo = selector.value;
                }

                if (!navigator.geolocation) { this.locationError = 'Geolocalización no soportada.'; this.isLoading = false; return; }
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        document.getElementById('event-latitud-input').value = position.coords.latitude;
                        document.getElementById('event-longitud-input').value = position.coords.longitude;
                        document.getElementById('event-form').submit();
                    },
                    () => { this.locationError = 'Activa el GPS y otorga los permisos.'; this.isLoading = false; }
                );
            }
        }));
    });
</script>
@endpush