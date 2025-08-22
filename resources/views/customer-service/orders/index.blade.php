<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Pedidos</h2>
            <div>
                <a href="{{ route('customer-service.orders.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-blue-700">Crear Pedido</a>
                <a href="{{ route('customer-service.orders.dashboard') }}" class="ml-4 px-4 py-2 bg-gray-800 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-black">Dashboard</a>
                <!-- <button @click="isImportModalOpen = true" class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-blue-700">Carga de SO</button> -->
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="orderManager()" x-cloak>
        <div class="mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert"><p>{{ session('success') }}</p></div>@endif
            @if(session('error'))<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert"><p>{{ session('error') }}</p></div>@endif
            @if(session('warning'))<div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6" role="alert"><p>{{ session('warning') }}</p></div>@endif

            @if(session()->has('import_error_rows') && !empty(session('import_error_rows')))
                @php
                    $errorRows = session('import_error_rows');
                    $errorHeaders = array_keys($errorRows[0]);
                @endphp
                <div x-data="{ show: true }" x-show="show" x-transition class="mb-6 bg-red-50 border border-red-200 p-4 rounded-lg relative">
                    <div class="flex justify-between items-center mb-2">
                        <div>
                            <h3 class="text-lg font-bold text-red-800">Errores en la Importación</h3>
                            <p class="text-red-600 text-sm">Los siguientes registros no pudieron ser importados. Por favor, corrige los datos y vuelve a intentarlo.</p>
                        </div>
                        <div class="flex items-center gap-4">
                             <a href="{{ route('customer-service.orders.download-errors') }}" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-red-700">
                                <i class="fas fa-file-download mr-2"></i>Descargar Reporte
                            </a>
                            <button @click="show = false" class="text-red-500 hover:text-red-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </div>
                    <div class="max-h-60 overflow-x-auto mt-4">
                        <table class="min-w-full text-sm">
                            <thead class="bg-red-100">
                                <tr>
                                    @foreach($errorHeaders as $header)
                                        <th class="px-2 py-1 text-center font-semibold text-red-900">{{ str_replace('_', ' ', $header) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                @foreach($errorRows as $errorRow)
                                <tr>
                                    @foreach($errorRow as $value)
                                        <td class="border-t px-2 py-1">{{ $value }}</td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="bg-white p-6 rounded-lg shadow-lg mb-6">
                @include('customer-service.orders.partials._filters')
            </div>

            <div class="flex justify-between items-center mb-4">
                <span class="text-sm text-gray-500" x-show="isLoading">Cargando datos...</span>
                <div class="flex items-center space-x-4">
                    <button @click="isColumnModalOpen = true" class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-gray-700">
                        <i class="fas fa-columns mr-2"></i>Seleccionar Columnas
                    </button>
                </div>
                <div class="flex items-center space-x-4">
                    <button @click="isImportModalOpen = true" class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-gray-700">
                        <i class="fas fa-file-import mr-2"></i>Carga de SO
                    </button>
                </div>

            </div>
            <div class="flex justify-between items-center mb-4">
                {{-- Barra de acciones para selección --}}
                <div x-show="selectedOrders.length > 0" class="bg-gray-800 text-white p-3 rounded-lg shadow-lg flex justify-between items-center transition-transform w-full" x-transition>
                    <span x-text="`(${selectedOrders.length}) órdenes seleccionadas.`"></span>
                    <button @click="bulkEdit()" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold hover:bg-indigo-700">
                        <i class="fas fa-edit mr-2"></i>Editar Selección
                    </button>
                </div>
            </div>
            <div id="orders-table-container">
                @include('customer-service.orders.partials.table')
            </div>
            <div x-show="isLoading" class="text-center py-10">
                <i class="fas fa-spinner fa-spin text-4xl text-gray-500"></i>
                <p class="mt-2 text-gray-600">Cargando datos...</p>
            </div>
            @include('customer-service.orders.partials._column-selector-modal')
            @include('customer-service.orders.partials._import-modal')
            @include('customer-service.orders.partials._advanced-filters-modal')
        </div>
    </div>
</x-app-layout>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<style>
    /* --- INICIA CÓDIGO CORREGIDO Y MEJORADO --- */

    /* Reglas generales del redimensionador (se mantienen) */
    th { position: relative; }
    .resizer {
        position: absolute; right: 0; top: 0; height: 100%;
        width: 5px; background: transparent; z-index: 10; cursor: ew-resize;
    }
    .resizing { background: #ff9c00; }
    
    /* Encabezados: Permite que el texto se divida en varias líneas si no cabe */
    th .drag-handle {
        cursor: move; display: block; padding: 4px; user-select: none;
        white-space: normal; overflow-wrap: normal;
    }

    /* Celdas: Prepara el terreno para el truncado de texto */
    td { max-width: 0; }

    /* Contenedor de la Tabla: Se le quita el scroll y se permite que su contenido se desborde */
    #orders-table-container {
        overflow: visible !important; 
    }

    /* Columna de Acciones: Se le asigna un ancho mínimo fijo */
    .actions-column {
        width: 160px !important;      /* Ancho fijo */
        min-width: 160px !important; /* Ancho mínimo fijo */
    }
    
    /* --- TERMINA CÓDIGO CORREGIDO Y MEJORADO --- */
</style>

<script>
    function orderManager() {
        return {
            isLoading: true,
            isColumnModalOpen: false,
            isImportModalOpen: false,
            isAdvancedFilterModalOpen: false,
            advancedFilterCount: 0,            
            filters: { 
                page: 1, search: '', status: '', date_from: '', date_to: '',
                purchase_order_adv: '', bt_oc: '', customer_name_adv: '', channel: '',
                invoice_number_adv: '', invoice_date: '', origin_warehouse: '', 
                destination_locality: '', delivery_date: '', executive: '', 
                evidence_reception_date: '', evidence_cutoff_date: ''
            },
            visibleColumns: {},
            columnOrder: [],
            columnWidths: {},
            allColumns: {
                purchase_order: 'Orden Compra', bt_oc: 'BT OC', so_number: 'SO', customer_name: 'Razón Social', status: 'Estatus',
                creation_date: 'F. Creación', authorization_date: 'F. Autorización', channel: 'Canal',
                invoice_number: 'Factura', invoice_date: 'F. Factura',
                origin_warehouse: 'Almacén Origen', destination_locality: 'Localidad Destino',
                total_bottles: 'Botellas', total_boxes: 'Cajas', subtotal: 'Subtotal',
                delivery_date: 'F. Entrega', schedule: 'Horario', client_contact: 'Contacto',
                shipping_address: 'Dirección', executive: 'Ejecutivo', observations: 'Observaciones',
                evidence_reception_date: 'Recep. Evidencia', evidence_cutoff_date: 'Corte Evidencia',
            },
            orders: [],
            pagination: { currentPage: 1, lastPage: 1, links: [], total: 0, from: 0, to: 0 },
            resizerCleanups: [],
            selectedOrders: [],
            
            resizingState: {
                isResizing: false,
                currentHeader: null,
                startX: 0,
                startW: 0,
            },

            init() {
                const defaultOrder = Object.keys(this.allColumns);
                const savedOrder = localStorage.getItem('csOrderColumnOrder');
                const savedWidths = localStorage.getItem('csOrderColumnWidths');
                const savedVisible = localStorage.getItem('csOrderVisibleColumns');

                this.$watch('filters', () => this.updateAdvancedFilterCount());
                this.updateAdvancedFilterCount();
                this.columnOrder = savedOrder ? JSON.parse(savedOrder) : defaultOrder;
                this.columnWidths = savedWidths ? JSON.parse(savedWidths) : {};
                this.$el.addEventListener('toggle-all-orders', (e) => {
                    this.toggleAllOrders(e.detail);
                });                
                
                let visible = savedVisible ? JSON.parse(savedVisible) : {};
                defaultOrder.forEach(key => {
                    if (typeof visible[key] === 'undefined') {
                        visible[key] = defaultOrder.indexOf(key) < 8;
                    }
                });
                this.visibleColumns = visible;

                this.applyFilters();
                
                this.$watch('filters', () => this.applyFilters(true), { deep: true });
                this.$watch('visibleColumns', (val) => {
                    localStorage.setItem('csOrderVisibleColumns', JSON.stringify(val));
                    this.$nextTick(() => this.reinitTableInteractions());
                }, { deep: true });
                this.$watch('columnOrder', (val) => {
                    localStorage.setItem('csOrderColumnOrder', JSON.stringify(val));
                    this.$nextTick(() => this.reinitTableInteractions());
                });
                this.$watch('columnWidths', (val) => localStorage.setItem('csOrderColumnWidths', JSON.stringify(val)), { deep: true });
            },

            applyFilters(resetPage = false) {
                if (resetPage) this.filters.page = 1;
                this.isLoading = true;
                const params = new URLSearchParams(this.filters);
                
                fetch(`{{ route('customer-service.orders.filter') }}?${params.toString()}`)
                    .then(response => response.json())
                    .then(data => {
                        this.orders = data.data;
                        this.pagination = {
                            currentPage: data.current_page, lastPage: data.last_page,
                            links: data.links, total: data.total,
                            from: data.from, to: data.to
                        };
                        this.isLoading = false;
                        this.$nextTick(() => this.reinitTableInteractions());
                    })
                    .catch(() => {
                        this.isLoading = false; this.orders = [];
                        alert('Error al cargar los datos.');
                    });
            },

            changePage(page) {
                if (page && page !== this.filters.page) this.filters.page = page;
            },

            reinitTableInteractions() {
                setTimeout(() => {
                    this.initDraggableColumns();
                    this.initResizableColumns();
                }, 100);
            },
            
            initDraggableColumns() {
                const tableHeader = document.querySelector('#orders-table-container thead tr');
                if (!tableHeader) return;
                if (tableHeader.sortable && typeof tableHeader.sortable.destroy === 'function') {
                    tableHeader.sortable.destroy();
                }
                tableHeader.sortable = new Sortable(tableHeader, {
                    animation: 150,
                    handle: '.drag-handle',
                    filter: '.resizer',
                    preventOnFilter: false,
                    onEnd: (evt) => {
                        const newOrder = Array.from(evt.target.children).map(th => th.dataset.column).filter(key => key);
                        this.columnOrder = newOrder;
                    }
                });
            },

            initResizableColumns() {
                this.resizerCleanups.forEach(cleanup => cleanup());
                this.resizerCleanups = [];
                document.querySelectorAll('#orders-table-container .resizer').forEach(resizer => {
                    const onMouseDown = (e) => this.startResize(e);
                    resizer.addEventListener('mousedown', onMouseDown);
                    this.resizerCleanups.push(() => resizer.removeEventListener('mousedown', onMouseDown));
                });
            },

            startResize(e) {
                e.stopPropagation();
                this.resizingState.isResizing = true;
                this.resizingState.currentHeader = e.target.parentNode;
                this.resizingState.startX = e.clientX;
                this.resizingState.startW = parseInt(window.getComputedStyle(e.target.parentNode).width, 10);

                const onMouseMove = this.doResize.bind(this);
                const onMouseUp = this.stopResize.bind(this);
                
                document.addEventListener('mousemove', onMouseMove);
                document.addEventListener('mouseup', onMouseUp);
                
                e.target.classList.add('resizing');
                document.body.style.userSelect = 'none';
                document.body.style.cursor = 'ew-resize';
            },

            doResize(e) {
                if (!this.resizingState.isResizing) return;
                const newWidth = this.resizingState.startW + (e.clientX - this.resizingState.startX);
                this.resizingState.currentHeader.style.width = `${newWidth}px`;
            },

            stopResize(e) {
                if (!this.resizingState.isResizing) return;
                this.resizingState.currentHeader.querySelector('.resizer').classList.remove('resizing');
                if (this.resizingState.currentHeader.dataset.column) {
                    this.columnWidths[this.resizingState.currentHeader.dataset.column] = this.resizingState.currentHeader.style.width;
                }
                this.resizingState.isResizing = false;
                this.resizingState.currentHeader = null;
                
                document.removeEventListener('mousemove', this.doResize.bind(this));
                document.removeEventListener('mouseup', this.stopResize.bind(this));
                
                document.body.style.userSelect = '';
                document.body.style.cursor = '';
            },

            getFormattedCell(order, columnKey) {
                const value = order[columnKey];
                if (value === null || typeof value === 'undefined' || value === '') return '—';
                
                try {
                    const dateColumns = ['creation_date', 'authorization_date', 'invoice_date', 'delivery_date', 'evidence_reception_date', 'evidence_cutoff_date'];
                    if (dateColumns.includes(columnKey)) {
                        // Formateo de fechas
                        const date = new Date(value);
                        if (isNaN(date.getTime())) return '—';
                        return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
                    }
                    if (columnKey === 'subtotal') {
                        // Formateo de moneda
                        return '$' + new Intl.NumberFormat('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
                    }
                    if (columnKey === 'total_boxes') {
                        // Formateo de números enteros
                        return new Intl.NumberFormat('es-MX', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
                    }
                    
                    // --- CAMBIO CLAVE: Se elimina el bloque que cortaba el texto ---
                    // Ahora, simplemente devolvemos el valor original para todas las demás columnas.
                    return value;

                } catch (e) {
                    console.error(`Error formateando la columna '${columnKey}' con el valor:`, value, e);
                    return 'Error';
                }
            },

            updateAdvancedFilterCount() {
                // CORRECCIÓN: Se actualiza la lista de filtros a contar
                const advancedKeys = [
                    'purchase_order_adv', 'bt_oc', 'customer_name_adv', 'channel',
                    'invoice_number_adv', 'invoice_date', 'origin_warehouse',
                    'destination_locality', 'delivery_date', 'executive', 
                    'evidence_reception_date', 'evidence_cutoff_date'
                ];
                this.advancedFilterCount = advancedKeys.filter(key => this.filters[key] && this.filters[key] !== '').length;
            },
            clearAdvancedFilters() {
                // CORRECCIÓN: Se limpian todos los nuevos filtros
                this.filters.purchase_order_adv = '';
                this.filters.bt_oc = '';
                this.filters.customer_name_adv = '';
                this.filters.channel = '';
                this.filters.invoice_number_adv = '';
                this.filters.invoice_date = '';
                this.filters.origin_warehouse = '';
                this.filters.destination_locality = '';
                this.filters.delivery_date = '';
                this.filters.executive = '';
                this.filters.evidence_reception_date = '';
                this.filters.evidence_cutoff_date = '';
            },

            toggleAllOrders(checked) {
                if (checked) {
                    this.selectedOrders = this.orders.map(o => o.id);
                } else {
                    this.selectedOrders = [];
                }
            },
            bulkEdit() {
                if (this.selectedOrders.length === 0) {
                    alert('Por favor, selecciona al menos una orden para editar.');
                    return;
                }
                const params = new URLSearchParams();
                this.selectedOrders.forEach(id => params.append('ids[]', id));
                window.location.href = `{{ route('customer-service.orders.bulk-edit') }}?${params.toString()}`;
            }

        }
    }
</script>