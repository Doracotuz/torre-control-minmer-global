<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Asignación de Guías a Rutas') }}</h2>
    </x-slot>

    {{-- Inicializamos Alpine.js con el manejador para ambos modales --}}
    <div class="py-12" x-data="assignmentManager()">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            
            {{-- Botones y Filtros (código completo) --}}
            <div class="flex justify-between items-center mb-6">
                <a href="{{ route('rutas.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600">
                    &larr; Volver al Dashboard
                </a>
                <div class="flex items-center gap-4">
                    <button @click="isAssignModalOpen = true" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                        Cargar CSV
                    </button>
                    <a href="{{ route('rutas.asignaciones.create') }}" class="inline-flex items-center px-4 py-2 bg-[#ff9c00] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-600">
                        Añadir Guía
                    </a>
                </div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow-md mb-6">
                 <form action="{{ route('rutas.asignaciones.index') }}" method="GET">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <input type="text" name="search" placeholder="Buscar por Guía, Operador, Placas..." value="{{ request('search') }}" class="rounded-md border-gray-300 shadow-sm">
                        <select name="estatus" class="rounded-md border-gray-300 shadow-sm">
                            <option value="">Todos los Estatus</option>
                            <option value="En Espera" {{ request('estatus') == 'En Espera' ? 'selected' : '' }}>En Espera</option>
                            <option value="Planeada" {{ request('estatus') == 'Planeada' ? 'selected' : '' }}>Planeada</option>
                            <option value="En Transito" {{ request('estatus') == 'En Transito' ? 'selected' : '' }}>En Tránsito</option>
                            <option value="Completada" {{ request('estatus') == 'Completada' ? 'selected' : '' }}>Completada</option>
                        </select>
                        <div>
                            <button type="submit" class="w-full justify-center inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-[#2c3856] hover:bg-[#1a2b41]">Filtrar</button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- TABLA PRINCIPAL (CÓDIGO COMPLETO) --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Guía</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Facturas</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Operador</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ruta Asignada</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estatus</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($guias as $guia)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $guia->guia }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $guia->facturas_count }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $guia->operador }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $guia->ruta->nombre ?? 'Sin Asignar' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full @if($guia->estatus == 'En Espera') bg-yellow-100 text-yellow-800 @elseif($guia->estatus == 'Planeada') bg-blue-100 text-blue-800 @elseif($guia->estatus == 'En Transito') bg-purple-100 text-purple-800 @elseif($guia->estatus == 'Completada') bg-green-100 text-green-800 @endif">
                                            {{ $guia->estatus }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-4">
                                        <button @click="openDetailsModal({{ $guia }})" class="text-gray-500 hover:text-gray-800">Ver Detalles</button>
                                        <button @click="openAssignModal({{ $guia }})" class="text-indigo-600 hover:text-indigo-900" 
                                            :disabled="{{ $guia->estatus !== 'En Espera' ? 'true' : 'false' }}"
                                            :class="{ 'opacity-50 cursor-not-allowed': {{ $guia->estatus !== 'En Espera' ? 'true' : 'false' }} }">
                                            Asignar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No se encontraron guías.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Paginación --}}
            <div class="mt-6">{{ $guias->appends(request()->query())->links() }}</div>

            <div x-show="isAssignModalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @keydown.escape.window="closeAllModals()">
                <div @click.outside="closeAllModals()" class="bg-white rounded-lg shadow-xl p-8 w-full max-w-2xl max-h-[90vh] flex flex-col">
                    <h3 class="text-xl font-bold text-[#2c3856] mb-4">Asignar Ruta a la Guía <span x-text="selectedGuia?.guia" class="text-[#ff9c00]"></span></h3>
                    <input type="text" x-model="searchTerm" @input.debounce.300ms="searchRoutes()" placeholder="Buscar plantilla de ruta por nombre..." class="w-full rounded-md border-gray-300 shadow-sm mb-4">
                    <div class="flex-grow overflow-y-auto border rounded-md">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Región</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Distancia</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-if="isLoading"><tr><td colspan="4" class="p-4 text-center text-gray-500">Buscando...</td></tr></template>
                                <template x-if="!isLoading && availableRoutes.length === 0"><tr><td colspan="4" class="p-4 text-center text-gray-500">No se encontraron rutas.</td></tr></template>
                                <template x-for="ruta in availableRoutes" :key="ruta.id">
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900" x-text="ruta.nombre"></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500" x-text="ruta.region"></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500" x-text="ruta.distancia_total_km + ' km'"></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                            <form :action="`/rutas/asignaciones/${selectedGuia.id}/assign`" method="POST">
                                                @csrf
                                                <input type="hidden" name="ruta_id" :value="ruta.id">
                                                <button type="submit" class="px-3 py-1 bg-green-600 text-white text-xs font-semibold rounded-md hover:bg-green-700">Seleccionar</button>
                                            </form>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div x-show="isDetailsModalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @keydown.escape.window="closeAllModals()">
                <div @click.outside="closeAllModals()" class="bg-white rounded-lg shadow-xl p-8 w-full max-w-4xl max-h-[90vh] flex flex-col">
                    <div class="flex justify-between items-center border-b pb-4 mb-4">
                         <h3 class="text-xl font-bold text-[#2c3856]">Detalles de la Guía <span x-text="selectedGuia?.guia" class="text-[#ff9c00]"></span></h3>
                         <button @click="closeAllModals()" class="text-gray-500 hover:text-gray-800">&times;</button>
                    </div>
                   
                    <div class="flex-grow overflow-y-auto">
                        <div x-show="selectedGuia">
                            {{-- Info General --}}
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 text-sm">
                                <div><strong class="block text-gray-500">Operador:</strong> <span x-text="selectedGuia.operador"></span></div>
                                <div><strong class="block text-gray-500">Placas:</strong> <span x-text="selectedGuia.placas"></span></div>
                                <div><strong class="block text-gray-500">Ruta Asignada:</strong> <span x-text="selectedGuia.ruta?.nombre || 'Sin Asignar'"></span></div>
                                <div><strong class="block text-gray-500">Estatus:</strong> <span x-text="selectedGuia.estatus"></span></div>
                            </div>

                            {{-- Tabla de Facturas --}}
                            <h4 class="text-md font-semibold text-gray-700 mb-2">Facturas Incluidas</h4>
                            <table class="min-w-full divide-y divide-gray-200 border">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase"># Factura</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Destino</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cajas</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Botellas</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <template x-for="factura in selectedGuia.facturas" :key="factura.id">
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-800" x-text="factura.numero_factura"></td>
                                            <td class="px-4 py-3 text-sm text-gray-600" x-text="factura.destino"></td>
                                            <td class="px-4 py-3 text-sm text-gray-600" x-text="factura.cajas"></td>
                                            <td class="px-4 py-3 text-sm text-gray-600" x-text="factura.botellas"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- SCRIPT MANEJADOR DE ALPINE.JS --}}
    <script>
        function assignmentManager() {
            return {
                isAssignModalOpen: false,
                isDetailsModalOpen: false,
                selectedGuia: null,
                availableRoutes: [],
                searchTerm: '',
                isLoading: false,
                openAssignModal(guia) {
                    this.selectedGuia = guia;
                    this.isAssignModalOpen = true;
                    this.searchRoutes();
                },
                openDetailsModal(guia) {
                    this.selectedGuia = guia;
                    this.isDetailsModalOpen = true;
                },
                closeAllModals() {
                    this.isAssignModalOpen = false;
                    this.isDetailsModalOpen = false;
                    this.selectedGuia = null;
                    this.searchTerm = '';
                    this.availableRoutes = [];
                },
                searchRoutes() {
                    this.isLoading = true;
                    // Asegúrate de que el nombre de la ruta es correcto
                    fetch(`{{ route('rutas.plantillas.search') }}?search=${this.searchTerm}`)
                        .then(response => response.json())
                        .then(data => {
                            this.availableRoutes = data;
                            this.isLoading = false;
                        });
                }
            }
        }
    </script>
</x-app-layout>