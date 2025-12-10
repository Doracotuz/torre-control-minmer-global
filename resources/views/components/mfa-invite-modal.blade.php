@if (session()->pull('mfa_invitation'))
<div x-data="{ show: false }" 
     x-init="setTimeout(() => show = true, 3000)" 
     x-show="show" 
     style="display: none;"
     class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center px-4 py-6 sm:p-0">
    
    <div x-show="show"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" 
         @click="show = false"></div>

    <div x-show="show"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         class="relative bg-white rounded-2xl px-4 pt-5 pb-4 text-left shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full sm:p-8 border border-gray-100">
        
        <div class="sm:flex sm:items-start">
            
            <div class="mt-3 text-center sm:mt-0 sm:ml-5 sm:text-left">
                <h3 class="text-xl leading-6 font-bold text-[#2c3856]" id="modal-title">
                    ¡Mejora la seguridad de tu cuenta!
                </h3>
                <div class="mt-3">
                    <p class="text-sm text-gray-500">
                        Hola, <strong>{{ Auth::user()->name }}</strong>. Notamos que aún no activas la Autenticación de Dos Factores (2FA).
                    </p>
                    <p class="text-sm text-gray-500 mt-2">
                        Activarla solo toma 2 minutos y protege tu información incluso si alguien adivina tu contraseña.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="mt-6 sm:mt-8 sm:flex sm:flex-row-reverse gap-3">
            <a href="{{ route('profile.edit') }}" 
               class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-3 bg-[#ff9c00] text-base font-bold text-white hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:w-auto sm:text-sm transition-colors">
                Configurar ahora
            </a>
            <button type="button" 
                    @click="show = false"
                    class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-3 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                Recordármelo luego
            </button>
        </div>
        
        <button @click="show = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-500 transition-colors">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>
@endif