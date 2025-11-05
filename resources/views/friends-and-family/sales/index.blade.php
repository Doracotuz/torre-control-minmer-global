<x-app-layout>
    <div x-data="salesManager()" x-init='init(@json($products), {{ $nextFolio }})'>

        <div class="sticky top-16 z-10 bg-white shadow-md border-b border-gray-200">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-4">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    
                    <div class="mb-4 md:mb-0">
                        <h2 class="text-2xl font-bold text-gray-800">Total de Venta:</h2>
                        <span class="text-3xl font-extrabold text-gray-900" x-text="formatCurrency(totalVenta)"></span>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4">
                        
                        <button @click="printProductList()"
                                :disabled="isSaving || isPrinting"
                                class="inline-flex items-center justify-center px-6 py-3 bg-white border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i x-show="isPrinting" class="fas fa-spinner fa-spin -ml-1 mr-3" style="display: none;"></i>
                            <i x-show="!isPrinting" class="fas fa-print -ml-1 mr-3"></i>
                            <span x-text="isPrinting ? 'Generando...' : 'Imprimir Lista'"></span>
                        </button>
                        
                        <button @click="submitCheckout()"
                                :disabled="isSaving || isPrinting || localCart.size === 0 || !clientName || !surtidorName"
                                class="inline-flex items-center justify-center px-8 py-4 bg-gray-800 border border-transparent rounded-lg font-semibold text-lg text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i x-show="isSaving" class="fas fa-spinner fa-spin -ml-1 mr-3" style="display: none;"></i>
                            <i x-show="!isSaving" class="fas fa-file-pdf -ml-1 mr-3"></i>
                            <span x-text="isSaving ? 'Procesando...' : 'Generar Venta (PDF)'"></span>
                        </button>
                    </div>

                </div>
                
                <div x-show="globalError" class="mt-3 text-center text-red-600 font-medium" x-text="globalError" x-transition style="display: none;"></div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    
                    <div>
                        <label for="client_name" class="block text-xs font-medium text-gray-500">Nombre del Cliente (*)</label>
                        <input type="text" id="client_name" x-model="clientName" placeholder="Nombre completo del cliente" class="w-full rounded-md border-gray-300 shadow-sm focus:border-gray-800 focus:ring-gray-800 text-sm">
                    </div>
                    
                    <div>
                        <label for="surtidor_name" class="block text-xs font-medium text-gray-500">Nombre del Surtidor (*)</label>
                        <input type="text" id="surtidor_name" x-model="surtidorName" placeholder="Quién preparó el pedido" class="w-full rounded-md border-gray-300 shadow-sm focus:border-gray-800 focus:ring-gray-800 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500">Folio de Venta (Consecutivo)</label>
                        <div class="mt-1 block w-full rounded-md border border-gray-300 bg-gray-50 text-gray-800 shadow-sm px-3 py-1.5 text-sm h-[38px] flex items-center">
                            <span x-text="folioVenta"></span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-4">
                    <input type="text" x-model="filter" placeholder="Buscar por SKU o descripción..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-gray-800 focus:ring-gray-800">
                </div>

                <div class="hidden md:flex bg-gray-50 rounded-lg px-6 py-3 text-xs font-semibold uppercase text-gray-500 tracking-wider mb-3">
                    <div class="w-1/12 lg:w-[5%]">#</div>
                    <div class="w-5/12 lg:w-[35%]">Producto</div>
                    <div class="w-1/4 lg:w-[20%] text-right">Precio</div>
                    <div class="w-1/4 lg:w-[20%] text-center">Disponible</div>
                    <div class="w-1/4 lg:w-[20%] text-center">Cantidad</div>
                </div>

                <div class="flex flex-col gap-4">
                    <template x-for="(product, index) in filteredProducts" :key="product.id">
                        
                        <div x-data="{ isFocused: false }"
                             class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300"
                             :class="{ 
                                 'opacity-40': getAvailableStock(product) <= 0 && !getProductInCart(product.id),
                                 'ring-2 ring-gray-800': isFocused 
                             }">
                            
                            <div class="p-4 md:p-5 flex flex-col md:flex-row md:items-center md:gap-4">

                                <div class="w-full md:w-1/12 lg:w-[5%] mb-2 md:mb-0 md:text-left md:font-bold">
                                    <label class="md:hidden text-xs font-medium text-gray-500">#:</label>
                                    <span class="text-lg text-gray-900" x-text="index + 1"></span>
                                </div>

                                <div class="w-full md:w-5/12 lg:w-[35%] mb-4 md:mb-0">
                                    <div class="text-xs uppercase text-gray-400" x-text="product.sku"></div>
                                    <p class="text-lg font-bold text-gray-900" x-text="product.description"></p>
                                    <div class="text-sm text-gray-500" x-text="`${product.brand || ''} / ${product.type || ''}`"></div>
                                </div>

                                <div class="w-full md:w-1/4 lg:w-[20%] mb-4 md:mb-0 md:text-right">
                                    <label class="md:hidden text-xs font-medium text-gray-500">Precio:</label>
                                    <span class="text-2xl font-extrabold text-gray-800" x-text="formatCurrency(product.price)"></span>
                                </div>

                                <div class="w-full md:w-1/4 lg:w-[20%] mb-4 md:mb-0 md:text-center">
                                    <span class="text-sm font-semibold"
                                          :class="getAvailableStock(product) > 0 ? 'text-green-600' : 'text-red-600'"
                                          x-text="`Disponible: ${getAvailableStock(product)}`">
                                    </span>
                                </div>
                                
                                <div class="w-full md:w-1/4 lg:w-[20%]">
                                    <label :for="`qty-${product.id}`" class="md:hidden text-sm font-medium text-gray-700 mb-1">Cantidad:</label>
                                    <input :id="`qty-${product.id}`" type="number" min="0" :max="getAvailableStock(product) + (getProductInCart(product.id) || 0)"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-lg font-bold text-center focus:ring-gray-800 focus:border-gray-800"
                                           :disabled="getAvailableStock(product) <= 0 && !getProductInCart(product.id)"
                                           :value="getProductInCart(product.id)"
                                           @input.debounce.500ms="onQuantityChange($event, product)"
                                           @focus="isFocused = true"
                                           @blur="isFocused = false">
                                    <div x-show="product.error" class="text-xs text-red-500 mt-1" x-text="product.error" x-transition style="display: none;"></div>
                                </div>

                            </div>
                        </div>
                    </template>
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
                globalError: '',
                pollingInterval: null,
                clientName: '',
                surtidorName: '',
                folioVenta: null, 

                init(initialProducts, initialFolio) {
                    const productsArray = Array.isArray(initialProducts) ? initialProducts : [];
                    
                    this.folioVenta = initialFolio;

                    this.products = productsArray.map(p => {
                        const myCartItem = p.cart_items.find(item => item.user_id === {{ Auth::id() }});
                        if (myCartItem) {
                            this.localCart.set(myCartItem.ff_product_id, myCartItem.quantity);
                        }
                        
                        return {
                            ...p,
                            photo_url: p.photo_path ? `/storage/${p.photo_path}` : 'https://via.placeholder.com/150',
                            total_stock: p.movements_sum_quantity ? parseInt(p.movements_sum_quantity) : 0,
                            reserved_by_others: p.reserved_by_others ? parseInt(p.reserved_by_others) : 0,
                            cart_items: [],
                            error: ''
                        };
                    });
                    
                    this.pollingInterval = setInterval(() => this.pollReservations(), 10000);
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
                        if (product) {
                            total += product.price * quantity;
                        }
                    });
                    return total;
                },

                formatCurrency(value) {
                    if (isNaN(value)) {
                        value = 0;
                    }
                    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(value);
                },
                
                getAvailableStock(product) {
                    return product.total_stock - product.reserved_by_others;
                },

                getProductInCart(productId) {
                    return this.localCart.get(productId);
                },
                
                async onQuantityChange(event, product) {
                    let newQuantity = parseInt(event.target.value);
                    const currentCartQty = this.getProductInCart(product.id) || 0;
                    const maxAvailable = this.getAvailableStock(product) + currentCartQty;

                    product.error = '';

                    if (isNaN(newQuantity) || newQuantity < 0) {
                        newQuantity = 0;
                    }
                    if (newQuantity > maxAvailable) {
                        newQuantity = maxAvailable;
                        product.error = `No puedes exceder el stock disponible (${maxAvailable}).`;
                    }
                    
                    event.target.value = newQuantity;

                    if (newQuantity === currentCartQty) return;

                    if (newQuantity === 0) {
                        this.localCart.delete(product.id);
                    } else {
                        this.localCart.set(product.id, newQuantity);
                    }

                    try {
                        const response = await fetch("{{ route('ff.sales.cart.update') }}", {
                            method: 'POST',
                            body: JSON.stringify({ product_id: product.id, quantity: newQuantity }),
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        });
                        
                        const data = await response.json();
                        if (!response.ok) {
                            product.error = data.message;
                            
                            const finalQty = data.new_quantity !== undefined ? data.new_quantity : currentCartQty;

                            if (finalQty === 0) {
                                this.localCart.delete(product.id);
                            } else {
                                this.localCart.set(product.id, finalQty);
                            }
                            
                            event.target.value = finalQty;
                        }
                    } catch (e) {
                        product.error = 'Error de conexión al guardar en carrito.';
                        this.localCart.set(product.id, currentCartQty);
                        event.target.value = currentCartQty;
                    }
                },

                async pollReservations() {
                    try {
                        const response = await fetch("{{ route('ff.sales.reservations') }}");
                        const reservations = await response.json();
                        
                        this.products.forEach((product, index) => {
                            const newReserved = reservations[product.id] ? parseInt(reservations[product.id]) : 0;
                            this.products[index].reserved_by_others = newReserved;
                            
                            const myCartQty = this.getProductInCart(product.id);
                            const maxAvailable = this.getAvailableStock(product) + myCartQty;

                            if (myCartQty > 0 && myCartQty > maxAvailable) {
                                product.error = `¡Stock reducido! Tu reserva (${myCartQty}) excede el nuevo máximo disponible (${maxAvailable}). Ajusta tu cantidad.`;
                            }
                        });
                    } catch (e) {
                        console.error("Error al actualizar reservaciones", e);
                    }
                },

                async submitCheckout() {
                    this.isSaving = true;
                    this.globalError = '';
                    
                    if (this.localCart.size === 0) {
                        this.globalError = "Tu carrito está vacío.";
                        this.isSaving = false;
                        return;
                    }

                    if (!this.clientName || !this.surtidorName) {
                        this.globalError = "Debes ingresar el Nombre del Cliente y del Surtidor.";
                        this.isSaving = false;
                        return;
                    }
                    
                    const postData = {
                        client_name: this.clientName,
                        surtidor_name: this.surtidorName
                    };

                    try {
                        const response = await fetch("{{ route('ff.sales.checkout') }}", {
                            method: 'POST',
                            body: JSON.stringify(postData),
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        });

                        if (response.status === 200 && response.headers.get('Content-Type') === 'application/pdf') {
                            
                            const folio = response.headers.get('X-Venta-Folio');
                            
                            const blob = await response.blob();
                            const url = window.URL.createObjectURL(blob);
                            window.open(url);

                            alert(`¡Venta completada con éxito!\nFolio: ${folio}`);
                            
                            setTimeout(() => {
                                location.reload(); 
                            }, 1000); 

                        } else {
                            const data = await response.json();
                            this.globalError = data.message || "Error al procesar la venta.";
                        }

                    } catch (e) {
                        this.globalError = 'Error de conexión. Intenta de nuevo.';
                    } finally {
                        this.isSaving = false;
                    }
                },
                
                async printProductList() {
                    let sets = window.prompt("¿Cuántos juegos (copias) deseas imprimir?", "1");
                    
                    if (sets === null) return; 
                    const numSets = parseInt(sets);
                    if (isNaN(numSets) || numSets <= 0) {
                        alert("Por favor, introduce un número válido.");
                        return;
                    }

                    const productsToPrint = this.filteredProducts.filter(p => p.is_active).map(p => {
                        return {
                            sku: p.sku,
                            description: p.description,
                            price: p.price,
                            available_stock: this.getAvailableStock(p) 
                        };
                    });

                    this.isPrinting = true;
                    this.globalError = '';

                    try {
                        const response = await fetch("{{ route('ff.sales.printList') }}", {
                            method: 'POST',
                            body: JSON.stringify({ 
                                products: productsToPrint,
                                numSets: numSets 
                            }),
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        });

                        if (response.headers.get('Content-Type') === 'application/pdf') {
                            const blob = await response.blob();
                            const url = window.URL.createObjectURL(blob);
                            window.open(url);
                        } else {
                            const data = await response.json();
                            this.globalError = data.message || "Error al generar el PDF.";
                        }

                    } catch (e) {
                        this.globalError = 'Error de conexión. Intenta de nuevo.';
                    } finally {
                        this.isPrinting = false;
                    }
                }
            }
        }
    </script>
</x-app-layout>