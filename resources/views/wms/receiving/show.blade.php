<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Recepción de PO: {{ $purchaseOrder->po_number }}</h2>
    </x-slot>

    <div class="py-12" x-data="receivingApp">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white p-6 rounded-lg shadow-xl mb-8">
                <h3 class="font-bold text-lg text-gray-800 border-b pb-3 mb-3">Resumen de Recepción</h3>
                <div class="space-y-2">
                    <template x-for="item in summary" :key="item.product_id">
                        <div class="grid grid-cols-12 gap-4 items-center text-sm p-2 rounded-md" :class="{'bg-green-50': item.balance <= 0, 'bg-yellow-50': item.balance > 0}">
                            <div class="col-span-12 md:col-span-6">
                                <p class="font-semibold text-gray-800" x-text="item.name"></p>
                                <p class="text-xs text-gray-500 font-mono" x-text="item.sku"></p>
                            </div>
                            <div class="col-span-4 md:col-span-2 text-center">
                                <span class="font-semibold text-gray-500">Planeado:</span>
                                <span class="font-bold text-lg text-gray-800" x-text="item.ordered"></span>
                            </div>
                            <div class="col-span-4 md:col-span-2 text-center">
                                <span class="font-semibold text-gray-500">Recibido:</span>
                                <span class="font-bold text-lg text-gray-800" x-text="item.received"></span>
                            </div>
                            <div class="col-span-4 md:col-span-2 text-center">
                                <span class="px-2 py-1 text-xs font-bold rounded-full"
                                      :class="{
                                        'bg-green-100 text-green-800': item.balance == 0,
                                        'bg-yellow-100 text-yellow-800': item.balance > 0,
                                        'bg-red-100 text-red-800': item.balance < 0
                                      }">
                                    <span x-show="item.balance > 0" x-text="`FALTAN ${item.balance}`"></span>
                                    <span x-show="item.balance == 0">COMPLETO</span>
                                    <span x-show="item.balance < 0" x-text="`SOBRAN ${Math.abs(item.balance)}`"></span>
                                </span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            <div x-show="step === 'start'" class="text-center bg-white p-8 rounded-lg shadow-xl">
                <h3 class="text-2xl font-bold text-gray-800">Listo para Recibir</h3>
                <p class="text-gray-600 my-4">Presiona el botón para comenzar a registrar una nueva tarima para esta orden de compra.</p>
                <button @click="startNewPallet()" :disabled="loading" class="w-full px-8 py-4 bg-indigo-600 text-white font-semibold rounded-md shadow-lg hover:bg-indigo-700 text-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!loading"><i class="fas fa-plus mr-2"></i> Crear Nueva Tarima</span>
                    <span x-show="loading">Creando tarima...</span>
                </button>
            </div>

            <div x-show="step === 'receiving'" class="bg-white p-8 rounded-lg shadow-xl space-y-6">
                <template x-if="currentPallet">
                    <div>
                        <div class="text-center p-4 bg-indigo-50 rounded-lg">
                            <p class="text-sm font-semibold text-indigo-700">Registrando en Tarima</p>
                            <p class="text-3xl font-mono font-bold text-indigo-900" x-text="currentPallet.lpn"></p>
                        </div>
                        <div class="mt-6">
                             <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Producto</label>
                                    <select x-model.number="newItem.product_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Seleccione o escanee...</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                 <div>
                                    <label class="block text-sm font-medium text-gray-700">Cantidad</label>
                                    <input type="number" x-model.number="newItem.quantity" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                            <button @click="addItemToPallet" :disabled="loading || !newItem.product_id || !newItem.quantity" class="w-full mt-4 px-6 py-3 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">Añadir a la Tarima</button>
                        </div>
                        <div class="border-t pt-4 mt-6">
                            <h4 class="font-semibold text-lg text-gray-800">Contenido Actual de la Tarima:</h4>
                            <ul class="space-y-2 mt-2 max-h-60 overflow-y-auto">
                                <template x-for="item in currentPallet.items" :key="item.id">
                                    <li class="flex justify-between p-2 bg-gray-50 rounded-md">
                                        <span class="truncate pr-4" x-text="`${item.product.name} (${item.product.sku})`"></span>
                                        <span class="font-bold flex-shrink-0" x-text="`x ${item.quantity}`"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                        <div class="flex justify-between items-center mt-6 border-t pt-6">
                            <button @click="finishPallet(true)" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Finalizar Tarima</button>
                            <a href="{{ route('wms.purchase-orders.show', $purchaseOrder) }}" class="px-6 py-3 bg-green-600 text-white font-semibold rounded-md shadow-md hover:bg-green-700">Terminar Recepción</a>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('receivingApp', () => ({
                summary: @json($receivingSummary),
                step: 'start',
                loading: false,
                purchaseOrderId: {{ $purchaseOrder->id }},
                currentPallet: null,
                newItem: { product_id: '', quantity: 1 },
                
                async startNewPallet() {
                    this.loading = true;
                    this.currentPallet = null;
                    try {
                        const response = await fetch('{{ route('wms.receiving.startPallet') }}', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                            body: JSON.stringify({ purchase_order_id: this.purchaseOrderId })
                        });
                        if (!response.ok) {
                            const errorData = await response.json();
                            throw new Error(errorData.error || 'Error al crear la tarima.');
                        }
                        this.currentPallet = await response.json();
                        this.step = 'receiving';
                    } catch (error) {
                        console.error(error);
                        alert(error.message);
                    } finally {
                        this.loading = false;
                    }
                },

                async addItemToPallet() {
                    if (!this.newItem.product_id || !this.newItem.quantity) return;
                    this.loading = true;
                    try {
                        const response = await fetch(`/wms/receiving/pallets/${this.currentPallet.id}/add-item`, {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                            body: JSON.stringify(this.newItem)
                        });
                        if (!response.ok) throw new Error('Error al añadir el producto.');
                        
                        this.currentPallet = await response.json();
                        this.newItem = { product_id: '', quantity: 1 };
                    } catch (error) {
                        console.error(error);
                        alert(error.message);
                    } finally {
                        this.loading = false;
                    }
                },
                
                finishPallet() {
                    if (!this.currentPallet || this.currentPallet.items.length === 0) {
                        alert('No hay productos en la tarima actual para finalizar.');
                        this.step = 'start';
                        return;
                    }

                    // Actualiza el resumen con los datos de la tarima actual
                    this.currentPallet.items.forEach(palletItem => {
                        const summaryItem = this.summary.find(item => item.product_id == palletItem.product_id);
                        if (summaryItem) {
                            summaryItem.received += palletItem.quantity;
                            summaryItem.balance = summaryItem.ordered - summaryItem.received;
                        }
                    });

                    alert(`Tarima ${this.currentPallet.lpn} finalizada y registrada en ubicación de RECEPCION. El inventario ha sido actualizado.`);
                    
                    // Resetea para la siguiente tarima
                    this.currentPallet = null; 
                    this.step = 'start';
                }
            }));
        });
    </script>
</x-app-layout>