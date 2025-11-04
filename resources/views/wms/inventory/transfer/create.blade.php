<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-3xl text-gray-800 leading-tight tracking-tight">
            Transferencia de Inventario
        </h2>
    </x-slot>

    <div class="py-12" x-data="transferApp()">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md shadow-md" role="alert">
                    <p class="font-bold">Éxito</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md shadow-md" role="alert">
                    <p class="font-bold">Error</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md shadow-md" role="alert">
                    <p class="font-bold">Error de Validación</p>
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif            
            <div class="bg-white rounded-2xl shadow-xl border p-8">

                <div x-show="step === 1" x-transition>
                    <form @submit.prevent="findLpn()">
                        <div class="text-center">
                            <i class="fas fa-barcode text-gray-300 text-6xl mb-4"></i>
                            <h3 class="text-2xl font-bold text-gray-800">Escanear Tarima (LPN)</h3>
                            <p class="text-gray-600 my-4">Ingresa el LPN de la tarima que deseas mover.</p>
                        </div>
                        <div class="max-w-md mx-auto relative">
                            <input type="text" id="lpn_input" x-model="lpnInput" class="pl-4 w-full text-center text-2xl font-mono rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-3" placeholder="LPN-..." required autofocus>
                        </div>
                        <div class="text-center mt-4">
                            <button type="submit" :disabled="loading || !lpnInput" class="px-8 py-3 bg-indigo-600 text-white font-semibold rounded-lg shadow-lg hover:bg-indigo-700 text-lg transition disabled:opacity-50">
                                <span x-show="!loading"><i class="fas fa-search mr-2"></i> Buscar Tarima</span>
                                <span x-show="loading"><i class="fas fa-spinner fa-spin mr-2"></i>Buscando...</span>
                            </button>
                        </div>
                        <template x-if="errorMessage"><p class="text-center text-red-500 mt-3" x-text="errorMessage"></p></template>
                    </form>
                </div>

                <div x-show="step === 2" x-transition>
                    <div class="text-right mb-4">
                        <button @click="reset()" class="text-sm text-gray-600 hover:text-gray-900">&larr; Escanear otra tarima</button>
                    </div>

                    <h3 class="font-bold text-xl text-gray-800 border-b pb-3 mb-4">Confirmar Transferencia</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-4 rounded-lg border mb-6">
                        <div>
                            <p class="text-sm text-gray-500">LPN a Mover</p>
                            <p class="font-mono font-bold text-2xl text-indigo-600" x-text="palletData.lpn"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Ubicación Actual</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="font-semibold text-xl text-gray-800" x-text="`${palletData.location.aisle}-${palletData.location.rack}-${palletData.location.shelf}-${palletData.location.bin}`"></p>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800" x-text="palletData.location.type"></span>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-500">Contenido</p>
                            <ul class="text-sm space-y-2 mt-1">
                                <template x-for="item in palletData.items" :key="item.id">
                                    <li class="flex justify-between items-center">
                                        <div>
                                            <p class="font-semibold text-gray-800" x-text="item.product.name"></p>
                                            <p class="text-xs text-gray-500">
                                                <span class="font-mono" x-text="item.product.sku"></span> | 
                                                <span class="font-semibold" x-text="item.quality.name"></span>
                                            </p>
                                        </div>
                                        <strong class="text-lg font-bold" x-text="`x${item.quantity}`"></strong>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>

                    <form action="{{ route('wms.inventory.transfer.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="pallet_id" :value="palletData.id">
                        
                        <label for="destination_location_code" class="block font-medium text-gray-700">Escanear Ubicación de Destino</label>
                        <input type="text" id="destination_location_code" name="destination_location_code" x-model="locationInput" class="mt-1 block w-full text-center text-xl font-mono rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 py-3" placeholder="UBICACIÓN..." required>
                        
                        <div class="text-right mt-6">
                            <button type="submit" :disabled="!locationInput" class="px-8 py-3 bg-green-600 text-white font-bold rounded-lg shadow-lg hover:bg-green-700 text-lg transition disabled:opacity-50">
                                <i class="fas fa-check-circle mr-2"></i> Confirmar Transferencia
                            </button>
                        </div>
                    </form>
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
                        if (!response.ok) throw new Error(data.error);
                        
                        this.palletData = data;
                        this.step = 2;
                        this.$nextTick(() => document.getElementById('destination_location_code').focus());

                    } catch (error) {
                        this.errorMessage = error.message;
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