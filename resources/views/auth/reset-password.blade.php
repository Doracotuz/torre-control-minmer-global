<x-guest-layout>
    {{-- NEW: Sección de Logo y Título para coincidir con login.blade.php --}}
    <div class="flex flex-col items-center justify-center mb-6">
        <img src="{{ Storage::disk('s3')->url('LogoAzul.png') }}" alt="Minmer Global Logo" class="h-20 mb-3">
        <h1 class="text-3xl font-extrabold text-[#2c3856] mb-1" style="font-family: 'Raleway', sans-serif;">TORRE DE CONTROL</h1>
        <p class="text-sm text-gray-600" style="font-family: 'Montserrat', sans-serif;">MINMER GLOBAL</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-5"> {{-- Ajuste de espaciado para coincidir con login.blade.php --}}
            <x-input-label for="email" :value="__('Correo Electrónico')" class="text-gray-700 font-medium" /> {{-- Alineado el estilo con login.blade.php --}}
            <x-text-input id="email" class="block mt-2 w-full px-4 py-2 rounded-lg border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all duration-200 shadow-sm" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" /> {{-- Alineado el estilo con login.blade.php --}}
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-500 text-sm" /> {{-- Alineado el estilo con login.blade.php --}}
        </div>

        <div class="mb-5"> {{-- Ajuste de espaciado para coincidir con login.blade.php --}}
            <x-input-label for="password" :value="__('Nueva Contraseña')" class="text-gray-700 font-medium" /> {{-- Alineado el estilo con login.blade.php --}}
            <x-text-input id="password" class="block mt-2 w-full px-4 py-2 rounded-lg border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all duration-200 shadow-sm"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-500 text-sm" /> {{-- Alineado el estilo con login.blade.php --}}
        </div>

        <div class="mb-6"> {{-- Ajuste de espaciado para coincidir con login.blade.php --}}
            <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" class="text-gray-700 font-medium" /> {{-- Alineado el estilo con login.blade.php --}}

            <x-text-input id="password_confirmation" class="block mt-2 w-full px-4 py-2 rounded-lg border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all duration-200 shadow-sm"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-500 text-sm" /> {{-- Alineado el estilo con login.blade.php --}}
        </div>

        <div class="flex items-center justify-center mt-6"> {{-- Centrado el botón y ajustado el margen superior --}}
            {{-- Botón con el estilo de login.blade.php --}}
            <button type="submit" class="w-full py-3 bg-[#ff9c00] text-white rounded-lg font-semibold text-lg uppercase tracking-wider hover:bg-orange-600 focus:bg-orange-600 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-[#2c3856] focus:ring-offset-2 transition ease-in-out duration-300 transform hover:scale-105 shadow-lg">
                {{ __('Restablecer Contraseña') }}
            </button>
        </div>
    </form>

    {{-- NEW: Botón para regresar a la vista de login --}}
    <div class="text-center mt-6">
        <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:text-blue-800 transition-colors duration-200 font-medium">
            {{ __('Volver a Iniciar Sesión') }}
        </a>
    </div>

    {{-- NEW: Enlaces de Términos y Condiciones y Política de Privacidad (copiados de login.blade.php) --}}
    <div class="mt-8 pt-6 border-t border-gray-200 text-center space-y-3">
        <p class="text-sm text-gray-500 mb-4">© 2025 Minmer Global. Todos los derechos reservados.</p>
        <p class="text-sm text-gray-600">
            <a href="{{ route('terms.conditions') }}" class="text-blue-600 hover:text-blue-800 transition-colors duration-200 font-medium">Términos y Condiciones</a>
        </p>
        <p class="text-sm text-gray-600">
            <a href="{{ route('privacy.policy') }}" class="text-blue-600 hover:text-blue-800 transition-colors duration-200 font-medium">Política de Privacidad</a>
        </p>
    </div>
</x-guest-layout>