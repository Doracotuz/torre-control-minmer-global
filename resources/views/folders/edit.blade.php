<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2c3856] leading-tight">
            {{ __('Editar Carpeta') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-[#E8ECF7]">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200">
                <div class="p-6 md:p-8">
                    <h3 class="text-2xl font-bold text-[#2c3856] mb-6">
                        Editando: <span class="text-[#ff9c00]">{{ $folder->name }}</span>
                    </h3>

                    <form method="POST" action="{{ route('folders.update', $folder) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                Nombre de la Carpeta
                            </label>
                            <input type="text" id="name" name="name" 
                                   class="mt-1 block w-full border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00] rounded-md shadow-sm" 
                                   value="{{ old('name', $folder->name) }}" 
                                   required 
                                   autofocus>
                            @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="area_name" class="block text-sm font-medium text-gray-700">
                                Área de la Carpeta
                            </label>
                            <input type="text" id="area_name" name="area_name" 
                                   class="mt-1 block w-full bg-gray-100 border-gray-300 rounded-md shadow-sm text-gray-500" 
                                   value="{{ $folder->area->name ?? 'N/A' }}" 
                                   readonly>
                            <p class="mt-1 text-xs text-gray-500">El área de una carpeta no se puede cambiar después de su creación.</p>
                        </div>

                        <div class="flex items-center justify-end space-x-4 mt-6">
                            <a href="{{ $folder->parent_id ? route('folders.index', $folder->parent_id) : route('folders.index') }}" 
                               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-[#2c3856] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#1a2233] focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Actualizar Carpeta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>