<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Carpeta') }}
            <span class="text-gray-500"> / {{ $folder->name }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('folders.update', $folder) }}">
                        @csrf
                        @method('PUT')

                        <!-- Nombre de la Carpeta -->
                        <div>
                            <x-input-label for="name" :value="__('Nombre de la Carpeta')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $folder->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Información del Área (solo lectura) -->
                        <div class="mt-4">
                            <x-input-label for="area_name" :value="__('Área de la Carpeta')" />
                            <x-text-input id="area_name" class="block mt-1 w-full bg-gray-100" type="text" name="area_name" :value="$userArea->name" readonly />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ $folder->parent_id ? route('folders.show', $folder->parent_id) : route('folders.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-4">
                                {{ __('Cancelar') }}
                            </a>
                            <x-primary-button>
                                {{ __('Actualizar Carpeta') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>