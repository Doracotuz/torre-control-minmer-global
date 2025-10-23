<x-app-layout>
    <x-slot name="header">
         <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Tarea de Surtido (Picking)
                </h2>
                <p class="text-sm text-gray-500">
                    Para Orden de Venta:
                    <a href="{{ route('wms.sales-orders.show', $pickList->salesOrder) }}" class="text-indigo-600 hover:underline">
                        {{ $pickList->salesOrder->so_number }}
                    </a>
                </p>
            </div>
            <a href="{{ route('wms.picking.pdf', $pickList) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md shadow-sm hover:bg-red-700 text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Descargar PDF
            </a>
        </div>
    </x-slot>

    {{-- Mensaje de Error General --}}
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
        @if (session('error'))
            <div class="p-4 mb-4 text-sm text-red-800 bg-red-100 rounded-lg shadow" role="alert">
                <span class="font-bold">Error:</span> {{ session('error') }}
            </div>
        @endif
    </div>

    {{--
      LA CORRECCIÓN ESTÁ AQUÍ: Se usan comillas simples (') para x-data
    --}}
    <div class="py-12" x-data='pickingForm(@json($pickList->items), "{{ csrf_token() }}")'>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 md:p-8 rounded-2xl shadow-xl border">

                {{-- Información General --}}
                <div class="mb-8 pb-4 border-b grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                     <div>
                        <span class="text-gray-500 font-medium block">Cliente:</span>
                        <p class="text-gray-800 font-semibold">{{ $pickList->salesOrder->customer_name }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 font-medium block">Fecha Orden:</span>
                        <p class="text-gray-800 font-semibold">{{ $pickList->salesOrder->order_date->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 font-medium block">Estatus Picking:</span>
                        <span @class([
                            'px-3 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full',
                            'bg-blue-100 text-blue-800' => $pickList->status == 'Generated',
                            'bg-green-100 text-green-800' => $pickList->status == 'Completed',
                            'bg-gray-100 text-gray-800' => !in_array($pickList->status, ['Generated', 'Completed']),
                        ])>
                            {{ $pickList->status == 'Generated' ? 'Generado (Pendiente)' : $pickList->status }}
                        </span>
                    </div>
                </div>

                {{-- PASO 1: SELECCIONAR UBICACIÓN DE STAGING --}}
                <div class="mb-6 bg-gray-50 p-4 rounded-lg border">
                    <label for="staging_location_id" class="block text-sm font-medium text-gray-700 mb-1">
                        1. Selecciona la ubicación de empaque/staging donde dejarás los productos:
                    </label>
                    <select id="staging_location_id" name="staging_location_id"
                            x-model="selectedStagingLocationId"
                            class="w-full md:w-1/2 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">-- Selecciona una ubicación --</option>
                        @foreach($stagingLocations as $location)
                            <option value="{{ $location->id }}">
                                {{ $location->code }} - {{ $location->aisle ?? '' }}{{ $location->rack ?? '' }}{{ $location->shelf ?? '' }}{{ $location->bin ?? '' }} ({{ $location->getTranslatedTypeAttribute() }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- PASO 2: CONFIRMAR ITEMS (Solo visible si se seleccionó staging) --}}
                <div x-show="selectedStagingLocationId" x-transition>
                    <h3 class="font-bold text-xl text-gray-800 mb-4">2. Confirma cada producto</h3>

                    {{-- Lista de Items a Surtir --}}
                    <div class="space-y-6">
                        <template x-for="(item, index) in items" :key="item.id">
                            <div class="border rounded-lg overflow-hidden" :class="{ 'border-green-400 bg-green-50': item.is_picked, 'border-gray-200': !item.is_picked }">
                                <div class="p-4 grid grid-cols-1 md:grid-cols-5 gap-4">
                                    {{-- Info Esperada --}}
                                    <div class="md:col-span-2">
                                        <p class="text-xs text-gray-500">Esperado:</p>
                                        {{-- Usa optional chaining (?) por si location o pallet son null --}}
                                        <p class="font-mono font-bold text-red-600 text-lg" x-text="item.location ? `${item.location.aisle}-${item.location.rack}-${item.location.shelf}-${item.location.bin}` : 'N/A'"></p>
                                        <p class="font-mono text-sm">
                                            <span class="text-indigo-700 font-semibold" x-text="item.pallet?.lpn || 'N/A'"></span> / 
                                            <span class="text-blue-600" x-text="item.quality?.name || 'N/A'"></span>
                                        </p>
                                        <p class="text-sm font-medium text-gray-900" x-text="item.product?.name || 'N/A'"></p>
                                        <p class="text-xs text-gray-500 font-mono" x-text="item.product?.sku || 'N/A'"></p>
                                    </div>
                                    {{-- Cantidad Esperada --}}
                                    <div class="text-center md:text-right">
                                        <p class="text-xs text-gray-500">Cantidad:</p>
                                        <p class="text-3xl font-bold text-gray-800" x-text="item.quantity_to_pick"></p>
                                    </div>

                                    {{-- Inputs de Verificación (si no está recogido) --}}
                                    <div class="md:col-span-2 space-y-2" x-show="!item.is_picked">
                                        <div>
                                            <label :for="'loc_'+index" class="text-xs text-gray-600">Scan Ubicación</label>
                                            <input :id="'loc_'+index" type="text" x-model="item.scanned_location_code" @keydown.enter.prevent="focusNext($event.target, 'lpn_'+index)" class="input-scan">
                                        </div>
                                         <div>
                                            <label :for="'lpn_'+index" class="text-xs text-gray-600">Scan LPN</label>
                                            <input :id="'lpn_'+index" type="text" x-model="item.scanned_lpn" @keydown.enter.prevent="focusNext($event.target, 'sku_'+index)" class="input-scan">
                                        </div>
                                        <div>
                                            <label :for="'sku_'+index" class="text-xs text-gray-600">Scan SKU</label>
                                            <input :id="'sku_'+index" type="text" x-model="item.scanned_sku" @keydown.enter.prevent="focusNext($event.target, 'qty_'+index)" class="input-scan">
                                        </div>
                                        <div>
                                            <label :for="'qty_'+index" class="text-xs text-gray-600">Scan Cantidad</label>
                                            <input :id="'qty_'+index" type="number" x-model.number="item.scanned_quantity" @keydown.enter.prevent="confirmItem(index)" class="input-scan">
                                        </div>
                                    </div>

                                    {{-- Indicador de Confirmado --}}
                                    <div class="md:col-span-2 flex items-center justify-center text-green-600" x-show="item.is_picked" x-transition>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="ml-2 font-semibold">Confirmado</span>
                                    </div>
                                </div>

                                {{-- Botón y Mensaje de Error/Éxito por Item --}}
                                <div class="bg-gray-100 p-2 text-right" x-show="!item.is_picked">
                                    <span x-text="item.message" class="text-xs mr-2" :class="item.message_type === 'error' ? 'text-red-600' : 'text-green-600'"></span>
                                    <button @click="confirmItem(index)"
                                            :disabled="item.loading"
                                            class="px-3 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 disabled:opacity-50">
                                        <span x-show="!item.loading">Confirmar Item</span>
                                        <span x-show="item.loading">Procesando...</span>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                     {{-- PASO 3: COMPLETAR PICKING (Solo visible si se seleccionó staging y todos los items están OK) --}}
                    <div class="mt-8 border-t pt-6 text-center" x-show="selectedStagingLocationId && allItemsConfirmed" x-transition>
                        <h3 class="font-bold text-lg text-green-700">¡Todos los items confirmados!</h3>
                        <p class="text-gray-600 my-3 text-sm max-w-md mx-auto">
                            Ahora puedes completar el proceso de picking. La orden pasará a estado "Empacado".
                        </p>
                        <form action="{{ route('wms.picking.complete', $pickList) }}" method="POST" onsubmit="return confirm('¿Completar todo el picking y mover la orden a Empacado?');">
                            @csrf
                            {{-- Envía la ubicación de staging seleccionada --}}
                            <input type="hidden" name="staging_location_id" :value="selectedStagingLocationId">
                            <button type="submit" class="inline-flex items-center px-8 py-3 bg-green-600 text-white font-semibold rounded-lg shadow-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                     <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Completar Picking
                            </button>
                        </form>
                    </div>
                </div>

                 {{-- Mensaje si el picking ya está completado --}}
                @if ($pickList->status == 'Completed')
                 <div class="mt-8 border-t pt-6 text-center text-green-800 font-semibold p-4 bg-green-50 rounded-lg shadow">
                    <p>
                        <i class="fas fa-check-circle mr-2"></i>
                        Este surtido fue confirmado por <strong>{{ \App\Models\User::find($pickList->picker_id)->name ?? 'Usuario Desconocido' }}</strong>
                        el {{ $pickList->picked_at ? $pickList->picked_at->format('d/m/Y H:i') : 'N/A' }}.
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Estilos para inputs de scan --}}
    <style>
        .input-scan {
            display: block; width: 100%; padding: 0.5rem; border: 1px solid #D1D5DB; border-radius: 0.375rem; font-family: monospace; font-size: 0.875rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .input-scan:focus {
             border-color: #6366F1; /* Indigo-500 */
             outline: none;
             box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.5); /* Anillo Indigo */
        }
    </style>

    {{-- Script Alpine.js --}}
    <script>
        function pickingForm(initialItems, csrfToken) {
            return {
                items: initialItems.map(item => ({
                    ...item,
                    scanned_location_code: '',
                    scanned_sku: '',
                    scanned_quantity: item.quantity_to_pick, // Pre-llena cantidad
                    scanned_lpn: '',
                    is_picked: item.is_picked || false, // Asegura que sea booleano
                    loading: false,
                    message: '',
                    message_type: ''
                })),
                selectedStagingLocationId: '',

                // Propiedad computada para saber si todos están confirmados
                get allItemsConfirmed() {
                    return this.items.length > 0 && this.items.every(item => item.is_picked);
                },

                // Función para confirmar un item individual vía Fetch API
                confirmItem(index) {
                    const item = this.items[index];
                    item.loading = true;
                    item.message = '';
                    item.message_type = '';

                    fetch(`/wms/picking/item/${item.id}/confirm`, { // URL de la nueva ruta
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
                            // Si el status es 422 (error de validación/lógica) u otro error
                            item.message = body.message || 'Error desconocido';
                            item.message_type = 'error';
                        }
                    })
                    .catch(error => {
                        item.message = 'Error de conexión o del servidor.';
                        item.message_type = 'error';
                        console.error('Error en Fetch:', error);
                    })
                    .finally(() => {
                        item.loading = false;
                        // Opcional: Enfocar el primer campo del siguiente item no recogido
                        this.$nextTick(() => {
                             const nextUnpickedIndex = this.items.findIndex((itm, idx) => idx > index && !itm.is_picked);
                             if (nextUnpickedIndex !== -1) {
                                const nextInput = document.getElementById('loc_' + nextUnpickedIndex);
                                if (nextInput) {
                                    nextInput.focus();
                                    nextInput.select();
                                }
                             } else {
                                 // Si ya no hay más, quizás enfocar el botón de completar si existe
                                 const completeButton = document.querySelector('form[action*="/complete"] button');
                                 if(completeButton && this.allItemsConfirmed) completeButton.focus();
                             }
                        });
                    });
                },

                // Ayuda para mover el foco con Enter
                focusNext(currentElement, nextElementId) {
                    const nextElement = document.getElementById(nextElementId);
                    if (nextElement) {
                        nextElement.focus();
                        nextElement.select(); // Selecciona el texto para fácil reemplazo
                    } else {
                         // Si es el último campo (cantidad), intenta confirmar
                         const index = currentElement.id.split('_')[1]; // Obtiene el índice del ID
                         this.confirmItem(parseInt(index));
                    }
                }
            }
        }
    </script>
</x-app-layout>