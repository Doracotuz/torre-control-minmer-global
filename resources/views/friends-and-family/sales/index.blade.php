<x-app-layout>
    <x-slot name="header"></x-slot>
    <style>
        [x-cloak] { display: none !important; }
        .custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 4px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: #a0aec0; }
        .app-content-height { height: calc(100vh - 65px); }
        .form-input-sm { display: block; width: 100%; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.5rem; font-size: 0.875rem; padding: 0.5rem 0.75rem; transition: all 0.2s; }
        .form-input-sm:focus { background-color: #ffffff; border-color: #2c3856; outline: none; box-shadow: 0 0 0 1px #2c3856; }
        .form-input-sm::placeholder { color: #9ca3af; }
    </style>

    <div x-data="salesManager()" x-init='init(@json($products), {{ $nextFolio }})' class="bg-[#f8fafc] font-sans text-gray-800">
        
        <div x-show="flashMessage" x-cloak 
             x-transition:enter="transform ease-out duration-300"
             x-transition:enter-start="translate-y-2 opacity-0 scale-95"
             x-transition:enter-end="translate-y-0 opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed top-6 left-1/2 transform -translate-x-1/2 z-50 flex items-center px-6 py-4 rounded-full shadow-2xl border border-white/20 backdrop-blur-md"
             :class="{
                 'bg-emerald-600 text-white': flashType === 'success',
                 'bg-rose-600 text-white': flashType === 'danger',
                 'bg-sky-600 text-white': flashType === 'info'
             }">
            <i class="fas mr-3 text-lg" :class="{
                'fa-check-circle': flashType === 'success',
                'fa-exclamation-circle': flashType === 'danger',
                'fa-info-circle': flashType === 'info'
            }"></i>
            <span class="font-semibold tracking-wide text-sm" x-text="flashMessage"></span>
        </div>

        <div class="flex flex-col lg:flex-row app-content-height overflow-hidden">
            
            <div class="w-full lg:w-8/12 xl:w-9/12 h-full flex flex-col relative border-r border-gray-200 bg-[#f8fafc]">
                
                <div class="px-6 py-4 bg-white border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 z-10 shadow-sm">
                    <div>
                        <h1 class="text-2xl font-extrabold text-gray-800 tracking-tight">Gestión de pedidos</h1>
                        <p class="text-xs text-gray-500 font-medium mt-0.5">
                            <span x-show="!editMode">Carga o edición de pedidos</span>
                            <span x-show="editMode" class="text-orange-600 font-bold animate-pulse">
                                <i class="fas fa-edit mr-1"></i> Editando Pedido #<span x-text="form.folio"></span>
                            </span>
                        </p>
                    </div>
                    
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <div class="relative flex-grow sm:flex-grow-0 sm:w-64 group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400 group-focus-within:text-[#ff9c00]"></i>
                            </div>
                            <input type="text" x-model="filter" class="block w-full pl-9 pr-4 py-2 bg-gray-50 border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#ff9c00] focus:border-transparent transition-all placeholder-gray-400" placeholder="Buscar producto...">
                        </div>

                        <button @click="openSearchModal()" class="flex items-center justify-center px-4 py-2 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-lg font-bold text-xs uppercase tracking-wide transition-colors border border-indigo-100 whitespace-nowrap" title="Buscar y Editar Pedido">
                            <i class="fas fa-history mr-2"></i> Buscar Pedido
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto custom-scroll p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-6 pb-20">
                        <template x-for="product in filteredProducts" :key="product.id">
                            <div class="group bg-white rounded-2xl p-4 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 relative flex flex-col h-full"
                                 :class="{ 'ring-2 ring-[#2c3856] ring-offset-1': getProductInCart(product.id) > 0 }">
                                
                                <div class="absolute top-3 right-3 z-10 flex flex-col gap-1 items-end">
                                    <span x-show="getProductInCart(product.id) > 0" class="px-2 py-1 bg-[#2c3856] text-white text-[10px] font-bold rounded-md shadow-sm animate-pulse">
                                        En carrito: <span x-text="getProductInCart(product.id)"></span>
                                    </span>
                                    <span x-show="getAvailableStock(product) < 10 && getAvailableStock(product) > 0" class="px-2 py-1 bg-amber-100 text-amber-700 text-[10px] font-bold rounded-md border border-amber-200">Poco Stock</span>
                                </div>

                                <div x-show="getAvailableStock(product) <= 0 && !getProductInCart(product.id)" class="absolute inset-0 bg-white/60 z-20 backdrop-blur-[1px] flex items-center justify-center rounded-2xl">
                                    <div class="bg-gray-100 text-gray-500 px-4 py-2 rounded-lg font-bold text-sm border border-gray-200 shadow-sm flex items-center"><i class="fas fa-ban mr-2"></i> Agotado</div>
                                </div>

                                <div class="w-full aspect-square rounded-xl bg-gray-50 mb-4 overflow-hidden border border-gray-100 flex items-center justify-center relative group-hover:border-gray-300 transition-colors">
                                    <img :src="product.photo_url" class="max-w-full max-h-full object-contain p-2 transition-transform duration-500 group-hover:scale-110" :alt="product.description" onerror="this.onerror=null; this.src='https://placehold.co/200x200?text=Sin+Imagen';">
                                </div>

                                <div class="flex-1 flex flex-col">
                                    <div class="flex justify-between items-start mb-1">
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider bg-gray-100 px-1.5 py-0.5 rounded" x-text="product.sku"></p>
                                        <p class="text-[10px] text-gray-400 truncate max-w-[80px]" x-text="product.brand"></p>
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-800 leading-snug line-clamp-2 mb-3" x-text="product.description" :title="product.description"></h3>
                                    <div class="mt-auto pt-3 border-t border-gray-50">
                                        <div class="flex justify-between items-end mb-3">
                                            <div><span class="text-xs text-gray-400">Precio</span><div class="text-lg font-black text-gray-900" x-text="formatCurrency(product.unit_price)"></div></div>
                                            <div class="text-right">
                                                <span class="text-[10px] uppercase font-bold text-gray-400">Disponibles</span>
                                                <div class="text-sm font-bold" :class="getAvailableStock(product) > 0 ? 'text-emerald-600' : 'text-red-500'" x-text="getAvailableStock(product)"></div>
                                            </div>
                                        </div>
                                        <div class="flex items-center bg-gray-50 rounded-lg p-1 border border-gray-200 shadow-sm">
                                            <button @click="updateQuantity(product, -1)" class="w-8 h-8 flex items-center justify-center rounded-md bg-white text-gray-500 shadow-sm border border-gray-100 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all active:scale-95 disabled:opacity-50" :disabled="!getProductInCart(product.id)"><i class="fas fa-minus text-xs"></i></button>
                                            <input type="number" class="flex-1 w-full bg-transparent border-none text-center font-bold text-gray-800 focus:ring-0 p-0 text-sm appearance-none" :value="getProductInCart(product.id) || 0" @change="onQuantityInput($event, product)" readonly>
                                            <button @click="updateQuantity(product, 1)" class="w-8 h-8 flex items-center justify-center rounded-md bg-[#2c3856] text-white shadow-md hover:bg-[#1e273d] hover:shadow-lg transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed" :disabled="getAvailableStock(product) <= 0"><i class="fas fa-plus text-xs"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-4/12 xl:w-3/12 bg-white h-full flex flex-col shadow-2xl z-10 relative">
                
                <div class="p-6 text-white shadow-md flex-shrink-0 relative overflow-hidden transition-colors duration-300"
                     :class="editMode ? 'bg-orange-600' : 'bg-[#2c3856]'">
                    <div class="relative z-10">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-base font-bold uppercase tracking-widest opacity-90" x-text="editMode ? 'Editando Pedido' : 'Nuevo Pedido'"></h2>
                            <button x-show="editMode" @click="cancelEditMode()" class="bg-white/20 hover:bg-white/30 px-2 py-1 rounded text-xs font-bold text-white transition-colors">
                                <i class="fas fa-times mr-1"></i> Cancelar Edición
                            </button>
                        </div>
                        <div class="flex items-baseline justify-between">
                            <span class="text-sm opacity-80">Total Estimado</span>
                            <span class="text-3xl font-black tracking-tight" x-text="formatCurrency(totalVenta)"></span>
                        </div>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto custom-scroll p-5 space-y-6 bg-white">
                    <div class="space-y-3">
                        <div class="flex items-center text-gray-800 font-bold text-xs uppercase tracking-wide border-b border-gray-100 pb-2">
                            <i class="fas fa-file-invoice mr-2 text-[#ff9c00]"></i> Datos de Remisión
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Folio</label>
                                <input type="number" x-model="form.folio" :disabled="editMode"
                                       class="w-full bg-gray-50 border-gray-200 rounded-lg text-sm font-bold focus:ring-[#ff9c00] focus:border-[#ff9c00] py-2 disabled:opacity-60 disabled:cursor-not-allowed">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Entrega</label>
                                <input type="datetime-local" x-model="form.delivery_date" class="w-full bg-gray-50 border-gray-200 rounded-lg text-[11px] focus:ring-[#ff9c00] focus:border-[#ff9c00] py-2">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center text-gray-800 font-bold text-xs uppercase tracking-wide border-b border-gray-100 pb-2">
                            <i class="fas fa-user mr-2 text-blue-600"></i> Cliente
                        </div>
                        <input type="text" x-model="form.client_name" placeholder="Cliente (Señor/es)" class="form-input-sm">
                        <input type="text" x-model="form.company_name" placeholder="Empresa" class="form-input-sm">
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" x-model="form.client_phone" placeholder="Teléfono" class="form-input-sm">
                            <input type="text" x-model="form.locality" placeholder="Localidad" class="form-input-sm">
                        </div>
                        <textarea x-model="form.address" rows="2" placeholder="Dirección Completa" class="form-input-sm resize-none"></textarea>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center text-gray-800 font-bold text-xs uppercase tracking-wide border-b border-gray-100 pb-2">
                            <i class="fas fa-truck mr-2 text-gray-500"></i> Logística
                        </div>
                        <input type="text" x-model="form.surtidor_name" placeholder="Surtidor" class="form-input-sm">
                        <input type="text" x-model="form.email_recipients" placeholder="Notificar a (emails ;)" class="form-input-sm">
                        <textarea x-model="form.observations" rows="2" placeholder="Observaciones (PDF)" class="form-input-sm resize-none"></textarea>
                    </div>

                    <div x-show="globalError" x-transition class="p-3 rounded-lg bg-red-50 border border-red-100 text-red-600 text-xs text-center font-medium">
                        <span x-text="globalError"></span>
                    </div>
                </div>

                <div class="p-5 bg-gray-50 border-t border-gray-200 space-y-3">
                    <button @click="submitCheckout()"
                            :disabled="isSaving || isPrinting || localCart.size === 0 || !isFormValid"
                            class="w-full flex items-center justify-center py-3.5 px-4 rounded-xl text-white font-bold text-sm uppercase tracking-wide shadow-lg transition-all transform active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="editMode ? 'bg-orange-600 hover:bg-orange-700' : (isFormValid && localCart.size > 0 ? 'bg-[#2c3856] hover:bg-[#1e273d]' : 'bg-gray-400 cursor-not-allowed')">
                        
                        <span x-show="!isSaving" x-text="editMode ? 'Actualizar Pedido' : 'Generar Venta'"></span>
                        <span x-show="isSaving"><i class="fas fa-circle-notch fa-spin"></i></span>
                    </button>

                    <button x-show="editMode" @click="confirmCancelOrder()"
                            :disabled="isSaving"
                            class="w-full py-2.5 px-4 rounded-lg bg-red-50 text-red-600 border border-red-100 font-bold text-xs uppercase tracking-wide hover:bg-red-100 transition-colors flex justify-center items-center">
                        <i class="fas fa-trash-alt mr-2"></i> Cancelar Pedido
                    </button>

                    <button x-show="!editMode" @click="printProductList()"
                            :disabled="isSaving || isPrinting"
                            class="w-full py-2.5 px-4 rounded-lg bg-white border border-gray-300 text-gray-600 font-bold text-xs uppercase tracking-wide hover:bg-gray-100 transition-colors flex justify-center items-center">
                        <i class="fas fa-print mr-2" :class="{'fa-spin': isPrinting}"></i> Imprimir Picking
                    </button>
                </div>
            </div>
        </div>

        <div x-show="searchModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="searchModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="searchModalOpen = false" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="searchModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm w-full p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Buscar Pedido a Editar</h3>
                    <input type="number" x-model="searchFolio" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mb-4" placeholder="Ingrese el número de Folio">
                    <div class="flex justify-end space-x-2">
                        <button @click="searchModalOpen = false" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-lg text-sm">Cancelar</button>
                        <button @click="loadOrderToEdit()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-sm flex items-center" :disabled="isSearching">
                            <i x-show="isSearching" class="fas fa-spinner fa-spin mr-2"></i> Buscar
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
                filter: '',
                isSaving: false,
                isPrinting: false,
                isSearching: false,
                searchModalOpen: false,
                searchFolio: '',
                editMode: false,
                globalError: '',
                pollingInterval: null,
                
                form: {
                    folio: '',
                    client_name: '',
                    company_name: '',
                    client_phone: '',
                    address: '',
                    locality: '',
                    delivery_date: '',
                    surtidor_name: '',
                    observations: '',
                    email_recipients: ''
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
                            cart_items: [],
                            error: ''
                        };
                    });
                    
                    const savedEmails = localStorage.getItem('ff_email_recipients');
                    if (savedEmails) {
                        this.form.email_recipients = savedEmails;
                    }

                    this.pollingInterval = setInterval(() => this.pollReservations(), 10000);
                },

                get isFormValid() {
                    return this.form.folio && this.form.client_name && this.form.company_name && this.form.client_phone && this.form.address && this.form.locality && this.form.delivery_date && this.form.surtidor_name;
                },

                showFlashMessage(message, type = 'info', duration = 4000) {
                    clearTimeout(this.flashTimeout);
                    this.flashMessage = message;
                    this.flashType = type;
                    this.flashTimeout = setTimeout(() => { this.flashMessage = ''; }, duration);
                },

                get filteredProducts() {
                    if (this.filter === '') return this.products; 
                    const search = this.filter.toLowerCase();
                    return this.products.filter(p => p.sku.toLowerCase().includes(search) || p.description.toLowerCase().includes(search));
                },
                
                get totalVenta() {
                    let total = 0;
                    this.localCart.forEach((quantity, productId) => {
                        const product = this.products.find(p => p.id === productId);
                        if (product) { total += product.unit_price * quantity; }
                    });
                    return total;
                },

                formatCurrency(value) {
                    if (isNaN(value)) value = 0;
                    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(value);
                },
                
                getAvailableStock(product) {
                    return product.total_stock - product.reserved_by_others;
                },

                getProductInCart(productId) { return this.localCart.get(productId); },

                updateQuantity(product, change) {
                    const currentQty = this.getProductInCart(product.id) || 0;
                    this.onQuantityChange({ target: { value: currentQty + change } }, product);
                },

                onQuantityInput(event, product) { this.onQuantityChange(event, product); },
                
                async onQuantityChange(event, product) {
                    let newQuantity = parseInt(event.target.value);
                    const currentCartQty = this.getProductInCart(product.id) || 0;
                    const maxAvailable = this.getAvailableStock(product) + currentCartQty;

                    product.error = '';
                    if (isNaN(newQuantity) || newQuantity < 0) newQuantity = 0;
                    
                    if (newQuantity > maxAvailable) {
                        newQuantity = maxAvailable;
                        this.showFlashMessage(`Stock máximo alcanzado (${maxAvailable})`, 'danger');
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
                            product.error = data.message;
                            this.showFlashMessage(data.message, 'danger');
                            if (data.new_quantity === 0 || data.new_quantity === undefined) this.localCart.delete(product.id);
                            else this.localCart.set(product.id, data.new_quantity);
                        }
                    } catch (e) {
                        this.showFlashMessage('Error de conexión.', 'danger');
                    }
                },

                async pollReservations() {
                    try {
                        const response = await fetch("{{ route('ff.sales.reservations') }}");
                        const reservations = await response.json();
                        this.products.forEach((product, index) => {
                            const myCartQty = this.getProductInCart(product.id) || 0;
                            const newReserved = reservations[product.id] ? parseInt(reservations[product.id]) : 0;
                            this.products[index].reserved_by_others = newReserved;
                        });
                    } catch (e) { console.error(e); }
                },

                openSearchModal() {
                    this.searchFolio = '';
                    this.searchModalOpen = true;
                },

                async loadOrderToEdit() {
                    if (!this.searchFolio) return;
                    this.isSearching = true;
                    
                    try {
                        const response = await fetch("{{ route('ff.sales.searchOrder') }}?folio=" + this.searchFolio);
                        const data = await response.json();
                        
                        if (!response.ok) throw new Error(data.message);
                        
                        this.form = { ...this.form, ...data.client_data, folio: this.searchFolio };
                        this.localCart.clear();
                        if (data.cart_items && Array.isArray(data.cart_items)) {
                            data.cart_items.forEach(item => {
                                this.localCart.set(item.product_id, item.quantity);
                            });
                        }
                        this.editMode = true;
                        this.searchModalOpen = false;
                        this.showFlashMessage('Pedido cargado correctamente. Puede editar.', 'success');
                        
                    } catch (e) {
                        alert(e.message || 'Error al buscar el pedido.');
                    } finally {
                        this.isSearching = false;
                    }
                },

                cancelEditMode() {
                    if(confirm('¿Salir del modo edición? Se limpiará el formulario.')) {
                        window.location.href = "{{ route('ff.sales.index') }}"; 
                    }
                },

                async confirmCancelOrder() {
                    const reason = prompt("¿Está seguro de CANCELAR este pedido? Esta acción devolverá el stock. Ingrese motivo:");
                    if (!reason) return;

                    this.isSaving = true;
                    try {
                        const response = await fetch("{{ route('ff.sales.cancelOrder') }}", {
                            method: 'POST',
                            body: JSON.stringify({ folio: this.form.folio, reason: reason, email_recipients: this.form.email_recipients }),
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json', 'Content-Type': 'application/json' }
                        });
                        const data = await response.json();
                        if(response.ok) {
                            alert(data.message);
                            window.location.reload();
                        } else {
                            alert(data.message);
                        }
                    } catch(e) {
                        alert("Error al cancelar.");
                    } finally {
                        this.isSaving = false;
                    }
                },

                async submitCheckout() {
                    this.isSaving = true;
                    this.globalError = '';
                    
                    if (this.form.email_recipients) { localStorage.setItem('ff_email_recipients', this.form.email_recipients); }

                    try {
                        const response = await fetch("{{ route('ff.sales.checkout') }}", {
                            method: 'POST',
                            body: JSON.stringify({ ...this.form, is_edit_mode: this.editMode }),
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json', 'Content-Type': 'application/json' }
                        });

                        if (response.status === 200 && response.headers.get('Content-Type') === 'application/pdf') {
                            const blob = await response.blob();
                            const url = window.URL.createObjectURL(blob);
                            window.open(url);
                            this.showFlashMessage(`¡${this.editMode ? 'Actualización' : 'Venta'} exitosa!`, 'success', 4000);
                            setTimeout(() => window.location.href = "{{ route('ff.sales.index') }}", 2000);
                        } else {
                            const data = await response.json();
                            if (data.errors) {
                                this.globalError = Object.values(data.errors).flat().join(' ');
                                this.showFlashMessage(this.globalError, 'danger');
                            } else {
                                this.globalError = data.message || "Error al procesar.";
                                this.showFlashMessage(this.globalError, 'danger');
                            }
                        }
                    } catch (e) {
                        this.globalError = 'Error de conexión.';
                        this.showFlashMessage(this.globalError, 'danger');
                    } finally {
                        this.isSaving = false;
                    }
                },
                
                async printProductList() {
                    let sets = window.prompt("¿Número de copias?", "1");
                    if (!sets) return; 
                    this.isPrinting = true;
                    try {
                        const productsToPrint = this.filteredProducts.filter(p => p.is_active).map(p => ({ sku: p.sku, description: p.description, unit_price: p.unit_price }));
                        const response = await fetch("{{ route('ff.sales.printList') }}", {
                            method: 'POST', body: JSON.stringify({ products: productsToPrint, numSets: sets }),
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json', 'Content-Type': 'application/json' }
                        });
                        if (response.headers.get('Content-Type') === 'application/pdf') {
                            const blob = await response.blob();
                            const url = window.URL.createObjectURL(blob);
                            window.open(url);
                        }
                    } catch (e) { this.showFlashMessage('Error al imprimir.', 'danger'); } 
                    finally { this.isPrinting = false; }
                }
            }
        }
    </script>
</x-app-layout>