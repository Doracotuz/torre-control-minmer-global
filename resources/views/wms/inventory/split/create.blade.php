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
            transition: all 0.3s ease; width: 100%; text-align: center; font-size: 1.5rem;
        }
        .input-arch:focus { border-bottom-color: #ff9c00; box-shadow: none; outline: none; }
        .input-arch::placeholder { color: #d1d5db; font-weight: 400; }

        .input-qty {
            background: transparent; border: none; border-bottom: 1px solid #e5e7eb;
            text-align: center; font-weight: 700; color: #2c3856; font-size: 1.2rem;
            width: 100%; padding: 0.5rem;
        }
        .input-qty:focus { border-bottom-color: #ff9c00; outline: none; }

        .btn-nexus { 
            background: #2c3856; color: white; border-radius: 1rem; font-weight: 700;
            transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center;
        }
        .btn-nexus:hover { background: #1a253a; transform: translateY(-2px); box-shadow: 0 10px 20px -5px rgba(44, 56, 86, 0.2); }

        [x-cloak] { display: none !important; }
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20 relative overflow-hidden">
        
        <div class="fixed inset-0 -z-10 pointer-events-none">
            <div class="absolute top-0 right-0 w-[50vw] h-full bg-gradient-to-l from-[#f8fafc] to-transparent"></div>
            <div class="absolute bottom-0 left-0 w-[40rem] h-[40rem] bg-[#ff9c00]/5 rounded-full blur-[120px]"></div>
        </div>

        <div class="max-w-4xl mx-auto px-6 pt-12 relative z-10" x-data="splitForm()">
            
            <div class="flex items-center gap-4 mb-12 stagger-enter">
                <a href="{{ route('wms.inventory.index') }}" class="w-12 h-12 rounded-xl border-2 border-gray-200 flex items-center justify-center text-gray-400 hover:border-[#ff9c00] hover:text-[#ff9c00] transition-colors">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <span class="w-8 h-1 bg-[#ff9c00]"></span>
                        <span class="text-xs font-bold text-[#2c3856] uppercase tracking-[0.2em]">Operaciones</span>
                    </div>
                    <h1 class="text-4xl font-raleway font-black text-[#2c3856]">
                        SPLIT <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-600">INVENTARIO</span>
                    </h1>
                </div>
            </div>

            @if (session('success'))
                <div class="mb-8 bg-green-50 border-l-4 border-green-500 text-green-700 p-6 rounded-r-xl shadow-sm flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center shrink-0">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <p class="font-bold text-sm uppercase tracking-wider">Operación Exitosa</p>
                        <p>{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-8 bg-red-50 border-l-4 border-red-500 text-red-700 p-6 rounded-r-xl shadow-sm flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                        <i class="fas fa-times"></i>
                    </div>
                    <div>
                        <p class="font-bold text-sm uppercase tracking-wider">Error de Proceso</p>
                        <p>{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-8 bg-red-50 border-l-4 border-red-500 text-red-700 p-6 rounded-r-xl shadow-sm">
                    <div class="flex items-center gap-4 mb-2">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <p class="font-bold text-sm uppercase tracking-wider">Errores de Validación</p>
                    </div>
                    <ul class="list-disc list-inside text-sm ml-14">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif            

            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-[#2c3856]/5 border border-gray-100 overflow-hidden relative min-h-[400px]">
                
                <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-10" x-transition:enter-end="opacity-100 translate-x-0" class="p-10 md:p-16 flex flex-col items-center justify-center h-full text-center">
                    <div class="w-20 h-20 bg-[#f3f4f6] rounded-3xl flex items-center justify-center mb-8 text-[#2c3856]">
                        <i class="fas fa-cut text-4xl"></i>
                    </div>
                    <h3 class="text-2xl font-raleway font-black text-[#2c3856] mb-2">Escanear LPN</h3>
                    <p class="text-gray-400 font-medium mb-10 max-w-sm mx-auto">Ingresa el LPN de la tarima desde la cual deseas separar productos.</p>
                    
                    <form @submit.prevent="findLpn()" class="w-full max-w-md">
                        <div class="relative mb-8">
                            <input type="text" id="lpn_input" x-model="lpnInput" class="input-arch uppercase font-mono tracking-widest" placeholder="LPN-..." required autofocus>
                            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-0 h-0.5 bg-[#ff9c00] transition-all duration-300 group-focus-within:w-full"></div>
                        </div>
                        <button type="submit" :disabled="loading || !lpnInput" class="btn-nexus w-full py-4 text-sm uppercase tracking-widest shadow-lg shadow-[#2c3856]/20 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!loading">Buscar Tarima</span>
                            <span x-show="loading"><i class="fas fa-circle-notch fa-spin mr-2"></i> Procesando...</span>
                        </button>
                    </form>
                    <p class="text-red-500 font-bold text-sm mt-6" x-text="errorMessage" x-show="errorMessage" x-transition></p>
                </div>

                <div x-show="step === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-10" x-transition:enter-end="opacity-100 translate-x-0" class="p-8 md:p-12" style="display: none;">
                    
                    <div class="flex justify-between items-center mb-8 pb-6 border-b border-gray-100">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Origen</p>
                            <h2 class="text-3xl font-mono font-black text-red-500" x-text="palletData?.lpn"></h2>
                        </div>
                        <button @click="reset()" class="text-xs font-bold text-gray-400 hover:text-[#ff9c00] uppercase tracking-widest transition-colors flex items-center gap-2">
                            <i class="fas fa-undo"></i> Cancelar
                        </button>
                    </div>

                    <template x-if="!palletData?.items || palletData.items.length === 0">
                        <div class="text-center py-10 bg-gray-50 rounded-2xl">
                            <i class="fas fa-box-open text-4xl text-gray-300 mb-4"></i>
                            <h3 class="text-lg font-bold text-[#2c3856]">LPN Vacío</h3>
                            <p class="text-sm text-gray-500 max-w-xs mx-auto mt-2">
                                Esta tarima existe históricamente (PO: <span x-text="palletData?.purchase_order?.po_number"></span>), 
                                pero no tiene productos para dividir.
                            </p>
                        </div>
                    </template>

                    <template x-if="palletData?.items && palletData.items.length > 0">
                        <div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mb-10">
                                <div class="space-y-4">
                                    <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Ubicación</p>
                                        <span class="text-xl font-bold text-[#2c3856]" x-text="palletData ? `${palletData.location.aisle}-${palletData.location.rack}-${palletData.location.shelf}-${palletData.location.bin}` : 'N/A'"></span>
                                    </div>
                                    </div>
                                <div class="flex flex-col justify-center">
                                    <label for="new_lpn" class="text-xs font-bold text-[#ff9c00] uppercase tracking-widest block mb-4 text-center">Escanear Nuevo LPN (Destino)</label>
                                    <input type="text" id="new_lpn" name="new_lpn" form="splitForm" class="input-arch text-3xl uppercase font-mono tracking-widest mb-4" placeholder="NUEVO LPN..." required>
                                </div>
                            </div>

                            <form id="splitForm" action="{{ route('wms.inventory.split.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="source_pallet_id" :value="palletData?.id">
                                
                                <h4 class="text-sm font-bold text-[#2c3856] uppercase tracking-widest mb-6 border-b border-gray-100 pb-2">Seleccionar Cantidades a Mover</h4>
                                
                                <div class="space-y-4 mb-10">
                                    <template x-for="(item, index) in palletData?.items" :key="item.id">
                                        <div x-data="{ moveQty: '' }" 
                                            class="flex flex-col md:flex-row md:items-center justify-between p-5 rounded-2xl border transition-all duration-300 bg-white shadow-sm"
                                            :class="parseInt(moveQty) > item.quantity ? 'border-red-500 bg-red-50' : 'border-gray-100 hover:border-[#ff9c00]'">
                                            
                                            <div class="flex-1 mb-4 md:mb-0">
                                                <div class="flex items-start justify-between">
                                                    <div>
                                                        <p class="font-black text-[#2c3856] text-xl" x-text="item.product.name"></p>
                                                        <div class="flex flex-wrap items-center gap-2 mt-2">
                                                            <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs font-mono font-bold border border-gray-200" x-text="item.product.sku"></span>
                                                            <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded text-xs font-bold uppercase tracking-wide" x-text="item.quality.name"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="flex items-center gap-6 border-t md:border-t-0 border-gray-100 pt-4 md:pt-0">
                                                <div class="text-right">
                                                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1">En Tarima</p>
                                                    <p class="text-3xl font-black text-[#2c3856]" x-text="item.quantity"></p>
                                                </div>
                                                
                                                <i class="fas fa-chevron-right text-gray-300 hidden md:block"></i>

                                                <div class="w-40 relative">
                                                    <p class="text-[9px] font-bold uppercase tracking-widest mb-1 text-center"
                                                    :class="parseInt(moveQty) > item.quantity ? 'text-red-600' : 'text-[#ff9c00]'">
                                                        A Mover
                                                    </p>
                                                    
                                                    <input type="hidden" :name="`items_to_split[${index}][item_id]`" :value="item.id">
                                                    
                                                    <input type="number" 
                                                        :name="`items_to_split[${index}][quantity]`" 
                                                        x-model="moveQty"
                                                        min="0" 
                                                        :max="item.quantity" 
                                                        class="input-qty w-full text-center font-bold text-xl py-2 rounded-lg border-b-2 focus:ring-0 transition-colors bg-transparent"
                                                        :class="parseInt(moveQty) > item.quantity ? 'border-red-500 text-red-600' : 'border-gray-200 focus:border-[#ff9c00] text-[#2c3856]'"
                                                        placeholder="0">

                                                    <div class="absolute top-full left-0 w-full text-center mt-1">
                                                        <template x-if="parseInt(moveQty) > item.quantity">
                                                            <span class="text-[10px] font-bold text-red-600 animate-pulse">¡Excede Existencia!</span>
                                                        </template>
                                                        <template x-if="moveQty && parseInt(moveQty) <= item.quantity">
                                                            <span class="text-[10px] font-bold text-gray-400">
                                                                Quedarán: <span x-text="item.quantity - parseInt(moveQty)"></span>
                                                            </span>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <div class="text-right">
                                    <button type="submit" class="btn-nexus px-10 py-4 text-sm uppercase tracking-widest shadow-xl shadow-[#2c3856]/20 hover:bg-green-600">
                                        <i class="fas fa-check-circle mr-3"></i> Confirmar Split
                                    </button>
                                </div>
                            </form>
                        </div>
                    </template>
                </div>

            </div>
        </div>
    </div>

    <script>
        function splitForm() {
            return {
                step: 1,
                loading: false,
                lpnInput: '',
                palletData: null,
                errorMessage: '',

                async findLpn() {
                    this.loading = true;
                    this.errorMessage = '';
                    try {
                        const response = await fetch('{{ route("wms.inventory.find-lpn") }}', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                            body: JSON.stringify({ lpn: this.lpnInput })
                        });
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.error || 'Error al buscar LPN');
                        
                        this.palletData = data;
                        this.step = 2;
                        this.$nextTick(() => {
                            const newLpnInput = document.getElementById('new_lpn');
                            if(newLpnInput) newLpnInput.focus();
                        });

                    } catch (error) {
                        this.errorMessage = error.message;
                        this.lpnInput = '';
                        this.$nextTick(() => document.getElementById('lpn_input').focus());
                    } finally {
                        this.loading = false;
                    }
                },

                reset() {
                    this.step = 1;
                    this.palletData = null;
                    this.lpnInput = '';
                    this.errorMessage = '';
                    this.$nextTick(() => document.getElementById('lpn_input').focus());
                }
            }
        }
    </script>
</x-app-layout>