<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Crear Nueva Sesión de Conteo</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                
                <form action="{{ route('wms.physical-counts.store') }}" method="POST" enctype="multipart/form-data" x-data="{ countType: '{{ old('type', 'cycle') }}' }">
                    @csrf
                    @if (session('error'))<div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert"><p>{{ session('error') }}</p></div>@endif
                    @if ($errors->any())<div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert"><ul class="list-disc list-inside">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

                    <div class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nombre de la Sesión</label>
                            <input type="text" name="name" id="name" value="{{ old('name', 'Conteo ' . now()->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>

                        <div>
                            <label for="warehouse_id" class="block text-sm font-medium text-gray-700">Almacén de Conteo <span class="text-red-500">*</span></label>
                            <select name="warehouse_id" id="warehouse_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Seleccione un almacén...</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700">Tipo de Conteo <span class="text-red-500">*</span></label>
                            <select name="type" id="type" x-model="countType" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="cycle">Cíclico (Por Zona/Pasillo)</option>
                                <option value="full">Completo (Wall-to-Wall)</option>
                                <option value="dirigido">Dirigido (Por CSV)</option>
                            </select>
                        </div>

                        <div x-show="countType === 'cycle'" x-transition
                             class="p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                            <label for="aisle" class="block text-sm font-medium text-gray-700">
                                Seleccionar Pasillo a Contar <span class="text-red-500">*</span>
                            </label>
                            <select name="aisle" id="aisle" :required="countType === 'cycle'" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Seleccione un pasillo...</option>
                                @foreach($aisles as $aisle)
                                    <option value="{{ $aisle }}" @selected(old('aisle') == $aisle)>
                                        Pasillo {{ $aisle }}
                                    </option>
                                @endforeach
                            </select>
                            @error('aisle') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div x-show="countType === 'dirigido'" x-transition 
                             class="p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                            <label for="locations_file" class="block text-sm font-medium text-gray-700">
                                Archivo de Ubicaciones (.csv) <span class="text-red-500">*</span>
                            </label>
                            <input type="file" name="locations_file" id="locations_file" :required="countType === 'dirigido'" accept=".csv, .txt" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-white">
                            <a href="{{ route('wms.physical-counts.template') }}" class="text-xs text-indigo-600 font-semibold hover:underline mt-2 inline-block"><i class="fas fa-download mr-1"></i> Descargar plantilla</a>
                            @error('locations_file') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="assigned_user_id" class="block text-sm font-medium text-gray-700">Asignar a Usuario <span class="text-red-500">*</span></label>
                            <select name="assigned_user_id" id="assigned_user_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Seleccione un usuario...</option>
                                @foreach($users as $user)<option value="{{ $user->id }}" @selected(old('assigned_user_id') == $user->id)>{{ $user->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end pt-5 border-t">
                        <a href="{{ route('wms.physical-counts.index') }}" class="px-5 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg shadow-sm hover:bg-gray-300 mr-4">Cancelar</a>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700">
                            Crear y Generar Tareas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>