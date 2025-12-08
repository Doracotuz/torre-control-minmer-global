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
                       class="inline-flex items-center px-4 py-2 bg-white text-[#2c3856] border border-gray-200 rounded-xl text-xs font-bold uppercase tracking-wider shadow-sm hover:bg-gray-50 hover:border-[#ff9c00] transition-all duration-300">
                        <i class="fas fa-history mr-2"></i> Historial
                    </a>

                    <a href="{{ route('ff.dashboard.index') }}" 
                       class="inline-flex items-center px-5 py-2 bg-[#2c3856] text-white rounded-xl text-xs font-bold uppercase tracking-wider shadow-md hover:bg-[#1e273d] hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-300">
                        <i class="fas fa-arrow-left mr-2"></i> Dashboard
                    </a>
                </div>
            </div>
        </x-slot>

        <div class="py-6 bg-[#E8ECF7] min-h-screen">
            
            <div class="bg-white rounded-2xl p-2 shadow-[0_2px_15px_rgba(0,0,0,0.03)] border border-slate-100 mb-6 flex flex-col md:flex-row gap-2 items-center justify-between">
                
                <div class="flex flex-col md:flex-row gap-2 w-full md:w-auto">
                    <a href="{{ route('ff.inventory.backorders') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-purple-50 border border-transparent hover:border-purple-100 transition-all group w-full md:w-auto">
                        <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-lg shadow-sm group-hover:scale-105 group-hover:bg-purple-100 transition-all">
                            <i class="fas fa-boxes-packing"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Acción</span>
                            <span class="text-sm font-bold text-[#2c3856] group-hover:text-purple-700">Surtir Backorders</span>
                        </div>
                        <i class="fas fa-chevron-right text-slate-300 text-xs ml-2 opacity-0 group-hover:opacity-100 transition-opacity transform group-hover:translate-x-1"></i>
                    </a>

                    <div class="w-px h-10 bg-slate-100 hidden md:block"></div>

                    <a href="{{ route('ff.inventory.backorder_relations') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-rose-50 border border-transparent hover:border-rose-100 transition-all group w-full md:w-auto">
                        <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-lg shadow-sm group-hover:scale-105 group-hover:bg-rose-100 transition-all">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Reporte</span>
                            <span class="text-sm font-bold text-[#2c3856] group-hover:text-rose-700">Pasivos y Deuda</span>
                        </div>
                        <i class="fas fa-chevron-right text-slate-300 text-xs ml-2 opacity-0 group-hover:opacity-100 transition-opacity transform group-hover:translate-x-1"></i>
                    </a>
                </div>

                <div class="hidden lg:flex items-center gap-4 px-4">
                    <div class="text-right">
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Valor Inventario</p>
                        <p class="text-lg font-black text-[#2c3856] font-mono" x-text="formatMoney(totalValue)"></p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-[#ff9c00]/10 flex items-center justify-center text-[#ff9c00]">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                     class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-800 px-4 py-3 rounded-xl mb-6 shadow-sm flex items-center justify-between" role="alert">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2 text-xl"></i>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-600 hover:text-emerald-800"><i class="fas fa-times"></i></button>
                </div>
            @endif

            @if (session('import_errors'))
                <div class="bg-red-50 border-l-4 border-red-500 text-red-800 px-6 py-4 rounded-xl mb-6 shadow-sm">
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
                
                <div class="p-5 border-b border-gray-100 flex flex-col lg:flex-row gap-4 justify-between items-center bg-white/50 backdrop-blur-sm">
                    
                    <div class="relative w-full lg:w-1/3 group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-300 group-focus-within:text-[#ff9c00] transition-colors duration-300"></i>
                        </div>
                        <input type="text" x-model="filter" 
                               class="block w-full pl-11 pr-4 py-2.5 bg-slate-50 border-none text-gray-700 rounded-xl focus:ring-2 focus:ring-[#ff9c00] focus:bg-white transition-all duration-200 placeholder-gray-400 font-medium text-sm" 
                               placeholder="Buscar SKU, producto...">
                    </div>

                    <div class="flex flex-wrap gap-2 w-full lg:w-auto justify-end items-center">
                        
                        <select x-model="filterBrand" class="pl-3 pr-8 py-2 bg-white border border-gray-200 rounded-lg text-xs font-bold text-gray-600 focus:ring-2 focus:ring-[#ff9c00] focus:border-transparent cursor-pointer hover:border-gray-300 transition-all uppercase tracking-wide">
                            <option value="">Marca</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand }}">{{ $brand }}</option>
                            @endforeach
                        </select>

                        <select x-model="filterType" class="pl-3 pr-8 py-2 bg-white border border-gray-200 rounded-lg text-xs font-bold text-gray-600 focus:ring-2 focus:ring-[#ff9c00] focus:border-transparent cursor-pointer hover:border-gray-300 transition-all uppercase tracking-wide">
                            <option value="">Tipo</option>
                            @foreach($types as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                        
                        <div class="h-6 w-px bg-gray-200 mx-2 hidden md:block"></div>

                        <button @click="openImportModal()" 
                                class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-[#2c3856] hover:bg-slate-100 rounded-lg transition-all" 
                                title="Importar CSV">
                            <i class="fas fa-file-upload"></i>
                        </button>
                        
                        <button @click="exportFilteredCsv()" 
                                class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-[#2c3856] hover:bg-slate-100 rounded-lg transition-all" 
                                title="Exportar CSV">
                            <i class="fas fa-file-download"></i>
                        </button>

                        <button @click="resetFilters()" x-show="filter || filterBrand || filterType" x-transition 
                                class="ml-2 px-3 py-1.5 text-red-500 bg-red-50 hover:bg-red-100 rounded-lg text-xs font-bold transition-all" 
                                title="Limpiar Filtros">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full whitespace-nowrap text-left">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-gray-100">
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest font-[Montserrat]">Producto</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest font-[Montserrat]">Categoría</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right font-[Montserrat]">Precio</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center font-[Montserrat]">Stock</th>
                                @if(Auth::user()->isSuperAdmin() || Auth::user()->is_area_admin)
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right font-[Montserrat]">Acción</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 bg-white">
                            <template x-for="product in filteredProducts" :key="product.id">
                                <tr class="hover:bg-blue-50/40 transition-colors duration-200 group">
                                    
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="h-12 w-12 flex-shrink-0 rounded-lg border border-gray-100 overflow-hidden p-1 bg-white shadow-sm group-hover:scale-105 transition-transform">
                                                <img class="h-full w-full object-contain mix-blend-multiply" :src="product.photo_url" :alt="product.sku">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-bold text-[#2c3856]" x-text="product.description"></div>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <div class="text-[10px] text-slate-500 font-mono bg-slate-100 inline-block px-1.5 py-0.5 rounded border border-slate-200" x-text="product.sku"></div>
                                                    <div x-show="product.upc" class="text-[10px] text-slate-400 font-mono" x-text="'UPC: ' + product.upc"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600 w-fit uppercase" 
                                                  x-text="product.brand || 'N/A'"></span>
                                            <span class="text-[10px] text-slate-400 ml-0.5" x-text="product.type"></span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <div class="text-sm font-bold text-[#2c3856] font-mono" x-text="formatMoney(product.unit_price)"></div>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <div class="inline-flex items-center justify-center px-3 py-1 rounded-lg text-xs font-bold shadow-sm border transition-all duration-300"
                                             :class="{
                                                 'bg-emerald-50 text-emerald-700 border-emerald-100': (product.movements_sum_quantity || 0) > 5,
                                                 'bg-amber-50 text-amber-700 border-amber-100': (product.movements_sum_quantity || 0) > 0 && (product.movements_sum_quantity || 0) <= 5,
                                                 'bg-red-50 text-red-700 border-red-100': (product.movements_sum_quantity || 0) <= 0
                                             }">
                                            <span x-text="product.movements_sum_quantity || 0"></span>
                                            <span class="text-[9px] ml-1 opacity-70">pz</span>
                                        </div>
                                    </td>

                                    @if(Auth::user()->isSuperAdmin() || Auth::user()->is_area_admin)
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end space-x-1 opacity-60 group-hover:opacity-100 transition-opacity duration-200">
                                            <button @click="openModal(product, 'add')" 
                                                    class="h-8 w-8 flex items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white transition-all shadow-sm" 
                                                    title="Entrada">
                                                <i class="fas fa-plus text-xs"></i>
                                            </button>
                                            <button @click="openModal(product, 'remove')" 
                                                    class="h-8 w-8 flex items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white transition-all shadow-sm" 
                                                    title="Salida">
                                                <i class="fas fa-minus text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                    @endif
                                </tr>
                            </template>
                            
                            <template x-if="filteredProducts.length === 0">
                                <tr>
                                    <td colspan="5" class="px-6 py-20 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mb-4">
                                                <i class="fas fa-search text-slate-300 text-2xl"></i>
                                            </div>
                                            <h3 class="text-base font-bold text-[#2c3856]">Sin Resultados</h3>
                                            <p class="text-xs text-slate-400 mt-1">Ajusta los filtros de búsqueda.</p>
                                            <button @click="resetFilters()" class="mt-4 text-[#ff9c00] text-xs font-bold hover:underline">
                                                Ver todo
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                
                <div class="bg-white px-6 py-3 border-t border-gray-100 flex items-center justify-between text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    <span x-text="`Total: ${filteredProducts.length} Items`"></span>
                    <span x-text="`Stock Global: ${totalStock}`"></span>
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
                                <div class="h-14 w-14 rounded-2xl flex items-center justify-center shadow-sm mb-4 transition-colors duration-300"
                                     :class="form.type === 'add' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'">
                                    <i class="fas fa-lg" :class="form.type === 'add' ? 'fa-plus' : 'fa-minus'"></i>
                                </div>
                                <h3 class="text-lg font-black text-[#2c3856]" x-text="form.type === 'add' ? 'Entrada de Inventario' : 'Salida de Inventario'"></h3>
                                <p class="text-xs font-bold text-slate-400 mt-1 uppercase tracking-wide" x-text="form.product_name"></p>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Cantidad</label>
                                    <input type="number" min="1" x-model.number="form.quantity_raw" required 
                                           class="block w-full text-center py-3 bg-slate-50 border-none rounded-xl text-[#2c3856] focus:ring-2 focus:ring-[#2c3856] focus:bg-white transition-all font-bold text-2xl placeholder-slate-300" 
                                           placeholder="0">
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Motivo</label>
                                    <input type="text" x-model="form.reason" required 
                                           class="block w-full px-4 py-2.5 bg-slate-50 border-none rounded-xl text-slate-700 focus:ring-2 focus:ring-[#2c3856] focus:bg-white transition-all text-sm font-medium" 
                                           placeholder="Ej. Compra, Ajuste, Merma...">
                                </div>
                            </div>

                            <div x-show="errorMessage" x-transition class="mt-4 p-3 rounded-xl bg-rose-50 text-rose-600 text-xs font-bold border border-rose-100 flex items-center justify-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                <span x-text="errorMessage"></span>
                            </div>
                        </div>

                        <div class="bg-slate-50 px-6 py-4 flex gap-3 border-t border-slate-100">
                            <button type="button" @click="closeModal()" class="flex-1 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-600 hover:bg-slate-100 transition-all uppercase">
                                Cancelar
                            </button>
                            <button type="submit" 
                                    :disabled="isSaving"
                                    class="flex-1 py-2.5 rounded-xl border border-transparent shadow-lg text-xs font-bold text-white transition-all transform hover:-translate-y-0.5 uppercase flex items-center justify-center"
                                    :class="form.type === 'add' ? 'bg-[#2c3856] hover:bg-[#1e273d] shadow-blue-900/20' : 'bg-rose-500 hover:bg-rose-600 shadow-rose-500/30'">
                                <span x-text="isSaving ? 'Guardando...' : 'Confirmar'"></span>
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
                                    <div class="bg-blue-50 p-2 rounded-lg text-blue-600">
                                        <i class="fas fa-file-csv fa-lg"></i>
                                    </div>
                                    <h3 class="text-lg font-black text-[#2c3856]">Importación Masiva</h3>
                                </div>
                                <button type="button" @click="closeImportModal()" class="text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            
                            <div class="mt-4">
                                <div class="flex items-center justify-center w-full">
                                    <label for="movements_file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-slate-200 rounded-2xl cursor-pointer bg-slate-50 hover:bg-white hover:border-[#ff9c00] transition-all group">
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                            <i class="fas fa-cloud-upload-alt text-2xl text-slate-400 group-hover:text-[#ff9c00] mb-2 transition-colors"></i>
                                            <p class="mb-1 text-xs text-slate-500 font-bold">Clic para subir archivo</p>
                                            <p class="text-[10px] text-slate-400 uppercase">CSV (SKU, Qty, Reason)</p>
                                        </div>
                                        <input id="movements_file" name="movements_file" type="file" class="hidden" required accept=".csv,.txt" />
                                    </label>
                                </div>
                                
                                <div class="mt-4 flex items-center justify-between">
                                    <div class="text-[10px] text-slate-400 font-medium">
                                        ¿Necesitas el formato base?
                                    </div>
                                    <button type="button" @click="downloadFilteredTemplate()" class="text-[10px] font-bold text-[#2c3856] hover:underline flex items-center gap-1">
                                        <i class="fas fa-download"></i> Descargar Plantilla
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-slate-100">
                            <button type="submit" class="flex-1 py-2.5 rounded-xl bg-[#ff9c00] text-white text-xs font-bold uppercase hover:bg-orange-600 shadow-md transition-all">
                                Procesar
                            </button>
                            <button type="button" @click="closeImportModal()" class="flex-1 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-600 hover:bg-slate-100 uppercase transition-all">
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
                        unit_price: parseFloat(p.unit_price) || 0
                    }));
                },

                get filteredProducts() {
                    const search = this.filter.toLowerCase();
                    return this.products.filter(p => {
                        if (this.filterBrand && p.brand !== this.filterBrand) return false;
                        if (this.filterType && p.type !== this.filterType) return false;
                        if (search) {
                            return p.sku.toLowerCase().includes(search) || 
                                   p.description.toLowerCase().includes(search) ||
                                   (p.upc && p.upc.toLowerCase().includes(search));
                        }
                        return true;
                    });
                },

                get totalStock() {
                    return this.filteredProducts.reduce((acc, p) => acc + (p.movements_sum_quantity || 0), 0);
                },

                get totalValue() {
                    return this.filteredProducts.reduce((acc, p) => acc + ((p.unit_price || 0) * (Math.max(0, p.movements_sum_quantity))), 0);
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
                        
                        // Opcional: Toast
                        // alert("Movimiento registrado correctamente");

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