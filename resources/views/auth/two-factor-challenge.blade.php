<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Security Check - Control Tower</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Raleway:wght@700;800;900&display=swap');

        :root {
            --minmer-navy: #2c3856;
            --minmer-orange: #FF9C00;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            /* Fondo base */
            background-image: url('{{ Storage::disk('s3')->url('fondDeLogin.png') }}');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        /* --- Animaciones de Fondo --- */
        .ambient-light {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }
        .light-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
            animation: float-orb 20s infinite ease-in-out alternate;
        }
        .orb-1 { top: -10%; left: -10%; width: 40vw; height: 40vw; background: rgba(44, 56, 86, 0.6); animation-delay: 0s; }
        .orb-2 { bottom: -10%; right: -10%; width: 35vw; height: 35vw; background: rgba(255, 156, 0, 0.4); animation-delay: -5s; }
        .orb-3 { bottom: 20%; left: 20%; width: 25vw; height: 25vw; background: rgba(44, 56, 86, 0.3); animation-delay: -10s; }

        @keyframes float-orb {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, 50px) scale(1.1); }
            100% { transform: translate(-20px, -30px) scale(0.9); }
        }

        /* --- Glassmorphism Premium --- */
        .glass-card {
            background: rgba(255, 255, 255, 0.05); /* Muy transparente */
            backdrop-filter: blur(25px) saturate(110%);
            -webkit-backdrop-filter: blur(25px) saturate(110%);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        /* --- Input Interactivo de Alta Tecnología --- */
        .scanner-input-container {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
        }

        /* Línea de escaneo luminosa */
        .scanner-line {
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 156, 0, 0.2), transparent);
            pointer-events: none;
            transition: left 0.5s ease;
        }
        .scanner-input:focus ~ .scanner-line {
            left: 100%;
            transition: left 1.5s ease-in-out infinite;
        }

        /* Brillo en el borde al enfocar */
        .input-glow {
            position: absolute;
            inset: -2px;
            background: conic-gradient(from 180deg at 50% 50%, var(--minmer-orange) 0deg, transparent 60deg, transparent 300deg, var(--minmer-orange) 360deg);
            border-radius: inherit;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
            z-index: -1;
            animation: rotate-glow 4s linear infinite;
        }
        .scanner-input:focus ~ .input-glow { opacity: 1; }

        @keyframes rotate-glow {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* --- Utilidades de Animación de Entrada --- */
        .animate-enter-up { animation: enter-up 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; opacity: 0; }
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }

        @keyframes enter-up {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="flex flex-col h-full relative bg-[#1a1f2c]">

    <div class="ambient-light">
        <div class="light-orb orb-1"></div>
        <div class="light-orb orb-2"></div>
        <div class="light-orb orb-3"></div>
        <div class="absolute inset-0 bg-black/30 backdrop-blur-[2px]"></div>
    </div>

    <main class="flex-grow flex items-center justify-center p-4 sm:p-6 relative z-10">
        <div class="w-full max-w-6xl mx-auto grid lg:grid-cols-5 gap-8 items-center">
            
            <div class="hidden lg:flex lg:col-span-3 flex-col items-start justify-center p-12 select-none text-white animate-enter-up">
                <div class="flex items-center gap-4 mb-8">
                    <img src="{{ Storage::disk('s3')->url('LogoBlanco1.PNG') }}" alt="Minmer Global Logo" class="h-16 opacity-90">
                    <div class="h-10 w-px bg-white/30"></div>
                    <h1 class="text-3xl font-extrabold tracking-[0.2em] text-transparent bg-clip-text bg-gradient-to-r from-white to-white/70" style="font-family: 'Raleway', sans-serif;">
                        CONTROL TOWER
                    </h1>
                </div>
                
                <div class="pl-6 border-l-2 border-[#FF9C00]">
                    <h2 class="text-5xl font-black leading-tight mb-4" style="font-family: 'Raleway', sans-serif;">
                        Verificación<br>
                        <span class="text-[#FF9C00]">Segura.</span>
                    </h2>
                    <p class="text-xl text-white/80 font-light leading-relaxed max-w-lg">
                        Acceso restringido. Tu identidad es la llave para operar en la torre de control.
                    </p>
                </div>
            </div>

            <div class="w-full max-w-[450px] mx-auto lg:col-span-2 animate-enter-up delay-200">
                
                <div class="glass-card rounded-3xl p-8 sm:p-10 relative overflow-hidden group">
                    
                    <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-[#FF9C00]/50 to-transparent"></div>

                    <div class="lg:hidden flex justify-center mb-8">
                        <img src="{{ Storage::disk('s3')->url('LogoBlanco1.PNG') }}" alt="Minmer Logo" class="h-12 opacity-90">
                    </div>

                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-tr from-[#2c3856] to-[#1a2236] text-[#FF9C00] mb-5 shadow-lg shadow-black/20 border border-white/10 relative">
                            <div class="absolute inset-0 rounded-full bg-[#FF9C00] opacity-20 blur-md animate-pulse"></div>
                            <svg class="w-9 h-9 relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-white tracking-tight mb-2" style="font-family: 'Raleway', sans-serif;">Autenticación 2FA</h2>
                        <p class="text-sm text-white/60 font-medium">
                            Ingresa el token dinámico de tu dispositivo.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('2fa.verify') }}" x-data="{ code: '', focused: false }">
                        @csrf

                        <div class="mb-8">
                            <div class="scanner-input-container relative group z-0">
                                <div class="input-glow"></div>
                                
                                <input id="code" 
                                       type="text" 
                                       name="code" 
                                       inputmode="numeric" 
                                       pattern="[0-9]*"
                                       autocomplete="one-time-code"
                                       autofocus
                                       maxlength="6"
                                       x-model="code"
                                       @focus="focused = true"
                                       @blur="focused = false"
                                       placeholder="000 000"
                                       class="scanner-input w-full px-4 py-5 text-center text-3xl font-mono tracking-[0.5em] font-bold text-white bg-white/10 border-2 border-white/10 rounded-xl focus:border-[#FF9C00]/50 focus:bg-[#2c3856]/80 focus:ring-0 shadow-inner transition-all duration-300 placeholder-white/20 relative z-10 backdrop-blur-md" />
                                
                                <div class="scanner-line z-20"></div>

                                <div class="flex justify-center gap-2 mt-3 absolute -bottom-6 inset-x-0">
                                    <template x-for="i in 6">
                                        <div class="w-1.5 h-1.5 rounded-full transition-all duration-300"
                                             :class="code.length >= i ? 'bg-[#FF9C00] scale-110 shadow-[0_0_10px_#FF9C00]' : 'bg-white/20'"></div>
                                    </template>
                                </div>
                            </div>
                            
                            @error('code')
                                <p class="mt-8 text-sm text-red-300 text-center font-bold flex items-center justify-center gap-2 bg-red-900/30 py-2 rounded-lg border border-red-500/30 backdrop-blur-md animate-[pulse_2s_infinite]">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="flex flex-col gap-4 mt-10">
                            <button type="submit" 
                                    :disabled="code.length < 6"
                                    :class="code.length < 6 ? 'opacity-50 cursor-not-allowed' : 'hover:scale-[1.02] hover:shadow-[0_10px_20px_-10px_rgba(255,156,0,0.5)]'"
                                    class="relative w-full py-4 bg-gradient-to-r from-[#FF9C00] to-[#ffb340] text-[#2c3856] font-extrabold rounded-xl transition-all duration-300 ease-out uppercase tracking-wider text-sm shadow-lg flex items-center justify-center gap-3 overflow-hidden group disabled:opacity-50">
                                
                                <div class="absolute inset-0 h-full w-full scale-0 rounded-xl transition-all duration-300 group-hover:scale-100 group-hover:bg-white/20"></div>
                                
                                <span class="relative z-10">Validar Acceso</span>
                                <svg class="w-5 h-5 relative z-10 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </button>
                        </div>
                    </form>
                </div>

            </div>

        </div>
    </main>

    <footer class="w-full p-6 relative z-10 animate-enter-up delay-300">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row justify-between items-center text-center text-xs text-white/50 font-medium uppercase tracking-widest">
            <div class="flex gap-6 mb-4 md:mb-0">
                <a href="{{ route('terms.conditions') }}" class="hover:text-white transition-colors">Términos</a>
                <a href="{{ route('privacy.policy') }}" class="hover:text-white transition-colors">Privacidad</a>
            </div>
            <span>© {{ date('Y') }} Minmer Global Systems.</span>
        </div>
    </footer>

</body>
</html>