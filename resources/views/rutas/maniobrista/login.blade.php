@extends('layouts.guest-rutas')

@section('content')
<style>
    @keyframes pulse-ring {
        0% { transform: scale(0.33); opacity: 1; }
        80%, 100% { opacity: 0; }
    }
    .glass-panel {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }
    .pattern-grid {
        background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
        background-size: 24px 24px;
    }
</style>

<div class="min-h-screen bg-slate-50 pattern-grid pb-20">
    <div class="relative bg-[#2c3856] pb-24 overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
        <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
        
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-12">
            <div class="text-center relative z-10">
                <span class="inline-block py-1 px-3 rounded-full bg-white/10 border border-white/20 text-blue-200 text-xs font-mono tracking-widest uppercase mb-4 backdrop-blur-sm">
                    Personal de Campo
                </span>
                <h2 class="text-4xl md:text-5xl font-extrabold text-white tracking-tight mb-2">
                    Portal de <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-400">Maniobrista</span>
                </h2>
                <p class="text-blue-200/80 text-sm md:text-base max-w-xl mx-auto font-light">
                    Registro de asistencias y carga de evidencias.
                </p>
            </div>
        </div>
    </div>

    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 -mt-16 relative z-20">
        <div class="glass-panel rounded-2xl shadow-[0_20px_50px_rgba(8,_112,_184,_0.07)] overflow-hidden ring-1 ring-slate-900/5 p-8">
            <form id="login-form" method="POST" action="{{ route('maniobrista.access') }}" class="space-y-6">
                @csrf
                <div>
                    <label for="numero_empleado" class="block text-sm font-bold text-slate-700 uppercase tracking-wider mb-2">Número de Empleado</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input id="numero_empleado" class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl leading-5 bg-slate-50 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:border-transparent sm:text-sm transition duration-150 ease-in-out" type="text" name="numero_empleado" required autofocus placeholder="Ej. EMP-12345" />
                    </div>
                </div>

                <div>
                    <label for="guia" class="block text-sm font-bold text-slate-700 uppercase tracking-wider mb-2">Número de Guía</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <input id="guia" class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl leading-5 bg-slate-50 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:border-transparent sm:text-sm transition duration-150 ease-in-out" type="text" name="guia" required placeholder="Ej. G-2024-001" />
                    </div>
                </div>

                @if(session('error'))
                    <div class="rounded-lg bg-red-50 p-4 border-l-4 border-red-500">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <button type="button" onclick="confirmAndSubmit()" class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gradient-to-r from-[#ff9c00] to-orange-600 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#ff9c00] transition-all transform active:scale-95 uppercase tracking-widest">
                    Acceder al Sistema
                </button>
            </form>
        </div>
        
        <div class="mt-8 text-center">
            <p class="text-xs text-slate-400">
                &copy; {{ date('Y') }} Sistema de Control Logístico
            </p>
        </div>
    </div>
</div>

<script>
    function confirmAndSubmit() {
        const employee = document.getElementById('numero_empleado').value;
        if(employee && document.getElementById('guia').value) {
            if (confirm(`¿Confirmas que tu número de empleado es ${employee}?`)) {
                document.getElementById('login-form').submit();
            }
        } else {
            alert('Por favor complete todos los campos');
        }
    }
</script>
@endsection