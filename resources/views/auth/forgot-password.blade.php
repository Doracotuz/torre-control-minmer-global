<x-guest-layout>

    <div class="flex flex-col items-center justify-center mb-6">
        <img src="{{ Storage::disk('s3')->url('LogoAzul.png') }}" alt="Minmer Global Logo" class="h-20 mb-3">
        <h1 class="text-3xl font-extrabold text-[#2c3856] mb-1" style="font-family: 'Raleway', sans-serif;">TORRE DE CONTROL</h1>
        <p class="text-sm text-gray-600" style="font-family: 'Montserrat', sans-serif;">MINMER GLOBAL</p>
    </div>

    <div class="mb-4 text-sm text-gray-600 text-center">
        {{ __('Para restablecer tu contraseña, proporciona tu correo electrónico.') }}
    </div>

    <x-auth-session-status class="mb-4 text-center text-sm text-red-600" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-5">
            <x-input-label for="email" :value="__('Correo Electrónico')" class="text-gray-700 font-medium" />
            <x-text-input id="email" class="block mt-2 w-full px-4 py-2 rounded-lg border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all duration-200 shadow-sm" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-500 text-sm" />
        </div>

        <div class="flex items-center justify-center mt-6">
            <button type="submit" class="w-full py-3 bg-[#ff9c00] text-white rounded-lg font-semibold text-lg uppercase tracking-wider hover:bg-orange-600 focus:bg-orange-600 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-[#2c3856] focus:ring-offset-2 transition ease-in-out duration-300 transform hover:scale-105 shadow-lg">
                {{ __('Envíar correo') }}
            </button>
        </div>
    </form>

    <div class="text-center mt-6">
        <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:text-blue-800 transition-colors duration-200 font-medium">
            {{ __('¿Recordaste tu contraseña? Volver a Iniciar Sesión') }}
        </a>
    </div>

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