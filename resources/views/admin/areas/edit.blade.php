<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Área') }}
            <span class="text-gray-500"> / {{ $area->name }}</span>
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 p-4 sm:p-8">
                <form method="POST" action="{{ route('admin.areas.update', $area) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <x-input-label for="name" :value="__('Nombre del Área')" />
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $area->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="description" :value="__('Descripción (Opcional)')" />
                        <textarea id="description" name="description" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $area->description) }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="icon" :value="__('Icono del Área (Opcional)')" />
                        <input id="icon" name="icon" type="file" class="block mt-1 w-full text-sm sm:text-base text-gray-500
                               file:mr-2 sm:file:mr-4 file:py-1.5 sm:file:py-2 file:px-3 sm:file:px-4
                               file:rounded-md file:border-0
                               file:text-sm file:font-semibold
                               file:bg-indigo-50 file:text-indigo-700
                               hover:file:bg-indigo-100 transition-colors duration-150" />
                        <p class="mt-1 text-xs text-gray-500">{{ __('Formatos permitidos: JPG, PNG, GIF, SVG. Máx: 2MB.') }}</p>
                        <x-input-error :messages="$errors->get('icon')" class="mt-2" />

                        @if ($area->icon_path)
                            <div class="mt-3 flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                                <p class="text-sm text-gray-600">{{ __('Icono actual:') }}</p>
                                <img src="{{ asset('storage/' . $area->icon_path) }}" alt="{{ $area->name }} Icon" class="h-10 w-10 object-contain rounded-md border border-gray-200 p-1 flex-shrink-0">
                                <label for="remove_icon" class="flex items-center text-red-600 text-sm cursor-pointer hover:text-red-800">
                                    <input type="checkbox" name="remove_icon" id="remove_icon" value="1" class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500 mr-1">
                                    {{ __('Eliminar icono actual') }}
                                </label>
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-end mt-4 space-y-3 sm:space-y-0 sm:space-x-3">
                        <a href="{{ route('admin.areas.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 border border-transparent rounded-full font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-4 shadow-sm w-full sm:w-auto">
                            {{ __('Cancelar') }}
                        </a>
                        <x-primary-button class="bg-[#2c3856] hover:bg-[#ff9c00] focus:bg-[#ff9c00] active:bg-[#a06d00] focus:ring-[#2c3856] shadow-md w-full sm:w-auto justify-center">
                            {{ __('Actualizar Área') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>