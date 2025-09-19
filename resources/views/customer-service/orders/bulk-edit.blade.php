<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edición Masiva de Órdenes:
            <span class="text-indigo-600">
                @if($ordersCount > 5)
                    {{ $ordersCount }} registros seleccionados
                @else
                    {{ implode(', ', $soNumbers) }}
                @endif
            </span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                
                {{-- --- INICIA MODIFICACIÓN: Se añade Alpine.js al formulario --- --}}
                <form action="{{ route('customer-service.orders.bulk-update') }}" method="POST"
                      x-data="{
                          delivery_date: '',
                          destination_locality: '',
                          first_order_origin: '{{ $firstOrderOrigin }}',
                          
                          calculateCutoffDate() {
                              // Ahora la función necesita tanto la fecha como el destino para ejecutarse
                              if (!this.delivery_date || !this.destination_locality) {
                                  document.querySelector('[name=evidence_cutoff_date]').value = '';
                                  return;
                              }

                              const deliveryDate = new Date(this.delivery_date + 'T00:00:00');
                              
                              let daysToAdd = (3 - deliveryDate.getDay() + 7) % 7;
                              if (daysToAdd === 0) {
                                  daysToAdd = 7;
                              }
                              
                              // SE REINTEGRA LA LÓGICA DE COMPARACIÓN
                              // Compara el destino seleccionado con el origen de la PRIMERA orden.
                              if (this.first_order_origin !== this.destination_locality) {
                                  daysToAdd += 7;
                              }
                              
                              deliveryDate.setDate(deliveryDate.getDate() + daysToAdd);
                              
                              const year = deliveryDate.getFullYear();
                              const month = String(deliveryDate.getMonth() + 1).padStart(2, '0');
                              const day = String(deliveryDate.getDate()).padStart(2, '0');
                              
                              document.querySelector('[name=evidence_cutoff_date]').value = `${year}-${month}-${day}`;
                          }
                      }">
                {{-- --- TERMINA MODIFICACIÓN --- --}}

                    @csrf
                    <input type="hidden" name="ids" value="{{ json_encode($orders->pluck('id')) }}">

                    <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-700 p-4 mb-8">
                        <p class="font-bold">Instrucciones:</p>
                        <p>Solo los campos que completes se aplicarán a todas las órdenes seleccionadas. Los campos que dejes en blanco no modificarán los datos existentes.</p>
                    </div>

                    <div class="mb-8 p-4 border rounded-md bg-gray-50">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Datos Individuales por SO</h3>
                        <div class="space-y-4">
                            @foreach ($orders as $order)
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                                    <div class="font-semibold text-gray-700">SO: {{ $order->so_number }}</div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">BT OC</label>
                                        <input type="text" name="invoices[{{ $order->id }}][bt_oc]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Factura</label>
                                        <input type="text" name="invoices[{{ $order->id }}][invoice_number]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Fecha Factura</label>
                                        <input type="date" name="invoices[{{ $order->id }}][invoice_date]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-t pt-6">Campos Generales (se aplican a todos)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- --- INICIA MODIFICACIÓN: Conectar campos a Alpine.js --- --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fecha de Entrega</label>
                            <input type="date" name="delivery_date" 
                                   x-model="delivery_date" 
                                   @change="calculateCutoffDate()" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div><label class="block text-sm font-medium text-gray-700">Horario</label><input type="text" name="schedule" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700">Dirección de Envío</label><input type="text" name="shipping_address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Localidad Destino</label>
                            <select name="destination_locality" 
                                    x-model="destination_locality" 
                                    @change="calculateCutoffDate()" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Selecciona una localidad</option>
                                <option value="AGS">AGS</option>
                                <option value="BCN">BCN</option>
                                <option value="CDMX">CDMX</option>
                                <option value="CUU">CUU</option>
                                <option value="COA">COA</option>
                                <option value="CUL">CUL</option>
                                <option value="CUN">CUN</option>
                                <option value="CVJ">CVJ</option>
                                <option value="GDL">GDL</option>
                                <option value="GRO">GRO</option>
                                <option value="GTO">GTO</option>
                                <option value="HGO">HGO</option>
                                <option value="MEX">MEX</option>
                                <option value="MIC">MIC</option>
                                <option value="MID">MID</option>
                                <option value="MLM">MLM</option>
                                <option value="MTY">MTY</option>
                                <option value="MZN">MZN</option>
                                <option value="NAY">NAY</option>
                                <option value="DGO">DGO</option>
                                <option value="ZAC">ZAC</option>
                                <option value="OAX">OAX</option>
                                <option value="PUE">PUE</option>
                                <option value="QRO">QRO</option>
                                <option value="SIN">SIN</option>
                                <option value="SJD">SJD</option>
                                <option value="SLP">SLP</option>
                                <option value="SMA">SMA</option>
                                <option value="SON">SON</option>
                                <option value="TAB">TAB</option>
                                <option value="TGZ">TGZ</option>
                                <option value="TIJ">TIJ</option>
                                <option value="TLX">TLX</option>
                                <option value="VER">VER</option>
                                <option value="YUC">YUC</option>
                                <option value="ZAM">ZAM</option>
                            </select>
                        </div>
                        <div><label class="block text-sm font-medium text-gray-700">Contacto Cliente</label><input type="text" name="client_contact" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700">Ejecutivo</label><input type="text" name="executive" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Sobredimensionado</label>
                            <select name="is_oversized" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">No Cambiar</option>
                                <option value="0">No</option>
                                <option value="1">Sí</option>
                            </select>
                        </div>                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Recepción de Evidencia</label>
                            <input type="date" name="evidence_reception_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Corte de Evidencias</label>
                            <input type="date" name="evidence_cutoff_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100" readonly>
                        </div>

                    </div>
                    
                    <div class="flex justify-end gap-4 mt-8">
                        <a href="{{ route('customer-service.orders.index') }}" class="px-4 py-2 bg-gray-200 rounded-md">Cancelar</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Aplicar Cambios a {{ $ordersCount }} Órdenes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>