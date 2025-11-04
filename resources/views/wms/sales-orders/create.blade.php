<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-[#2c3856] leading-tight">
                {{ __('Crear Nueva Orden de Venta (SO)') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12" x-data="salesOrderForm()" x-init="initData(@json($stockData))" x-cloak>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('wms.sales-orders.store') }}">
                @csrf
                
                <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
                    <h3 class="text-xl font-bold text-[#2c3856] mb-4 border-b pb-2">Detalles de la Orden</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label for="so_number" class="block text-sm font-medium text-gray-700">Número de Orden (SO) <span class="text-red-500">*</span></label>
                            <input type="text" name="so_number" id="so_number" value="{{ old('so_number') }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#ff9c00] focus:ring-[#ff9c00] sm:text-sm">
                            @error('so_number') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="invoice_number" class="block text-sm font-medium text-gray-700">Número de Factura</label>
                            <input type="text" name="invoice_number" id="invoice_number" value="{{ old('invoice_number') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#ff9c00] focus:ring-[#ff9c00] sm:text-sm">
                        </div>

                        <div>
                            <label for="customer_name" class="block text-sm font-medium text-gray-700">Nombre del Cliente <span class="text-red-500">*</span></label>
                            <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name') }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#ff9c00] focus:ring-[#ff9c00] sm:text-sm">
                            @error('customer_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="delivery_date" class="block text-sm font-medium text-gray-700">Fecha de Entrega <span class="text-red-500">*</span></label>
                            <input type="date" name="delivery_date" id="delivery_date" value="{{ old('delivery_date', now()->format('Y-m-d')) }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#ff9c00] focus:ring-[#ff9c00] sm:text-sm">
                            @error('delivery_date') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-lg rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h3 class="text-xl font-bold text-[#2c3856]">Líneas de la Orden</h3>
                        <button type="button" @click="addLine()" class="px-3 py-1 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-medium">
                            + Añadir Línea
                        </button>
                    </div>

                    @error('lines') <span class="text-sm text-red-500 mb-4 block">{{ $message }}</span> @enderror

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/5">Lote / LPN (Stock Disponible)</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ubicación</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Calidad</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dispon.</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty. Pedida</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-if="lines.length === 0">
                                    <tr>
                                        <td colspan="6" class="px-4 py-12 text-center text-gray-500">
                                            Añade al menos una línea de producto.
                                        </td>
                                    </tr>
                                </template>

                                <template x-for="(line, index) in lines" :key="index">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <select x-model="line.pallet_item_id" :name="`lines[${index}][pallet_item_id]`" @change="updateAvailableStock()" required
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#ff9c00] focus:ring-[#ff9c00] sm:text-sm">
                                                <option value="">-- Seleccionar Lote/LPN --</option>
                                                
                                                <template x-if="line.pallet_item_id && getStockItem(line.pallet_item_id)">
                                                    <option :value="line.pallet_item_id" 
                                                            x-text="formatStockOption(getStockItem(line.pallet_item_id))"
                                                            :class="{ 'text-red-700 font-bold': !getStockItem(line.pallet_item_id).is_available }">
                                                    </option>
                                                </template>

                                                <template x-for="stock in availableStock" :key="stock.id">
                                                    <option :value="stock.id" 
                                                            x-text="formatStockOption(stock)"
                                                            :class="{ 'text-red-700 font-bold': !stock.is_available }">
                                                    </option>
                                                </template>
                                            </select>
                                        </td>
                                        
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">
                                            <span x-text="getStockItem(line.pallet_item_id)?.pallet.location.code || 'N/A'"></span>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <template x-if="getStockItem(line.pallet_item_id)">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                      :class="{
                                                        'bg-green-100 text-green-800': getStockItem(line.pallet_item_id).is_available,
                                                        'bg-red-100 text-red-800': !getStockItem(line.pallet_item_id).is_available
                                                      }"
                                                      x-text="getStockItem(line.pallet_item_id).quality.name">
                                                </span>
                                            </template>
                                            <span x-show="!getStockItem(line.pallet_item_id)">N/A</span>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm font-bold text-gray-900">
                                            <span x-text="getAvailableQty(getStockItem(line.pallet_item_id))"></span>
                                        </td>

                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <input type="number" x-model.number="line.quantity" :name="`lines[${index}][quantity]`"
                                                   min="1" :max="getAvailableQty(getStockItem(line.pallet_item_id))" required
                                                   class="w-24 rounded-md border-gray-300 shadow-sm focus:border-[#ff9c00] focus:ring-[#ff9c00] sm:text-sm"
                                                   :disabled="!line.pallet_item_id">
                                        </td>

                                        <td class="px-4 py-2 whitespace-nowrap text-center">
                                            <button type="button" @click="removeLine(index)" class="text-red-600 hover:text-red-800">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <a href="{{ route('wms.sales-orders.index') }}" class="mr-4 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancelar
                    </a>
                    <button type="submit" 
                            :disabled="lines.length === 0 || lines.some(l => !l.pallet_item_id || !l.quantity)"
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
                stockData: [], // Inicia vacío
                availableStock: [],
                lines: [{ pallet_item_id: '', quantity: 1 }],

                // Nueva función para inicializar de forma segura
                initData(data) {
                    this.stockData = data;
                    this.updateAvailableStock();
                },

                // Añade una nueva línea vacía
                addLine() {
                    this.lines.push({ pallet_item_id: '', quantity: 1 });
                },

                // Elimina una línea por su índice
                removeLine(index) {
                    this.lines.splice(index, 1);
                    this.updateAvailableStock();
                },

                // Obtiene el objeto de stock completo usando su ID
                getStockItem(id) {
                    if (!id) return null;
                    return this.stockData.find(item => item.id == id);
                },

                // Obtiene la cantidad física disponible de un item
                getAvailableQty(item) {
                    if (!item) return 0;
                    return item.quantity - item.committed_quantity;
                },

                // Formatea el texto para la opción del <select>
                formatStockOption(stock) {
                    let warning = stock.warning_message ? ` (🔥 ${stock.warning_message})` : '';
                    return `LPN: ${stock.pallet.lpn} | SKU: ${stock.product.sku} | Qty: ${this.getAvailableQty(stock)}${warning}`;
                }, // <--- La coma que quité antes ya no está

                // Actualiza la lista de stock "disponible" para los menús desplegables
                updateAvailableStock() {
                    // Obtiene los IDs de todos los LPNs ya seleccionados en las líneas
                    const selectedIds = this.lines
                        .map(line => Number(line.pallet_item_id))
                        .filter(id => id > 0);
                    
                    // El stock disponible es todo el stock que NO está en la lista de seleccionados
                    this.availableStock = this.stockData.filter(item => !selectedIds.includes(item.id));
                }
            }
        }
    </script>
</x-app-layout>