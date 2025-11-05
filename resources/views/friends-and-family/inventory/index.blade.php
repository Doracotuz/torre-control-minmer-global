<x-app-layout>
    
    <div x-data="inventoryManager()" x-init="init('{!! e(json_encode($products)) !!}')">

        <x-slot name="header">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4 md:mb-0">
                    Inventario (Friends & Family)
                </h2>
                <div class="flex flex-wrap justify-end space-x-2">
                    <a href="{{ route('ff.inventory.log') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 transition ease-in-out duration-150">
                       <i class="fas fa-history mr-2"></i> Ver Registro
                    </a>
                </div>
            </div>
        </x-slot>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-6">
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p class="font-bold">Éxito</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            @if (session('import_errors'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                    <p class="font-bold">{{ session('error_summary', 'Error en la importación') }}</p>
                    <ul class="list-disc pl-5 mt-2">
                        @foreach (session('import_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label for="search_filter" class="block text-sm font-medium text-gray-700">Buscar por SKU o Descripción:</label>
                        <input type="text" id="search_filter" x-model="filter" placeholder="Buscar..." class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="brand_filter" class="block text-sm font-medium text-gray-700">Marca:</label>
                        <select id="brand_filter" x-model="filterBrand" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">-- Todas las Marcas --</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand }}">{{ $brand }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="type_filter" class="block text-sm font-medium text-gray-700">Tipo:</label>
                        <select id="type_filter" x-model="filterType" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">-- Todos los Tipos --</option>
                            @foreach($types as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-4 text-right">
                    <button @click.prevent="exportFilteredCsv()"
                       class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 transition ease-in-out duration-150">
                       <i class="fas fa-file-csv mr-2"></i> Exportar Inventario
                    </button>                    
                    <button @click.prevent="openImportModal()"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 transition ease-in-out duration-150">
                       <i class="fas fa-upload mr-2"></i> Importar CSV
                    </button>                    
                    <button @click.prevent="resetFilters()" class="inline-flex items-center px-3 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-600 uppercase tracking-widest hover:bg-gray-300 transition ease-in-out duration-150">
                        <i class="fas fa-times mr-2"></i> Limpiar Filtros
                    </button>
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
        
        <div x-show="isImportModalOpen"
             @keydown.escape.window="closeImportModal()"
             class="fixed inset-0 z-50 bg-gray-900 bg-opacity-60 flex items-center justify-center p-4 backdrop-blur-sm"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             style="display: none;">

            <form action="{{ route('ff.inventory.import') }}" method="POST" enctype="multipart/form-data"
                  @click.outside="closeImportModal()"
                  class="bg-white rounded-xl shadow-2xl w-full max-w-lg"
                  x-show="isImportModalOpen"
                  x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
                  x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200"
                  x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90">
                
                @csrf
                
                <div class="flex justify-between items-center p-5 border-b rounded-t-xl bg-blue-100">
                    <h3 class="text-xl font-semibold text-gray-900">
                        Importar Movimientos de Inventario
                    </h3>
                    <button type="button" @click.prevent="closeImportModal()" class="text-gray-400 hover:text-gray-900"><i class="fas fa-times fa-lg"></i></button>
                </div>
                
                <div class="p-6 space-y-4">
                    <p class="text-sm text-gray-600">
                        Sube un archivo CSV con las columnas: <strong>SKU, Quantity, Reason</strong>.
                        Usa cantidades positivas para añadir stock y negativas para restar.
                    </p>
                    
                    <div>
                        <label for="movements_file" class="block text-sm font-medium text-gray-700">Selecciona el archivo CSV:</label>
                        <input type="file" name="movements_file" id="movements_file" class="mt-1 block w-full text-sm text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-md file:border-0
                            file:font-semibold file:bg-blue-50 file:text-blue-700
                            hover:file:bg-blue-100" required>
                    </div>

                    <div>
                        <button type="button" 
                           @click.prevent="downloadFilteredTemplate()"
                           class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800"
                           title="La plantilla se generará con los productos que has filtrado en la vista principal.">
                           <i class="fas fa-file-csv mr-2"></i> Descargar Plantilla (con productos filtrados)
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-end p-4 bg-gray-50 border-t rounded-b-xl space-x-3">
                    <button type="button" @click.prevent="closeImportModal()" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="inline-flex items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fas fa-upload -ml-1 mr-2"></i>
                        Importar Movimientos
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
            filterBrand: '',
            filterType: '',
            isModalOpen: false,
            isImportModalOpen: false,
            isSaving: false,
            errorMessage: '',
            form: {
                product_id: null,
                product_name: '',
                type: 'add',
                quantity_raw: 1,
                reason: ''
            },
            
            init(initialProductsJson) {
                let productsArray = [];
                
                if (typeof initialProductsJson === 'string' && initialProductsJson.length > 0) {
                    try {
                        productsArray = JSON.parse(initialProductsJson);
                    } catch (e) {
                        console.error("Error al parsear el JSON de productos:", e);
                    }
                } else {
                    productsArray = initialProductsJson || [];
                }
                
                productsArray = Array.isArray(productsArray) ? productsArray : [];

                this.products = productsArray.map(p => ({
                    ...p,
                    movements_sum_quantity: p.movements_sum_quantity ? parseInt(p.movements_sum_quantity, 10) : 0
                }));
            },
            
            get filteredProducts() {
                const search = this.filter.toLowerCase();
                
                return this.products.filter(p => {
                    if (this.filterBrand && p.brand !== this.filterBrand) {
                        return false;
                    }
                    
                    if (this.filterType && p.type !== this.filterType) {
                        return false;
                    }
                    
                    if (search) {
                        const inSku = p.sku.toLowerCase().includes(search);
                        const inDesc = p.description.toLowerCase().includes(search);
                        if (!inSku && !inDesc) {
                            return false;
                        }
                    }
                    
                    return true; 
                });
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

            openImportModal() {
                this.isImportModalOpen = true;
            },
            
            closeImportModal() {
                this.isImportModalOpen = false;
            },
            
            resetFilters() {
                this.filter = '';
                this.filterBrand = '';
                this.filterType = '';
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

                    const productIndex = this.products.findIndex(p => p.id === data.product_id);
                    if (productIndex > -1) {
                        this.products[productIndex].movements_sum_quantity = parseInt(data.new_total, 10);
                        this.products = [...this.products];
                    }
                    
                    this.closeModal();

                } catch (error) {
                    console.error(error);
                    if (!this.errorMessage) this.errorMessage = 'No se pudo conectar con el servidor. Revisa la consola para más detalles.';
                } finally {
                    this.isSaving = false;
                }
            },

            exportFilteredCsv() {
                const baseUrl = "{{ route('ff.inventory.exportCsv') }}";
                const params = new URLSearchParams();
                
                if (this.filter) {
                    params.append('search', this.filter);
                }
                if (this.filterBrand) {
                    params.append('brand', this.filterBrand);
                }
                if (this.filterType) {
                    params.append('type', this.filterType);
                }
                
                const finalUrl = `${baseUrl}?${params.toString()}`;
                
                window.location.href = finalUrl;
            },

            downloadFilteredTemplate() {
                const baseUrl = "{{ route('ff.inventory.movementsTemplate') }}";
                const params = new URLSearchParams();
                
                if (this.filter) {
                    params.append('search', this.filter);
                }
                if (this.filterBrand) {
                    params.append('brand', this.filterBrand);
                }
                if (this.filterType) {
                    params.append('type', this.filterType);
                }
                
                const finalUrl = `${baseUrl}?${params.toString()}`;
                
                window.location.href = finalUrl;
            }
        }
    }
</script>
</x-app-layout>