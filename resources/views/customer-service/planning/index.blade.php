<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gestión de Planificación de Rutas') }}
            </h2>
        </div>
    </x-slot>

    <!-- <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                <p class="font-bold">Éxito</p>
                <p>{{ session('success') }}</p>
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                <p class="font-bold">Error</p>
                <p>{{ session('error') }}</p>
            </div>
        @endif
        @if (session('info'))
            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4" role="alert">
                <p class="font-bold">Información</p>
                <p>{{ session('info') }}</p>
            </div>
        @endif
    </div> -->

    <div class="py-12" x-data="planningManager()">
        <div id="flash-success" class="fixed top-20 right-4 z-50 bg-white border-l-4 border-[#ff9c00] text-[#2c3856] px-6 py-4 rounded-lg shadow-xl flex items-center justify-between min-w-[300px]" role="alert" style="display: none;">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <strong class="font-bold mr-1">¡Éxito!</strong>
                <span id="flash-success-message" class="block sm:inline"></span>
            </div>
            <button onclick="document.getElementById('flash-success').style.display = 'none';" class="text-gray-500 hover:text-gray-700 focus:outline-none"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
        <div id="flash-error" class="fixed top-20 right-4 z-50 bg-white border-l-4 border-red-600 text-red-700 px-6 py-4 rounded-lg shadow-xl flex items-center justify-between min-w-[300px]" role="alert" style="display: none;">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <strong class="font-bold mr-1">¡Error!</strong>
                <span id="flash-error-message" class="block sm:inline"></span>
            </div>
            <button onclick="document.getElementById('flash-error').style.display = 'none';" class="text-gray-500 hover:text-gray-700 focus:outline-none"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>        
        <div class="mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                <div class="mb-4">
                    @include('customer-service.planning._filters')
                </div>

                <div class="mb-6">
                    <div x-show="selectedPlannings.length > 0" class="bg-gray-800 text-white p-3 rounded-lg shadow-lg flex flex-col md:flex-row justify-between items-center transition-transform w-full" x-transition>
                        <span class="mb-2 md:mb-0" x-text="`(${selectedPlannings.length}) registros seleccionados.`"></span>
                        <div class="flex flex-col sm:flex-row items-center space-y-2 sm:space-y-0 sm:space-x-4">
                            <button @click="bulkEdit()" class="px-4 py-2 w-full sm:w-auto bg-indigo-600 text-white rounded-md text-sm font-semibold hover:bg-indigo-700"><i class="fas fa-edit mr-2"></i>Editar Selección</button>
                            <button @click="createGuide()" class="px-4 py-2 w-full sm:w-auto bg-[#ff9c00] text-white rounded-md text-sm font-semibold hover:bg-orange-600"><i class="fas fa-plus-circle mr-2"></i>Crear Guía</button>
                            <button @click="openAddToGuiaModal()" class="px-4 py-2 w-full sm:w-auto bg-orange-600 text-white rounded-md text-sm font-semibold hover:bg-orange-700"><i class="fas fa-plus mr-2"></i>Añadir a Guía</button>                        
                        </div>
                    </div>               
                </div>

                <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0 mb-6">
                    <div class="flex items-center space-x-2 sm:space-x-4 flex-wrap">
                        <a href="{{ route('customer-service.planning.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-blue-700"><i class="fas fa-plus mr-2"></i>Añadir</a>                    
                        <button @click="isColumnModalOpen = true" class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-gray-700"><i class="fas fa-columns mr-2"></i>Columnas</button>
                        <button @click="isAdvancedFilterModalOpen = true" class="px-4 py-2 bg-teal-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-teal-700"><i class="fas fa-filter mr-2"></i>Filtros</button>
                        <button @click="exportToCsv()" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-green-700"><i class="fas fa-file-csv mr-2"></i>Exportar</button>
                    </div>
                    
                </div>
                
                <div id="planning-table-container" x-show="!isLoading">
                    @include('customer-service.planning._table')
                </div>
                
                {{-- **INICIA BLOQUE DE PAGINACIÓN CORREGIDO Y MEJORADO** --}}
                <div x-show="!isLoading && pagination.total > 0" class="mt-4 flex flex-col md:flex-row justify-between items-center text-sm text-gray-700">
                    <p class="mb-2 md:mb-0">
                        Mostrando de <span x-text="pagination.from || 0"></span> a <span x-text="pagination.to || 0"></span> de <span x-text="pagination.total || 0"></span> resultados
                    </p>
                    <div class="flex items-center text-sm text-gray-700">
                        <span>Mostrar</span>
                            <select x-model="rowsPerPage" @change="changeRowsPerPage()" class="mx-2 rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="30">30</option>
                                <option value="40">40</option>
                                <option value="50">50</option>
                                <option value="60">60</option>
                                <option value="70">70</option>
                                <option value="80">80</option>
                                <option value="90">90</option>
                                <option value="100">100</option>
                            </select>
                        <span>registros.</span>
                    </div>                    
                    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center">
                        <template x-for="(link, index) in pagination.links" :key="index">
                            <button @click="changePage(link)" 
                                    :disabled="!link.url"
                                    class="px-3 py-1 mx-1 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                                    :class="{
                                        'bg-indigo-600 text-white': link.active,
                                        'bg-white text-gray-600 hover:bg-gray-100': !link.active && link.url,
                                        'bg-gray-100 text-gray-400 cursor-not-allowed': !link.url
                                    }"
                                    >
                                <span x-html="link.label"></span>
                            </button>
                        </template>
                    </nav>
                </div>
                {{-- **TERMINA BLOQUE DE PAGINACIÓN** --}}
                
                <div x-show="isLoading" class="text-center py-10">
                    <i class="fas fa-spinner fa-spin text-4xl text-gray-500"></i>
                    <p class="mt-2 text-gray-600">Cargando datos...</p>
                </div>
            </div>
        </div>
        
        {{-- Modales --}}
        @include('customer-service.planning._column-selector-modal')
        @include('customer-service.planning._scales-modal')
        @include('customer-service.planning._add-to-guia-modal')
        @include('customer-service.planning._advanced-filters-modal')
        <div x-show="isEditGuiaModalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @keydown.escape.window="closeAllModals()">
            <div @click.outside="closeAllModals()" class="bg-white rounded-lg shadow-xl p-8 w-full max-w-md">
                <h3 class="text-xl font-bold text-[#2c3856] mb-4">Editar Número de Guía</h3>
                <form @submit.prevent="submitGuiaEdit()">
                    <div>
                        <label for="guia_number_input" class="block text-sm font-medium text-gray-700">Nuevo número de guía</label>
                        <input type="text" id="guia_number_input" x-model="guiaToEdit.number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>
                    <div class="mt-6 flex justify-end space-x-4">
                        <button type="button" @click="closeAllModals()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
        
    </div>

