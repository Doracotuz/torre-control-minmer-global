<x-app-layout>
    <div x-data="inventoryManager()" x-init="init(@js($products))" class="font-sans">

        <x-slot name="header">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h2 class="font-bold text-2xl text-[#2c3856] leading-tight font-[Montserrat]">
                        <i class="fas fa-boxes mr-2 text-[#ff9c00]"></i> Inventario
                    </h2>
                    <p class="text-sm text-gray-500 font-[Montserrat] mt-1">Gestión de stock y movimientos</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('ff.inventory.log') }}" 
                       class="inline-flex items-center px-4 py-2 bg-white text-[#2c3856] border border-gray-200 rounded-full text-sm font-semibold shadow-sm hover:bg-gray-50 hover:text-[#ff9c00] transition-all duration-300">
                        <i class="fas fa-history mr-2"></i> Historial
                    </a>

                    <a href="{{ route('ff.dashboard.index') }}" 
                       class="inline-flex items-center px-5 py-2 bg-[#2c3856] text-white rounded-full text-sm font-bold shadow-md hover:bg-[#1e273d] hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-300">
                        <i class="fas fa-arrow-left mr-2"></i> Dashboard
                    </a>
                </div>
            </div>
        </x-slot>

        <div class="py-6">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-3xl p-6 shadow-[0_3px_10px_rgb(0,0,0,0.05)] border border-white/50 flex items-center justify-between relative overflow-hidden group hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-300">
                    <div class="relative z-10">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Productos Visibles</p>
                        <h3 class="text-3xl font-extrabold text-[#2c3856]" x-text="filteredProducts.length"></h3>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-2xl text-[#2c3856] group-hover:scale-110 group-hover:bg-[#2c3856] group-hover:text-white transition-all duration-300">
                        <i class="fas fa-tags fa-xl"></i>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-[0_3px_10px_rgb(0,0,0,0.05)] border border-white/50 flex items-center justify-between relative overflow-hidden group hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-300">
                    <div class="relative z-10">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Valor Total (Vista)</p>
                        <h3 class="text-3xl font-extrabold text-[#2c3856]" x-text="formatMoney(totalValue)"></h3>
                    </div>
                    <div class="bg-orange-50 p-4 rounded-2xl text-[#ff9c00] group-hover:scale-110 group-hover:bg-[#ff9c00] group-hover:text-white transition-all duration-300">
                        <i class="fas fa-dollar-sign fa-xl"></i>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-[0_3px_10px_rgb(0,0,0,0.05)] border border-white/50 flex items-center justify-between relative overflow-hidden group hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-300">
                    <div class="relative z-10">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Unidades en Stock</p>
                        <h3 class="text-3xl font-extrabold text-[#2c3856]" x-text="totalStock"></h3>
                    </div>
                    <div class="bg-emerald-50 p-4 rounded-2xl text-emerald-600 group-hover:scale-110 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300">
                        <i class="fas fa-cubes fa-xl"></i>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                     class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-800 px-4 py-3 rounded-r-xl mb-6 shadow-sm flex items-center justify-between" role="alert">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2 text-xl"></i>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-600 hover:text-emerald-800"><i class="fas fa-times"></i></button>
                </div>
            @endif

            @if (session('import_errors'))
                <div class="bg-red-50 border-l-4 border-red-500 text-red-800 px-6 py-4 rounded-r-xl mb-6 shadow-sm">
                    <div class="flex items-center font-bold text-lg mb-2">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        {{ session('error_summary', 'Errores en la importación') }}
                    </div>
                    <ul class="list-disc list-inside text-sm space-y-1 opacity-90 pl-2">
                        @foreach (session('import_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 overflow-hidden">
                
                <div class="p-6 border-b border-gray-100 flex flex-col lg:flex-row gap-4 justify-between items-center bg-white">
                    
                    <div class="relative w-full lg:w-1/3 group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-300 group-focus-within:text-[#ff9c00] transition-colors duration-300"></i>
                        </div>
                        <input type="text" x-model="filter" 
                               class="block w-full pl-11 pr-4 py-3 bg-[#F3F4F6] border-none text-gray-700 rounded-xl focus:ring-2 focus:ring-[#ff9c00] focus:bg-white transition-all duration-200 placeholder-gray-400 font-[Montserrat] text-sm" 
                               placeholder="Buscar por SKU o descripción...">
                    </div>

                    <div class="flex flex-wrap gap-3 w-full lg:w-auto justify-end items-center">
                        
                        <select x-model="filterBrand" class="pl-4 pr-10 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-600 focus:ring-2 focus:ring-[#ff9c00] focus:border-transparent shadow-sm cursor-pointer hover:border-gray-300 transition-all">
                            <option value="">Todas las Marcas</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand }}">{{ $brand }}</option>
                            @endforeach
                        </select>

                        <select x-model="filterType" class="pl-4 pr-10 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-600 focus:ring-2 focus:ring-[#ff9c00] focus:border-transparent shadow-sm cursor-pointer hover:border-gray-300 transition-all">
                            <option value="">Todos los Tipos</option>
                            @foreach($types as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                        
                        <div class="h-8 w-px bg-gray-200 mx-2 hidden md:block"></div>

                        <button @click="openImportModal()" 
                                class="p-2.5 text-gray-500 hover:text-[#2c3856] hover:bg-gray-100 rounded-xl transition-all duration-200 border border-transparent hover:border-gray-200" 
                                title="Importar CSV">
                            <i class="fas fa-file-upload fa-lg"></i>
                        </button>
                        
                        <button @click="exportFilteredCsv()" 
                                class="p-2.5 text-gray-500 hover:text-[#2c3856] hover:bg-gray-100 rounded-xl transition-all duration-200 border border-transparent hover:border-gray-200" 
                                title="Exportar CSV">
                            <i class="fas fa-file-download fa-lg"></i>
                        </button>

                        <button @click="resetFilters()" x-show="filter || filterBrand || filterType" x-transition 
                                class="px-4 py-2.5 text-red-500 bg-red-50 hover:bg-red-100 rounded-xl text-sm font-bold transition-all" 
                                title="Limpiar Filtros">
                            <i class="fas fa-times mr-1"></i> Limpiar
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full whitespace-nowrap text-left">
                        <thead>
                            <tr class="bg-[#F9FAFB] border-b border-gray-100">
                                <th class="px-6 py-4 text-sm font-bold text-gray-500 uppercase tracking-wider font-[Montserrat]">Producto</th>
                                <th class="px-6 py-4 text-sm font-bold text-gray-500 uppercase tracking-wider font-[Montserrat]">Categoría</th>
                                <th class="px-6 py-4 text-sm font-bold text-gray-500 uppercase tracking-wider text-right font-[Montserrat]">Precio</th>
                                <th class="px-6 py-4 text-sm font-bold text-gray-500 uppercase tracking-wider text-center font-[Montserrat]">Stock</th>
                                @if(Auth::user()->isSuperAdmin() || Auth::user()->is_area_admin)
                                <th class="px-6 py-4 text-sm font-bold text-gray-500 uppercase tracking-wider text-right font-[Montserrat]">Ajuste Rápido</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 bg-white">
                            <template x-for="product in filteredProducts" :key="product.id">
                                <tr class="hover:bg-blue-50/90 transition-colors duration-200 group">
                                    
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="h-14 w-14 flex-shrink-0 rounded-xl border border-gray-100 overflow-hidden p-1 bg-white shadow-sm group-hover:shadow-md transition-shadow">
                                                <img class="h-full w-full object-contain rounded-lg" :src="product.photo_url" :alt="product.sku">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-bold text-[#2c3856]" x-text="product.description"></div>
                                                <div class="text-xs text-gray-400 font-mono mt-1 bg-gray-100 inline-block px-1.5 py-0.5 rounded" x-text="product.sku"></div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-[#e8ecf7] text-[#2c3856] w-fit" 
                                                  x-text="product.brand || 'Sin Marca'"></span>
                                            <span class="text-xs text-gray-500 ml-1" x-text="product.type"></span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <div class="text-sm font-bold text-gray-700 font-mono" x-text="formatMoney(product.price)"></div>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <div class="inline-flex items-center justify-center px-4 py-1.5 rounded-full text-sm font-bold shadow-sm border transition-all duration-300"
                                             :class="{
                                                 'bg-emerald-50 text-emerald-700 border-emerald-100': (product.movements_sum_quantity || 0) > 5,
                                                 'bg-[#fff3e0] text-[#e65100] border-[#ffe0b2]': (product.movements_sum_quantity || 0) > 0 && (product.movements_sum_quantity || 0) <= 5,
                                                 'bg-red-50 text-red-700 border-red-100': (product.movements_sum_quantity || 0) <= 0
                                             }">
                                            <i class="fas fa-cube mr-2 text-xs opacity-80"></i>
                                            <span x-text="product.movements_sum_quantity || 0"></span>
                                        </div>
                                    </td>

                                    @if(Auth::user()->isSuperAdmin() || Auth::user()->is_area_admin)
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end space-x-2 opacity-40 group-hover:opacity-100 transition-opacity duration-200">
                                            <button @click="openModal(product, 'add')" 
                                                    class="h-9 w-9 flex items-center justify-center rounded-full bg-white border border-emerald-200 text-emerald-600 hover:bg-emerald-500 hover:text-white hover:border-emerald-500 transition-all shadow-sm transform hover:scale-110" 
                                                    title="Añadir Stock">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button @click="openModal(product, 'remove')" 
                                                    class="h-9 w-9 flex items-center justify-center rounded-full bg-white border border-red-200 text-red-600 hover:bg-red-500 hover:text-white hover:border-red-500 transition-all shadow-sm transform hover:scale-110" 
                                                    title="Restar Stock">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                        </div>
                                    </td>
                                    @endif
                                </tr>
                            </template>
                            
                            <template x-if="filteredProducts.length === 0">
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="bg-gray-50 rounded-full p-6 mb-4 shadow-inner">
                                                <i class="fas fa-search text-gray-300 text-3xl"></i>
                                            </div>
                                            <h3 class="text-lg font-bold text-[#2c3856]">No se encontraron productos</h3>
                                            <p class="text-gray-500 text-sm mt-1">Intenta con otra búsqueda o ajusta los filtros.</p>
                                            <button @click="resetFilters()" class="mt-4 text-[#ff9c00] hover:text-orange-600 font-bold text-sm underline">
                                                Limpiar todos los filtros
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                
                <div class="bg-gray-50/50 px-6 py-4 border-t border-gray-100 flex items-center justify-between text-xs font-medium text-gray-500">
                    <span x-text="`Mostrando ${filteredProducts.length} registros`"></span>
                </div>
            </div>
        </div>

        <div x-show="isModalOpen" 
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;" x-cloak>
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:block sm:p-0">
                
                <div x-show="isModalOpen" 
                     x-transition.opacity.duration.300ms
                     class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-[#2c3856] opacity-60 backdrop-blur-sm"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="isModalOpen" 
                     @click.outside="closeModal()"
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full border border-gray-100">
                    
                    <form @submit.prevent="submitMovement">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-8 relative">
                            <button type="button" @click="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                                <i class="fas fa-times"></i>
                            </button>
                            
                            <div class="flex flex-col items-center text-center mb-6">
                                <div class="h-16 w-16 rounded-2xl flex items-center justify-center shadow-md mb-4 transition-colors duration-300"
                                     :class="form.type === 'add' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600'">
                                    <i class="fas fa-2x" :class="form.type === 'add' ? 'fa-plus' : 'fa-minus'"></i>
                                </div>
                                <h3 class="text-xl font-extrabold text-[#2c3856]" x-text="form.type === 'add' ? 'Registrar Entrada' : 'Registrar Salida'"></h3>
                                <p class="text-sm font-medium text-[#ff9c00] mt-1" x-text="form.product_name"></p>
                            </div>

                            <div class="space-y-5">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Cantidad</label>
                                    <div class="relative">
                                        <input type="number" min="1" x-model.number="form.quantity_raw" required 
                                               class="block w-full pl-4 pr-4 py-3 bg-gray-50 border-none rounded-xl text-gray-900 focus:ring-2 focus:ring-[#2c3856] focus:bg-white transition-all font-bold text-lg text-center placeholder-gray-300" 
                                               placeholder="0">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Motivo / Referencia</label>
                                    <input type="text" x-model="form.reason" required 
                                           class="block w-full px-4 py-3 bg-gray-50 border-none rounded-xl text-gray-900 focus:ring-2 focus:ring-[#2c3856] focus:bg-white transition-all text-sm" 
                                           placeholder="Ej. Reposición de inventario...">
                                </div>
                            </div>

                            <div x-show="errorMessage" x-transition class="mt-4 p-3 rounded-xl bg-red-50 text-red-600 text-sm border border-red-100 flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                <span x-text="errorMessage"></span>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse gap-3 border-t border-gray-100">
                            <button type="submit" 
                                    :disabled="isSaving"
                                    class="w-full inline-flex justify-center items-center rounded-xl border border-transparent shadow-md px-4 py-3 text-sm font-bold text-white focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all transform hover:-translate-y-0.5"
                                    :class="form.type === 'add' ? 'bg-[#2c3856] hover:bg-[#1e273d] focus:ring-[#2c3856]' : 'bg-red-600 hover:bg-red-700 focus:ring-red-500'">
                                <i x-show="isSaving" class="fas fa-spinner fa-spin mr-2"></i>
                                <span x-text="isSaving ? 'Procesando...' : 'Confirmar Acción'"></span>
                            </button>
                            <button type="button" @click="closeModal()" class="mt-3 sm:mt-0 w-full inline-flex justify-center rounded-xl border border-gray-200 shadow-sm px-4 py-3 bg-white text-sm font-bold text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 transition-all">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div x-show="isImportModalOpen" 
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;" x-cloak>
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:block sm:p-0">
                
                <div x-show="isImportModalOpen" x-transition.opacity class="fixed inset-0 bg-[#2c3856] opacity-60 backdrop-blur-sm" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="isImportModalOpen" 
                     @click.outside="closeImportModal()"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-gray-100">
                    
                    <form action="{{ route('ff.inventory.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-8">
                            <div class="flex justify-between items-center mb-6">
                                <div class="flex items-center gap-3">
                                    <div class="bg-blue-100 p-2 rounded-lg text-[#2c3856]">
                                        <i class="fas fa-file-csv fa-lg"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-[#2c3856]">Importación Masiva</h3>
                                </div>
                                <button type="button" @click="closeImportModal()" class="text-gray-400 hover:text-gray-500">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            
                            <div class="mt-4">
                                <div class="flex items-center justify-center w-full">
                                    <label for="movements_file" class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-300 rounded-2xl cursor-pointer bg-gray-50 hover:bg-blue-50 hover:border-blue-300 transition-all group">
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 group-hover:text-[#ff9c00] mb-3 transition-colors"></i>
                                            <p class="mb-2 text-sm text-gray-500 font-medium"><span class="font-bold text-[#2c3856]">Clic para subir</span> o arrastra aquí</p>
                                            <p class="text-xs text-gray-400">Formato CSV (SKU, Quantity, Reason)</p>
                                        </div>
                                        <input id="movements_file" name="movements_file" type="file" class="hidden" required accept=".csv,.txt" />
                                    </label>
                                </div>
                                
                                <div class="mt-6 bg-[#E8ECF7] rounded-xl p-4 flex items-center justify-between">
                                    <div class="text-xs text-[#2c3856]">
                                        <strong>¿Necesitas el formato?</strong><br>
                                        Descarga la plantilla pre-llenada.
                                    </div>
                                    <button type="button" @click="downloadFilteredTemplate()" class="text-xs font-bold text-white bg-[#2c3856] px-3 py-2 rounded-lg hover:bg-[#1e273d] transition-colors">
                                        <i class="fas fa-download mr-1"></i> Plantilla
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse gap-3 border-t border-gray-100">
                            <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-md px-4 py-3 bg-[#ff9c00] text-sm font-bold text-white hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:w-auto transition-all">
                                Procesar Archivo
                            </button>
                            <button type="button" @click="closeImportModal()" class="mt-3 sm:mt-0 w-full inline-flex justify-center rounded-xl border border-gray-200 shadow-sm px-4 py-3 bg-white text-sm font-bold text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 transition-all">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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
                    quantity_raw: '',
                    reason: ''
                },

                init(data) {
                    let productsArray = Array.isArray(data) ? data : [];
                    this.products = productsArray.map(p => ({
                        ...p,
                        movements_sum_quantity: p.movements_sum_quantity ? parseInt(p.movements_sum_quantity, 10) : 0,
                        price: parseFloat(p.price) || 0
                    }));
                },

                get filteredProducts() {
                    const search = this.filter.toLowerCase();
                    return this.products.filter(p => {
                        if (this.filterBrand && p.brand !== this.filterBrand) return false;
                        if (this.filterType && p.type !== this.filterType) return false;
                        if (search) {
                            return p.sku.toLowerCase().includes(search) || 
                                   p.description.toLowerCase().includes(search);
                        }
                        return true;
                    });
                },

                get totalStock() {
                    return this.filteredProducts.reduce((acc, p) => acc + (p.movements_sum_quantity || 0), 0);
                },

                get totalValue() {
                    return this.filteredProducts.reduce((acc, p) => acc + ((p.price || 0) * (Math.max(0, p.movements_sum_quantity))), 0);
                },

                formatMoney(amount) {
                    return '$' + amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                },

                openModal(product, type) {
                    this.isModalOpen = true;
                    this.errorMessage = '';
                    this.form.product_id = product.id;
                    this.form.product_name = product.description;
                    this.form.type = type;
                    this.form.quantity_raw = ''; 
                    this.form.reason = '';
                    this.$nextTick(() => {
                        const input = document.querySelector('input[type="number"]');
                        if(input) input.focus();
                    });
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
                    
                    if (!this.form.quantity_raw || this.form.quantity_raw <= 0) {
                        this.errorMessage = "La cantidad debe ser mayor a 0.";
                        return;
                    }
                    if (!this.form.reason.trim()) {
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

                        if (!response.ok) throw new Error(data.message || 'Error desconocido');

                        const productIndex = this.products.findIndex(p => p.id === data.product_id);
                        if (productIndex > -1) {
                            this.products[productIndex].movements_sum_quantity = parseInt(data.new_total, 10);
                            this.products = [...this.products]; 
                        }
                        
                        this.closeModal();
                        
                        alert("Movimiento registrado correctamente");

                    } catch (error) {
                        console.error(error);
                        this.errorMessage = error.message || 'No se pudo conectar con el servidor.';
                    } finally {
                        this.isSaving = false;
                    }
                },

                exportFilteredCsv() {
                    const baseUrl = "{{ route('ff.inventory.exportCsv') }}";
                    const params = new URLSearchParams();
                    if (this.filter) params.append('search', this.filter);
                    if (this.filterBrand) params.append('brand', this.filterBrand);
                    if (this.filterType) params.append('type', this.filterType);
                    window.location.href = `${baseUrl}?${params.toString()}`;
                },

                downloadFilteredTemplate() {
                    const baseUrl = "{{ route('ff.inventory.movementsTemplate') }}";
                    const params = new URLSearchParams();
                    if (this.filter) params.append('search', this.filter);
                    if (this.filterBrand) params.append('brand', this.filterBrand);
                    if (this.filterType) params.append('type', this.filterType);
                    window.location.href = `${baseUrl}?${params.toString()}`;
                }
            }
        }
    </script>
</x-app-layout>