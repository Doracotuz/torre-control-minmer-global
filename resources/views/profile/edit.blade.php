<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Perfíl de Usuario') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Información del Perfil') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600">
                                {{ __("Actualiza la información de perfil de tu cuenta y la dirección de correo electrónico.") }}
                            </p>
                        </header>

                        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                            @csrf
                        </form>

                        <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data"
                            x-data="{ photoName: null, photoPreview: null }" {{-- Alpine.js data for photo preview --}}
                            x-on:change="
                                photoName = $refs.photo.files[0].name;
                                const reader = new FileReader();
                                reader.onload = (e) => {
                                    photoPreview = e.target.result;
                                };
                                reader.readAsDataURL($refs.photo.files[0]);
                            "
                        >
                            @csrf
                            @method('patch')

                            <div class="flex flex-col items-center">
                                <x-input-label for="profile_photo" :value="__('Foto de Perfil')" class="mb-4 text-lg font-semibold text-[#2c3856]" />

                                <div class="mt-2 mb-4">
                                    <template x-if="photoPreview">
                                        <img :src="photoPreview" class="h-24 w-24 rounded-full object-cover border-4 border-gray-200 shadow-md">
                                    </template>
                                    <template x-if="!photoPreview && '{{ $user->profile_photo_path }}'">
                                        <img src="{{ $user->profile_photo_path ? Storage::disk('s3')->url($user->profile_photo_path) : '' }}" alt="{{ $user->name }}" class="h-24 w-24 rounded-full object-cover border-4 border-gray-200 shadow-md">

                                    </template>
                                    <template x-if="!photoPreview && !'{{ $user->profile_photo_path }}'">
                                        <div class="h-24 w-24 rounded-full bg-gray-200 flex items-center justify-center text-gray-400 border-4 border-gray-300 shadow-md">
                                            <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM12 12.5c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z"></path></svg>
                                        </div>
                                    </template>
                                </div>

                                <input type="file" class="hidden" x-ref="photo" name="profile_photo" id="profile_photo" accept="image/*">
                                <label for="profile_photo" class="inline-flex items-center px-5 py-2 bg-[#ff9c00] text-white rounded-full font-semibold text-sm uppercase tracking-widest hover:bg-orange-600 focus:bg-orange-600 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-[#2c3856] focus:ring-offset-2 transition ease-in-out duration-300 transform hover:scale-105 shadow-md cursor-pointer">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    <span x-text="photoName || 'Seleccionar Nueva Foto'"></span>
                                </label>
                                <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />

                                @if ($user->profile_photo_path)
                                    <div class="mt-4">
                                        <label for="remove_profile_photo" class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="remove_profile_photo" id="remove_profile_photo" value="1" class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500">
                                            <span class="ml-2 text-sm text-red-600 font-medium">{{ __('Eliminar foto de perfil actual') }}</span>
                                        </label>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-8">
                                <x-input-label for="name" :value="__('Nombre')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
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

                            <div class="mt-4">
                                <x-input-label for="phone_number" :value="__('Número Telefónico')" />
                                <x-text-input id="phone_number" name="phone_number" type="tel" class="mt-1 block w-full" :value="old('phone_number', $user->phone_number)" autocomplete="tel" />
                                <x-input-error class="mt-2" :messages="$errors->get('phone_number')" />
                            </div>                            

                            <div>
                                <x-input-label for="email" :value="__('Correo Electrónico')" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
                                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                    <div>
                                        <p class="text-sm mt-2 text-gray-800">
                                            {{ __('Tu dirección de correo electrónico no está verificada.') }}

                                            <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                {{ __('Haz clic aquí para reenviar el correo electrónico de verificación.') }}
                                            </button>
                                        </p>

                                        @if (session('status') === 'verification-link-sent')
                                            <p class="mt-2 font-medium text-sm text-green-600">
                                                {{ __('Se ha enviado un nuevo enlace de verificación a tu dirección de correo electrónico.') }}
                                            </p>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Guardar') }}</x-primary-button>

                                @if (session('status') === 'profile-updated')
                                    <p
                                        x-data="{ show: true }"
                                        x-show="show"
                                        x-transition
                                        x-init="setTimeout(() => show = false, 2000)"
                                        class="text-sm text-gray-600"
                                    >{{ __('Guardado.') }}</p>
                                @endif
                            </div>
                        </form>
                    </section>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            {{--
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
            --}}
        </div>
    </div>
</x-app-layout>