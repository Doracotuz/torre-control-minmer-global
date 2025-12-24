<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="min-h-screen p-6 lg:p-10 font-sans text-slate-800 bg-[#f0f2f5]">
        
        <div class="max-w-4xl mx-auto mb-8 animate-entry">
            <a href="{{ route('electronic-label.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-[#ff9c00] mb-4 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Volver al Dashboard
            </a>
            <h1 class="text-3xl font-bold text-[#2c3856]">Creación de Marbetes</h1>
            <p class="text-gray-500 mt-1">Generación masiva de folios y códigos QR únicos.</p>
        </div>

        <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden animate-entry" style="animation-delay: 0.1s;">
            <div class="p-8 md:p-10">
                
                <form action="{{ route('electronic-label.store') }}" method="POST">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        
                        <div class="space-y-6">
                            
                            <div>
                                <label for="series" class="block text-sm font-bold text-[#2c3856] mb-2">Serie del Marbete</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-400 font-bold text-lg">#</span>
                                    </div>
                                    <input type="text" name="series" id="series" 
                                        maxlength="2" required
                                        placeholder="Ej. AA"
                                        oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')"
                                        class="w-full pl-10 pr-4 py-3 rounded-lg border-2 border-gray-200 focus:border-[#ff9c00] focus:ring focus:ring-orange-200 transition-all outline-none font-bold text-lg tracking-widest text-[#2c3856] uppercase">
                                </div>
                                <p class="text-xs text-gray-400 mt-2">Debe contener exactamente 2 caracteres alfanuméricos.</p>
                                @error('series') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="quantity" class="block text-sm font-bold text-[#2c3856] mb-2">Cantidad a Generar</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    </div>
                                    <input type="number" name="quantity" id="quantity" 
                                        min="1" max="5000" required
                                        placeholder="Ej. 100"
                                        class="w-full pl-10 pr-4 py-3 rounded-lg border-2 border-gray-200 focus:border-[#ff9c00] focus:ring focus:ring-orange-200 transition-all outline-none font-medium text-lg text-[#2c3856]">
                                </div>
                                <p class="text-xs text-gray-400 mt-2">Número de folios consecutivos a crear.</p>
                                @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                        </div>

                        <div class="bg-[#2c3856] rounded-xl p-6 text-white flex flex-col justify-between relative overflow-hidden group">
                            <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
                            
                            <div>
                                <h3 class="font-bold text-lg mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Formato de Salida
                                </h3>
                                <div class="space-y-4 text-sm text-gray-300">
                                    <p>Los marbetes se generarán siguiendo un consecutivo automático basado en la serie ingresada.</p>
                                    
                                    <div class="bg-white/10 p-3 rounded-lg border border-white/10">
                                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Ejemplo de Folio</p>
                                        <p class="font-mono text-lg text-white"><span class="text-[#ff9c00]" x-text="document.getElementById('series').value || 'XX'">XX</span>-0000000123</p>
                                    </div>

                                    <div class="bg-white/10 p-3 rounded-lg border border-white/10">
                                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Estructura del Link</p>
                                        <p class="font-mono text-xs text-emerald-400 break-all">
                                            {{ url('/') }}/app/qr/faces/pages/mobile/validadorqr/<span class="text-white">...52-caracteres...</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6">
                                <button type="submit" class="w-full bg-[#ff9c00] hover:bg-orange-600 text-white font-bold py-4 rounded-lg shadow-lg shadow-orange-500/30 transition-all duration-300 transform hover:-translate-y-1 flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                                    Generar Marbetes
                                </button>
                            </div>
                        </div>

                    </div>
                </form>

            </div>
        </div>

        <div class="mt-8 text-center">
            <p class="text-xs text-gray-400">© {{ date('Y') }} Control Tower - Módulo de Marbetes</p>
        </div>

    </div>
    
    <script>
        document.getElementById('series').addEventListener('input', function(e) {
            let val = e.target.value.toUpperCase();
            // Actualiza el texto del ejemplo con Alpine o Vanilla JS si lo prefieres
            // Aquí usamos un selector directo para simplificar ya que x-text depende de Alpine en el elemento padre
            // Si usas el x-text del ejemplo anterior, asegúrate que el div padre tenga x-data
        });
    </script>
</x-app-layout>