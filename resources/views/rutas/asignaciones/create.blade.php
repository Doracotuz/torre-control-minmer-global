<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Añadir Nueva Guía Manualmente') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="guiaFormManager">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <form action="{{ route('rutas.asignaciones.store') }}" method="POST">
                    @csrf

                    @php
                        // Decodificamos la cadena JSON de IDs para poder iterarla como un arreglo PHP
                        $planningIdsArray = json_decode($planning_ids ?? '[]');
                    @endphp

                    @if(is_array($planningIdsArray))
                        @foreach($planningIdsArray as $id)
                            <input type="hidden" name="planning_ids[]" value="{{ $id }}">
                        @endforeach
                    @endif
                    @if ($errors->any())
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                            <p class="font-bold">Hay errores en tu formulario:</p>
                            <ul class="mt-2 list-disc list-inside">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <h3 class="text-lg font-semibold text-[#2c3856] border-b pb-2 mb-6">Datos de la Guía</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="guia" class="block text-sm font-medium text-gray-700">Número de Guía (Único)</label>
                            <div class="relative mt-1">
                                <input type="text" name="guia" id="guia" x-model="guia.numero" required class="block w-full rounded-md border-gray-300 shadow-sm pr-10">
                                <button type="button" @click="generateTempGuia()" title="Generar Guía Temporal" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-blue-600">
                                    <i class="fas fa-magic"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label for="operador" class="block text-sm font-medium text-gray-700">Operador</label>
                            <input type="text" name="operador" id="operador" value="{{ old('operador', 'Pendiente') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label for="placas" class="block text-sm font-medium text-gray-700">Placas</label>
                            <input type="text" name="placas" id="placas" value="{{ old('placas', 'Pendiente') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $guiaData['telefono'] ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>                        
                        <div>
                            <label for="pedimento" class="block text-sm font-medium text-gray-700">Pedimento</label>
                            <input type="text" name="pedimento" id="pedimento" value="{{ old('pedimento') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label for="custodia" class="block text-sm font-medium text-gray-700">Custodia</label>
                            <input type="text" name="custodia" id="custodia" x-model="guia.custodia" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label for="hora_planeada" class="block text-sm font-medium text-gray-700">Hora Planeada</label>
                            <input type="text" name="hora_planeada" id="hora_planeada" x-model="guia.hora_planeada" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label for="origen" class="block text-sm font-medium text-gray-700">Origen</label>
                            <input type="text" name="origen" id="origen" x-model="guia.origen" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label for="fecha_asignacion" class="block text-sm font-medium text-gray-700">Fecha Asignación</label>
                            <input type="date" name="fecha_asignacion" id="fecha_asignacion" x-model="guia.fecha_asignacion" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                    </div>

                    <div class="mt-10">
                        <h3 class="text-lg font-semibold text-[#2c3856] border-b pb-2 mb-6">Facturas de la Guía</h3>
                        <template x-for="(factura, index) in facturas" :key="index">
                             <div class="grid grid-cols-1 md:grid-cols-10 gap-4 items-end bg-gray-50 p-4 rounded-md mb-4 border">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700"># Factura</label>
                                    <input type="text" :name="'facturas[' + index + '][numero_factura]'" x-model="factura.numero_factura" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Destino</label>
                                    <input type="text" :name="'facturas[' + index + '][destino]'" x-model="factura.destino" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">SO</label>
                                    <input type="text" :name="'facturas[' + index + '][so]'" x-model="factura.so" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">F. Entrega</label>
                                    <input type="date" :name="'facturas[' + index + '][fecha_entrega]'" x-model="factura.fecha_entrega" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Hora Cita</label>
                                    <input type="text" :name="'facturas[' + index + '][hora_cita]'" x-model="factura.hora_cita" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Cajas</label>
                                    <input type="number" :name="'facturas[' + index + '][cajas]'" x-model.number="factura.cajas" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Botellas</label>
                                    <input type="number" :name="'facturas[' + index + '][botellas]'" x-model.number="factura.botellas" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                <div class="md:col-span-1">
                                    <button type="button" @click="if (facturas.length > 1) facturas.splice(index, 1)" :disabled="facturas.length <= 1" class="w-full justify-center inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50 text-sm">
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        </template>
                        <button type="button" @click="facturas.push({numero_factura: '', destino: '', cajas: 0, botellas: 0, hora_cita: '', so: '', fecha_entrega: ''})" class="mt-2 text-sm font-semibold text-blue-600 hover:text-blue-800">
                            + Añadir otra factura
                        </button>
                    </div>

                    <div class="flex justify-end gap-4 mt-8">
                        @php
                            $cancel_route = (Auth::user()->area && Auth::user()->area->name == 'Customer Service')
                                ? route('customer-service.planning.index')
                                : route('rutas.asignaciones.index');
                        @endphp

                        <a href="{{ $cancel_route }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancelar</a>
                        <button type="submit" class="px-4 py-2 bg-[#ff9c00] text-white rounded-md hover:bg-orange-600">
                            Guardar Guía
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('guiaFormManager', () => {
            const initialFacturas = {!! !empty($facturasData) ? json_encode($facturasData) : json_encode(old('facturas', [['numero_factura' => '', 'destino' => '', 'cajas' => 0, 'botellas' => 0, 'hora_cita' => '', 'so' => '', 'fecha_entrega' => '']])) !!};

            return {
                guia: {
                    numero: @json(old('guia')),
                    origen: @json(old('origen', $guiaData['origen'] ?? '')),
                    fecha_asignacion: @json(old('fecha_asignacion', $guiaData['fecha_asignacion'] ?? '')),
                    hora_planeada: @json(old('hora_planeada', $guiaData['hora_planeada'] ?? '')),
                    custodia: @json(old('custodia', $guiaData['custodia'] ?? '')),
                },
                facturas: initialFacturas,
                generateTempGuia() {
                    const timestamp = new Date().toISOString().slice(2, 10).replace(/-/g, '');
                    const randomStr = Math.random().toString(36).substring(2, 7).toUpperCase();
                    this.guia.numero = `TEMP-${timestamp}-${randomStr}`;
                }
            }
        });
    });
    </script>
</x-app-layout>