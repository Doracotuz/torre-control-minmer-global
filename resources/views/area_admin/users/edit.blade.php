<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2c3856] leading-tight">
            Editando a: <span class="text-[#ff9c00]">{{ $user->name }}</span>
        </h2>
    </x-slot>

    <div class="py-12 bg-[#E8ECF7]">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200">

                <form method="POST" action="{{ route('area_admin.users.update', $user) }}" enctype="multipart/form-data" class="p-6 md:p-8"
                      x-data="{ // Agregado x-data para Alpine.js
                          photoName: null,
                          photoPreview: '{{ $user->profile_photo_path ? Storage::disk('s3')->url($user->profile_photo_path) : null }}'
                      }">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                        <div class="space-y-6">
                            <div>
                                <x-input-label for="name" :value="__('Nombre Completo')" class="font-semibold" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name)" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="position" :value="__('Posición')" class="font-semibold" />
                                <select id="position" name="position" class="block mt-1 w-full border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00] rounded-md shadow-sm">
                                    <option value="">{{ __('Selecciona una Posición') }}</option>
                                    @foreach ($positions as $position)
                                        <option value="{{ $position->name }}" {{ old('position', $user->position) == $position->name ? 'selected' : '' }}>
                                            {{ $position->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('position')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="phone_number" :value="__('Número Telefónico')" class="font-semibold" />
                                <x-text-input id="phone_number" class="block mt-1 w-full" type="tel" name="phone_number" :value="old('phone_number', $user->phone_number)" />
                                <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="email" :value="__('Email')" class="font-semibold" />
                                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="password" :value="__('Nueva Contraseña')" class="font-semibold" />
                                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" autocomplete="new-password" />
                                <p class="mt-1 text-xs text-gray-500">Dejar vacío para no cambiar la contraseña.</p>
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="password_confirmation" :value="__('Confirmar Nueva Contraseña')" class="font-semibold" />
                                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" />
                                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <x-input-label :value="__('Foto de Perfil')" class="font-semibold mb-3 text-center" />

                                <div class="flex flex-col items-center space-y-4">
                                    <div class="w-32 h-32 rounded-full bg-gray-100 border-4 border-gray-200 flex items-center justify-center overflow-hidden shadow-md">
                                        <template x-if="!photoPreview">
                                            <svg class="w-20 h-20 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM12 12.5c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z"></path></svg>
                                        </template>
                                        <template x-if="photoPreview">
                                            <img :src="photoPreview" class="w-full h-full object-cover">
                                        </template>
                                    </div>

                                    <label for="profile_photo" class="inline-flex items-center px-5 py-2 bg-[#ff9c00] text-white rounded-full font-semibold text-xs uppercase tracking-widest hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-[#2c3856] focus:ring-offset-2 transition ease-in-out duration-300 transform hover:scale-105 shadow-md cursor-pointer">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        <span x-text="photoName || 'Cambiar Foto'"></span>
                                    </label>

                                    <input id="profile_photo" name="profile_photo" type="file" class="hidden" x-ref="photo"
                                           x-on:change="
                                                photoName = $refs.photo.files.length > 0 ? $refs.photo.files[0].name : null;
                                                if (photoName) {
                                                    const reader = new FileReader();
                                                    reader.onload = (e) => { photoPreview = e.target.result; };
                                                    reader.readAsDataURL($refs.photo.files[0]);
                                                }
                                           ">

                                    @if ($user->profile_photo_path)
                                        <label for="remove_profile_photo" class="flex items-center text-sm text-red-600 hover:text-red-800 cursor-pointer">
                                            <input type="checkbox" name="remove_profile_photo" id="remove_profile_photo" value="1"
                                                   class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500"
                                                   x-on:click="if ($event.target.checked) { photoPreview = null; photoName = null; $refs.photo.value = ''; }">
                                            <span class="ml-2">{{ __('Eliminar foto actual') }}</span>
                                        </label>
                                    @endif

                                    <x-input-error :messages="$errors->get('profile_photo')" class="mt-2" />
                                </div>
                            </div>

                            {{-- Hidden input for area_id to maintain current area --}}
                            <input type="hidden" name="area_id" value="{{ $currentArea->id }}">

                            <div class="mt-6">
                                <x-input-label for="area_name_display" :value="__('Área Asignada')" class="font-semibold" />
                                <x-text-input id="area_name_display" class="block mt-1 w-full bg-gray-100 border-gray-300 text-gray-500 cursor-not-allowed" type="text" :value="$currentArea->name" readonly />
                                <p class="mt-2 text-sm text-gray-500">Este usuario pertenece a tu área y no puede ser cambiado desde aquí.</p>
                            </div>

                            <div class="pt-2">
                                <label for="is_area_admin" class="inline-flex items-center">
                                    <input id="is_area_admin" type="checkbox" class="rounded border-gray-300 text-[#ff9c00] shadow-sm focus:ring-[#ff9c00]" name="is_area_admin" value="1" {{ old('is_area_admin', $user->is_area_admin) ? 'checked' : '' }}>
                                    <span class="ms-2 text-sm text-gray-600">{{ __('Asignar como Administrador de Área') }}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-8 border-t pt-6">
                        <a href="{{ route('area_admin.users.index') }}" class="text-sm text-gray-600 hover:text-gray-900 underline mr-6">
                            Cancelar
                        </a>
                        <x-primary-button>
                            {{ __('Actualizar Usuario') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>