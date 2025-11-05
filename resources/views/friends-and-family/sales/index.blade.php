<x-app-layout>
    <div x-data="salesManager()" x-init='init(@json($products))'>

        <div class="sticky top-16 z-10 bg-white shadow-md border-b border-gray-200">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-4">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="mb-4 md:mb-0">
                        <h2 class="text-2xl font-bold text-gray-800">Total de Venta:</h2>
                        <span class="text-3xl font-extrabold text-blue-600" x-text="formatCurrency(totalVenta)"></span>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4">
                        <button @click="printProductList()"
                                :disabled="isSaving || isPrinting"
                                class="inline-flex items-center justify-center px-6 py-4 bg-blue-500 border border-transparent rounded-lg font-semibold text-lg text-white uppercase tracking-widest hover:bg-blue-600 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i x-show="isPrinting" class="fas fa-spinner fa-spin -ml-1 mr-3" style="display: none;"></i>
                            <i x-show="!isPrinting" class="fas fa-print -ml-1 mr-3"></i>
                            <span x-text="isPrinting ? 'Generando...' : 'Imprimir Lista'"></span>
                        </button>
                        
                        <button @click="submitCheckout()"
                                :disabled="isSaving || isPrinting || localCart.size === 0"
                                class="inline-flex items-center justify-center px-8 py-4 bg-green-600 border border-transparent rounded-lg font-semibold text-lg text-white uppercase tracking-widest hover:bg-green-700 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i x-show="isSaving" class="fas fa-spinner fa-spin -ml-1 mr-3" style="display: none;"></i>
                            <i x-show="!isSaving" class="fas fa-file-pdf -ml-1 mr-3"></i>
                            <span x-text="isSaving ? 'Procesando...' : 'Generar Venta (PDF)'"></span>
                        </button>
                    </div>

                </div>
                <div x-show="globalError" class="mt-3 text-center text-red-600 font-medium" x-text="globalError" x-transition style="display: none;"></div>
            </div>
        </div>
        
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-4">
                    <input type="text" x-model="filter" placeholder="Buscar por SKU o descripción..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
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
                        
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden transition-all duration-300"
                             :class="{ 'opacity-40': getAvailableStock(product) <= 0 && !getProductInCart(product.id) }">
                            
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
                                           class="w-full rounded-md border-gray-300 shadow-sm text-lg font-bold md:text-center"
                                           :disabled="getAvailableStock(product) <= 0 && !getProductInCart(product.id)"
                                           :value="getProductInCart(product.id)"
                                           @input.debounce.500ms="onQuantityChange($event, product)">
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

                init(initialProducts) {
                    const productsArray = Array.isArray(initialProducts) ? initialProducts : [];
                    
                    this.products = productsArray.map(p => {
                        const myCartItem = p.cart_items.find(item => item.user_id === {{ Auth::id() }});
                        if (myCartItem) {
                            this.localCart.set(myCartItem.ff_product_id, myCartItem.quantity);
                        }
                        
                        return {
                            ...p,
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
                    const maxAvailable = this.getAvailableStock(product) + (this.getProductInCart(product.id) || 0);

                    product.error = '';

                    if (isNaN(newQuantity) || newQuantity < 0) {
                        newQuantity = 0;
                    }
                    if (newQuantity > maxAvailable) {
                        newQuantity = maxAvailable;
                        product.error = `No puedes exceder el stock disponible (${maxAvailable}).`;
                    }
                    
                    event.target.value = newQuantity;

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
                            this.localCart.delete(product.id);
                            event.target.value = 0;
                        }
                    } catch (e) {
                        product.error = 'Error de conexión al guardar en carrito.';
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
                            if (myCartQty > 0 && myCartQty > this.getAvailableStock(product)) {
                                product.error = `¡Stock reducido! Solo quedan ${this.getAvailableStock(product)}. Ajusta tu cantidad.`;
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
                    
                    const cartArray = Array.from(this.localCart, ([id, qty]) => ({ product_id: id, quantity: qty }));

                    try {
                        const response = await fetch("{{ route('ff.sales.checkout') }}", {
                            method: 'POST',
                            body: JSON.stringify({ cart: cartArray }),
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
                            
                            location.reload(); 

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

                    const productsToPrint = this.filteredProducts.map(p => {
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