<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-3xl text-gray-800 leading-tight">Nueva Orden de Compra</h2>
    </x-slot>

    <script id="po-create-data" type="application/json">
    {
        "apiSearchUrl": "{{ route('wms.api.search-products') }}"
    }
    </script>

    <div class="py-12" x-data="poForm()" x-init="initData()">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('wms.purchase-orders.store') }}" method="POST">
                @csrf

                {{-- Success Modal --}}
                @if(session('success'))
                <div x-data="{ showSuccess: true }" x-show="showSuccess" x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#2c3856]/80 backdrop-blur-sm">
                    <div x-show="showSuccess" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                         class="bg-white rounded-[2rem] shadow-2xl w-full max-w-md overflow-hidden text-center">
                        <div class="p-10">
                            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                <i class="fas fa-check-circle text-green-500 text-4xl"></i>
                            </div>
                            <h3 class="text-2xl font-black text-[#2c3856] mb-2">¡Orden Creada!</h3>
                            <p class="text-gray-500 mb-1">La Orden de Compra ha sido creada exitosamente.</p>
                            @if(session('created_po_number'))
                            <p class="text-lg font-bold text-[#ff9c00] font-mono">{{ session('created_po_number') }}</p>
                            @endif
                        </div>
                        <div class="bg-gray-50 px-8 py-5 flex flex-col sm:flex-row gap-3">
                            <button type="button" @click="showSuccess = false"
                                    class="flex-1 px-6 py-3 bg-white border border-gray-200 text-[#2c3856] font-bold rounded-xl hover:bg-gray-50 transition-all text-sm">
                                <i class="fas fa-plus mr-2"></i> Crear Otra Orden
                            </button>
                            <a href="{{ route('wms.purchase-orders.index') }}"
                               class="flex-1 px-6 py-3 bg-[#2c3856] text-white font-bold rounded-xl hover:bg-[#1a253a] transition-all text-sm text-center shadow-lg">
                                <i class="fas fa-list mr-2"></i> Ir a Órdenes
                            </a>
                        </div>
                    </div>
                </div>
                @endif
                
                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-xl shadow-sm">
                        <p class="font-bold">Por favor, corrige los siguientes errores:</p>
                        <ul class="list-disc list-inside mt-1 text-sm">
                            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                @endif
                
                <div class="bg-white rounded-[2rem] shadow-xl border border-gray-100 p-8 md:p-12">
                    
                    <h3 class="text-xl font-black text-[#2c3856] mb-6 border-b border-gray-100 pb-2">Información General</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Nº de PO <span class="text-red-500">*</span></label>
                            <input type="text" name="po_number" value="{{ old('po_number') }}" required class="w-full rounded-xl border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00] font-bold text-[#2c3856]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Fecha Esperada <span class="text-red-500">*</span></label>
                            <input type="date" name="expected_date" value="{{ old('expected_date', now()->format('Y-m-d')) }}" required class="w-full rounded-xl border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Almacén Arribo <span class="text-red-500">*</span></label>
                            <select name="warehouse_id" required class="w-full rounded-xl border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00]">
                                <option value="">-- Seleccionar --</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#ff9c00] uppercase tracking-wide mb-1">Cliente / Área <span class="text-red-500">*</span></label>
                            <select name="area_id" x-model="areaId" required class="w-full rounded-xl border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00]">
                                <option value="">-- Seleccionar --</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Contenedor</label>
                            <input type="text" name="container_number" value="{{ old('container_number') }}" class="w-full rounded-xl border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Factura</label>
                            <input type="text" name="document_invoice" value="{{ old('document_invoice') }}" class="w-full rounded-xl border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Pedimento A4</label>
                            <input type="text" name="pedimento_a4" value="{{ old('pedimento_a4') }}" class="w-full rounded-xl border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Pedimento G1</label>
                            <input type="text" name="pedimento_g1" value="{{ old('pedimento_g1') }}" class="w-full rounded-xl border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00]">
                        </div>
                    </div>
                    
                    <div class="border-t border-gray-100 pt-8">
                        <h3 class="text-xl font-black text-[#2c3856] mb-6">Detalle de Productos</h3>
                        
                        <div x-show="!areaId" class="mb-4 p-4 bg-orange-50 text-orange-700 rounded-lg text-sm border border-orange-100">
                            <i class="fas fa-exclamation-triangle mr-2"></i> Selecciona un <strong>Cliente / Área</strong> primero para buscar productos.
                        </div>

                        <div class="space-y-4">
                            <template x-for="(line, index) in lines" :key="index">
                                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl border border-gray-100">
                                    
                                    <div class="w-2/3 relative">
                                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Producto</label>
                                        <input type="hidden" :name="`lines[${index}][product_id]`" x-model.number="line.product_id" required>
                                        
                                        <input type="text"
                                               x-model="line.searchTerm"
                                               @input.debounce.300ms="searchProducts(index)"
                                               @keydown.enter.prevent="selectFirstProduct(index)"
                                               @click.away="line.searchResults = []"
                                               :placeholder="line.selectedProductName || 'Buscar SKU o Nombre...'"
                                               :disabled="!areaId"
                                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-[#2c3856] focus:ring-[#2c3856] disabled:bg-gray-100 disabled:cursor-not-allowed">
                                        
                                        <div x-show="line.searchResults.length > 0" class="absolute z-20 w-full bg-white border border-gray-200 rounded-lg shadow-xl mt-1 max-h-60 overflow-y-auto">
                                            <template x-for="(product, prodIndex) in line.searchResults" :key="product.id">
                                                <div @click="selectProduct(index, prodIndex)" class="cursor-pointer p-3 hover:bg-blue-50 border-b border-gray-50 last:border-0 transition-colors">
                                                    <div class="font-bold text-[#2c3856] text-sm" x-text="product.sku"></div>
                                                    <p class="text-xs text-gray-500" x-text="product.name"></p>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    
                                    <div class="w-1/3">
                                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Cantidad Solicitada</label>
                                        <input type="number" :name="`lines[${index}][quantity_ordered]`" x-model.number="line.quantity_ordered" min="1" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm font-bold text-center focus:border-[#2c3856] focus:ring-[#2c3856]">
                                    </div>
                                    
                                    <div class="pt-5">
                                        <button type="button" @click="removeLine(index)" class="w-8 h-8 flex items-center justify-center rounded-full bg-white border border-gray-200 text-gray-400 hover:text-red-500 hover:border-red-200 transition-colors">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <button type="button" @click="addLine()" class="mt-6 px-6 py-3 bg-gray-100 text-[#2c3856] font-bold rounded-xl hover:bg-gray-200 transition-colors text-sm w-full border border-dashed border-gray-300">
                            + Agregar Línea de Producto
                        </button>
                    </div>

                    <div class="mt-10 flex justify-end gap-4">
                        <a href="{{ route('wms.purchase-orders.index') }}" class="px-8 py-3 bg-white border border-gray-200 text-gray-600 font-bold rounded-xl hover:bg-gray-50 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" 
                                :disabled="lines.length === 0 || lines.some(l => !l.product_id || !l.quantity_ordered)"
                                class="px-8 py-3 bg-[#2c3856] text-white font-bold rounded-xl shadow-lg hover:bg-[#1a253a] transition-transform hover:-translate-y-1 disabled:opacity-50 disabled:cursor-not-allowed">
                            Guardar Orden
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function poForm() {
            return {
                areaId: '',
                lines: [],
                apiSearchUrl: '',

                initData() {
                    const data = JSON.parse(document.getElementById('po-create-data').textContent);
                    this.apiSearchUrl = data.apiSearchUrl;
                    this.lines = [{ product_id: '', quantity_ordered: 1, searchTerm: '', selectedProductName: '', searchResults: [] }];
                },

                createNewLine() {
                    return { product_id: '', quantity_ordered: 1, searchTerm: '', selectedProductName: '', searchResults: [] };
                },

                addLine() {
                    this.lines.push(this.createNewLine());
                },

                removeLine(index) {
                    if (this.lines.length > 1) {
                        this.lines.splice(index, 1);
                    } else {
                        alert('La orden debe tener al menos un producto.');
                    }
                },

                searchProducts(index) {
                    const line = this.lines[index];
                    line.product_id = '';
                    line.selectedProductName = '';

                    if (!this.areaId) {
                        alert("Por favor selecciona un Cliente/Área primero.");
                        line.searchTerm = '';
                        return;
                    }

                    if (line.searchTerm.length < 2) {
                        line.searchResults = [];
                        return;
                    }

                    fetch(`${this.apiSearchUrl}?query=${line.searchTerm}&area_id=${this.areaId}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error("Error en el servidor");
                        return response.json();
                    })
                    .then(data => {
                        line.searchResults = data;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        line.searchResults = [];
                    });
                },

                selectProduct(lineIndex, resultIndex) {
                    const line = this.lines[lineIndex];
                    const product = line.searchResults[resultIndex];
                    
                    line.product_id = product.id;
                    line.selectedProductName = `${product.sku} | ${product.name}`;
                    line.searchTerm = `${product.sku} | ${product.name}`;
                    line.searchResults = [];
                },
                
                selectFirstProduct(index) {
                    if (this.lines[index].searchResults.length > 0) {
                        this.selectProduct(index, 0);
                    }
                }
            }
        }
        
        document.addEventListener('alpine:init', () => {
            Alpine.data('poForm', poForm);
        });
    </script>
</x-app-layout>