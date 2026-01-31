<x-app-layout>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Raleway:wght@800;900&display=swap');
        
        .font-raleway { font-family: 'Raleway', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        
        .stagger-enter { opacity: 0; transform: translateY(20px); animation: enterUp 0.5s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; }
        @keyframes enterUp { to { opacity: 1; transform: translateY(0); } }
        
        .input-arch {
            background: transparent; border: none; border-bottom: 2px solid #e5e7eb; border-radius: 0;
            padding: 0.8rem 0; font-family: 'Montserrat', sans-serif; font-weight: 600; color: #2c3856;
            transition: all 0.3s ease; width: 100%; font-size: 1.1rem;
        }
        .input-arch:focus { border-bottom-color: #ff9c00; box-shadow: none; outline: none; }
        .input-arch-select { background-image: none; cursor: pointer; padding-right: 1.5rem; }

        .btn-nexus { 
            background: #2c3856; color: white; border-radius: 1rem; font-weight: 700;
            transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center;
        }
        .btn-nexus:hover { background: #1a253a; transform: translateY(-2px); box-shadow: 0 10px 20px -5px rgba(44, 56, 86, 0.2); }
        
        .btn-ghost {
            background: transparent; color: #2c3856; border: 2px solid #e5e7eb; border-radius: 1rem; font-weight: 700;
        }
        .btn-ghost:hover { border-color: #2c3856; background: #2c3856; color: white; }

        .progress-card {
            background: white; border-radius: 1.5rem; padding: 1.5rem; border: 1px solid #f3f4f6;
            transition: all 0.3s; position: relative; overflow: hidden;
        }
        .progress-card-complete { border-color: #10b981; background: #ecfdf5; }
        .progress-card-pending { border-color: #fbbf24; background: #fffbeb; }

        [x-cloak] { display: none !important; }
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20 relative overflow-hidden" x-data="receivingApp()">
        
        <div class="fixed inset-0 -z-10 pointer-events-none">
            <div class="absolute top-0 right-0 w-[50vw] h-full bg-gradient-to-l from-[#f8fafc] to-transparent"></div>
            <div class="absolute bottom-0 left-0 w-[40rem] h-[40rem] bg-[#ff9c00]/5 rounded-full blur-[120px]"></div>
        </div>

        <div class="max-w-[1920px] mx-auto px-6 pt-10 relative z-10">
            
            <div class="flex flex-col xl:flex-row justify-between items-end mb-10 stagger-enter" style="animation-delay: 0.1s;">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-12 h-1 bg-[#ff9c00]"></span>
                        <span class="text-sm font-bold text-[#2c3856] tracking-[0.3em] uppercase">Estación de Trabajo</span>
                    </div>
                    <h1 class="text-5xl font-raleway font-black text-[#2c3856] leading-none">
                        RECEPCIÓN <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-600">FÍSICA</span>
                    </h1>
                    <p class="text-gray-500 font-bold mt-2 text-lg">PO: <span class="font-mono text-[#2c3856]">{{ $purchaseOrder->po_number }}</span></p>
                </div>

                <div class="flex flex-wrap gap-3 mt-6 xl:mt-0">
                    <a href="{{ route('wms.purchase-orders.index') }}" class="btn-ghost px-6 py-3 h-12 flex items-center gap-2 text-sm uppercase tracking-wider">
                        <i class="fas fa-arrow-left"></i> Volver a Lista
                    </a>
                    <a href="{{ route('wms.purchase-orders.show', $purchaseOrder) }}" class="btn-nexus px-6 py-3 h-12 flex items-center gap-2 text-sm uppercase tracking-wider shadow-lg shadow-[#2c3856]/20">
                        <i class="fas fa-check-circle"></i> Finalizar Recepción
                    </a>
                </div>
            </div>

            <div class="mb-12 stagger-enter" style="animation-delay: 0.2s;">
                <h3 class="text-lg font-raleway font-black text-[#2c3856] mb-4">Progreso de la Orden</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <template x-for="item in summary" :key="item.product_id">
                        <div class="progress-card shadow-sm group hover:-translate-y-1 hover:shadow-md" :class="item.balance <= 0 ? 'progress-card-complete' : 'progress-card-pending'">
                            <div class="flex justify-between items-start mb-2">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg" :class="item.balance <= 0 ? 'bg-green-200 text-green-700' : 'bg-yellow-200 text-yellow-700'">
                                    <i class="fas" :class="item.balance <= 0 ? 'fa-check' : 'fa-box-open'"></i>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-black text-[#2c3856]" x-text="item.received"></p>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase">de <span x-text="item.ordered"></span></p>
                                </div>
                            </div>
                            <p class="font-bold text-[#2c3856] text-sm leading-tight" x-text="item.name"></p>
                            <p class="text-xs text-gray-500 font-mono mt-1" x-text="item.sku"></p>
                            
                            <div class="w-full bg-white/50 rounded-full h-1.5 mt-3 overflow-hidden">
                                <div class="h-full transition-all duration-1000 ease-out" 
                                     :class="item.balance <= 0 ? 'bg-green-500' : 'bg-yellow-500'" 
                                     :style="`width: ${Math.min((item.received / item.ordered) * 100, 100)}%`"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 stagger-enter" style="animation-delay: 0.3s;">
                
                <div class="xl:col-span-2 space-y-8">
                    
                    <div class="rounded-[2.5rem] p-8 text-center border transition-all duration-500 relative overflow-hidden shadow-xl"
                         :class="step === 'start' ? 'bg-white border-gray-200' : 'bg-[#2c3856] border-[#2c3856]'">
                        
                        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-[#ff9c00]/20 to-transparent rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>
                        
                        <p class="text-sm font-bold uppercase tracking-[0.2em] mb-2 relative z-10" 
                           :class="step === 'start' ? 'text-gray-400' : 'text-[#ff9c00]'">
                           <span x-text="step === 'start' ? 'Esperando Tarima' : 'Tarima en Proceso'"></span>
                        </p>
                        
                        <h2 class="text-5xl font-mono font-black tracking-tight relative z-10"
                            :class="step === 'start' ? 'text-gray-300' : 'text-white'"
                            x-text="step === 'start' ? 'STANDBY' : currentPallet.lpn">
                        </h2>
                    </div>

                    <div x-show="step === 'start'" x-transition:enter.duration.500ms class="bg-white rounded-[2.5rem] p-10 border border-gray-100 shadow-xl shadow-[#2c3856]/5 text-center">
                        <form @submit.prevent="startNewPallet()" class="max-w-lg mx-auto">
                            <div class="mb-8">
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Escanear Nuevo LPN</label>
                                <div class="relative">
                                    <input type="text" id="lpn_input" x-model="lpnInput" class="input-arch text-center text-3xl font-mono uppercase tracking-widest" placeholder="LPN-..." required autofocus>
                                    <i class="fas fa-barcode absolute left-0 top-1/2 -translate-y-1/2 text-gray-300 text-2xl"></i>
                                </div>
                            </div>
                            <button type="submit" :disabled="loading || !lpnInput" class="btn-nexus w-full py-4 text-sm uppercase tracking-widest shadow-lg shadow-[#2c3856]/20 disabled:opacity-50">
                                <span x-show="!loading"><i class="fas fa-play mr-2"></i> Iniciar Tarima</span>
                                <span x-show="loading"><i class="fas fa-circle-notch fa-spin mr-2"></i> Verificando...</span>
                            </button>
                        </form>
                    </div>

                    <div x-show="step === 'receiving'" x-transition:enter.duration.500ms class="space-y-8">
                        
                        <div class="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-xl shadow-[#2c3856]/5">
                            <div class="grid grid-cols-1 md:grid-cols-6 gap-8 mb-8">
                                <div class="md:col-span-3 relative">
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Producto / SKU / UPC</label>
                                    <input type="text" 
                                        x-model="productSearchInput" 
                                        @input.debounce.300ms="findProduct()" 
                                        @keydown.enter.prevent="selectFirstProduct()"
                                        @keydown.escape.prevent="clearSearch()"
                                        class="input-arch"
                                        placeholder="Escanear...">
                                    
                                    <template x-if="selectedProduct">
                                        <div class="absolute top-full left-0 w-full mt-2 p-3 bg-blue-50 border border-blue-100 rounded-xl flex justify-between items-center z-20 shadow-lg">
                                            <div>
                                                <p class="text-sm font-bold text-[#2c3856]" x-text="selectedProduct.name"></p>
                                                <p class="text-xs text-gray-500 font-mono" x-text="selectedProduct.sku"></p>
                                            </div>
                                            <button type="button" @click="clearSearch()" class="text-red-500 hover:text-red-700"><i class="fas fa-times"></i></button>
                                        </div>
                                    </template>

                                    <div x-show="productSearchResults.length > 0" class="absolute top-full left-0 w-full mt-2 bg-white border border-gray-100 rounded-xl shadow-2xl z-30 max-h-60 overflow-y-auto">
                                        <template x-for="product in productSearchResults" :key="product.id">
                                            <div @click="selectProduct(product)" class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-50 last:border-0">
                                                <p class="text-sm font-bold text-[#2c3856]" x-text="product.name"></p>
                                                <p class="text-xs text-gray-400 font-mono" x-text="product.sku"></p>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Calidad</label>
                                    <select x-model.number="newItem.quality_id" class="input-arch input-arch-select">
                                        <option value="">Seleccionar...</option>
                                        @foreach ($qualities as $quality)
                                            <option value="{{ $quality->id }}">{{ $quality->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Cantidad</label>
                                    <input type="number" x-model.number="newItem.quantity" min="1" class="input-arch text-center font-bold text-xl">
                                </div>
                            </div>

                            <button @click="addItemToPallet" :disabled="loading || !newItem.product_id || !newItem.quantity || !newItem.quality_id" class="btn-nexus w-full py-4 text-sm uppercase tracking-widest shadow-lg shadow-[#2c3856]/20 disabled:opacity-50 hover:bg-blue-600">
                                <i class="fas fa-plus mr-2"></i> Añadir a Tarima
                            </button>
                        </div>

                        <div class="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-xl shadow-[#2c3856]/5">
                            <div class="flex justify-between items-center mb-6">
                                <h4 class="text-lg font-raleway font-black text-[#2c3856]">Contenido Actual</h4>
                                <span class="text-xs font-bold text-gray-400 uppercase" x-text="`${currentPallet?.items.length || 0} Líneas`"></span>
                            </div>

                            <div class="space-y-3">
                                <template x-if="!currentPallet || currentPallet.items.length === 0">
                                    <div class="text-center py-8 text-gray-400 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                                        <i class="fas fa-box-open text-3xl mb-2 opacity-50"></i>
                                        <p class="text-sm font-bold">Tarima vacía</p>
                                    </div>
                                </template>

                                <template x-for="item in currentPallet?.items" :key="item.id">
                                    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex justify-between items-center group hover:border-[#ff9c00] transition-colors" :class="editingItemId === item.id ? 'ring-2 ring-[#ff9c00]' : ''">
                                        
                                        <div x-show="editingItemId !== item.id" class="flex-grow flex justify-between items-center w-full">
                                            <div class="flex items-center gap-4">
                                                <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-gray-500 font-bold text-xs">
                                                    <i class="fas fa-cube"></i>
                                                </div>
                                                <div>
                                                    <p class="font-bold text-[#2c3856]" x-text="item.product.name"></p>
                                                    <div class="flex gap-2 text-xs">
                                                        <span class="bg-blue-50 text-blue-600 px-2 rounded font-bold uppercase" x-text="item.quality.name"></span>
                                                        <span class="text-gray-400 font-mono" x-text="item.product.sku"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-6">
                                                <p class="font-black text-2xl text-[#2c3856]" x-text="item.quantity"></p>
                                                <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <button @click="startEditing(item)" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-[#2c3856] hover:text-white transition-colors"><i class="fas fa-pencil-alt text-xs"></i></button>
                                                    <button @click="deleteItem(item.id)" class="w-8 h-8 rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-colors"><i class="fas fa-trash text-xs"></i></button>
                                                </div>
                                            </div>
                                        </div>

                                        <div x-show="editingItemId === item.id" class="w-full">
                                            <div class="grid grid-cols-6 gap-4 mb-3">
                                                <div class="col-span-3">
                                                    <select x-model.number="editForm.product_id" class="input-arch text-xs"><option value="">Producto...</option>@foreach ($products as $product)<option value="{{ $product->id }}">{{ $product->name }}</option>@endforeach</select>
                                                </div>
                                                <div class="col-span-2">
                                                    <select x-model.number="editForm.quality_id" class="input-arch text-xs"><option value="">Calidad...</option>@foreach ($qualities as $quality)<option value="{{ $quality->id }}">{{ $quality->name }}</option>@endforeach</select>
                                                </div>
                                                <div>
                                                    <input type="number" x-model.number="editForm.quantity" min="1" class="input-arch text-center font-bold text-lg">
                                                </div>
                                            </div>
                                            <div class="flex justify-end gap-3">
                                                <button @click="cancelEditing()" class="text-xs font-bold text-gray-400 hover:text-[#2c3856] uppercase">Cancelar</button>
                                                <button @click="saveEdit(item.id)" class="px-4 py-1 bg-green-500 text-white rounded-lg text-xs font-bold uppercase shadow hover:bg-green-600">Guardar</button>
                                            </div>
                                        </div>

                                    </div>
                                </template>
                            </div>

                            <div class="mt-8 pt-6 border-t border-gray-100">
                                <button @click="finishPallet()" class="btn-nexus w-full py-4 text-sm uppercase tracking-widest shadow-lg bg-gray-800 hover:bg-black">
                                    <i class="fas fa-flag-checkered mr-2"></i> Finalizar Tarima
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="space-y-8">
                    <div class="bg-gray-50 rounded-[2.5rem] p-8 border border-gray-200 h-full max-h-[800px] overflow-hidden flex flex-col">
                        <h4 class="text-lg font-raleway font-black text-gray-400 mb-6 flex items-center gap-2">
                            <i class="fas fa-history"></i> Historial Reciente
                        </h4>
                        
                        <div class="flex-grow overflow-y-auto pr-2 custom-scrollbar space-y-4">
                            <template x-if="finishedPallets.length === 0">
                                <div class="text-center py-10 opacity-50">
                                    <i class="fas fa-clipboard-list text-4xl mb-2 text-gray-300"></i>
                                    <p class="text-xs font-bold text-gray-400">Sin historial en esta sesión</p>
                                </div>
                            </template>

                            <template x-for="pallet in finishedPallets.slice().reverse()" :key="pallet.lpn">
                                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm animate-pulse-once" style="--animate-duration: 0.5s;">
                                    <div class="flex justify-between items-start mb-3">
                                        <p class="font-mono font-black text-[#2c3856] text-lg" x-text="pallet.lpn"></p>
                                        <span class="text-[10px] font-bold bg-green-100 text-green-700 px-2 py-0.5 rounded uppercase">Finalizada</span>
                                    </div>
                                    
                                    <ul class="text-xs space-y-2 mb-3">
                                        <template x-for="item in pallet.items" :key="item.id">
                                            <li class="flex justify-between items-center text-gray-600 border-b border-gray-50 pb-1 last:border-0">
                                                <span>
                                                    <span class="font-bold text-[#2c3856]" x-text="`x${item.quantity}`"></span>
                                                    <span x-text="item.product.name"></span>
                                                </span>
                                            </li>
                                        </template>
                                    </ul>
                                    
                                    <div class="text-[10px] text-gray-400 text-right font-mono mt-2">
                                        <i class="fas fa-clock mr-1"></i>
                                        <span x-text="new Date(pallet.updated_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})"></span>
                                    </div>
                                </div>
                            </template>
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
                    if (!this.newItem.product_id || !this.newItem.quantity || !this.newItem.quality_id) {
                        alert('Por favor, complete todos los campos.');
                        return;
                    }

                    try {
                        const summaryItem = this.summary.find(s => s.product_id == this.newItem.product_id);
                        
                        if (!summaryItem) {
                            if (!confirm('¡PRODUCTO NO ESPERADO!\nEste producto no está en la Orden de Compra.\n¿Deseas recibirlo de todos modos?')) {
                                this.clearSearch();
                                return;
                            }
                        } else {
                            const totalAfterAdd = Number(summaryItem.received) + Number(this.newItem.quantity);
                            if (totalAfterAdd > summaryItem.ordered) {
                                if (!confirm(`¡SOBRE-RECEPCIÓN!\nRecibirás ${this.newItem.quantity} unidades.\nEl total (${totalAfterAdd}) superará lo ordenado (${summaryItem.ordered}).\n¿Continuar?`)) {
                                    return;
                                }
                            }
                        }
                    } catch (e) {
                        alert('Error al validar cantidades.');
                        return;
                    }

                    const addedQty = parseInt(this.newItem.quantity);
                    const productId = this.newItem.product_id;

                    this.loading = true;
                    try {
                        const response = await fetch(`/wms/receiving/pallets/${this.currentPallet.id}/add-item`, {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                            body: JSON.stringify(this.newItem)
                        });
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.error || 'Error al añadir producto.');
                        
                        this.currentPallet = data; 
                        this.newItem = { product_id: '', quantity: 1, quality_id: '' };
                        this.clearSearch();

                        const summaryItem = this.summary.find(s => s.product_id == productId);
                        if(summaryItem) {
                            summaryItem.received = parseInt(summaryItem.received) + addedQty;
                            summaryItem.balance = summaryItem.ordered - summaryItem.received;
                        }

                    } catch (error) {
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
                        if (!response.ok) throw new Error(data.error || 'Error del servidor.');

                        this.currentPallet.updated_at = new Date().toISOString();
                        
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
                        alert('Campos incompletos.');
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
                        alert(`Error: ${error.message}`);
                    } finally {
                        this.loading = false;
                    }
                },

                async deleteItem(itemId) {
                    if (!confirm('¿Eliminar producto de la tarima?')) return;
                    
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
                        alert(`Error: ${error.message}`);
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