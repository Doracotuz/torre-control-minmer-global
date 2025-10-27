<x-guest-layout>
    
    <h2 class="text-3xl font-bold text-gray-800 mb-8">Inicio de sesión</h2>
    
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-6">
            <label for="email" class="block mb-2 text-sm font-medium text-gray-600">Correo electrónico</label>
            <div class="relative">
                <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22 6C22 4.9 21.1 4 20 4H4C2.9 4 2 4.9 2 6M22 6V18C22 19.1 21.1 20 20 20H4C2.9 20 2 19.1 2 18V6M22 6L12 13L2 6" stroke="#2C3856" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <input id="email" type="email" name="email" required autocomplete="email" placeholder="Ingrese su correo electrónico"
                    class="w-full px-4 py-3 pr-10 border-t-0 border-l-0 border-r-0 border-b-2 border-[#CAD2D9] rounded-t-lg rounded-b-none focus:ring-orange-500 focus:border-orange-500">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mb-6">
            <label for="password" class="block mb-2 text-sm font-medium text-gray-600">Contraseña</label>
            <div class="relative">
                
                <button type="button" id="togglePassword" 
                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 z-10 text-[#2C3856] 
                               hover:text-[#E58C00] 
                               focus:outline-none 
                               rounded-lg transition duration-200 ease-in-out"
                        aria-label="Mostrar contraseña">
                    
                    <svg id="eye-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <svg id="eye-off-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 1.274-4.057 5.064-7 9.542-7 1.18 0 2.31.25 3.364.7l-4.225 4.225m0 0a3 3 0 104.242 4.242l4.225-4.225a10.027 10.027 0 01.7 3.364c0 4.057-3.79 7-8.268 7-1.18 0-2.31-.25-3.364-.7m0 0l-4.225-4.225M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </button>
                
                <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Ingrese su contraseña"
                    class="w-full px-4 py-3 pr-10 border-t-0 border-l-0 border-r-0 border-b-2 border-[#E58C00] rounded-t-lg rounded-b-none focus:ring-orange-500 focus:border-orange-500">
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <div class="flex items-center justify-between mb-8">
            <label for="remember_me" class="flex items-center">
                <input id="remember_me" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-orange-500 focus:ring-orange-500" name="remember">
                <span class="ml-2 text-sm text-gray-600">Recordarme</span>
            </label>
            <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:underline font-medium">¿Olvidó su contraseña?</a>
        </div>

        <div class="flex items-center justify-center">
            <button type="submit" class="w-full flex items-center justify-center py-3.5 px-4 bg-[#FF9C00] hover:bg-orange-600 text[#2C3856] font-bold rounded-full focus:outline-none transition duration-300">
                Iniciar sesión →
            </button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            const toggleButton = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            const eyeOffIcon = document.getElementById('eye-off-icon');

            function showPassword() {
                passwordInput.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeOffIcon.classList.remove('hidden');
            }

            function hidePassword() {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeOffIcon.classList.add('hidden');
            }

            toggleButton.addEventListener('mousedown', showPassword);
            
            toggleButton.addEventListener('mouseup', hidePassword);
            
            toggleButton.addEventListener('mouseleave', hidePassword);

            toggleButton.addEventListener('touchstart', showPassword, { passive: true });
            
            toggleButton.addEventListener('touchend', hidePassword);
        });
    </script>
    </x-guest-layout>