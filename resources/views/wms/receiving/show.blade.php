<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-3xl text-gray-800 leading-tight tracking-tight">Estación de Recepción Física</h2>
                <p class="text-md text-gray-500 mt-1">Orden de Compra: <span class="font-mono text-indigo-600 font-semibold">{{ $purchaseOrder->po_number }}</span></p>
            </div>
            <a href="{{ route('wms.purchase-orders.show', $purchaseOrder) }}" class="px-5 py-2 bg-green-600 text-white font-semibold rounded-lg shadow-md hover:bg-green-700"><i class="fas fa-flag-checkered mr-2"></i>Finalizar Recepción</a>
        </div>
    </x-slot>

    <div class="py-12" x-data="receivingApp()">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white p-8 rounded-2xl shadow-xl border border-gray-200">

                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 ml-2">Progreso General</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <template x-for="item in summary" :key="item.product_id">
                            <div class="p-4 rounded-xl border flex flex-col h-full" :class="{'bg-green-50 border-green-200': item.balance <= 0, 'bg-yellow-50 border-yellow-200': item.balance > 0}">
                                <div class="flex-grow">
                                    <p class="font-bold text-gray-800" x-text="item.name"></p>
                                    <p class="text-xs text-gray-500 font-mono" x-text="item.sku"></p>
                                </div>
                                <div class="mt-3">
                                    <div class="flex justify-between items-baseline"><span class="font-bold text-3xl text-gray-900" x-text="item.received"></span><span class="text-gray-500 font-medium">/ <span x-text="item.ordered"></span></span></div>
                                    <div class="w-full bg-gray-200 rounded-full h-2 mt-1"><div class="h-2 rounded-full transition-all duration-500" :class="{'bg-green-500': item.balance <= 0, 'bg-yellow-500': item.balance > 0}" :style="`width: ${ (item.received / item.ordered) * 100 }%`"></div></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                    
                    <div class="lg:col-span-2 space-y-6">
                        <div class="text-center p-4 rounded-lg" :class="step === 'start' ? 'bg-gray-100' : 'bg-gradient-to-r from-indigo-500 to-blue-500 text-white shadow-lg'">
                            <p class="text-sm font-semibold" x-text="step === 'start' ? 'Esperando nueva tarima...' : 'Registrando productos en:'"></p>
                            <p class="text-4xl font-mono font-bold" x-text="step === 'start' ? 'STANDBY' : currentPallet.lpn"></p>
                        </div>

                        <div x-show="step === 'start'" x-transition:enter.duration.300ms><form @submit.prevent="startNewPallet()" class="text-center"><div class="max-w-md mx-auto relative"><i class="fas fa-barcode absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 text-2xl"></i><input type="text" id="lpn_input" x-model="lpnInput" class="pl-12 w-full text-center text-2xl font-mono rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-3" placeholder="ESCANEAR LPN" required autofocus></div><button type="submit" :disabled="loading || !lpnInput" class="mt-4 px-8 py-3 bg-indigo-600 text-white font-semibold rounded-lg shadow-lg hover:bg-indigo-700 text-lg transition disabled:opacity-50"><span x-show="!loading"><i class="fas fa-play-circle mr-2"></i> Iniciar Tarima</span><span x-show="loading">Verificando...</span></button></form></div>

                        <div x-show="step === 'receiving'" x-transition:enter.duration.300ms class="space-y-6">
                            <div class="border rounded-lg p-4 bg-gray-50">
                                <div class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
                                    <div class="md:col-span-3 relative">
                                        <label class="block text-sm font-medium">Escanear SKU, UPC o buscar Producto</label>
                                        <input type="text" 
                                            x-model="productSearchInput" 
                                            @input.debounce.300ms="findProduct()" 
                                            @keydown.enter.prevent="selectFirstProduct()"
                                            @keydown.escape.prevent="clearSearch()"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"
                                            placeholder="Escanear o escribir...">

                                        <template x-if="selectedProduct">
                                            <div class="mt-2 p-2 bg-indigo-50 border border-indigo-200 rounded-md text-sm">
                                                <span class="font-bold text-indigo-700" x-text="selectedProduct.name"></span>
                                                <span class="text-gray-500 font-mono" x-text="`(${selectedProduct.sku})`"></span>
                                                <button type_button @click="clearSearch()" class="ml-2 text-red-500 text-xs font-bold">[X]</button>
                                            </div>
                                        </template>

                                        <template x-if="productSearchResults.length > 0">
                                            <ul class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto mt-1">
                                                <template x-for="product in productSearchResults" :key="product.id">
                                                    <li @click="selectProduct(product)" 
                                                        class="p-3 cursor-pointer hover:bg-gray-100 text-sm">
                                                        <strong x-text="product.name"></strong>
                                                        <span class="block text-xs text-gray-500 font-mono" x-text="`SKU: ${product.sku} | UPC: ${product.upc || 'N/A'}`"></span>
                                                    </li>
                                                </template>
                                            </ul>
                                        </template>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium">Calidad</label>
                                        <select x-model.number="newItem.quality_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        <option value="">Seleccione...</option>
                                        @foreach ($qualities as $quality)
                                            <option value="{{ $quality->id }}">{{ $quality->name }}</option>
                                        @endforeach</select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium">Cantidad</label>
                                        <input type="number" x-model.number="newItem.quantity" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </div>
                                </div>
                                <button @click="addItemToPallet" :disabled="loading || !newItem.product_id || !newItem.quantity || !newItem.quality_id" class="w-full mt-4 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition disabled:opacity-50 text-base">
                                    <i class="fas fa-plus mr-2"></i>
                                    Añadir Producto a Tarima
                                </button>
                            </div>
                            <div class="border-t pt-4">
                                <h4 class="font-bold text-gray-700 mb-2">Contenido de la Tarima Actual:</h4>
                                <div class="space-y-2">
                                    <template x-if="!currentPallet || currentPallet.items.length === 0">
                                        <p class="text-sm text-center text-gray-500 py-4">Aún no hay productos en esta tarima.</p>
                                    </template>

                                    <template x-for="item in currentPallet?.items" :key="item.id">
                                        <div class="border rounded-lg transition-all" :class="editingItemId === item.id ? 'bg-indigo-50' : 'bg-white'">
                                            <div x-show="editingItemId !== item.id" class="flex justify-between items-center p-2 text-sm">
                                                <div>
                                                    <p><strong class="text-indigo-700" x-text="`[${item.quality.name}] `"></strong><span class="font-medium" x-text="item.product.name"></span></p>
                                                </div>
                                                <div class="flex items-center gap-4">
                                                    <p class="font-bold text-lg" x-text="`x ${item.quantity}`"></p>
                                                    <button @click="startEditing(item)" class="text-gray-400 hover:text-blue-600"><i class="fas fa-pencil-alt"></i></button>
                                                    <button @click="deleteItem(item.id)" class="text-gray-400 hover:text-red-600"><i class="fas fa-trash"></i></button>
                                                </div>
                                            </div>
                                            <div x-show="editingItemId === item.id" class="p-3 bg-white rounded-lg border-2 border-indigo-500">
                                                <div class="grid grid-cols-6 gap-2">
                                                    <select x-model.number="editForm.product_id" class="col-span-3 rounded-md text-sm"><option value="">Producto...</option>@foreach ($products as $product)<option value="{{ $product->id }}">{{ $product->name }}</option>@endforeach</select>
                                                    <select x-model.number="editForm.quality_id" class="col-span-2 rounded-md text-sm"><option value="">Calidad...</option>@foreach ($qualities as $quality)<option value="{{ $quality->id }}">{{ $quality->name }}</option>@endforeach</select>
                                                    <input type="number" x-model.number="editForm.quantity" min="1" class="rounded-md text-sm">
                                                </div>
                                                <div class="flex justify-end gap-2 mt-2">
                                                    <button @click="cancelEditing()" class="text-sm text-gray-600 px-3 py-1 rounded-md hover:bg-gray-200">Cancelar</button>
                                                    <button @click="saveEdit(item.id)" class="text-sm text-white bg-green-600 px-3 py-1 rounded-md hover:bg-green-700">Guardar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="text-center border-t pt-6"><button @click="finishPallet()" class="w-full px-4 py-2 bg-gray-800 text-white font-semibold rounded-lg hover:bg-gray-900"><i class="fas fa-flag-checkered mr-2"></i>Finalizar Tarima y Empezar Siguiente</button></div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200">
                        <h3 class="font-bold text-lg text-gray-800 mb-4 flex items-center"><i class="fas fa-history text-gray-400 mr-3"></i>Historial de Tarimas Recibidas</h3>
                        <div class="space-y-3 max-h-[60vh] overflow-y-auto pr-2">
                            <template x-if="finishedPallets.length === 0"><p class="text-center text-gray-500 text-sm pt-8">Aún no se han finalizado tarimas.</p></template>
                            <template x-for="pallet in finishedPallets.slice().reverse()" :key="pallet.lpn"><div class="bg-white p-3 rounded-lg border border-gray-200 animate-pulse-once" style="--animate-duration: 0.5s;"><div class="flex justify-between items-center"><p class="font-mono font-bold text-indigo-800" x-text="pallet.lpn"></p><div class="text-xs font-semibold text-gray-500 text-right"><p x-text="`Recibido por: ${pallet.user ? pallet.user.name : 'N/A'}`"></p><p x-text="new Date(pallet.finished_at).toLocaleTimeString()"></p></div></div><ul class="text-xs mt-2 space-y-1 border-t pt-2"><template x-for="item in pallet.items" :key="item.id"><li class="flex justify-between"><span><strong class="text-indigo-700" x-text="`[${item.quality.name}] `"></strong> <span x-text="`${item.product.name}`"></span></span><span class="font-semibold" x-text="`x ${item.quantity}`"></span></li></template></ul></div></template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    function receivingApp() {
        return {
            summary: @json($receivingSummary),
            purchaseOrderId: {{ $purchaseOrder->id }},
            allProducts: @json($products),
            
            step: 'start', loading: false, currentPallet: null,
            newItem: { product_id: '', quantity: 1, quality_id: '' },
            lpnInput: '', finishedPallets: @json($finishedPallets),

            productSearchInput: '',
            productSearchResults: [],
            selectedProduct: null,            

            editingItemId: null,
            editForm: { product_id: '', quality_id: '', quantity: 0 },
            // finishedPallets: @json($finishedPallets),

            async startNewPallet() {
                    if (!this.lpnInput.trim()) {
                        alert('Por favor, escanea o ingresa un LPN.');
                        return;
                    }

                    this.loading = true;
                    try {
                        const response = await fetch('/wms/receiving/start-pallet', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                lpn: this.lpnInput,
                                purchase_order_id: this.purchaseOrderId
                            })
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.error || 'Error al verificar el LPN.');
                        }

                        this.currentPallet = data;
                        this.step = 'receiving';
                        this.lpnInput = '';

                    } catch (error) {
                        console.error('Error en startNewPallet:', error);
                        alert(`Error: ${error.message}`);
                        this.lpnInput = '';
                    } finally {
                        this.loading = false;
                    }
                },

            async addItemToPallet() {
                console.log('Iniciando addItemToPallet...');

                if (!this.newItem.product_id || !this.newItem.quantity || !this.newItem.quality_id) {
                    console.error('Validación fallida: Faltan datos en el formulario.');
                    alert('Por favor, seleccione un producto, calidad y cantidad.');
                    return;
                }

                try {
                    const summaryItem = this.summary.find(s => s.product_id == this.newItem.product_id);
                    
                    if (!summaryItem) {
                        const product = this.allProducts.find(p => p.id == this.newItem.product_id);
                        const productName = product ? product.name : `ID ${this.newItem.product_id}`;
                        const productSku = product ? product.sku : 'SKU Desconocido';

                        const message = `¡PRODUCTO NO ESPERADO!\n\n` +
                                        `El producto "${productName}" (SKU: ${productSku}) no está en esta Orden de Compra.\n\n` +
                                        `¿Deseas recibirlo de todos modos?`;
                        
                        if (!confirm(message)) {
                            console.log('El usuario canceló la recepción de un producto no esperado.');
                            this.clearSearch();
                            return;
                        }

                    } else {
                        console.log('Producto encontrado en el resumen:', summaryItem);
                        const totalAfterAdd = Number(summaryItem.received) + Number(this.newItem.quantity);
                        
                        if (totalAfterAdd > summaryItem.ordered) {
                            const message = `¡SOBRE-RECEPCIÓN!\n\n` +
                                            `Estás a punto de recibir ${this.newItem.quantity} unidades.\n` +
                                            `El total recibido (${totalAfterAdd}) superaría las ${summaryItem.ordered} unidades ordenadas.\n\n` +
                                            `¿Deseas continuar?`;
                            if (!confirm(message)) {
                                console.log('El usuario canceló la sobre-recepción.');
                                return;
                            }
                        }
                    }
                } catch (e) {
                    console.error('Error durante la validación de recepción:', e);
                    alert('Ocurrió un error al validar las cantidades.');
                    return;
                }

                this.loading = true;
                console.log('Enviando datos al servidor:', this.newItem);
                try {
                    const response = await fetch(`/wms/receiving/pallets/${this.currentPallet.id}/add-item`, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                        body: JSON.stringify(this.newItem)
                    });
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.error || 'Error al añadir producto.');
                    
                    console.log('Respuesta recibida del servidor:', data);
                    this.currentPallet = data; 
                    this.newItem = { product_id: '', quantity: 1, quality_id: '' };
                    this.clearSearch();

                } catch (error) {
                    console.error('Error en la llamada fetch:', error);
                    alert(`Error: ${error.message}`);
                } finally {
                    this.loading = false;
                }
            },

            async finishPallet() {
                if (!this.currentPallet || this.currentPallet.items.length === 0) {
                    alert('La tarima está vacía. Se cancelará sin guardar.');
                    this.step = 'start';
                    return;
                }

                this.loading = true;
                try {
                    const response = await fetch(`/wms/receiving/pallets/${this.currentPallet.id}/finish`, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                    });
                    
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.error || 'Error del servidor al finalizar.');
                    }

                    this.currentPallet.items.forEach(palletItem => {
                        const summaryItem = this.summary.find(s => s.product_id == palletItem.product_id);
                        if (summaryItem) {
                            summaryItem.received = Number(summaryItem.received) + Number(palletItem.quantity);
                            summaryItem.balance = summaryItem.ordered - summaryItem.received;
                        }
                    });

                    this.currentPallet.finished_at = new Date().toISOString();
                    this.finishedPallets.push(JSON.parse(JSON.stringify(this.currentPallet)));
                    
                    this.currentPallet = null; 
                    this.step = 'start';
                    this.$nextTick(() => { document.getElementById('lpn_input').focus(); });

                } catch (error) {
                    alert(`Error: ${error.message}`);
                } finally {
                    this.loading = false;
                }
            },
            startEditing(item) {
                this.editingItemId = item.id;
                this.editForm = JSON.parse(JSON.stringify({
                    product_id: item.product_id,
                    quality_id: item.quality_id,
                    quantity: item.quantity
                }));
            },

            cancelEditing() {
                this.editingItemId = null;
                this.editForm = { product_id: '', quality_id: '', quantity: 0 };
            },

            async saveEdit(itemId) {
                if (!this.editForm.product_id || !this.editForm.quantity || !this.editForm.quality_id) {
                    alert('Todos los campos son requeridos.');
                    return;
                }
                this.loading = true;
                try {
                    const response = await fetch(`/wms/receiving/pallet-items/${itemId}`, {
                        method: 'PUT',
                        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                        body: JSON.stringify(this.editForm)
                    });
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.error);

                    this.currentPallet = data;
                    this.cancelEditing();
                } catch (error) {
                    alert(`Error al guardar: ${error.message}`);
                } finally {
                    this.loading = false;
                }
            },

            async deleteItem(itemId) {
                if (!confirm('¿Estás seguro de que deseas eliminar este producto de la tarima?')) return;
                
                this.loading = true;
                try {
                    const response = await fetch(`/wms/receiving/pallet-items/${itemId}`, {
                        method: 'DELETE',
                        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'}
                    });
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.error);

                    this.currentPallet = data;
                } catch (error) {
                    alert(`Error al eliminar: ${error.message}`);
                } finally {
                    this.loading = false;
                }
            },

            findProduct() {
                if (this.productSearchInput.trim() === '') {
                    this.productSearchResults = [];
                    return;
                }

                const search = this.productSearchInput.toLowerCase();

                const exactMatch = this.allProducts.find(p => 
                    (p.sku && p.sku.toLowerCase() === search) || 
                    (p.upc && p.upc.toLowerCase() === search)
                );

                if (exactMatch) {
                    this.selectProduct(exactMatch);
                    return;
                }

                this.productSearchResults = this.allProducts.filter(p => 
                    (p.name && p.name.toLowerCase().includes(search)) ||
                    (p.sku && p.sku.toLowerCase().includes(search))
                ).slice(0, 10);
            },

            selectProduct(product) {
                this.selectedProduct = product;
                this.newItem.product_id = product.id;
                this.productSearchInput = '';
                this.productSearchResults = [];
            },

            selectFirstProduct() {
                if (this.productSearchResults.length > 0) {
                    this.selectProduct(this.productSearchResults[0]);
                }
            },

            clearSearch() {
                this.selectedProduct = null;
                this.newItem.product_id = '';
                this.productSearchInput = '';
                this.productSearchResults = [];
            }

        }
    }
    document.addEventListener('alpine:init', () => {
        Alpine.data('receivingApp', receivingApp);
    });
</script>
</x-app-layout>