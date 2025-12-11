<section x-data="mfaHandler()">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Autenticación de Dos Factores (2FA)') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('Añade una capa extra de seguridad. Requeriremos un código de tu celular al iniciar sesión.') }}
        </p>
    </header>

    <div class="mt-6">
        <template x-if="!isEnabled && setupStep === 0">
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-6">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-white rounded-full shadow-sm">
                        <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Protege tu cuenta') }}</h3>
                        <p class="mt-1 text-sm text-gray-600 mb-4">
                            Incluso si alguien roba tu contraseña, no podrá entrar sin tu teléfono. Configura esto en 2 minutos.
                        </p>
                        <x-primary-button type="button" x-on:click="startSetup">
                            {{ __('Comenzar Configuración') }}
                        </x-primary-button>
                    </div>
                </div>
            </div>
        </template>

        <template x-if="!isEnabled && setupStep > 0">
            <div class="border border-gray-200 rounded-xl shadow-sm bg-white">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-3 flex justify-between items-center text-xs font-bold text-gray-400 uppercase tracking-wider">
                    <span :class="{'text-[#ff9c00]': setupStep === 1}">1. Descargar App</span>
                    <svg class="w-4 h-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    <span :class="{'text-[#ff9c00]': setupStep === 2}">2. Sincronizar</span>
                    <svg class="w-4 h-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    <span :class="{'text-[#ff9c00]': setupStep === 3}">3. Verificar</span>
                </div>

                <div class="p-6">
                    
                    <div x-show="setupStep === 1">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Paso 1: ¿Tienes una app autenticadora?') }}</h3>
                        <p class="text-sm text-gray-600 mb-6">{{ __('Necesitas una aplicación gratuita en tu celular para generar los códigos. Elige tu favorita:') }}</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            
                            <div class="p-4 border rounded-xl hover:border-blue-300 hover:shadow-md transition bg-gray-50/50">
                                <div class="flex items-center gap-3 mb-4">
                                    <img src="https://play-lh.googleusercontent.com/NntMALIH4odanPPYSqUOXsX8zy_giiK2olJiqkcxwFIOOspVrhMi9Miv6LYdRnKIg-3R=w240-h480-rw" class="w-10 h-10" alt="Google">
                                    <p class="font-bold text-gray-800">Google Authenticator</p>
                                </div>
                                <div class="flex gap-2">
                                    <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank" class="flex-1 py-2 px-3 bg-white border border-gray-200 rounded-lg text-xs font-bold text-gray-600 text-center hover:bg-gray-50 hover:text-green-600 transition flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.3,12.5L17.38,15.42L14.54,12.58L17.38,9.74L20.3,12.66C20.71,13.08 20.3,13.74 19.7,13.74H19.7V13.74L17.38,16.06L20.3,13.14L16.81,9.65L20.3,12.5M16.81,8.88L14.54,11.15L6.05,2.66L16.81,8.88Z" /></svg>
                                        Android
                                    </a>
                                    <a href="https://apps.apple.com/us/app/google-authenticator/id388497605" target="_blank" class="flex-1 py-2 px-3 bg-white border border-gray-200 rounded-lg text-xs font-bold text-gray-600 text-center hover:bg-gray-50 hover:text-blue-600 transition flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.71,19.5C17.88,20.74 17,21.95 15.66,21.97C14.32,22 13.89,21.18 12.37,21.18C10.84,21.18 10.37,21.95 9.1,22C7.79,22.05 6.8,20.68 5.96,19.47C4.25,17 2.94,12.45 4.7,9.39C5.57,7.87 7.13,6.91 8.82,6.88C10.1,6.86 11.32,7.75 12.11,7.75C12.89,7.75 14.37,6.68 15.92,6.84C16.57,6.87 18.39,7.1 19.56,8.82C19.47,8.88 17.39,10.1 17.41,12.63C17.44,15.65 20.06,16.66 20.09,16.67C20.06,16.74 19.67,18.11 18.71,19.5M13,3.5C13.73,2.67 14.94,2.04 15.94,2C16.07,3.17 15.6,4.35 14.9,5.19C14.21,6.04 13.07,6.7 11.95,6.61C11.8,5.37 12.36,4.26 13,3.5Z" /></svg>
                                        iOS
                                    </a>
                                </div>
                            </div>

                            <div class="p-4 border rounded-xl hover:border-blue-300 hover:shadow-md transition bg-gray-50/50">
                                <div class="flex items-center gap-3 mb-4">
                                    <img src="https://play-lh.googleusercontent.com/_1CV99jklLbXuun-6E7eCPR-sKKeZc602rhw_QHZz-qm7xrPdgWsJVc7NtFkkliI8No" class="w-10 h-10" alt="Microsoft">
                                    <p class="font-bold text-gray-800">Microsoft Authenticator</p>
                                </div>
                                <div class="flex gap-2">
                                    <a href="https://play.google.com/store/apps/details?id=com.azure.authenticator" target="_blank" class="flex-1 py-2 px-3 bg-white border border-gray-200 rounded-lg text-xs font-bold text-gray-600 text-center hover:bg-gray-50 hover:text-green-600 transition flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.3,12.5L17.38,15.42L14.54,12.58L17.38,9.74L20.3,12.66C20.71,13.08 20.3,13.74 19.7,13.74H19.7V13.74L17.38,16.06L20.3,13.14L16.81,9.65L20.3,12.5M16.81,8.88L14.54,11.15L6.05,2.66L16.81,8.88Z" /></svg>
                                        Android
                                    </a>
                                    <a href="https://apps.apple.com/us/app/microsoft-authenticator/id983156458" target="_blank" class="flex-1 py-2 px-3 bg-white border border-gray-200 rounded-lg text-xs font-bold text-gray-600 text-center hover:bg-gray-50 hover:text-blue-600 transition flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.71,19.5C17.88,20.74 17,21.95 15.66,21.97C14.32,22 13.89,21.18 12.37,21.18C10.84,21.18 10.37,21.95 9.1,22C7.79,22.05 6.8,20.68 5.96,19.47C4.25,17 2.94,12.45 4.7,9.39C5.57,7.87 7.13,6.91 8.82,6.88C10.1,6.86 11.32,7.75 12.11,7.75C12.89,7.75 14.37,6.68 15.92,6.84C16.57,6.87 18.39,7.1 19.56,8.82C19.47,8.88 17.39,10.1 17.41,12.63C17.44,15.65 20.06,16.66 20.09,16.67C20.06,16.74 19.67,18.11 18.71,19.5M13,3.5C13.73,2.67 14.94,2.04 15.94,2C16.07,3.17 15.6,4.35 14.9,5.19C14.21,6.04 13.07,6.7 11.95,6.61C11.8,5.37 12.36,4.26 13,3.5Z" /></svg>
                                        iOS
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <x-primary-button type="button" x-on:click="setupStep = 2">
                                {{ __('Ya tengo la App instalada') }} &rarr;
                            </x-primary-button>
                        </div>
                    </div>

                    <div x-show="setupStep === 2">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Paso 2: Sincronizar') }}</h3>
                        
                        <div class="flex flex-col xl:flex-row gap-8 items-start">
                            <div class="flex-1 w-full">
                                <ol class="list-decimal list-inside space-y-3 text-sm text-gray-700 mt-2">
                                    <li>Abre la aplicación en tu celular.</li>
                                    <li>Busca el botón <strong class="bg-gray-100 px-2 py-0.5 rounded text-gray-900">+</strong> o "Agregar cuenta".</li>
                                    <li>Selecciona <strong>"Escanear código QR"</strong>.</li>
                                    <li>Apunta la cámara al código.</li>
                                </ol>

                                <div class="mt-6 p-4 bg-yellow-50 rounded-lg border border-yellow-100 w-full">
                                    <p class="text-xs font-bold text-yellow-800 uppercase mb-1">{{ __('¿No funciona la cámara?') }}</p>
                                    <p class="text-xs text-yellow-700 mb-2">{{ __('Ingresa esta clave manualmente en la app:') }}</p>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <code class="bg-white px-2 py-1 rounded border border-yellow-200 font-mono text-sm select-all break-all" x-text="secretKey"></code>
                                        <button @click="navigator.clipboard.writeText(secretKey); alert('Copiado')" class="text-xs text-yellow-600 hover:underline underline-offset-2 shrink-0">Copiar</button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex-shrink-0 mx-auto xl:mx-0 bg-white p-2 border rounded-lg shadow-sm">
                                <img :src="qrCode" alt="QR Code" class="w-56 h-56 object-contain max-w-full">
                            </div>
                        </div>

                        <div class="flex justify-between mt-8 pt-4 border-t border-gray-100">
                            <button type="button" x-on:click="setupStep = 1" class="text-sm text-gray-500 hover:text-gray-900">
                                &larr; Volver
                            </button>
                            <x-primary-button type="button" x-on:click="setupStep = 3">
                                {{ __('Ya lo escaneé') }} &rarr;
                            </x-primary-button>
                        </div>
                    </div>

                    <div x-show="setupStep === 3">
                        <div class="text-center max-w-sm mx-auto">
                            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M12 14v-4m0-4h.01M12 2a10 10 0 1010 10A10 10 0 0012 2z" /></svg>
                            </div>
                            
                            <h3 class="text-lg font-bold text-gray-900 mb-2">{{ __('Paso 3: Verificación Final') }}</h3>
                            <p class="text-sm text-gray-600 mb-6">{{ __('Tu app debería estar mostrando un código de 6 números que cambia cada 30 segundos. Ingrésalo aquí para confirmar que todo funciona.') }}</p>

                            <input type="text" x-model="code" class="w-full text-center text-3xl tracking-[0.5em] font-mono font-bold border-gray-300 rounded-xl focus:border-[#ff9c00] focus:ring-[#ff9c00]" placeholder="000000" maxlength="6">

                            <p x-show="errorMessage" x-text="errorMessage" class="mt-2 text-sm text-red-600 font-bold bg-red-50 py-1 rounded"></p>

                            <div class="mt-6 space-y-3">
                                <x-primary-button type="button" class="w-full justify-center py-3" x-on:click="confirmSetup" ::disabled="code.length < 6">
                                    {{ __('Activar Seguridad') }}
                                </x-primary-button>
                                
                                <button type="button" x-on:click="setupStep = 2" class="text-sm text-gray-500 hover:text-gray-900 block w-full">
                                    El código no funciona, volver atrás
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </template>

        <template x-if="isEnabled && setupStep === 0">
            <div class="bg-green-50 border border-green-200 rounded-xl p-6 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="p-2 bg-white rounded-full border border-green-100 text-green-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-green-800">{{ __('Cuenta Protegida') }}</h3>
                        <p class="text-sm text-green-700">{{ __('La autenticación de dos factores está activa.') }}</p>
                    </div>
                </div>
                <x-danger-button type="button" x-on:click="openDisableModal = true">
                    {{ __('Desactivar') }}
                </x-danger-button>
            </div>
        </template>
    </div>

    <div x-show="openDisableModal" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;"
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="openDisableModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                {{ __('¿Seguro que quieres desactivar 2FA?') }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    {{ __('Tu cuenta quedará vulnerable. Ingresa tu contraseña para confirmar.') }}
                                </p>
                                <div class="mt-4">
                                    <input type="password" 
                                           x-model="password" 
                                           class="shadow-sm focus:ring-red-500 focus:border-red-500 block w-full sm:text-sm border-gray-300 rounded-md" 
                                           placeholder="Contraseña actual"
                                           @keyup.enter="disableMfa">
                                    <p x-show="errorMessage" x-text="errorMessage" class="mt-2 text-sm text-red-600 font-bold"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="disableMfa" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        {{ __('Sí, desactivar') }}
                    </button>
                    <button type="button" @click="openDisableModal = false; password = ''; errorMessage = ''" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        {{ __('Cancelar') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function mfaHandler() {
            return {
                isEnabled: {{ Auth::user()->google2fa_secret ? 'true' : 'false' }},
                setupStep: 0, 
                qrCode: null,
                secretKey: '', 
                code: '',
                password: '',
                errorMessage: '',
                openDisableModal: false,

                async startSetup() {
                    this.errorMessage = '';
                    try {
                        const res = await fetch('{{ route("mfa.generate") }}', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
                        });
                        const data = await res.json();
                        if(res.ok) {
                            this.qrCode = data.qr_code;
                            this.secretKey = data.secret; 
                            this.setupStep = 1; 
                        } else {
                            alert('Error de conexión con el servidor.');
                        }
                    } catch (e) { console.error(e); }
                },

                async confirmSetup() {
                    this.errorMessage = '';
                    try {
                        const res = await fetch('{{ route("mfa.enable") }}', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                            body: JSON.stringify({ code: this.code })
                        });
                        const data = await res.json();
                        
                        if (res.ok) {
                            this.isEnabled = true;
                            this.setupStep = 0; 
                            this.code = '';
                            alert('¡Excelente! Tu cuenta ahora es mucho más segura.');
                        } else {
                            this.errorMessage = data.message || 'Código incorrecto.';
                        }
                    } catch (e) {
                        this.errorMessage = 'Error de conexión.';
                    }
                },

                async disableMfa() {
                    if (this.password.length === 0) {
                        this.errorMessage = 'Por favor ingresa tu contraseña.';
                        return;
                    }
                    
                    this.errorMessage = '';
                    
                    try {
                        const res = await fetch('{{ route("mfa.disable") }}', {
                            method: 'POST',
                            headers: { 
                                'X-CSRF-TOKEN': '{{ csrf_token() }}', 
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ password: this.password })
                        });
                        
                        const data = await res.json();

                        if (res.ok) {
                            this.isEnabled = false;
                            this.openDisableModal = false;
                            this.password = '';
                            this.setupStep = 0;
                            alert('2FA Desactivado correctamente.');
                        } else {
                            if (data.errors && data.errors.password) {
                                this.errorMessage = data.errors.password[0];
                            } else {
                                this.errorMessage = data.message || 'Error al desactivar.';
                            }
                        }
                    } catch (e) {
                        console.error(e);
                        this.errorMessage = 'Error de conexión.';
                    }
                }
            }
        }
    </script>
</section>