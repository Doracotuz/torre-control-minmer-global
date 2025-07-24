<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Monitoreo de Rutas Activas') }}
        </h2>
    </x-slot>

    {{-- Inicializamos el componente Alpine.js 'monitoringManager' definido en el script global --}}
    <div class="py-12" x-data="monitoringManager">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 xl:grid-cols-4 gap-8">

                <div class="lg:col-span-1 xl:col-span-1 space-y-6">
                    <div class="flex justify-between items-center">
                        <a href="{{ route('rutas.dashboard') }}" class="inline-flex items-center text-sm font-semibold text-gray-600 hover:text-gray-800">
                            &larr; Volver al Dashboard
                        </a>
                        <button @click="openEventModal()" :disabled="selectedGuias.length !== 1" class="px-4 py-2 bg-[#ff9c00] text-white rounded-md text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
                            Agregar Evento
                        </button>
                    </div>
                    
                    {{-- Filtros de Búsqueda --}}
                    <div class="bg-white p-4 rounded-lg shadow-md">
                        <form action="{{ route('rutas.monitoreo.index') }}" method="GET">
                            <h3 class="text-lg font-semibold text-[#2c3856] mb-4">Filtros</h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="search" class="text-sm font-medium text-gray-700">Búsqueda</label>
                                    <input type="text" name="search" id="search" placeholder="Guía, Operador, Ruta..." value="{{ request('search') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                <div>
                                    <label for="estatus" class="text-sm font-medium text-gray-700">Estatus</label>
                                    <select name="estatus" id="estatus" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        <option value="">Todos</option>
                                        <option value="En Transito" {{ request('estatus', 'En Transito') == 'En Transito' ? 'selected' : '' }}>En Tránsito (Activas)</option>
                                        <option value="Planeada" {{ request('estatus') == 'Planeada' ? 'selected' : '' }}>Planeadas</option>
                                        <option value="Completada" {{ request('estatus') == 'Completada' ? 'selected' : '' }}>Completadas</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="start_date" class="text-sm font-medium text-gray-700">Desde</label>
                                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                <div>
                                    <label for="end_date" class="text-sm font-medium text-gray-700">Hasta</label>
                                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <button type="button" @click="deselectAll()" class="w-full justify-center inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50">
                                        Deseleccionar Todo
                                    </button>
                                    <button type="submit" class="w-full justify-center inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-[#2c3856] hover:bg-[#1a2b41]">Aplicar Filtros</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div id="flash-success" class="fixed top-5 right-5 z-50 bg-white border-l-4 border-[#ff9c00] text-[#2c3856] px-6 py-4 rounded-lg shadow-xl" role="alert" style="display: none;">
                        <p><strong class="font-bold mr-1">¡Éxito!</strong> <span id="flash-success-message"></span></p>
                    </div>

                    {{-- Lista de Guías --}}
                    <div class="bg-white rounded-lg shadow-md max-h-[60vh] overflow-y-auto">
                        <ul class="divide-y divide-gray-200">
                            @forelse ($guias as $guia)
                                <li class="p-4 hover:bg-gray-50 flex justify-between items-center">
                                    <label class="flex items-center space-x-3">
                                        <input type="checkbox" 
                                               class="h-5 w-5 rounded border-gray-300 text-[#ff9c00] focus:ring-[#ff9c00]"
                                               @change="updateSelection($el, {{ $guia->id }})"
                                               :checked="selectedGuias.includes('{{ $guia->id }}')">
                                        <div>
                                            <p class="text-sm font-bold text-gray-900">{{ $guia->guia }} <span class="font-normal text-gray-600">| {{ $guia->operador }}</span></p>
                                            <p class="text-xs text-gray-500">{{ $guia->ruta->nombre ?? 'Ruta no definida' }}</p>
                                        </div>
                                    </label>
                                    <button @click="openDetailsModal({{ $guia->id }})" class="text-sm text-blue-600 hover:underline flex-shrink-0 ml-4">
                                        Detalles
                                    </button>
                                </li>
                            @empty
                                <li class="p-4 text-center text-sm text-gray-500">No se encontraron guías con los filtros seleccionados.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <div class="lg:col-span-2 xl:col-span-3">
                    <div id="monitoreo-map" class="w-full h-[85vh] rounded-lg shadow-md bg-gray-200 sticky top-8"></div>
                </div>
            </div>

            <div x-show="isEventModalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 p-4" style="display: none;">
                <div @click.outside="closeAllModals()" class="bg-white rounded-lg shadow-xl p-8 w-full max-w-lg">
                    <h3 class="text-xl font-bold text-[#2c3856] mb-6 border-b pb-3">Registrar Nuevo Evento</h3>
                    
                    <form x-show="selectedGuia" :action="'/rutas/monitoreo/' + selectedGuia.id + '/events'" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-2 gap-6">

                            <div>
                                <label for="event_type" class="block text-sm font-medium text-gray-700">Categoría</label>
                                <select name="tipo" id="event_type" x-model="evento.tipo" 
                                        @change="evento.subtipo = eventSubtypes[evento.tipo] ? eventSubtypes[evento.tipo][0] : ''" 
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="Notificacion">Notificación</option>
                                    <option value="Incidencias">Incidencias</option>
                                    <option value="Entrega">Entrega</option>
                                </select>
                            </div>

                            <div>
                                <label for="event_subtype" class="block text-sm font-medium text-gray-700">Detalle del Evento</label>
                                <select name="subtipo" id="event_subtype" x-model="evento.subtipo" 
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <template x-for="subtype in eventSubtypes[evento.tipo]" :key="subtype">
                                        <option :value="subtype" x-text="subtype"></option>
                                    </template>
                                </select>
                            </div>

                            <div class="col-span-2" x-show="evento.tipo === 'Entrega'" x-transition>
                                <label for="factura_id" class="block text-sm font-medium text-gray-700">Factura Afectada</label>
                                <select name="factura_id" id="factura_id" :required="evento.tipo === 'Entrega'" 
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                        :disabled="!getSelectedGuiaFacturas() || getSelectedGuiaFacturas().length === 0">
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
                </div>
            </div>

            <div x-show="isDetailsModalOpen" @keydown.escape.window="closeAllModals()" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div @click.outside="closeAllModals()" class="bg-white rounded-lg shadow-xl p-6 w-full max-w-4xl max-h-[90vh] flex flex-col">
                    <div class="flex justify-between items-center border-b pb-3 mb-4">
                         <h3 class="text-2xl font-bold text-[#2c3856]">Detalles de la Guía <span x-text="selectedGuia?.guia" class="text-[#ff9c00]"></span></h3>
                         <button @click="closeAllModals()" class="text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
                    </div>
                    <div class="flex-grow overflow-y-auto" x-show="selectedGuia">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6 bg-gray-50 p-4 rounded-lg">
                            <div><strong class="block text-gray-500 text-sm">Operador:</strong> <span x-text="selectedGuia.operador"></span></div>
                            <div><strong class="block text-gray-500 text-sm">Placas:</strong> <span x-text="selectedGuia.placas"></span></div>
                            <div><strong class="block text-gray-500 text-sm">Ruta Asignada:</strong> <span x-text="selectedGuia.ruta_nombre"></span></div>
                            <div><strong class="block text-gray-500 text-sm">Estatus:</strong> <span x-text="selectedGuia.estatus"></span></div>
                            <div><strong class="block text-gray-500 text-sm">Inicio de Ruta:</strong> <span x-text="selectedGuia.fecha_inicio_ruta || 'N/A'"></span></div>
                            <div><strong class="block text-gray-500 text-sm">Fin de Ruta:</strong> <span x-text="selectedGuia.fecha_fin_ruta || 'N/A'"></span></div>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-800 mb-2">Facturas Incluidas</h4>
                                <div class="border rounded-lg overflow-hidden text-sm">
                                    <table class="min-w-full divide-y divide-gray-200"><thead class="bg-gray-50"><tr class="text-left text-xs font-medium text-gray-500 uppercase"><th class="px-4 py-2"># Factura</th><th class="px-4 py-2">Destino</th><th class="px-4 py-2">Estatus</th></tr></thead><tbody class="bg-white divide-y divide-gray-200"><template x-for="factura in selectedGuia.facturas" :key="factura.id"><tr><td class="px-4 py-2" x-text="factura.numero_factura"></td><td class="px-4 py-2" x-text="factura.destino"></td><td class="px-4 py-2" x-text="factura.estatus_entrega"></td></tr></template></tbody></table>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-gray-800 mb-2">Eventos Registrados</h4>
                                <div class="border rounded-lg overflow-hidden text-sm">
                                    <table class="min-w-full divide-y divide-gray-200"><thead class="bg-gray-50"><tr class="text-left text-xs font-medium text-gray-500 uppercase"><th class="px-4 py-2">Evento</th><th class="px-4 py-2">Fecha y Hora</th><th class="px-4 py-2">Nota</th></tr></thead><tbody class="bg-white divide-y divide-gray-200"><template x-for="evento in selectedGuia.eventos" :key="evento.fecha_evento"><tr><td class="px-4 py-2" x-text="evento.subtipo"></td><td class="px-4 py-2" x-text="evento.fecha_evento"></td><td class="px-4 py-2" x-text="evento.nota"></td></tr></template></tbody></table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        window.guiasData = {!! $guiasJson !!};
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places,drawing&callback=initMonitoreoMap" async defer></script>
</x-app-layout>