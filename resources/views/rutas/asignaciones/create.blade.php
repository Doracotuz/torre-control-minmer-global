<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Añadir Nueva Guía Manualmente') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <form action="{{ route('rutas.asignaciones.store') }}" method="POST">
                    @csrf
                    
                    {{-- Mostrar errores de validación --}}
                    @if ($errors->any())
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                            <p class="font-bold">Hay errores en tu formulario:</p>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <h3 class="text-lg font-semibold text-[#2c3856] border-b pb-2 mb-6">Datos de la Guía</h3>
                    {{-- SECCIÓN DE GUÍA ACTUALIZADA --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="guia" class="block text-sm font-medium text-gray-700">Número de Guía (Único)</label>
                            <input type="text" name="guia" id="guia" value="{{ old('guia') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label for="operador" class="block text-sm font-medium text-gray-700">Operador</label>
                            <input type="text" name="operador" id="operador" value="{{ old('operador') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label for="placas" class="block text-sm font-medium text-gray-700">Placas</label>
                            <input type="text" name="placas" id="placas" value="{{ old('placas') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label for="pedimento" class="block text-sm font-medium text-gray-700">Pedimento (Opcional)</label>
                            <input type="text" name="pedimento" id="pedimento" value="{{ old('pedimento') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label for="custodia" class="block text-sm font-medium text-gray-700">Custodia (Opcional)</label>
                            <input type="text" name="custodia" id="custodia" value="{{ old('custodia') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label for="hora_planeada" class="block text-sm font-medium text-gray-700">Hora Planeada (Opcional)</label>
                            <input type="text" name="hora_planeada" id="hora_planeada" value="{{ old('hora_planeada') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                         <div>
                            <label for="origen" class="block text-sm font-medium text-gray-700">Origen (3 caracteres)</label>
                            <select name="origen" id="origen" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Selecciona un origen</option>
                            <option value="MEX" {{ old('origen') == 'MEX' ? 'selected' : '' }}>MEX</option>
                            <option value="SJD" {{ old('origen') == 'SJD' ? 'selected' : '' }}>SJD</option>
                            <option value="GDL" {{ old('origen') == 'GDL' ? 'selected' : '' }}>GDL</option>
                            <option value="MTY" {{ old('origen') == 'MTY' ? 'selected' : '' }}>MTY</option>
                            <option value="CUN" {{ old('origen') == 'CUN' ? 'selected' : '' }}>CUN</option>
                            <option value="MIN" {{ old('origen') == 'MIN' ? 'selected' : '' }}>MIN</option>
                            <option value="MZN" {{ old('origen') == 'MZN' ? 'selected' : '' }}>MZN</option>
                            <option value="VER" {{ old('origen') == 'VER' ? 'selected' : '' }}>VER</option>
                        </select>
                        </div>
                        <div>
                            <label for="fecha_asignacion" class="block text-sm font-medium text-gray-700">Fecha Asignación</label>
                            <input type="date" name="fecha_asignacion" id="fecha_asignacion" value="{{ old('fecha_asignacion') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                    </div>

                    {{-- SECCIÓN DE FACTURAS ACTUALIZADA --}}
                    <div class="mt-10" x-data="{ facturas: {{ json_encode(old('facturas', [['numero_factura' => '', 'destino' => '', 'cajas' => 0, 'botellas' => 0, 'hora_cita' => '', 'so' => '', 'fecha_entrega' => '']])) }} }">
                        <h3 class="text-lg font-semibold text-[#2c3856] border-b pb-2 mb-6">Facturas de la Guía</h3>
                        
                        <template x-for="(factura, index) in facturas" :key="index">
                            {{-- Se ajusta el grid a 10 columnas para todos los campos --}}
                            <div class="grid grid-cols-1 md:grid-cols-10 gap-4 items-end bg-gray-50 p-4 rounded-md mb-4 border">
                                <div class="md:col-span-2">
                                    <label :for="'factura_num_' + index" class="block text-sm font-medium text-gray-700"># Factura</label>
                                    <input type="text" :name="'facturas[' + index + '][numero_factura]'" :id="'factura_num_' + index" x-model="factura.numero_factura" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                <div class="md:col-span-2">
                                    <label :for="'factura_dest_' + index" class="block text-sm font-medium text-gray-700">Destino</label>
                                    <input type="text" :name="'facturas[' + index + '][destino]'" :id="'factura_dest_' + index" x-model="factura.destino" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                <div>
                                    <label :for="'factura_so_' + index" class="block text-sm font-medium text-gray-700">SO</label>
                                    <input type="number" :name="'facturas[' + index + '][so]'" :id="'factura_so_' + index" x-model.number="factura.so" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                <div>
                                    <label :for="'factura_fecha_entrega_' + index" class="block text-sm font-medium text-gray-700">F. Entrega</label>
                                    <input type="date" :name="'facturas[' + index + '][fecha_entrega]'" :id="'factura_fecha_entrega_' + index" x-model="factura.fecha_entrega" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                {{-- INICIO DE CAMPOS REINTEGRADOS --}}
                                <div>
                                    <label :for="'factura_hora_cita_' + index" class="block text-sm font-medium text-gray-700">Hora Cita</label>
                                    <input type="text" :name="'facturas[' + index + '][hora_cita]'" :id="'factura_hora_cita_' + index" x-model="factura.hora_cita" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                <div>
                                    <label :for="'factura_cajas_' + index" class="block text-sm font-medium text-gray-700">Cajas</label>
                                    <input type="number" :name="'facturas[' + index + '][cajas]'" :id="'factura_cajas_' + index" x-model.number="factura.cajas" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                <div>
                                    <label :for="'factura_botellas_' + index" class="block text-sm font-medium text-gray-700">Botellas</label>
                                    <input type="number" :name="'facturas[' + index + '][botellas]'" :id="'factura_botellas_' + index" x-model.number="factura.botellas" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                {{-- FIN DE CAMPOS REINTEGRADOS --}}
                                <div class="md:col-span-1">
                                    <button type="button" @click="if (facturas.length > 1) facturas.splice(index, 1)" class="w-full justify-center inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50 text-sm" :disabled="facturas.length <= 1">
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
                        <a href="{{ route('rutas.asignaciones.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancelar</a>
                        <button type="submit" class="px-4 py-2 bg-[#ff9c00] text-white rounded-md hover:bg-orange-600">
                            Guardar Guía
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
