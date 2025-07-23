<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Monitoreo de Rutas Activas') }}
        </h2>
    </x-slot>

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
                                        <option value="En Transito" {{ request('estatus', 'En Transito') == 'En Transito' ? 'selected' : '' }}>En Tránsito (Activas)</option>
                                        <option value="Planeada" {{ request('estatus') == 'Planeada' ? 'selected' : '' }}>Planeadas</option>
                                        <option value="Completada" {{ request('estatus') == 'Completada' ? 'selected' : '' }}>Completadas</option>
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label for="start_date" class="text-sm font-medium text-gray-700">Desde</label>
                                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </div>
                                    <div>
                                        <label for="end_date" class="text-sm font-medium text-gray-700">Hasta</label>
                                        <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </div>
                                </div>
                                <div>
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
                                <li class="p-4 hover:bg-gray-50">
                                    <label class="flex items-center space-x-3">
                                        <input type="checkbox" 
                                            class="h-5 w-5 rounded border-gray-300 text-[#ff9c00] focus:ring-[#ff9c00] guia-checkbox" 
                                            :value="{{ $guia->id }}"
                                            @change="updateSelection($el, {{ $guia->id }})"
                                            :checked="selectedGuias.includes('{{ $guia->id }}')">
                                        <div>
                                            <p class="text-sm font-bold text-gray-900">{{ $guia->guia }} <span class="font-normal text-gray-600">| {{ $guia->operador }}</span></p>
                                            <p class="text-xs text-gray-500">{{ $guia->ruta->nombre ?? 'Ruta no definida' }}</p>
                                        </div>
                                    </label>
                                </li>
                            @empty
                                <li class="p-4 text-center text-sm text-gray-500">No se encontraron guías con los filtros seleccionados.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <div class="lg:col-span-2 xl:col-span-3">
                    <div id="monitoreo-map" @contextmenu.prevent="if(selectedGuias.length === 1) setEventLocationFromMapClick($event)" class="w-full h-[85vh] rounded-lg shadow-md bg-gray-200 sticky top-8"></div>
                </div>
            </div>

            <div x-show="isEventModalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div @click.outside="isEventModalOpen = false" class="bg-white rounded-lg shadow-xl p-8 w-full max-w-lg">
                    <h3 class="text-xl font-bold text-[#2c3856] mb-4">Registrar Nuevo Evento</h3>
                    <form :action="`/rutas/monitoreo/${selectedGuias[0]}/events`" method="POST" enctype="multipart/form-data" x-show="selectedGuias.length === 1">
                        @csrf
                        <div class="grid grid-cols-2 gap-4">
                            {{-- Tipo y Subtipo --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tipo de Evento</label>
                                <select name="tipo" x-model="evento.tipo" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="Entrega">Entrega</option>
                                    <option value="Notificacion">Notificación</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Evento</label>
                                {{-- CORRECCIÓN: Usamos x-for para generar las opciones dinámicamente --}}
                                <select name="subtipo" x-model="evento.subtipo" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                                    <template x-for="subtipo in eventSubtypes[evento.tipo]" :key="subtipo">
                                        <option :value="subtipo" x-text="subtipo"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Selector de Factura (si es de Entrega) --}}
                            <div class="col-span-2" x-show="evento.tipo === 'Entrega'">
                                <label class="block text-sm font-medium text-gray-700">Factura Afectada</label>
                                {{-- CORRECCIÓN: La lógica de la plantilla se queda igual, pero ahora recibirá datos --}}
                                <select name="factura_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" :disabled="getSelectedGuiaFacturas().length === 0">
                                    <template x-if="getSelectedGuiaFacturas().length === 0">
                                        <option>No hay facturas para esta guía.</option>
                                    </template>
                                    <template x-for="factura in getSelectedGuiaFacturas()" :key="factura.id">
                                        <option :value="factura.id" x-text="factura.numero_factura + ' - ' + factura.destino"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Coordenadas --}}
                            <div class="col-span-2 text-xs text-gray-500">Haz clic en el mapa para obtener la ubicación o ingrésala manualmente.</div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Latitud</label>
                                <input type="text" name="latitud" x-model="evento.lat" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Longitud</label>
                                <input type="text" name="longitud" x-model="evento.lng" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            
                            {{-- Nota y Evidencia --}}
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Nota (Opcional)</label>
                                <textarea name="nota" rows="2" class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></textarea>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Evidencia (Opcional)</label>
                                <input type="file" name="evidencia" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#ff9c00]/20 file:text-[#ff9c00] hover:file:bg-[#ff9c00]/30">
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-4">
                            <button type="button" @click="isEventModalOpen = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancelar</button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Guardar Evento</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.guiasData = {!! $guiasJson !!};
    </script>

    <script>
        document.addEventListener('turbo:load', function() {
            @if (session('success'))
                const el = document.getElementById('flash-success');
                document.getElementById('flash-success-message').innerText = '{{ session('success') }}';
                el.style.display = 'block';
                setTimeout(() => { el.style.display = 'none'; }, 5000);
            @endif
        });
    </script>

    <script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places,drawing&callback=initMonitoreoMap" async defer></script>
</x-app-layout>