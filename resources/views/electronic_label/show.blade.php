<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="relative min-h-screen text-[#2c3856] font-sans overflow-x-hidden selection:bg-[#ff9c00] selection:text-white"
         x-data="{ 
            mouse: { x: 0, y: 0, w: 0, h: 0 },
            focusedRow: null,
            init() {
                this.mouse.w = window.innerWidth;
                this.mouse.h = window.innerHeight;
                window.addEventListener('resize', () => { this.mouse.w = window.innerWidth; this.mouse.h = window.innerHeight; });
            },
            onMove(e) {
                this.mouse.x = e.clientX;
                this.mouse.y = e.clientY;
                
                const bg = document.getElementById('ambient-bg');
                if(bg) bg.style.transform = `translate(${e.clientX * 0.02}px, ${e.clientY * 0.02}px)`;
            },
            copyToClipboard(text, id) {
                navigator.clipboard.writeText(text);
                const el = document.getElementById('feedback-' + id);
                el.classList.remove('translate-y-full', 'opacity-0');
                setTimeout(() => el.classList.add('translate-y-full', 'opacity-0'), 2000);
            }
         }"
         @mousemove.window="onMove">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Raleway:wght@700;800;900&display=swap" rel="stylesheet">
        
        <style>
            body { font-family: 'Montserrat', sans-serif; }
            .font-brand { font-family: 'Raleway', sans-serif; }
            
            .spotlight-mask {
                background: radial-gradient(600px circle at var(--x) var(--y), rgba(255, 156, 0, 0.03), transparent 40%);
                position: fixed; inset: 0; pointer-events: none; z-index: 1;
            }

            .card-3d {
                transition: transform 0.1s ease;
                transform-style: preserve-3d;
            }
            
            .entry-anim { opacity: 0; animation: slideUpFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
            @keyframes slideUpFade { from { opacity: 0; transform: translateY(40px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }

            .glass-panel {
                background: rgba(255, 255, 255, 0.6);
                backdrop-filter: blur(16px);
                border: 1px solid rgba(255, 255, 255, 0.8);
                box-shadow: 0 4px 30px rgba(0, 0, 0, 0.03);
            }

            .row-focus-blur { transition: all 0.4s ease; }
            .parent-hovered .row-focus-blur:not(.active-row) {
                filter: blur(2px);
                opacity: 0.4;
                transform: scale(0.98);
            }
            .active-row {
                transform: scale(1.02);
                background: white;
                box-shadow: 0 20px 50px -12px rgba(44, 56, 86, 0.15);
                border-color: #ff9c00;
                z-index: 10;
            }

            .glitch-text { position: relative; }
            .glitch-text::before, .glitch-text::after {
                content: attr(data-text); position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0;
            }
            .group:hover .glitch-text::before { animation: glitch-1 0.4s infinite; opacity: 1; color: #ff9c00; clip-path: inset(50% 0 30% 0); transform: translate(-2px,0); }
            .group:hover .glitch-text::after { animation: glitch-2 0.4s infinite; opacity: 1; color: #2c3856; clip-path: inset(10% 0 60% 0); transform: translate(2px,0); }
            
            @keyframes glitch-1 { 0% {clip-path: inset(20% 0 80% 0);} 20% {clip-path: inset(60% 0 10% 0);} 40% {clip-path: inset(40% 0 50% 0);} 60% {clip-path: inset(80% 0 5% 0);} 80% {clip-path: inset(10% 0 70% 0);} 100% {clip-path: inset(30% 0 20% 0);} }
            @keyframes glitch-2 { 0% {clip-path: inset(10% 0 60% 0);} 20% {clip-path: inset(30% 0 20% 0);} 40% {clip-path: inset(10% 0 50% 0);} 60% {clip-path: inset(50% 0 30% 0);} 80% {clip-path: inset(20% 0 10% 0);} 100% {clip-path: inset(70% 0 5% 0);} }
        </style>

        <div class="spotlight-mask" :style="`--x: ${mouse.x}px; --y: ${mouse.y}px`"></div>

        <div id="ambient-bg" class="fixed inset-0 z-0 pointer-events-none transition-transform duration-75 ease-out">
            <div class="absolute top-0 right-0 w-[800px] h-[800px] bg-gradient-to-b from-blue-50 to-transparent rounded-full blur-3xl opacity-60 mix-blend-multiply"></div>
            <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-gradient-to-t from-orange-50 to-transparent rounded-full blur-3xl opacity-60 mix-blend-multiply"></div>
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoNDQsIDU2LCA4NiwgMC4wNSkiLz48L3N2Zz4='); opacity: 0.6;"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-6 py-10 lg:py-16">
            
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end mb-16 gap-8 entry-anim" style="animation-delay: 0.1s;">
                <div class="relative">
                    <a href="{{ route('electronic-label.records') }}" class="group inline-flex items-center space-x-2 text-xs font-bold text-slate-400 uppercase tracking-widest hover:text-[#ff9c00] transition-colors duration-300 mb-6">
                        <span class="border-b border-transparent group-hover:border-[#ff9c00] transition-all">← Volver al Historial</span>
                    </a>
                    
                    <h1 class="text-8xl font-black font-brand text-[#2c3856] tracking-tighter leading-[0.8] mb-2 group cursor-default">
                        <span class="glitch-text inline-block" data-text="{{ $batchInfo->series }}">{{ $batchInfo->series }}</span>
                    </h1>
                    
                    <div class="flex items-center gap-4 mt-6">
                        <div class="bg-[#2c3856] text-white px-4 py-1.5 text-xs font-bold uppercase tracking-widest rounded-sm transform skew-x-[-10deg]">
                            <span class="block transform skew-x-[10deg]">Producción</span>
                        </div>
                        <div class="h-px w-16 bg-slate-300"></div>
                        <span class="text-xl font-medium text-slate-600">{{ $batchInfo->product_name }}</span>
                    </div>
                </div>

                <div class="flex flex-col items-end gap-6">
                    <div class="text-right">
                        <div class="text-[10px] uppercase font-bold text-slate-400 tracking-[0.2em] mb-1">Fecha de Generación</div>
                        <div class="text-2xl font-brand font-bold text-[#2c3856]">{{ \Carbon\Carbon::parse($date)->format('d . m . Y') }}</div>
                    </div>
                    
                    <a href="{{ route('electronic-label.download-csv', ['series' => $batchInfo->series, 'date' => $date]) }}" 
                       class="relative group overflow-hidden bg-[#ff9c00] text-white px-10 py-4 rounded-full font-brand font-bold text-sm shadow-xl shadow-orange-200 transition-all hover:shadow-orange-300 hover:scale-105 active:scale-95">
                        <div class="absolute inset-0 w-full h-full bg-white/20 scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>
                        <span class="relative flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            DESCARGAR DATA
                        </span>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-20 entry-anim" style="animation-delay: 0.2s;">
                <div class="card-3d glass-panel p-8 rounded-3xl relative overflow-hidden group"
                     :style="`transform: perspective(1000px) rotateX(${(mouse.y - (mouse.h/2)) / 50}deg) rotateY(${(mouse.x - (mouse.w/2)) / 50}deg)`">
                    <div class="absolute top-0 right-0 p-6 opacity-5 group-hover:opacity-10 transition-opacity">
                        <svg class="w-32 h-32 text-[#2c3856]" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM7 10h2v7H7zm4-3h2v10h-2zm4 6h2v4h-2z"/></svg>
                    </div>
                    <div class="relative z-10">
                        <div class="text-[#ff9c00] font-bold text-xs uppercase tracking-widest mb-2">Volumen Total</div>
                        <div class="text-6xl font-brand font-black text-[#2c3856]">{{ $labels->total() }}</div>
                        <div class="mt-4 text-xs text-slate-500 font-medium flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span> Etiquetas generadas
                        </div>
                    </div>
                </div>

                <div class="card-3d glass-panel p-8 rounded-3xl relative overflow-hidden group"
                     :style="`transform: perspective(1000px) rotateX(${(mouse.y - (mouse.h/2)) / 60}deg) rotateY(${(mouse.x - (mouse.w/2)) / 60}deg)`">
                     <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-gradient-to-tl from-[#2c3856]/10 to-transparent rounded-full"></div>
                    <div class="relative z-10">
                        <div class="text-[#2c3856] font-bold text-xs uppercase tracking-widest mb-2">Rango de Serie</div>
                        <div class="text-4xl font-brand font-bold text-slate-700 flex items-baseline gap-2">
                            <span class="text-lg opacity-50">#</span>{{ $labels->first()?->folio ?? '000' }}
                        </div>
                        <div class="mt-4 w-full bg-slate-200 h-0.5 relative overflow-hidden">
                            <div class="absolute left-0 top-0 h-full w-1/3 bg-[#2c3856] animate-[shimmer_2s_infinite]"></div>
                        </div>
                    </div>
                </div>

                <div class="card-3d glass-panel p-8 rounded-3xl relative overflow-hidden flex items-center justify-between"
                     :style="`transform: perspective(1000px) rotateX(${(mouse.y - (mouse.h/2)) / 70}deg) rotateY(${(mouse.x - (mouse.w/2)) / 70}deg)`">
                    <div>
                        <div class="text-[#2c3856] font-bold text-xs uppercase tracking-widest mb-1">Estado del Lote</div>
                        <div class="text-2xl font-brand font-bold text-emerald-600">Validado</div>
                    </div>
                    <div class="relative flex items-center justify-center w-16 h-16">
                        <div class="absolute inset-0 border-4 border-slate-100 rounded-full"></div>
                        <div class="absolute inset-0 border-4 border-[#ff9c00] rounded-full border-t-transparent animate-spin"></div>
                        <svg class="w-6 h-6 text-[#2c3856]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </div>
            </div>

            <div class="relative entry-anim" style="animation-delay: 0.4s;"
                 :class="{ 'parent-hovered': focusedRow !== null }">
                
                <div class="flex justify-between items-end mb-6 px-4">
                    <h3 class="text-xl font-brand font-bold text-[#2c3856]">Registro de Etiquetas</h3>
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                        Mostrando {{ $labels->count() }} resultados
                    </div>
                </div>

                <div class="grid grid-cols-12 px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#2c3856]/50 border-b border-slate-200 mb-4">
                    <div class="col-span-2">ID Folio</div>
                    <div class="col-span-2 text-center">Secuencia</div>
                    <div class="col-span-6 pl-4">Enlace Digital</div>
                    <div class="col-span-2 text-right">Interacción</div>
                </div>

                <div class="space-y-2">
                    @foreach($labels as $index => $label)
                        <div class="row-focus-blur relative grid grid-cols-12 items-center bg-white rounded-xl p-4 border border-transparent transition-all duration-300 cursor-default"
                             :class="{ 'active-row': focusedRow === {{ $label->id }} }"
                             @mouseenter="focusedRow = {{ $label->id }}"
                             @mouseleave="focusedRow = null">
                            
                            <div class="col-span-2 font-brand font-bold text-[#2c3856] text-lg relative">
                                <span class="absolute -left-4 top-1/2 -translate-y-1/2 w-1 h-0 bg-[#ff9c00] transition-all duration-300"
                                      :class="{ 'h-8': focusedRow === {{ $label->id }} }"></span>
                                {{ $label->folio }}
                            </div>

                            <div class="col-span-2 text-center">
                                <span class="inline-block px-3 py-1 rounded-md bg-slate-50 text-slate-500 text-xs font-mono font-bold border border-slate-100 transition-colors"
                                      :class="{ 'bg-[#2c3856] text-white border-[#2c3856]': focusedRow === {{ $label->id }} }">
                                    {{ str_pad($label->consecutive, 5, '0', STR_PAD_LEFT) }}
                                </span>
                            </div>

                            <div class="col-span-6 pl-4 relative">
                                <button @click="copyToClipboard('{{ $label->full_url }}', '{{ $label->id }}')" 
                                        class="w-full text-left group/btn flex items-center justify-between bg-slate-50 hover:bg-white border border-slate-100 hover:border-[#ff9c00] rounded-lg px-4 py-2 transition-all duration-200">
                                    <span class="text-xs text-slate-500 font-medium truncate font-mono w-[90%] group-hover/btn:text-[#2c3856] transition-colors">
                                        {{ $label->full_url }}
                                    </span>
                                    <svg class="w-4 h-4 text-slate-300 group-hover/btn:text-[#ff9c00] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                </button>
                                
                                <div id="feedback-{{ $label->id }}" 
                                     class="absolute top-0 right-0 h-full bg-emerald-500 text-white text-[10px] font-bold px-4 rounded-lg flex items-center justify-center translate-y-full opacity-0 transition-all duration-300 pointer-events-none shadow-lg z-20">
                                    COPIADO
                                </div>
                            </div>

                            <div class="col-span-2 flex justify-end">
                                <a href="{{ $label->full_url }}" target="_blank" 
                                   class="w-10 h-10 rounded-full flex items-center justify-center text-slate-400 hover:bg-[#2c3856] hover:text-white transition-all duration-300 transform hover:rotate-12 hover:scale-110">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $labels->links() }}
                </div>
            </div>
            
            <div class="py-12 flex justify-center opacity-40 hover:opacity-100 transition-opacity duration-500">
                <div class="flex items-center gap-2">
                    <div class="h-2 w-2 bg-[#ff9c00] rounded-full animate-bounce"></div>
                    <div class="h-2 w-2 bg-[#2c3856] rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                    <div class="h-2 w-2 bg-[#ff9c00] rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>