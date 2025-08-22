<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Registro de Planificación
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm-rounded-lg p-8">
                
                <form action="{{ route('customer-service.planning.update', $planning) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @if ($errors->any())
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert"><p><b>Error:</b> {{ $errors->first() }}</p></div>
                    @endif

                    <h4 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Información Principal</h4>
                    
                    @if(!$planning->order)
                        {{-- MODO EDICIÓN COMPLETA: Para registros manuales --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div><label class="block text-sm font-medium text-gray-700">Razón Social / Contacto</label><input type="text" name="razon_social" value="{{ old('razon_social', $planning->razon_social) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required></div>
                            <div><label class="block text-sm font-medium text-gray-700">Dirección</label><input type="text" name="direccion" value="{{ old('direccion', $planning->direccion) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required></div>
                            <div><label class="block text-sm font-medium text-gray-700">SO (Opcional)</label><input type="text" name="so_number" value="{{ old('so_number', $planning->so_number) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                            <div><label class="block text-sm font-medium text-gray-700">Factura</label><input type="text" name="factura" value="{{ old('factura', $planning->factura) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required></div>
                            <div><label class="block text-sm font-medium text-gray-700">Piezas</label><input type="number" name="pzs" value="{{ old('pzs', $planning->pzs) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                            <div><label class="block text-sm font-medium text-gray-700">Cajas</label><input type="number" name="cajas" value="{{ old('cajas', $planning->cajas) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Origen</label>
                                <select name="origen" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    @foreach($options['origenes'] as $origen)
                                        <option value="{{ $origen }}" {{ old('origen', $planning->origen) == $origen ? 'selected' : '' }}>{{ $origen }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Destino</label>
                                <select name="destino" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    @foreach($options['destinos'] as $destino)
                                        <option value="{{ $destino }}" {{ old('destino', $planning->destino) == $destino ? 'selected' : '' }}>{{ $destino }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div><label class="block text-sm font-medium text-gray-700">Hora Cita</label><input type="text" name="hora_cita" value="{{ old('hora_cita', $planning->hora_cita) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        </div>
                    @else
                        {{-- MODO LECTURA PARCIAL: Para registros vinculados a un pedido --}}
                        <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-800 p-4 mb-8 text-sm">
                            La información principal es gestionada desde el Pedido original y no puede ser modificada aquí.
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 text-sm">
                            <div><strong class="block text-gray-500">Razón Social / Contacto:</strong><span class="mt-1 block">{{ $planning->razon_social }}</span></div>
                            <div><strong class="block text-gray-500">Dirección:</strong><span class="mt-1 block">{{ $planning->direccion }}</span></div>
                            <div><strong class="block text-gray-500">SO:</strong><span class="mt-1 block">{{ $planning->so_number }}</span></div>
                            <div><strong class="block text-gray-500">Factura:</strong><span class="mt-1 block">{{ $planning->factura }}</span></div>
                            <div><strong class="block text-gray-500">Piezas:</strong><span class="mt-1 block">{{ $planning->pzs }}</span></div>
                            <div><strong class="block text-gray-500">Cajas:</strong><span class="mt-1 block">{{ $planning->cajas }}</span></div>
                            <div><strong class="block text-gray-500">Origen:</strong><span class="mt-1 block">{{ $planning->origen }}</span></div>
                            <div><strong class="block text-gray-500">Destino:</strong><span class="mt-1 block">{{ $planning->destino }}</span></div>
                            <div><strong class="block text-gray-500">Hora Cita:</strong><span class="mt-1 block">{{ $planning->hora_cita }}</span></div>
                            <div><strong class="block text-gray-500">Fecha Entrega:</strong><span class="mt-1 block">{{ $planning->order->delivery_date?->format('d/m/Y') ?? 'N/A' }}</span></div>

                        </div>
                        {{-- Campos ocultos para pasar la validación --}}
                        <input type="hidden" name="razon_social" value="{{ $planning->razon_social }}">
                        <input type="hidden" name="direccion" value="{{ $planning->direccion }}">
                        <input type="hidden" name="so_number" value="{{ $planning->so_number }}">
                        <input type="hidden" name="factura" value="{{ $planning->factura }}">
                        <input type="hidden" name="pzs" value="{{ $planning->pzs }}">
                        <input type="hidden" name="cajas" value="{{ $planning->cajas }}">
                        <input type="hidden" name="origen" value="{{ $planning->origen }}">
                        <input type="hidden" name="destino" value="{{ $planning->destino }}">
                        <input type="hidden" name="hora_cita" value="{{ $planning->hora_cita }}">
                    @endif
                    {{-- --- TERMINA LÓGICA CONDICIONAL --- --}}

                    <h4 class="text-lg font-semibold text-gray-800 mt-6 mb-4 border-b pb-2">Información de la Ruta y Transporte (Editable)</h4>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div><label class="block text-sm font-medium text-gray-700">Fecha de Carga</label><input type="date" name="fecha_carga" value="{{ old('fecha_carga', $planning->fecha_carga?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700">Hora de Carga</label><input type="time" name="hora_carga" value="{{ old('hora_carga', $planning->hora_carga) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        
                        <div><label class="block text-sm font-medium text-gray-700">Servicio</label><select name="servicio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">--</option>
                            @foreach($options['servicio'] as $option) <option value="{{ $option }}" @selected(old('servicio', $planning->servicio) == $option)>{{ $option }}</option> @endforeach
                        </select></div>
                        <div><label class="block text-sm font-medium text-gray-700">Tipo de Ruta</label><select name="tipo_ruta" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">--</option>
                            @foreach($options['tipo_ruta'] as $option) <option value="{{ $option }}" @selected(old('tipo_ruta', $planning->tipo_ruta) == $option)>{{ $option }}</option> @endforeach
                        </select></div>
                        <div><label class="block text-sm font-medium text-gray-700">Región</label><select name="region" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">--</option>
                            @foreach($options['region'] as $option) <option value="{{ $option }}" @selected(old('region', $planning->region) == $option)>{{ $option }}</option> @endforeach
                        </select></div>
                        
                        <div><label class="block text-sm font-medium text-gray-700">Transporte</label><input type="text" name="transporte" value="{{ old('transporte', $planning->transporte) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700">Operador</label><input type="text" name="operador" value="{{ old('operador', $planning->operador) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700">Placas</label><input type="text" name="placas" value="{{ old('placas', $planning->placas) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700">Teléfono</label><input type="text" name="telefono" value="{{ old('telefono', $planning->telefono) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700">Capacidad</label><select name="capacidad" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">--</option>
                            @foreach($options['capacidad'] as $option) <option value="{{ $option }}" @selected(old('capacidad', $planning->capacidad) == $option)>{{ $option }}</option> @endforeach
                        </select></div>
                        <div><label class="block text-sm font-medium text-gray-700">Custodia</label><select name="custodia" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">--</option>
                            @foreach($options['custodia'] as $option) <option value="{{ $option }}" @selected(old('custodia', $planning->custodia) == $option)>{{ $option }}</option> @endforeach
                        </select></div>

                        <div><label class="block text-sm font-medium text-gray-700">Estado (Destino)</label><select name="estado" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">--</option>
                            @foreach($options['estado'] as $option) <option value="{{ $option }}" @selected(old('estado', $planning->estado) == $option)>{{ $option }}</option> @endforeach
                        </select></div>
                        <div><label class="block text-sm font-medium text-gray-700">Estatus Entrega</label><input type="text" name="estatus_de_entrega" value="{{ old('estatus_de_entrega', $planning->estatus_de_entrega) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700">¿Devolución?</label><select name="devolucion" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">--</option>
                            @foreach($options['devolucion'] as $option) <option value="{{ $option }}" @selected(old('devolucion', $planning->devolucion) == $option)>{{ $option }}</option> @endforeach
                        </select></div>
                        <div><label class="block text-sm font-medium text-gray-700">¿Urgente?</label><select name="urgente" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">--</option>
                            @foreach($options['urgente'] as $option) <option value="{{ $option }}" @selected(old('urgente', $planning->urgente) == $option)>{{ $option }}</option> @endforeach
                        </select></div>
                    </div>
                    
                    <div class="flex justify-end gap-4 mt-8">
                        <a href="{{ route('customer-service.planning.show', $planning) }}" class="px-4 py-2 bg-gray-200 rounded-md">Cancelar</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>