<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edición Masiva para SO: <span class="text-indigo-600">{{ $soNumbers }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <form action="{{ route('customer-service.planning.bulk-update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="ids" value="{{ json_encode($planningIds) }}">

                    <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-700 p-4 mb-8">
                        <p class="font-bold">Instrucciones:</p>
                        <p>Solo los campos que completes se aplicarán a todos los registros seleccionados. Los campos que dejes en blanco no modificarán los datos existentes.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div><label class="block text-sm font-medium text-gray-700">Fecha de Carga</label><input type="date" name="fecha_carga" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700">Hora de Carga</label><input type="time" name="hora_carga" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        
                        <div><label class="block text-sm font-medium text-gray-700">Servicio</label><select name="servicio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="">--</option>@foreach($options['servicio'] as $o) <option value="{{ $o }}">{{ $o }}</option> @endforeach</select></div>
                        <div><label class="block text-sm font-medium text-gray-700">Tipo de Ruta</label><select name="tipo_ruta" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="">--</option>@foreach($options['tipo_ruta'] as $o) <option value="{{ $o }}">{{ $o }}</option> @endforeach</select></div>
                        <div><label class="block text-sm font-medium text-gray-700">Región</label><select name="region" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="">--</option>@foreach($options['region'] as $o) <option value="{{ $o }}">{{ $o }}</option> @endforeach</select></div>
                        
                        <!-- <div><label class="block text-sm font-medium text-gray-700">Transporte</label><input type="text" name="transporte" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700">Operador</label><input type="text" name="operador" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700">Placas</label><input type="text" name="placas" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700">Teléfono</label><input type="text" name="telefono" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div> -->
                        <div><label class="block text-sm font-medium text-gray-700">Capacidad</label><select name="capacidad" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="">--</option>@foreach($options['capacidad'] as $o) <option value="{{ $o }}">{{ $o }}</option> @endforeach</select></div>
                        <div><label class="block text-sm font-medium text-gray-700">Custodia</label><select name="custodia" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="">--</option>@foreach($options['custodia'] as $o) <option value="{{ $o }}">{{ $o }}</option> @endforeach</select></div>

                        <div><label class="block text-sm font-medium text-gray-700">Estado (Destino)</label><select name="estado" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="">--</option>@foreach($options['estado'] as $o) <option value="{{ $o }}">{{ $o }}</option> @endforeach</select></div>
                        <!-- <div><label class="block text-sm font-medium text-gray-700">Estatus Entrega</label><input type="text" name="estatus_de_entrega" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div> -->
                        <div><label class="block text-sm font-medium text-gray-700">¿Devolución?</label><select name="devolucion" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="">--</option>@foreach($options['devolucion'] as $o) <option value="{{ $o }}">{{ $o }}</option> @endforeach</select></div>
                        <div><label class="block text-sm font-medium text-gray-700">¿Urgente?</label><select name="urgente" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="">--</option>@foreach($options['urgente'] as $o) <option value="{{ $o }}">{{ $o }}</option> @endforeach</select></div>
                        <div><strong class="block text-gray-500">Maniobras:</strong><span>{{ $planning->maniobras ?? '0' }}</span></div>
                        <div class="col-span-full"><strong class="block text-gray-500">Observaciones:</strong><p class="mt-1 text-gray-900 bg-gray-50 p-2 rounded">{{ $planning->observaciones ?? 'Sin dato' }}</p></div>
                        </div>
                    
                    <div class="flex justify-end gap-4 mt-8">
                        <a href="{{ route('customer-service.planning.index') }}" class="px-4 py-2 bg-gray-200 rounded-md">Cancelar</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Aplicar Cambios a {{ $planningsCount }} Registros</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>