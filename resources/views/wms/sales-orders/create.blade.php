<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Crear Orden de Venta (Salida)</h2></x-slot>
    
    {{-- La llamada a x-data ahora es más simple --}}
    <div class="py-12" x-data="soForm()">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('wms.sales-orders.store') }}" method="POST">
                @csrf
                @if ($errors->any())<div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert"><ul class="list-disc list-inside">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                
                <div class="bg-white p-8 rounded-lg shadow-xl space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label for="so_number" class="block text-sm font-medium text-gray-700">Nº de Orden de Venta (SO)</label><input type="text" name="so_number" value="{{ old('so_number') }}" required class="mt-1 block w-full rounded-md border-gray-300"></div>
                        <div><label for="invoice_number" class="block text-sm font-medium text-gray-700">Nº de Factura (Opcional)</label><input type="text" name="invoice_number" value="{{ old('invoice_number') }}" class="mt-1 block w-full rounded-md border-gray-300"></div>
                        <div class="md:col-span-2"><label for="customer_name" class="block text-sm font-medium text-gray-700">Nombre del Cliente</label><input type="text" name="customer_name" value="{{ old('customer_name') }}" required class="mt-1 block w-full rounded-md border-gray-300"></div>
                        <div><label for="delivery_date" class="block text-sm font-medium text-gray-700">Fecha de Entrega</label><input type="date" name="delivery_date" value="{{ old('delivery_date', now()->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border-gray-300"></div>
                    </div>

                    <div class="border-t pt-6">
                        <h3 class="font-semibold text-lg">Productos a Enviar</h3>
                        <div class="space-y-4 mt-2">
                            <template x-for="(line, index) in lines" :key="index">
                                <div class="grid grid-cols-12 gap-x-4 gap-y-2 bg-gray-50 p-4 rounded-md border">
                                    <div class="col-span-12 relative">
                                        <label class="block text-sm font-medium">1. Buscar Producto (SKU o Nombre)</label>
                                        <input type="text" @input.debounce.300ms="line.filterProducts($event.target.value)" class="mt-1 w-full rounded-md" placeholder="Escribe para buscar...">
                                        <div x-show="line.product_id === '' && line.searchTerm !== ''" class="absolute top-full left-0 w-full border rounded-md mt-1 bg-white max-h-40 overflow-y-auto z-10 shadow-lg">
                                            <template x-for="product in line.filteredProducts" :key="product.id"><div @click="line.selectProduct(product)" class="p-2 cursor-pointer hover:bg-indigo-100" x-text="`${product.name} (${product.sku})`"></div></template>
                                            <template x-if="line.filteredProducts.length === 0"><div class="p-2 text-sm text-gray-500">No se encontraron productos.</div></template>
                                        </div>
                                        <div x-show="line.product_id !== ''" class="mt-2 p-2 bg-indigo-100 text-indigo-800 rounded-md flex justify-between items-center"><span x-text="line.selectedProduct ? `${line.selectedProduct.name} (${line.selectedProduct.sku})` : ''"></span><button type="button" @click="line.resetProduct()" class="text-indigo-600 font-bold">&times;</button></div>
                                    </div>

                                    <div class="col-span-12"><label class="block text-sm font-medium">2. Seleccionar Lote (LPN / Calidad / Pedimento)</label>
                                        <select :name="`lines[${index}][pallet_item_id]`" x-model.number="line.pallet_item_id" @change="line.updateAvailableQuantity()" class="mt-1 w-full rounded-md" :disabled="line.product_id === ''">
                                            <option value="">Selecciona un lote disponible...</option>
                                            <template x-for="item in line.availableStock" :key="item.id">
                                                <option :value="item.id" x-text="`LPN: ${item.pallet.lpn} | Cant: ${item.quantity} | Calidad: ${item.quality.name} | Pedimento: ${item.pallet.purchase_order.pedimento_a4 || 'N/A'}`"></option>
                                            </template>
                                        </select>
                                    </div>
                                    
                                    <div class="col-span-12"><label class="block text-sm font-medium">3. Cantidad a Enviar</label><div class="flex items-center"><input type="number" :name="`lines[${index}][quantity]`" x-model.number="line.quantity" min="1" :max="line.availableQuantity" class="mt-1 w-full rounded-md" :disabled="line.pallet_item_id === ''"><p class="text-sm text-gray-500 ml-2 whitespace-nowrap">de <span class="font-bold" x-text="line.availableQuantity"></span> disp.</p><button type="button" @click="removeLine(index)" class="ml-4 text-red-500 hover:text-red-700 p-2 font-bold text-lg">&times;</button></div></div>
                                </div>
                            </template>
                        </div>
                        <button type="button" @click="addLine()" class="mt-4 px-3 py-1 bg-gray-200 text-gray-700 text-sm rounded-md hover:bg-gray-300">+ Añadir Producto</button>
                    </div>
                    <div class="mt-6 flex justify-end"><a href="{{ route('wms.sales-orders.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md mr-4">Cancelar</a><button type="submit" class="px-6 py-2 bg-indigo-600 text-white font-semibold rounded-md shadow-sm hover:bg-indigo-700">Guardar y Reservar Inventario</button></div>
                </div>
            </form>
        </div>
    </div>

    {{-- INICIO DE LA CORRECCIÓN --}}
    <script>
        // Primero, definimos los datos del inventario en una variable de JavaScript normal
        const stockData = @json($stockData);

        // Luego, definimos la función que usa esa variable
        function soForm() {
            const createLine = () => ({
                product_id: '', pallet_item_id: '', quantity: 1, selectedProduct: null, searchTerm: '',
                filteredProducts: [], availableStock: [], availableQuantity: 0,
                filterProducts(term) {
                    this.searchTerm = term.toLowerCase();
                    if (!this.searchTerm) { this.filteredProducts = []; return; }
                    const allProducts = [...new Map(stockData.map(item => [item.product.id, item.product])).values()];
                    this.filteredProducts = allProducts.filter(p => p.name.toLowerCase().includes(this.searchTerm) || p.sku.toLowerCase().includes(this.searchTerm)).slice(0, 5);
                },
                selectProduct(product) {
                    this.product_id = product.id; this.selectedProduct = product; this.searchTerm = ''; this.pallet_item_id = '';
                    this.availableStock = stockData.filter(s => s.product_id == this.product_id);
                },
                updateAvailableQuantity() {
                    const stockItem = stockData.find(s => s.id == this.pallet_item_id);
                    this.availableQuantity = stockItem ? stockItem.quantity : 0;
                    this.quantity = 1;
                },
                resetProduct() {
                    this.product_id = ''; this.selectedProduct = null; this.pallet_item_id = ''; this.availableQuantity = 0; this.quantity = 1;
                }
            });
            return {
                lines: [createLine()],
                addLine() { this.lines.push(createLine()); },
                removeLine(index) { this.lines.splice(index, 1); }
            }
        }
    </script>
    {{-- FIN DE LA CORRECCIÓN --}}
</x-app-layout>