<style>
@keyframes flash { 0%, 100% { background-color: white; } 50% { background-color: #fef3c7; } }
.flashing-row { animation: flash 1.5s infinite; }
.resizer { position: absolute; top: 0; right: 0; width: 5px; height: 100%; cursor: col-resize; user-select: none; }
th { position: relative; }

    #planning-table-container {
        overflow: visible !important; 
    }

</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    @if (session('success'))
        sessionStorage.setItem('planning_flash_success', '{{ session('success') }}');
    @endif
    @if (session('error'))
        sessionStorage.setItem('planning_flash_error', '{{ session('error') }}');
    @endif
});    
function planningManager() {
    return {
        plannings: [],
        pagination: { links: [] }, // Inicializar links como array vacío
        isLoading: true,
        isEditGuiaModalOpen: false,
        guiaToEdit: { id: null, number: '' },        
        filters: { 
            search: new URLSearchParams(window.location.search).get('search') || '', 
            status: new URLSearchParams(window.location.search).get('status') || '', 
            origen: new URLSearchParams(window.location.search).get('origen') || '', 
            destino: new URLSearchParams(window.location.search).get('destino') || '', 
            date_created_from: new URLSearchParams(window.location.search).get('date_created_from') || '', 
            date_created_to: new URLSearchParams(window.location.search).get('date_created_to') || '', 
            page: new URLSearchParams(window.location.search).get('page') || 1,
            // --- FILTROS AVANZADOS ---
            guia_adv: new URLSearchParams(window.location.search).get('guia_adv') || '',
            so_number_adv: new URLSearchParams(window.location.search).get('so_number_adv') || '',
            factura_adv: new URLSearchParams(window.location.search).get('factura_adv') || '',
            razon_social_adv: new URLSearchParams(window.location.search).get('razon_social_adv') || '',
            direccion_adv: new URLSearchParams(window.location.search).get('direccion_adv') || '',
            fecha_entrega_adv: new URLSearchParams(window.location.search).get('fecha_entrega_adv') || '',
            fecha_carga_adv: new URLSearchParams(window.location.search).get('fecha_carga_adv') || '',
            origen_adv: new URLSearchParams(window.location.search).get('origen_adv') || '',
            destino_adv: new URLSearchParams(window.location.search).get('destino_adv') || '',
            estado_adv: new URLSearchParams(window.location.search).get('estado_adv') || '',
            transporte_adv: new URLSearchParams(window.location.search).get('transporte_adv') || '',
            operador_adv: new URLSearchParams(window.location.search).get('operador_adv') || '',
            placas_adv: new URLSearchParams(window.location.search).get('placas_adv') || '',
            tipo_ruta_adv: new URLSearchParams(window.location.search).get('tipo_ruta_adv') || '',
            servicio_adv: new URLSearchParams(window.location.search).get('servicio_adv') || '',
            canal_adv: new URLSearchParams(window.location.search).get('canal_adv') || '',
            custodia_adv: new URLSearchParams(window.location.search).get('custodia_adv') || '',
            urgente_adv: new URLSearchParams(window.location.search).get('urgente_adv') || '',
            devolucion_adv: new URLSearchParams(window.location.search).get('devolucion_adv') || ''
        },
        selectedPlannings: [],
        rowsPerPage: localStorage.getItem('planning_rowsPerPage') || 10,
        sortColumn: new URLSearchParams(window.location.search).get('sort_by') || 'created_at',
        sortDirection: new URLSearchParams(window.location.search).get('sort_dir') || 'asc',
        isColumnModalOpen: false,
        allColumns: @json($allColumns),
        visibleColumns: {},
        columnOrder: [],
        columnWidths: {},
        isScalesModalOpen: false,
        isAddToGuiaModalOpen: false,
        isAdvancedFilterModalOpen: false,
        selectedPlanning: {},
        scalesCount: 1,
        scales: [],
        warehouses: @json($warehouses),
        guiaSearch: '',
        guiaSearchResults: [],
        sorts: [],

        init() {
            try {
                const urlSorts = new URLSearchParams(window.location.search).get('sorts');
                this.sorts = urlSorts ? JSON.parse(urlSorts) : [];
            } catch (e) {
                this.sorts = [];
            }            
            this.loadColumnSettings();
            this.fetchPlannings();
            this.$watch('filters', Alpine.debounce(() => { 
                this.filters.page = 1; 
                this.fetchPlannings(); 
            }, 300));
            this.$watch('visibleColumns', () => this.saveColumnSettings());
            this.$nextTick(() => { this.initSortable(); this.initResizers(); });
            this.$el.addEventListener('toggle-all-plannings', (e) => { this.toggleAllPlannings(e.detail); });
            const flashSuccess = sessionStorage.getItem('planning_flash_success');
            if (flashSuccess) {
                this.showFlashMessage(flashSuccess, 'success');
                sessionStorage.removeItem('planning_flash_success');
            }
            const flashError = sessionStorage.getItem('planning_flash_error');
            if (flashError) {
                this.showFlashMessage(flashError, 'error');
                sessionStorage.removeItem('planning_flash_error');
            }
        },

        // --- NUEVA FUNCIÓN: Para mostrar notificaciones dinámicamente ---
        showFlashMessage(message, type = 'success') {
            const id = type === 'success' ? 'flash-success' : 'flash-error';
            const messageId = type === 'success' ? 'flash-success-message' : 'flash-error-message';
            
            const el = document.getElementById(id);
            const msgEl = document.getElementById(messageId);

            if (el && msgEl) {
                msgEl.innerText = message;
                el.style.display = 'flex';
                setTimeout(() => {
                    el.style.display = 'none';
                }, 5000); // Se oculta después de 5 segundos
            }
        },

        // --- FUNCIÓN NUEVA: Abre el modal de edición de guía ---
        openEditGuiaModal(planning) {
            if (planning && planning.guia) {
                this.guiaToEdit.id = planning.guia.id;
                this.guiaToEdit.number = planning.guia.guia;
                this.isEditGuiaModalOpen = true;
            }
        },

        closeAllModals() {
            this.isScalesModalOpen = false;
            this.isImportModalOpen = false;
            this.isEditGuiaModalOpen = false; // Añadido
            // ...
        },        

        // --- FUNCIÓN NUEVA: Envía el formulario de edición ---
        submitGuiaEdit() {
            fetch(`/rutas/asignaciones/${this.guiaToEdit.id}/update-number`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ guia_number: this.guiaToEdit.number })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.showFlashMessage(data.message, 'success');
                    // Actualizamos el dato en la tabla al instante, sin recargar
                    this.plannings.forEach(p => {
                        if (p.guia && p.guia.id === this.guiaToEdit.id) {
                            p.guia.guia = data.new_guia_number;
                        }
                    });
                    this.closeAllModals();
                } else {
                    this.showFlashMessage(data.message || 'Ocurrió un error.', 'error');
                }
            });
        },        


        fetchPlannings() {
            this.isLoading = true; this.selectedPlannings = [];
            const cleanFilters = Object.fromEntries(Object.entries(this.filters).filter(([_, v]) => v !== null && v !== ''));
            const params = new URLSearchParams(cleanFilters);
            
            if (this.sorts.length > 0) {
                params.append('sorts', JSON.stringify(this.sorts));
            }
            params.append('per_page', this.rowsPerPage);
            window.history.pushState({}, '', `${window.location.pathname}?${params}`);

            fetch(`{{ route('customer-service.planning.filter') }}?${params}`)
                .then(response => {
                    if (!response.ok) {
                        // Si la respuesta del servidor es un error (ej. 500), lo capturamos
                        throw new Error(`Error del servidor: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    this.plannings = data.data;
                    this.pagination = data;
                    this.isLoading = false; // El proceso fue exitoso
                })
                .catch(error => {
                    console.error('Ocurrió un error al filtrar los datos:', error);
                    // --- CAMBIO: Usamos la nueva función en lugar de alert() ---
                    this.showFlashMessage('Hubo un error al aplicar los filtros.', 'error');
                    this.plannings = [];
                    this.pagination = { links: [] };
                    this.isLoading = false;
                });
        },

        clearBasicFilters() {
              this.filters.search = '';
            this.filters.status = '';
            this.filters.origen = '';
            this.filters.destino = '';
            this.filters.date_created_from = '';
            this.filters.date_created_to = '';
        },       
        
        // **NUEVA FUNCIÓN PARA MANEJAR CLICS DE PAGINACIÓN**
        changePage(link) {
            if (!link.url) return; // No hacer nada si el link es nulo (ej. '...' o deshabilitado)
            try {
                // Extraer el número de página de la URL completa
                const url = new URL(link.url);
                this.filters.page = url.searchParams.get('page');
                this.fetchPlannings();
            } catch (e) {
                console.error("URL de paginación inválida:", link.url);
            }
        },

        getFormattedCell(planning, columnKey) {
            // Primero, manejamos el caso especial de la columna 'guia'
            if (columnKey === 'guia') {
                if (planning.guia && planning.guia.id) {
                    // Devolvemos un botón HTML que, al ser clickeado, llama a la función
                    // para abrir el modal de edición, pasándole el objeto 'planning' completo.
                    return `<button type="button" @click='openEditGuiaModal(${JSON.stringify(planning)})' class="text-indigo-600 hover:underline font-semibold" title="Editar Número de Guía">${planning.guia.guia}</button>`;
                }
                return 'Sin Asignar'; // Si no hay guía, mostramos 'Sin Asignar'
            }

            // Para todas las demás columnas, obtenemos el valor
            const value = planning[columnKey];
            
            // Si el valor es nulo o vacío, devolvemos 'N/A'
            if (value === null || value === undefined || value === '') {
                return 'N/A';
            }
            
            // Formateamos las fechas
            if (['fecha_carga', 'fecha_entrega', 'created_at'].includes(columnKey)) {
                try {
                    const date = new Date(value);
                    // Verificamos que sea una fecha válida antes de formatear
                    if (isNaN(date.getTime())) return value;
                    return date.toLocaleDateString('es-MX', { 
                        day: '2-digit', 
                        month: '2-digit', 
                        year: 'numeric' 
                    });
                } catch (e) {
                    return value; // Si hay error, devuelve el valor original
                }
            }

            // Formateamos la hora
            if (columnKey === 'hora_carga' && typeof value === 'string') {
                return value.substring(0, 5);
            }
            
            // Formateamos el subtotal como moneda
            if (columnKey === 'subtotal') {
                return `$${parseFloat(value).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            }
            
            // Si no es un caso especial, devolvemos el valor tal cual
            return value;
        },

        changeRowsPerPage() {
            localStorage.setItem('planning_rowsPerPage', this.rowsPerPage);
            this.filters.page = 1;
            this.fetchPlannings();
        },
        sortBy(column, event) {
            const existingSortIndex = this.sorts.findIndex(s => s.column === column);

            if (event.ctrlKey || event.metaKey) {
                if (existingSortIndex > -1) {
                    this.sorts.splice(existingSortIndex, 1); // Elimina el elemento del arreglo
                    this.fetchPlannings();
                }
                return; // Termina la ejecución aquí
            }            

            if (event.shiftKey) { // Si se presiona Shift, se añade o modifica un orden
                if (existingSortIndex > -1) {
                    // Si ya existe, cambia su dirección
                    this.sorts[existingSortIndex].dir = this.sorts[existingSortIndex].dir === 'asc' ? 'desc' : 'asc';
                } else {
                    // Si no existe, se añade al final
                    this.sorts.push({ column: column, dir: 'asc' });
                }
            } else { // Si no se presiona Shift, es un ordenamiento primario
                if (existingSortIndex > -1) {
                    // Si la columna ya está en el orden, o es la única, cambia su dirección
                    const currentDir = this.sorts[existingSortIndex].dir;
                    this.sorts = [{ column: column, dir: currentDir === 'asc' ? 'desc' : 'asc' }];
                } else {
                    // Si es una columna nueva, se convierte en el único orden
                    this.sorts = [{ column: column, dir: 'asc' }];
                }
            }
            this.fetchPlannings();
        },
        // --- TERMINA CAMBIO ---

        // --- INICIA CAMBIO: Nueva función auxiliar para la vista ---
        getSortState(column) {
            const index = this.sorts.findIndex(s => s.column === column);
            if (index > -1) {
                return { dir: this.sorts[index].dir, priority: index + 1 };
            }
            return null;
        },
        exportToCsv() {
            const cleanFilters = Object.fromEntries(Object.entries(this.filters).filter(([_, v]) => v !== null && v !== '' && v !== 'page'));
            const params = new URLSearchParams(cleanFilters);

            // --- CORRECCIÓN: Usa el nuevo arreglo 'sorts' en lugar de las variables antiguas ---
            if (this.sorts.length > 0) {
                params.append('sorts', JSON.stringify(this.sorts));
            }
            
            window.location.href = `{{ route('customer-service.planning.export-csv') }}?${params.toString()}`;
        },
     

        // --- LÓGICA DE GESTIÓN DE COLUMNAS (NUEVO) ---
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
                filter: '.no-drag',
                onEnd: (evt) => {
                    const movedItem = this.columnOrder.splice(evt.oldIndex-1, 1)[0];
                    this.columnOrder.splice(evt.newIndex-1, 0, movedItem);
                    this.saveColumnSettings();
                }
            });
        },
        initResizers() {
            this.$el.querySelectorAll('th .resizer').forEach(resizer => {
                let header = resizer.parentElement; let startX, startWidth;
                const onMouseMove = (e) => {
                    const newWidth = startWidth + (e.clientX - startX);
                    // **AQUÍ ESTÁ LA CORRECCIÓN**
                    if (newWidth > 20) { // Límite reducido de 50 a 20
                        header.style.width = `${newWidth}px`;
                        this.columnWidths[header.dataset.column] = `${newWidth}px`;
                    }
                };
                const onMouseUp = () => { document.removeEventListener('mousemove', onMouseMove); document.removeEventListener('mouseup', onMouseUp); this.saveColumnSettings(); };
                resizer.addEventListener('mousedown', (e) => { e.preventDefault(); startX = e.clientX; startWidth = header.offsetWidth; document.addEventListener('mousemove', onMouseMove); document.addEventListener('mouseup', onMouseUp); });
            });
        },
        // --- FIN LÓGICA DE GESTIÓN DE COLUMNAS ---

        // --- LÓGICA DE FILTROS AVANZADOS (NUEVO) ---
        applyAdvancedFilters() {
            this.isAdvancedFilterModalOpen = false;
            // El watcher de 'filters' se encargará de llamar a fetchPlannings
            this.filters.page = 1; 
            this.fetchPlannings();
        },
        resetAdvancedFilters() {
             Object.keys(this.filters).forEach(key => {
                if (key.endsWith('_adv')) {
                    this.filters[key] = '';
                }
            });
            this.applyAdvancedFilters();
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
                if (data.success) {
                    this.showFlashMessage(data.message, 'success');
                    this.closeScalesModal();
                    this.fetchPlannings();
                } else {
                    this.showFlashMessage(data.message || 'Ocurrió un error.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.showFlashMessage('Error de conexión al guardar escalas.', 'error');
            });
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
                this.showFlashMessage('Por favor, selecciona al menos un registro.', 'error');
                return;
            }

            // --- LÓGICA DE VALIDACIÓN CORREGIDA ---
            // Convertimos todos los IDs a String para una comparación segura
            const selectedIdsAsStrings = this.selectedPlannings.map(String);
            const selectedRecords = this.plannings.filter(p => selectedIdsAsStrings.includes(String(p.id)));

            const alreadyAssigned = selectedRecords.some(p => p.guia !== null && p.guia !== undefined);

            if (alreadyAssigned) {
                this.showFlashMessage('Una o más órdenes ya tienen guía. Desasígnalas o deselecciónalas para continuar.', 'error');
                return;
            }
            // --- FIN DE LA VALIDACIÓN ---

            // Usamos el primer registro seleccionado como fuente para los datos (tu lógica original)
            const firstSelected = this.plannings.find(p => p.id == this.selectedPlannings[0]);

            if (!firstSelected) {
                // Este error solo debería ocurrir si hay selecciones en otras páginas,
                // pero la validación de arriba ya lo previene para las guías asignadas.
                this.showFlashMessage('No se pudo encontrar el registro principal en la página actual para pre-rellenar datos.', 'error');
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
                method: 'POST', // 1. Especificamos el método POST
                headers: {      // 2. Añadimos las cabeceras necesarias
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                // --- CAMBIO: Usamos la nueva función en lugar de alert() ---
                if (data.success) {
                    this.showFlashMessage(data.message, 'success');
                    this.fetchPlannings();
                } else {
                    this.showFlashMessage(data.message || 'Ocurrió un error.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.showFlashMessage('Error de conexión al marcar como directa.', 'error');
            });
        }        

    }
}
</script>
</x-app-layout>