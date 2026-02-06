<x-app-layout>
    <x-slot name="header"></x-slot>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Raleway:wght@800;900&display=swap');
        
        .font-raleway { font-family: 'Raleway', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        
        .stagger-enter { opacity: 0; transform: translateY(20px); animation: enterUp 0.5s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; }
        @keyframes enterUp { to { opacity: 1; transform: translateY(0); } }
        
        .input-arch {
            background: transparent; border: none; border-bottom: 2px solid #e5e7eb; border-radius: 0;
            padding: 0.8rem 0; font-family: 'Montserrat', sans-serif; font-weight: 600; color: #2c3856;
            transition: all 0.3s ease; width: 100%; font-size: 1rem;
        }
        .input-arch:focus { border-bottom-color: #ff9c00; box-shadow: none; outline: none; }
        .input-arch-select { background-image: none; cursor: pointer; padding-right: 1.5rem; }

        .btn-nexus { 
            background: #2c3856; color: white; border-radius: 1rem; font-weight: 700;
            transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center;
        }
        .btn-nexus:hover { background: #1a253a; transform: translateY(-2px); box-shadow: 0 10px 20px -5px rgba(44, 56, 86, 0.2); }
        
        .btn-ghost {
            background: transparent; color: #2c3856; border: 2px solid #e5e7eb; border-radius: 1rem; font-weight: 700;
        }
        .btn-ghost:hover { border-color: #2c3856; background: #2c3856; color: white; }

        [x-cloak] { display: none !important; }
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20 relative overflow-hidden">
        
        <div class="fixed inset-0 -z-10 pointer-events-none">
            <div class="absolute top-0 right-0 w-[50vw] h-full bg-gradient-to-l from-[#f8fafc] to-transparent"></div>
            <div class="absolute bottom-0 left-0 w-[40rem] h-[40rem] bg-[#ff9c00]/5 rounded-full blur-[120px]"></div>
        </div>

        <script id="so-edit-data" type="application/json">
        {
            "apiSearchUrl": "{{ route('wms.api.search-stock-products') }}",
            "apiQualitiesUrl": "{{ route('wms.api.get-available-qualities') }}",
            "qualities": @json($qualities),
            "oldWarehouseId": "{{ old('warehouse_id', $salesOrder->warehouse_id) }}",
            "oldAreaId": "{{ old('area_id', $salesOrder->area_id) }}",
            "existingLines": @json($salesOrder->lines->load('product', 'palletItem.pallet'))
        }
        </script>

        <div class="max-w-7xl mx-auto px-6 pt-10 relative z-10" x-data="salesOrderForm()" x-init="initData()" x-cloak>
            
            <div class="flex flex-col md:flex-row justify-between items-end mb-10 stagger-enter" style="animation-delay: 0.1s;">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-12 h-1 bg-[#ff9c00]"></span>
                        <span class="text-sm font-bold text-[#2c3856] tracking-[0.3em] uppercase">Modo Edición</span>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-raleway font-black text-[#2c3856] leading-none">
                        Orden <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-600">{{ $salesOrder->so_number }}</span>
                    </h1>
                </div>
                <div class="mt-4 md:mt-0 flex gap-3">
                    <button @click="$refs.importInput.click()" class="btn-ghost px-6 py-3 text-sm uppercase tracking-wider">
                        <i class="fas fa-file-upload mr-2"></i> Actualizar via CSV
                    </button>
                    <a href="{{ route('wms.sales-orders.show', $salesOrder) }}" class="btn-ghost px-6 py-3 text-sm uppercase tracking-wider border-red-200 text-red-600 hover:bg-red-50">
                        Cancelar
                    </a>
                </div>
            </div>

            <form action="{{ route('wms.sales-orders.import-update', $salesOrder) }}" method="POST" enctype="multipart/form-data" class="hidden">
                @csrf
                <input type="file" name="file" x-ref="importInput" accept=".csv,.txt" onchange="this.form.submit()">
            </form>

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-xl font-bold flex items-center gap-3 stagger-enter">
                    <i class="fas fa-exclamation-circle text-xl"></i>
                    {{ session('error') }}
                </div>
            @endif
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-r-xl font-bold flex items-center gap-3 stagger-enter">
                    <i class="fas fa-check-circle text-xl"></i>
                    {{ session('success') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-xl stagger-enter">
                    <ul class="list-disc list-inside text-sm font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('wms.sales-orders.update', $salesOrder) }}" id="edit-so-form" class="stagger-enter" style="animation-delay: 0.2s;">
                @csrf
                @method('PUT')
                
                {{-- Bloque Detalles Generales --}}
                <div class="bg-white rounded-[2.5rem] shadow-xl shadow-[#2c3856]/5 border border-gray-100 p-10 mb-8">
                    <h3 class="text-xl font-raleway font-black text-[#2c3856] mb-8 border-b border-gray-100 pb-4">Detalles Generales</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Número de Orden</label>
                            <input type="text" name="so_number" value="{{ old('so_number', $salesOrder->so_number) }}" required class="input-arch text-lg" placeholder="SO-...">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Factura (Opcional)</label>
                            <input type="text" name="invoice_number" value="{{ old('invoice_number', $salesOrder->invoice_number) }}" class="input-arch text-lg" placeholder="F-0000">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Fecha de Entrega</label>
                            <input type="date" name="delivery_date" value="{{ old('delivery_date', $salesOrder->order_date->format('Y-m-d')) }}" required class="input-arch text-lg">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Cliente Final</label>
                            <input type="text" name="customer_name" value="{{ old('customer_name', $salesOrder->customer_name) }}" required class="input-arch text-lg" placeholder="Nombre del cliente">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Almacén de Surtido</label>
                            <select name="warehouse_id" required x-model="warehouse_id" @change="clearAllLines()" class="input-arch input-arch-select text-lg">
                                <option value="">Seleccionar...</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-[#ff9c00] uppercase tracking-widest mb-1">Área / Proyecto</label>
                            <select name="area_id" x-model="area_id" @change="clearAllLines()" class="input-arch input-arch-select text-[#ff9c00] text-lg font-bold">
                                <option value="">General</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Bloque Líneas --}}
                <div x-data="{ tab: 'manual' }" class="bg-white rounded-[2.5rem] shadow-xl shadow-[#2c3856]/5 border border-gray-100 p-10">
                    <div class="flex gap-6 border-b border-gray-100 mb-8">
                        <button type="button" @click="tab = 'manual'"
                                :class="tab === 'manual' ? 'text-[#ff9c00] border-[#ff9c00]' : 'text-gray-400 border-transparent hover:text-[#2c3856]'"
                                class="pb-4 border-b-2 font-bold text-sm uppercase tracking-widest transition-colors">
                            Edición Manual
                        </button>
                        <button type="button" @click="tab = 'plantilla'"
                                :class="tab === 'plantilla' ? 'text-[#ff9c00] border-[#ff9c00]' : 'text-gray-400 border-transparent hover:text-[#2c3856]'"
                                class="pb-4 border-b-2 font-bold text-sm uppercase tracking-widest transition-colors">
                            Carga por Plantilla
                        </button>
                    </div>

                    <div x-show="tab === 'manual'">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-raleway font-black text-[#2c3856]">Líneas de la Orden</h3>
                            <button type="button" @click="addLine()" class="btn-ghost px-4 py-2 text-xs uppercase tracking-widest">
                                <i class="fas fa-plus mr-1"></i> Añadir Línea
                            </button>
                        </div>

                        <div class="space-y-4">
                            <template x-if="lines.length === 0">
                                <div class="text-center py-12 text-gray-400 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                                    <i class="fas fa-box-open text-3xl mb-2 opacity-50"></i>
                                    <p class="text-sm font-bold">No hay líneas agregadas.</p>
                                </div>
                            </template>

                            <template x-for="(line, index) in lines" :key="index">
                                <div class="p-6 bg-gray-50 rounded-2xl border border-gray-100 relative group transition-all hover:shadow-md hover:border-blue-100">
                                    <button type="button" @click="removeLine(index)" class="absolute top-4 right-4 text-gray-300 hover:text-red-500 transition-colors">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    
                                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-end">
                                        <div class="lg:col-span-2">
                                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Calidad</label>
                                            
                                            <select :name="`lines[${index}][quality_id]`" x-model="line.quality_id" required 
                                                    @change="onQualityChange(index)"
                                                    class="input-arch input-arch-select text-sm"
                                                    :disabled="!warehouse_id">
                                                <option value="">Seleccionar...</option>
                                                <template x-for="quality in qualities" :key="quality.id">
                                                    {{-- CORRECCIÓN: Forzamos el value a String para que coincida con el modelo --}}
                                                    <option :value="String(quality.id)" x-text="quality.name"></option>
                                                </template>
                                            </select>
                                        </div>
                                        
                                        <div class="lg:col-span-5 relative">
                                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Producto</label>
                                            <input type="hidden" :name="`lines[${index}][product_id]`" x-model.number="line.product_id" required>
                                            
                                            <input type="text"
                                                   x-model="line.searchTerm"
                                                   @input.debounce.300ms="searchProducts(index)"
                                                   @keydown.down.prevent="selectNext(index)"
                                                   @keydown.up.prevent="selectPrevious(index)"
                                                   @keydown.enter.prevent="selectProduct(index, line.highlightedIndex)"
                                                   @click.away="line.searchResults = []"
                                                   :placeholder="line.selectedProductName || 'Buscar SKU o Nombre...'"
                                                   :disabled="!line.quality_id"
                                                   class="input-arch text-sm"
                                                   :class="{ 'border-red-500': !line.quality_id }">
                                            
                                            <div x-show="line.searchLoading" class="absolute right-0 bottom-3 text-[#ff9c00]">
                                                <i class="fas fa-circle-notch fa-spin"></i>
                                            </div>
                                            
                                            <div x-show="line.searchResults.length > 0" class="absolute z-20 w-full bg-white border border-gray-100 rounded-xl shadow-xl mt-1 max-h-60 overflow-y-auto">
                                                <template x-for="(product, prodIndex) in line.searchResults" :key="product.id">
                                                    <div @click="selectProduct(index, prodIndex)"
                                                         @mouseenter="line.highlightedIndex = prodIndex"
                                                         :class="{ 'bg-blue-50': line.highlightedIndex === prodIndex }"
                                                         class="cursor-pointer p-3 border-b border-gray-50 last:border-0 hover:bg-blue-50 transition-colors">
                                                        <div class="flex justify-between items-center">
                                                            <span class="font-bold text-[#2c3856] text-xs" x-text="product.sku"></span>
                                                            <span class="font-black text-green-600 text-xs" x-text="`Disp: ${product.total_available}`"></span>
                                                        </div>
                                                        <p class="text-[10px] text-gray-500 mt-1 uppercase" x-text="product.name"></p>
                                                    </div>
                                                </template>
                                            </div>
                                            
                                            <template x-if="!line.searchLoading && line.searchTerm.length > 1 && line.searchResults.length === 0 && !line.product_id">
                                                <p class="absolute text-[10px] text-red-500 mt-1 font-bold">Sin resultados con stock disponible.</p>
                                            </template>
                                        </div>

                                        <div class="lg:col-span-1">
                                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1 text-center">Disponible</label>
                                            <div class="text-center font-mono font-bold text-gray-500 py-2 border-b-2 border-gray-200" x-text="line.total_available"></div>
                                        </div>

                                        <div class="lg:col-span-2">
                                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Cantidad</label>
                                            <input type="number" x-model.number="line.quantity" :name="`lines[${index}][quantity]`"
                                                   min="1" :max="line.total_available + line.quantity" required
                                                   class="input-arch text-center font-black text-[#2c3856] text-lg"
                                                   placeholder="0" :disabled="!line.product_id">
                                        </div>

                                        <div class="lg:col-span-2">
                                            <label class="block text-[10px] font-bold text-blue-400 uppercase tracking-widest mb-1">LPN (Opcional)</label>
                                            <input type="text" x-model="line.lpn" :name="`lines[${index}][lpn]`"
                                                   class="input-arch text-xs font-mono text-blue-600"
                                                   placeholder="Automático" :disabled="!line.product_id">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div x-show="tab === 'plantilla'" class="text-center py-10">
                        <div class="max-w-lg mx-auto bg-gray-50 rounded-[2rem] p-10 border border-dashed border-gray-300">
                            <i class="fas fa-file-csv text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-600 mb-6 font-medium">Carga masiva para reemplazar todas las líneas actuales.</p>
                            
                            <form action="{{ route('wms.sales-orders.import-update', $salesOrder) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                                @csrf
                                <input type="hidden" name="warehouse_id" :value="warehouse_id">
                                <input type="hidden" name="area_id" :value="area_id">
                                
                                <div class="relative">
                                    <input type="file" name="file" id="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                    <div class="btn-ghost w-full py-4 text-xs uppercase tracking-widest">
                                        Seleccionar Archivo
                                    </div>
                                </div>
                                
                                <div class="flex justify-center gap-4">
                                    <a href="{{ route('wms.sales-orders.template') }}" class="text-xs font-bold text-[#2c3856] hover:text-[#ff9c00] border-b border-[#2c3856] hover:border-[#ff9c00]">
                                        Descargar Plantilla
                                    </a>
                                    <button type="submit" class="btn-nexus px-6 py-2 text-xs uppercase tracking-widest shadow-lg">
                                        Actualizar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="mt-10 pt-6 border-t border-gray-100 flex justify-end">
                        <button type="submit" 
                                form="edit-so-form"
                                :disabled="!warehouse_id || lines.length === 0 || lines.some(l => !l.product_id || !l.quality_id || !l.quantity)"
                                class="btn-nexus px-10 py-4 text-sm uppercase tracking-widest shadow-xl shadow-[#2c3856]/20 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-sync-alt mr-2"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function salesOrderForm() {
            return {
                warehouse_id: '',
                area_id: '',
                apiSearchUrl: '',
                apiQualitiesUrl: '',
                qualities: [], 
                availableQualities: [],
                lines: [],
                searchTimeout: null,

                initData() {
                    const data = JSON.parse(document.getElementById('so-edit-data').textContent);
                    this.apiSearchUrl = data.apiSearchUrl;
                    this.apiQualitiesUrl = data.apiQualitiesUrl;
                    this.qualities = data.qualities; 
                    this.warehouse_id = data.oldWarehouseId || '';
                    this.area_id = data.oldAreaId || '';
                    
                    if (data.existingLines && data.existingLines.length > 0) {
                        this.lines = data.existingLines.map(line => ({
                            product_id: line.product_id,
                            quality_id: String(line.quality_id), 
                            quantity: line.quantity_ordered,
                            lpn: line.pallet_item && line.pallet_item.pallet ? line.pallet_item.pallet.lpn : null,
                            searchTerm: `${line.product.sku} | ${line.product.name}`,
                            selectedProductName: `${line.product.sku} | ${line.product.name}`,
                            searchResults: [],
                            searchLoading: false,
                            highlightedIndex: -1,
                            total_available: line.calculated_available ?? 0 
                        }));
                    } else {
                        this.lines = [this.createNewLine()];
                    }
                },

                createNewLine() {
                    return {
                        product_id: '',
                        quality_id: '',
                        quantity: '',
                        lpn: null,
                        searchTerm: '',
                        selectedProductName: '', 
                        searchResults: [],
                        searchLoading: false,
                        highlightedIndex: -1,
                        total_available: 0
                    };
                },

                addLine() {
                    this.lines.push(this.createNewLine());
                },

                removeLine(index) {
                    if(this.lines.length > 1) {
                        this.lines.splice(index, 1);
                    } else {
                        alert('La orden debe tener al menos una línea.');
                    }
                },

                clearAllLines() {
                    if (this.lines.length > 0 && this.lines[0].product_id) {
                        if (confirm('¿Cambiar parámetros globales? Esto reiniciará las líneas de pedido.')) {
                            this.lines = [this.createNewLine()];
                            this.fetchQualities();
                        }
                    } else {
                        this.fetchQualities();
                    }
                },

                onQualityChange(index) {
                    const line = this.lines[index];
                    line.product_id = '';
                    line.selectedProductName = '';
                    line.total_available = 0;
                    line.searchTerm = '';
                },

                fetchQualities() {
                    if (!this.warehouse_id) {
                        this.availableQualities = [];
                        return;
                    }

                    const params = new URLSearchParams({
                        warehouse_id: this.warehouse_id,
                        area_id: this.area_id
                    });

                    fetch(`${this.apiQualitiesUrl}?${params.toString()}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.availableQualities = data;
                    })
                    .catch(error => {
                        console.error('Error fetching qualities:', error);
                        this.availableQualities = [];
                    });
                },

                searchProducts(index) {
                    const line = this.lines[index];
                    
                    line.product_id = '';
                    line.selectedProductName = '';
                    line.highlightedIndex = -1;
                    line.total_available = 0;

                    if (!this.warehouse_id) {
                        alert('Por favor, selecciona un almacén de surtido primero.');
                        line.searchTerm = '';
                        return;
                    }
                    if (!line.quality_id) {
                        alert('Por favor, selecciona una calidad para esta línea.');
                        line.searchTerm = '';
                        return;
                    }
                    if (line.searchTerm.length < 2) {
                        line.searchResults = [];
                        line.searchLoading = false;
                        return;
                    }

                    line.searchLoading = true;
                    if(this.searchTimeout) clearTimeout(this.searchTimeout);

                    this.searchTimeout = setTimeout(() => {
                        const params = new URLSearchParams({
                            query: line.searchTerm,
                            warehouse_id: this.warehouse_id,
                            quality_id: line.quality_id,
                            area_id: this.area_id 
                        });

                        fetch(`${this.apiSearchUrl}?${params.toString()}`, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(response => response.json())
                        .then(data => {
                            line.searchResults = data;
                            line.searchLoading = false;
                        })
                        .catch(() => {
                            line.searchLoading = false;
                            line.searchResults = [];
                        });
                    }, 300);
                },

                selectProduct(lineIndex, resultIndex) {
                    const line = this.lines[lineIndex];
                    if (!line.searchResults[resultIndex]) return;

                    const product = line.searchResults[resultIndex];
                    line.product_id = product.id;
                    line.selectedProductName = `${product.sku} | ${product.name}`;
                    line.searchTerm = `${product.sku} | ${product.name}`;
                    line.total_available = product.total_available;
                    line.quantity = 1;
                    line.searchResults = []; 
                    line.highlightedIndex = -1;
                },

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
                }
            }
        }
    </script>
</x-app-layout>