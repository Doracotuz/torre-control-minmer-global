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
                <button @click="isColumnModalOpen = true" class="text-sm text-blue-600 hover:underline">Personalizar Columnas</button>
                <button @click="isImportModalOpen = true" class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-blue-700">Carga de SO</button>

            </div>

            <div id="orders-table-container" class="overflow-x-auto">
                @include('customer-service.orders.partials.table')
            </div>

            @include('customer-service.orders.partials._column-selector-modal')
            @include('customer-service.orders.partials._import-modal')
        </div>
    </div>
</x-app-layout>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<style>
    .resizer {
        position: absolute;
        right: 0;
        top: 0;
        height: 100%;
        width: 5px;
        background: transparent;
        z-index: 10;
        touch-action: none;
        cursor: ew-resize; /* Cursor para indicar redimensionamiento */
    }
    .resizing {
        background: #ff9c00;
    }
    th {
        position: relative;
    }
    .drag-handle {
        cursor: move;
        display: block;
        padding: 4px;
        user-select: none;
    }
</style>

<script>
    function orderManager() {
        return {
            isLoading: true,
            isColumnModalOpen: false,
            isImportModalOpen: false,
            filters: { page: 1, search: '', status: '', channel: '', date_from: '', date_to: '' },
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

                this.columnOrder = savedOrder ? JSON.parse(savedOrder) : defaultOrder;
                this.columnWidths = savedWidths ? JSON.parse(savedWidths) : {};
                
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
                        const date = new Date(value);
                        if (isNaN(date.getTime())) return '—';
                        return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
                    }
                    if (columnKey === 'subtotal') return '$' + new Intl.NumberFormat('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
                    if (columnKey === 'total_boxes') return new Intl.NumberFormat('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
                    const longTextColumns = ['customer_name', 'shipping_address', 'observations'];
                    if (longTextColumns.includes(columnKey)) return String(value).substring(0, 35) + (String(value).length > 35 ? '...' : '');
                    return value;
                } catch (e) {
                    console.error(`Error formateando la columna '${columnKey}' con el valor:`, value, e);
                    return 'Error';
                }
            }
        }
    }
</script>