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
                <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20.9998 2L18.9998 4M18.9998 4L21.9998 7L18.4998 10.5L15.4998 7.5M18.9998 4L15.4998 7.5M11.3898 11.61C11.9061 12.1195 12.3166 12.726 12.5975 13.3948C12.8785 14.0635 13.0244 14.7813 13.0268 15.5066C13.0292 16.232 12.8882 16.9507 12.6117 17.6213C12.3352 18.2919 11.9288 18.9012 11.4159 19.4141C10.903 19.9271 10.2937 20.3334 9.62309 20.6099C8.95247 20.8864 8.23379 21.0275 7.50842 21.025C6.78305 21.0226 6.06533 20.8767 5.39658 20.5958C4.72782 20.3148 4.12125 19.9043 3.61179 19.388C2.60992 18.3507 2.05555 16.9614 2.06808 15.5193C2.08061 14.0772 2.65904 12.6977 3.67878 11.678C4.69853 10.6583 6.078 10.0798 7.52008 10.0673C8.96216 10.0548 10.3515 10.6091 11.3888 11.611L11.3898 11.61ZM11.3898 11.61L15.4998 7.5" stroke="#2C3856" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
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
</x-guest-layout>