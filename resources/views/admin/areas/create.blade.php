<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Nueva Área') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8"> {{-- Ancho completo y padding responsivo --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 p-4 sm:p-8"> {{-- Ajuste de padding --}}
                <form method="POST" action="{{ route('admin.areas.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4"> {{-- Añadido mb-4 --}}
                        <x-input-label for="name" :value="__('Nombre del Área')" />
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mb-4"> {{-- Añadido mb-4 --}}
                        <x-input-label for="description" :value="__('Descripción (Opcional)')" />
                        <textarea id="description" name="description" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="mb-4"> {{-- Añadido mb-4 --}}
                        <x-input-label for="icon" :value="__('Icono del Área (Opcional)')" />
                        <input id="icon" name="icon" type="file" class="block mt-1 w-full text-sm sm:text-base text-gray-500
                               file:mr-2 sm:file:mr-4 file:py-1.5 sm:file:py-2 file:px-3 sm:file:px-4
                               file:rounded-md file:border-0
                               file:text-sm file:font-semibold
                               file:bg-indigo-50 file:text-indigo-700
                               hover:file:bg-indigo-100 transition-colors duration-150" />
                        <p class="mt-1 text-xs text-gray-500">{{ __('Formatos permitidos: JPG, PNG, GIF, SVG. Máx: 2MB.') }}</p>
                        <x-input-error :messages="$errors->get('icon')" class="mt-2" />
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-end mt-4 space-y-3 sm:space-y-0 sm:space-x-3"> {{-- Flexbox responsivo para botones --}}
                        <a href="{{ route('admin.areas.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 border border-transparent rounded-full font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm w-full sm:w-auto">
                            {{ __('Cancelar') }}
                        </a>
                        <x-primary-button class="bg-[#2c3856] hover:bg-[#ff9c00] focus:bg-[#ff9c00] active:bg-[#a06d00] focus:ring-[#2c3856] shadow-md w-full sm:w-auto justify-center">
                            {{ __('Crear Área') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>