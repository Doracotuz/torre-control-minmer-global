<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>
            @if(request()->routeIs('tracking.*'))
                Rastreo de entregas -
            @else
                Portal Operativo -
            @endif
            {{ config('app.name', 'Minmer Global') }}
        </title>
        
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Space+Grotesk:wght@300;500;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body { font-family: 'Outfit', sans-serif; }
            .font-mono-tech { font-family: 'Space Grotesk', monospace; }
            
            .bg-animated {
                background-color: #f0f4f8;
                background-image: 
                    radial-gradient(at 0% 0%, hsla(253,16%,7%,0.05) 0px, transparent 50%),
                    radial-gradient(at 50% 100%, hsla(225,39%,30%,0.05) 0px, transparent 50%);
                background-size: 100% 100%;
                min-height: 100vh;
            }
        </style>
    </head>
    <body class="antialiased bg-animated text-slate-800 overflow-x-hidden">
        
        <nav class="fixed w-full z-50 top-0 left-0 transition-all duration-300 backdrop-blur-md bg-white/70 border-b border-white/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <div class="flex-shrink-0 flex items-center gap-3">
                        <img src="{{ Storage::disk('s3')->url('LogoAzulm.PNG') }}" alt="Logo" class="h-10 w-auto opacity-90 hover:opacity-100 transition-opacity">
                        <div class="hidden md:block h-6 w-px bg-slate-300 mx-2"></div>
                        <span class="hidden md:block text-slate-500 text-sm tracking-widest uppercase font-bold">Control Tower</span>
                    </div>
                    <div class="flex flex-col items-end mr-2">
                        <div id="clock-date" class="text-xs font-mono-tech text-slate-400">
                            Cargando fecha...
                        </div>
                        <div id="clock-time" class="text-sm font-mono font-bold text-[#ff9c00] tracking-widest leading-none">
                            00:00:00
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <main class="pt-24 min-h-screen relative">
            @yield('content')
        </main>

        @stack('scripts')
        <script>
            function updateClock() {
                const now = new Date();
                
                const dateOptions = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
                const dateString = now.toLocaleDateString('es-MX', dateOptions);
                
                const timeString = now.toLocaleTimeString('es-MX', { hour12: false });
                
                document.getElementById('clock-date').textContent = dateString;
                document.getElementById('clock-time').textContent = timeString;
            }

            updateClock();
            setInterval(updateClock, 1000);
        </script>        
    </body>
</html>