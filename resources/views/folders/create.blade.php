<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2c3856] leading-tight">
            {{ __('Crear Nueva Carpeta') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200">
                <div class="p-6 md:p-8">

                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-[#2c3856]">
                            Crear Nueva Carpeta
                        </h3>
                        @if ($currentFolder)
                            <p class="text-gray-500 text-sm mt-1">
                                Dentro de: <span class="font-medium text-[#ff9c00]">{{ $currentFolder->name }}</span>
                            </p>
                        @else
                             <p class="text-gray-500 text-sm mt-1">
                                Vas a crear una nueva carpeta en el nivel raíz.
                            </p>
                        @endif
                    </div>
                    
                    <form action="{{ route('folders.store') }}" method="POST">
                        @csrf
                        
                        @if ($currentFolder)
                            <input type="hidden" name="parent_id" value="{{ $currentFolder->id }}">
                        @endif

                        @if (!$currentFolder)
                            <div class="mb-4">
                                <label for="area_id" class="block text-sm font-medium text-gray-700">
                                    ¿En qué área deseas crear esta carpeta?
                                </label>
                                <select id="area_id" name="area_id" 
                                        class="mt-1 block w-full border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00] rounded-md shadow-sm">
                                    
                                    @foreach ($manageableAreas as $area)
                                        <option value="{{ $area->id }}" 
                                            {{-- Selecciona el área principal por defecto --}}
                                            @selected($area->id == $user->area_id)> 
                                            {{ $area->name }}
                                        </option>
                                    @endforeach
                                    
                                </select>
                                @error('area_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                Nombre de la Carpeta
                            </label>
                            <input type="text" id="name" name="name" 
                                   class="mt-1 block w-full border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00] rounded-md shadow-sm" 
                                   value="{{ old('name') }}" 
                                   required 
                                   autofocus>
                            @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end space-x-4 mt-6">
                            <a href="{{ $currentFolder ? route('folders.index', $currentFolder) : route('folders.index') }}" 
                               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-[#2c3856] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#1a2233] focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Crear Carpeta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>