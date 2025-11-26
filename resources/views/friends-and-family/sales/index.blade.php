<x-app-layout>
    <x-slot name="header"></x-slot>
    <style>
        [x-cloak] { display: none !important; }
        /* Scroll elegante y sutil */
        .custom-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 4px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        .form-input-sm { 
            display: block; width: 100%; background-color: #f8fafc; 
            border: 1px solid #e2e8f0; border-radius: 0.5rem; 
            font-size: 0.8rem; padding: 0.5rem 0.75rem; transition: all 0.2s; 
        }
        .form-input-sm:focus { 
            background-color: #ffffff; border-color: #2c3856; 
            outline: none; box-shadow: 0 0 0 1px #2c3856; 
        }
        /* Ocultar flechas de input number */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    </style>

    <div x-data="salesManager()" x-init='init(@json($products), {{ $nextFolio }})' class="bg-gray-50 font-sans text-gray-800 min-h-screen pb-12">
        
        <div x-show="flashMessage" x-cloak 
             x-transition:enter="transform ease-out duration-300"
             x-transition:enter-start="-translate-y-2 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed top-4 left-1/2 transform -translate-x-1/2 z-[60] flex items-center px-6 py-3 rounded-xl shadow-xl border border-white/40 backdrop-blur-md"
             :class="{
                 'bg-emerald-600/90 text-white': flashType === 'success',
                 'bg-rose-600/90 text-white': flashType === 'danger',
                 'bg-[#2c3856]/90 text-white': flashType === 'info'
             }">
            <i class="fas mr-3 text-sm" :class="{
                'fa-check-circle': flashType === 'success',
                'fa-exclamation-circle': flashType === 'danger',
                'fa-info-circle': flashType === 'info'
            }"></i>
            <span class="font-medium text-sm" x-text="flashMessage"></span>
        </div>

        <div class="max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
            
            <div class="flex flex-col lg:flex-row gap-6 items-start">
                
                <div class="w-full lg:w-9/12 flex flex-col gap-6">
                    
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 top-4 z-30">
                        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4">
                            <div>
                                <h1 class="text-2xl font-bold text-[#2c3856] tracking-tight">Gestión de Pedidos</h1>
                                <p class="text-xs text-gray-500 mt-1 flex items-center gap-2">
                                    <span x-show="!editMode">Creación de nueva venta</span>
                                    <span x-show="editMode" class="text-orange-600 font-bold bg-orange-50 px-2 py-0.5 rounded-full animate-pulse border border-orange-100">
                                        <i class="fas fa-edit mr-1"></i> Editando #<span x-text="form.folio"></span>
                                    </span>
                                </p>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row gap-3 w-full xl:w-auto">
                                
                                <select x-model="filters.brand" class="bg-gray-50 border-gray-200 text-gray-600 text-xs rounded-lg focus:ring-[#2c3856] focus:border-[#2c3856] py-2.5 px-3 min-w-[140px]">
                                    <option value="">Todas las marcas</option>
                                    <template x-for="brand in uniqueBrands" :key="brand">
                                        <option :value="brand" x-text="brand"></option>
                                    </template>
                                </select>

                                <select x-model="filters.type" class="bg-gray-50 border-gray-200 text-gray-600 text-xs rounded-lg focus:ring-[#2c3856] focus:border-[#2c3856] py-2.5 px-3 min-w-[140px]">
                                    <option value="">Todos los tipos</option>
                                    <template x-for="type in uniqueTypes" :key="type">
                                        <option :value="type" x-text="type"></option>
                                    </template>
                                </select>

                                <div class="relative flex-grow">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <input type="text" x-model="filters.search" class="block w-full pl-9 pr-4 py-2.5 bg-gray-50 border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-[#2c3856] focus:border-[#2c3856] transition-all placeholder-gray-400" placeholder="Buscar SKU, producto...">
                                </div>

                                <div class="flex gap-2">
                                    <button @click="downloadTemplate()" class="px-3 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 hover:text-[#2c3856] transition-colors" title="Descargar Plantilla CSV (con filtros actuales)">
                                        <i class="fas fa-file-download"></i>
                                    </button>
                                    <button @click="$refs.csvImportInput.click()" class="px-3 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 hover:text-[#2c3856] transition-colors" title="Importar Pedido desde CSV">
                                        <i class="fas fa-file-upload"></i>
                                    </button>
                                    <input type="file" x-ref="csvImportInput" class="hidden" accept=".csv" @change="importCsvOrder($event)">

                                    <button @click="openSearchModal()" class="px-4 py-2 bg-[#2c3856] text-white hover:bg-[#1e273d] rounded-lg font-bold text-xs transition-all shadow-sm whitespace-nowrap">
                                        <i class="fas fa-history mr-2"></i> Buscar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-6">
                        <template x-for="product in filteredProducts" :key="product.id">
                            <div class="group bg-white rounded-2xl p-4 shadow-[0_2px_8px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_20px_rgb(0,0,0,0.08)] transition-all duration-300 border border-gray-100 relative flex flex-col h-full hover:-translate-y-1"
                                 :class="{ 'ring-2 ring-[#2c3856] ring-offset-2': getProductInCart(product.id) > 0 }">
                                
                                <div class="absolute top-3 right-3 z-10 flex flex-col gap-1 items-end">
                                    <span x-show="getProductInCart(product.id) > 0" class="px-2 py-0.5 bg-[#2c3856] text-white text-[10px] font-bold rounded shadow-sm">
                                        En carrito
                                    </span>
                                    <span x-show="getAvailableStock(product) < 10 && getAvailableStock(product) > 0" class="px-2 py-0.5 bg-amber-100 text-amber-700 text-[10px] font-bold rounded border border-amber-200">
                                        Poco Stock
                                    </span>
                                </div>

                                <div x-show="getAvailableStock(product) <= 0 && !getProductInCart(product.id)" class="absolute inset-0 bg-white/60 z-20 backdrop-blur-[1px] flex items-center justify-center rounded-2xl transition-opacity">
                                    <div class="bg-gray-100 text-gray-500 px-4 py-2 rounded-lg font-bold text-xs border border-gray-200 shadow-sm flex items-center uppercase tracking-wide">
                                        <i class="fas fa-ban mr-2"></i> Agotado
                                    </div>
                                </div>

                                <div class="w-full aspect-square rounded-xl bg-gray-50 mb-4 overflow-hidden border border-gray-100 flex items-center justify-center relative">
                                    <img :src="product.photo_url" class="max-w-full max-h-full object-contain p-4 transition-transform duration-500 group-hover:scale-110 mix-blend-multiply" loading="lazy">
                                </div>

                                <div class="flex-1 flex flex-col">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="text-[10px] font-mono font-bold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded border border-gray-200" x-text="product.sku"></span>
                                        <span class="text-[10px] font-bold text-[#2c3856] uppercase tracking-wide truncate max-w-[100px]" x-text="product.brand"></span>
                                    </div>
                                    
                                    <h3 class="text-sm font-bold text-gray-800 leading-snug line-clamp-2 mb-4 h-10" x-text="product.description" :title="product.description"></h3>
                                    
                                    <div class="mt-auto pt-3 border-t border-gray-50">
                                        <div class="flex justify-between items-end mb-3">
                                            <div>
                                                <p class="text-[10px] text-gray-400 font-bold uppercase mb-0.5">Precio</p>
                                                <div class="text-lg font-extrabold text-[#2c3856]" x-text="formatCurrency(product.unit_price)"></div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-[10px] uppercase font-bold text-gray-400 mb-0.5">Disp.</p>
                                                <div class="text-sm font-bold" :class="getAvailableStock(product) > 0 ? 'text-emerald-600' : 'text-red-500'" x-text="getAvailableStock(product)"></div>
                                            </div>
                                        </div>

                                        <div class="flex items-center bg-gray-50 rounded-lg p-1 border border-gray-200 shadow-inner">
                                            <button @click="updateQuantity(product, -1)" 
                                                class="w-8 h-8 flex items-center justify-center rounded-md bg-white text-gray-500 shadow-sm border border-gray-100 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all active:scale-95 disabled:opacity-50" 
                                                :disabled="!getProductInCart(product.id)">
                                                <i class="fas fa-minus text-xs"></i>
                                            </button>
                                            
                                            <input type="number" 
                                                class="flex-1 w-full bg-transparent border-none text-center font-bold text-gray-800 focus:ring-0 p-0 text-sm" 
                                                :value="getProductInCart(product.id) || 0" 
                                                @change="validateInput($event, product)"
                                                @focus="$event.target.select()">
                                                
                                            <button @click="updateQuantity(product, 1)" 
                                                class="w-8 h-8 flex items-center justify-center rounded-md bg-[#2c3856] text-white shadow-md hover:bg-[#1e273d] hover:shadow-lg transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed" 
                                                :disabled="getAvailableStock(product) <= 0">
                                                <i class="fas fa-plus text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                     <div x-show="filteredProducts.length === 0" class="col-span-full py-20 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                            <i class="fas fa-search text-gray-400 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">No hay productos</h3>
                        <p class="text-gray-500 text-sm">Intenta cambiar los filtros de búsqueda.</p>
                        <button @click="resetFilters()" class="mt-4 text-[#2c3856] text-sm font-bold hover:underline">Limpiar filtros</button>
                    </div>
                </div>

                <div class="w-full lg:w-3/12 sticky top-6 self-start h-[calc(100vh-3rem)] flex flex-col z-20">
                    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden flex flex-col h-full relative">
                        
                        <div class="p-5 text-white flex-shrink-0 relative overflow-hidden transition-colors duration-500"
                             :class="editMode ? 'bg-orange-600' : 'bg-[#2c3856]'">
                            <div class="absolute top-0 right-0 p-4 opacity-10">
                                <i class="fas fa-shopping-cart text-6xl"></i>
                            </div>
                            <div class="relative z-10">
                                <div class="flex justify-between items-center mb-2">
                                    <h2 class="text-xs font-bold uppercase tracking-widest opacity-90" x-text="editMode ? 'Modo Edición' : 'Nuevo Pedido'"></h2>
                                    <button x-show="editMode" @click="cancelEditMode()" class="bg-white/20 hover:bg-white/30 px-2 py-1 rounded text-[10px] font-bold text-white transition-colors backdrop-blur-sm">
                                        <i class="fas fa-times mr-1"></i> Cancelar
                                    </button>
                                </div>
                                <div class="flex items-baseline justify-between mt-2">
                                    <span class="text-sm opacity-80 font-medium">Total Estimado</span>
                                    <span class="text-2xl font-black tracking-tight" x-text="formatCurrency(totalVenta)"></span>
                                </div>
                                <div class="mt-2 text-[10px] font-medium opacity-70 flex justify-between">
                                    <span x-text="localCart.size + ' Items'"></span>
                                    <span x-text="totalPiezas + ' Unidades'"></span>
                                </div>
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto custom-scroll p-5 bg-white space-y-6">
                            
                            <div class="space-y-3">
                                <div class="flex items-center text-[#2c3856] font-bold text-[11px] uppercase tracking-wider border-b border-gray-100 pb-1">
                                    <i class="fas fa-file-invoice mr-2 opacity-50"></i> Datos de Remisión
                                </div>
                                <div class="grid grid-cols-12 gap-2">
                                    <div class="col-span-4">
                                        <label class="text-[9px] font-bold text-gray-400 uppercase ml-1">Folio</label>
                                        <input type="number" x-model="form.folio" :disabled="editMode"
                                               class="w-full bg-gray-50 border-gray-200 rounded-lg text-xs font-bold focus:ring-[#2c3856] focus:border-[#2c3856] py-2 disabled:opacity-60 text-center">
                                    </div>
                                    <div class="col-span-8">
                                        <label class="text-[9px] font-bold text-gray-400 uppercase ml-1">Entrega</label>
                                        <input type="datetime-local" x-model="form.delivery_date" class="w-full bg-gray-50 border-gray-200 rounded-lg text-[10px] focus:ring-[#2c3856] focus:border-[#2c3856] py-2">
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center text-[#2c3856] font-bold text-[11px] uppercase tracking-wider border-b border-gray-100 pb-1">
                                    <i class="fas fa-user mr-2 opacity-50"></i> Cliente
                                </div>
                                <input type="text" x-model="form.client_name" placeholder="Nombre Cliente" class="form-input-sm">
                                <input type="text" x-model="form.company_name" placeholder="Empresa / Razón Social" class="form-input-sm">
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="text" x-model="form.client_phone" placeholder="Teléfono" class="form-input-sm">
                                    <input type="text" x-model="form.locality" placeholder="Localidad / Zona" class="form-input-sm">
                                </div>
                                <textarea x-model="form.address" rows="2" placeholder="Dirección Completa" class="form-input-sm resize-none"></textarea>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center text-[#2c3856] font-bold text-[11px] uppercase tracking-wider border-b border-gray-100 pb-1">
                                    <i class="fas fa-truck mr-2 opacity-50"></i> Logística
                                </div>
                                <input type="text" x-model="form.surtidor_name" placeholder="Nombre Surtidor" class="form-input-sm">
                                <input type="text" x-model="form.email_recipients" placeholder="Notificar a: email1; email2;" class="form-input-sm">
                                <textarea x-model="form.observations" rows="2" placeholder="Observaciones (Salen en PDF)" class="form-input-sm resize-none"></textarea>
                            </div>

                            <div x-show="globalError" x-transition class="p-3 rounded-lg bg-red-50 border border-red-100 text-red-600 text-xs text-center font-medium">
                                <i class="fas fa-exclamation-triangle mr-1"></i> <span x-text="globalError"></span>
                            </div>
                        </div>

                        <div class="p-4 bg-gray-50 border-t border-gray-200 space-y-2 flex-shrink-0 z-10">
                            <button @click="submitCheckout()"
                                    :disabled="isSaving || isPrinting || localCart.size === 0 || !isFormValid"
                                    class="w-full flex items-center justify-center py-3 px-4 rounded-xl text-white font-bold text-xs uppercase tracking-wider shadow-lg transition-all transform active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed group"
                                    :class="editMode ? 'bg-orange-600 hover:bg-orange-700' : (isFormValid && localCart.size > 0 ? 'bg-[#2c3856] hover:bg-[#1e273d]' : 'bg-gray-400 cursor-not-allowed')">
                                
                                <span x-show="!isSaving" class="flex items-center">
                                    <span x-text="editMode ? 'Actualizar Pedido' : 'Generar Venta'"></span>
                                    <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                                </span>
                                <span x-show="isSaving"><i class="fas fa-circle-notch fa-spin"></i></span>
                            </button>

                            <div class="flex gap-2">
                                <button x-show="editMode" @click="confirmCancelOrder()"
                                        :disabled="isSaving"
                                        class="flex-1 py-2 px-3 rounded-lg bg-white text-red-600 border border-gray-200 font-bold text-[10px] uppercase tracking-wide hover:bg-red-50 transition-colors">
                                    <i class="fas fa-trash-alt mr-1"></i> Cancelar
                                </button>

                                <button x-show="!editMode" @click="printProductList()"
                                        :disabled="isSaving || isPrinting"
                                        class="flex-1 py-2 px-3 rounded-lg bg-white border border-gray-200 text-gray-500 font-bold text-[10px] uppercase tracking-wide hover:bg-gray-100 hover:text-gray-700 transition-colors">
                                    <i class="fas fa-print mr-1" :class="{'fa-spin': isPrinting}"></i> Picking
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div x-show="searchModalOpen" x-cloak class="fixed inset-0 z-[70] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="searchModalOpen = false"></div>
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm w-full p-6">
                    <div class="text-center mb-6">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-indigo-50 mb-4">
                            <i class="fas fa-history text-[#2c3856] text-xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Buscar Pedido</h3>
                        <p class="text-sm text-gray-500">Ingrese el folio para editar un pedido existente.</p>
                    </div>
                    
                    <input type="number" x-model="searchFolio" class="w-full bg-gray-50 border-gray-200 rounded-xl shadow-sm focus:ring-[#2c3856] focus:border-[#2c3856] text-center font-bold text-lg mb-6 py-3" placeholder="Ej: 10001">
                    
                    <div class="flex gap-3">
                        <button @click="searchModalOpen = false" class="flex-1 bg-white border border-gray-300 text-gray-700 font-bold py-2.5 rounded-xl text-sm hover:bg-gray-50">Cancelar</button>
                        <button @click="loadOrderToEdit()" class="flex-1 bg-[#2c3856] text-white font-bold py-2.5 rounded-xl text-sm hover:bg-[#1e273d] shadow-lg" :disabled="isSearching">
                            <span x-show="!isSearching">Buscar</span>
                            <i x-show="isSearching" class="fas fa-spinner fa-spin"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
    
    <script>
        function salesManager() {
            return {
                products: [],
                localCart: new Map(),
                
                filters: {
                    search: '',
                    brand: '',
                    type: ''
                },
                
                isSaving: false,
                isPrinting: false,
                isSearching: false,
                searchModalOpen: false,
                searchFolio: '',
                editMode: false,
                globalError: '',
                pollingInterval: null,
                
                form: {
                    folio: '', client_name: '', company_name: '', client_phone: '',
                    address: '', locality: '', delivery_date: '', surtidor_name: '',
                    observations: '', email_recipients: ''
                },
                
                flashMessage: '',
                flashType: 'info',
                flashTimeout: null,

                init(initialProducts, initialFolio) {
                    const productsArray = Array.isArray(initialProducts) ? initialProducts : [];
                    this.form.folio = initialFolio;

                    this.products = productsArray.map((p, index) => {
                        const myCartItem = p.cart_items.find(item => item.user_id === {{ Auth::id() }});
                        if (myCartItem) {
                            this.localCart.set(myCartItem.ff_product_id, myCartItem.quantity);
                        }
                        return {
                            ...p,
                            originalIndex: index + 1,
                            photo_url: p.photo_url, 
                            total_stock: p.movements_sum_quantity ? parseInt(p.movements_sum_quantity) : 0,
                            reserved_by_others: p.reserved_by_others ? parseInt(p.reserved_by_others) : 0,
                            unit_price: parseFloat(p.unit_price) || 0,
                            brand: p.brand || 'Sin Marca',
                            type: p.type || 'General',
                            cart_items: [],
                            error: ''
                        };
                    });
                    
                    const savedEmails = localStorage.getItem('ff_email_recipients');
                    if (savedEmails) this.form.email_recipients = savedEmails;
                    
                    this.pollingInterval = setInterval(() => this.pollReservations(), 10000);
                },

                get uniqueBrands() {
                    return [...new Set(this.products.map(p => p.brand).filter(b => b))].sort();
                },
                
                get uniqueTypes() {
                    return [...new Set(this.products.map(p => p.type).filter(t => t))].sort();
                },

                get filteredProducts() {
                    let result = this.products;
                    
                    const search = this.filters.search.toLowerCase();
                    if (search) {
                        result = result.filter(p => p.sku.toLowerCase().includes(search) || p.description.toLowerCase().includes(search));
                    }
                    
                    if (this.filters.brand) {
                        result = result.filter(p => p.brand === this.filters.brand);
                    }
                    
                    if (this.filters.type) {
                        result = result.filter(p => p.type === this.filters.type);
                    }

                    return result;
                },

                get totalVenta() {
                    let total = 0;
                    this.localCart.forEach((quantity, productId) => {
                        const product = this.products.find(p => p.id === productId);
                        if (product) { total += product.unit_price * quantity; }
                    });
                    return total;
                },

                get totalPiezas() {
                    let total = 0;
                    this.localCart.forEach((qty) => total += qty);
                    return total;
                },

                get isFormValid() {
                    return this.form.folio && this.form.client_name && this.form.company_name;
                },

                resetFilters() {
                    this.filters.search = '';
                    this.filters.brand = '';
                    this.filters.type = '';
                },

                formatCurrency(value) {
                    if (isNaN(value)) value = 0;
                    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(value);
                },
                
                getAvailableStock(product) {
                    return product.total_stock - product.reserved_by_others;
                },

                getProductInCart(productId) { return this.localCart.get(productId); },

                validateInput(event, product) {
                    let value = parseInt(event.target.value);
                    if (isNaN(value) || value < 0) value = 0;
                    
                    const currentCartQty = this.localCart.get(product.id) || 0;
                    const availableReal = this.getAvailableStock(product) + currentCartQty;

                    if (value > availableReal) {
                        value = availableReal;
                        this.showFlashMessage(`Stock máximo disponible: ${availableReal}`, 'danger');
                    }
                    
                    event.target.value = value;
                    
                    this.onQuantityChange(value, product);
                },

                updateQuantity(product, change) {
                    const currentQty = this.getProductInCart(product.id) || 0;
                    const newQty = currentQty + change;
                    this.onQuantityChange(newQty, product);
                },

                async onQuantityChange(newQuantity, product) {
                    const currentCartQty = this.getProductInCart(product.id) || 0;
                    const maxAvailable = this.getAvailableStock(product) + currentCartQty;

                    if (newQuantity < 0) newQuantity = 0;
                    if (newQuantity > maxAvailable) {
                        newQuantity = maxAvailable;
                        this.showFlashMessage('Stock insuficiente', 'danger');
                    }
                    
                    if (newQuantity === 0) { this.localCart.delete(product.id); } 
                    else { this.localCart.set(product.id, newQuantity); }

                    try {
                        const response = await fetch("{{ route('ff.sales.cart.update') }}", {
                            method: 'POST',
                            body: JSON.stringify({ product_id: product.id, quantity: newQuantity }),
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json', 'Content-Type': 'application/json' }
                        });
                        const data = await response.json();
                        if (!response.ok) {
                            this.showFlashMessage(data.message, 'danger');
                            if (data.new_quantity === 0 || data.new_quantity === undefined) this.localCart.delete(product.id);
                            else this.localCart.set(product.id, data.new_quantity);
                        }
                    } catch (e) {
                        this.showFlashMessage('Error de conexión.', 'danger');
                    }
                },

                downloadTemplate() {
                    let url = "{{ route('ff.sales.downloadTemplate') }}";
                    const params = new URLSearchParams();
                    if(this.filters.brand) params.append('brand', this.filters.brand);
                    if(this.filters.type) params.append('type', this.filters.type);
                    if(this.filters.search) params.append('search', this.filters.search);
                    
                    window.location.href = url + '?' + params.toString();
                },

                async importCsvOrder(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    const formData = new FormData();
                    formData.append('order_csv', file);

                    this.isSaving = true;
                    try {
                        const response = await fetch("{{ route('ff.sales.importOrder') }}", { 
                            method: 'POST', body: formData,
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json'}
                        });
                        const data = await response.json();
                        
                        if(response.ok) {
                            this.showFlashMessage(data.message || 'Pedido importado al carrito', 'success');
                            if(data.cart_items) {
                                this.localCart.clear();
                                data.cart_items.forEach(item => this.localCart.set(item.id, item.qty));
                            }
                            event.target.value = '';
                        } else {
                            this.showFlashMessage(data.message || 'Error en importación', 'danger');
                        }
                    } catch (e) {
                        this.showFlashMessage('Error al subir archivo', 'danger');
                    } finally {
                        this.isSaving = false;
                    }
                },

                async pollReservations() {
                     try {
                        const response = await fetch("{{ route('ff.sales.reservations') }}");
                        const reservations = await response.json();
                        this.products.forEach((product, index) => {
                            const newReserved = reservations[product.id] ? parseInt(reservations[product.id]) : 0;
                            this.products[index].reserved_by_others = newReserved;
                        });
                    } catch (e) { console.error(e); }
                },
                
                showFlashMessage(message, type = 'info', duration = 4000) {
                    clearTimeout(this.flashTimeout);
                    this.flashMessage = message;
                    this.flashType = type;
                    this.flashTimeout = setTimeout(() => { this.flashMessage = ''; }, duration);
                },
                
                openSearchModal() { this.searchFolio = ''; this.searchModalOpen = true; },
                
                async loadOrderToEdit() {
                    if (!this.searchFolio) return;
                    this.isSearching = true;
                    try {
                        const response = await fetch("{{ route('ff.sales.searchOrder') }}?folio=" + this.searchFolio);
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message);
                        
                        this.form = { ...this.form, ...data.client_data, folio: this.searchFolio };
                        this.localCart.clear();
                        if (data.cart_items) data.cart_items.forEach(item => this.localCart.set(item.product_id, item.quantity));
                        
                        this.editMode = true;
                        this.searchModalOpen = false;
                        this.showFlashMessage('Pedido cargado. Puede editar.', 'success');
                    } catch (e) { alert(e.message || 'Error al buscar.'); } finally { this.isSearching = false; }
                },

                cancelEditMode() {
                    if(confirm('¿Salir del modo edición?')) window.location.href = "{{ route('ff.sales.index') }}"; 
                },

                async confirmCancelOrder() {
                    const reason = prompt("¿Motivo de cancelación?");
                    if (!reason) return;
                    this.isSaving = true;
                    try {
                        const response = await fetch("{{ route('ff.sales.cancelOrder') }}", {
                            method: 'POST', body: JSON.stringify({ folio: this.form.folio, reason: reason }),
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json', 'Content-Type': 'application/json' }
                        });
                        const data = await response.json();
                        if(response.ok) { alert(data.message); window.location.reload(); }
                        else { alert(data.message); }
                    } catch(e) { alert("Error al cancelar."); } finally { this.isSaving = false; }
                },

                async submitCheckout() {
                    this.isSaving = true;
                    this.globalError = '';
                    if (this.form.email_recipients) localStorage.setItem('ff_email_recipients', this.form.email_recipients);

                    try {
                        const response = await fetch("{{ route('ff.sales.checkout') }}", {
                            method: 'POST', body: JSON.stringify({ ...this.form, is_edit_mode: this.editMode }),
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json', 'Content-Type': 'application/json' }
                        });

                        if (response.status === 200 && response.headers.get('Content-Type') === 'application/pdf') {
                            const blob = await response.blob();
                            window.open(window.URL.createObjectURL(blob));
                            this.showFlashMessage(`¡${this.editMode ? 'Actualización' : 'Venta'} exitosa!`, 'success');
                            setTimeout(() => window.location.href = "{{ route('ff.sales.index') }}", 2000);
                        } else {
                            const data = await response.json();
                            this.globalError = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : "Error");
                            this.showFlashMessage(this.globalError, 'danger');
                        }
                    } catch (e) { this.globalError = 'Error de conexión.'; this.showFlashMessage(this.globalError, 'danger'); } finally { this.isSaving = false; }
                },

                async printProductList() {
                    let sets = prompt("¿Número de copias?", "1");
                    if (!sets) return; 
                    this.isPrinting = true;
                    try {
                        const productsToPrint = this.filteredProducts.filter(p => p.is_active).map(p => ({ sku: p.sku, description: p.description, unit_price: p.unit_price }));
                        const response = await fetch("{{ route('ff.sales.printList') }}", {
                            method: 'POST', body: JSON.stringify({ products: productsToPrint, numSets: sets }),
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json', 'Content-Type': 'application/json' }
                        });
                        if (response.headers.get('Content-Type') === 'application/pdf') {
                            window.open(window.URL.createObjectURL(await response.blob()));
                        }
                    } catch (e) { this.showFlashMessage('Error al imprimir.', 'danger'); } finally { this.isPrinting = false; }
                }
            }
        }
    </script>
</x-app-layout>