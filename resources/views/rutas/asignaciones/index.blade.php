<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Asignación de Guías a Rutas') }}</h2>
    </x-slot>

    <div class="py-12" x-data="assignmentManager()">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            
            {{-- Botones y Filtros --}}
            <div class="bg-white p-4 rounded-lg shadow-md mb-6">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-4">
                        <a href="{{ route('rutas.dashboard') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-800">&larr; Volver</a>
                        <a href="{{ route('rutas.asignaciones.create') }}" class="px-4 py-2 bg-[#ff9c00] text-white rounded-md text-sm font-semibold">Añadir Manualmente</a>
                        <button @click="isImportModalOpen = true" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold">Cargar CSV</button>
                        <a href="{{ route('rutas.asignaciones.export', request()->query()) }}" class="px-4 py-2 bg-teal-600 text-white rounded-md text-sm font-semibold">Exportar Vista</a>
                    </div>
                    <form action="{{ route('rutas.asignaciones.index') }}" method="GET" class="flex items-center gap-2">
                        <input type="text" name="search" placeholder="Buscar..." value="{{ request('search') }}" class="rounded-md border-gray-300 shadow-sm text-sm">
                        <select name="origen" class="rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">Todos los Orígenes</option>
                            @foreach($origenes as $origen)
                                <option value="{{ $origen }}" @if(request('origen') == $origen) selected @endif>{{ $origen }}</option>
                            @endforeach
                        </select>                        <select name="estatus" class="rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">Todos los Estatus</option>
                            <option value="En Espera" @if(request('estatus') == 'En Espera') selected @endif>En Espera</option>
                            <option value="Planeada" @if(request('estatus') == 'Planeada') selected @endif>Planeada</option>
                            <option value="En Transito" @if(request('estatus') == 'En Transito') selected @endif>En Tránsito</option>
                            <option value="Completada" @if(request('estatus') == 'Completada') selected @endif>Completada</option>
                        </select>
                        <button type="submit" class="px-4 py-2 bg-[#2c3856] text-white rounded-md text-sm font-semibold">Filtrar</button>
                    </form>
                </div>
            </div>

            {{-- Tabla de Guías --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Guía</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Operador / Placas</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Origen</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha Asignación</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Custodia</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Hora Planeada</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ruta Asignada</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estatus</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($guias as $guia)
                            <tr>
                                <td class="px-4 py-3"><button @click="openDetailsModal({{ $guia }})" class="font-bold text-blue-600 hover:underline">{{ $guia->guia }}</button></td>
                                <td class="px-4 py-3">{{ $guia->operador }} <br> <span class="text-gray-500">{{ $guia->placas }}</span></td>
                                <td class="px-4 py-3">{{ $guia->origen }}</td>
                                <td class="px-4 py-3">{{ $guia->fecha_asignacion ? \Carbon\Carbon::parse($guia->fecha_asignacion)->format('d/m/Y') : 'N/A' }}</td>
                                <td class="px-4 py-3">{{ $guia->custodia ?? 'N/A' }}</td>
                                <td class="px-4 py-3">{{ $guia->hora_planeada ?? 'N/A' }}</td>
                                <td class="px-4 py-3">{{ $guia->ruta->nombre ?? 'Sin asignar' }}</td>
                                <td class="px-4 py-3"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ...">{{ $guia->estatus }}</span></td>
                                <td class="px-4 py-3 flex items-center gap-2">
                                    <button @click="openAssignModal({{ $guia }})" :disabled="{{ $guia->estatus !== 'En Espera' ? 'true' : 'false' }}" class="text-sm text-blue-600 hover:text-blue-800 disabled:opacity-50">Asignar</button>
                                    <button @click="openEditModal({{ $guia->id }})" class="text-sm text-gray-600 hover:text-gray-800">Editar</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-4 text-gray-500">No se encontraron guías.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $guias->appends(request()->query())->links() }}</div>
        </div>

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
            
            <div x-show="isDetailsModalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" @keydown.escape.window="closeAllModals()">
                <div @click.outside="closeAllModals()" class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col">
                    <template x-if="selectedGuia">
                        <div>
                            <div class="flex justify-between items-center border-b p-4">
                                <h3 class="text-xl font-bold text-[#2c3856]">Detalles de la Guía <span x-text="selectedGuia.guia" class="text-[#ff9c00]"></span></h3>
                                <button @click="closeAllModals()" class="text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
                            </div>
                            <div class="p-6 flex-grow overflow-y-auto">
                                {{-- Información General de la Guía --}}
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-4 mb-6 bg-gray-50 p-4 rounded-lg border text-sm">
                                    <div><strong class="block text-gray-500">Operador:</strong> <span x-text="selectedGuia.operador || 'N/A'"></span></div>
                                    <div><strong class="block text-gray-500">Placas:</strong> <span x-text="selectedGuia.placas || 'N/A'"></span></div>
                                    <div><strong class="block text-gray-500">Teléfono:</strong> <span x-text="selectedGuia.telefono || 'N/A'"></span></div>
                                    <div><strong class="block text-gray-500">Origen:</strong> <span x-text="selectedGuia.origen || 'N/A'"></span></div>
                                    <div><strong class="block text-gray-500">Fecha Asignación:</strong> <span x-text="formatDate(selectedGuia.fecha_asignacion)"></span></div>
                                    <div><strong class="block text-gray-500">Custodia:</strong> <span x-text="selectedGuia.custodia || 'N/A'"></span></div>
                                    <div><strong class="block text-gray-500">Hora Planeada:</strong> <span x-text="selectedGuia.hora_planeada || 'N/A'"></span></div>
                                    <div><strong class="block text-gray-500">Pedimento:</strong> <span x-text="selectedGuia.pedimento || 'N/A'"></span></div>
                                    <div><strong class="block text-gray-500">Ruta Asignada:</strong> <span x-text="selectedGuia.ruta?.nombre || 'Sin Asignar'"></span></div>
                                    <div><strong class="block text-gray-500">Estatus:</strong> <span x-text="selectedGuia.estatus"></span></div>
                                    <div><strong class="block text-gray-500">Transporte:</strong> <span x-text="selectedGuia.transporte"></span></div>
                                </div>

                                {{-- Tabla de Facturas --}}
                                <h4 class="text-md font-semibold text-gray-700 mb-2">Facturas Incluidas</h4>
                                <div class="border rounded-lg overflow-hidden max-h-64 overflow-y-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-100 sticky top-0">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase"># Factura</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">SO</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Destino</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cajas / Botellas</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha Entrega</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <template x-for="factura in selectedGuia.facturas" :key="factura.id">
                                                <tr>
                                                    <td class="px-4 py-3" x-text="factura.numero_factura"></td>
                                                    <td class="px-4 py-3" x-text="factura.so || 'N/A'"></td>
                                                    <td class="px-4 py-3" x-text="factura.destino"></td>
                                                    <td class="px-4 py-3" x-text="`${factura.cajas} / ${factura.botellas}`"></td>
                                                    <td class="px-4 py-3" x-text="formatDate(factura.fecha_entrega)"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            <div x-show="isImportModalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @keydown.escape.window="closeAllModals()">
                <div @click.outside="closeAllModals()" class="bg-white rounded-lg shadow-xl p-8 w-full max-w-md">
                    <h3 class="text-xl font-bold text-[#2c3856] mb-4">Cargar Guías por CSV</h3>
                    <form action="{{ route('rutas.asignaciones.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label for="csv_file" class="block text-sm font-medium text-gray-700">Seleccionar archivo CSV</label>
                            <input type="file" name="csv_file" id="csv_file" required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                        <div class="mb-6 text-sm text-gray-600">
                            Descarga la plantilla de ejemplo para asegurar el formato correcto.
                            <a href="{{ route('rutas.asignaciones.template') }}" class="text-blue-600 hover:underline">Descargar plantilla</a>
                        </div>
                        <div class="flex justify-end space-x-4">
                            <button type="button" @click="closeAllModals()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancelar</button>
                            <button type="submit" class="px-4 py-2 bg-[#ff9c00] text-white rounded-md hover:bg-orange-600">Subir CSV</button>
                        </div>
                    </form>
                </div>
            </div>

        <div x-show="isEditModalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
            <div @click.outside="closeAllModals()" class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col">
                {{-- Usamos x-if para renderizar solo cuando hay datos --}}
                <template x-if="editData.id">
                    <div>
                        <div class="flex justify-between items-center border-b p-4">
                            <h3 class="text-xl font-bold text-[#2c3856]">Editando Guía: <span x-text="editData.guia"></span></h3>
                            <button @click="closeAllModals()" class="text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
                        </div>
                        <div class="p-6 flex-grow overflow-y-auto">
                            <form @submit.prevent="submitEditForm()">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div><label class="block text-sm font-medium">Operador</label><input type="text" x-model="editData.operador" class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></div>
                                    <div><label class="block text-sm font-medium">Placas</label><input type="text" x-model="editData.placas" class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></div>
                                    <div><label class="block text-sm font-medium">Teléfono</label><input type="text" x-model="editData.telefono" class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></div>
                                    <div><label class="block text-sm font-medium">Origen</label><input type="text" x-model="editData.origen" class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></div>
                                    <div><label class="block text-sm font-medium">Fecha Asignación</label><input type="date" x-model="editData.fecha_asignacion" class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></div>
                                    <div><label class="block text-sm font-medium">Pedimento</label><input type="text" x-model="editData.pedimento" class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></div>
                                    <div><label class="block text-sm font-medium">Custodia</label><input type="text" x-model="editData.custodia" class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></div>
                                    <div><label class="block text-sm font-medium">Hora Planeada</label><input type="text" x-model="editData.hora_planeada" class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></div>
                                    <div>
                                        <label class="block text-sm font-medium">Transporte</label>
                                        <input type="text" x-model="editData.transporte" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                                    </div>
                                </div>
                                <h4 class="text-lg font-semibold text-[#2c3856] border-b mt-6 pb-2 mb-4">Facturas</h4>
                                <div class="space-y-3">
                                    <template x-for="(factura, index) in editData.facturas" :key="index">
                                        {{-- Se ajusta el grid para que quepan todos los campos --}}
                                        <div class="grid grid-cols-10 gap-4 items-end bg-gray-50 p-3 rounded-md border">
                                            <div class="col-span-2"><label class="text-xs"># Factura</label><input type="text" x-model="factura.numero_factura" class="w-full rounded-md border-gray-300 text-sm"></div>
                                            <div class="col-span-2"><label class="text-xs">Destino</label><input type="text" x-model="factura.destino" class="w-full rounded-md border-gray-300 text-sm"></div>
                                            <div><label class="text-xs">SO</label><input type="text" x-model="factura.so" class="w-full rounded-md border-gray-300 text-sm"></div>
                                            <div><label class="text-xs">F. Entrega</label><input type="date" x-model="factura.fecha_entrega" class="w-full rounded-md border-gray-300 text-sm"></div>
                                            {{-- INICIO DE CAMPOS REINTEGRADOS --}}
                                            <div><label class="text-xs">Hora Cita</label><input type="text" x-model="factura.hora_cita" class="w-full rounded-md border-gray-300 text-sm"></div>
                                            <div><label class="text-xs">Cajas</lebel><input type="number" x-model="factura.cajas" class="w-full rounded-md border-gray-300 text-sm"></div>
                                            <div><label class="text-xs">Botellas</label><input type="number" x-model="factura.botellas" class="w-full rounded-md border-gray-300 text-sm"></div>
                                            {{-- FIN DE CAMPOS REINTEGRADOS --}}
                                            <button type="button" @click="editData.facturas.splice(index, 1)" class="bg-red-500 text-white rounded-md py-2 text-sm">Eliminar</button>
                                        </div>
                                    </template>
                                </div>
                                <button type="button" @click="editData.facturas.push({numero_factura: '', destino: '', so: '', fecha_entrega: '', hora_cita: '', cajas: 0, botellas: 0})" class="mt-2 text-sm text-blue-600">+ Añadir Factura</button>
                                
                                <div class="mt-6 pt-4 border-t flex justify-end gap-4">
                                    <button type="button" @click="closeAllModals()" class="px-4 py-2 bg-gray-200 rounded-md">Cancelar</button>
                                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md">Guardar Cambios</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </template>
                <template x-if="isLoading">
                    <div class="p-6 text-center">Cargando datos para editar...</div>
                </template>
            </div>
        </div>
    </div>

    {{-- SCRIPT MANEJADOR DE ALPINE.JS --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('assignmentManager', () => ({
                isAssignModalOpen: false,
                isDetailsModalOpen: false,
                isImportModalOpen: false,
                isEditModalOpen: false,
                selectedGuia: null,
                editData: {},
                availableRoutes: [],
                searchTerm: '',
                isLoading: false,


                formatDate(dateString) {
                    // Si la fecha es nula, indefinida o no es un string, devuelve 'N/A' y evita el error.
                    if (!dateString || typeof dateString !== 'string') {
                        return 'N/A';
                    }

                    const parts = dateString.split('-');
                    if (parts.length !== 3) return 'N/A';

                    // Creamos la fecha de forma segura
                    const date = new Date(parts[0], parts[1] - 1, parts[2]);
                    
                    if (isNaN(date.getTime())) {
                        return 'N/A';
                    }

                    return date.toLocaleDateString('es-MX', { 
                        day: '2-digit', 
                        month: '2-digit', 
                        year: 'numeric' 
                    });
                },
                
                openAssignModal(guia) {
                    this.selectedGuia = guia;
                    this.isAssignModalOpen = true;
                    this.searchTerm = '';
                    this.searchRoutes();
                },
                openDetailsModal(guia) {
                    this.selectedGuia = guia;
                    this.isDetailsModalOpen = true;
                },
                closeAllModals() {
                    this.isAssignModalOpen = false;
                    this.isDetailsModalOpen = false;
                    this.isImportModalOpen = false;
                    this.isEditModalOpen = false;
                    this.selectedGuia = null;
                    this.editData = {};
                },
                searchRoutes() {
                    this.isLoading = true;
                    fetch(`{{ route('rutas.plantillas.search') }}?search=${this.searchTerm}`)
                        .then(response => response.json())
                        .then(data => {
                            this.availableRoutes = data;
                            this.isLoading = false;
                        });
                },
                openEditModal(guiaId) {
                    this.isLoading = true;
                    this.isEditModalOpen = true;
                    this.editData = {};
                    fetch(`/rutas/asignaciones/${guiaId}/edit`)
                        .then(res => res.json())
                        .then(data => {
                            this.editData = data;
                            this.isLoading = false;
                        });
                },
                submitEditForm() {
                    fetch(`/rutas/asignaciones/${this.editData.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(this.editData)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            alert(data.message);
                            window.location.reload();
                        } else {
                            alert(data.message || 'Ocurrió un error.');
                        }
                    });
                }
            }));
        });
    </script>
</x-app-layout>