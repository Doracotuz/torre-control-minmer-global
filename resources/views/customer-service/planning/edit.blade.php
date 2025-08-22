<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Planificación: <span class="text-blue-600">{{ $planning->so_number }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <form action="{{ route('customer-service.planning.update', $planning) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    @if ($errors->any())
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                            <p><b>Error:</b> {{ $errors->first() }}</p>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div><label class="block text-sm font-medium text-gray-700">Fecha de Carga</label><input type="date" name="fecha_carga" value="{{ old('fecha_carga', $planning->fecha_carga?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700">Hora de Carga</label><input type="time" name="hora_carga" value="{{ old('hora_carga', $planning->hora_carga) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700">Hora Cita</label><input type="text" name="hora_cita" value="{{ old('hora_cita', $planning->hora_cita) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        
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