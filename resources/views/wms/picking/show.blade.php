<x-app-layout>
    <x-slot name="header"></x-slot>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Raleway:wght@800;900&display=swap');
        
        .font-raleway { font-family: 'Raleway', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        
        .stagger-enter { opacity: 0; transform: translateY(20px); animation: enterUp 0.5s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; }
        @keyframes enterUp { to { opacity: 1; transform: translateY(0); } }
        
        .input-arch {
            background: transparent; border: none; border-bottom: 2px solid #e5e7eb; border-radius: 0;
            padding: 0.8rem 0; font-family: 'Montserrat', sans-serif; font-weight: 600; color: #2c3856;
            transition: all 0.3s ease; width: 100%; font-size: 0.9rem;
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

        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .input-scan {
            width: 100%; padding: 0.5rem; border: 1px solid #e5e7eb; border-radius: 0.5rem; 
            font-family: 'Montserrat', monospace; font-weight: 600; color: #2c3856;
            transition: all 0.2s;
        }
        .input-scan:focus {
             border-color: #ff9c00; outline: none; box-shadow: 0 0 0 2px rgba(255, 156, 0, 0.2);
        }

        [x-cloak] { display: none !important; }
    </style>

    <div class="min-h-screen text-[#2b2b2b] font-montserrat pb-20 relative overflow-x-hidden">
        
        <div class="fixed inset-0 -z-10 pointer-events-none">
            <div class="absolute top-0 right-0 w-[60vw] h-[60vh] bg-gradient-to-bl from-[#e0e7ff] to-transparent opacity-50 blur-[100px]"></div>
            <div class="absolute bottom-0 left-0 w-[40rem] h-[40rem] bg-[#fff7ed] rounded-full blur-[120px]"></div>
        </div>

        <div class="max-w-[1920px] mx-auto px-4 md:px-8 pt-8 relative z-10" x-data='pickingForm(@json($pickList->items), "{{ csrf_token() }}")' x-cloak>
            
            <div class="flex flex-col xl:flex-row justify-between items-end mb-8 stagger-enter" style="animation-delay: 0.1s;">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-12 h-1 bg-[#ff9c00] rounded-full"></span>
                        <a href="{{ route('wms.sales-orders.show', $pickList->salesOrder) }}" class="text-xs font-bold text-gray-400 tracking-[0.2em] uppercase hover:text-[#ff9c00] transition-colors">
                            <i class="fas fa-arrow-left mr-1"></i> Volver a Orden {{ $pickList->salesOrder->so_number }}
                        </a>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-raleway font-black text-[#2c3856] leading-none">
                        TAREA DE <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-500">PICKING</span>
                    </h1>
                </div>

                <div class="mt-6 xl:mt-0">
                    <a href="{{ route('wms.picking.pdf', $pickList) }}" target="_blank" class="btn-ghost px-6 py-3 h-12 flex items-center gap-2 text-xs uppercase tracking-wider bg-white shadow-sm hover:shadow-md">
                        <i class="fas fa-file-pdf text-red-500"></i> Descargar PDF
                    </a>
                </div>
            </div>

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-xl font-bold flex items-center gap-3 stagger-enter">
                    <i class="fas fa-exclamation-circle text-xl"></i>
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 stagger-enter" style="animation-delay: 0.2s;">
                <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Cliente</p>
                        <p class="font-bold text-[#2c3856]">{{ $pickList->salesOrder->customer_name }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-xl">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Fecha Orden</p>
                        <p class="font-bold text-[#2c3856]">{{ $pickList->salesOrder->order_date->format('d/m/Y') }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Estatus</p>
                        <span class="inline-block px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider
                            {{ $pickList->status == 'Generated' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                            {{ $pickList->status == 'Generated' ? 'En Proceso' : $pickList->status }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="glass-panel rounded-[2.5rem] p-8 mb-8 stagger-enter" style="animation-delay: 0.3s;">
                <label for="staging_location_id" class="block text-xs font-bold text-[#2c3856] uppercase tracking-widest mb-3">
                    <i class="fas fa-map-marker-alt text-[#ff9c00] mr-2"></i> 1. Selecciona Ubicación de Empaque (Staging)
                </label>
                <select id="staging_location_id" name="staging_location_id" x-model="selectedStagingLocationId"
                        class="input-arch input-arch-select text-lg font-bold text-[#2c3856]">
                    <option value="">-- Seleccionar Ubicación --</option>
                    @foreach($stagingLocations as $location)
                        <option value="{{ $location->id }}">
                            {{ $location->code }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div x-show="selectedStagingLocationId" x-transition class="stagger-enter" style="animation-delay: 0.4s;">
                <h3 class="text-xl font-raleway font-black text-[#2c3856] mb-6 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-[#2c3856] text-white flex items-center justify-center text-sm">2</span>
                    Confirmación de Productos
                </h3>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <template x-for="(item, index) in items" :key="item.id">
                        <div class="relative bg-white rounded-[2rem] p-6 shadow-sm border transition-all duration-300"
                             :class="item.is_picked ? 'border-green-200 shadow-md' : 'border-gray-100 hover:shadow-lg'">
                            
                            <div class="absolute top-0 right-0 p-4" x-show="item.is_picked" x-transition>
                                <div class="bg-green-100 text-green-600 rounded-full px-4 py-1 text-xs font-black uppercase tracking-widest flex items-center gap-2">
                                    <i class="fas fa-check"></i> Listo
                                </div>
                            </div>

                            <div class="flex flex-col md:flex-row gap-6">
                                <div class="flex-1">
                                    <div class="mb-4">
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Ubicación Origen</p>
                                        <p class="text-3xl font-raleway font-black text-[#2c3856]" x-text="item.location ? item.location.code : 'N/A'"></p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded text-[10px] font-bold font-mono" x-text="item.pallet?.lpn || 'N/A'"></span>
                                            <span class="text-gray-300">|</span>
                                            <span class="text-xs font-bold text-blue-600" x-text="item.quality?.name || 'N/A'"></span>
                                        </div>
                                    </div>

                                    <div>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Producto</p>
                                        <p class="font-bold text-gray-800 leading-tight" x-text="item.product?.name || 'N/A'"></p>
                                        <p class="text-xs font-mono text-gray-500 mt-1" x-text="item.product?.sku || 'N/A'"></p>
                                    </div>
                                </div>

                                <div class="w-full md:w-64 bg-gray-50 rounded-2xl p-5 border border-gray-100 flex flex-col justify-center">
                                    <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-2">
                                        <span class="text-xs font-bold text-gray-500 uppercase">A Recolectar</span>
                                        <span class="text-2xl font-black text-[#2c3856]" x-text="item.quantity_to_pick"></span>
                                    </div>

                                    <div x-show="!item.is_picked" class="space-y-3">
                                        <div>
                                            <input :id="'loc_'+index" type="text" x-model="item.scanned_location_code" 
                                                   @keydown.enter.prevent="focusNext($event.target, 'lpn_'+index)" 
                                                   class="input-scan text-center uppercase" placeholder="Scan Ubicación">
                                        </div>
                                         <div>
                                            <input :id="'lpn_'+index" type="text" x-model="item.scanned_lpn" 
                                                   @keydown.enter.prevent="focusNext($event.target, 'sku_'+index)" 
                                                   class="input-scan text-center uppercase" placeholder="Scan LPN">
                                        </div>
                                        <div>
                                            <input :id="'sku_'+index" type="text" x-model="item.scanned_sku" 
                                                   @keydown.enter.prevent="focusNext($event.target, 'qty_'+index)" 
                                                   class="input-scan text-center uppercase" placeholder="Scan SKU">
                                        </div>
                                        <div>
                                            <input :id="'qty_'+index" type="number" x-model.number="item.scanned_quantity" 
                                                   @keydown.enter.prevent="confirmItem(index)" 
                                                   class="input-scan text-center font-bold text-blue-600" placeholder="Cant.">
                                        </div>
                                        
                                        <button @click="confirmItem(index)" :disabled="item.loading"
                                                class="w-full btn-nexus py-3 text-[10px] uppercase tracking-widest shadow-lg">
                                            <span x-show="!item.loading">Confirmar</span>
                                            <span x-show="item.loading"><i class="fas fa-circle-notch fa-spin"></i></span>
                                        </button>
                                        
                                        <p x-show="item.message" x-text="item.message" 
                                           class="text-[10px] text-center font-bold mt-2" 
                                           :class="item.message_type === 'error' ? 'text-red-500' : 'text-green-500'"></p>
                                    </div>

                                    <div x-show="item.is_picked" class="text-center py-4">
                                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2 text-green-500 text-2xl">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <p class="text-xs font-bold text-green-600 uppercase">Confirmado</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 p-4 shadow-2xl z-50 flex justify-center transform transition-transform duration-300"
                     x-show="selectedStagingLocationId && allItemsConfirmed"
                     x-transition:enter="translate-y-full" x-transition:enter-end="translate-y-0"
                     x-transition:leave="translate-y-0" x-transition:leave-end="translate-y-full">
                     
                    <form action="{{ route('wms.picking.complete', $pickList) }}" method="POST" class="w-full max-w-md"
                          onsubmit="return confirm('¿Completar picking y mover orden a Empacado?');">
                        @csrf
                        <input type="hidden" name="staging_location_id" :value="selectedStagingLocationId">
                        <button type="submit" class="w-full btn-nexus py-4 text-sm uppercase tracking-widest shadow-xl bg-green-600 hover:bg-green-700">
                            <i class="fas fa-flag-checkered mr-2"></i> Completar Picking
                        </button>
                    </form>
                </div>
            </div>

            @if ($pickList->status == 'Completed')
                <div class="mt-8 mb-12 bg-green-50 rounded-[2rem] p-8 text-center border border-green-100">
                    <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <h3 class="text-2xl font-raleway font-black text-green-800 mb-2">Surtido Completado</h3>
                    <p class="text-green-700 font-medium">
                        Confirmado por <strong>{{ \App\Models\User::find($pickList->picker_id)->name ?? 'Usuario' }}</strong>
                        el {{ $pickList->picked_at ? $pickList->picked_at->format('d/m/Y H:i') : 'N/A' }}.
                    </p>
                </div>
            @endif
        </div>
    </div>

    <script>
        function pickingForm(initialItems, csrfToken) {
            return {
                items: initialItems.map(item => ({
                    ...item,
                    scanned_location_code: '',
                    scanned_sku: '',
                    scanned_quantity: item.quantity_to_pick,
                    scanned_lpn: '',
                    is_picked: item.is_picked || false,
                    loading: false,
                    message: '',
                    message_type: ''
                })),
                selectedStagingLocationId: '',

                get allItemsConfirmed() {
                    return this.items.length > 0 && this.items.every(item => item.is_picked);
                },

                confirmItem(index) {
                    const item = this.items[index];
                    item.loading = true;
                    item.message = '';
                    item.message_type = '';

                    fetch(`/wms/picking/item/${item.id}/confirm`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            scanned_location_code: item.scanned_location_code,
                            scanned_sku: item.scanned_sku,
                            scanned_quantity: item.scanned_quantity,
                            scanned_lpn: item.scanned_lpn
                        })
                    })
                    .then(response => response.json().then(data => ({ status: response.status, body: data })))
                    .then(({ status, body }) => {
                        if (status === 200 && body.success) {
                            item.is_picked = true;
                            item.message = body.message || 'Confirmado!';
                            item.message_type = 'success';
                        } else {
                            item.message = body.message || 'Error';
                            item.message_type = 'error';
                        }
                    })
                    .catch(error => {
                        item.message = 'Error de conexión.';
                        item.message_type = 'error';
                        console.error(error);
                    })
                    .finally(() => {
                        item.loading = false;
                        this.$nextTick(() => {
                             const nextUnpickedIndex = this.items.findIndex((itm, idx) => idx > index && !itm.is_picked);
                             if (nextUnpickedIndex !== -1) {
                                const nextInput = document.getElementById('loc_' + nextUnpickedIndex);
                                if (nextInput) {
                                    nextInput.focus();
                                    nextInput.select();
                                }
                             }
                        });
                    });
                },

                focusNext(currentElement, nextElementId) {
                    const nextElement = document.getElementById(nextElementId);
                    if (nextElement) {
                        nextElement.focus();
                        nextElement.select();
                    } else {
                         const index = currentElement.id.split('_')[1];
                         this.confirmItem(parseInt(index));
                    }
                }
            }
        }
    </script>
</x-app-layout>