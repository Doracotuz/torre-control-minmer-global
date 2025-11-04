<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Crear Nueva Sesión de Conteo</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <form action="{{ route('wms.physical-counts.store') }}" method="POST" enctype="multipart/form-data" x-data="{ countType: 'cycle' }">
                    @csrf
                    @if (session('error'))<div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert"><p>{{ session('error') }}</p></div>@endif
                    @if ($errors->any())<div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert"><ul class="list-disc list-inside">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

                    <div class="space-y-4">
                        <div><label for="name" class="block text-sm font-medium text-gray-700">Nombre de la Sesión</label><input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-gray-300"></div>
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700">Tipo de Conteo</label>
                            <select name="type" id="type" x-model="countType" required class="mt-1 block w-full rounded-md border-gray-300">
                                <option value="cycle">Cíclico (Todo el inventario)</option>
                                <option value="full">Completo (Wall-to-Wall)</option>
                                <option value="dirigido">Dirigido (por CSV)</option>
                            </select>
                        </div>

                        <div>
                            <label for="warehouse_id" class="block text-sm font-medium text-gray-700">Almacén de Conteo</label>
                            <select name="warehouse_id" id="warehouse_id" required class="mt-1 block w-full rounded-md border-gray-300">
                                <option value="">Seleccione un almacén...</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>                        

                        <div x-show="countType === 'dirigido'" x-transition class="p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                            <label for="locations_file" class="block text-sm font-medium text-gray-700">Archivo de Ubicaciones (.csv)</label>
                            <input type="file" name="locations_file" id="locations_file" accept=".csv, .txt" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-white">
                            <a href="{{ route('wms.physical-counts.template') }}" class="text-xs text-indigo-600 font-semibold hover:underline mt-2 inline-block"><i class="fas fa-download mr-1"></i> Descargar plantilla</a>
                        </div>

                        <div>
                            <label for="assigned_user_id" class="block text-sm font-medium text-gray-700">Asignar a Usuario</label>
                            <select name="assigned_user_id" id="assigned_user_id" required class="mt-1 block w-full rounded-md border-gray-300">
                                <option value="">Seleccione un usuario...</option>
                                @foreach($users as $user)<option value="{{ $user->id }}" @selected(old('assigned_user_id') == $user->id)>{{ $user->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('wms.physical-counts.index') }}" class="px-4 py-2 bg-gray-200 rounded-md mr-4">Cancelar</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Crear y Generar Tareas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>