<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Crear Orden de Compra</h2>
    </x-slot>

    {{-- Script para pasar datos de Blade a Alpine --}}
    <script id="po-create-data" type="application/json">
    {
        "apiSearchUrl": "{{ route('wms.api.search-products') }}",
        "products": @json($products)
    }
    </script>

    <div class="py-12" x-data="poForm()" x-init="initData()">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('wms.purchase-orders.store') }}" method="POST">
                @csrf
                
                {{-- Bloque de Errores --}}
                @if ($errors->any())
                    <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                        <p class="font-bold">Por favor, corrige los siguientes errores:</p>
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <div class="bg-white p-8 rounded-lg shadow-xl space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="po_number" class="block text-sm font-medium text-gray-700">Nº de PO <span class="text-red-500">*</span></label>
                            <input type="text" name="po_number" id="po_number" value="{{ old('po_number') }}" required class="mt-1 block w-full rounded-md border-gray-300">
                        </div>
                        <div>
                            <label for="expected_date" class="block text-sm font-medium text-gray-700">Fecha Esperada <span class="text-red-500">*</span></label>
                            <input type="date" name="expected_date" id="expected_date" value="{{ old('expected_date', now()->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border-gray-300">
                        </div>
                        <div>
                            <label for="warehouse_id" class="block text-sm font-medium text-gray-700">Almacén de Arribo <span class="text-red-500">*</span></label>
                            <select name="warehouse_id" id="warehouse_id" required class="mt-1 block w-full rounded-md border-gray-300">
                                <option value="">-- Seleccionar Almacén --</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label for="container_number" class="block text-sm font-medium text-gray-700">Contenedor</label><input type="text" name="container_number" id="container_number" value="{{ old('container_number') }}" class="mt-1 block w-full rounded-md border-gray-300"></div>
                        <div><label for="document_invoice" class="block text-sm font-medium text-gray-700">Factura</LabeL><input type="text" name="document_invoice" id="document_invoice" value="{{ old('document_invoice') }}" class="mt-1 block w-full rounded-md border-gray-300"></div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label for="pedimento_a4" class="block text-sm font-medium text-gray-700">Pedimento A4</label><input type="text" name="pedimento_a4" id="pedimento_a4" value="{{ old('pedimento_a4') }}" class="mt-1 block w-full rounded-md border-gray-300"></div>
                        <div><label for="pedimento_g1" class="block text-sm font-medium text-gray-700">Pedimento G1</label><input type="text" name="pedimento_g1" id="pedimento_g1" value="{{ old('pedimento_g1') }}" class="mt-1 block w-full rounded-md border-gray-300"></div>
                    </div>
                    
                    <div class="border-t pt-6">
                        <h3 class="font-semibold text-lg">Productos</h3>
                        <div class="space-y-3 mt-2">
                            <template x-for="(line, index) in lines" :key="index">
                                <div class="flex items-center space-x-3">
                                    
                                    {{-- BUSCADOR DE PRODUCTOS --}}
                                    <div class="w-1/2 relative">
                                        <input type="hidden" :name="`lines[${index}][product_id]`" x-model.number="line.product_id" required>
                                        <input type="text"
                                               x-model="line.searchTerm"
                                               @input.debounce.300ms="searchProducts(index)"
                                               @keydown.enter.prevent="selectFirstProduct(index)"
                                               @click.away="line.searchResults = []"
                                               :placeholder="line.selectedProductName || 'Buscar SKU, Nombre o UPC...'"
                                               class="w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                                        
                                        <div x-show="line.searchResults.length > 0" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-y-auto">
                                            <template x-for="(product, prodIndex) in line.searchResults" :key="product.id">
                                                <div @click="selectProduct(index, prodIndex)"
                                                     class="cursor-pointer p-3 hover:bg-blue-50 border-b">
                                                    <div class="font-medium text-gray-900" x-text="product.sku"></div>
                                                    <p class="text-sm text-gray-600" x-text="product.name"></p>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    
                                    <input type="number" :name="`lines[${index}][quantity_ordered]`" x-model.number="line.quantity_ordered" placeholder="Cantidad" min="1" required class="w-1/4 rounded-md border-gray-300">
                                    
                                    <button type="button" @click="removeLine(index)" class="text-red-500 hover:text-red-700 p-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                        <button type="button" @click="addLine()" class="mt-4 px-3 py-1 bg-gray-200 text-gray-700 text-sm rounded-md">+ Añadir Producto</button>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" 
                                :disabled="lines.length === 0 || lines.some(l => !l.product_id || !l.quantity_ordered)"
                                class="px-6 py-2 bg-indigo-600 text-white rounded-md font-semibold disabled:bg-gray-400">
                            Guardar Orden de Compra
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function poForm() {
            return {
                lines: [],
                allProducts: [],
                apiSearchUrl: '',

                initData() {
                    const data = JSON.parse(document.getElementById('po-create-data').textContent);
                    this.apiSearchUrl = data.apiSearchUrl;
                    this.allProducts = data.products; // Fallback por si la API falla
                    this.lines = [{ product_id: '', quantity_ordered: 1, searchTerm: '', selectedProductName: '', searchResults: [] }];
                },

                createNewLine() {
                    return { product_id: '', quantity_ordered: 1, searchTerm: '', selectedProductName: '', searchResults: [] };
                },

                addLine() {
                    this.lines.push(this.createNewLine());
                },

                removeLine(index) {
                    this.lines.splice(index, 1);
                },

                searchProducts(index) {
                    const line = this.lines[index];
                    line.product_id = '';
                    line.selectedProductName = '';

                    if (line.searchTerm.length < 2) {
                        line.searchResults = [];
                        return;
                    }

                    fetch(`${this.apiSearchUrl}?query=${line.searchTerm}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(response => response.json())
                    .then(data => {
                        line.searchResults = data;
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
        
        // Inicializa Alpine
        document.addEventListener('alpine:init', () => {
            Alpine.data('poForm', poForm);
        });
    </script>
</x-app-layout>