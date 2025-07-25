<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Monitoreo de Rutas Activas') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="monitoringManager">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 xl:grid-cols-4 gap-8">

                {{-- Columna Izquierda: Filtros y Lista de Guías --}}
                <div class="lg:col-span-1 xl:col-span-1 space-y-6">
                    <div class="flex justify-between items-center">
                        <a href="{{ route('rutas.dashboard') }}" class="inline-flex items-center text-sm font-semibold text-gray-600 hover:text-gray-800">
                            &larr; Volver al Dashboard
                        </a>
                        <button @click="openEventModal()" :disabled="selectedGuias.length !== 1" class="px-4 py-2 bg-[#ff9c00] text-white rounded-md text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
                            Agregar Evento
                        </button>
                    </div>
                    
                    {{-- Panel de Filtros Dinámicos --}}
                    <div class="bg-white p-4 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold text-[#2c3856] mb-4">Filtros</h3>
                        <div class="space-y-4">
                            <div>
                                <label for="search" class="text-sm font-medium text-gray-700">Búsqueda</label>
                                <input type="text" id="search" placeholder="Guía, Operador, Ruta..." x-model.debounce.300ms="filters.search" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>
                            <div>
                                <label for="estatus" class="text-sm font-medium text-gray-700">Estatus</label>
                                <select id="estatus" x-model="filters.estatus" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Todos</option>
                                    <option value="En Transito">En Tránsito (Activas)</option>
                                    <option value="Planeada">Planeadas</option>
                                    <option value="Completada">Completadas</option>
                                </select>
                            </div>
                            <div>
                                <label for="start_date" class="text-sm font-medium text-gray-700">Desde</label>
                                <input type="date" id="start_date" x-model="filters.start_date" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>
                            <div>
                                <label for="end_date" class="text-sm font-medium text-gray-700">Hasta</label>
                                <input type="date" id="end_date" x-model="filters.end_date" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>
                            <div class="pt-2">
                                <button type="button" @click="deselectAll()" class="w-full justify-center inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50">
                                    Deseleccionar Todas las Rutas
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Lista de Guías Dinámica --}}
                    <div class="bg-white rounded-lg shadow-md max-h-[60vh] overflow-y-auto">
                        <ul class="divide-y divide-gray-200">
                            <template x-if="isLoading">
                                <li class="p-4 text-center text-sm text-gray-500">Cargando guías...</li>
                            </template>
                            <template x-if="!isLoading && guias.length === 0">
                                <li class="p-4 text-center text-sm text-gray-500">No se encontraron guías con los filtros aplicados.</li>
                            </template>
                            <template x-for="guia in guias" :key="guia.id">
                                <li class="p-4 hover:bg-gray-50 flex justify-between items-center">
                                    <label class="flex items-center space-x-3 cursor-pointer">
                                        <input type="checkbox" 
                                               class="h-5 w-5 rounded border-gray-300 text-[#ff9c00] focus:ring-[#ff9c00]"
                                               @change="updateSelection($el, guia.id)"
                                               :checked="selectedGuias.includes(String(guia.id))">
                                        <div>
                                            <p class="text-sm font-bold text-gray-900"><span x-text="guia.guia"></span> <span class="font-normal text-gray-600">| </span><span x-text="guia.operador"></span></p>
                                            <p class="text-xs text-gray-500" x-text="guia.ruta ? guia.ruta.nombre : 'Ruta no definida'"></p>
                                        </div>
                                    </label>
                                    <button @click="openDetailsModal(guia.id)" class="text-sm text-blue-600 hover:underline flex-shrink-0 ml-4">
                                        Detalles
                                    </button>
                                </li>
                            </template>
                        </ul>
                    </div>
                        <div x-show="!isLoading && pagination.total > 0" class="p-4 border-t flex items-center justify-between text-sm text-gray-600">
                            <p>Mostrando <span x-text="pagination.from"></span> a <span x-text="pagination.to"></span> de <span x-text="pagination.total"></span> resultados</p>
                            <div class="flex gap-2">
                                <button @click="changePage(pagination.prev_page_url)" :disabled="!pagination.prev_page_url" class="px-3 py-1 border rounded-md disabled:opacity-50">Anterior</button>
                                <button @click="changePage(pagination.next_page_url)" :disabled="!pagination.next_page_url" class="px-3 py-1 border rounded-md disabled:opacity-50">Siguiente</button>
                            </div>
                        </div>                    
                </div>

                {{-- Columna Derecha: Mapa --}}
                <div class="lg:col-span-2 xl:col-span-3">
                    <div id="monitoreo-map" class="w-full h-[85vh] rounded-lg shadow-md bg-gray-200 sticky top-8"></div>
                </div>
            </div>

            {{-- Modal para Agregar Evento --}}
            <div x-show="isEventModalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 p-4" style="display: none;">
                <div @click.outside="closeAllModals()" class="bg-white rounded-lg shadow-xl p-8 w-full max-w-lg">
                    <h3 class="text-xl font-bold text-[#2c3856] mb-6 border-b pb-3">Registrar Nuevo Evento</h3>
                    <template x-if="selectedGuia">
                        <form :action="'/rutas/monitoreo/' + selectedGuia.id + '/events'" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="latitud" x-model="evento.lat">
                            <input type="hidden" name="longitud" x-model="evento.lng">
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label for="event_type" class="block text-sm font-medium text-gray-700">Categoría</label>
                                    <select name="tipo" id="event_type" x-model="evento.tipo" @change="evento.subtipo = eventSubtypes[evento.tipo] ? eventSubtypes[evento.tipo][0] : ''" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="Notificacion">Notificación</option>
                                        <option value="Incidencias">Incidencias</option>
                                        <option value="Entrega">Entrega</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="event_subtype" class="block text-sm font-medium text-gray-700">Detalle del Evento</label>
                                    <select name="subtipo" id="event_subtype" x-model="evento.subtipo" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <template x-for="subtype in eventSubtypes[evento.tipo]" :key="subtype">
                                            <option :value="subtype" x-text="subtype"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="col-span-2" x-show="evento.tipo === 'Entrega'" x-transition>
                                    <label for="factura_id" class="block text-sm font-medium text-gray-700">Factura Afectada</label>
                                    <select name="factura_id" id="factura_id" :required="evento.tipo === 'Entrega'" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" :disabled="!getSelectedGuiaFacturas() || getSelectedGuiaFacturas().length === 0">
                                        <template x-if="!getSelectedGuiaFacturas() || getSelectedGuiaFacturas().length === 0">
                                            <option>No hay facturas pendientes</option>
                                        </template>
                                        <template x-for="factura in getSelectedGuiaFacturas()" :key="factura.id">
                                            <option :value="factura.id" x-text="factura.numero_factura"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="col-span-2 text-xs text-gray-500 -mt-2">
                                    <p>Haz clic derecho en el mapa para obtener la ubicación o ingrésala manualmente.</p>
                                </div>
                                <div>
                                    <label for="latitud" class="block text-sm font-medium text-gray-700">Latitud</label>
                                    <input type="text" name="latitud" x-model="evento.lat" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label for="longitud" class="block text-sm font-medium text-gray-700">Longitud</label>
                                    <input type="text" name="longitud" x-model="evento.lng" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div class="col-span-2">
                                    <label for="nota" class="block text-sm font-medium text-gray-700">Nota (Opcional)</label>
                                    <textarea name="nota" rows="2" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Evidencia</label>
                                    <input type="file" name="evidencia[]" multiple class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#ff9c00]/20 file:text-[#ff9c00] hover:file:bg-[#ff9c00]/30">
                                </div>
                            </div>
                            <div class="mt-8 flex justify-end gap-4">
                                <button type="button" @click="closeAllModals()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 font-medium">Cancelar</button>
                                <button type="submit" class="px-4 py-2 bg-[#2c3856] text-white rounded-md hover:bg-[#1a2b41] font-medium">Guardar Evento</button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>

            {{-- Modal para Ver Detalles --}}
            <div x-show="isDetailsModalOpen" @keydown.escape.window="closeAllModals()" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
                <div @click.outside="closeAllModals()" class="bg-white rounded-lg shadow-xl p-6 w-full max-w-4xl max-h-[90vh] flex flex-col">
                    <div class="flex justify-between items-center border-b pb-3 mb-4">
                        <h3 class="text-2xl font-bold text-[#2c3856]">Detalles de la Guía <span x-text="selectedGuia?.guia" class="text-[#ff9c00]"></span></h3>
                        <button @click="closeAllModals()" class="text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
                    </div>
                    <div class="flex-grow overflow-y-auto" x-show="selectedGuia">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6 bg-gray-50 p-4 rounded-lg">
                            <div><strong class="block text-gray-500 text-sm">Operador:</strong> <span x-text="selectedGuia?.operador"></span></div>
                            <div><strong class="block text-gray-500 text-sm">Placas:</strong> <span x-text="selectedGuia?.placas"></span></div>
                            <div><strong class="block text-gray-500 text-sm">Ruta Asignada:</strong> <span x-text="selectedGuia?.ruta_nombre"></span></div>
                            <div><strong class="block text-gray-500 text-sm">Estatus:</strong> <span x-text="selectedGuia?.estatus"></span></div>
                            <div><strong class="block text-gray-500 text-sm">Inicio de Ruta:</strong> <span x-text="selectedGuia?.fecha_inicio_ruta || 'N/A'"></span></div>
                            <div><strong class="block text-gray-500 text-sm">Fin de Ruta:</strong> <span x-text="selectedGuia?.fecha_fin_ruta || 'N/A'"></span></div>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-800 mb-2">Facturas Incluidas</h4>
                                <div class="border rounded-lg overflow-hidden text-sm">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50"><tr class="text-left text-xs font-medium text-gray-500 uppercase"><th class="px-4 py-2"># Factura</th><th class="px-4 py-2">Destino</th><th class="px-4 py-2">Estatus</th></tr></thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <template x-for="factura in selectedGuia?.facturas || []" :key="factura.id">
                                                <tr><td class="px-4 py-2" x-text="factura.numero_factura"></td><td class="px-4 py-2" x-text="factura.destino"></td><td class="px-4 py-2" x-text="factura.estatus_entrega"></td></tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-gray-800 mb-2">Eventos Registrados</h4>
                                <div class="border rounded-lg overflow-hidden text-sm">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50"><tr class="text-left text-xs font-medium text-gray-500 uppercase"><th class="px-4 py-2">Evento</th><th class="px-4 py-2">Fecha y Hora</th><th class="px-4 py-2">Nota</th></tr></thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <template x-for="evento in selectedGuia?.eventos || []" :key="evento.fecha_evento">
                                                <tr><td class="px-4 py-2" x-text="evento.subtipo"></td><td class="px-4 py-2" x-text="evento.fecha_evento"></td><td class="px-4 py-2" x-text="evento.nota"></td></tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Pasamos los valores iniciales de los filtros desde PHP a JS
        const initialFilters = {
            search: "{{ request('search', '') }}",
            estatus: "{{ request('estatus', 'En Transito') }}",
            start_date: "{{ request('start_date', '') }}",
            end_date: "{{ request('end_date', '') }}"
        };
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places,drawing&callback=initMonitoreoMap" async defer></script>
    
    {{-- El script de Alpine.js se mueve aquí para asegurar su correcta ejecución --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('monitoringManager', () => ({
                isLoading: true,
                selectedGuias: JSON.parse(sessionStorage.getItem('selectedGuias')) || [],
                isEventModalOpen: false,
                isDetailsModalOpen: false,
                selectedGuia: null,
                evento: { tipo: 'Notificacion', subtipo: '', lat: '', lng: '' },
                eventSubtypes: {
                    'Entrega': ['Factura Entregada', 'Factura no entregada'],
                    'Notificacion': ['Sanitario', 'Alimentos', 'Combustible', 'Pernocta', 'Llegada a carga', 'Fin de carga', 'En ruta', 'Llegada a cliente', 'Proceso de entrega'],
                    'Incidencias': ['Rechazo', 'Percance', 'Cambios de datos de unidad', 'Datos Incorrectos', 'Datos Incompletos', 'Cambio de dirección de entrega', 'Capacidad de unidad errónea', 'Carga Tardía', 'Daños al cliente', 'Datos incompletos en planeación', 'Desvío de ruta', 'Entrega en dirección errónea', 'Extravío del producto', 'Falla mecánica', 'Falta de maniobristas', 'Falta de evidencia', 'Incidencia con transito', 'Ingreso de unidades a resguardo', 'Llegada tardía de custodia', 'Mercancía robada', 'No comparten datos de unidad', 'No cuenta con herramientas de embarque', 'No cuenta con herramientas de entrega', 'No cumple con capacidad requerida', 'No cumple con solicitud de unidad', 'No envía estatus', 'No envía evidencias de entrega', 'No llega a tiempo a embarque', 'No llega a tiempo de entrega', 'No lleva gastos', 'No lleva combustible', 'No presenta checklist', 'No regresa producto', 'No reporta incidencias en tiempo', 'No respeta especificaciones del cliente', 'No respeta instrucciones de custodia', 'No valido carga', 'No valido documentos de entrega', 'Salió sin custodia', 'Solicitud de unidades sin antelación', 'Transporte accidentado']
                },
                guias: [],
                pagination: {},
                filters: initialFilters,
                debounce: null,

                init() {
                    this.applyFilters();
                    
                    // CORRECCIÓN: Se añade el listener para el clic derecho en el mapa
                    if (typeof monitoreoMap !== 'undefined' && monitoreoMap) {
                        monitoreoMap.addListener('rightclick', (event) => {
                            this.handleMapRightClick(event.latLng);
                        });
                    } else {
                        // Si el mapa no está listo, esperamos un poco y lo intentamos de nuevo.
                        setTimeout(() => {
                            if (typeof monitoreoMap !== 'undefined' && monitoreoMap) {
                                monitoreoMap.addListener('rightclick', (event) => {
                                    this.handleMapRightClick(event.latLng);
                                });
                            }
                        }, 500);
                    }

                    this.$watch('filters', (newValue, oldValue) => {
                        // Si cambia cualquier filtro que no sea la página, reseteamos a la página 1
                        if (newValue.search !== oldValue.search || newValue.estatus !== oldValue.estatus || newValue.start_date !== oldValue.start_date || newValue.end_date !== oldValue.end_date) {
                            this.filters.page = 1;
                        }
                        clearTimeout(this.debounce);
                        this.debounce = setTimeout(() => this.applyFilters(), 300);
                    });
                    this.$watch('selectedGuias', (newSelection) => {
                        sessionStorage.setItem('selectedGuias', JSON.stringify(newSelection));
                        this.redrawMap();
                    });
                },

                applyFilters() {
                    this.isLoading = true;
                    const params = new URLSearchParams(this.filters).toString();
                    fetch(`{{ route('rutas.monitoreo.filter') }}?${params}`)
                        .then(response => response.json())
                        .then(data => {
                            this.guias = data.paginator.data;
                            this.pagination = data.paginator;
                            window.guiasData = data.guiasJson;
                            this.isLoading = false;
                            this.redrawMap();
                        });
                },

                changePage(url) {
                    if (!url) return;
                    // Extraemos el número de página de la URL que nos da Laravel
                    const pageNumber = new URL(url).searchParams.get('page');
                    this.filters.page = pageNumber;
                    // applyFilters se llamará automáticamente gracias al $watch
                },                
                
                redrawMap() {
                    Object.keys(activeRenderers).forEach(id => removeMonitoreoRoute(id));
                    this.selectedGuias.forEach(id => {
                        if (window.guiasData[id]) {
                            drawMonitoreoRoute(id);
                        }
                    });
                },

                updateSelection(checkbox, guiaId) {
                    guiaId = String(guiaId);
                    if (checkbox.checked) {
                        this.selectedGuias = [...new Set([...this.selectedGuias, guiaId])];
                    } else {
                        this.selectedGuias = this.selectedGuias.filter(id => id !== guiaId);
                    }
                },
                
                deselectAll() { this.selectedGuias = []; },
                
                // CORRECCIÓN: Nueva función para manejar el clic derecho
                handleMapRightClick(latLng) {
                    if(this.selectedGuias.length !== 1) {
                        alert("Por favor, selecciona solo una guía para añadir un evento desde el mapa.");
                        return;
                    }
                    this.evento.lat = latLng.lat().toFixed(6);
                    this.evento.lng = latLng.lng().toFixed(6);
                    this.openEventModal(); // Reutilizamos la función de abrir modal
                },

                openEventModal() {
                    if (this.selectedGuias.length !== 1) { 
                        // Solo mostramos alerta si no fue activado por el mapa
                        if(!this.evento.lat) {
                           alert("Por favor, selecciona solo una guía para registrar un evento.");
                        }
                        return; 
                    }
                    const guiaId = this.selectedGuias[0];
                    this.selectedGuia = window.guiasData[guiaId];
                    // Si no hay un subtipo ya definido, asignamos el primero de la lista
                    if(!this.evento.subtipo) {
                        this.evento.subtipo = this.eventSubtypes[this.evento.tipo][0];
                    }
                    this.isEventModalOpen = true;
                },

                openDetailsModal(guiaId) {
                    this.selectedGuia = window.guiasData[guiaId];
                    this.isDetailsModalOpen = true;
                },

                closeAllModals() {
                    this.isEventModalOpen = false;
                    this.isDetailsModalOpen = false;
                    // Limpiamos las coordenadas al cerrar el modal
                    this.evento.lat = '';
                    this.evento.lng = '';
                },

                getSelectedGuiaFacturas() {
                    if (!this.selectedGuia) return [];
                    return this.selectedGuia.facturas.filter(f => f.estatus_entrega === 'Pendiente');
                }
            }));
        });
    </script>
</x-app-layout>
