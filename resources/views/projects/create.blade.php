<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Crear Nuevo Proyecto
            </h2>
            <a href="{{ route('projects.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md text-sm font-semibold hover:bg-gray-400">
                Cancelar
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">

                @if ($errors->any())
                    <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                        <p class="font-bold">Por favor, corrige los siguientes errores:</p>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('projects.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700">Nombre del Proyecto</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700">Descripción</label>
                            <textarea name="description" id="description" rows="4"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description') }}</textarea>
                        </div>

                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700">Fecha de Inicio</label>
                            <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700">Fecha de Entrega</label>
                            <input type="date" name="due_date" id="due_date" value="{{ old('due_date') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div class="md:col-span-2">
                             <label for="leader_id" class="block text-sm font-medium text-gray-700">Líder del Proyecto</label>
                             <select name="leader_id" id="leader_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">-- Sin asignar --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('leader_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                             </select>
                        </div>
                        <div class="md:col-span-2">
                            <label for="areas" class="block text-sm font-medium text-gray-700">Áreas Involucradas</label>
                            <select name="areas[]" id="areas" multiple class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @foreach($areas as $area)
                                    {{-- Para el formulario de edición, pre-selecciona las áreas ya asociadas --}}
                                    <option value="{{ $area->id }}" @if(isset($project) && $project->areas->contains($area->id)) selected @endif>
                                        {{ $area->name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Mantén presionada la tecla Ctrl (o Cmd en Mac) para seleccionar varias áreas.</p>
                        </div>                        
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="px-5 py-2 bg-[#2c3856] text-white font-semibold rounded-lg shadow-md hover:bg-gray-800 transition-colors">
                            Guardar Proyecto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>