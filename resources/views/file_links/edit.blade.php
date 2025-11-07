<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2c3856] leading-tight">
            {{ __('Editar Elemento') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200">
                <div class="p-6 md:p-8">
                    <h3 class="text-2xl font-bold text-[#2c3856] mb-6">
                        Editando: <span class="text-[#ff9c00]">{{ $fileLink->name }}</span>
                    </h3>
                    
                    <form method="POST" action="{{ route('file_links.update', $fileLink) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Nombre del Elemento') }}</label>
                            <input id="name" class="block mt-1 w-full border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00] rounded-md shadow-sm" type="text" name="name" :value="old('name', $fileLink->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <label for="type_display" class="block text-sm font-medium text-gray-700">{{ __('Tipo de Elemento') }}</label>
                            <input id="type_display" class="block mt-1 w-full bg-gray-100 border-gray-300 rounded-md shadow-sm text-gray-500" type="text" name="type_display" :value="$fileLink->type == 'file' ? 'Archivo Local' : 'Enlace Externo'" readonly />
                        </div>

                        @if ($fileLink->type == 'link')
                            <div class="mt-4">
                                <label for="url" class="block text-sm font-medium text-gray-700">{{ __('URL del Enlace') }}</label>
                                <input id="url" class="block mt-1 w-full border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00] rounded-md shadow-sm" type="url" name="url" :value="old('url', $fileLink->url)" placeholder="https://ejemplo.com/reporte" />
                                <x-input-error :messages="$errors->get('url')" class="mt-2" />
                            </div>
                        @else
                            <div class="mt-4">
                                <label for="path_display" class="block text-sm font-medium text-gray-700">{{ __('Ruta del Archivo') }}</label>
                                <input id="path_display" class="block mt-1 w-full bg-gray-100 border-gray-300 rounded-md shadow-sm text-gray-500" type="text" name="path_display" :value="$fileLink->path ? basename($fileLink->path) : 'N/A'" readonly />
                                <p class="mt-1 text-xs text-gray-500">Para cambiar el archivo físico, debes eliminar este elemento y subir uno nuevo.</p>
                            </div>
                        @endif

                        <div class="flex items-center justify-end space-x-4 mt-6">
                            <a href="{{ route('folders.index', $fileLink->folder) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Cancelar') }}
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-[#2c3856] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#1a2233] focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Actualizar Elemento') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>