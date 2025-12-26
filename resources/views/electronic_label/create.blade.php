<x-app-layout>
    <x-slot name="header"></x-slot>

    <div x-data="{ series: '' }" class="min-h-screen p-6 lg:p-10 font-sans text-slate-800">
        
        <link rel="preconnect" href="https://fonts.googleapis.com"> 
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin> 
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Raleway:wght@700;800;900&display=swap" rel="stylesheet">
        
        <style>
            body { font-family: 'Montserrat', sans-serif; }
            .font-brand { font-family: 'Raleway', sans-serif; }
            .animate-entry { animation: slideUpFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; transform: translateY(40px); }
            @keyframes slideUpFade { to { opacity: 1; transform: translateY(0); } }
            .section-title { color: #2c3856; font-family: 'Raleway', sans-serif; font-size: 1.25rem; font-weight: 800; margin-bottom: 1.5rem; border-bottom: 2px solid #ff9c00; padding-bottom: 0.5rem; display: inline-block; }
            .input-label { display: block; font-size: 0.875rem; font-weight: 700; color: #2c3856; margin-bottom: 0.5rem; }
            .input-field { width: 100%; padding: 0.75rem 1rem; border-radius: 0.5rem; border: 2px solid #e5e7eb; transition: all 0.2s; outline: none; font-weight: 500; color: #2c3856; }
            .input-field:focus { border-color: #ff9c00; box-shadow: 0 0 0 4px rgba(255, 156, 0, 0.1); }
        </style>

        <div class="max-w-6xl mx-auto mb-8 animate-entry">
            <a href="{{ route('electronic-label.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-[#ff9c00] mb-4 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Volver al Dashboard
            </a>
            <h1 class="text-3xl font-bold text-[#2c3856] font-brand">Creación de Marbetes</h1>
            <p class="text-gray-500 mt-1">Complete la información detallada para generar los folios.</p>
        </div>

        @if(session('success'))
            <div class="max-w-6xl mx-auto mb-6 animate-entry">
                <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-r shadow-sm flex items-start">
                    <svg class="w-6 h-6 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        <p class="font-bold">Generación Completada</p>
                        <p>{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('electronic-label.store') }}" method="POST" class="max-w-6xl mx-auto animate-entry" style="animation-delay: 0.1s;">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 space-y-8">
                    
                    <div class="bg-white p-8 rounded-2xl shadow-lg border-t-4 border-[#ff9c00]">
                        <h2 class="section-title">1. Datos del Marbete</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <div>
                                <label class="input-label">Tipo de Marbete</label>
                                <input type="text" name="label_type" required class="input-field" placeholder="Ej. Marbete Nacional / Marbete Importado">
                            </div>

                            <div>
                                <label class="input-label">Serie</label>
                                <input type="text" name="series" id="series" x-model="series" maxlength="2" required 
                                    oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '')"
                                    class="input-field uppercase tracking-widest font-bold" placeholder="Ej. le">
                            </div>

                            <div>
                                <label class="input-label">Fecha de Elaboración</label>
                                <input type="date" name="elaboration_date" required class="input-field">
                            </div>

                            <div>
                                <label class="input-label">Lote de Producción (Marbete)</label>
                                <input type="text" name="label_batch" required class="input-field" placeholder="Ej. L-2024-001">
                            </div>

                            <div class="md:col-span-2 bg-orange-50 p-4 rounded-xl border border-orange-100">
                                <label class="input-label text-[#ff9c00]">Cantidad a Generar</label>
                                <input type="number" name="quantity" min="1" max="500000" required class="input-field border-orange-200 focus:border-orange-400" placeholder="Número de folios">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-8 rounded-2xl shadow-lg border-t-4 border-[#2c3856]">
                        <h2 class="section-title">2. Datos del Producto</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <div class="md:col-span-2">
                                <label class="input-label">Nombre o Marca</label>
                                <input type="text" name="product_name" required class="input-field" placeholder="Ej. Tequila Reserva Especial">
                            </div>

                            <div>
                                <label class="input-label">Tipo de Producto</label>
                                <input type="text" name="product_type" required class="input-field" placeholder="Ej. Tequila Reposado">
                            </div>

                            <div>
                                <label class="input-label">Graduación Alcohólica (%)</label>
                                <input type="number" step="0.1" name="alcohol_content" required class="input-field" placeholder="Ej. 38.5">
                            </div>

                            <div>
                                <label class="input-label">Capacidad</label>
                                <input type="text" name="capacity" required class="input-field" placeholder="Ej. 750">
                            </div>

                            <div>
                                <label class="input-label">Origen del Producto</label>
                                <input type="text" name="origin" required class="input-field" placeholder="Ej. México">
                            </div>

                            <div>
                                <label class="input-label">Fecha de Envasado</label>
                                <input type="date" name="packaging_date" required class="input-field">
                            </div>

                            <div>
                                <label class="input-label">Lote de Producción (Producto)</label>
                                <input type="text" name="product_batch" required class="input-field" placeholder="Ej. L-TEQ-998">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-8 rounded-2xl shadow-lg border-t-4 border-gray-400">
                        <h2 class="section-title">3. Fabricante / Importador</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <div class="md:col-span-2">
                                <label class="input-label">Nombre o Razón Social</label>
                                <input type="text" name="maker_name" required class="input-field" placeholder="Ej. Destilería Ejemplo S.A. de C.V.">
                            </div>

                            <div>
                                <label class="input-label">RFC</label>
                                <input type="text" name="maker_rfc" required class="input-field uppercase" placeholder="Ej. XAXX010101000">
                            </div>
                        </div>
                    </div>

                </div>

                <div class="lg:col-span-1">
                    <div class="sticky top-6">
                        <div class="bg-[#2c3856] rounded-2xl p-6 text-white flex flex-col justify-between relative overflow-hidden shadow-2xl">
                            <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
                            
                            <div>
                                <h3 class="font-bold text-lg mb-6 flex items-center font-brand border-b border-white/20 pb-4">
                                    <svg class="w-5 h-5 mr-2 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    Resumen de Generación
                                </h3>
                                
                                <div class="space-y-6 text-sm">
                                    <div class="bg-white/10 p-4 rounded-xl border border-white/10">
                                        <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Folio Inicial (Ejemplo)</p>
                                        <p class="font-mono text-xl text-white tracking-wider">
                                            <span class="text-[#ff9c00] font-bold" x-text="series ? series : 'XX'">XX</span>-0000000123
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-gray-400 text-xs mb-1">Link generado:</p>
                                        <p class="font-mono text-[10px] text-emerald-400 break-all leading-relaxed bg-black/20 p-2 rounded">
                                            {{ url('/') }}/app/qr/faces/pages/mobile/validadorqr/<span class="text-white">...id...</span>
                                        </p>
                                    </div>

                                    <div class="text-xs text-gray-300 space-y-2 pt-4 border-t border-white/10">
                                        <p>Validación de duplicados</p>
                                        <p>Registro de Lotes</p>
                                        <p>Asignación de Fabricante</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8">
                                <button type="submit" class="w-full bg-[#ff9c00] hover:bg-orange-600 text-white font-bold py-4 rounded-xl shadow-lg shadow-orange-500/30 transition-all duration-300 transform hover:-translate-y-1 flex items-center justify-center font-brand text-lg">
                                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                                    Confirmar y Generar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>

        <div class="mt-12 text-center pb-6">
            <p class="text-xs text-gray-400">© {{ date('Y') }} Control Tower - Módulo de Marbetes</p>
        </div>

    </div>
</x-app-layout>