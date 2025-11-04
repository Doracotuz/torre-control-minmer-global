<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-[#2c3856] leading-tight">
            {{ __('Crear Nueva Orden de Venta (SO)') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="salesOrderForm()" x-init='initData(
        "{{ route('wms.api.search-stock-products') }}",
        @json($qualities),
        @json(session('imported_lines', []))
    )' x-cloak>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Error:</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Éxito:</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
                @php session()->forget('imported_lines'); @endphp
            @endif


            <form method="POST" action="{{ route('wms.sales-orders.store') }}">
                @csrf
                
                <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
                    <h3 class="text-xl font-bold text-[#2c3856] mb-4 border-b pb-2">Detalles de la Orden</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        
                        <div>
                            <label for="so_number" class="block text-sm font-medium text-gray-700">Número de Orden (SO) <span class="text-red-500">*</span></label>
                            <input type="text" name="so_number" id="so_number" value="{{ old('so_number') }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#ff9c00] focus:ring-[#ff9c00] sm:text-sm">
                            @error('so_number') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="customer_name" class="block text-sm font-medium text-gray-700">Nombre del Cliente <span class="text-red-500">*</span></label>
                            <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name') }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#ff9c00] focus:ring-[#ff9c00] sm:text-sm">
                            @error('customer_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label for="warehouse_id" class="block text-sm font-medium text-gray-700">Almacén de Surtido <span class="text-red-500">*</span></label>
                            <select name="warehouse_id" id="warehouse_id" required x-model="warehouse_id" @change="clearAllLines()"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#ff9c00] focus:ring-[#ff9c00] sm:text-sm">
                                <option value="">-- Seleccionar Almacén --</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('warehouse_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="invoice_number" class="block text-sm font-medium text-gray-700">Número de Factura</label>
                            <input type="text" name="invoice_number" id="invoice_number" value="{{ old('invoice_number') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#ff9c00] focus:ring-[#ff9c00] sm:text-sm">
                        </div>

                        <div>
                            <label for="delivery_date" class="block text-sm font-medium text-gray-700">Fecha de Entrega <span class="text-red-500">*</span></label>
                            <input type="date" name="delivery_date" id="delivery_date" value="{{ old('delivery_date', now()->format('Y-m-d')) }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#ff9c00] focus:ring-[#ff9c00] sm:text-sm">
                            @error('delivery_date') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div x-data="{ tab: 'manual' }" class="bg-white shadow-lg rounded-lg p-6">
                    <div class="border-b border-gray-200 mb-4">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                            <button type="button" @click="tab = 'manual'"
                                    :class="tab === 'manual' ? 'border-[#ff9c00] text-[#ff9c00]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Entrada Manual
                            </button>
                            <button type="button" @click="tab = 'plantilla'"
                                    :class="tab === 'plantilla' ? 'border-[#ff9c00] text-[#ff9c00]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Carga por Plantilla
                            </button>
                        </nav>
                    </div>

                    <div x-show="tab === 'manual'">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Líneas de la Orden</h3>
                            <button type="button" @click="addLine()" class="px-3 py-1 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-medium">
                                + Añadir Línea
                            </button>
                        </div>
                        @error('lines') <span class="text-sm text-red-500 mb-4 block">{{ $message }}</span> @enderror

                        <div class="hidden lg:grid lg:grid-cols-12 gap-4 text-xs font-medium text-gray-500 uppercase tracking-wider mb-2 px-4">
                            <span class="lg:col-span-4">Producto (SKU/Nombre/UPC)</span>
                            <span class="lg:col-span-2">Calidad</span>
                            <span class="lg:col-span-1">Qty.</span>
                            <span class="lg:col-span-3">LPN (Opcional)</span>
                            <span class="lg:col-span-2 text-right">Acciones</span>
                        </div>

                        <div class="space-y-4">
                            <template x-if="lines.length === 0">
                                <p class="text-center text-gray-500 py-8">Añade al menos una línea de producto.</p>
                            </template>

                            <template x-for="(line, index) in lines" :key="index">
                                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 p-4 border rounded-lg hover:bg-gray-50">
                                    
                                    <div class="lg:col-span-4 relative">
                                        <label class="block text-sm font-medium text-gray-700 lg:hidden mb-1">Producto</label>
                                        
                                        <input type="hidden" :name="`lines[${index}][product_id]`" x-model.number="line.product_id" required>
                                        
                                        <input type="text"
                                               x-model="line.searchTerm"
                                               @input.debounce.300ms="searchProducts(index)"
                                               @keydown.down.prevent="selectNext(index)"
                                               @keydown.up.prevent="selectPrevious(index)"
                                               @keydown.enter.prevent="selectProduct(index, line.highlightedIndex)"
                                               @click.away="line.searchResults = []"
                                               :placeholder="line.selectedProductName || 'Buscar SKU, Nombre o UPC...'"
                                               :disabled="!warehouse_id"
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#ff9c00] focus:ring-[#ff9c00] sm:text-sm"
                                               :class="{ 'border-red-500': !warehouse_id }">
                                        
                                        <div x-show="line.searchLoading" class="absolute top-2 right-2">
                                            <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </div>
                                        
                                        <div x-show="line.searchResults.length > 0" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-y-auto">
                                            <template x-for="(product, prodIndex) in line.searchResults" :key="product.id">
                                                <div @click="selectProduct(index, prodIndex)"
                                                     @mouseenter="line.highlightedIndex = prodIndex"
                                                     :class="{ 'bg-blue-100': line.highlightedIndex === prodIndex }"
                                                     class="cursor-pointer p-3 hover:bg-blue-50 border-b">
                                                    <p class="font-medium text-gray-900" x-text="product.sku"></p>
                                                    <p class="text-sm text-gray-600" x-text="product.name"></p>
                                                    <p class="text-xs text-gray-400" x-text="product.upc"></p>
                                                </div>
                                            </template>
                                        </div>
                                        <template x-if="!line.searchLoading && line.searchTerm.length > 1 && line.searchResults.length === 0">
                                            <p class="text-xs text-red-500 mt-1">No se encontraron productos con stock en este almacén.</p>
                                        </template>
                                    </div>

                                    <div class="lg:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 lg:hidden mb-1">Calidad</label>
                                        <select x-model="line.quality_id" :name="`lines[${index}][quality_id]`" required
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#ff9c00] focus:ring-[#ff9c00] sm:text-sm"
                                                :disabled="!line.product_id">
                                            <option value="">-- Seleccionar --</option>
                                            <template x-for="quality in qualities" :key="quality.id">
                                                <option :value="quality.id" x-text="quality.name"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <div class="lg:col-span-1">
                                        <label class="block text-sm font-medium text-gray-700 lg:hidden mb-1">Cantidad</label>
                                        <input type="number" x-model.number="line.quantity" :name="`lines[${index}][quantity]`"
                                               min="1" required
                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#ff9c00] focus:ring-[#ff9c00] sm:text-sm"
                                               placeholder="Qty" :disabled="!line.product_id">
                                    </div>

                                    <div class="lg:col-span-3">
                                        <label class="block text-sm font-medium text-gray-700 lg:hidden mb-1">LPN (Opcional)</label>
                                        <input type="text" x-model="line.lpn" :name="`lines[${index}][lpn]`"
                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#ff9c00] focus:ring-[#ff9c00] sm:text-sm"
                                               placeholder="Dejar vacío para automático" :disabled="!line.product_id">
                                    </div>
                                    
                                    <div class="lg:col-span-2 flex items-end justify-end space-x-2">
                                        <button type="button" @click="removeLine(index)" class="text-red-600 hover:text-red-800 p-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div x-show="tab === 'plantilla'" class="text-center">
                        <p class="text-gray-600 mb-4">Carga las líneas de la orden usando una plantilla CSV. Esto reemplazará cualquier línea de la "Entrada Manual".</p>
                        
                        <form action="{{ route('wms.sales-orders.import-new') }}" method="POST" enctype="multipart/form-data" class="space-y-4 max-w-lg mx-auto">
                            @csrf
                            <div>
                                <label for="file" class="block text-sm font-medium text-gray-700">Archivo CSV</label>
                                <input type="file" name="file" id="file" required class="mt-1 block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-full file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100
                                ">
                            </div>
                            
                            <div class="flex items-center justify-center space-x-4">
                                <a href="{{ route('wms.sales-orders.template') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-download mr-1"></i> Descargar Plantilla
                                </a>
                                <button type="submit" class="inline-flex items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                    <i class="fas fa-upload mr-2"></i> Cargar Archivo
                                </button>
                            </div>
                        </form>
                    </div>

                </div>

                <div class="mt-6 flex justify-end">
                    <a href="{{ route('wms.sales-orders.index') }}" class="mr-4 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancelar
                    </a>
                    <button type="submit" 
                            :disabled="!warehouse_id || lines.length === 0 || lines.some(l => !l.product_id || !l.quality_id || !l.quantity)"
                            class="inline-flex items-center justify-center px-6 py-2 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-[#2c3856] hover:bg-[#1f2940] disabled:bg-gray-400">
                        Crear Orden de Venta
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function salesOrderForm() {
            return {
                warehouse_id: '{{ old('warehouse_id') }}' || '',
                apiSearchUrl: '',
                qualities: [],
                lines: [], // <-- 'lines' está definido
                searchTimeout: null,

                // Nueva función para inicializar de forma segura
                initData(apiSearchUrl, qualities, importedLines) {
                    this.apiSearchUrl = apiSearchUrl;
                    this.qualities = qualities;
                    
                    if (importedLines && importedLines.length > 0) {
                        this.lines = importedLines.map(line => ({
                            product_id: line.product_id,
                            quality_id: line.quality_id,
                            quantity: line.quantity_ordered,
                            lpn: line.pallet_item_id ? 'LPN-PRE-ASIGNADO' : '',
                            searchTerm: '',
                            selectedProductName: `Importado: ${line.product_id}`, // Mejorable
                            searchResults: [],
                            searchLoading: false,
                            highlightedIndex: -1
                        }));
                    } else {
                        this.lines = [this.createNewLine()];
                    }
                },

                // Crea la estructura de una nueva línea
                createNewLine() {
                    return {
                        product_id: '',
                        quality_id: '',
                        quantity: '',
                        lpn: '',
                        searchTerm: '',
                        selectedProductName: '', 
                        searchResults: [],
                        searchLoading: false,
                        highlightedIndex: -1
                    };
                },

                // Añade una nueva línea
                addLine() {
                    this.lines.push(this.createNewLine());
                },

                // Elimina una línea
                removeLine(index) {
                    this.lines.splice(index, 1);
                },

                // Limpia las líneas si se cambia el almacén
                clearAllLines() {
                    if (this.lines.length > 0 && this.lines[0].product_id) {
                        if (confirm('¿Cambiar de almacén? Esto borrará todas las líneas de pedido actuales.')) {
                            this.lines = [this.createNewLine()];
                        } else {
                            // Este es un truco para revertir el <select> si el usuario dice "Cancelar"
                            // Necesitaríamos almacenar el 'old_warehouse_id' para que funcione 100%
                        }
                    }
                },

                // Busca productos con stock
                searchProducts(index) {
                    const line = this.lines[index];
                    
                    // Limpia el producto seleccionado si la búsqueda cambia
                    line.product_id = '';
                    line.selectedProductName = '';
                    line.highlightedIndex = -1;

                    if (line.searchTerm.length < 2) {
                        line.searchResults = [];
                        line.searchLoading = false;
                        return;
                    }
                    if (!this.warehouse_id) {
                        alert('Por favor, selecciona un almacén de surtido primero.');
                        line.searchTerm = '';
                        line.searchLoading = false;
                        return;
                    }

                    line.searchLoading = true;
                    
                    if(this.searchTimeout) {
                        clearTimeout(this.searchTimeout);
                    }

                    this.searchTimeout = setTimeout(() => {
                        fetch(`${this.apiSearchUrl}?query=${line.searchTerm}&warehouse_id=${this.warehouse_id}`, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(response => {
                            if (!response.ok) throw new Error('Error de red');
                            return response.json();
                        })
                        .then(data => {
                            line.searchResults = data;
                            line.searchLoading = false;
                        })
                        .catch(() => {
                            line.searchLoading = false;
                            line.searchResults = [];
                        });
                    }, 300); // Debounce de 300ms
                },

                // Selecciona un producto del dropdown
                selectProduct(lineIndex, resultIndex) {
                    const line = this.lines[lineIndex];
                    if (!line.searchResults[resultIndex]) return;

                    const product = line.searchResults[resultIndex];
                    line.product_id = product.id;
                    line.selectedProductName = `${product.sku} | ${product.name}`;
                    line.searchTerm = `${product.sku} | ${product.name}`; // Pone el texto en el input
                    line.searchResults = []; // Cierra el dropdown
                    line.highlightedIndex = -1;
                },

                // Navegación con teclado
                selectNext(lineIndex) {
                    const line = this.lines[lineIndex];
                    if (line.highlightedIndex < line.searchResults.length - 1) {
                        line.highlightedIndex++;
                    }
                },
                selectPrevious(lineIndex) {
                    const line = this.lines[lineIndex];
                    if (line.highlightedIndex > 0) {
                        line.highlightedIndex--;
                    }
                } // <--- ESTA VEZ LA COMA ESTÁ ELIMINADA.
            }
        }
    </script>
</x-app-layout>