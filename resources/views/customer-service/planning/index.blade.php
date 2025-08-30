<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gestión de Planificación de Rutas') }}
            </h2>

        </div>
    </x-slot>

    <div class="py-12" x-data="planningManager()">
        <div class="mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="mb-6">
                    @include('customer-service.planning._filters')
                </div>
                <div x-show="selectedPlannings.length > 0" class="bg-gray-800 text-white p-3 rounded-lg shadow-lg mb-4 flex justify-between items-center transition-transform" x-transition>
                    <span x-text="`(${selectedPlannings.length}) registros seleccionados.`"></span>
                    <div class="flex items-center space-x-4">
                        <button @click="bulkEdit()" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold hover:bg-indigo-700">
                            <i class="fas fa-edit mr-2"></i>Editar Selección
                        </button>
                        <button @click="createGuide()" class="px-4 py-2 bg-[#ff9c00] text-white rounded-md text-sm font-semibold hover:bg-orange-600">
                            <i class="fas fa-plus-circle mr-2"></i>Crear Guía con Selección
                        </button>
                        <button @click="openAddToGuiaModal()" class="px-4 py-2 bg-orange-600 text-white rounded-md text-sm font-semibold hover:bg-orange-700">
                            <i class="fas fa-plus mr-2"></i>Añadir a Guía Existente
                        </button>                        
                    </div>
                </div>               
                <div class="flex items-center space-x-4">
                    <a href="{{ route('customer-service.planning.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i>Añadir Manualmente
                    </a>                    
                    <button @click="isColumnModalOpen = true" class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-gray-700">
                        <i class="fas fa-columns mr-2"></i>Seleccionar Columnas
                    </button>
                </div>
                
                <br>
                <div x-show="!isLoading" style="display: none;">
                    @include('customer-service.planning._table')
                </div>
                
                <div x-show="isLoading" class="text-center py-10">
                    <i class="fas fa-spinner fa-spin text-4xl text-gray-500"></i>
                    <p class="mt-2 text-gray-600">Cargando datos...</p>
                </div>
            </div>
        </div>
        <div x-show="isAddToGuiaModalOpen" @keydown.escape.window="isAddToGuiaModalOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" style="display: none;">
            <div @click.outside="isAddToGuiaModalOpen = false" class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
                <h3 class="text-xl font-bold text-[#2c3856] mb-4">Añadir a Guía Existente</h3>
                <form :action="`/rutas/asignaciones/add-orders-to-guia`" method="POST">
                    @csrf
                    <input type="hidden" name="planning_ids" :value="JSON.stringify(selectedPlannings)">
                    
                    <div>
                        <label for="guia_search" class="block text-sm font-medium text-gray-700">Buscar Guía (por número, operador o placas)</label>
                        <input type="text" id="guia_search" x-model="guiaSearch" @input.debounce.300ms="searchGuias()" class="mt-1 block w-full rounded-md border-gray-300" placeholder="Escribe para buscar...">
                    </div>

                    <div class="mt-4 max-h-60 overflow-y-auto border rounded-lg">
                        <template x-for="guia in guiaSearchResults" :key="guia.id">
                            <label class="flex items-center p-3 hover:bg-gray-100 border-b cursor-pointer">
                                <input type="radio" name="guia_id" :value="guia.id" class="rounded-full text-[#ff9c00] focus:ring-[#ff9c00]">
                                <div class="ml-3 text-sm">
                                    <p class="font-semibold" x-text="guia.guia"></p>
                                    <p class="text-gray-600" x-text="`${guia.operador} - ${guia.placas}`"></p>
                                </div>
                            </label>
                        </template>
                        <template x-if="guiaSearch.length > 1 && guiaSearchResults.length === 0">
                            <p class="p-3 text-sm text-gray-500">No se encontraron guías.</p>
                        </template>
                    </div>

                    <div class="mt-6 flex justify-end gap-4">
                        <button type="button" @click="isAddToGuiaModalOpen = false" class="px-4 py-2 bg-gray-200 rounded-md">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-md">Añadir a Guía</button>
                    </div>
                </form>
            </div>
        </div>        
        
        @include('customer-service.planning._column-selector-modal')
        @include('customer-service.planning._scales-modal')
    </div>

