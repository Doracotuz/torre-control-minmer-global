<x-app-layout>
    
    <div x-data="inventoryManager()" x-init="init('{!! e(json_encode($products)) !!}')">

        <x-slot name="header">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4 md:mb-0">
                    Inventario (Friends & Family)
                </h2>
                <div class="flex space-x-2">
                    <a href="{{ route('ff.inventory.log') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 transition ease-in-out duration-150">
                       <i class="fas fa-history mr-2"></i> Ver Registro
                    </a>
                    <a href="{{ route('ff.inventory.exportCsv') }}"
                       class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 transition ease-in-out duration-150">
                       <i class="fas fa-file-csv mr-2"></i> Exportar Inventario
                    </a>
                </div>
            </div>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="mb-4">
                    <input type="text" x-model="filter" placeholder="Buscar por SKU o descripción..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Marca / Tipo</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad Existente</th>
                                    @if(Auth::user()->isSuperAdmin())
                                    <th class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="product in filteredProducts" :key="product.id">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="product.sku"></td>
                                        <td class="px-6 py-4 text-sm text-gray-700" style="max-width: 300px;" x-text="product.description"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div x-text="product.brand || 'N/A'" class="font-semibold"></div>
                                            <div x-text="product.type || 'N/A'" class="text-xs"></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right" x-text="`$${parseFloat(product.price).toFixed(2)}`"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="text-xl font-bold"
                                                  :class="{
                                                      'text-green-600': (product.movements_sum_quantity || 0) > 0,
                                                      'text-red-600': (product.movements_sum_quantity || 0) < 0,
                                                      'text-gray-800': (product.movements_sum_quantity || 0) == 0
                                                  }"
                                                  x-text="product.movements_sum_quantity || 0">
                                            </span>
                                        </td>
                                        @if(Auth::user()->isSuperAdmin())
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <button @click="openModal(product, 'add')" class="inline-flex items-center p-2 border border-transparent rounded-full shadow-sm text-white bg-green-600 hover:bg-green-700 transition-transform transform hover:scale-110" title="Añadir Stock">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button @click="openModal(product, 'remove')" class="inline-flex items-center p-2 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 transition-transform transform hover:scale-110" title="Restar Stock">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                        </td>
                                        @endif
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="isModalOpen"
             @keydown.escape.window="closeModal()"
             class="fixed inset-0 z-50 bg-gray-900 bg-opacity-60 flex items-center justify-center p-4 backdrop-blur-sm"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             style="display: none;">

            <form @submit.prevent="submitMovement"
                  @click.outside="closeModal()"
                  class="bg-white rounded-xl shadow-2xl w-full max-w-lg"
                  x-show="isModalOpen"
                  x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
                  x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200"
                  x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90">
                
                <div class="flex justify-between items-center p-5 border-b rounded-t-xl"
                     :class="{ 'bg-green-100': form.type === 'add', 'bg-red-100': form.type === 'remove' }">
                    <h3 class="text-xl font-semibold text-gray-900">
                        <span x-text="form.type === 'add' ? 'Añadir Stock' : 'Restar Stock'"></span>
                    </h3>
                    <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-900"><i class="fas fa-times fa-lg"></i></button>
                </div>
                
                <div class="p-6 space-y-4">
                    <div class="text-center">
                        <p class="text-sm text-gray-500">Producto:</p>
                        <p class="text-lg font-bold text-gray-800" x-text="form.product_name"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700" x-text="form.type === 'add' ? 'Cantidad a Añadir' : 'Cantidad a Restar'"></label>
                        <input type="number" min="1" x-model.number="form.quantity_raw" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Motivo (requerido)</label>
                        <input type="text" x-model="form.reason" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>

                    <div x-show="errorMessage" class="text-sm text-red-600 bg-red-100 p-3 rounded-md" x-text="errorMessage" style="display: none;"></div>
                </div>

                <div class="flex items-center justify-end p-4 bg-gray-50 border-t rounded-b-xl space-x-3">
                    <button type="button" @click="closeModal()" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="inline-flex items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white"
                            :class="{ 'bg-green-600 hover:bg-green-700': form.type === 'add', 'bg-red-600 hover:bg-red-700': form.type === 'remove' }"
                            :disabled="isSaving">
                        <i x-show="isSaving" class="fas fa-spinner fa-spin -ml-1 mr-2" style="display: none;"></i>
                        <span x-text="isSaving ? 'Guardando...' : (form.type === 'add' ? 'Confirmar Añadir' : 'Confirmar Resta')"></span>
                    </button>
                </div>
            </form>
        </div>
    </div> 
    <script>
    function inventoryManager() {
        return {
            products: [],
            filter: '',
            isModalOpen: false,
            isSaving: false,
            errorMessage: '',
            form: {
                product_id: null,
                product_name: '',
                type: 'add',
                quantity_raw: 1,
                reason: ''
            },
            
            // FUNCIÓN INIT MODIFICADA PARA PARSEAR LA CADENA JSON
            init(initialProductsJson) {
                let productsArray = [];
                
                // Intentamos parsear la cadena JSON que inyectamos desde Blade
                if (typeof initialProductsJson === 'string' && initialProductsJson.length > 0) {
                    try {
                        productsArray = JSON.parse(initialProductsJson);
                    } catch (e) {
                        console.error("Error al parsear el JSON de productos:", e);
                    }
                } else {
                    // Manejo de caso vacío/nulo si la inyección no resultó en una cadena
                    productsArray = initialProductsJson || [];
                }
                
                // Línea de seguridad
                productsArray = Array.isArray(productsArray) ? productsArray : [];

                this.products = productsArray.map(p => ({
                    ...p,
                    movements_sum_quantity: p.movements_sum_quantity ? parseInt(p.movements_sum_quantity, 10) : 0
                }));
            },
            
            get filteredProducts() {
                if (this.filter === '') {
                    return this.products;
                }
                const search = this.filter.toLowerCase();
                return this.products.filter(p => 
                    p.sku.toLowerCase().includes(search) || 
                    p.description.toLowerCase().includes(search)
                );
            },

            openModal(product, type) {
                this.isModalOpen = true;
                this.errorMessage = '';
                this.form.product_id = product.id;
                this.form.product_name = product.description;
                this.form.type = type;
                this.form.quantity_raw = 1;
                this.form.reason = '';
            },
            
            closeModal() {
                this.isModalOpen = false;
            },

            async submitMovement() {
                if (this.isSaving) return;
                
                if (this.form.quantity_raw <= 0) {
                    this.errorMessage = "La cantidad debe ser mayor a 0.";
                    return;
                }
                if (this.form.reason.trim() === '') {
                    this.errorMessage = "El motivo es obligatorio.";
                    return;
                }
                
                this.isSaving = true;
                this.errorMessage = '';

                const finalQuantity = this.form.type === 'add' 
                    ? this.form.quantity_raw 
                    : -this.form.quantity_raw;

                try {
                    // El token CSRF debe estar en una meta etiqueta en el head de tu layout.
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    
                    const response = await fetch("{{ route('ff.inventory.storeMovement') }}", {
                        method: 'POST',
                        body: JSON.stringify({
                            product_id: this.form.product_id,
                            quantity: finalQuantity,
                            reason: this.form.reason
                        }),
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        this.errorMessage = data.message || 'Error desconocido.';
                        throw new Error(data.message);
                    }

                    // Actualizar el inventario en el frontend
                    const productIndex = this.products.findIndex(p => p.id === data.product_id);
                    if (productIndex > -1) {
                        // Aseguramos que el nuevo total sea un número
                        this.products[productIndex].movements_sum_quantity = parseInt(data.new_total, 10);
                        // Forzar a Alpine a re-renderizar la lista (necesario después de modificar un array/objeto directamente)
                        this.products = [...this.products];
                    }
                    
                    this.closeModal();

                } catch (error) {
                    console.error(error);
                    if (!this.errorMessage) this.errorMessage = 'No se pudo conectar con el servidor. Revisa la consola para más detalles.';
                } finally {
                    this.isSaving = false;
                }
            }
        }
    }
</script>
</x-app-layout>