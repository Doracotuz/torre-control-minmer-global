<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Elemento') }}
            <span class="text-gray-500"> / {{ $fileLink->name }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('file_links.update', $fileLink) }}">
                        @csrf
                        @method('PUT')

                        <!-- Nombre del Elemento -->
                        <div>
                            <x-input-label for="name" :value="__('Nombre del Elemento')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $fileLink->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Tipo de Elemento (solo lectura) -->
                        <div class="mt-4">
                            <x-input-label for="type_display" :value="__('Tipo de Elemento')" />
                            <x-text-input id="type_display" class="block mt-1 w-full bg-gray-100" type="text" name="type_display" :value="$fileLink->type == 'file' ? 'Archivo Local' : 'Enlace Externo'" readonly />
                        </div>

                        <!-- Campo para URL (solo editable si es un enlace) -->
                        @if ($fileLink->type == 'link')
                            <div class="mt-4">
                                <x-input-label for="url" :value="__('URL del Enlace')" />
                                <x-text-input id="url" class="block mt-1 w-full" type="url" name="url" :value="old('url', $fileLink->url)" placeholder="https://ejemplo.com/reporte" />
                                <x-input-error :messages="$errors->get('url')" class="mt-2" />
                            </div>
                        @else
                            <!-- Mostrar la ruta del archivo si es un archivo (no editable) -->
                            <div class="mt-4">
                                <x-input-label for="path_display" :value="__('Ruta del Archivo')" />
                                <x-text-input id="path_display" class="block mt-1 w-full bg-gray-100" type="text" name="path_display" :value="$fileLink->path ? basename($fileLink->path) : 'N/A'" readonly />
                                <p class="mt-1 text-sm text-gray-500">Para cambiar el archivo, elimine y cree uno nuevo.</p>
                            </div>
                        @endif

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('folders.show', $fileLink->folder) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-4">
                                {{ __('Cancelar') }}
                            </a>
                            <x-primary-button>
                                {{ __('Actualizar Elemento') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>