<style>
@keyframes flash { 0%, 100% { background-color: white; } 50% { background-color: #fef3c7; } }
.flashing-row { animation: flash 1.5s infinite; }
.resizer { position: absolute; top: 0; right: 0; width: 5px; height: 100%; cursor: col-resize; user-select: none; }
th { position: relative; }
</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
function planningManager() {
    return {
        // --- ESTADO DE LA TABLA Y DATOS ---
        plannings: [],
        pagination: {},
        isLoading: true,
        filters: { search: '', status: '', date_from: '', date_to: '', page: 1 },
        selectedPlannings: [],

        // --- GESTIÓN DE COLUMNAS ---
        isColumnModalOpen: false,
        allColumns: @json($allColumns),
        visibleColumns: {},
        columnOrder: [],
        columnWidths: {},

        // --- GESTIÓN DE MODAL DE ESCALAS ---
        isScalesModalOpen: false,
        selectedPlanning: {},
        scalesCount: 1,
        scales: [],
        warehouses: @json($warehouses),
        isAddToGuiaModalOpen: false,
        guiaSearch: '',
        guiaSearchResults: [],        
        

        selectedPlannings: [],

        // --- INICIALIZACIÓN ---
        init() {
            this.loadColumnSettings();
            this.fetchPlannings();
            
            this.$watch('filters', () => { this.filters.page = 1; this.fetchPlannings(); });
            this.$watch('visibleColumns', () => this.saveColumnSettings());

            this.$nextTick(() => {
                this.initSortable();
                this.initResizers();
            });

            this.$el.addEventListener('toggle-all-plannings', (e) => {
                this.toggleAllPlannings(e.detail);
            });

        },

        // --- LÓGICA DE DATOS ---
        fetchPlannings() {
            this.isLoading = true;
            const params = new URLSearchParams(this.filters).toString();
            fetch(`{{ route('customer-service.planning.filter') }}?${params}`)
                .then(response => response.json())
                .then(data => {
                    this.plannings = data.data;
                    this.pagination = data;
                    this.isLoading = false;
                });
        },
        changePage(page) { if (page) { this.filters.page = page; this.fetchPlannings(); } },

        getFormattedCell(planning, columnKey) {
            const value = planning[columnKey];

            if (columnKey === 'guia') {
                // Revisa si el registro de planificación tiene una guía asociada y si esa guía tiene un número.
                if (planning.guia && planning.guia.guia) {
                    return planning.guia.guia;
                }
                return 'Sin Asignar'; // Muestra esto si no tiene guía
            }            

            // Si el valor está vacío, devuelve 'N/A' inmediatamente
            if (value === null || value === undefined || value === '') {
                return 'N/A';
            }

            // Lógica específica para las columnas de fecha
            if (['fecha_carga', 'fecha_entrega'].includes(columnKey)) {
                try {
                    // Maneja tanto fechas con hora como sin hora
                    const date = new Date(value.replace(' ', 'T'));
                    
                    if (isNaN(date.getTime())) {
                        return 'Fecha inválida';
                    }
                    
                    return date.toLocaleDateString('es-MX', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });
                } catch (error) {
                    return 'Fecha inválida';
                }
            }

            if (columnKey === 'hora_carga') {
                return value.substring(0, 5); // Muestra solo HH:MM
            }

            if (columnKey === 'subtotal') {
                return `$${parseFloat(value).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            }

            return value;
        },      

        // --- LÓGICA DE GESTIÓN DE COLUMNAS ---
        loadColumnSettings() {
            const defaultOrder = Object.keys(this.allColumns);
            const defaultVisible = defaultOrder.reduce((acc, key) => ({...acc, [key]: true}), {});
            
            this.columnOrder = JSON.parse(localStorage.getItem('planning_columnOrder')) || defaultOrder;
            this.visibleColumns = {...defaultVisible, ...JSON.parse(localStorage.getItem('planning_visibleColumns'))};
            this.columnWidths = JSON.parse(localStorage.getItem('planning_columnWidths')) || {};
        },
        saveColumnSettings() {
            localStorage.setItem('planning_visibleColumns', JSON.stringify(this.visibleColumns));
            localStorage.setItem('planning_columnOrder', JSON.stringify(this.columnOrder));
            localStorage.setItem('planning_columnWidths', JSON.stringify(this.columnWidths));
        },
        initSortable() {
            const tableHead = this.$el.querySelector('thead tr');
            Sortable.create(tableHead, {
                animation: 150,
                handle: '.drag-handle',
                onEnd: (evt) => {
                    const movedItem = this.columnOrder.splice(evt.oldIndex, 1)[0];
                    this.columnOrder.splice(evt.newIndex, 0, movedItem);
                    this.saveColumnSettings();
                }
            });
        },
        initResizers() {
            const headers = this.$el.querySelectorAll('th');
            headers.forEach(header => {
                const resizer = header.querySelector('.resizer');
                if (!resizer) return;
                
                let startX, startWidth;
                const onMouseMove = (e) => {
                    const newWidth = startWidth + (e.clientX - startX);
                    if (newWidth > 50) { // Mínimo ancho
                        header.style.width = `${newWidth}px`;
                        this.columnWidths[header.dataset.column] = `${newWidth}px`;
                    }
                };
                const onMouseUp = () => {
                    document.removeEventListener('mousemove', onMouseMove);
                    document.removeEventListener('mouseup', onMouseUp);
                    this.saveColumnSettings();
                };
                resizer.addEventListener('mousedown', (e) => {
                    startX = e.clientX;
                    startWidth = header.offsetWidth;
                    document.addEventListener('mousemove', onMouseMove);
                    document.addEventListener('mouseup', onMouseUp);
                });
            });
        },

        openScalesModal(planning) {
            this.selectedPlanning = planning;
            this.isScalesModalOpen = true;
        },

        closeScalesModal() {
            this.isScalesModalOpen = false;
            this.scales = [];
            this.scalesCount = 1;
            this.selectedPlanning = {};
        },

        generateScales() {
            if (this.scalesCount > 0) {
                this.scales = Array.from({ length: this.scalesCount }, () => ({ origen: '', destino: '' }));
            }
        },

        saveScales() {
            // Validación simple en el frontend
            const allScalesValid = this.scales.every(s => s.origen && s.destino);
            if (!allScalesValid) {
                alert('Por favor, complete todos los campos de origen y destino.');
                return;
            }

            fetch(`/customer-service/planning/${this.selectedPlanning.id}/add-scales`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ scales: this.scales })
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    alert(data.message);
                    this.closeScalesModal();
                    this.fetchPlannings(); // Recargar la tabla
                } else {
                    alert('Ocurrió un error.');
                }
            })
            .catch(error => console.error('Error:', error));
        },

        openAddToGuiaModal() {
            if (this.selectedPlannings.length === 0) {
                alert('Por favor, selecciona al menos un registro.');
                return;
            }
            this.isAddToGuiaModalOpen = true;
        },

        searchGuias() {
            if (this.guiaSearch.length < 2) {
                this.guiaSearchResults = [];
                return;
            }
            fetch(`/rutas/asignaciones/search?term=${this.guiaSearch}`)
                .then(res => res.json())
                .then(data => {
                    this.guiaSearchResults = data;
                });
        },        

        createGuide() {
            if (this.selectedPlannings.length === 0) {
                alert('Por favor, selecciona al menos un registro de planificación.');
                return;
            }

            // Tomamos el primer registro seleccionado como fuente para los datos de la guía
            const firstSelected = this.plannings.find(p => p.id == this.selectedPlannings[0]);

            if (!firstSelected) {
                alert('Error al encontrar el registro seleccionado. Intenta de nuevo.');
                return;
            }

            // Construimos los parámetros para la URL
            const params = new URLSearchParams();
            this.selectedPlannings.forEach(id => params.append('planning_ids[]', id));
            
            // Añadimos los datos a pre-rellenar
            if(firstSelected.custodia) params.append('custodia', firstSelected.custodia);
            if(firstSelected.hora_carga) params.append('hora_planeada', firstSelected.hora_carga.substring(0, 5));
            if(firstSelected.origen) params.append('origen', firstSelected.origen);
            if(firstSelected.fecha_carga) params.append('fecha_asignacion', firstSelected.fecha_carga.split('T')[0]);

            // Redirigimos al formulario de creación de guías
            window.location.href = `{{ route('rutas.asignaciones.create') }}?${params.toString()}`;
        },   
        
        toggleAllPlannings(checked) {
            if (checked) {
                // Si la casilla está marcada, añade todos los IDs de la página actual a la selección
                this.selectedPlannings = this.plannings.map(p => p.id);
            } else {
                // Si se desmarca, limpia la selección
                this.selectedPlannings = [];
            }
        },
        
        bulkEdit() {
            if (this.selectedPlannings.length === 0) {
                alert('Por favor, selecciona al menos un registro para editar.');
                return;
            }

            // Construimos los parámetros para la URL de edición masiva
            const params = new URLSearchParams();
            this.selectedPlannings.forEach(id => params.append('ids[]', id));

            // Redirigimos al nuevo formulario de edición masiva
            window.location.href = `{{ route('customer-service.planning.bulk-edit') }}?${params.toString()}`;
        },
        
        markAsDirect(planningId) {
            if (!confirm('¿Estás seguro de que esta ruta no necesita escalas y quieres aprobarla?')) {
                return;
            }

            fetch(`/customer-service/planning/${planningId}/mark-as-direct`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                }
            })
            .then(response => {
                if (response.ok) {
                    this.fetchPlannings(); // Recarga la tabla para que se apliquen los cambios visuales
                } else {
                    alert('Ocurrió un error al marcar la ruta como directa.');
                }
            });
        }        

    }
}
</script>
</x-app-layout>