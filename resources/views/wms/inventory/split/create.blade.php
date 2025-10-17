<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-3xl text-gray-800 leading-tight tracking-tight">
            Dividir Inventario de Tarima (Split)
        </h2>
    </x-slot>

    <div class="py-12" x-data="splitForm()">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-xl border p-8">
                
                {{-- Muestra errores de sesión del backend --}}
                @if (session('error'))
                    <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert">
                        <p class="font-bold">Error:</p>
                        <p>{{ session('error') }}</p>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                        <p class="font-bold">Por favor, corrige los siguientes errores:</p>
                        <ul class="list-disc list-inside">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <div x-show="step === 1" x-transition>
                    <form @submit.prevent="findLpn()">
                        <div class="text-center">
                            <i class="fas fa-cut text-gray-300 text-6xl mb-4"></i>
                            <h3 class="text-2xl font-bold text-gray-800">Escanear Tarima Origen</h3>
                            <p class="text-gray-600 my-4">Ingresa el LPN de la tarima de la cual deseas mover productos.</p>
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

                    <h3 class="font-bold text-xl text-gray-800 border-b pb-3 mb-4">Confirmar Split</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-4 rounded-lg border mb-6">
                        <div>
                            <p class="text-sm text-gray-500">LPN Origen</p>
                            <p class="font-mono font-bold text-2xl text-red-600" x-text="palletData.lpn"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Ubicación Actual</p>
                            <p class="font-semibold text-xl text-gray-800" x-text="palletData.location ? `${palletData.location.aisle}-${palletData.location.rack}-${palletData.location.shelf}-${palletData.location.bin}` : 'N/A'"></p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-500">Origen (PO)</p>
                            <p class="font-mono text-sm" x-text="`${palletData.purchase_order.po_number} | Pedimento A4: ${palletData.purchase_order.pedimento_a4 || 'N/A'}`"></p>
                        </div>
                    </div>

                    <form action="{{ route('wms.inventory.split.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="source_pallet_id" :value="palletData.id">
                        
                        <div class="space-y-6">
                            <div>
                                <label for="new_lpn" class="block font-medium text-gray-700">Escanear LPN de la <strong>Nueva</strong> Tarima (Destino)</label>
                                <input type="text" id="new_lpn" name="new_lpn" class="mt-1 block w-full text-center text-xl font-mono rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 py-3" placeholder="NUEVO LPN..." required>
                            </div>
                            
                            <div class="border-t pt-4">
                                <label class="block font-medium text-gray-700">Indica la cantidad a <strong>mover</strong> a la nueva tarima:</label>
                                <div class="space-y-3 mt-2">
                                    <template x-for="(item, index) in palletData.items" :key="item.id">
                                        <div class="grid grid-cols-3 gap-4 items-center p-2 rounded-md hover:bg-gray-50">
                                            <div class="col-span-2">
                                                <p class="font-semibold" x-text="item.product.name"></p>
                                                <p class="text-xs text-gray-500" x-text="`SKU: ${item.product.sku} | Calidad: ${item.quality.name} | Disp: ${item.quantity}`"></p>
                                            </div>
                                            <div>
                                                <input type="hidden" :name="`items_to_split[${index}][item_id]`" :value="item.id">
                                                <input type="number" :name="`items_to_split[${index}][quantity]`" value="0" min="0" :max="item.quantity" class="w-full rounded-md border-gray-300 text-center text-lg font-bold">
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="text-right mt-8 border-t pt-6">
                            <button type="submit" class="px-8 py-3 bg-green-600 text-white font-bold rounded-lg shadow-lg hover:bg-green-700 text-lg transition">
                                <i class="fas fa-check-circle mr-2"></i> Confirmar Split
                            </button>
                        </div>
                    </form>
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
                        if (!response.ok) throw new Error(data.error);
                        
                        this.palletData = data;
                        this.step = 2;
                        this.$nextTick(() => document.getElementById('new_lpn').focus());

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
                    this.errorMessage = '';
                    this.$nextTick(() => document.getElementById('lpn_input').focus());
                }
            }
        }
    </script>
</x-app-layout>