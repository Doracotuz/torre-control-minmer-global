<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Pedido: <span class="text-blue-600">{{ $order->so_number }}</span></h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                {{-- Se añade x-data y x-init para la lógica de Alpine.js --}}
                <form action="{{ route('customer-service.orders.update', $order) }}" method="POST"
                      x-data="{
                          delivery_date: '{{ old('delivery_date', $order->delivery_date?->format('Y-m-d')) }}',
                          destination_locality: '{{ old('destination_locality', $order->destination_locality) }}',
                          origin_warehouse: '{{ $order->origin_warehouse }}',

                          calculateCutoffDate() {
                              if (!this.delivery_date || !this.destination_locality) return;

                              const deliveryDate = new Date(this.delivery_date + 'T00:00:00');
                              let daysToAdd = (3 - deliveryDate.getDay() + 7) % 7;
                              if (daysToAdd === 0) {
                                  daysToAdd = 7; // Si es miércoles, saltar al siguiente
                              }
                              
                              // Si origen y destino son diferentes, añadir una semana extra
                              if (this.origin_warehouse !== this.destination_locality) {
                                  daysToAdd += 7;
                              }

                              deliveryDate.setDate(deliveryDate.getDate() + daysToAdd);
                              
                              const year = deliveryDate.getFullYear();
                              const month = String(deliveryDate.getMonth() + 1).padStart(2, '0');
                              const day = String(deliveryDate.getDate()).padStart(2, '0');
                              
                              // Actualiza el campo de fecha de corte
                              document.querySelector('[name=evidence_cutoff_date]').value = `${year}-${month}-${day}`;
                          },

                        invoice_number: '{{ old('invoice_number', $order->invoice_number) }}',
                        evidences: {{ $order->evidences->toJson() }},
                        newFile: null,
                        isUploading: false,

                        uploadFile() {
                            if (!this.newFile) return;
                            this.isUploading = true;

                            let formData = new FormData();
                            formData.append('evidence_file', this.newFile);

                            fetch('{{ route('customer-service.orders.evidence.upload', $order) }}', {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                body: formData
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    this.evidences.push(data.evidence);
                                    document.querySelector('[name=evidence_reception_date]').value = data.reception_date;
                                    this.newFile = null;
                                    document.getElementById('evidence_file_input').value = ''; // Limpiar el input
                                    alert(data.message);
                                } else {
                                    alert('Error: ' + data.message);
                                }
                            })
                            .catch(error => alert('Ocurrió un error de red.'))
                            .finally(() => this.isUploading = false);
                        },

                        deleteEvidence(evidenceId, index) {
                            if (!confirm('¿Estás seguro de que deseas eliminar esta evidencia?')) return;

                            fetch(`/customer-service/orders/evidence/${evidenceId}`, {
                                method: 'DELETE',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    this.evidences.splice(index, 1);
                                    alert(data.message);
                                } else {
                                    alert('Error: ' + data.message);
                                }
                            })
                            .catch(error => alert('Ocurrió un error de red.'));
                        }

                      }"
                      x-init="calculateCutoffDate()">

                    @csrf
                    @method('PUT')
                    
                    <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-700 p-4 mb-6">
                        <p class="font-bold">Nota:</p>
                        <p>Esta sección es para añadir información logística y de facturación al pedido. Los datos originales de la carga no se pueden modificar aquí.</p>
                    </div>
                    @if ($errors->any())<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert"><p><b>Error:</b> {{ $errors->first() }}</p></div>@endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label for="bt_oc" class="block text-sm font-medium text-gray-700">Bt de OC</label><input type="text" name="bt_oc" value="{{ old('bt_oc', $order->bt_oc) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div><label for="invoice_number" class="block text-sm font-medium text-gray-700">Factura</label><input type="text" name="invoice_number" x-model="invoice_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div><label for="invoice_date" class="block text-sm font-medium text-gray-700">Fecha Factura</label><input type="date" name="invoice_date" value="{{ old('invoice_date', $order->invoice_date?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        
                        {{-- Campo modificado para activar el cálculo --}}
                        <div>
                            <label for="delivery_date" class="block text-sm font-medium text-gray-700">Fecha de Entrega</label>
                            <input type="date" name="delivery_date" x-model="delivery_date" @change="calculateCutoffDate()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>

                        <div><label for="schedule" class="block text-sm font-medium text-gray-700">Horario</label><input type="text" name="schedule" value="{{ old('schedule', $order->schedule) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div><label for="client_contact" class="block text-sm font-medium text-gray-700">Cliente</label><input type="text" name="client_contact" value="{{ old('client_contact', $order->client_contact) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div class="md:col-span-2"><label for="shipping_address" class="block text-sm font-medium text-gray-700">Dirección de Envío</label><input type="text" name="shipping_address" value="{{ old('shipping_address', $order->shipping_address) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        
                        {{-- Campo modificado para activar el cálculo --}}
                        <div>
                            <label for="destination_locality" class="block text-sm font-medium text-gray-700">Localidad Destino</label>
                            <select name="destination_locality" x-model="destination_locality" @change="calculateCutoffDate()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Selecciona una localidad</option>
                                <option value="AGS" {{ old('destination_locality', $order->destination_locality) == 'AGS' ? 'selected' : '' }}>AGS</option>
                                <option value="BCN" {{ old('destination_locality', $order->destination_locality) == 'BCN' ? 'selected' : '' }}>BCN</option>
                                <option value="CDMX" {{ old('destination_locality', $order->destination_locality) == 'CDMX' ? 'selected' : '' }}>CDMX</option>
                                <option value="CUU" {{ old('destination_locality', $order->destination_locality) == 'CUU' ? 'selected' : '' }}>CUU</option>
                                <option value="COA" {{ old('destination_locality', $order->destination_locality) == 'COA' ? 'selected' : '' }}>COA</option>
                                <option value="CUL" {{ old('destination_locality', $order->destination_locality) == 'CUL' ? 'selected' : '' }}>CUL</option>
                                <option value="CUN" {{ old('destination_locality', $order->destination_locality) == 'CUN' ? 'selected' : '' }}>CUN</option>
                                <option value="CVJ" {{ old('destination_locality', $order->destination_locality) == 'CVJ' ? 'selected' : '' }}>CVJ</option>
                                <option value="GDL" {{ old('destination_locality', $order->destination_locality) == 'GDL' ? 'selected' : '' }}>GDL</option>
                                <option value="GRO" {{ old('destination_locality', $order->destination_locality) == 'GRO' ? 'selected' : '' }}>GRO</option>
                                <option value="GTO" {{ old('destination_locality', $order->destination_locality) == 'GTO' ? 'selected' : '' }}>GTO</option>
                                <option value="HGO" {{ old('destination_locality', $order->destination_locality) == 'HGO' ? 'selected' : '' }}>HGO</option>
                                <option value="MEX" {{ old('destination_locality', $order->destination_locality) == 'MEX' ? 'selected' : '' }}>MEX</option>
                                <option value="MIC" {{ old('destination_locality', $order->destination_locality) == 'MIC' ? 'selected' : '' }}>MIC</option>
                                <option value="MID" {{ old('destination_locality', $order->destination_locality) == 'MID' ? 'selected' : '' }}>MID</option>
                                <option value="MLM" {{ old('destination_locality', $order->destination_locality) == 'MLM' ? 'selected' : '' }}>MLM</option>
                                <option value="MTY" {{ old('destination_locality', $order->destination_locality) == 'MTY' ? 'selected' : '' }}>MTY</option>
                                <option value="MZN" {{ old('destination_locality', $order->destination_locality) == 'MZN' ? 'selected' : '' }}>MZN</option>
                                <option value="NAY" {{ old('destination_locality', $order->destination_locality) == 'NAY' ? 'selected' : '' }}>NAY</option>
                                <option value="DGO" {{ old('destination_locality', $order->destination_locality) == 'DGO' ? 'selected' : '' }}>DGO</option>
                                <option value="ZAC" {{ old('destination_locality', $order->destination_locality) == 'ZAC' ? 'selected' : '' }}>ZAC</option>
                                <option value="OAX" {{ old('destination_locality', $order->destination_locality) == 'OAX' ? 'selected' : '' }}>OAX</option>
                                <option value="PUE" {{ old('destination_locality', $order->destination_locality) == 'PUE' ? 'selected' : '' }}>PUE</option>
                                <option value="QRO" {{ old('destination_locality', $order->destination_locality) == 'QRO' ? 'selected' : '' }}>QRO</option>
                                <option value="SIN" {{ old('destination_locality', $order->destination_locality) == 'SIN' ? 'selected' : '' }}>SIN</option>
                                <option value="SJD" {{ old('destination_locality', $order->destination_locality) == 'SJD' ? 'selected' : '' }}>SJD</option>
                                <option value="SLP" {{ old('destination_locality', $order->destination_locality) == 'SLP' ? 'selected' : '' }}>SLP</option>
                                <option value="SMA" {{ old('destination_locality', $order->destination_locality) == 'SMA' ? 'selected' : '' }}>SMA</option>
                                <option value="SON" {{ old('destination_locality', $order->destination_locality) == 'SON' ? 'selected' : '' }}>SON</option>
                                <option value="TAB" {{ old('destination_locality', $order->destination_locality) == 'TAB' ? 'selected' : '' }}>TAB</option>
                                <option value="TGZ" {{ old('destination_locality', $order->destination_locality) == 'TGZ' ? 'selected' : '' }}>TGZ</option>
                                <option value="TIJ" {{ old('destination_locality', $order->destination_locality) == 'TIJ' ? 'selected' : '' }}>TIJ</option>
                                <option value="TLX" {{ old('destination_locality', $order->destination_locality) == 'TLX' ? 'selected' : '' }}>TLX</option>
                                <option value="VER" {{ old('destination_locality', $order->destination_locality) == 'VER' ? 'selected' : '' }}>VER</option>
                                <option value="YUC" {{ old('destination_locality', $order->destination_locality) == 'YUC' ? 'selected' : '' }}>YUC</option>
                                <option value="ZAM" {{ old('destination_locality', $order->destination_locality) == 'ZAM' ? 'selected' : '' }}>ZAM</option>
                            </select>
                        </div>

                        <div><label for="executive" class="block text-sm font-medium text-gray-700">Ejecutivo</label><input type="text" name="executive" value="{{ old('executive', $order->executive) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div><label for="evidence_reception_date" class="block text-sm font-medium text-gray-700">Recepción de Evidencia</label><input type="date" name="evidence_reception_date" value="{{ old('evidence_reception_date', $order->evidence_reception_date?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div>
                            <label for="is_oversized" class="block text-sm font-medium text-gray-700">Sobredimensionado</label>
                            <select name="is_oversized" id="is_oversized" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="0" {{ old('is_oversized', $order->is_oversized) == false ? 'selected' : '' }}>No</option>
                                <option value="1" {{ old('is_oversized', $order->is_oversized) == true ? 'selected' : '' }}>Sí</option>
                            </select>
                        </div>                        
                        {{-- Campo de Fecha de Corte ahora es de solo lectura para evitar modificación manual --}}
                        <div>
                            <label for="evidence_cutoff_date" class="block text-sm font-medium text-gray-700">Corte de Evidencias</label>
                            <input type="date" name="evidence_cutoff_date" value="{{ old('evidence_cutoff_date', $order->evidence_cutoff_date?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100" readonly>
                        </div>
                        
                        <div class="md:col-span-2"><label for="observations" class="block text-sm font-medium text-gray-700">Observaciones</label><textarea name="observations" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('observations', $order->observations) }}</textarea></div>
                    </div>

                    <div class="md:col-span-2 mt-6 border-t pt-6">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Evidencias de Entrega</h4>

                        <div class="space-y-2 mb-4">
                            <template x-if="evidences.length === 0">
                                <p class="text-sm text-gray-500">No hay evidencias adjuntas.</p>
                            </template>
                            <template x-for="(evidence, index) in evidences" :key="evidence.id">
                                <div class="flex items-center justify-between bg-gray-50 p-2 rounded-md border">
                                    <a :href="`/storage/${evidence.file_path}`" target="_blank" class="text-blue-600 hover:underline text-sm" x-text="evidence.file_name"></a>
                                    <button @click.prevent="deleteEvidence(evidence.id, index)" class="text-red-500 hover:text-red-700 text-xs font-bold">ELIMINAR</button>
                                </div>
                            </template>
                        </div>

                        <div>
                            <label for="evidence_file_input" class="block text-sm font-medium text-gray-700">Adjuntar nueva evidencia</label>
                            <div class="mt-1 flex items-center gap-2">
                                <input id="evidence_file_input" type="file" @change="newFile = $event.target.files[0]" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                <button @click.prevent="uploadFile()" 
                                        :disabled="!invoice_number || ['N/A', 'Sin dato', ''].includes(invoice_number.trim()) || !newFile || isUploading"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed whitespace-nowrap"
                                        x-text="isUploading ? 'Subiendo...' : 'Subir Archivo'">
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Archivos permitidos: PDF, JPG, PNG, XML. Max: 10MB.</p>
                            <template x-if="!invoice_number || ['N/A', 'Sin dato', ''].includes(invoice_number.trim())">
                                <p class="text-xs text-red-600 mt-1">**Nota:** Debes introducir un número de factura para poder subir una evidencia.</p>
                            </template>
                        </div>
                    </div>                    

                    <h4 class="text-lg font-semibold text-gray-800 mt-8 mb-4">Detalles de SKUs</h4>
                    <div class="max-h-70 overflow-y-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 sticky top-0 z-50">
                                <tr>
                                    <th class="px-4 py-2 text-left">SKU</th>
                                    <th class="px-4 py-2 text-left">Descripción</th>
                                    <th class="px-4 py-2 text-left">Cantidad Pedida</th>
                                    <th class="px-4 py-2 text-left">Cantidad Enviada</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($order->details as $index => $detail)
                                    {{-- El x-data ahora se mueve a cada fila para gestionar su propia búsqueda --}}
                                    <tr x-data="{ 
                                        open: false, 
                                        query: '{{ old('details.' . $index . '.sku', $detail->sku) }}', 
                                        selected: '{{ old('details.' . $index . '.sku', $detail->sku) }}',
                                        results: [],
                                        search() {
                                            if (this.query.length < 2) { this.results = []; return; }
                                            fetch(`{{ route('customer-service.products.search') }}?term=${this.query}`)
                                                .then(response => response.json())
                                                .then(data => this.results = data);
                                        }
                                    }">
                                        <td class="px-4 py-2">
                                            <div @click.away="open = false" class="relative">
                                                <input type="hidden" name="details[{{ $index }}][sku]" x-model="selected" required>
                                                <input type="text" x-model="query" @focus="open = true" @input.debounce.300ms="search()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Buscar SKU..." required>
                                                <ul x-show="open" class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-48 overflow-y-auto">
                                                    {{-- El template ahora itera sobre los resultados de la búsqueda --}}
                                                    <template x-for="product in results" :key="product.id">
                                                        <li @click="selected = product.sku; query = product.sku; open = false" class="px-4 py-2 cursor-pointer hover:bg-gray-100">
                                                            <span x-text="product.sku"></span> - <span x-text="product.description"></span>
                                                        </li>
                                                    </template>
                                                    <template x-if="results.length === 0 && query.length > 1">
                                                        <li class="px-4 py-2 text-gray-500">No se encontraron resultados.</li>
                                                    </template>
                                                </ul>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2">{{ $detail->product->description ?? 'N/A' }}</td>
                                        <td class="px-4 py-2">{{ $detail->quantity }}</td>
                                        <td class="px-4 py-2">
                                            <input type="number" name="details[{{ $index }}][sent]" value="{{ old('details.' . $index . '.sent', ($detail->sent > 0) ? $detail->sent : $detail->quantity) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">                                            <input type="hidden" name="details[{{ $index }}][id]" value="{{ $detail->id }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end gap-4 mt-8">
                        <a href="{{ route('customer-service.orders.show', $order) }}" class="px-4 py-2 bg-gray-200 rounded-md">Cancelar</a>
                        <a href="{{ route('customer-service.orders.edit-original', $order) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">Editar Datos Originales</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Guardar Cambios</button>
                    
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>