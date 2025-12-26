<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="min-h-screenrelative overflow-x-hidden font-sans text-[#2c3856] selection:bg-[#ff9c00] selection:text-white"
         x-data="{ 
            deleteModalOpen: false, 
            deleteUrl: '', 
            batchSeries: '',
            mouse: { x: 0, y: 0 },
            init() {
                window.addEventListener('mousemove', (e) => {
                    this.mouse.x = (e.clientX - window.innerWidth / 2) / 20;
                    this.mouse.y = (e.clientY - window.innerHeight / 2) / 20;
                });
            },
            confirmDelete(url, series) {
                this.deleteUrl = url;
                this.batchSeries = series;
                this.deleteModalOpen = true;
            }
         }"
         x-init="init()">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Raleway:wght@700;800;900&display=swap" rel="stylesheet">
        
        <style>
            body { font-family: 'Montserrat', sans-serif; }
            .font-brand { font-family: 'Raleway', sans-serif; }
            
            /* Animaciones de Entrada */
            .stagger-load { opacity: 0; animation: slideUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; }
            @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
            
            /* Fondo Animado Sutil */
            .bg-pattern {
                background-image: radial-gradient(#2c3856 0.5px, transparent 0.5px), radial-gradient(#ff9c00 0.5px, #f0f2f5 0.5px);
                background-size: 20px 20px;
                background-position: 0 0, 10px 10px;
                opacity: 0.05;
            }

            /* Efecto Cristal */
            .glass-card {
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.5);
                box-shadow: 0 10px 30px -5px rgba(44, 56, 86, 0.05);
                transition: all 0.3s ease;
            }
            .glass-card:hover {
                transform: translateY(-5px) scale(1.005);
                box-shadow: 0 20px 40px -5px rgba(44, 56, 86, 0.1);
                border-color: rgba(255, 156, 0, 0.3);
            }

            /* Botón Brillante */
            .btn-shine {
                position: relative; overflow: hidden;
            }
            .btn-shine::after {
                content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
                transition: 0.5s;
            }
            .btn-shine:hover::after { left: 100%; }
        </style>

        <div class="fixed inset-0 pointer-events-none z-0">
            <div class="absolute inset-0 bg-pattern"></div>
            <div class="absolute top-[-10%] right-[-10%] w-[600px] h-[600px] bg-gradient-to-br from-[#ff9c00] to-transparent opacity-5 rounded-full blur-3xl"
                 :style="`transform: translate(${mouse.x * -1}px, ${mouse.y * -1}px)`"></div>
            <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] bg-gradient-to-tr from-[#2c3856] to-transparent opacity-5 rounded-full blur-3xl"
                 :style="`transform: translate(${mouse.x}px, ${mouse.y}px)`"></div>
        </div>

        <div class="relative z-10 max-w-[1400px] mx-auto p-6 lg:p-10">
            
            <div class="flex flex-col lg:flex-row justify-between items-end mb-12 gap-6 stagger-load" style="animation-delay: 0.1s;">
                <div>
                    <a href="{{ route('electronic-label.index') }}" class="group inline-flex items-center text-xs font-bold text-gray-400 uppercase tracking-widest hover:text-[#ff9c00] mb-2 transition-colors">
                        <span class="mr-2 group-hover:-translate-x-1 transition-transform">←</span> Dashboard Principal
                    </a>
                    <h1 class="text-5xl font-black text-[#2c3856] font-brand tracking-tight">
                        Historial de <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#2c3856] to-[#ff9c00]">Lotes</span>
                    </h1>
                    <p class="text-gray-500 mt-2 font-medium max-w-lg">
                        Administración y trazabilidad de marbetes electrónicos generados.
                    </p>
                </div>

                <a href="{{ route('electronic-label.create') }}" 
                   class="btn-shine bg-[#2c3856] text-white px-8 py-4 rounded-xl shadow-xl shadow-blue-900/20 hover:shadow-blue-900/30 transition-all flex items-center gap-3 group">
                    <div class="bg-white/10 p-2 rounded-lg group-hover:bg-[#ff9c00] group-hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </div>
                    <div class="text-left">
                        <span class="block text-[10px] uppercase font-bold tracking-widest opacity-70">Acción</span>
                        <span class="block font-brand font-bold text-lg leading-none">Generar Nuevo Lote</span>
                    </div>
                </a>
            </div>

            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     class="fixed top-10 left-1/2 -translate-x-1/2 z-50 animate-bounce stagger-load">
                    <div class="bg-emerald-500 text-white px-6 py-3 rounded-full shadow-2xl flex items-center gap-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="font-bold">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <div class="space-y-4">
                <div class="hidden md:grid grid-cols-12 gap-4 px-8 pb-2 text-[10px] font-bold uppercase tracking-widest text-gray-400 stagger-load" style="animation-delay: 0.2s;">
                    <div class="col-span-2">Fecha de Emisión</div>
                    <div class="col-span-3">Identificador de Serie</div>
                    <div class="col-span-3">Producto Asociado</div>
                    <div class="col-span-2 text-center">Volumen</div>
                    <div class="col-span-2 text-right">Herramientas</div>
                </div>

                @forelse($batches as $index => $batch)
                    <div class="glass-card rounded-2xl p-6 relative group stagger-load" 
                         style="animation-delay: {{ 0.2 + ($index * 0.05) }}s;">
                        
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#2c3856] rounded-l-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                            
                            <div class="col-span-2 flex items-center gap-3">
                                <div class="bg-gray-100 p-2.5 rounded-lg text-gray-500 group-hover:bg-[#ff9c00] group-hover:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                                <div>
                                    <p class="font-brand font-bold text-lg text-[#2c3856]">{{ $batch->created_at->format('d/m/Y') }}</p>
                                    <p class="text-xs text-gray-400">{{ $batch->created_at->format('H:i A') }}</p>
                                </div>
                            </div>

                            <div class="col-span-3">
                                <div class="flex items-center gap-2">
                                    <span class="bg-[#ff9c00]/10 text-[#ff9c00] px-3 py-1 rounded-md text-sm font-black font-brand border border-[#ff9c00]/20">
                                        {{ $batch->series }}
                                    </span>
                                    <div class="h-px flex-1 bg-gray-200"></div>
                                </div>
                                <div class="flex justify-between text-[10px] text-gray-400 mt-1 font-mono">
                                    <span>{{ str_pad($batch->start_folio, 8, '0', STR_PAD_LEFT) }}</span>
                                    <span>➜</span>
                                    <span>{{ str_pad($batch->end_folio, 8, '0', STR_PAD_LEFT) }}</span>
                                </div>
                            </div>

                            <div class="col-span-3">
                                <p class="font-bold text-[#2c3856] text-sm md:text-base">{{ $batch->product_name }}</p>
                                <div class="flex items-center gap-1 mt-1">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    <p class="text-xs text-gray-500">{{ $batch->label_type }}</p>
                                </div>
                            </div>

                            <div class="col-span-2 text-center">
                                <span class="block font-brand font-black text-xl text-[#2c3856]">{{ number_format($batch->total) }}</span>
                                <span class="text-[10px] uppercase text-gray-400 font-bold tracking-wider">Unidades</span>
                                <div class="w-full bg-gray-100 h-1.5 rounded-full mt-2 overflow-hidden">
                                    <div class="bg-gradient-to-r from-[#2c3856] to-[#ff9c00] h-full w-[85%] rounded-full"></div>
                                </div>
                            </div>

                            <div class="col-span-2 flex justify-end gap-2 opacity-100 md:opacity-60 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('electronic-label.show-batch', ['series' => $batch->series, 'date' => $batch->created_at]) }}" 
                                   class="p-2.5 rounded-lg bg-white border border-gray-100 text-blue-600 hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all hover:scale-110 shadow-sm" title="Ver Detalles">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </a>

                                <a href="{{ route('electronic-label.download-csv', ['series' => $batch->series, 'date' => $batch->created_at]) }}" 
                                   class="p-2.5 rounded-lg bg-white border border-gray-100 text-emerald-600 hover:bg-emerald-500 hover:text-white hover:border-emerald-500 transition-all hover:scale-110 shadow-sm" title="Descargar CSV">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                </a>

                                <button @click="confirmDelete('{{ route('electronic-label.destroy-batch', ['series' => $batch->series, 'date' => $batch->created_at]) }}', '{{ $batch->series }}')" 
                                        class="p-2.5 rounded-lg bg-white border border-gray-100 text-red-500 hover:bg-red-500 hover:text-white hover:border-red-500 transition-all hover:scale-110 shadow-sm" title="Eliminar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-20 bg-white/50 rounded-3xl border border-dashed border-gray-300">
                        <div class="inline-block p-4 rounded-full bg-gray-50 mb-4">
                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-[#2c3856]">Sin Registros</h3>
                        <p class="text-gray-500 text-sm mt-1">No se han encontrado lotes generados en el sistema.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-8 p-4 rounded-xl bg-white/40 backdrop-blur-sm border border-white/60 stagger-load" style="animation-delay: 0.5s;">
                {{ $batches->links() }}
            </div>
        </div>

        <div x-show="deleteModalOpen" 
             style="display: none;"
             class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto overflow-x-hidden bg-[#2c3856]/80 backdrop-blur-sm transition-all"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="relative w-full max-w-md p-4" @click.away="deleteModalOpen = false">
                <div class="relative bg-white rounded-2xl shadow-2xl overflow-hidden transform transition-all"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100">
                    
                    <div class="bg-red-500 h-2 w-full"></div>
                    
                    <div class="p-8 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100 mb-6 animate-pulse">
                            <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        
                        <h3 class="mb-2 text-xl font-brand font-black text-[#2c3856]">¿Eliminar Lote <span x-text="batchSeries" class="text-red-600"></span>?</h3>
                        <p class="text-sm text-gray-500 leading-relaxed mb-8">
                            Esta acción es <span class="font-bold text-red-500">irreversible</span>. Se eliminarán permanentemente todos los marbetes y registros asociados a este lote.
                        </p>

                        <div class="flex justify-center gap-3">
                            <button @click="deleteModalOpen = false" 
                                    class="px-5 py-2.5 rounded-lg border border-gray-200 text-gray-600 font-bold text-sm hover:bg-gray-50 transition-colors">
                                Cancelar
                            </button>
                            
                            <form :action="deleteUrl" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="px-5 py-2.5 rounded-lg bg-red-600 text-white font-bold text-sm hover:bg-red-700 shadow-lg shadow-red-500/30 transition-all hover:scale-105">
                                    Sí, Eliminar Todo
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>