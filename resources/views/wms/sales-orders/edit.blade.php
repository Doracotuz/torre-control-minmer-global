<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Editar Orden de Venta: {{ $salesOrder->so_number }}
        </h2>
    </x-slot>
    
    {{-- 
      Volvemos al patrón original pero MÁS LIMPIO.
      Usamos comillas simples ( ' ) para x-data.
    --}}
    <div class="py-12" x-data='soForm(@json($salesOrder->lines))'>
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <form action="{{ route('wms.sales-orders.update', $salesOrder) }}" method="POST">
                @csrf
                @method('PUT') 

                @if ($errors->any())
                    <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <div class="bg-white p-8 rounded-lg shadow-xl space-y-6">
                    {{-- Campos de la cabecera (SO, Factura, Cliente, Fecha) --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="so_number" class="block text-sm font-medium text-gray-700">Nº de Orden de Venta (SO)</label>
                            <input type="text" id="so_number" name="so_number" value="{{ old('so_number', $salesOrder->so_number) }}" required class="mt-1 block w-full rounded-md border-gray-300">
                        </div>
                        <div>
                            <label for="invoice_number" class="block text-sm font-medium text-gray-700">Nº de Factura (Opcional)</label>
                            <input type="text" id="invoice_number" name="invoice_number" value="{{ old('invoice_number', $salesOrder->invoice_number) }}" class="mt-1 block w-full rounded-md border-gray-300">
                        </div>
                        <div class="md:col-span-2">
                            <label for="customer_name" class="block text-sm font-medium text-gray-700">Nombre del Cliente</label>
                            <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name', $salesOrder->customer_name) }}" required class="mt-1 block w-full rounded-md border-gray-300">
                        </div>
                        <div>
                            <label for="delivery_date" class="block text-sm font-medium text-gray-700">Fecha de Entrega</label>
                            <input type="date" id="delivery_date" name="delivery_date" value="{{ old('delivery_date', $salesOrder->order_date->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border-gray-300">
                        </div>
                    </div>

                    {{-- Sección de Productos --}}
                    <div class="border-t pt-6">
                        <h3 class="font-semibold text-lg">Productos a Enviar</h3>
                        <div class="space-y-4 mt-2">
                            <template x-for="(line, index) in lines" :key="index">
                                <div class="grid grid-cols-12 gap-x-4 gap-y-2 bg-gray-50 p-4 rounded-md border">
                                    
                                    {{-- 1. Buscar Producto --}}
                                    <div class="col-span-12 relative">
                                        <label :for="`product_search_${index}`" class="block text-sm font-medium">1. Buscar Producto (SKU o Nombre)</label>
                                        <input type="text"
                                               :id="`product_search_${index}`"
                                               name="product_search"
                                               @input.debounce.300ms="line.filterProducts($event.target.value)" 
                                               @focus="line.searchTerm = ''"
                                               :value="line.product_id ? `${line.selectedProduct.name} (${line.selectedProduct.sku})` : line.searchTerm"
                                               placeholder="Escribe para buscar..."
                                               class="mt-1 w-full rounded-md"
                                               autocomplete="off">
                                        
                                        <div x-show="line.product_id === '' && line.searchTerm !== ''" class="absolute top-full left-0 w-full border rounded-md mt-1 bg-white max-h-40 overflow-y-auto z-10 shadow-lg">
                                            <template x-for="product in line.filteredProducts" :key="product.id">
                                                <div @click="line.selectProduct(product)" class="p-2 cursor-pointer hover:bg-indigo-100" x-text="`${product.name} (${product.sku})`"></div>
                                            </template>
                                            <template x-if="line.filteredProducts.length === 0">
                                                <div class="p-2 text-sm text-gray-500">No se encontraron productos.</div>
                                            </template>
                                        </div>
                                    </div>

                                    {{-- 2. Seleccionar Lote --}}
                                    <div class="col-span-12">
                                        <label :for="`pallet_item_${index}`" class="block text-sm font-medium">2. Seleccionar Lote (LPN / Calidad / Pedimento)</label>
                                        <select :id="`pallet_item_${index}`" :name="`lines[${index}][pallet_item_id]`" x-model.number="line.pallet_item_id" @change="line.updateAvailableQuantity()" class="mt-1 w-full rounded-md" :disabled="line.product_id === ''">
                                            <option value="">Selecciona un lote disponible...</option>
                                            <template x-for="item in line.availableStock" :key="item.id">
                                                <option :value="item.id" 
                                                        x-text="`LPN: ${item.pallet?.lpn || 'N/A'} | Cant: ${item.quantity} | Calidad: ${item.quality?.name || 'N/A'} | Pedimento: ${item.pallet?.purchase_order?.pedimento_a4 || 'N/A'}`">
                                                </option>
                                            </template>
                                        </select>
                                    </div>
                                    
                                    {{-- 3. Cantidad a Enviar --}}
                                    <div class="col-span-12">
                                        <label :for="`quantity_${index}`" class="block text-sm font-medium">3. Cantidad a Enviar</label>
                                        <div class="flex items-center">
                                            <input type="number" :id="`quantity_${index}`" :name="`lines[${index}][quantity]`" x-model.number="line.quantity" min="1" :max="line.availableQuantityForInput" class="mt-1 w-full rounded-md" :disabled="line.pallet_item_id === ''">
                                            <p class="text-sm text-gray-500 ml-2 whitespace-nowrap">
                                                de <span class="font-bold" x-text="line.availableQuantity || 'N/A'"></span> disp.
                                            </p>
                                            <button type="button" @click="removeLine(index)" class="ml-4 text-red-500 hover:text-red-700 p-2 font-bold text-lg">&times;</button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <button type="button" @click="addLine()" class="mt-4 px-3 py-1 bg-gray-200 text-gray-700 text-sm rounded-md hover:bg-gray-300">+ Añadir Producto</button>
                    </div>
                    
                    {{-- Botones de Acción --}}
                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('wms.sales-orders.show', $salesOrder) }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md mr-4">Cancelar</a>
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white font-semibold rounded-md shadow-sm hover:bg-indigo-700">Actualizar Orden</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- --- INICIO DEL SCRIPT CORREGIDO --- --}}
    <script>
        // 1. Define los datos de stock global
        const stockData = @json($stockData);

        // 2. Define la función que CREA un objeto de línea (con datos y métodos)
        // Esta es una "fábrica" de líneas
        const createLine = (lineData = {}) => {
            
            const hasData = lineData && lineData.pallet_item;
            const initialProductId = hasData ? lineData.pallet_item.product_id : '';
            // Lo dejamos como NÚMERO (o null) para que coincida con el x-model.number
            const initialPalletItemId = hasData ? lineData.pallet_item_id : null; 
            const initialSelectedProduct = hasData ? lineData.pallet_item.product : null;
            
            const initialAvailableStock = stockData.filter(s => s.product_id == initialProductId);
            const initialStockItem = stockData.find(s => s.id == initialPalletItemId);
            
            const initialAvailableQty = initialStockItem ? initialStockItem.quantity : 0;
            const initialQtyForInput = initialStockItem 
                ? (initialStockItem.quantity + lineData.quantity_ordered) 
                : (lineData.quantity_ordered || 1);

            // Devuelve el objeto completo (propiedades Y métodos)
            return {
                product_id: initialProductId,
                pallet_item_id: initialPalletItemId, // Es un NÚMERO
                quantity: lineData.quantity_ordered || 1,
                selectedProduct: initialSelectedProduct,
                searchTerm: '',
                filteredProducts: [],
                availableStock: initialAvailableStock, 
                availableQuantity: initialAvailableQty, 
                availableQuantityForInput: initialQtyForInput,

                // --- MÉTODOS (para que el buscador funcione) ---
                filterProducts(term) {
                    this.searchTerm = term.toLowerCase();
                    if (!this.searchTerm) { this.filteredProducts = []; return; }
                    const allProducts = [...new Map(stockData.map(item => [item.product.id, item.product])).values()];
                    this.filteredProducts = allProducts.filter(p => p.name.toLowerCase().includes(this.searchTerm) || p.sku.toLowerCase().includes(this.searchTerm)).slice(0, 5);
                },
                
                selectProduct(product) {
                    this.product_id = product.id; 
                    this.selectedProduct = product; 
                    this.searchTerm = ''; 
                    this.pallet_item_id = null; // Reinicia a null
                    this.availableStock = stockData.filter(s => s.product_id == this.product_id);
                    this.availableQuantity = 0;
                    this.availableQuantityForInput = 1;
                    this.quantity = 1;
                },
                
                updateAvailableQuantity() {
                    const stockItem = stockData.find(s => s.id == this.pallet_item_id);
                    this.availableQuantity = stockItem ? stockItem.quantity : 0;
                    
                    const originalOrderedQty = (lineData.pallet_item_id == this.pallet_item_id) ? lineData.quantity_ordered : 0;
                    this.availableQuantityForInput = stockItem ? (stockItem.quantity + originalOrderedQty) : 1;
                    
                    if (this.pallet_item_id != (lineData.pallet_item_id ? lineData.pallet_item_id : null)) {
                        this.quantity = 1;
                    }
                },
                
                resetProduct() {
                    this.product_id = ''; 
                    this.selectedProduct = null; 
                    this.pallet_item_id = null; 
                    this.availableQuantity = 0;
                    this.availableQuantityForInput = 1;
                    this.quantity = 1;
                    this.searchTerm = '';
                    this.availableStock = [];
                    this.filteredProducts = [];
                }
            };
        }; // --- Fin de createLine ---


        // 3. Define la función Alpine principal
        // Esta función recibe los datos de Laravel y DEVUELVE el objeto completo
        function soForm(initialLines = []) {
          
          return {
              // 1. Mapea los datos JSON a objetos "completos"
              lines: initialLines.length > 0 ? initialLines.map(line => createLine(line)) : [createLine()],

              // 2. Métodos para AÑADIR/QUITAR líneas
              addLine() {
                  this.lines.push(createLine()); 
              },
              removeLine(index) {
                  this.lines.splice(index, 1);
              }
          };
        } // --- Fin de soForm ---
    </script>
    {{-- --- FIN DEL SCRIPT --- --}}
</x-app-layout>