<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Nuevo Usuario') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 p-4 sm:p-8">
                <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data"> {{-- ¡Importante: enctype para subir archivos! --}}
                    @csrf

                    <div class="mb-4">
                        <x-input-label for="name" :value="__('Nombre')" />
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="password" :value="__('Contraseña')" />
                        <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" />
                        <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <label for="is_area_admin" class="inline-flex items-center">
                            <input id="is_area_admin" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="is_area_admin" value="1" {{ old('is_area_admin') ? 'checked' : '' }}>
                            <span class="ms-2 text-sm text-gray-600">{{ __('Es Administrador de Área') }}</span>
                        </label>
                        <x-input-error :messages="$errors->get('is_area_admin')" class="mt-2" />
                    </div>                        

                    <div class="mb-4">
                        <x-input-label for="area_id" :value="__('Área')" />
                        <select id="area_id" name="area_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                            <option value="">{{ __('Selecciona un Área') }}</option>
                            @foreach ($areas as $area)
                                <option value="{{ $area->id }}" {{ old('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('area_id')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="profile_photo" :value="__('Foto de Perfil (Opcional)')" />
                        <input id="profile_photo" name="profile_photo" type="file" class="block mt-1 w-full text-sm sm:text-base text-gray-500
                               file:mr-2 sm:file:mr-4 file:py-1.5 sm:file:py-2 file:px-3 sm:file:px-4
                               file:rounded-md file:border-0
                               file:text-sm file:font-semibold
                               file:bg-indigo-50 file:text-indigo-700
                               hover:file:bg-indigo-100 transition-colors duration-150" />
                        <p class="mt-1 text-xs text-gray-500">{{ __('Formatos permitidos: JPG, PNG, GIF, SVG. Máx: 2MB.') }}</p>
                        <x-input-error :messages="$errors->get('profile_photo')" class="mt-2" />
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-end mt-4 space-y-3 sm:space-y-0 sm:space-x-3">
                        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 border border-transparent rounded-full font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm w-full sm:w-auto">
                            {{ __('Cancelar') }}
                        </a>
                        <x-primary-button class="bg-[#2c3856] hover:bg-[#ff9c00] focus:bg-[#ff9c00] active:bg-[#a06d00] focus:ring-[#2c3856] shadow-md w-full sm:w-auto justify-center">
                            {{ __('Crear Usuario') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>