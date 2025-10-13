<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Transferencia de Inventario</h2>
    </x-slot>

    <div class="py-12" x-data="transferForm()">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <form action="{{ route('wms.inventory.transfer.store') }}" method="POST">
                    @csrf
                    <div class="space-y-6">
                        {{-- 1. Seleccionar Producto --}}
                        <div>
                            <label for="product_id" class="block text-sm font-medium text-gray-700">Producto</label>
                            <select name="product_id" id="product_id" x-model.number="selectedProductId" @change="updateFromLocations()" required class="mt-1 block w-full rounded-md border-gray-300">
                                <option value="">-- Seleccione un producto --</option>
                                <template x-for="product in uniqueProducts" :key="product.id">
                                    <option :value="product.id" x-text="`${product.name} (${product.sku})`"></option>
                                </template>
                            </select>
                        </div>

                        {{-- 2. Seleccionar Origen (se llena dinámicamente) --}}
                        <div x-show="selectedProductId">
                            <label for="from_location_id" class="block text-sm font-medium text-gray-700">Desde la Ubicación</label>
                            <select name="from_location_id" id="from_location_id" x-model.number="selectedFromLocationId" @change="updateAvailableQuantity()" required class="mt-1 block w-full rounded-md border-gray-300">
                                <option value="">-- Seleccione ubicación de origen --</option>
                                <template x-for="loc in fromLocations" :key="loc.location_id">
                                    <option :value="loc.location_id" x-text="`${loc.location_code} (Disponible: ${loc.quantity})`"></option>
                                </template>
                            </select>
                        </div>

                        {{-- 3. Cantidad a Transferir --}}
                        <div x-show="selectedFromLocationId">
                            <label for="quantity" class="block text-sm font-medium text-gray-700">Cantidad a Transferir</label>
                            <input type="number" name="quantity" id="quantity" x-model.number="quantityToTransfer" min="1" :max="availableQuantity" required class="mt-1 block w-full rounded-md border-gray-300">
                            <p class="text-xs text-gray-500 mt-1" x-text="`Disponible: ${availableQuantity}`"></p>
                        </div>

                        {{-- 4. Seleccionar Destino --}}
                        <div x-show="selectedFromLocationId">
                            <label for="to_location_id" class="block text-sm font-medium text-gray-700">Hacia la Ubicación</label>
                            <select name="to_location_id" id="to_location_id" required class="mt-1 block w-full rounded-md border-gray-300">
                                <option value="">-- Seleccione ubicación de destino --</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}">{{ $location->code }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <a href="{{ route('wms.inventory.index') }}" class="px-4 py-2 bg-gray-300 rounded-md mr-4">Cancelar</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Confirmar Transferencia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        function transferForm() {
            return {
                stockData: @json($stockData),
                selectedProductId: '',
                selectedFromLocationId: '',
                quantityToTransfer: 1,
                fromLocations: [],
                availableQuantity: 0,

                get uniqueProducts() {
                    const products = this.stockData.map(item => item.product);
                    return products.filter((product, index, self) =>
                        index === self.findIndex((p) => p.id === product.id)
                    );
                },
                updateFromLocations() {
                    this.selectedFromLocationId = '';
                    this.fromLocations = this.stockData
                        .filter(item => item.product_id == this.selectedProductId)
                        .map(item => ({
                            location_id: item.location_id,
                            location_code: item.location.code,
                            quantity: item.quantity
                        }));
                },
                updateAvailableQuantity() {
                    if (!this.selectedFromLocationId) {
                        this.availableQuantity = 0;
                        return;
                    }
                    const stockItem = this.fromLocations.find(loc => loc.location_id == this.selectedFromLocationId);
                    this.availableQuantity = stockItem ? stockItem.quantity : 0;
                }
            }
        }
    </script>
</x-app-layout>