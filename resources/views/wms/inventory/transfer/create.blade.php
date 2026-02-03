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

        <div class="max-w-4xl mx-auto px-6 pt-12 relative z-10" x-data="transferApp()">
            
            <div class="flex items-center gap-4 mb-12 stagger-enter">
                <a href="{{ route('wms.inventory.index') }}" class="w-12 h-12 rounded-xl border-2 border-gray-200 flex items-center justify-center text-gray-400 hover:border-[#ff9c00] hover:text-[#ff9c00] transition-colors">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <span class="w-8 h-1 bg-[#ff9c00]"></span>
                        <span class="text-xs font-bold text-[#2c3856] uppercase tracking-[0.2em]">Movimientos Internos</span>
                    </div>
                    <h1 class="text-4xl font-raleway font-black text-[#2c3856]">
                        TRANSFERENCIA <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-600">LPN</span>
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

            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-[#2c3856]/5 border border-gray-100 overflow-hidden relative min-h-[400px]">
                
                <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-10" x-transition:enter-end="opacity-100 translate-x-0" class="p-10 md:p-16 flex flex-col items-center justify-center h-full text-center">
                    <div class="w-20 h-20 bg-[#f3f4f6] rounded-3xl flex items-center justify-center mb-8 text-[#2c3856]">
                        <i class="fas fa-barcode text-4xl"></i>
                    </div>
                    <h3 class="text-2xl font-raleway font-black text-[#2c3856] mb-2">Escanear LPN</h3>
                    <p class="text-gray-400 font-medium mb-10 max-w-sm mx-auto">Escanea el código de barras de la tarima (LPN) que deseas reubicar.</p>
                    
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
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">LPN Identificado</p>
                            <h2 class="text-3xl font-mono font-black text-[#2c3856]" x-text="palletData?.lpn"></h2>
                            <p class="text-xs text-gray-500 mt-1" x-show="palletData?.purchase_order">
                                Origen: <span class="font-bold" x-text="palletData?.purchase_order?.po_number"></span>
                            </p>
                        </div>
                        <button @click="reset()" class="text-xs font-bold text-gray-400 hover:text-[#ff9c00] uppercase tracking-widest transition-colors flex items-center gap-2">
                            <i class="fas fa-undo"></i> Cancelar
                        </button>
                    </div>

                    <template x-if="!palletData?.items || palletData.items.length === 0">
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-r-xl">
                            <div class="flex items-start gap-4">
                                <i class="fas fa-exclamation-triangle text-3xl text-yellow-500"></i>
                                <div>
                                    <h3 class="text-lg font-bold text-yellow-700">Tarima sin Existencias</h3>
                                    <p class="text-yellow-600 text-sm mt-1">
                                        Este LPN existe en el sistema y está vinculado a la ubicación 
                                        <strong x-text="palletData?.location?.code"></strong>, 
                                        pero no contiene productos físicos actualmente.
                                    </p>
                                    <p class="text-yellow-600 text-sm mt-2 font-bold">
                                        Acción bloqueada: No se pueden realizar transferencias de tarimas vacías.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="palletData?.items && palletData.items.length > 0">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                            
                            <div class="space-y-8">
                                <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Ubicación Actual</p>
                                    <div class="flex items-center gap-3">
                                        <i class="fas fa-map-marker-alt text-[#ff9c00]"></i>
                                        <span class="text-xl font-bold text-[#2c3856]" x-text="palletData ? `${palletData.location.aisle}-${palletData.location.rack}-${palletData.location.shelf}-${palletData.location.bin}` : 'N/A'"></span>
                                    </div>
                                </div>

                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4">Contenido</p>
                                    <div class="space-y-3 max-h-48 overflow-y-auto pr-2 custom-scrollbar">
                                        <template x-for="item in palletData?.items" :key="item.id">
                                            <div class="flex justify-between items-center p-3 rounded-xl border border-gray-100 hover:border-gray-200 transition-colors">
                                                <div>
                                                    <p class="font-bold text-sm text-[#2c3856]" x-text="item.product.name"></p>
                                                    <p class="text-[10px] text-gray-400 font-mono">
                                                        <span x-text="item.product.sku"></span>
                                                    </p>
                                                </div>
                                                <span class="font-black text-lg text-[#2c3856]" x-text="item.quantity"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col justify-center">
                                <form action="{{ route('wms.inventory.transfer.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="pallet_id" :value="palletData?.id">
                                    
                                    <div class="mb-10">
                                        <label for="destination_location_code" class="text-xs font-bold text-[#ff9c00] uppercase tracking-widest block mb-4 text-center">Escanear Destino</label>
                                        <input type="text" id="destination_location_code" name="destination_location_code" x-model="locationInput" class="input-arch text-3xl uppercase font-mono tracking-widest" placeholder="UBICACIÓN" required>
                                    </div>
                                    
                                    <button type="submit" :disabled="!locationInput" class="btn-nexus w-full py-5 text-sm uppercase tracking-widest shadow-xl shadow-[#2c3856]/20 disabled:opacity-50 hover:bg-green-600">
                                        <i class="fas fa-exchange-alt mr-3"></i> Confirmar Movimiento
                                    </button>
                                </form>
                            </div>

                        </div>
                    </template>
                </div>

            </div>
        </div>
    </div>

    <script>
        function transferApp() {
            return {
                step: 1,
                loading: false,
                lpnInput: '',
                locationInput: '',
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
                            const destInput = document.getElementById('destination_location_code');
                            if(destInput) destInput.focus();
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
                    this.locationInput = '';
                    this.errorMessage = '';
                    this.$nextTick(() => document.getElementById('lpn_input').focus());
                }
            }
        }
    </script>
</x-app-layout>